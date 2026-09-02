<?php

namespace App\Http\Requests\Inventory;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-vehicles');
    }

    public function rules(): array
    {
        return [
            // ── Classification ────────────────────────────────────────
            'make_id'           => ['required', 'exists:vehicle_makes,id'],
            'model_id'          => ['required', 'exists:vehicle_models,id'],
            'variant_id'        => ['nullable', 'exists:vehicle_variants,id'],
            'category'          => ['required', Rule::in(array_keys(Vehicle::CATEGORIES))],
            'branch_id'         => ['required', 'exists:branches,id'],

            // ── Physical ──────────────────────────────────────────────
            'year'              => ['required', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'registration_year' => ['nullable', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'color'             => ['nullable', 'string', 'max:50'],
            'mileage'           => ['required', 'integer', 'min:0', 'max:9999999'],
            'fuel_type'         => ['required', Rule::in(array_keys(Vehicle::FUEL_TYPES))],
            'transmission'      => ['required', Rule::in(array_keys(Vehicle::TRANSMISSIONS))],
            'engine_capacity'   => ['nullable', 'string', 'max:20'],
            'condition_grade'   => ['required', Rule::in(array_keys(Vehicle::CONDITIONS))],

            // ── Identity ──────────────────────────────────────────────
            'registration_number' => ['nullable', 'string', 'max:30'],
            'chassis_number'      => ['nullable', 'string', 'max:50'],
            'engine_number'       => ['nullable', 'string', 'max:50'],
            'vin_number'          => ['nullable', 'string', 'max:20'],

            // ── Import ────────────────────────────────────────────────
            'import_status'     => ['required', Rule::in(array_keys(Vehicle::IMPORT_STATUSES))],
            'auction_grade'     => ['nullable', 'string', 'max:10'],

            // ── Pricing ───────────────────────────────────────────────
            'purchase_price'    => ['required', 'numeric', 'min:0'],
            'repair_cost'       => ['nullable', 'numeric', 'min:0'],
            'misc_cost'         => ['nullable', 'numeric', 'min:0'],
            'sale_price'        => ['required', 'numeric', 'min:0'],
            'min_sale_price'    => ['nullable', 'numeric', 'min:0', 'lte:sale_price'],

            // ── Notes ─────────────────────────────────────────────────
            'notes' => ['nullable', 'string', 'max:2000'],

            // ── Vehicle Images ─────────────────────────────────────────
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120',],
        ];
    }

    public function messages(): array
    {
        return [
            'make_id.required'       => 'Please select a vehicle make.',
            'model_id.required'      => 'Please select a vehicle model.',
            'year.required'          => 'Please enter the vehicle year.',
            'mileage.required'       => 'Please enter the mileage.',
            'fuel_type.required'     => 'Please select a fuel type.',
            'transmission.required'  => 'Please select the transmission type.',
            'purchase_price.required'=> 'Please enter the purchase price.',
            'sale_price.required'    => 'Please enter the sale price.',
            'min_sale_price.lte'     => 'Minimum sale price cannot exceed the sale price.',
            'category.required'      => 'Please select a vehicle category.',
            'import_status.required' => 'Please select the import status.',
        ];
    }

    protected function prepareForValidation(): void
    {
    // Normalize numeric fields — remove commas and convert empty values to 0
    $numeric = [
        'purchase_price',
        'repair_cost',
        'misc_cost',
        'sale_price',
        'min_sale_price',
    ];

    foreach ($numeric as $field) {
        if ($this->has($field)) {
            $value = $this->input($field);

            $this->merge([
                $field => ($value === null || $value === '')
                    ? 0
                    : str_replace(',', '', $value),
            ]);
        }
    }

    // Default optional numeric fields to zero
    foreach (['repair_cost', 'misc_cost', 'min_sale_price'] as $field) {
        if (!$this->has($field) || $this->input($field) === null || $this->input($field) === '') {
            $this->merge([
                $field => 0,
            ]);
        }
    }
  }
}
