@extends('layouts.app')
@section('title','Leads')
@section('breadcrumb','CRM / Leads')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Leads</h4>
        <small class="text-muted">{{ number_format($leads->total()) }} total
            @if($overdue > 0)<span class="badge bg-danger ms-2">{{ $overdue }} overdue follow-ups</span>@endif
        </small>
    </div>
    @can('create-leads')
    <button type="button" class="btn btn-primary btn-sm" onclick="openLeadFormModal('{{ route('leads.create') }}', null)">
        <i class="bi bi-plus-circle me-1"></i> Add Lead
    </button>
    @endcan
</div>

{{-- Pipeline counts --}}
<div class="row g-2 mb-4">
    @foreach(\App\Models\Lead::STATUSES as $key => $meta)
    <div class="col">
        <a href="{{ route('leads.index', ['status' => $key]) }}"
           class="card border-0 shadow-sm text-decoration-none h-100 {{ ($filters['status'] ?? '') === $key ? 'border-primary border-2' : '' }}">
            <div class="card-body py-2 text-center">
                <div class="fw-bold fs-5">{{ $pipeline[$key] ?? 0 }}</div>
                <div class="text-{{ $meta['color'] }} small fw-semibold">{{ $meta['label'] }}</div>
            </div>
        </a>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('leads.index') }}">
            <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                           class="form-control form-control-sm" placeholder="Search name or phone...">
                </div>
                <div class="col-md-2">
                    <select name="source" class="form-select form-select-sm">
                        <option value="">All Sources</option>
                        @foreach(\App\Models\Lead::SOURCES as $k => $v)
                            <option value="{{ $k }}" {{ ($filters['source'] ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="assigned_to" class="form-select form-select-sm">
                        <option value="">All Assignees</option>
                        @foreach($salesUsers as $u)
                            <option value="{{ $u->id }}" {{ ($filters['assigned_to'] ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter</button>
                    <a href="{{ route('leads.index') }}" class="btn btn-light btn-sm"><i class="bi bi-x"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($leads->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-funnel fs-1 opacity-25 d-block mb-2"></i>
                <p>No leads found.</p>
                @can('create-leads')
                <button type="button" class="btn btn-primary btn-sm mt-2" onclick="openLeadFormModal('{{ route('leads.create') }}', null)">
                    <i class="bi bi-plus me-1"></i> Add Lead
                </button>
                @endcan
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Name</th>
                        <th>Phone</th>
                        <th>Interest</th>
                        <th>Source</th>
                        <th>Assigned</th>
                        <th>Follow-Up</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leads as $lead)
                    <tr>
                        <td class="ps-4">
                            <a href="{{ route('leads.show', $lead) }}"
                               class="fw-semibold text-decoration-none text-dark">{{ $lead->full_name }}</a>
                            @if($lead->customer_id)
                                <span class="badge bg-success-subtle text-success ms-1" style="font-size:10px;">Customer</span>
                            @endif
                        </td>
                        <td><a href="tel:{{ $lead->phone }}" class="text-decoration-none">{{ $lead->phone }}</a></td>
                        <td><small>{{ $lead->vehicle_interest ?? '—' }}</small></td>
                        <td><span class="badge bg-light text-dark">{{ \App\Models\Lead::SOURCES[$lead->source] ?? $lead->source }}</span></td>
                        <td><small>{{ $lead->assignedTo?->name ?? '—' }}</small></td>
                        <td>
                            @if($lead->next_follow_up)
                                <small class="{{ $lead->next_follow_up->isPast() ? 'text-danger fw-semibold' : 'text-muted' }}">
                                    <i class="bi bi-clock me-1"></i>{{ $lead->next_follow_up->format('d M, H:i') }}
                                </small>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $lead->status_color }}-subtle text-{{ $lead->status_color }}">
                                {{ $lead->status_label }}
                            </span>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <button type="button" class="btn btn-light btn-sm"
                                        onclick="openLeadViewModal('{{ route('leads.show', $lead) }}', '{{ $lead->full_name }}')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @can('edit-leads')
                                <button type="button" class="btn btn-light btn-sm"
                                        onclick="openLeadFormModal('{{ route('leads.edit', $lead) }}', '{{ $lead->full_name }}')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @endcan
                                @can('delete-leads')
                                <button type="button" class="btn btn-light btn-sm text-danger"
                                        onclick="openLeadDeleteModal('{{ route('leads.destroy', $lead) }}', '{{ $lead->full_name }}', this)">
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
            <small class="text-muted">{{ $leads->firstItem() }}–{{ $leads->lastItem() }} of {{ number_format($leads->total()) }}</small>
            {{ $leads->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection

{{-- ══════════════ View Details Modal ══════════════ --}}
<div class="modal fade" id="leadViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lead Details <span id="leadViewModalName" class="text-muted"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="leadViewModalBody">
                <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════ Add / Edit Modal ══════════════ --}}
<div class="modal fade" id="leadFormModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leadFormModalTitle">Add Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="leadFormModalBody">
                <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            </div>
            <div class="modal-footer">
                <div class="alert alert-danger py-2 px-3 mb-0 me-auto d-none" id="leadFormModalError"></div>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="leadFormModalSaveBtn" onclick="submitLeadFormModal()">
                    <i class="bi bi-check2 me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════ Delete Confirmation Modal ══════════════ --}}
<div class="modal fade" id="leadDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete <strong id="leadDeleteModalName"></strong>? This cannot be undone.</p>
                <div class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none" id="leadDeleteModalError"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="leadDeleteModalConfirmBtn" onclick="submitLeadDeleteModal()">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const leadCsrfToken = document.querySelector('meta[name=csrf-token]')?.content || '';

// ══════════════ View Modal ══════════════
function openLeadViewModal(url, name) {
    document.getElementById('leadViewModalName').textContent = '— ' + name;
    document.getElementById('leadViewModalBody').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    new bootstrap.Modal(document.getElementById('leadViewModal')).show();

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.text())
        .then(html => { document.getElementById('leadViewModalBody').innerHTML = html; })
        .catch(() => {
            document.getElementById('leadViewModalBody').innerHTML = '<div class="alert alert-danger">Failed to load lead details.</div>';
        });
}

// ══════════════ Add / Edit Modal ══════════════
function openLeadFormModal(url, name) {
    document.getElementById('leadFormModalTitle').textContent = name ? 'Edit Lead' : 'Add Lead';
    document.getElementById('leadFormModalBody').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    document.getElementById('leadFormModalError').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('leadFormModal')).show();

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.text())
        .then(html => { document.getElementById('leadFormModalBody').innerHTML = html; })
        .catch(() => {
            document.getElementById('leadFormModalBody').innerHTML = '<div class="alert alert-danger">Failed to load form.</div>';
        });
}

function submitLeadFormModal() {
    const form = document.querySelector('#leadFormModalBody #leadForm');
    if (!form) return;

    const errorBox = document.getElementById('leadFormModalError');
    const saveBtn  = document.getElementById('leadFormModalSaveBtn');
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
let leadDeleteContext = null;

function openLeadDeleteModal(url, name, triggerEl) {
    leadDeleteContext = { url, row: triggerEl.closest('tr') };
    document.getElementById('leadDeleteModalName').textContent = name;
    document.getElementById('leadDeleteModalError').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('leadDeleteModal')).show();
}

function submitLeadDeleteModal() {
    if (!leadDeleteContext) return;

    const errorBox = document.getElementById('leadDeleteModalError');
    const btn = document.getElementById('leadDeleteModalConfirmBtn');
    errorBox.classList.add('d-none');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

    const fd = new FormData();
    fd.append('_method', 'DELETE');

    fetch(leadDeleteContext.url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': leadCsrfToken,
        },
        body: fd,
    })
        .then(async r => {
            const data = await r.json().catch(() => ({}));
            if (!r.ok) throw data;
            return data;
        })
        .then(() => {
            bootstrap.Modal.getInstance(document.getElementById('leadDeleteModal')).hide();
            leadDeleteContext.row?.remove();
            leadDeleteContext = null;
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-trash me-1"></i> Delete';
            errorBox.textContent = err.message || 'Failed to delete lead.';
            errorBox.classList.remove('d-none');
        });
}
</script>
@endpush
