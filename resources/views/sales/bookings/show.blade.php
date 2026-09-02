@extends('layouts.app')
@section('title', $booking->booking_number)
@section('breadcrumb', 'Sales / Bookings / ' . $booking->booking_number)
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">{{ $booking->booking_number }}</h4>
        <small class="text-muted">{{ $booking->created_at->format('d M Y') }}</small>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-{{ $booking->status_color }}-subtle text-{{ $booking->status_color }} border px-3 py-2">{{ $booking->status_label }}</span>
        @if($booking->status === 'active')
        <a href="{{ route('invoices.create', ['booking_id' => $booking->id]) }}" class="btn btn-success btn-sm">
            <i class="bi bi-receipt me-1"></i> Create Invoice
        </a>
        @endif
        <a href="{{ route('bookings.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
    </div>
</div>
<div class="row g-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted small text-uppercase mb-2">Customer</h6>
                        <div class="fw-semibold">{{ $booking->customer->full_name }}</div>
                        <div class="text-muted small">{{ $booking->customer->mobile }}</div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small text-uppercase mb-2">Vehicle</h6>
                        <div class="fw-semibold">{{ $booking->vehicle->make->name }} {{ $booking->vehicle->vehicleModel->name }}</div>
                        <div class="text-muted small">{{ $booking->vehicle->year }} · {{ $booking->vehicle->stock_number }}</div>
                    </div>
                </div>
                <table class="table table-sm">
                    <tr><td class="text-muted">Agreed Sale Price</td><td class="text-end fw-semibold">PKR {{ number_format($booking->agreed_sale_price) }}</td></tr>
                    <tr><td class="text-muted">Booking Advance</td><td class="text-end text-success fw-semibold">PKR {{ number_format($booking->booking_amount) }}</td></tr>
                    <tr class="table-light"><td>Balance Remaining</td><td class="text-end text-danger fw-bold">PKR {{ number_format($booking->agreed_sale_price - $booking->booking_amount) }}</td></tr>
                    <tr><td class="text-muted">Payment Method</td><td class="text-end">{{ \App\Models\Booking::PAYMENT_METHODS[$booking->payment_method] }}</td></tr>
                    @if($booking->payment_reference)<tr><td class="text-muted">Reference</td><td class="text-end font-monospace">{{ $booking->payment_reference }}</td></tr>@endif
                    @if($booking->expected_delivery_date)<tr><td class="text-muted">Expected Delivery</td><td class="text-end">{{ $booking->expected_delivery_date->format('d M Y') }}</td></tr>@endif
                </table>
                @if($booking->notes)<div class="alert alert-light border py-2 small"><i class="bi bi-sticky me-1"></i>{{ $booking->notes }}</div>@endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        @if($booking->status === 'active')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-semibold">Cancel Booking</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('bookings.cancel', $booking) }}" onsubmit="return confirm('Cancel this booking?')">
                    @csrf
                    <input type="text" name="reason" class="form-control form-control-sm mb-2" placeholder="Cancellation reason" required>
                    <button type="submit" class="btn btn-danger btn-sm w-100">Cancel & Release Vehicle</button>
                </form>
            </div>
        </div>
        @endif
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('documents.generate') }}">
                    @csrf
                    <input type="hidden" name="document_type" value="booking_receipt">
                    <input type="hidden" name="reference_type" value="booking">
                    <input type="hidden" name="reference_id" value="{{ $booking->id }}">
                    <input type="hidden" name="customer_id" value="{{ $booking->customer_id }}">
                    <input type="hidden" name="vehicle_id" value="{{ $booking->vehicle_id }}">
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-file-pdf me-1"></i> Generate Booking Receipt
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
