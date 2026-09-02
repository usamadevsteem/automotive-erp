@extends('layouts.app')
@section('title','Trade-Ins')
@section('breadcrumb','Sales / Trade-Ins')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Trade-Ins</h4>
    @can('create-trade-ins')
    <button type="button" class="btn btn-primary btn-sm" onclick="openTradeInFormModal('{{ route('trade-ins.create') }}')">
        <i class="bi bi-plus-circle me-1"></i> New Trade-In
    </button>
    @endcan
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th class="ps-4">Customer</th><th>Trade Vehicle</th><th>New Vehicle</th><th>Market Value</th><th>Offered</th><th>Status</th><th class="pe-4"></th></tr>
            </thead>
            <tbody>
                @forelse($tradeIns as $t)
                <tr>
                    <td class="ps-4">{{ $t->customer->full_name }}</td>
                    <td class="small">{{ $t->trade_make }} {{ $t->trade_model }} {{ $t->trade_year }}</td>
                    <td class="small">{{ $t->newVehicle->make->name }} {{ $t->newVehicle->vehicleModel->name }}</td>
                    <td>PKR {{ number_format($t->market_value) }}</td>
                    <td class="fw-semibold">PKR {{ number_format($t->offered_value) }}</td>
                    <td><span class="badge bg-{{ $t->status_color }}-subtle text-{{ $t->status_color }}">{{ $t->status_label }}</span></td>
                    <td class="pe-4">
                        <button type="button" class="btn btn-light btn-sm"
                                onclick="openTradeInViewModal('{{ route('trade-ins.show', $t) }}')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5 text-muted">No trade-ins found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-top">{{ $tradeIns->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

{{-- View Modal --}}
<div class="modal fade" id="tradeInViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Trade-In Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="tradeInViewModalBody"><div class="text-center py-5"><div class="spinner-border text-primary"></div></div></div>
        </div>
    </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="tradeInFormModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">New Trade-In</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="tradeInFormModalBody"><div class="text-center py-5"><div class="spinner-border text-primary"></div></div></div>
            <div class="modal-footer">
                <div class="alert alert-danger py-2 px-3 mb-0 me-auto d-none" id="tradeInFormModalError"></div>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="tradeInFormModalSaveBtn" onclick="submitTradeInFormModal()">
                    <i class="bi bi-check2 me-1"></i> Submit
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
