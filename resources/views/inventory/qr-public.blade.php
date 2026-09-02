<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $vehicle->full_name }} — {{ $vehicle->branch->tenant->company_name }}</title>
    <meta name="description" content="{{ $vehicle->full_name }}, {{ $vehicle->mileage_formatted }}, {{ $vehicle->sale_price_formatted }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body { background: #f8f9fa; }
        .dealer-header { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); }
        .price-badge { font-size: 1.5rem; }
        .spec-card { border-left: 3px solid #0d6efd; }
    </style>
</head>
<body>

{{-- Dealer Header --}}
<div class="dealer-header text-white py-3 px-4">
    <div class="container-sm">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="fw-bold fs-6">{{ $vehicle->branch->tenant->company_name }}</div>
                <div class="text-white-50 small">{{ $vehicle->branch->name }}</div>
            </div>
            @if($vehicle->branch->tenant->logo_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($vehicle->branch->tenant->logo_path) }}"
                     alt="Logo" style="max-height:40px;">
            @endif
        </div>
    </div>
</div>

<div class="container-sm py-4">

    {{-- Vehicle Header --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="fw-bold mb-1">{{ $vehicle->full_name }}</h5>
                    <div class="text-muted small">
                        Stock# {{ $vehicle->stock_number }} ·
                        {{ \App\Models\Vehicle::CATEGORIES[$vehicle->category] }}
                    </div>
                </div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                    {{ $vehicle->status_label }}
                </span>
            </div>

            <div class="price-badge fw-bold text-primary mt-3">
                {{ $vehicle->sale_price_formatted }}
            </div>
        </div>
    </div>

    {{-- Key Specs --}}
    <div class="row g-2 mb-3">
        @php
            $specs = [
                ['icon' => 'bi-calendar3',      'label' => 'Year',         'value' => $vehicle->year],
                ['icon' => 'bi-speedometer2',   'label' => 'Mileage',      'value' => $vehicle->mileage_formatted],
                ['icon' => 'bi-fuel-pump',      'label' => 'Fuel',         'value' => \App\Models\Vehicle::FUEL_TYPES[$vehicle->fuel_type]],
                ['icon' => 'bi-gear',           'label' => 'Transmission', 'value' => \App\Models\Vehicle::TRANSMISSIONS[$vehicle->transmission]],
                ['icon' => 'bi-palette',        'label' => 'Color',        'value' => $vehicle->color ?? 'N/A'],
                ['icon' => 'bi-clipboard-check','label' => 'Condition',    'value' => \App\Models\Vehicle::CONDITIONS[$vehicle->condition_grade]],
            ];
        @endphp

        @foreach($specs as $spec)
        <div class="col-6">
            <div class="card border-0 bg-white shadow-sm h-100 spec-card">
                <div class="card-body py-2 px-3">
                    <div class="text-muted" style="font-size:11px;">
                        <i class="{{ $spec['icon'] }} me-1"></i>{{ $spec['label'] }}
                    </div>
                    <div class="fw-semibold small">{{ $spec['value'] }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Identity --}}
    @if($vehicle->registration_number || $vehicle->chassis_number)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6 class="fw-semibold mb-3 text-muted text-uppercase small" style="letter-spacing:1px">
                Vehicle Identity
            </h6>
            @if($vehicle->registration_number)
            <div class="d-flex justify-content-between py-1 border-bottom">
                <span class="text-muted small">Registration</span>
                <span class="fw-semibold font-monospace small">{{ $vehicle->registration_number }}</span>
            </div>
            @endif
            @if($vehicle->chassis_number)
            <div class="d-flex justify-content-between py-1 border-bottom">
                <span class="text-muted small">Chassis #</span>
                <span class="fw-semibold font-monospace small">{{ $vehicle->chassis_number }}</span>
            </div>
            @endif
            @if($vehicle->engine_number)
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted small">Engine #</span>
                <span class="fw-semibold font-monospace small">{{ $vehicle->engine_number }}</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Contact Dealer --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h6 class="fw-semibold mb-3">Interested in this vehicle?</h6>

            @if($vehicle->branch->phone)
            <a href="tel:{{ $vehicle->branch->phone }}"
               class="btn btn-primary w-100 mb-2">
                <i class="bi bi-telephone-fill me-2"></i>
                Call {{ $vehicle->branch->phone }}
            </a>
            @endif

            @if($vehicle->branch->phone)
            <a href="https://wa.me/92{{ ltrim($vehicle->branch->phone, '0') }}?text=Hi, I'm interested in {{ urlencode($vehicle->full_name) }} (Stock# {{ $vehicle->stock_number }})"
               class="btn btn-success w-100"
               target="_blank">
                <i class="bi bi-whatsapp me-2"></i> WhatsApp Inquiry
            </a>
            @endif
        </div>
    </div>

    {{-- Branch Info --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h6 class="fw-semibold mb-2">
                <i class="bi bi-building me-1"></i> {{ $vehicle->branch->name }}
            </h6>
            @if($vehicle->branch->address)
            <p class="text-muted small mb-1">
                <i class="bi bi-geo-alt me-1"></i>{{ $vehicle->branch->address }}
                @if($vehicle->branch->city) , {{ $vehicle->branch->city }} @endif
            </p>
            @endif
            @if($vehicle->branch->email)
            <p class="text-muted small mb-0">
                <i class="bi bi-envelope me-1"></i>{{ $vehicle->branch->email }}
            </p>
            @endif
        </div>
    </div>

    <div class="text-center mt-4 mb-2">
        <small class="text-muted">
            Powered by AutoDealer ERP ·
            Scanned {{ now()->format('d M Y') }}
        </small>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
