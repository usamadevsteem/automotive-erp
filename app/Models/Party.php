<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Party extends TenantModel
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id',
        'name',
        'type',
        'phone',
        'email',
        'address',
        'notes',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const TYPES = [
        'person'   => 'Person',
        'investor' => 'Investor',
        'business' => 'Business',
        'showroom' => 'Showroom',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function partyNotes(): HasMany
    {
    return $this->hasMany(PartyNote::class);
    }
}