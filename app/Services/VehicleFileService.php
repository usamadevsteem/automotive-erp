<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleFileDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VehicleFileService
{
    /**
     * Upload a document to the vehicle's digital file.
     */
    public function upload(Vehicle $vehicle, UploadedFile $file, array $data): VehicleFileDocument
    {
        // Store to S3: tenants/{id}/vehicles/{vehicleId}/files/{filename}
        $path = $file->store(
            "tenants/{$vehicle->tenant_id}/vehicles/{$vehicle->id}/files",
            's3'
        );

        return VehicleFileDocument::create([
            'tenant_id'      => $vehicle->tenant_id,
            'vehicle_id'     => $vehicle->id,
            'document_type'  => $data['document_type'],
            'document_label' => $data['document_label'] ?? null,
            'file_path'      => $path,
            'file_name'      => $file->getClientOriginalName(),
            'file_size'      => $file->getSize(),
            'mime_type'      => $file->getMimeType(),
            'expiry_date'    => $data['expiry_date'] ?? null,
            'is_original'    => $data['is_original'] ?? false,
            'notes'          => $data['notes'] ?? null,
            'uploaded_by'    => auth()->id(),
            'uploaded_at'    => now(),
        ]);
    }

    /**
     * Delete a document from the vehicle file.
     */
    public function delete(VehicleFileDocument $document): void
    {
        Storage::disk('s3')->delete($document->file_path);
        $document->delete();
    }

    /**
     * Mark a document as verified.
     */
    public function verify(VehicleFileDocument $document): VehicleFileDocument
    {
        $document->update([
            'is_verified' => true,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return $document->fresh();
    }

    /**
     * Get the full checklist — required docs + uploaded status.
     */
    public function getChecklist(Vehicle $vehicle): array
    {
        $vehicle->load('fileDocuments');

        $uploadedByType = $vehicle->fileDocuments
            ->groupBy('document_type')
            ->map(fn($docs) => $docs->first());

        // Required docs for all vehicles
        $required = [
            'registration_book'    => ['label' => 'Registration Book',    'required' => true],
            'smart_card'           => ['label' => 'Smart Card',            'required' => true],
            'transfer_letter'      => ['label' => 'Transfer Letter',       'required' => true],
            'open_transfer_letter' => ['label' => 'Open Transfer Letter',  'required' => false],
            'insurance'            => ['label' => 'Insurance Policy',      'required' => false],
            'token_tax'            => ['label' => 'Token Tax',             'required' => false],
        ];

        // Additional required docs for imported/auction vehicles
        if ($vehicle->import_status !== 'local') {
            $required['auction_sheet']     = ['label' => 'Auction Sheet',     'required' => true];
            $required['import_bill']       = ['label' => 'Import Bill',       'required' => true];
            $required['customs_clearance'] = ['label' => 'Customs Clearance', 'required' => true];
            $required['biometric_slip']    = ['label' => 'Biometric Slip',    'required' => false];
        }

        $checklist = [];

        foreach ($required as $type => $meta) {
            $doc        = $uploadedByType->get($type);
            $checklist[] = [
                'type'         => $type,
                'label'        => $meta['label'],
                'required'     => $meta['required'],
                'uploaded'     => (bool) $doc,
                'document'     => $doc,
                'is_verified'  => $doc?->is_verified ?? false,
                'is_expired'   => $doc?->isExpired() ?? false,
                'expiry_status'=> $doc?->expiry_status ?? null,
            ];
        }

        // Append any extra uploaded docs not in the required list
        foreach ($vehicle->fileDocuments as $doc) {
            if (!array_key_exists($doc->document_type, $required)) {
                $checklist[] = [
                    'type'         => $doc->document_type,
                    'label'        => $doc->type_label,
                    'required'     => false,
                    'uploaded'     => true,
                    'document'     => $doc,
                    'is_verified'  => $doc->is_verified,
                    'is_expired'   => $doc->isExpired(),
                    'expiry_status'=> $doc->expiry_status,
                ];
            }
        }

        return $checklist;
    }

    /**
     * Calculate how complete the vehicle file is (percentage).
     */
    public function getCompleteness(Vehicle $vehicle): int
    {
        $checklist = $this->getChecklist($vehicle);
        $required  = array_filter($checklist, fn($item) => $item['required']);
        $total     = count($required);

        if ($total === 0) return 100;

        $uploaded = count(array_filter($required, fn($item) => $item['uploaded']));

        return (int) round(($uploaded / $total) * 100);
    }

    /**
     * Get documents expiring within $days days across all vehicles for this tenant.
     * Used by the scheduler alert command.
     */
    public function getExpiringSoon(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return VehicleFileDocument::with(['vehicle.make', 'vehicle.vehicleModel', 'vehicle.branch'])
            ->whereIn('document_type', VehicleFileDocument::EXPIRABLE_TYPES)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [today(), today()->addDays($days)])
            ->orderBy('expiry_date')
            ->get();
    }
}
