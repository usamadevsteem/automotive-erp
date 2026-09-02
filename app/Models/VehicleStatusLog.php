<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleStatusLog extends TenantModel
{
    // Immutable audit log — no updates, no soft delete
    public $timestamps  = false;
    public $updatedAt   = false;

    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'from_status',
        'to_status',
        'reason',
        'changed_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // ── Helpers ────────────────────────────────────────────────────────

    public function getFromStatusLabelAttribute(): string
    {
        return Vehicle::STATUSES[$this->from_status] ?? ucfirst($this->from_status ?? 'New');
    }

    public function getToStatusLabelAttribute(): string
    {
        return Vehicle::STATUSES[$this->to_status] ?? ucfirst($this->to_status);
    }
}
