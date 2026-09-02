<?php

namespace App\Http\Requests\Inventory;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit-vehicles');
    }

    public function rules(): array
    {
        return [
            'make_id'             => ['required', 'exists:vehicle_makes,id'],
            'model_id'            => ['required', 'exists:vehicle_models,id'],
            'variant_id'          => ['nullable', 'exists:vehicle_variants,id'],
            'category'            => ['required', Rule::in(array_keys(Vehicle::CATEGORIES))],
            'branch_id'           => ['required', 'exists:branches,id'],
            'year'                => ['required', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'registration_year'   => ['nullable', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'color'               => ['nullable', 'string', 'max:50'],
            'mileage'             => ['required', 'integer', 'min:0'],
            'fuel_type'           => ['required', Rule::in(array_keys(Vehicle::FUEL_TYPES))],
            'transmission'        => ['required', Rule::in(array_keys(Vehicle::TRANSMISSIONS))],
            'engine_capacity'     => ['nullable', 'string', 'max:20'],
            'condition_grade'     => ['required', Rule::in(array_keys(Vehicle::CONDITIONS))],
            'registration_number' => ['nullable', 'string', 'max:30'],
            'chassis_number'      => ['nullable', 'string', 'max:50'],
            'engine_number'       => ['nullable', 'string', 'max:50'],
            'vin_number'          => ['nullable', 'string', 'max:20'],
            'import_status'       => ['required', Rule::in(array_keys(Vehicle::IMPORT_STATUSES))],
            'auction_grade'       => ['nullable', 'string', 'max:10'],
            'purchase_price'      => ['required', 'numeric', 'min:0'],
            'repair_cost'         => ['nullable', 'numeric', 'min:0'],
            'misc_cost'           => ['nullable', 'numeric', 'min:0'],
            'sale_price'          => ['required', 'numeric', 'min:0'],
            'min_sale_price'      => ['nullable', 'numeric', 'min:0'],
            'notes'               => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
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
                $field => $value === '' || $value === null
                    ? null
                    : str_replace(',', '', $value),
            ]);
        }
    }

    $this->mergeIfMissing([
        'repair_cost'    => 0,
        'misc_cost'      => 0,
        'min_sale_price' => 0,
    ]);
    }
}
