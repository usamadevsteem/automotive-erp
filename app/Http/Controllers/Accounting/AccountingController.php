<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vendor;
use App\Services\AccountingService;
use App\Services\NumberingService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    public function __construct(
        private readonly AccountingService $accountingService,
        private readonly NumberingService  $numberingService,
        private readonly PaymentService    $paymentService,
    ) {}

    // ── Chart of Accounts ─────────────────────────────────────────

    public function accountIndex(): View
    {
        $accounts = Account::with('children')
            ->whereNull('parent_id')
            ->orderBy('account_code')
            ->get();
        return view('accounting.accounts.index', compact('accounts'));
    }

    public function accountCreate(): View
    {
        $parents = Account::active()->orderBy('account_code')->get();

        if (request()->ajax()) {
            return view('accounting.accounts.partials.form', compact('parents'));
        }

        return view('accounting.accounts.create', compact('parents'));
    }

    public function accountStore(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'account_code' => [
                'required','string','max:20',
                \Illuminate\Validation\Rule::unique('accounts', 'account_code')
                    ->where('tenant_id', app('tenant')->id),
            ],
            'account_name' => ['required','string','max:150'],
            'account_type' => ['required','in:asset,liability,equity,revenue,expense'],
            'account_subtype' => ['nullable','string','max:50'],
            'parent_id'    => ['nullable','exists:accounts,id'],
            'description'  => ['nullable','string','max:255'],
        ]);

        $data['is_system'] = false;
        Account::create($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Account created.']);
        }

        return redirect()->route('accounts.index')->with('success', 'Account created.');
    }

    public function accountEdit(Account $account): View
    {
        $this->authorize('manage-accounts');
        $parents = Account::active()->where('id', '!=', $account->id)->orderBy('account_code')->get();

        if (request()->ajax()) {
            return view('accounting.accounts.partials.form', compact('account', 'parents'));
        }

        return view('accounting.accounts.edit', compact('account', 'parents'));
    }

    public function accountUpdate(Request $request, Account $account): RedirectResponse|JsonResponse
    {
        $this->authorize('manage-accounts');

        $data = $request->validate([
            'account_code' => [
                'required','string','max:20',
                \Illuminate\Validation\Rule::unique('accounts', 'account_code')
                    ->where('tenant_id', app('tenant')->id)
                    ->ignore($account->id),
            ],
            'account_name'    => ['required','string','max:150'],
            'account_type'    => ['required','in:asset,liability,equity,revenue,expense'],
            'account_subtype' => ['nullable','string','max:50'],
            'parent_id'       => ['nullable','exists:accounts,id', Rule::notIn([$account->id])],
            'description'     => ['nullable','string','max:255'],
            'is_active'       => ['nullable','boolean'],
        ]);

        $data['is_active'] = $data['is_active'] ?? false;
        $account->update($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Account updated.']);
        }

        return redirect()->route('accounts.index')->with('success', 'Account updated.');
    }

    public function accountDestroy(Request $request, Account $account): RedirectResponse|JsonResponse
    {
        $this->authorize('manage-accounts');

        if ($account->is_system) {
            $message = 'This is a system account and cannot be deleted. You can deactivate it instead.';

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        if ($account->journalLines()->exists()) {
            $message = 'This account has journal entries posted against it and cannot be deleted. Deactivate it instead to hide it from new entries.';

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        if ($account->children()->exists()) {
            $message = 'This account has sub-accounts. Remove or reassign them first.';

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $account->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Account deleted.']);
        }

        return redirect()->route('accounts.index')->with('success', 'Account deleted.');
    }

    // ── Journal Entries ────────────────────────────────────────────

    public function journalIndex(Request $request): View
    {
        $entries = JournalEntry::with(['branch','createdBy'])
            ->when($request->from,  fn($q,$v) => $q->where('entry_date','>=',$v))
            ->when($request->to,    fn($q,$v) => $q->where('entry_date','<=',$v))
            ->when($request->type,  fn($q,$v) => $q->where('entry_type',$v))
            ->latest('entry_date')->paginate(20)->withQueryString();
        return view('accounting.journals.index', compact('entries'));
    }

    public function journalCreate(): View
    {
        $accounts = Account::active()->orderBy('account_code')->get();
        return view('accounting.journals.create', compact('accounts'));
    }

    public function journalStore(Request $request): RedirectResponse
    {
        $request->validate([
            'entry_date'     => ['required','date'],
            'narration'      => ['required','string','max:500'],
            'entry_type'     => ['required','string'],
            'lines'          => ['required','array','min:2'],
            'lines.*.account_id'    => ['required','exists:accounts,id'],
            'lines.*.debit_amount'  => ['nullable','numeric','min:0'],
            'lines.*.credit_amount' => ['nullable','numeric','min:0'],
            'lines.*.description'   => ['nullable','string','max:255'],
        ]);

        try {
            $lines = collect($request->lines)->map(fn($l) => [
                'account_id'  => $l['account_id'],
                'debit'       => (float)($l['debit_amount']  ?? 0),
                'credit'      => (float)($l['credit_amount'] ?? 0),
                'description' => $l['description'] ?? null,
            ])->toArray();

            $this->accountingService->postEntry([
                'branch_id'  => auth()->user()->branch_id,
                'entry_date' => $request->entry_date,
                'narration'  => $request->narration,
                'entry_type' => $request->entry_type,
                'is_auto'    => false,
            ], $lines);

            return redirect()->route('journal-entries.index')->with('success', 'Journal entry posted.');
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function journalShow(JournalEntry $journalEntry): View
    {
        $journalEntry->load(['lines.account','branch','createdBy']);
        return view('accounting.journals.show', compact('journalEntry'));
    }

    // ── Payments ───────────────────────────────────────────────────

    public function paymentIndex(Request $request): View
    {
        $payments = Payment::with(['branch','createdBy'])
            ->when($request->type,   fn($q,$v) => $q->where('type',$v))
            ->when($request->from,   fn($q,$v) => $q->where('payment_date','>=',$v))
            ->when($request->to,     fn($q,$v) => $q->where('payment_date','<=',$v))
            ->latest('payment_date')->paginate(20)->withQueryString();
        return view('accounting.payments.index', compact('payments'));
    }

    public function paymentCreate(): View
    {
        $customers = Customer::orderBy('full_name')->get(['id','full_name']);
        $vendors   = Vendor::orderBy('name')->get(['id','name']);
        $employees = User::active()->orderBy('name')->get(['id','name']);

        if (request()->ajax()) {
            return view('accounting.payments.partials.form', compact('customers','vendors','employees'));
        }

        return view('accounting.payments.create', compact('customers','vendors','employees'));
    }

    public function paymentStore(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'type'             => ['required','in:received,paid'],
            'party_type'       => ['required','in:customer,vendor,employee,other'],
            'party_id'         => ['required','integer'],
            'amount'           => ['required','numeric','min:0.01'],
            'payment_method'   => ['required','in:cash,cheque,bank_transfer,online'],
            'payment_date'     => ['required','date'],
            'cheque_number'    => ['nullable','string','max:50'],
            'cheque_date'      => ['nullable','date'],
            'bank_name'        => ['nullable','string','max:100'],
            'reference_number' => ['nullable','string','max:100'],
            'notes'            => ['nullable','string','max:255'],
        ]);

        $data['branch_id'] = auth()->user()->branch_id;
        $payment = $this->paymentService->record($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "Payment {$payment->payment_number} recorded."]);
        }

        return redirect()->route('payments.index')
            ->with('success', "Payment {$payment->payment_number} recorded.");
    }

    public function paymentEdit(Payment $payment): View
    {
        $this->authorize('create-payments');
        $customers = Customer::orderBy('full_name')->get(['id','full_name']);
        $vendors   = Vendor::orderBy('name')->get(['id','name']);
        $employees = User::active()->orderBy('name')->get(['id','name']);

        if (request()->ajax()) {
            return view('accounting.payments.partials.form', compact('payment','customers','vendors','employees'));
        }

        return view('accounting.payments.edit', compact('payment','customers','vendors','employees'));
    }

    public function paymentUpdate(Request $request, Payment $payment): RedirectResponse|JsonResponse
    {
        $this->authorize('create-payments');

        $data = $request->validate([
            'type'             => ['required','in:received,paid'],
            'party_type'       => ['required','in:customer,vendor,employee,other'],
            'party_id'         => ['required','integer'],
            'amount'           => ['required','numeric','min:0.01'],
            'payment_method'   => ['required','in:cash,cheque,bank_transfer,online'],
            'payment_date'     => ['required','date'],
            'cheque_number'    => ['nullable','string','max:50'],
            'cheque_date'      => ['nullable','date'],
            'bank_name'        => ['nullable','string','max:100'],
            'reference_number' => ['nullable','string','max:100'],
            'notes'            => ['nullable','string','max:255'],
        ]);

        $payment->update($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Payment updated.']);
        }

        return redirect()->route('payments.index')->with('success', 'Payment updated.');
    }

    public function paymentDestroy(Request $request, Payment $payment): RedirectResponse|JsonResponse
    {
        $this->authorize('create-payments');

        if ($payment->journal_entry_id) {
            $message = 'This payment has a linked journal entry and cannot be deleted.';

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $payment->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Payment deleted.']);
        }

        return redirect()->route('payments.index')->with('success', 'Payment deleted.');
    }

    // ── Vendors ────────────────────────────────────────────────────

    public function vendorIndex(): View
    {
        $vendors = Vendor::latest()->paginate(20);
        return view('accounting.vendors.index', compact('vendors'));
    }

    public function vendorCreate(): View
    {
        if (request()->ajax()) {
            return view('accounting.vendors.partials.form');
        }

        return view('accounting.vendors.create');
    }

    public function vendorStore(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'name'           => ['required','string','max:150'],
            'vendor_type'    => ['required','in:supplier,import_agent,parts_vendor,service_vendor'],
            'phone'          => ['nullable','string','max:20'],
            'email'          => ['nullable','email'],
            'address'        => ['nullable','string'],
            'city'           => ['nullable','string','max:100'],
            'ntn_number'     => ['nullable','string','max:20'],
            'bank_name'      => ['nullable','string','max:100'],
            'account_number' => ['nullable','string','max:50'],
            'opening_balance'=> ['nullable','numeric'],
            'notes'          => ['nullable','string'],
        ]);

        Vendor::create($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Vendor created.']);
        }

        return redirect()->route('vendors.index')->with('success', 'Vendor created.');
    }

    public function vendorEdit(Vendor $vendor): View
    {
        $this->authorize('manage-vendors');

        if (request()->ajax()) {
            return view('accounting.vendors.partials.form', compact('vendor'));
        }

        return view('accounting.vendors.edit', compact('vendor'));
    }

    public function vendorUpdate(Request $request, Vendor $vendor): RedirectResponse|JsonResponse
    {
        $this->authorize('manage-vendors');

        $data = $request->validate([
            'name'           => ['required','string','max:150'],
            'vendor_type'    => ['required','in:supplier,import_agent,parts_vendor,service_vendor'],
            'phone'          => ['nullable','string','max:20'],
            'email'          => ['nullable','email'],
            'address'        => ['nullable','string'],
            'city'           => ['nullable','string','max:100'],
            'ntn_number'     => ['nullable','string','max:20'],
            'bank_name'      => ['nullable','string','max:100'],
            'account_number' => ['nullable','string','max:50'],
            'opening_balance'=> ['nullable','numeric'],
            'is_active'      => ['nullable','boolean'],
            'notes'          => ['nullable','string'],
        ]);

        $data['is_active'] = $data['is_active'] ?? false;
        $vendor->update($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Vendor updated.']);
        }

        return redirect()->route('vendors.index')->with('success', 'Vendor updated.');
    }

    public function vendorDestroy(Request $request, Vendor $vendor): RedirectResponse|JsonResponse
    {
        $this->authorize('manage-vendors');

        if ($vendor->expenses()->exists()) {
            $message = 'This vendor has expenses recorded against it and cannot be deleted. Deactivate it instead.';

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $vendor->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Vendor deleted.']);
        }

        return redirect()->route('vendors.index')->with('success', 'Vendor deleted.');
    }

    // ── Expenses ───────────────────────────────────────────────────

    public function expenseIndex(Request $request): View
    {
        $expenses = Expense::with(['category','vendor','createdBy'])
            ->when($request->from,   fn($q,$v) => $q->where('expense_date','>=',$v))
            ->when($request->to,     fn($q,$v) => $q->where('expense_date','<=',$v))
            ->when($request->status, fn($q,$v) => $q->where('status',$v))
            ->latest('expense_date')->paginate(20)->withQueryString();

        $categories = ExpenseCategory::active()->get();
        return view('accounting.expenses.index', compact('expenses','categories'));
    }

    public function expenseCreate(): View
    {
        $categories = ExpenseCategory::active()->get();
        $vendors    = Vendor::active()->get();

        if (request()->ajax()) {
            return view('accounting.expenses.partials.form', compact('categories','vendors'));
        }

        return view('accounting.expenses.create', compact('categories','vendors'));
    }

    public function expenseStore(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'category_id'      => ['required','exists:expense_categories,id'],
            'description'      => ['required','string','max:255'],
            'amount'           => ['required','numeric','min:0.01'],
            'payment_method'   => ['required','in:cash,cheque,bank_transfer,online'],
            'vendor_id'        => ['nullable','exists:vendors,id'],
            'expense_date'     => ['required','date'],
            'reference_number' => ['nullable','string','max:100'],
        ]);

        $data['expense_number'] = $this->numberingService->expense();
        $data['branch_id']      = auth()->user()->branch_id;
        $data['created_by']     = auth()->id();
        $data['status']         = 'approved';

        $expense = Expense::create($data);

        // Post accounting entry
        $expense->load('category');
        $entry = $this->accountingService->postExpenseEntry($expense);
        $expense->update(['journal_entry_id' => $entry->id]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "Expense {$expense->expense_number} recorded."]);
        }

        return redirect()->route('expenses.index')->with('success', "Expense {$expense->expense_number} recorded.");
    }

    public function expenseEdit(Expense $expense): View
    {
        $this->authorize('create-expenses');
        $categories = ExpenseCategory::active()->get();
        $vendors    = Vendor::active()->get();

        if (request()->ajax()) {
            return view('accounting.expenses.partials.form', compact('expense','categories','vendors'));
        }

        return view('accounting.expenses.edit', compact('expense','categories','vendors'));
    }

    public function expenseUpdate(Request $request, Expense $expense): RedirectResponse|JsonResponse
    {
        $this->authorize('create-expenses');

        $data = $request->validate([
            'category_id'      => ['required','exists:expense_categories,id'],
            'description'      => ['required','string','max:255'],
            'amount'           => ['required','numeric','min:0.01'],
            'payment_method'   => ['required','in:cash,cheque,bank_transfer,online'],
            'vendor_id'        => ['nullable','exists:vendors,id'],
            'expense_date'     => ['required','date'],
            'reference_number' => ['nullable','string','max:100'],
        ]);

        $expense->update($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Expense updated.']);
        }

        return redirect()->route('expenses.index')->with('success', 'Expense updated.');
    }

    public function expenseDestroy(Request $request, Expense $expense): RedirectResponse|JsonResponse
    {
        $this->authorize('create-expenses');

        if ($expense->journal_entry_id) {
            $message = 'This expense has a linked journal entry and cannot be deleted.';

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $expense->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Expense deleted.']);
        }

        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }

    // ── Reports ────────────────────────────────────────────────────

    public function trialBalance(Request $request): View
    {
        $fromDate = $request->from ?? now()->startOfYear()->toDateString();
        $toDate   = $request->to   ?? now()->toDateString();

        $data = $this->accountingService->getTrialBalance($fromDate, $toDate);
        return view('accounting.reports.trial-balance', compact('data','fromDate','toDate'));
    }

    public function profitLoss(Request $request): View
    {
        $fromDate = $request->from ?? now()->startOfYear()->toDateString();
        $toDate   = $request->to   ?? now()->toDateString();

        $data = $this->accountingService->getProfitLoss($fromDate, $toDate);
        return view('accounting.reports.profit-loss', compact('data','fromDate','toDate'));
    }

    public function ledger(Request $request): View
    {
        $account = Account::findOrFail($request->account_id ?? Account::first()?->id);
        $fromDate= $request->from ?? now()->startOfMonth()->toDateString();
        $toDate  = $request->to   ?? now()->toDateString();

        $lines = $account->journalLines()
            ->with('journalEntry')
            ->whereHas('journalEntry', fn($q) =>
                $q->whereBetween('entry_date', [$fromDate, $toDate])
            )
            ->orderBy(\DB::raw('(SELECT entry_date FROM journal_entries WHERE journal_entries.id = journal_entry_id)'))
            ->get();

        $accounts = Account::active()->orderBy('account_code')->get();
        return view('accounting.reports.ledger', compact('account','lines','accounts','fromDate','toDate'));
    }
}
