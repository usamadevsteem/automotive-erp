@extends('layouts.app')
@section('title','Expenses')
@section('breadcrumb','Accounting / Expenses')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Expenses</h4>
    @can('create-expenses')
    <button type="button" class="btn btn-primary btn-sm" onclick="openExpenseFormModal('{{ route('expenses.create') }}', null)">
        <i class="bi bi-plus-circle me-1"></i> Add Expense
    </button>
    @endcan
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th class="ps-4">Expense #</th><th>Category</th><th>Description</th><th>Amount</th><th>Date</th><th>Status</th>
                    @can('create-expenses')<th class="pe-4 text-end">Actions</th>@endcan
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $e)
                <tr>
                    <td class="ps-4 fw-semibold">{{ $e->expense_number }}</td>
                    <td><span class="badge bg-light text-dark">{{ $e->category->name }}</span></td>
                    <td class="small">{{ $e->description }}</td>
                    <td class="fw-semibold">{{ $e->amount_formatted }}</td>
                    <td><small>{{ $e->expense_date->format('d M Y') }}</small></td>
                    <td><span class="badge bg-{{ $e->status_color }}-subtle text-{{ $e->status_color }}">{{ $e->status_label }}</span></td>
                    @can('create-expenses')
                    <td class="pe-4 text-end">
                        <button type="button" class="btn btn-light btn-sm" onclick="openExpenseFormModal('{{ route('expenses.edit', $e) }}', '{{ $e->expense_number }}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-light btn-sm text-danger" onclick="openExpenseDeleteModal('{{ route('expenses.destroy', $e) }}', '{{ $e->expense_number }}', this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                    @endcan
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5 text-muted">No expenses found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="px-4 py-3 border-top">{{ $expenses->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

<div class="modal fade" id="expenseFormModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="expenseFormModalTitle">Add Expense</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="expenseFormModalBody"><div class="text-center py-5"><div class="spinner-border text-primary"></div></div></div>
            <div class="modal-footer">
                <div class="alert alert-danger py-2 px-3 mb-0 me-auto d-none" id="expenseFormModalError"></div>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="expenseFormModalSaveBtn" onclick="submitExpenseFormModal()">
                    <i class="bi bi-check2 me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="expenseDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Delete Expense</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p class="mb-0">Delete expense <strong id="expenseDeleteModalName"></strong>?</p>
                <div class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none" id="expenseDeleteModalError"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="expenseDeleteModalConfirmBtn" onclick="submitExpenseDeleteModal()">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
