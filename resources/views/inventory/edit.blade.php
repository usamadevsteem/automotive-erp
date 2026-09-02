@extends('layouts.app')

@section('title', 'Edit ' . $vehicle->stock_number)
@section('breadcrumb', 'Inventory / ' . $vehicle->stock_number . ' / Edit')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Edit Vehicle</h4>
        <small class="text-muted">{{ $vehicle->full_name }} &mdash; {{ $vehicle->stock_number }}</small>
    </div>
    <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Vehicle
    </a>
</div>

@include('inventory.partials.edit-form')

<div class="d-flex justify-content-end gap-2 mt-3 mb-5">
    <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-light">Cancel</a>
    <button type="submit" form="vehicleForm" class="btn btn-primary">
        <i class="bi bi-check-circle me-1"></i> Save Changes
    </button>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => window.initVehicleEditForm(document));
</script>
@endpush
