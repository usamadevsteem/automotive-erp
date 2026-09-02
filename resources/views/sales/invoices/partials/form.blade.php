<form id="invoiceForm" action="{{ route('invoices.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label small fw-semibold required">Customer</label>
            <select name="customer_id" class="form-select" required>
                <option value="">Select Customer</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">
                        {{ $c->full_name }} — {{ $c->mobile }}
                        @if($c->tax_status === 'filer') ✓ Filer @endif
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold required">Vehicle</label>
            <select name="vehicle_id" class="form-select vf-inv-vehicle" required>
                <option value="">Select Vehicle</option>
                @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" data-price="{{ $v->sale_price }}">
                        {{ $v->stock_number }} — {{ $v->make->name }} {{ $v->vehicleModel->name }} {{ $v->year }}
                    </option>
                @endforeach
            </select>
        </div>

        @if($bookings->count())
        <div class="col-12">
            <label class="form-label small fw-semibold">Link to Booking (optional)</label>
            <select name="booking_id" class="form-select form-select-sm">
                <option value="">No booking</option>
                @foreach($bookings as $b)
                    <option value="{{ $b->id }}">
                        {{ $b->booking_number }} — {{ $b->customer->full_name }} (Advance: PKR {{ number_format($b->booking_amount) }})
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="col-md-4">
            <label class="form-label small fw-semibold required">Invoice Date</label>
            <input type="date" name="invoice_date" value="{{ today()->toDateString() }}" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold required">Payment Type</label>
            <select name="payment_type" class="form-select form-select-sm vf-inv-paytype" required>
                @foreach(\App\Models\SaleInvoice::PAYMENT_TYPES as $k => $v)
                    <option value="{{ $k }}" {{ $k === 'cash' ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Notes</label>
            <input type="text" name="notes" class="form-control form-control-sm">
        </div>

        <div class="col-12"><hr class="my-1"></div>

        <div class="col-md-3">
            <label class="form-label small fw-semibold required">Sale Price</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text">PKR</span>
                <input type="number" name="sale_price" class="form-control text-end vf-inv-sale" required min="0">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Discount</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text">PKR</span>
                <input type="number" name="discount" value="0" class="form-control text-end vf-inv-discount" min="0">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Withholding Tax</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text">PKR</span>
                <input type="number" name="withholding_tax" value="0" class="form-control text-end vf-inv-wht" min="0">
            </div>
        </div>
        <div class="col-md-3 vf-inv-paid-box">
            <label class="form-label small fw-semibold">Amount Paid Now</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text">PKR</span>
                <input type="number" name="amount_paid" value="0" class="form-control text-end vf-inv-paid" min="0">
            </div>
        </div>

        <div class="col-12">
            <div class="bg-primary-subtle rounded p-3 d-flex justify-content-between fw-bold">
                <span>Net Amount</span>
                <span class="vf-inv-net-display">PKR 0</span>
            </div>
            <input type="hidden" name="net_amount" class="vf-inv-net" value="0">
        </div>
        <div class="col-12">
            <div class="bg-danger-subtle rounded p-2 d-flex justify-content-between small">
                <span class="text-muted">Balance Due</span>
                <span class="fw-bold text-danger vf-inv-balance-display">PKR 0</span>
            </div>
        </div>
    </div>
</form>
