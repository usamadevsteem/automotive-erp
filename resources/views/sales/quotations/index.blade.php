@extends('layouts.app')
@section('title','Quotations')
@section('breadcrumb','Sales / Quotations')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Quotations</h4>
    @can('create-quotations')
    <button type="button" class="btn btn-primary btn-sm" onclick="openQuotationFormModal('{{ route('quotations.create') }}')">
        <i class="bi bi-plus-circle me-1"></i> New Quotation
    </button>
    @endcan
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Quotation #</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Net Price</th>
                        <th>Valid Until</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations as $q)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $q->quotation_number }}</td>
                        <td>{{ $q->customer->full_name }}</td>
                        <td>{{ $q->vehicle->make->name }} {{ $q->vehicle->vehicleModel->name }} {{ $q->vehicle->year }}</td>
                        <td class="fw-semibold">PKR {{ number_format($q->net_price) }}</td>
                        <td><small class="{{ $q->valid_until->isPast() ? 'text-danger' : 'text-muted' }}">{{ $q->valid_until->format('d M Y') }}</small></td>
                        <td><span class="badge bg-{{ $q->status_color }}-subtle text-{{ $q->status_color }}">{{ $q->status_label }}</span></td>
                        <td class="pe-4 text-end">
                            <button type="button" class="btn btn-light btn-sm"
                                    onclick="openQuotationViewModal('{{ route('quotations.show', $q) }}')">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">No quotations found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-top">{{ $quotations->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

{{-- View Modal --}}
<div class="modal fade" id="quotationViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Quotation Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="quotationViewModalBody"><div class="text-center py-5"><div class="spinner-border text-primary"></div></div></div>
        </div>
    </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="quotationFormModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">New Quotation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="quotationFormModalBody"><div class="text-center py-5"><div class="spinner-border text-primary"></div></div></div>
            <div class="modal-footer">
                <div class="alert alert-danger py-2 px-3 mb-0 me-auto d-none" id="quotationFormModalError"></div>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="quotationFormModalSaveBtn" onclick="submitQuotationFormModal()">
                    <i class="bi bi-check2 me-1"></i> Create
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
