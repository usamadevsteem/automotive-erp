@extends('layouts.app')
@section('title','Vendors')
@section('breadcrumb','Accounting / Vendors')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Vendors</h4>
    @can('manage-vendors')
    <button type="button" class="btn btn-primary btn-sm" onclick="openVendorFormModal('{{ route('vendors.create') }}', null)">
        <i class="bi bi-plus-circle me-1"></i> Add Vendor
    </button>
    @endcan
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th class="ps-4">Name</th><th>Type</th><th>Phone</th><th>City</th><th>Balance</th>
                    @can('manage-vendors')<th class="pe-4 text-end">Actions</th>@endcan
                </tr>
            </thead>
            <tbody>
                @forelse($vendors as $v)
                <tr class="{{ !$v->is_active ? 'opacity-50' : '' }}">
                    <td class="ps-4 fw-semibold">
                        {{ $v->name }}
                        @if(!$v->is_active)<span class="badge bg-warning-subtle text-warning ms-1" style="font-size:10px;">Inactive</span>@endif
                    </td>
                    <td><span class="badge bg-light text-dark">{{ \App\Models\Vendor::TYPES[$v->vendor_type] }}</span></td>
                    <td>{{ $v->phone ?? '—' }}</td>
                    <td>{{ $v->city ?? '—' }}</td>
                    <td>PKR {{ number_format($v->opening_balance) }}</td>
                    @can('manage-vendors')
                    <td class="pe-4 text-end">
                        <button type="button" class="btn btn-light btn-sm" onclick="openVendorFormModal('{{ route('vendors.edit', $v) }}', '{{ $v->name }}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-light btn-sm text-danger" onclick="openVendorDeleteModal('{{ route('vendors.destroy', $v) }}', '{{ $v->name }}', this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                    @endcan
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">No vendors found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="px-4 py-3 border-top">{{ $vendors->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

<div class="modal fade" id="vendorFormModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="vendorFormModalTitle">Add Vendor</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="vendorFormModalBody"><div class="text-center py-5"><div class="spinner-border text-primary"></div></div></div>
            <div class="modal-footer">
                <div class="alert alert-danger py-2 px-3 mb-0 me-auto d-none" id="vendorFormModalError"></div>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="vendorFormModalSaveBtn" onclick="submitVendorFormModal()">
                    <i class="bi bi-check2 me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="vendorDeleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Delete Vendor</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p class="mb-0">Delete vendor <strong id="vendorDeleteModalName"></strong>?</p>
                <div class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none" id="vendorDeleteModalError"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="vendorDeleteModalConfirmBtn" onclick="submitVendorDeleteModal()">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
