@extends('layouts.app')
@section('title','Payments')
@section('breadcrumb','Accounting / Payments')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Payments</h4>
    @can('create-payments')
    <button type="button" class="btn btn-primary btn-sm" onclick="openPaymentFormModal('{{ route('payments.create') }}', null)">
        <i class="bi bi-plus-circle me-1"></i> Record Payment
    </button>
    @endcan
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th class="ps-4">Payment #</th><th>Type</th><th>Party</th><th>Amount</th><th>Method</th><th>Date</th>
                    @can('create-payments')<th class="pe-4 text-end">Actions</th>@endcan
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                <tr>
                    <td class="ps-4 fw-semibold">{{ $p->payment_number }}</td>
                    <td><span class="badge bg-{{ $p->type === 'received' ? 'success' : 'danger' }}-subtle text-{{ $p->type === 'received' ? 'success' : 'danger' }}">{{ ucfirst($p->type) }}</span></td>
                    <td>{{ $p->party_name }} <small class="text-muted">({{ ucfirst($p->party_type) }})</small></td>
                    <td class="fw-semibold">{{ $p->amount_formatted }}</td>
                    <td>{{ \App\Models\Payment::PAYMENT_METHODS[$p->payment_method] }}</td>
                    <td><small>{{ $p->payment_date->format('d M Y') }}</small></td>
                    @can('create-payments')
                    <td class="pe-4 text-end">
                        <button type="button" class="btn btn-light btn-sm" onclick="openPaymentFormModal('{{ route('payments.edit', $p) }}', '{{ $p->payment_number }}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-light btn-sm text-danger" onclick="openPaymentDeleteModal('{{ route('payments.destroy', $p) }}', '{{ $p->payment_number }}', this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                    @endcan
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5 text-muted">No payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="px-4 py-3 border-top">{{ $payments->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

<div class="modal fade" id="paymentFormModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="paymentFormModalTitle">Record Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="paymentFormModalBody"><div class="text-center py-5"><div class="spinner-border text-primary"></div></div></div>
            <div class="modal-footer">
                <div class="alert alert-danger py-2 px-3 mb-0 me-auto d-none" id="paymentFormModalError"></div>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="paymentFormModalSaveBtn" onclick="submitPaymentFormModal()">
                    <i class="bi bi-check2 me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="paymentDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Delete Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p class="mb-0">Delete payment <strong id="paymentDeleteModalName"></strong>?</p>
                <div class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none" id="paymentDeleteModalError"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="paymentDeleteModalConfirmBtn" onclick="submitPaymentDeleteModal()">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
