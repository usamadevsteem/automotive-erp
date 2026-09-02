@extends('layouts.app')
@section('title','Sale Invoices')
@section('breadcrumb','Sales / Invoices')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Sale Invoices</h4>
    @can('create-invoices')
    <button type="button" class="btn btn-primary btn-sm" onclick="openInvoiceFormModal('{{ route('invoices.create') }}')">
        <i class="bi bi-plus-circle me-1"></i> New Invoice
    </button>
    @endcan
</div>

{{-- Status filter tabs --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2 px-3">
        <ul class="nav nav-pills gap-1">
            @foreach([''=>'All', 'draft'=>'Draft', 'issued'=>'Issued', 'partial'=>'Partial', 'paid'=>'Paid', 'cancelled'=>'Cancelled'] as $k => $label)
            <li class="nav-item">
                <a href="{{ route('invoices.index', ['status' => $k]) }}"
                   class="nav-link py-1 px-3 {{ request('status','') === $k ? 'active' : 'text-muted' }}">
                    {{ $label }}
                </a>
            </li>
            @endforeach
        </ul>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Invoice #</th><th>Customer</th><th>Vehicle</th>
                        <th>Net Amount</th><th>Balance Due</th><th>Date</th><th>Type</th><th>Status</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $inv->invoice_number }}</td>
                        <td>{{ $inv->customer->full_name }}</td>
                        <td class="small">{{ $inv->vehicle->make->name }} {{ $inv->vehicle->vehicleModel->name }} {{ $inv->vehicle->year }}</td>
                        <td class="fw-semibold">PKR {{ number_format($inv->net_amount) }}</td>
                        <td class="{{ $inv->balance_due > 0 ? 'text-danger fw-semibold' : 'text-success' }}">
                            PKR {{ number_format($inv->balance_due) }}
                        </td>
                        <td><small>{{ $inv->invoice_date->format('d M Y') }}</small></td>
                        <td>
                            <span class="badge bg-light text-dark">
                                {{ \App\Models\SaleInvoice::PAYMENT_TYPES[$inv->payment_type] ?? ucfirst($inv->payment_type) }}
                            </span>
                        </td>
                        <td><span class="badge bg-{{ $inv->status_color }}-subtle text-{{ $inv->status_color }}">{{ $inv->status_label }}</span></td>
                        <td class="pe-4 text-end">
                            <button type="button" class="btn btn-light btn-sm"
                                    onclick="openInvoiceViewModal('{{ route('invoices.show', $inv) }}')">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-5 text-muted">No invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-top">{{ $invoices->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

{{-- View Modal --}}
<div class="modal fade" id="invoiceViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Invoice Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="invoiceViewModalBody"><div class="text-center py-5"><div class="spinner-border text-primary"></div></div></div>
        </div>
    </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="invoiceFormModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">New Invoice</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="invoiceFormModalBody"><div class="text-center py-5"><div class="spinner-border text-primary"></div></div></div>
            <div class="modal-footer">
                <div class="alert alert-danger py-2 px-3 mb-0 me-auto d-none" id="invoiceFormModalError"></div>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="invoiceFormModalSaveBtn" onclick="submitInvoiceFormModal()">
                    <i class="bi bi-check2 me-1"></i> Create Invoice
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
