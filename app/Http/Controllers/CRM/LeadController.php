<?php

namespace App\Http\Controllers\CRM;


use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerActivity;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;  

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['status','source','assigned_to','search']);

        $leads = Lead::with(['customer','assignedTo','branch'])
            ->when($filters['status']      ?? null, fn($q,$v) => $q->where('status',$v))
            ->when($filters['source']      ?? null, fn($q,$v) => $q->where('source',$v))
            ->when($filters['assigned_to'] ?? null, fn($q,$v) => $q->where('assigned_to',$v))
            ->when($filters['search']      ?? null, fn($q,$v) => $q->search($v))
            ->latest()->paginate(20)->withQueryString();

        // Pipeline counts
        $pipeline = collect(array_keys(Lead::STATUSES))->mapWithKeys(fn($s) =>
            [$s => Lead::where('status',$s)->count()]
        );

        $salesUsers = User::active()->get();
        $overdue    = Lead::overdueFollowUp()->count();

        return view('crm.leads.index', compact('leads','filters','pipeline','salesUsers','overdue'));
    }

    public function create(): View
    {
        $salesUsers = User::active()->get();
        $customers  = Customer::orderBy('full_name')->get(['id','full_name','mobile']);

        if (request()->ajax()) {
            return view('crm.leads.partials.form', compact('salesUsers', 'customers'));
        }

        return view('crm.leads.create', compact('salesUsers','customers'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'full_name'        => ['required','string','max:100'],
            'phone'            => ['required','string','max:20'],
            'email'            => ['nullable','email'],
            'source'           => ['required','string'],
            'vehicle_interest' => ['nullable','string','max:200'],
            'budget'           => ['nullable','numeric','min:0'],
            'assigned_to'      => ['nullable','exists:users,id'],
            'next_follow_up'   => ['nullable','date'],
            'notes'            => ['nullable','string'],
            'customer_id'      => ['nullable','exists:customers,id'],
        ]);

        $data['tenant_id'] = app('tenant')->id;
        $data['branch_id'] = auth()->user()->branch_id;
        $data['created_by']= auth()->id();
        $data['status']    = 'new';

        $lead = Lead::create($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Lead created successfully.']);
        }

        return redirect()->route('leads.show', $lead)
            ->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead): View
    {
        $lead->load(['customer','assignedTo','createdBy','activities.createdBy','branch']);

        if (request()->ajax()) {
            return view('crm.leads.partials.show-modal', compact('lead'));
        }

        $salesUsers = User::active()->get();
        $customers  = Customer::orderBy('full_name')->get(['id','full_name','mobile']);
        return view('crm.leads.show', compact('lead','salesUsers','customers'));
    }

    public function edit(Lead $lead): View
    {
        $salesUsers = User::active()->get();
        $customers  = Customer::orderBy('full_name')->get(['id','full_name','mobile']);

        if (request()->ajax()) {
            return view('crm.leads.partials.form', compact('lead', 'salesUsers', 'customers'));
        }

        return view('crm.leads.edit', compact('lead','salesUsers','customers'));
    }

    public function update(Request $request, Lead $lead): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'full_name'        => ['required','string','max:100'],
            'phone'            => ['required','string','max:20'],
            'email'            => ['nullable','email'],
            'source'           => ['required','string'],
            'vehicle_interest' => ['nullable','string','max:200'],
            'budget'           => ['nullable','numeric','min:0'],
            'assigned_to'      => ['nullable','exists:users,id'],
            'next_follow_up'   => ['nullable','date'],
            'notes'            => ['nullable','string'],
            'customer_id'      => ['nullable','exists:customers,id'],
        ]);

        $lead->update($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Lead updated.']);
        }

        return redirect()->route('leads.show', $lead)->with('success', 'Lead updated.');
    }

    // public function updateStatus(Request $request, Lead $lead): RedirectResponse
    // {
    //     $request->validate([
    //         'status'      => ['required','in:new,contacted,qualified,negotiation,won,lost'],
    //         'lost_reason' => ['required_if:status,lost','nullable','string','max:255'],
    //     ]);

    //     $lead->update([
    //         'status'       => $request->status,
    //         'lost_reason'  => $request->lost_reason,
    //         'converted_at' => $request->status === 'won' ? now() : $lead->converted_at,
    //     ]);

    //     return back()->with('success', 'Lead status updated to ' . $request->status . '.');
    // }


    public function updateStatus(Request $request, Lead $lead): RedirectResponse|JsonResponse
    {
        $request->validate([
            'status'      => ['required','in:new,contacted,qualified,negotiation,won,lost'],
            'lost_reason' => ['required_if:status,lost','nullable','string','max:255'],
        ]);

        $lead->update([
            'status'       => $request->status,
            'lost_reason'  => $request->lost_reason,
            'converted_at' => $request->status === 'won' ? now() : $lead->converted_at,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Lead status updated to ' . $request->status . '.']);
        }

        return back()->with('success', 'Lead status updated to ' . $request->status . '.');
    }

      public function assign(Request $request, Lead $lead): RedirectResponse
        {
            $request->validate([
                'assigned_to' => ['nullable', 'exists:users,id'],
            ]);

            $lead->update([
                'assigned_to' => $request->assigned_to ?: null,
            ]);

            return back()->with('success', 'Lead assignment updated.');
        }

    public function scheduleFollowUp(Request $request, Lead $lead): RedirectResponse
    {
        $request->validate([
            'next_follow_up' => ['required','date','after:now'],
            'note'           => ['nullable','string','max:500'],
        ]);

        $lead->update(['next_follow_up' => $request->next_follow_up]);

        if ($request->note) {
            CustomerActivity::create([
                'tenant_id'   => app('tenant')->id,
                'customer_id' => $lead->customer_id,
                'lead_id'     => $lead->id,
                'type'        => 'task',
                'subject'     => 'Follow-up scheduled',
                'description' => $request->note,
                'scheduled_at'=> $request->next_follow_up,
                'created_by'  => auth()->id(),
            ]);
        }

        return back()->with('success', 'Follow-up scheduled.');
    }

    public function convertToCustomer(Request $request, Lead $lead): RedirectResponse
    {
    if ($lead->customer_id) {
        return redirect()->route('customers.show', $lead->customer_id)
            ->with('info', 'Lead is already linked to a customer.');
    }

    $customer = DB::transaction(function () use ($lead) {

        $customer = Customer::create([
            'tenant_id'   => app('tenant')->id,
            'branch_id'   => $lead->branch_id,
            'full_name'   => $lead->full_name,
            'mobile'      => $lead->phone,
            'email'       => $lead->email,
            'source'      => $lead->source,
            'assigned_to' => $lead->assigned_to,
            'created_by'  => auth()->id(),
        ]);

        $lead->update([
            'customer_id'  => $customer->id,
            'status'       => 'qualified',
            'converted_at' => now(),
        ]);

        CustomerActivity::where('lead_id', $lead->id)
        ->whereNull('customer_id')
        ->update([
            'customer_id' => $customer->id,
     ]);

        return $customer;
    });

    return redirect()->route('customers.show', $customer)
        ->with('success', 'Lead converted to customer.');
}

    public function destroy(Lead $lead): RedirectResponse|JsonResponse
    {
        $this->authorize('delete-leads');
        $lead->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Lead deleted.']);
        }

        return redirect()->route('leads.index')->with('success', 'Lead deleted.');
    }


    public function storeActivity(Request $request, Lead $lead): RedirectResponse
{
    $data = $request->validate([
        'type' => ['required', 'in:call,meeting,whatsapp,email,note,task'],
        'subject' => ['nullable', 'string', 'max:255'],
        'description' => ['nullable', 'string', 'max:2000'],
    ]);

    CustomerActivity::create([
        'tenant_id' => app('tenant')->id,
        'customer_id' => $lead->customer_id,
        'lead_id' => $lead->id,
        'type' => $data['type'],
        'subject' => $data['subject'] ?? null,
        'description' => $data['description'] ?? null,
        'created_by' => auth()->id(),
    ]);

    return back()->with('success', 'Activity logged successfully.');
}

}
