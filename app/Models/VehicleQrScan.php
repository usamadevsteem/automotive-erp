<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleQrScan extends TenantModel
{
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'qr_code',
        'ip_address',
        'user_agent',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
