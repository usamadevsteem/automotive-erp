<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends TenantModel
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id','branch_id','customer_id','full_name','phone','email',
        'source','vehicle_interest','budget','status','lost_reason',
        'assigned_to','next_follow_up','notes','converted_at','created_by',
    ];

    protected $casts = [
        'budget'         => 'decimal:2',
        'next_follow_up' => 'datetime',
        'converted_at'   => 'datetime',
    ];

    const STATUSES = [
        'new'         => ['label' => 'New',         'color' => 'primary'],
        'contacted'   => ['label' => 'Contacted',   'color' => 'info'],
        'qualified'   => ['label' => 'Qualified',   'color' => 'warning'],
        'negotiation' => ['label' => 'Negotiation', 'color' => 'orange'],
        'won'         => ['label' => 'Won',         'color' => 'success'],
        'lost'        => ['label' => 'Lost',        'color' => 'danger'],
    ];

    const SOURCES = Customer::SOURCES;

    public function customer(): BelongsTo   { return $this->belongsTo(Customer::class); }
    public function branch(): BelongsTo     { return $this->belongsTo(Branch::class); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class,'assigned_to'); }
    public function createdBy(): BelongsTo  { return $this->belongsTo(User::class,'created_by'); }
    public function activities(): HasMany   { return $this->hasMany(CustomerActivity::class,'lead_id')->latest(); }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'secondary';
    }

    public function isOpen(): bool
    {
        return !in_array($this->status, ['won','lost']);
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status',['won','lost']);
    }

    public function scopeOverdueFollowUp($query)
    {
        return $query->whereNotNull('next_follow_up')
                     ->where('next_follow_up','<', now())
                     ->whereNotIn('status',['won','lost']);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('full_name','like',"%{$term}%")
              ->orWhere('phone','like',"%{$term}%")
              ->orWhere('vehicle_interest','like',"%{$term}%");
        });
    }
}
