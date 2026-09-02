@can('view-vehicle-cost')
@php $showCost = true; @endphp
@else
@php $showCost = false; @endphp
@endcan

@php $quickViewImages = $vehicle->getMedia('images'); @endphp
@if($quickViewImages->isNotEmpty())
<div class="d-flex gap-2 mb-3 overflow-auto pb-1">
    @foreach($quickViewImages as $img)
    <img src="{{ $img->getUrl('thumb') }}"
         class="rounded {{ $img->getCustomProperty('is_featured') ? 'border border-primary border-2' : 'border' }}"
         style="width:80px; height:56px; object-fit:cover; cursor:pointer; flex-shrink:0;"
         onclick="window.open('{{ $img->getUrl('gallery') }}', '_blank')">
    @endforeach
</div>
@endif

<div class="row g-3">
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Stock #</small>
        <div class="fw-semibold">{{ $vehicle->stock_number }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Status</small>
        <span class="badge bg-{{ $vehicle->status_color }}-subtle text-{{ $vehicle->status_color }} border border-{{ $vehicle->status_color }}-subtle">
            {{ $vehicle->status_label }}
        </span>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Vehicle</small>
        <div class="fw-semibold">{{ $vehicle->make->name }} {{ $vehicle->vehicleModel->name }}</div>
        <small class="text-muted">{{ $vehicle->variant?->name ?? '—' }}</small>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Year / Color</small>
        <div>{{ $vehicle->year }} &middot; {{ $vehicle->color ?? '—' }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Mileage</small>
        <div>{{ $vehicle->mileage_formatted }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Branch</small>
        <div>{{ $vehicle->branch->name }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Fuel / Transmission</small>
        <div class="text-capitalize">{{ $vehicle->fuel_type }} &middot; {{ $vehicle->transmission }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Chassis #</small>
        <div>{{ $vehicle->chassis_number ?? '—' }}</div>
    </div>

    <div class="col-12"><hr class="my-1"></div>

    @if($showCost)
    <div class="col-md-4">
        <small class="text-muted d-block mb-1">Total Cost</small>
        <div class="fw-semibold">{{ $vehicle->total_cost_formatted }}</div>
    </div>
    @endif
    <div class="col-md-4">
        <small class="text-muted d-block mb-1">Sale Price</small>
        <div class="fw-semibold">{{ $vehicle->sale_price_formatted }}</div>
    </div>
    @if($showCost)
    <div class="col-md-4">
        <small class="text-muted d-block mb-1">Expected Profit</small>
        <div class="fw-semibold {{ $vehicle->expected_profit >= 0 ? 'text-success' : 'text-danger' }}">
            PKR {{ number_format($vehicle->expected_profit) }}
        </div>
    </div>
    @endif

    <div class="col-12"><hr class="my-1"></div>

    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Document Completeness</small>
        <div class="progress" style="height: 8px;">
            <div class="progress-bar" style="width: {{ $completeness }}%"></div>
        </div>
        <small class="text-muted">{{ $completeness }}% complete</small>
    </div>
    @if($qrImageUrl)
    <div class="col-md-6 text-md-end">
        <img src="{{ $qrImageUrl }}" alt="QR Code" style="max-height: 90px;">
    </div>
    @endif
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-up-right-square me-1"></i> Open Full Page
    </a>
</div>
