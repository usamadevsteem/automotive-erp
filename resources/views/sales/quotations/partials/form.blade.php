<form id="quotationForm" action="{{ route('quotations.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label small fw-semibold required">Customer</label>
            <select name="customer_id" class="form-select" required>
                <option value="">Select Customer</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->full_name }} — {{ $c->mobile }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold required">Vehicle</label>
            <select name="vehicle_id" class="form-select vf-quote-vehicle" required>
                <option value="">Select Vehicle</option>
                @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" data-price="{{ $v->sale_price }}">
                        {{ $v->stock_number }} — {{ $v->make->name }} {{ $v->vehicleModel->name }} {{ $v->year }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold required">Sale Price (PKR)</label>
            <input type="number" name="sale_price" class="form-control vf-quote-price" required min="0">
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Discount (PKR)</label>
            <input type="number" name="discount" value="0" class="form-control vf-quote-discount" min="0">
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Net Price (PKR)</label>
            <input type="text" class="form-control bg-light fw-bold vf-quote-net" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold required">Valid Until</label>
            <input type="date" name="valid_until" value="{{ now()->addDays(7)->toDateString() }}"
                   class="form-control" required min="{{ today()->addDay()->toDateString() }}">
        </div>
        <div class="col-12">
            <label class="form-label small fw-semibold">Notes</label>
            <textarea name="notes" rows="2" class="form-control"></textarea>
        </div>
    </div>
</form>
