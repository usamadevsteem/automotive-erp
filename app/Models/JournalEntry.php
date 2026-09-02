<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends TenantModel
{
    protected $fillable = [
        'tenant_id','branch_id','entry_number','entry_date','narration',
        'reference_type','reference_id','entry_type','total_debit',
        'total_credit','status','is_auto','created_by','posted_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'posted_at'  => 'datetime',
        'is_auto'    => 'boolean',
        'total_debit'  => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    public function lines(): HasMany      { return $this->hasMany(JournalEntryLine::class); }
    public function branch(): BelongsTo   { return $this->belongsTo(Branch::class); }
    public function createdBy(): BelongsTo{ return $this->belongsTo(User::class,'created_by'); }

    public function isBalanced(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }
}