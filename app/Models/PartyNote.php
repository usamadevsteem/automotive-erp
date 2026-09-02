<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Vehicle;

class PartyNote extends TenantModel
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id',
        'party_id',
        'vehicle_id',
        'type',
        'amount',
        'note_date',
        'description',
        'reference_type',
        'reference_id',
        'created_by',
    ];

    protected $casts = [
        'amount'    => 'decimal:2',
        'note_date' => 'date',
    ];

    public const TYPES = [
        'debit'  => 'Debit',
        'credit' => 'Credit',
    ];

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function vehicle(): BelongsTo
    {
    return $this->belongsTo(Vehicle::class);
    }

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

    public function getAmountFormattedAttribute(): string
    {
        return 'PKR ' . number_format((float) $this->amount);
    }
}