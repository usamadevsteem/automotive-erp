@extends('layouts.app')
@section('title', $quotation->quotation_number)
@section('breadcrumb', 'Sales / Quotations / ' . $quotation->quotation_number)
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">{{ $quotation->quotation_number }}</h4>
        <small class="text-muted">Created {{ $quotation->created_at->format('d M Y') }} by {{ $quotation->createdBy->name }}</small>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-{{ $quotation->status_color }}-subtle text-{{ $quotation->status_color }} border border-{{ $quotation->status_color }}-subtle px-3 py-2">{{ $quotation->status_label }}</span>
        @if($quotation->status === 'accepted' && !$quotation->booking)
        <a href="{{ route('bookings.create', ['quotation_id' => $quotation->id]) }}" class="btn btn-success btn-sm">
            <i class="bi bi-calendar-check me-1"></i> Create Booking
        </a>
        @endif
        <a href="{{ route('quotations.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
    </div>
</div>
<div class="row g-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted small text-uppercase mb-2">Customer</h6>
                        <div class="fw-semibold">{{ $quotation->customer->full_name }}</div>
                        <div class="text-muted small">{{ $quotation->customer->mobile }}</div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small text-uppercase mb-2">Vehicle</h6>
                        <div class="fw-semibold">{{ $quotation->vehicle->make->name }} {{ $quotation->vehicle->vehicleModel->name }}</div>
                        <div class="text-muted small">{{ $quotation->vehicle->year }} · {{ $quotation->vehicle->stock_number }}</div>
                    </div>
                </div>
                <table class="table table-sm">
                    <tr><td class="text-muted">Sale Price</td><td class="text-end">PKR {{ number_format($quotation->sale_price) }}</td></tr>
                    <tr><td class="text-muted">Discount</td><td class="text-end text-danger">- PKR {{ number_format($quotation->discount) }}</td></tr>
                    <tr class="table-light fw-bold"><td>Net Price</td><td class="text-end text-primary">PKR {{ number_format($quotation->net_price) }}</td></tr>
                    <tr><td class="text-muted">Valid Until</td><td class="text-end {{ $quotation->valid_until->isPast() ? 'text-danger' : '' }}">{{ $quotation->valid_until->format('d M Y') }}</td></tr>
                </table>
                @if($quotation->notes)
                <div class="alert alert-light border py-2 small mt-2"><i class="bi bi-sticky me-1"></i>{{ $quotation->notes }}</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-semibold">Update Status</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('quotations.status', $quotation) }}">
                    @csrf @method('PATCH')
                    <select name="status" class="form-select form-select-sm mb-2">
                        @foreach(\App\Models\Quotation::STATUSES as $k => $meta)
                            <option value="{{ $k }}" {{ $quotation->status === $k ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Update</button>
                </form>
                <hr>
                <div class="d-grid gap-2">
                    <form method="POST" action="{{ route('documents.generate') }}">
                        @csrf
                        <input type="hidden" name="document_type" value="proforma_invoice">
                        <input type="hidden" name="reference_type" value="quotation">
                        <input type="hidden" name="reference_id" value="{{ $quotation->id }}">
                        <input type="hidden" name="customer_id" value="{{ $quotation->customer_id }}">
                        <input type="hidden" name="vehicle_id" value="{{ $quotation->vehicle_id }}">
                        <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bi bi-file-pdf me-1"></i> Generate Proforma
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
