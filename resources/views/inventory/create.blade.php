@extends('layouts.app')

@section('title', 'Add Vehicle')
@section('breadcrumb', 'Inventory / Add Vehicle')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Add Vehicle</h4>
        <small class="text-muted">Add a new vehicle to inventory</small>
    </div>
    <a href="{{ route('vehicles.index') }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Inventory
    </a>
</div>

@include('inventory.partials.create-form')

<div class="d-flex justify-content-end gap-2 mt-3 mb-5">
    <a href="{{ route('vehicles.index') }}" class="btn btn-light">Cancel</a>
    <button type="submit" form="vehicleForm" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Add to Inventory
    </button>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => window.initVehicleEditForm(document));
</script>
@endpush
