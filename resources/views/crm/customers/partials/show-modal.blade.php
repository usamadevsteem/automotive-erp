<div class="row g-3">
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Full Name</small>
        <div class="fw-semibold">{{ $customer->full_name }}</div>
        @if($customer->father_husband_name)
            <small class="text-muted">S/O {{ $customer->father_husband_name }}</small>
        @endif
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Type</small>
        <span class="badge bg-primary-subtle text-primary">{{ \App\Models\Customer::TYPES[$customer->customer_type] }}</span>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Mobile</small>
        <div><a href="tel:{{ $customer->mobile }}" class="text-decoration-none">{{ $customer->mobile }}</a></div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Alt Mobile</small>
        <div>{{ $customer->mobile_alt ?? '—' }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">CNIC</small>
        <div class="font-monospace">{{ $customer->cnic ?? '—' }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Email</small>
        <div>{{ $customer->email ?? '—' }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">City</small>
        <div>{{ $customer->city ?? '—' }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Source</small>
        <span class="badge bg-light text-dark">{{ $customer->source_label }}</span>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Assigned To</small>
        <div>{{ $customer->assignedTo?->name ?? 'Unassigned' }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Tax Status</small>
        <div class="text-capitalize">{{ str_replace('_', ' ', $customer->tax_status) }}</div>
    </div>
    @if($customer->address)
    <div class="col-12">
        <small class="text-muted d-block mb-1">Address</small>
        <div>{{ $customer->address }}</div>
    </div>
    @endif
    @if($customer->notes)
    <div class="col-12">
        <small class="text-muted d-block mb-1">Notes</small>
        <div>{{ $customer->notes }}</div>
    </div>
    @endif
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('customers.show', $customer) }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-up-right-square me-1"></i> Open Full Page
    </a>
</div>
