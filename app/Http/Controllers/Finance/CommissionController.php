<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Services\CommissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommissionController extends Controller
{
    public function __construct(private readonly CommissionService $commissionService) {}

    public function index(Request $request): View
    {
        $commissions = Commission::with(['employee','saleInvoice.customer','vehicle.make'])
            ->when($request->status, fn($q,$v) => $q->where('status',$v))
            ->when($request->employee_id, fn($q,$v) => $q->where('employee_id',$v))
            ->latest()->paginate(20)->withQueryString();

        $totalPending  = Commission::where('status','pending')->sum('commission_amount');
        $totalApproved = Commission::where('status','approved')->sum('commission_amount');

        return view('commissions.index', compact('commissions','totalPending','totalApproved'));
    }

    public function approve(Commission $commission): RedirectResponse
    {
        $this->authorize('approve-commissions');
        $this->commissionService->approve($commission);
        return back()->with('success', 'Commission approved.');
    }

    public function pay(Request $request, Commission $commission): RedirectResponse
    {
        $this->authorize('pay-commissions');
        $data = $request->validate([
            'payment_method' => ['required','in:cash,bank_transfer,cheque'],
            'payment_date'   => ['required','date'],
            'notes'          => ['nullable','string'],
        ]);
        $data['branch_id'] = auth()->user()->branch_id;
        $this->commissionService->pay($commission, $data);
        return back()->with('success', "Commission of {$commission->amount_formatted} paid.");
    }

    // ── Commission Rules ───────────────────────────────────────────

    public function rules(): View
    {
        $rules = CommissionRule::with('createdBy')->get();
        return view('commissions.rules', compact('rules'));
    }

    public function storeRule(Request $request): RedirectResponse
    {
        $this->authorize('manage-commission-rules');
        $data = $request->validate([
            'name'          => ['required','string','max:100'],
            'applies_to'    => ['required','in:salesman,manager,branch'],
            'calc_type'     => ['required','in:fixed,percentage_profit,percentage_sale'],
            'value'         => ['required','numeric','min:0'],
            'min_sale_price'=> ['nullable','numeric','min:0'],
        ]);

        $data['created_by'] = auth()->id();
        CommissionRule::create($data);
        return back()->with('success', 'Commission rule created.');
    }

    public function toggleRule(CommissionRule $rule): RedirectResponse
    {
        $this->authorize('manage-commission-rules');
        $rule->update(['is_active' => !$rule->is_active]);
        return back()->with('success', 'Rule status updated.');
    }
}
