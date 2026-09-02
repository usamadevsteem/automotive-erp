<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleQrScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    /**
     * Generate QR code for a vehicle.
     * Stores a PNG image to S3 and saves the UUID on the vehicle.
     */
    public function generate(Vehicle $vehicle): Vehicle
    {
        // Generate a unique UUID for the QR code
        $code = (string) Str::uuid();

        // The public URL the QR code points to
        $publicUrl = route('vehicles.qr.public', $code);

        // Generate QR image as PNG (300x300)
        $qrImage = QrCode::format('png')
            ->size(300)
            ->errorCorrection('M')
            ->generate($publicUrl);

        // Store to S3: tenants/{id}/qr/{code}.png
        $path = "tenants/{$vehicle->tenant_id}/qr/{$code}.png";
        Storage::disk('s3')->put($path, $qrImage, 'public');

        $vehicle->update([
            'qr_code'      => $code,
            'qr_image_path'=> $path,
        ]);

        return $vehicle->fresh();
    }

    /**
     * Regenerate QR code (e.g. if vehicle is transferred to new tenant).
     */
    public function regenerate(Vehicle $vehicle): Vehicle
    {
        // Delete old QR image if exists
        if ($vehicle->qr_image_path) {
            Storage::disk('s3')->delete($vehicle->qr_image_path);
        }

        return $this->generate($vehicle);
    }

    /**
     * Log a QR code scan — called from the public QR landing page.
     */
    public function logScan(Vehicle $vehicle, Request $request): void
    {
        VehicleQrScan::create([
            'tenant_id'  => $vehicle->tenant_id,
            'vehicle_id' => $vehicle->id,
            'qr_code'    => $vehicle->qr_code,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'scanned_at' => now(),
        ]);
    }

    /**
     * Get the public URL for a vehicle's QR code image.
     */
    public function getImageUrl(Vehicle $vehicle): ?string
    {
        if (!$vehicle->qr_image_path) {
            return null;
        }

        return Storage::disk('s3')->url($vehicle->qr_image_path);
    }

    /**
     * Get scan statistics for a vehicle.
     */
    public function getScanStats(Vehicle $vehicle): array
    {
        $scans = $vehicle->qrScans();

        return [
            'total'       => $scans->count(),
            'today'       => $scans->whereDate('scanned_at', today())->count(),
            'this_week'   => $scans->where('scanned_at', '>=', now()->startOfWeek())->count(),
            'this_month'  => $scans->where('scanned_at', '>=', now()->startOfMonth())->count(),
        ];
    }
}
