<div class="row g-3">
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Quotation #</small>
        <div class="fw-semibold">{{ $quotation->quotation_number }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Status</small>
        <span class="badge bg-{{ $quotation->status_color }}-subtle text-{{ $quotation->status_color }}">
            {{ $quotation->status_label }}
        </span>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Customer</small>
        <div>{{ $quotation->customer->full_name }} — {{ $quotation->customer->mobile }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Vehicle</small>
        <div>{{ $quotation->vehicle->make->name }} {{ $quotation->vehicle->vehicleModel->name }} {{ $quotation->vehicle->year }}</div>
    </div>
    <div class="col-md-4">
        <small class="text-muted d-block mb-1">Sale Price</small>
        <div>PKR {{ number_format($quotation->sale_price) }}</div>
    </div>
    <div class="col-md-4">
        <small class="text-muted d-block mb-1">Discount</small>
        <div>PKR {{ number_format($quotation->discount) }}</div>
    </div>
    <div class="col-md-4">
        <small class="text-muted d-block mb-1">Net Price</small>
        <div class="fw-semibold">PKR {{ number_format($quotation->net_price) }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Valid Until</small>
        <div class="{{ $quotation->valid_until->isPast() ? 'text-danger fw-semibold' : '' }}">
            {{ $quotation->valid_until->format('d M Y') }}
        </div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Created By</small>
        <div>{{ $quotation->createdBy->name ?? '—' }}</div>
    </div>
    @if($quotation->notes)
    <div class="col-12">
        <small class="text-muted d-block mb-1">Notes</small>
        <div>{{ $quotation->notes }}</div>
    </div>
    @endif
</div>

@if(in_array($quotation->status, ['draft','sent']))
<hr>
<div class="d-flex gap-2 flex-wrap">
    @if($quotation->status === 'draft')
    <button type="button" class="btn btn-sm btn-outline-primary"
            onclick="updateQuotationStatus({{ $quotation->id }}, 'sent')">
        <i class="bi bi-send me-1"></i> Mark as Sent
    </button>
    @endif
    @if($quotation->status === 'sent')
    <button type="button" class="btn btn-sm btn-outline-success"
            onclick="updateQuotationStatus({{ $quotation->id }}, 'accepted')">
        <i class="bi bi-check-circle me-1"></i> Mark Accepted
    </button>
    <button type="button" class="btn btn-sm btn-outline-danger"
            onclick="updateQuotationStatus({{ $quotation->id }}, 'rejected')">
        <i class="bi bi-x-circle me-1"></i> Mark Rejected
    </button>
    @endif
</div>
@endif

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('quotations.show', $quotation) }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-up-right-square me-1"></i> Open Full Page
    </a>
</div>
