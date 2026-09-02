<div class="row g-3">
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Invoice #</small>
        <div class="fw-semibold">{{ $invoice->invoice_number }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Status</small>
        <span class="badge bg-{{ $invoice->status_color }}-subtle text-{{ $invoice->status_color }}">
            {{ $invoice->status_label }}
        </span>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Customer</small>
        <div>{{ $invoice->customer->full_name }} — {{ $invoice->customer->mobile }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Vehicle</small>
        <div>{{ $invoice->vehicle->make->name }} {{ $invoice->vehicle->vehicleModel->name }} {{ $invoice->vehicle->year }}</div>
    </div>
    <div class="col-md-4">
        <small class="text-muted d-block mb-1">Net Amount</small>
        <div class="fw-semibold">PKR {{ number_format($invoice->net_amount) }}</div>
    </div>
    <div class="col-md-4">
        <small class="text-muted d-block mb-1">Amount Paid</small>
        <div class="text-success">PKR {{ number_format($invoice->amount_paid) }}</div>
    </div>
    <div class="col-md-4">
        <small class="text-muted d-block mb-1">Balance Due</small>
        <div class="{{ $invoice->balance_due > 0 ? 'text-danger fw-semibold' : 'text-success' }}">
            PKR {{ number_format($invoice->balance_due) }}
        </div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Invoice Date</small>
        <div>{{ $invoice->invoice_date->format('d M Y') }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Payment Type</small>
        <div>{{ \App\Models\SaleInvoice::PAYMENT_TYPES[$invoice->payment_type] ?? $invoice->payment_type }}</div>
    </div>
</div>

@if($invoice->balance_due > 0 && $invoice->status !== 'cancelled')
<hr>
<h6 class="small fw-semibold text-muted text-uppercase mb-2">Record Payment</h6>
<form id="invoicePaymentForm" data-url="{{ route('invoices.payment', $invoice) }}">
    @csrf
    <div class="row g-2">
        <div class="col-md-4">
            <label class="form-label small">Amount (max PKR {{ number_format($invoice->balance_due) }})</label>
            <input type="number" name="amount" class="form-control form-control-sm"
                   max="{{ $invoice->balance_due }}" min="1" required>
        </div>
        <div class="col-md-4">
            <label class="form-label small">Method</label>
            <select name="payment_method" class="form-select form-select-sm" required>
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="cheque">Cheque</option>
                <option value="online">Online</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small">Date</label>
            <input type="date" name="payment_date" class="form-control form-control-sm"
                   value="{{ today()->toDateString() }}" required>
        </div>
        <div class="col-12">
            <button type="button" class="btn btn-success btn-sm mt-1" onclick="submitInvoicePayment({{ $invoice->id }})">
                <i class="bi bi-cash-coin me-1"></i> Record Payment
            </button>
        </div>
    </div>
</form>
<div class="alert alert-danger py-2 px-3 mt-2 mb-0 d-none" id="invoicePaymentError"></div>
@endif

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-up-right-square me-1"></i> Open Full Page
    </a>
</div>
