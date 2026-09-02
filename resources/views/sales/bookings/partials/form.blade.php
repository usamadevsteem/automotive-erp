<form id="bookingForm" action="{{ route('bookings.store') }}">
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
            <select name="vehicle_id" class="form-select vf-booking-vehicle" required>
                <option value="">Select Vehicle</option>
                @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" data-price="{{ $v->sale_price }}"
                        {{ $preselectedVehicleId == $v->id ? 'selected' : '' }}>
                        {{ $v->stock_number }} — {{ $v->make->name }} {{ $v->vehicleModel->name }} {{ $v->year }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold required">Agreed Sale Price</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text">PKR</span>
                <input type="number" name="agreed_sale_price" class="form-control vf-booking-price" required min="0">
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold required">Booking Advance (PKR)</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text">PKR</span>
                <input type="number" name="booking_amount" class="form-control" required min="1">
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Expected Delivery</label>
            <input type="date" name="expected_delivery_date" class="form-control form-control-sm">
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold required">Payment Method</label>
            <select name="payment_method" class="form-select form-select-sm" required>
                @foreach(\App\Models\Booking::PAYMENT_METHODS as $k => $v)
                    <option value="{{ $k }}" {{ $k === 'cash' ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Payment Reference</label>
            <input type="text" name="payment_reference" class="form-control form-control-sm" placeholder="Cheque # / Transaction ID">
        </div>
        <div class="col-12">
            <label class="form-label small fw-semibold">Notes</label>
            <textarea name="notes" rows="2" class="form-control form-control-sm"></textarea>
        </div>
    </div>
</form>
