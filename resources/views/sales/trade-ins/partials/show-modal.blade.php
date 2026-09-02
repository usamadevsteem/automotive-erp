<div class="row g-3">
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Customer</small>
        <div class="fw-semibold">{{ $tradeIn->customer->full_name }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Status</small>
        <span class="badge bg-{{ $tradeIn->status_color }}-subtle text-{{ $tradeIn->status_color }}">
            {{ $tradeIn->status_label }}
        </span>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Trade-In Vehicle</small>
        <div>{{ $tradeIn->trade_make }} {{ $tradeIn->trade_model }} {{ $tradeIn->trade_year }}</div>
        <small class="text-muted">{{ $tradeIn->trade_color ?? '—' }} · {{ number_format($tradeIn->trade_mileage ?? 0) }} km</small>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">New Vehicle</small>
        <div>{{ $tradeIn->newVehicle->make->name }} {{ $tradeIn->newVehicle->vehicleModel->name }}</div>
    </div>
    <div class="col-md-4">
        <small class="text-muted d-block mb-1">Market Value</small>
        <div>PKR {{ number_format($tradeIn->market_value) }}</div>
    </div>
    <div class="col-md-4">
        <small class="text-muted d-block mb-1">Offered Value</small>
        <div class="fw-semibold">PKR {{ number_format($tradeIn->offered_value) }}</div>
    </div>
    <div class="col-md-4">
        <small class="text-muted d-block mb-1">Approved Value</small>
        <div>{{ $tradeIn->approved_value ? 'PKR ' . number_format($tradeIn->approved_value) : '—' }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Condition</small>
        <div class="text-capitalize">{{ $tradeIn->trade_condition }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Evaluated By</small>
        <div>{{ $tradeIn->evaluatedBy->name ?? '—' }}</div>
    </div>
    @if($tradeIn->notes)
    <div class="col-12">
        <small class="text-muted d-block mb-1">Notes</small>
        <div>{{ $tradeIn->notes }}</div>
    </div>
    @endif
</div>

@if($tradeIn->status === 'pending')
<hr>
<h6 class="small fw-semibold text-muted text-uppercase mb-2">Approve Trade-In</h6>
<div class="row g-2">
    <div class="col-md-6">
        <input type="number" id="tradeInApprovedValue{{ $tradeIn->id }}" class="form-control form-control-sm"
               placeholder="Approved value (PKR)" value="{{ $tradeIn->offered_value }}" min="0">
    </div>
    <div class="col-md-6">
        <button type="button" class="btn btn-success btn-sm" onclick="submitTradeInApproval({{ $tradeIn->id }})">
            <i class="bi bi-check-circle me-1"></i> Approve
        </button>
    </div>
</div>
<div class="alert alert-danger py-2 px-3 mt-2 mb-0 d-none" id="tradeInApproveError"></div>
@endif

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('trade-ins.show', $tradeIn) }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-up-right-square me-1"></i> Open Full Page
    </a>
</div>
