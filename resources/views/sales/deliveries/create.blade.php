@extends('layouts.app')
@section('title','Create Delivery Order')
@section('breadcrumb','Sales / Deliveries / New')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Create Delivery Order</h4>
    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<div class="row g-4"><div class="col-lg-8">
<form method="POST" action="{{ route('deliveries.store') }}">
    @csrf
    <input type="hidden" name="sale_invoice_id" value="{{ $invoice->id }}">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-semibold">Delivery Details</h6>
            <small class="text-muted">{{ $invoice->invoice_number }} — {{ $invoice->customer->full_name }} — {{ $invoice->vehicle->make->name }} {{ $invoice->vehicle->vehicleModel->name }}</small>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold required">Delivery Date</label>
                    <input type="date" name="delivery_date" value="{{ today()->toDateString() }}" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Vehicle Condition Notes</label>
                    <textarea name="condition_notes" rows="2" class="form-control" placeholder="Note any scratches, dents, or fuel level at delivery..."></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Accessories Handed Over</label>
                    <div class="row g-2">
                        @foreach(['Spare Key','Toolkit','Floor Mats','Owner Manual','Service Book','Jack & Tools'] as $item)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="accessories_list[]" value="{{ $item }}" id="acc_{{ $loop->index }}">
                                <label class="form-check-label small" for="acc_{{ $loop->index }}">{{ $item }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Notes</label>
                    <textarea name="notes" rows="2" class="form-control"></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white d-flex gap-2">
            <button type="submit" class="btn btn-primary">Confirm Delivery & Mark Vehicle Delivered</button>
            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-light">Cancel</a>
        </div>
    </div>
</form>
</div></div>
@endsection
