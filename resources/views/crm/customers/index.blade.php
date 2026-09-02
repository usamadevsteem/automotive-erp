@extends('layouts.app')
@section('title','Customers')
@section('breadcrumb','CRM / Customers')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Customers</h4>
        <small class="text-muted">{{ number_format($customers->total()) }} total</small>
    </div>
    @can('create-customers')
    <button type="button" class="btn btn-primary btn-sm" onclick="openCustomerFormModal('{{ route('customers.create') }}', null)">
        <i class="bi bi-plus-circle me-1"></i> Add Customer
    </button>
    @endcan
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('customers.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                           class="form-control form-control-sm" placeholder="Search name, mobile, CNIC...">
                </div>
                <div class="col-md-2">
                    <select name="source" class="form-select form-select-sm">
                        <option value="">All Sources</option>
                        @foreach(\App\Models\Customer::SOURCES as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['source'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="customer_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach(\App\Models\Customer::TYPES as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['customer_type'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="{{ route('customers.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-x"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($customers->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people fs-1 opacity-25 d-block mb-2"></i>
                <p class="mb-0">No customers found.</p>
                @can('create-customers')
                <button type="button" class="btn btn-primary btn-sm mt-3" onclick="openCustomerFormModal('{{ route('customers.create') }}', null)">
                    <i class="bi bi-plus me-1"></i> Add First Customer
                </button>
                @endcan
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Name</th>
                        <th>Mobile</th>
                        <th>CNIC</th>
                        <th>City</th>
                        <th>Source</th>
                        <th>Type</th>
                        <th>Assigned To</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                    <tr>
                        <td class="ps-4">
                            <a href="{{ route('customers.show', $customer) }}"
                               class="fw-semibold text-decoration-none text-dark">
                                {{ $customer->full_name }}
                            </a>
                            @if($customer->father_husband_name)
                            <div class="text-muted" style="font-size:11px;">S/O {{ $customer->father_husband_name }}</div>
                            @endif
                        </td>
                        <td><a href="tel:{{ $customer->mobile }}" class="text-decoration-none">{{ $customer->mobile }}</a></td>
                        <td><small class="font-monospace">{{ $customer->cnic ?? '—' }}</small></td>
                        <td>{{ $customer->city ?? '—' }}</td>
                        <td><span class="badge bg-light text-dark">{{ $customer->source_label }}</span></td>
                        <td><span class="badge bg-primary-subtle text-primary">{{ \App\Models\Customer::TYPES[$customer->customer_type] }}</span></td>
                        <td><small>{{ $customer->assignedTo?->name ?? '—' }}</small></td>
                        <td class="pe-4 text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <button type="button" class="btn btn-light btn-sm"
                                        onclick="openCustomerViewModal('{{ route('customers.show', $customer) }}', '{{ $customer->full_name }}')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @can('edit-customers')
                                <button type="button" class="btn btn-light btn-sm"
                                        onclick="openCustomerFormModal('{{ route('customers.edit', $customer) }}', '{{ $customer->full_name }}')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @endcan
                                @can('delete-customers')
                                <button type="button" class="btn btn-light btn-sm text-danger"
                                        onclick="openCustomerDeleteModal('{{ route('customers.destroy', $customer) }}', '{{ $customer->full_name }}', this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-top d-flex align-items-center justify-content-between">
            <small class="text-muted">Showing {{ $customers->firstItem() }}–{{ $customers->lastItem() }} of {{ number_format($customers->total()) }}</small>
            {{ $customers->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection

{{-- ══════════════ View Details Modal ══════════════ --}}
<div class="modal fade" id="customerViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Customer Details <span id="customerViewModalName" class="text-muted"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="customerViewModalBody">
                <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════ Add / Edit Modal ══════════════ --}}
<div class="modal fade" id="customerFormModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerFormModalTitle">Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="customerFormModalBody">
                <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            </div>
            <div class="modal-footer">
                <div class="alert alert-danger py-2 px-3 mb-0 me-auto d-none" id="customerFormModalError"></div>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="customerFormModalSaveBtn" onclick="submitCustomerFormModal()">
                    <i class="bi bi-check2 me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════ Delete Confirmation Modal ══════════════ --}}
<div class="modal fade" id="customerDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Remove Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to remove <strong id="customerDeleteModalName"></strong>? This cannot be undone.</p>
                <div class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none" id="customerDeleteModalError"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="customerDeleteModalConfirmBtn" onclick="submitCustomerDeleteModal()">
                    <i class="bi bi-trash me-1"></i> Remove
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';

// ══════════════ View Modal ══════════════
function openCustomerViewModal(url, name) {
    document.getElementById('customerViewModalName').textContent = '— ' + name;
    document.getElementById('customerViewModalBody').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    new bootstrap.Modal(document.getElementById('customerViewModal')).show();

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.text())
        .then(html => { document.getElementById('customerViewModalBody').innerHTML = html; })
        .catch(() => {
            document.getElementById('customerViewModalBody').innerHTML = '<div class="alert alert-danger">Failed to load customer details.</div>';
        });
}

// ══════════════ Add / Edit Modal ══════════════
function openCustomerFormModal(url, name) {
    document.getElementById('customerFormModalTitle').textContent = name ? 'Edit Customer' : 'Add Customer';
    document.getElementById('customerFormModalBody').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    document.getElementById('customerFormModalError').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('customerFormModal')).show();

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.text())
        .then(html => { document.getElementById('customerFormModalBody').innerHTML = html; })
        .catch(() => {
            document.getElementById('customerFormModalBody').innerHTML = '<div class="alert alert-danger">Failed to load form.</div>';
        });
}

function submitCustomerFormModal() {
    const form = document.querySelector('#customerFormModalBody #customerForm');
    if (!form) return;

    const errorBox = document.getElementById('customerFormModalError');
    const saveBtn  = document.getElementById('customerFormModalSaveBtn');
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

// ══════════════ Delete Modal ══════════════
let customerDeleteContext = null;

function openCustomerDeleteModal(url, name, triggerEl) {
    customerDeleteContext = { url, row: triggerEl.closest('tr') };
    document.getElementById('customerDeleteModalName').textContent = name;
    document.getElementById('customerDeleteModalError').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('customerDeleteModal')).show();
}

function submitCustomerDeleteModal() {
    if (!customerDeleteContext) return;

    const errorBox = document.getElementById('customerDeleteModalError');
    const btn = document.getElementById('customerDeleteModalConfirmBtn');
    errorBox.classList.add('d-none');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Removing...';

    const fd = new FormData();
    fd.append('_method', 'DELETE');

    fetch(customerDeleteContext.url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: fd,
    })
        .then(async r => {
            const data = await r.json().catch(() => ({}));
            if (!r.ok) throw data;
            return data;
        })
        .then(() => {
            bootstrap.Modal.getInstance(document.getElementById('customerDeleteModal')).hide();
            customerDeleteContext.row?.remove();
            customerDeleteContext = null;
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-trash me-1"></i> Remove';
            errorBox.textContent = err.message || 'Failed to remove customer.';
            errorBox.classList.remove('d-none');
        });
}
</script>
@endpush
