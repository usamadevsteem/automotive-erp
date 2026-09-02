<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleTransfer extends TenantModel
{
    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'from_branch_id',
        'to_branch_id',
        'transfer_date',
        'transferred_by',
        'approved_by',
        'status',
        'notes',
        'rejection_reason',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    const STATUS_PENDING   = 'pending';
    const STATUS_APPROVED  = 'approved';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED  = 'rejected';

    const STATUS_LABELS = [
        self::STATUS_PENDING   => 'Pending',
        self::STATUS_APPROVED  => 'Approved',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_REJECTED  => 'Rejected',
    ];

    const STATUS_COLORS = [
        self::STATUS_PENDING   => 'warning',
        self::STATUS_APPROVED  => 'info',
        self::STATUS_COMPLETED => 'success',
        self::STATUS_REJECTED  => 'danger',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Helpers ────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
