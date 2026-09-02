@extends('layouts.app')
@section('title','Bookings')
@section('breadcrumb','Sales / Bookings')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Bookings</h4>
    @can('create-bookings')
    <button type="button" class="btn btn-primary btn-sm" onclick="openBookingFormModal('{{ route('bookings.create') }}')">
        <i class="bi bi-plus-circle me-1"></i> New Booking
    </button>
    @endcan
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Booking #</th><th>Customer</th><th>Vehicle</th>
                        <th>Advance</th><th>Sale Price</th><th>Status</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $b)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $b->booking_number }}</td>
                        <td>{{ $b->customer->full_name }}</td>
                        <td>{{ $b->vehicle->make->name }} {{ $b->vehicle->vehicleModel->name }} {{ $b->vehicle->year }}</td>
                        <td class="fw-semibold">PKR {{ number_format($b->booking_amount) }}</td>
                        <td>PKR {{ number_format($b->agreed_sale_price) }}</td>
                        <td><span class="badge bg-{{ $b->status_color }}-subtle text-{{ $b->status_color }}">{{ $b->status_label }}</span></td>
                        <td class="pe-4 text-end">
                            <button type="button" class="btn btn-light btn-sm"
                                    onclick="openBookingViewModal('{{ route('bookings.show', $b) }}')">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">No bookings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-top">{{ $bookings->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

{{-- View Modal --}}
<div class="modal fade" id="bookingViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Booking Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="bookingViewModalBody"><div class="text-center py-5"><div class="spinner-border text-primary"></div></div></div>
        </div>
    </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="bookingFormModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">New Booking</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="bookingFormModalBody"><div class="text-center py-5"><div class="spinner-border text-primary"></div></div></div>
            <div class="modal-footer">
                <div class="alert alert-danger py-2 px-3 mb-0 me-auto d-none" id="bookingFormModalError"></div>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="bookingFormModalSaveBtn" onclick="submitBookingFormModal()">
                    <i class="bi bi-check2 me-1"></i> Create Booking
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Cancel Confirmation Modal --}}
<div class="modal fade" id="bookingCancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Cancel Booking</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label small fw-semibold">Reason for cancellation</label>
                <textarea id="bookingCancelReason" class="form-control" rows="3"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" onclick="submitBookingCancel()">
                    <i class="bi bi-x-circle me-1"></i> Cancel Booking
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
