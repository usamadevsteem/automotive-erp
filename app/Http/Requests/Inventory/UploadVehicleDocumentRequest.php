<?php

namespace App\Http\Requests\Inventory;

use App\Models\VehicleFileDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadVehicleDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit-vehicles');
    }

    public function rules(): array
    {
        return [
            'document_type'  => ['required', Rule::in(array_keys(VehicleFileDocument::DOCUMENT_TYPES))],
            'document_label' => ['required_if:document_type,other', 'nullable', 'string', 'max:100'],
            'file'           => [
                'required',
                'file',
                'max:10240',   // 10 MB
                'mimes:pdf,jpg,jpeg,png,webp',
            ],
            'expiry_date'    => [
                'nullable',
                'date',
                'after:today',
                Rule::requiredIf(
                    fn() => in_array(
                        $this->input('document_type'),
                        VehicleFileDocument::EXPIRABLE_TYPES
                    )
                ),
            ],
            'is_original'    => ['boolean'],
            'notes'          => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.max'              => 'Document file must not exceed 10MB.',
            'file.mimes'            => 'Only PDF, JPG, PNG, and WEBP files are accepted.',
            'expiry_date.required'  => 'Expiry date is required for this document type.',
            'expiry_date.after'     => 'Expiry date must be in the future.',
            'document_label.required_if' => 'Please provide a label for the document.',
        ];
    }
}
