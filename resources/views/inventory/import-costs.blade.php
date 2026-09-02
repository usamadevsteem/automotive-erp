@extends('layouts.app')

@section('title', 'Import Costs — ' . $vehicle->stock_number)
@section('breadcrumb', 'Inventory / ' . $vehicle->stock_number . ' / Import Costs')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Import Cost Calculator</h4>
        <small class="text-muted">
            {{ $vehicle->stock_number }} · {{ $vehicle->full_name }}
        </small>
    </div>
    <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Vehicle
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <form method="POST" action="{{ route('vehicles.import-costs.update', $vehicle) }}">
            @csrf
            @method('PUT')

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-receipt text-primary me-2"></i>Cost Breakdown
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $fields = \App\Models\VehicleImportCost::COST_LINES;
                    @endphp

                    @foreach($fields as $field => $label)
                    <div class="row align-items-center mb-3">
                        <div class="col-md-5">
                            <label class="form-label mb-0 fw-semibold small">{{ $label }}</label>
                        </div>
                        <div class="col-md-7">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted">PKR</span>
                                <input type="text"
                                       name="{{ $field }}"
                                       id="field_{{ $field }}"
                                       value="{{ old($field, number_format($importCost->{$field} ?? 0, 0, '.', '')) }}"
                                       class="form-control text-end cost-input"
                                       placeholder="0">
                            </div>
                        </div>
                    </div>
                    @endforeach

                    {{-- Total --}}
                    <hr>
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <span class="fw-bold">Total Import / Landing Cost</span>
                        </div>
                        <div class="col-md-7">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-primary text-white">PKR</span>
                                <input type="text" id="totalDisplay"
                                       class="form-control text-end fw-bold bg-light"
                                       readonly
                                       value="{{ number_format($importCost->total_import_cost ?? 0) }}">
                            </div>
                            <div class="form-text">
                                Auto-calculated · synced to vehicle's landing cost.
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="mt-3">
                        <label class="form-label fw-semibold small">Notes</label>
                        <textarea name="notes" rows="2"
                                  class="form-control form-control-sm"
                                  placeholder="Any notes about these import costs...">{{ old('notes', $importCost->notes ?? '') }}</textarea>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2 me-1"></i> Save Import Costs
                    </button>
                    <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-light">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Summary panel --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm sticky-top" style="top:80px;">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-calculator text-primary me-2"></i>Profit Summary
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted">Purchase Price</td>
                        <td class="text-end fw-semibold">
                            PKR {{ number_format($vehicle->purchase_price) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Landing Cost</td>
                        <td class="text-end" id="landingCostRow">
                            PKR {{ number_format($vehicle->landing_cost) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Repair Cost</td>
                        <td class="text-end">PKR {{ number_format($vehicle->repair_cost) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Misc Cost</td>
                        <td class="text-end">PKR {{ number_format($vehicle->misc_cost) }}</td>
                    </tr>
                    <tr class="table-light">
                        <td class="fw-bold">Total Cost</td>
                        <td class="text-end fw-bold" id="totalCostRow">
                            PKR {{ number_format($vehicle->total_cost) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Sale Price</td>
                        <td class="text-end text-primary fw-bold">
                            PKR {{ number_format($vehicle->sale_price) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Expected Profit</td>
                        <td class="text-end fw-bold" id="profitRow">
                            <span class="{{ $vehicle->expected_profit >= 0 ? 'text-success' : 'text-danger' }}">
                                PKR {{ number_format($vehicle->expected_profit) }}
                            </span>
                        </td>
                    </tr>
                </table>

                <div class="alert alert-info small py-2 mt-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Saving these costs will automatically update the vehicle's
                    <strong>landing cost</strong> and recalculate
                    <strong>total cost</strong> and <strong>expected profit</strong>.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const purchasePrice = {{ $vehicle->purchase_price }};
const repairCost    = {{ $vehicle->repair_cost }};
const miscCost      = {{ $vehicle->misc_cost }};
const salePrice     = {{ $vehicle->sale_price }};

function parseNum(el) {
    return parseFloat(el.value.replace(/,/g, '') || 0) || 0;
}

function recalculate() {
    let total = 0;
    document.querySelectorAll('.cost-input').forEach(el => {
        total += parseNum(el);
    });

    document.getElementById('totalDisplay').value =
        Math.round(total).toLocaleString('en-PK');

    // Update summary panel
    const totalCost = purchasePrice + total + repairCost + miscCost;
    const profit    = salePrice - totalCost;

    document.getElementById('landingCostRow').textContent =
        'PKR ' + Math.round(total).toLocaleString('en-PK');
    document.getElementById('totalCostRow').textContent =
        'PKR ' + Math.round(totalCost).toLocaleString('en-PK');

    const profitEl = document.getElementById('profitRow');
    profitEl.innerHTML = `<span class="${profit >= 0 ? 'text-success' : 'text-danger'} fw-bold">
        PKR ${Math.round(profit).toLocaleString('en-PK')}
    </span>`;
}

document.querySelectorAll('.cost-input').forEach(el => {
    el.addEventListener('input', recalculate);
});

recalculate();
</script>
@endpush
