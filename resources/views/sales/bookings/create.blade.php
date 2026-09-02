@extends('layouts.app')
@section('title','New Booking')
@section('breadcrumb','Sales / Bookings / New')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">New Booking</h4> 
    <a href="{{ route('bookings.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<div class="row g-4"><div class="col-lg-8">
    <form method="POST" action="{{ route('bookings.store') }}">
    @csrf
    @if(request('quotation_id'))
        <input type="hidden" name="quotation_id" value="{{ request('quotation_id') }}">
    @endif
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3"><h6 class="mb-0 fw-semibold">Booking Details</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold required">Customer</label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">Select Customer</option>
                        @foreach($customers as $c)
                          <option value="{{ $c->id }}" {{ old('customer_id', $preselectedCustomerId) == $c->id ? 'selected' : '' }}>{{ $c->full_name }} — {{ $c->mobile }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold required">Vehicle</label>
                    <select name="vehicle_id" id="vehicleSelect" class="form-select" required>
                        <option value="">Select Vehicle</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}" data-price="{{ $v->sale_price }}"
                                {{ old('vehicle_id', $preselectedVehicleId) == $v->id ? 'selected' : '' }}>
                                {{ $v->stock_number }} — {{ $v->make->name }} {{ $v->vehicleModel->name }} {{ $v->year }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold required">Agreed Sale Price</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">PKR</span>
                       <input type="number" name="agreed_sale_price" id="agreedPrice"
                        value="{{ old('agreed_sale_price', $preselectedSalePrice) }} class="form-control" required min="0">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold required">Booking Advance (PKR)</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">PKR</span>
                        <input type="number" name="booking_amount" value="{{ old('booking_amount') }}"
                               class="form-control" required min="1">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Expected Delivery</label>
                    <input type="date" name="expected_delivery_date"
                           value="{{ old('expected_delivery_date') }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold required">Payment Method</label>
                    <select name="payment_method" class="form-select form-select-sm" required>
                        @foreach(\App\Models\Booking::PAYMENT_METHODS as $k => $v)
                            <option value="{{ $k }}" {{ old('payment_method','cash') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Payment Reference</label>
                    <input type="text" name="payment_reference" value="{{ old('payment_reference') }}" class="form-control form-control-sm" placeholder="Cheque # / Transaction ID">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Notes</label>
                    <textarea name="notes" rows="2" class="form-control form-control-sm">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex gap-2">
            <button type="submit" class="btn btn-primary">Create Booking & Reserve Vehicle</button>
            <a href="{{ route('bookings.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </div>
</form>
</div></div>
@endsection
@push('scripts')
<script>
document.getElementById('vehicleSelect').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (opt.dataset.price) document.getElementById('agreedPrice').value = opt.dataset.price;
});
</script>
@endpush
