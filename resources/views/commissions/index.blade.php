@extends('layouts.app')
@section('title','Commissions')
@section('breadcrumb','Finance / Commissions')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Commissions</h4>
    @can('manage-commission-rules')
    <a href="{{ route('commission-rules.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-gear me-1"></i> Manage Rules</a>
    @endcan
</div>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted small">Pending Commissions</div>
            <div class="fw-bold fs-4 text-warning">PKR {{ number_format($totalPending) }}</div>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted small">Approved (Unpaid)</div>
            <div class="fw-bold fs-4 text-info">PKR {{ number_format($totalApproved) }}</div>
        </div></div>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-4">Employee</th><th>Sale Invoice</th><th>Type</th><th>Amount</th><th>Status</th><th class="pe-4 text-end">Actions</th></tr></thead>
            <tbody>
                @forelse($commissions as $c)
                <tr>
                    <td class="ps-4">{{ $c->employee?->name ?? $c->referrer_name ?? '—' }}</td>
                    <td><a href="{{ route('invoices.show', $c->saleInvoice) }}" class="text-decoration-none">{{ $c->saleInvoice->invoice_number }}</a></td>
                    <td><span class="badge bg-light text-dark">{{ \App\Models\Commission::TYPES[$c->commission_type] }}</span></td>
                    <td class="fw-semibold">{{ $c->amount_formatted }}</td>
                    <td><span class="badge bg-{{ $c->status_color }}-subtle text-{{ $c->status_color }}">{{ $c->status_label }}</span></td>
                    <td class="pe-4 text-end">
                        @if($c->status === 'pending')
                        @can('approve-commissions')
                        <form method="POST" action="{{ route('commissions.approve', $c) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-success btn-sm">Approve</button>
                        </form>
                        @endcan
                        @elseif($c->status === 'approved')
                        @can('pay-commissions')
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#payModal{{ $c->id }}">Pay</button>
                        <div class="modal fade" id="payModal{{ $c->id }}">
                            <div class="modal-dialog"><div class="modal-content">
                                <form method="POST" action="{{ route('commissions.pay', $c) }}">
                                    @csrf
                                    <div class="modal-header"><h6 class="modal-title">Pay Commission</h6></div>
                                    <div class="modal-body">
                                        <select name="payment_method" class="form-select mb-2" required>
                                            <option value="cash">Cash</option><option value="bank_transfer">Bank Transfer</option><option value="cheque">Cheque</option>
                                        </select>
                                        <input type="date" name="payment_date" value="{{ today()->toDateString() }}" class="form-control" required>
                                    </div>
                                    <div class="modal-footer"><button class="btn btn-primary btn-sm">Confirm Payment</button></div>
                                </form>
                            </div></div>
                        </div>
                        @endcan
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">No commissions found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-top">{{ $commissions->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
