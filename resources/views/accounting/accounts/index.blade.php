@extends('layouts.app')
@section('title','Chart of Accounts')
@section('breadcrumb','Accounting / Chart of Accounts')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Chart of Accounts</h4>
    @can('manage-accounts')
    <button type="button" class="btn btn-primary btn-sm" onclick="openAccountFormModal('{{ route('accounts.create') }}', null)">
        <i class="bi bi-plus-circle me-1"></i> Add Account
    </button>
    @endcan
</div>





@foreach(['asset'=>'Assets','liability'=>'Liabilities','equity'=>'Equity','revenue'=>'Revenue','expense'=>'Expenses'] as $type => $label)
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white py-2"><h6 class="mb-0 fw-semibold text-{{ \App\Models\Account::TYPE_COLORS[$type] }}">{{ $label }}</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-sm mb-0">
            <tbody>
                @forelse($accounts->where('account_type',$type) as $acc)
                <tr class="{{ !$acc->is_active ? 'opacity-50' : '' }}">
                    <td class="ps-4 font-monospace small">{{ $acc->account_code }}</td>
                    <td>
                        {{ $acc->account_name }}
                        @if($acc->is_system)
                            <span class="badge bg-secondary-subtle text-secondary ms-1" style="font-size:10px;">System</span>
                        @endif
                        @if(!$acc->is_active)
                            <span class="badge bg-warning-subtle text-warning ms-1" style="font-size:10px;">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end fw-semibold">PKR {{ number_format($acc->getBalance()) }}</td>
                    @can('manage-accounts')
                    <td class="text-end pe-4" style="width:100px;">
                        <button type="button" class="btn btn-light btn-sm"
                                onclick="openAccountFormModal('{{ route('accounts.edit', $acc) }}', {{ $acc->id }})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-light btn-sm text-danger"
                                onclick="openAccountDeleteModal('{{ route('accounts.destroy', $acc) }}', '{{ $acc->account_code }} — {{ $acc->account_name }}', this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                    @endcan
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-3 text-muted small">No accounts in this category.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endforeach
@endsection

{{-- Add / Edit Modal --}}
<div class="modal fade" id="accountFormModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accountFormModalTitle">Add Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="accountFormModalBody">
                <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            </div>
            <div class="modal-footer">
                <div class="alert alert-danger py-2 px-3 mb-0 me-auto d-none" id="accountFormModalError"></div>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="accountFormModalSaveBtn" onclick="submitAccountFormModal()">
                    <i class="bi bi-check2 me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="accountDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete <strong id="accountDeleteModalName"></strong>?</p>
                <div class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none" id="accountDeleteModalError"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="accountDeleteModalConfirmBtn" onclick="submitAccountDeleteModal()">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')


<script>
const accountCsrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';

function openAccountFormModal(url, accountId) {
    document.getElementById('accountFormModalTitle').textContent = accountId ? 'Edit Account' : 'Add Account';
    document.getElementById('accountFormModalBody').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    document.getElementById('accountFormModalError').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('accountFormModal')).show();

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.text())
        .then(html => { document.getElementById('accountFormModalBody').innerHTML = html; })
        .catch(() => {
            document.getElementById('accountFormModalBody').innerHTML = '<div class="alert alert-danger">Failed to load form.</div>';
        });
}

function submitAccountFormModal() {
    const form = document.querySelector('#accountFormModalBody #accountForm');
    if (!form) return;

    const errorBox = document.getElementById('accountFormModalError');
    const saveBtn  = document.getElementById('accountFormModalSaveBtn');
    errorBox.classList.add('d-none');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

    fetch(form.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form),
    })
        .then(async r => {
            const data = await r.json().catch(() => ({}));
            if (!r.ok) throw data;
            return data;
        })
        .then(() => { window.location.reload(); })
        .catch(err => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-check2 me-1"></i> Save';
            let msg = err.message || 'Something went wrong. Please check the form and try again.';
            if (err.errors) msg = Object.values(err.errors).flat().join(' ');
            errorBox.textContent = msg;
            errorBox.classList.remove('d-none');
        });
}

let accountDeleteContext = null;

function openAccountDeleteModal(url, label, triggerEl) {
    accountDeleteContext = { url, row: triggerEl.closest('tr') };
    document.getElementById('accountDeleteModalName').textContent = label;
    document.getElementById('accountDeleteModalError').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('accountDeleteModal')).show();
}

function submitAccountDeleteModal() {
    if (!accountDeleteContext) return;

    const errorBox = document.getElementById('accountDeleteModalError');
    const btn = document.getElementById('accountDeleteModalConfirmBtn');
    errorBox.classList.add('d-none');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

    const fd = new FormData();
    fd.append('_method', 'DELETE');

    fetch(accountDeleteContext.url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': accountCsrfToken,
        },
        body: fd,
    })
        .then(async r => {
            const data = await r.json().catch(() => ({}));
            if (!r.ok) throw data;
            return data;
        })
        .then(() => {
            bootstrap.Modal.getInstance(document.getElementById('accountDeleteModal')).hide();
            accountDeleteContext.row?.remove();
            accountDeleteContext = null;
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-trash me-1"></i> Delete';
            errorBox.textContent = err.message || 'Failed to delete account.';
            errorBox.classList.remove('d-none');
        });
}
</script>
@endpush
