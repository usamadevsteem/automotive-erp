@extends('layouts.app')

@section('title', $vehicle->stock_number . ' — ' . $vehicle->full_name)
@section('breadcrumb', 'Inventory / ' . $vehicle->stock_number)

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────── --}}
<div class="d-flex align-items-start justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0">{{ $vehicle->full_name }}</h4>
            <span class="badge bg-{{ $vehicle->status_color }}-subtle text-{{ $vehicle->status_color }} border border-{{ $vehicle->status_color }}-subtle">
                {{ $vehicle->status_label }}
            </span>
        </div>
        <small class="text-muted">
            Stock# <strong>{{ $vehicle->stock_number }}</strong> ·
            Added by {{ $vehicle->addedBy->name }} on {{ $vehicle->created_at->format('d M Y') }}
        </small>
    </div>
    <div class="d-flex gap-2">
        @can('edit-vehicles')
        <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-light btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        @endcan
        <a href="{{ route('vehicles.index') }}" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

{{-- ── Image Gallery ────────────────────────────────────────────────── --}}

@include('inventory.partials.full-view-modal')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => window.initVehicleDetailTabs(document));
</script>
@endpush
