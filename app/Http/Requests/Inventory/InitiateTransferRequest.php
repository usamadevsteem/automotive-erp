<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class InitiateTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transfer-vehicles');
    }

    public function rules(): array
    {
        $vehicle = $this->route('vehicle');

        return [
            'to_branch_id'  => [
                'required',
                'exists:branches,id',
                // Cannot transfer to the same branch
                function ($attribute, $value, $fail) use ($vehicle) {
                    if ((int) $value === (int) $vehicle->branch_id) {
                        $fail('Cannot transfer to the same branch.');
                    }
                },
            ],
            'transfer_date' => ['nullable', 'date', 'gte:today'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ];
    }
}
