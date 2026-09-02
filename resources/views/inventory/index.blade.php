@extends('layouts.app')

@section('title', 'Vehicle Inventory')
@section('breadcrumb', 'Inventory / All Vehicles')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Vehicle Inventory</h4>
        <small class="text-muted">{{ number_format($vehicles->total()) }} vehicles total</small>
    </div>
    @can('create-vehicles')
    <button type="button" class="btn btn-primary" onclick="openVehicleCreateModal()">
        <i class="bi bi-plus-circle me-1"></i> Add Vehicle
    </button>
    @endcan
</div>

{{-- ── Status Tabs ─────────────────────────────────────────────────── --}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body p-0">
        <ul class="nav nav-pills p-3 gap-2">
            @php
                $statusTabs = [
                    ''                    => ['label' => 'All',                'icon' => 'bi-grid'],
                    'available'           => ['label' => 'Available',          'icon' => 'bi-check-circle'],
                    'reserved'            => ['label' => 'Reserved',           'icon' => 'bi-clock'],
                    'pending_inspection'  => ['label' => 'Pending Inspection', 'icon' => 'bi-search'],
                    'sold'                => ['label' => 'Sold',               'icon' => 'bi-bag-check'],
                    'delivered'           => ['label' => 'Delivered',          'icon' => 'bi-truck'],
                ];
            @endphp

            @foreach($statusTabs as $statusValue => $tab)
                @php
                    $isActive = ($filters['status'] ?? '') === $statusValue;
                    $count    = $statusValue
                        ? ($statusCounts[$statusValue] ?? 0)
                        : $statusCounts->sum();
                @endphp
                <li class="nav-item">
                    <a href="{{ route('vehicles.index', array_merge($filters, ['status' => $statusValue])) }}"
                       class="nav-link py-1 px-3 {{ $isActive ? 'active' : 'text-muted' }}">
                        <i class="{{ $tab['icon'] }} me-1"></i>
                        {{ $tab['label'] }}
                        <span class="badge {{ $isActive ? 'bg-white text-primary' : 'bg-light text-muted' }} ms-1">
                            {{ number_format($count) }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>

{{-- ── Filters ─────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('vehicles.index') }}" id="filterForm">
            <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text"
                           name="search"
                           value="{{ $filters['search'] ?? '' }}"
                           class="form-control form-control-sm"
                           placeholder="Search stock#, chassis, reg...">
                </div>
                <div class="col-md-2">
                    <select name="make_id" class="form-select form-select-sm" id="filterMake">
                        <option value="">All Makes</option>
                        @foreach($makes as $make)
                            <option value="{{ $make->id }}"
                                {{ ($filters['make_id'] ?? '') == $make->id ? 'selected' : '' }}>
                                {{ $make->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach(\App\Models\Vehicle::CATEGORIES as $key => $label)
                            <option value="{{ $key }}"
                                {{ ($filters['category'] ?? '') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="branch_id" class="form-select form-select-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}"
                                {{ ($filters['branch_id'] ?? '') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <input type="number"
                           name="year"
                           value="{{ $filters['year'] ?? '' }}"
                           class="form-control form-control-sm"
                           placeholder="Year">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="{{ route('vehicles.index') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-x"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Vehicle Table ────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($vehicles->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-car-front fs-1 d-block mb-3 opacity-25"></i>
                <p class="mb-1 fw-semibold">No vehicles found</p>
                <small>Try adjusting your filters or add a new vehicle.</small>
                @can('create-vehicles')
                <div class="mt-3">
                    <button type="button" class="btn btn-primary btn-sm" onclick="openVehicleCreateModal()">
                        <i class="bi bi-plus me-1"></i> Add First Vehicle
                    </button>
                </div>
                @endcan
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Stock #</th>
                            <th>Vehicle</th>
                            <th>Year</th>
                            <th>Color</th>
                            <th>Mileage</th>
                            <th>Branch</th>
                            @can('view-vehicle-cost')
                            <th>Cost</th>
                            @endcan
                            <th>Sale Price</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vehicles as $vehicle)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('vehicles.show', $vehicle) }}"
                                   class="text-decoration-none fw-semibold text-dark">
                                    {{ $vehicle->stock_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $vehicle->make->name }} {{ $vehicle->vehicleModel->name }}</div>
                                <small class="text-muted">{{ $vehicle->variant?->name ?? '—' }}</small>
                            </td>
                            <td>{{ $vehicle->year }}</td>
                            <td>
                                <span class="d-flex align-items-center gap-1">
                                    <span style="width:12px;height:12px;border-radius:50%;background:{{ \App\Helpers\ColorHelper::toHex($vehicle->color) }};border:1px solid #dee2e6;display:inline-block;"></span>
                                    {{ $vehicle->color ?? '—' }}
                                </span>
                            </td>
                            <td>{{ $vehicle->mileage_formatted }}</td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $vehicle->branch->name }}</span>
                            </td>
                            @can('view-vehicle-cost')
                            <td>
                                <small class="text-muted">{{ $vehicle->total_cost_formatted }}</small>
                            </td>
                            @endcan
                            <td class="fw-semibold">{{ $vehicle->sale_price_formatted }}</td>
                            <td>
                                <span class="badge bg-{{ $vehicle->status_color }}-subtle text-{{ $vehicle->status_color }} border border-{{ $vehicle->status_color }}-subtle">
                                    {{ $vehicle->status_label }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm dropdown-toggle"
                                            data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <button type="button" class="dropdown-item"
                                                    onclick="openViewModal('{{ route('vehicles.show', $vehicle) }}', '{{ $vehicle->stock_number }}')">
                                                <i class="bi bi-eye me-2"></i> View Details
                                            </button>
                                        </li>
                                        @can('edit-vehicles')
                                        <li>
                                            <button type="button" class="dropdown-item"
                                                    onclick="openEditModal('{{ route('vehicles.edit', $vehicle) }}', '{{ $vehicle->stock_number }}')">
                                                <i class="bi bi-pencil me-2"></i> Edit
                                            </button>
                                        </li>
                                        @endcan
                                        @can('transfer-vehicles')
                                        @if($vehicle->canBeTransferred())
                                        <li>
                                            <button type="button" class="dropdown-item"
                                                    onclick="openTransferModal('{{ route('vehicles.transfers.initiate', $vehicle) }}', '{{ $vehicle->stock_number }}')">
                                                <i class="bi bi-arrow-left-right me-2"></i> Transfer
                                            </button>
                                        </li>
                                        @endif
                                        @endcan
                                        @can('delete-vehicles')
                                        @if(!$vehicle->isSold())
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button type="button" class="dropdown-item text-danger"
                                                    onclick="openDeleteModal('{{ route('vehicles.destroy', $vehicle) }}', '{{ $vehicle->stock_number }}', this)">
                                                <i class="bi bi-trash me-2"></i> Remove
                                            </button>
                                        </li>
                                        @endif
                                        @endcan
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex align-items-center justify-content-between px-4 py-3 border-top">
                <small class="text-muted">
                    Showing {{ $vehicles->firstItem() }}–{{ $vehicles->lastItem() }}
                    of {{ number_format($vehicles->total()) }} vehicles
                </small>
                {{ $vehicles->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection

{{-- ══════════════ Create Modal ══════════════ --}}
<div class="modal fade" id="createModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="createModalBody">
                <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            </div>
            <div class="modal-footer">
                <div class="alert alert-danger py-2 px-3 mb-0 me-auto d-none" id="createModalError"></div>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="createModalSaveBtn" onclick="submitCreateModal()">
                    <i class="bi bi-plus-circle me-1"></i> Add to Inventory
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════ View Details Modal ══════════════ --}}
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vehicle Details <span id="viewModalStock" class="text-muted"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody">
                <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════ Edit Modal ══════════════ --}}
<div class="modal fade" id="editModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Vehicle <span id="editModalStock" class="text-muted"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="editModalBody">
                <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            </div>
            <div class="modal-footer">
                <div class="alert alert-danger py-2 px-3 mb-0 me-auto d-none" id="editModalError"></div>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="editModalSaveBtn" onclick="submitEditModal()">
                    <i class="bi bi-check-circle me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════ Transfer Modal ══════════════ --}}
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Transfer Vehicle <span id="transferModalStock" class="text-muted"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="transferModalBody">
                <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitTransferModal()">
                    <i class="bi bi-arrow-left-right me-1"></i> Submit Transfer
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════ Delete Confirmation Modal ══════════════ --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Remove Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to remove <strong id="deleteModalStock"></strong> from inventory? This cannot be undone.</p>
                <div class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none" id="deleteModalError"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteModalConfirmBtn" onclick="submitDeleteModal()">
                    <i class="bi bi-trash me-1"></i> Remove
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-submit filter form on select change
document.querySelectorAll('#filterForm select').forEach(el => {
    el.addEventListener('change', () => document.getElementById('filterForm').submit());
});

// ══════════════ Create Modal ══════════════
function openVehicleCreateModal() {
    document.getElementById('createModalBody').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    document.getElementById('createModalError').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('createModal')).show();

    fetch('{{ route('vehicles.create') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.text())
        // .then(html => {
        //     document.getElementById('createModalBody').innerHTML = html;
        //     window.initVehicleEditForm(document.getElementById('createModalBody'));
        // })
        .then(html => {
    const body = document.getElementById('createModalBody');

    body.innerHTML = html;

    window.initVehicleEditForm(body);
    initVehicleImagePreview(body);
})
        .catch(() => {
            document.getElementById('createModalBody').innerHTML = '<div class="alert alert-danger">Failed to load form.</div>';
        });
}

function submitCreateModal() {
    const form = document.querySelector('#createModalBody #vehicleForm');
    if (!form) return;

    const errorBox = document.getElementById('createModalError');
    const saveBtn  = document.getElementById('createModalSaveBtn');
    errorBox.classList.add('d-none');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Adding...';

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
            saveBtn.innerHTML = '<i class="bi bi-plus-circle me-1"></i> Add to Inventory';
            let msg = err.message || 'Something went wrong. Please check the form and try again.';
            if (err.errors) msg = Object.values(err.errors).flat().join(' ');
            errorBox.textContent = msg;
            errorBox.classList.remove('d-none');
        });
}

function initVehicleImagePreview(scope) {
    const input = scope.querySelector('#vehicleImages');
    const preview = scope.querySelector('#vehicleImagePreview');

    if (!input || !preview) return;

    input.addEventListener('change', function () {
        preview.innerHTML = '';

        const files = Array.from(this.files || []);

        if (files.length > 10) {
            alert('You can upload a maximum of 10 images.');
            this.value = '';
            return;
        }

        files.forEach((file, index) => {
            if (!file.type.startsWith('image/')) return;

            if (file.size > 5 * 1024 * 1024) {
                alert(`${file.name} is larger than 5 MB.`);
                return;
            }

            const reader = new FileReader();

            reader.onload = function (e) {
                const wrapper = document.createElement('div');

                wrapper.style.width = '140px';
                wrapper.style.position = 'relative';

                wrapper.innerHTML = `
                    <img
                        src="${e.target.result}"
                        class="rounded border"
                        style="width:140px;height:95px;object-fit:cover;"
                    >

                    ${index === 0 ? `
                        <span class="badge bg-primary position-absolute top-0 start-0 m-1">
                            Featured
                        </span>
                    ` : ''}
                `;

                preview.appendChild(wrapper);
            };

            reader.readAsDataURL(file);
        });
    });
}

// ══════════════ View Modal ══════════════
function openViewModal(url, stockNumber) {
    document.getElementById('viewModalStock').textContent = '— ' + stockNumber;
    document.getElementById('viewModalBody').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    new bootstrap.Modal(document.getElementById('viewModal')).show();

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.text())
        .then(html => {
            document.getElementById('viewModalBody').innerHTML = html;
            window.initVehicleDetailTabs(document.getElementById('viewModalBody'));
        })
        .catch(() => {
            document.getElementById('viewModalBody').innerHTML = '<div class="alert alert-danger">Failed to load vehicle details.</div>';
        });
}

// ══════════════ Edit Modal ══════════════
function openEditModal(url, stockNumber) {
    document.getElementById('editModalStock').textContent = '— ' + stockNumber;
    document.getElementById('editModalBody').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    document.getElementById('editModalError').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('editModal')).show();

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.text())
        .then(html => {
            document.getElementById('editModalBody').innerHTML = html;
            window.initVehicleEditForm(document.getElementById('editModalBody'));
        })
        .catch(() => {
            document.getElementById('editModalBody').innerHTML = '<div class="alert alert-danger">Failed to load edit form.</div>';
        });
}

function submitEditModal() {
    const form = document.querySelector('#editModalBody #vehicleForm');
    if (!form) return;

    const errorBox = document.getElementById('editModalError');
    const saveBtn  = document.getElementById('editModalSaveBtn');
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
            saveBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Save Changes';
            let msg = err.message || 'Something went wrong. Please check the form and try again.';
            if (err.errors) {
                msg = Object.values(err.errors).flat().join(' ');
            }
            errorBox.textContent = msg;
            errorBox.classList.remove('d-none');
        });
}

// ══════════════ Transfer Modal ══════════════
function openTransferModal(url, stockNumber) {
    document.getElementById('transferModalStock').textContent = '— ' + stockNumber;
    document.getElementById('transferModalBody').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    new bootstrap.Modal(document.getElementById('transferModal')).show();

    // Build the transfer form client-side using branches already available
    // in the page's filter dropdown (initiateTransfer route is POST-only,
    // so there's no GET endpoint to fetch a rendered form from).
    const branchSelect = document.querySelector('#filterForm select[name="branch_id"]');
    let options = '<option value="">Select Branch</option>';
    if (branchSelect) {
        branchSelect.querySelectorAll('option').forEach(opt => {
            if (opt.value) options += `<option value="${opt.value}">${opt.textContent}</option>`;
        });
    }

    document.getElementById('transferModalBody').innerHTML = `
        <form id="transferForm" data-url="${url}">
            <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]')?.content || ''}">
            <div class="mb-3">
                <label class="form-label fw-semibold small required">Transfer To Branch</label>
                <select name="to_branch_id" class="form-select" required>${options}</select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small">Transfer Date</label>
                <input type="date" name="transfer_date" class="form-control" min="${new Date().toISOString().split('T')[0]}">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small">Notes</label>
                <textarea name="notes" class="form-control" rows="3" maxlength="500"></textarea>
            </div>
            <div class="alert alert-danger d-none" id="transferFormError"></div>
        </form>
    `;
}

function submitTransferModal() {
    const form = document.getElementById('transferForm');
    if (!form) return;

    const errorBox = document.getElementById('transferFormError');
    errorBox.classList.add('d-none');

    fetch(form.dataset.url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
        },
        body: new FormData(form),
    })
        .then(async r => {
            const data = await r.json().catch(() => ({}));
            if (!r.ok) throw data;
            return data;
        })
        .then(() => { window.location.reload(); })
        .catch(err => {
            let msg = err.message || 'Something went wrong.';
            if (err.errors) msg = Object.values(err.errors).flat().join(' ');
            errorBox.textContent = msg;
            errorBox.classList.remove('d-none');
        });
}

// ══════════════ Delete Modal ══════════════
let deleteContext = null;

function openDeleteModal(url, stockNumber, triggerEl) {
    deleteContext = { url, row: triggerEl.closest('tr') };
    document.getElementById('deleteModalStock').textContent = stockNumber;
    document.getElementById('deleteModalError').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function submitDeleteModal() {
    if (!deleteContext) return;

    const errorBox = document.getElementById('deleteModalError');
    const btn = document.getElementById('deleteModalConfirmBtn');
    errorBox.classList.add('d-none');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Removing...';

    fetch(deleteContext.url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
        },
        body: (() => { const fd = new FormData(); fd.append('_method', 'DELETE'); return fd; })(),
    })
        .then(async r => {
            const data = await r.json().catch(() => ({}));
            if (!r.ok) throw data;
            return data;
        })
        .then(() => {
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            deleteContext.row?.remove();
            deleteContext = null;
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-trash me-1"></i> Remove';
            errorBox.textContent = err.message || 'Failed to remove vehicle.';
            errorBox.classList.remove('d-none');
        });
}
</script>
@endpush
