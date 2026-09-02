@extends('layouts.app')
@section('title','New Sale Invoice')
@section('breadcrumb','Sales / Invoices / New')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">New Sale Invoice</h4>
    <a href="{{ route('invoices.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>

<form method="POST" action="{{ route('invoices.store') }}" id="invoiceForm">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-receipt text-primary me-2"></i>Invoice Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold required">Customer</label>
                            <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}"
                                        data-tax="{{ $c->tax_status }}"
                                        {{ old('customer_id', $preBooking?->customer_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->full_name }} — {{ $c->mobile }}
                                        @if($c->tax_status === 'filer') ✓ Filer @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold required">Vehicle</label>
                            <select name="vehicle_id" id="vehicleSelect" class="form-select @error('vehicle_id') is-invalid @enderror" required>
                                <option value="">Select Vehicle</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}"
                                        data-price="{{ $v->sale_price }}"
                                        data-cost="{{ $v->total_cost }}"
                                        {{ old('vehicle_id', $preBooking?->vehicle_id ?? $preVehicle?->id) == $v->id ? 'selected' : '' }}>
                                        {{ $v->stock_number }} — {{ $v->make->name }} {{ $v->vehicleModel->name }} {{ $v->year }}
                                    </option>
                                @endforeach
                            </select>
                            @error('vehicle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @if($bookings->count())
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Link to Booking (optional)</label>
                            <select name="booking_id" class="form-select form-select-sm">
                                <option value="">No booking</option>
                                @foreach($bookings as $b)
                                    <option value="{{ $b->id }}"
                                        data-price="{{ $b->agreed_sale_price }}"
                                        data-paid="{{ $b->booking_amount }}"
                                        {{ old('booking_id', $preBooking?->id) == $b->id ? 'selected' : '' }}>
                                        {{ $b->booking_number }} — {{ $b->customer->full_name }} (Advance: PKR {{ number_format($b->booking_amount) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold required">Invoice Date</label>
                            <input type="date" name="invoice_date" value="{{ old('invoice_date', today()->toDateString()) }}"
                                   class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold required">Payment Type</label>
                            <select name="payment_type" id="paymentType" class="form-select form-select-sm" required>
                                @foreach(\App\Models\SaleInvoice::PAYMENT_TYPES as $k => $v)
                                    <option value="{{ $k }}" {{ old('payment_type','cash') === $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Notes</label>
                            <input type="text" name="notes" value="{{ old('notes') }}" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pricing panel --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top:80px;">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-calculator text-primary me-2"></i>Pricing (PKR)</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold required">Sale Price</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">PKR</span>
                            <input type="number" name="sale_price" id="salePrice"
                                   value="{{ old('sale_price', $preBooking?->agreed_sale_price) }}"
                                   class="form-control text-end" required min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Discount</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">PKR</span>
                            <input type="number" name="discount" id="discount" value="{{ old('discount',0) }}"
                                   class="form-control text-end" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Withholding Tax</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">PKR</span>
                            <input type="number" name="withholding_tax" id="withholdingTax" value="{{ old('withholding_tax',0) }}"
                                   class="form-control text-end" min="0">
                        </div>
                        <div class="form-text">Filer: 1% | Non-Filer: 2% of sale price</div>
                    </div>
                    <div class="bg-primary-subtle rounded p-3 mb-3">
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Net Amount</span>
                            <span id="netAmountDisplay">PKR 0</span>
                        </div>
                    </div>
                    <input type="hidden" name="net_amount" id="netAmount" value="{{ old('net_amount',0) }}">
                    <div class="mb-3" id="amountPaidBox">
                        <label class="form-label small fw-semibold">Amount Paid Now</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">PKR</span>
                            <input type="number" name="amount_paid" id="amountPaid"
                                   value="{{ old('amount_paid', $preBooking?->booking_amount ?? 0) }}"
                                   class="form-control text-end" min="0">
                        </div>
                    </div>
                    <div class="bg-danger-subtle rounded p-2 mb-3">
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">Balance Due</span>
                            <span class="fw-bold text-danger" id="balanceDueDisplay">PKR 0</span>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-receipt me-1"></i> Create Invoice
                        </button>
                        <a href="{{ route('invoices.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function recalc() {
    const sale   = parseFloat(document.getElementById('salePrice').value)    || 0;
    const disc   = parseFloat(document.getElementById('discount').value)      || 0;
    const wht    = parseFloat(document.getElementById('withholdingTax').value)|| 0;
    const paid   = parseFloat(document.getElementById('amountPaid').value)    || 0;
    const net    = sale - disc - wht;
    const balance= Math.max(0, net - paid);

    document.getElementById('netAmount').value          = net.toFixed(2);
    document.getElementById('netAmountDisplay').textContent = 'PKR ' + Math.round(net).toLocaleString('en-PK');
    document.getElementById('balanceDueDisplay').textContent= 'PKR ' + Math.round(balance).toLocaleString('en-PK');
}

['salePrice','discount','withholdingTax','amountPaid'].forEach(id =>
    document.getElementById(id)?.addEventListener('input', recalc)
);

document.getElementById('vehicleSelect')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (opt.dataset.price) {
        document.getElementById('salePrice').value = opt.dataset.price;
        recalc();
    }
});

document.getElementById('paymentType')?.addEventListener('change', function() {
   document.getElementById('amountPaidBox').style.display = '';
});

recalc();
</script>
@endpush
