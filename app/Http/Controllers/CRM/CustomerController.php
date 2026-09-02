<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerActivity;
use App\Models\CustomerDocument;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CustomerController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $filters   = $request->only(['search','source','customer_type','city','assigned_to']);
        $customers = Customer::with(['branch','assignedTo'])
            ->when($filters['search']        ?? null, fn($q,$v) => $q->search($v))
            ->when($filters['source']        ?? null, fn($q,$v) => $q->where('source',$v))
            ->when($filters['customer_type'] ?? null, fn($q,$v) => $q->where('customer_type',$v))
            ->when($filters['city']          ?? null, fn($q,$v) => $q->where('city',$v))
            ->when($filters['assigned_to']   ?? null, fn($q,$v) => $q->where('assigned_to',$v))
            ->latest()->paginate(20)->withQueryString();

        $salesUsers = User::active()->get();
        return view('crm.customers.index', compact('customers','filters','salesUsers'));
    }

    // ── Create ─────────────────────────────────────────────────────

    public function create(): View
    {
        $branches   = Branch::active()->get();
        $salesUsers = User::active()->get();

        if (request()->ajax()) {
            return view('crm.customers.partials.form', compact('branches', 'salesUsers'));
        }

        return view('crm.customers.create', compact('branches', 'salesUsers'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'full_name'          => ['required','string','max:100'],
            'father_husband_name'=> ['nullable','string','max:100'],
            'cnic'               => ['nullable','string','max:20'],
            'mobile'             => ['required','string','max:20'],
            'mobile_alt'         => ['nullable','string','max:20'],
            'email'              => ['nullable','email','max:150'],
            'address'            => ['nullable','string'],
            'city'               => ['nullable','string','max:100'],
            'occupation'         => ['nullable','string','max:100'],
            'customer_type'      => ['required','in:buyer,seller,both'],
            'source'             => ['required','string'],
            'tax_status'         => ['required','in:filer,non_filer,unknown'],
            'assigned_to'        => ['nullable','exists:users,id'],
            'notes'              => ['nullable','string'],
        ]);

        $data['created_by'] = auth()->id();
        $data['branch_id']  = $request->input('branch_id', auth()->user()->branch_id);
        $customer = Customer::create($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully.',
            ]);
        }

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer created successfully.');
    }

    // ── Show ───────────────────────────────────────────────────────

    public function show(Customer $customer): View
    {
        $customer->load([
            'branch','assignedTo','createdBy',
            'activities.createdBy',
            'documents.uploadedBy',
            'leads',
            'invoices.vehicle.make',
            
        ]);

        if (request()->ajax()) {
            return view('crm.customers.partials.show-modal', compact('customer'));
        }

        $salesUsers = User::active()->get();
        return view('crm.customers.show', compact('customer','salesUsers'));
    }

    // ── Edit / Update ──────────────────────────────────────────────

    public function edit(Customer $customer): View
    {
        $branches   = Branch::active()->get();
        $salesUsers = User::active()->get();

        if (request()->ajax()) {
            return view('crm.customers.partials.form', compact('customer', 'branches', 'salesUsers'));
        }

        return view('crm.customers.edit', compact('customer','branches','salesUsers'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'full_name'          => ['required','string','max:100'],
            'father_husband_name'=> ['nullable','string','max:100'],
            'cnic'               => ['nullable','string','max:20'],
            'mobile'             => ['required','string','max:20'],
            'mobile_alt'         => ['nullable','string','max:20'],
            'email'              => ['nullable','email','max:150'],
            'address'            => ['nullable','string'],
            'city'               => ['nullable','string','max:100'],
            'occupation'         => ['nullable','string','max:100'],
            'customer_type'      => ['required','in:buyer,seller,both'],
            'source'             => ['required','string'],
            'tax_status'         => ['required','in:filer,non_filer,unknown'],
            'assigned_to'        => ['nullable','exists:users,id'],
            'notes'              => ['nullable','string'],
        ]);

        $customer->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer updated.',
            ]);
        }

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer): RedirectResponse|JsonResponse
    {
        $this->authorize('delete-customers');
        $customer->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer removed.',
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer removed.');
    }

    // ── Activities ─────────────────────────────────────────────────

    public function storeActivity(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate([
            'type'         => ['required','in:call,meeting,whatsapp,email,note,task'],
            'subject'      => ['nullable','string','max:200'],
            'description'  => ['nullable','string'],
            'outcome'      => ['nullable','string','max:255'],
            'scheduled_at' => ['nullable','date'],
        ]);

        $data['customer_id'] = $customer->id;
        $data['created_by']  = auth()->id();
        $data['completed_at']= in_array($data['type'],['note','call','whatsapp','email']) ? now() : null;

        CustomerActivity::create($data);

        return back()->with('success', ucfirst($data['type']) . ' logged.');
    }

    // ── Documents ──────────────────────────────────────────────────

    public function uploadDocument(Request $request, Customer $customer): RedirectResponse
    {
        $request->validate([
            'document_type' => ['required','in:cnic_front,cnic_back,passport,utility_bill,salary_slip,other'],
            'file'          => ['required','file','max:5120','mimes:pdf,jpg,jpeg,png,webp'],
        ]);

        $path = $request->file('file')->store(
            "tenants/" . app('tenant')->id . "/customers/{$customer->id}/docs", 's3'
        );

        CustomerDocument::create([
            'tenant_id'     => app('tenant')->id,
            'customer_id'   => $customer->id,
            'document_type' => $request->document_type,
            'file_path'     => $path,
            'uploaded_by'   => auth()->id(),
            'uploaded_at'   => now(),
        ]);

        return back()->with('success', 'Document uploaded.');
    }

    public function deleteDocument(Customer $customer, CustomerDocument $document): RedirectResponse
    {
        abort_if($document->customer_id !== $customer->id, 404);
        Storage::disk('s3')->delete($document->file_path);
        $document->delete();
        return back()->with('success', 'Document removed.');
    }
}
