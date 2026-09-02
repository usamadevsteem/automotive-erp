<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateImportCostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit-vehicles');
    }

    public function rules(): array
    {
        return [
            'auction_price'          => ['nullable', 'numeric', 'min:0'],
            'auction_charges'        => ['nullable', 'numeric', 'min:0'],
            'shipping_charges'       => ['nullable', 'numeric', 'min:0'],
            'clearing_charges'       => ['nullable', 'numeric', 'min:0'],
            'customs_duty'           => ['nullable', 'numeric', 'min:0'],
            'port_charges'           => ['nullable', 'numeric', 'min:0'],
            'registration_charges'   => ['nullable', 'numeric', 'min:0'],
            'transportation_charges' => ['nullable', 'numeric', 'min:0'],
            'other_charges'          => ['nullable', 'numeric', 'min:0'],
            'notes'                  => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $fields = [
            'auction_price', 'auction_charges', 'shipping_charges',
            'clearing_charges', 'customs_duty', 'port_charges',
            'registration_charges', 'transportation_charges', 'other_charges',
        ];

        foreach ($fields as $field) {
            if ($this->has($field)) {
                $this->merge([$field => str_replace(',', '', $this->input($field, 0))]);
            }
        }
    }
}
