@extends('layouts.app')
@section('title','New Quotation')
@section('breadcrumb','Sales / Quotations / New')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">New Quotation</h4>
    <a href="{{ route('quotations.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<div class="row g-4">
<div class="col-lg-8">
<form method="POST" action="{{ route('quotations.store') }}">
    @csrf

    @if(request('lead_id'))
    <input type="hidden" name="lead_id" value="{{ request('lead_id') }}">
        @endif
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold required">Customer</label>
                    <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                        <option value="">Select Customer</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}"
                                {{ old('customer_id', request('customer_id')) == $c->id ? 'selected' : '' }}>
                                {{ $c->full_name }} — {{ $c->mobile }}
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
                            <option value="{{ $v->id }}" data-price="{{ $v->sale_price }}" {{ old('vehicle_id') == $v->id ? 'selected' : '' }}>
                                {{ $v->stock_number }} — {{ $v->make->name }} {{ $v->vehicleModel->name }} {{ $v->year }}
                            </option>
                        @endforeach
                    </select>
                    @error('vehicle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold required">Sale Price (PKR)</label>
                    <input type="number" name="sale_price" id="salePrice" value="{{ old('sale_price') }}"
                           class="form-control @error('sale_price') is-invalid @enderror" required min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Discount (PKR)</label>
                    <input type="number" name="discount" id="discount" value="{{ old('discount',0) }}"
                           class="form-control" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Net Price (PKR)</label>
                    <input type="text" id="netPriceDisplay" class="form-control bg-light fw-bold" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold required">Valid Until</label>
                    <input type="date" name="valid_until" value="{{ old('valid_until', now()->addDays(7)->toDateString()) }}"
                           class="form-control @error('valid_until') is-invalid @enderror" required min="{{ today()->addDay()->toDateString() }}">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Notes</label>
                    <textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex gap-2">
            <button type="submit" class="btn btn-primary">Create Quotation</button>
            <a href="{{ route('quotations.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </div>
</form>
</div>
</div>
@endsection
@push('scripts')
<script>
function updateNet() {
    const price = parseFloat(document.getElementById('salePrice').value) || 0;
    const disc  = parseFloat(document.getElementById('discount').value) || 0;
    document.getElementById('netPriceDisplay').value = 'PKR ' + (price - disc).toLocaleString('en-PK');
}
document.getElementById('salePrice').addEventListener('input', updateNet);
document.getElementById('discount').addEventListener('input', updateNet);
document.getElementById('vehicleSelect').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    document.getElementById('salePrice').value = opt.dataset.price || '';
    updateNet();
});
updateNet();
</script>
@endpush
