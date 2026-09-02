<div class="row g-3">
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Booking #</small>
        <div class="fw-semibold">{{ $booking->booking_number }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Status</small>
        <span class="badge bg-{{ $booking->status_color }}-subtle text-{{ $booking->status_color }}">
            {{ $booking->status_label }}
        </span>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Customer</small>
        <div>{{ $booking->customer->full_name }} — {{ $booking->customer->mobile }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Vehicle</small>
        <div>{{ $booking->vehicle->make->name }} {{ $booking->vehicle->vehicleModel->name }} {{ $booking->vehicle->year }}</div>
    </div>
    <div class="col-md-4">
        <small class="text-muted d-block mb-1">Advance Paid</small>
        <div class="fw-semibold">PKR {{ number_format($booking->booking_amount) }}</div>
    </div>
    <div class="col-md-4">
        <small class="text-muted d-block mb-1">Agreed Sale Price</small>
        <div>PKR {{ number_format($booking->agreed_sale_price) }}</div>
    </div>
    <div class="col-md-4">
        <small class="text-muted d-block mb-1">Expected Delivery</small>
        <div>{{ $booking->expected_delivery_date?->format('d M Y') ?? '—' }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Payment Method</small>
        <div>{{ \App\Models\Booking::PAYMENT_METHODS[$booking->payment_method] ?? $booking->payment_method }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Payment Reference</small>
        <div>{{ $booking->payment_reference ?? '—' }}</div>
    </div>
    @if($booking->notes)
    <div class="col-12">
        <small class="text-muted d-block mb-1">Notes</small>
        <div>{{ $booking->notes }}</div>
    </div>
    @endif
</div>

@if($booking->status === 'active')
<hr>
<button type="button" class="btn btn-sm btn-outline-danger"
        onclick="openBookingCancelModal({{ $booking->id }})">
    <i class="bi bi-x-circle me-1"></i> Cancel Booking
</button>
@endif

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('bookings.show', $booking) }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-up-right-square me-1"></i> Open Full Page
    </a>
</div>
