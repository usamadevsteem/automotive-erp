<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quotation extends TenantModel
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id','branch_id','quotation_number','customer_id','vehicle_id',
        'lead_id','sale_price','discount','net_price','valid_until','notes',
        'status','created_by',
    ];

    protected $casts = [
        'sale_price'  => 'decimal:2',
        'discount'    => 'decimal:2',
        'net_price'   => 'decimal:2',
        'valid_until' => 'date',
    ];

    const STATUSES = [
        'draft'    => ['label' => 'Draft',    'color' => 'secondary'],
        'sent'     => ['label' => 'Sent',     'color' => 'info'],
        'accepted' => ['label' => 'Accepted', 'color' => 'success'],
        'rejected' => ['label' => 'Rejected', 'color' => 'danger'],
        'expired'  => ['label' => 'Expired',  'color' => 'warning'],
    ];

    public function customer(): BelongsTo  { return $this->belongsTo(Customer::class); }
    public function vehicle(): BelongsTo   { return $this->belongsTo(Vehicle::class); }
    public function lead(): BelongsTo      { return $this->belongsTo(Lead::class); }
    public function branch(): BelongsTo    { return $this->belongsTo(Branch::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
    public function booking(): HasOne      { return $this->hasOne(Booking::class,'quotation_id'); }

    public function getStatusLabelAttribute(): string { return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status); }
    public function getStatusColorAttribute(): string { return self::STATUSES[$this->status]['color'] ?? 'secondary'; }
    public function isExpired(): bool { return $this->valid_until->isPast() && $this->status === 'sent'; }
}
