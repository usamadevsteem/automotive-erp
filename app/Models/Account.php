<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends TenantModel
{
    protected $fillable = [
        'tenant_id','account_code','account_name','account_type',
        'account_subtype','parent_id','is_system','is_active','description',
    ];

    protected $casts = ['is_system' => 'boolean', 'is_active' => 'boolean'];

    const TYPES = [
        'asset'     => 'Asset',
        'liability' => 'Liability',
        'equity'    => 'Equity',
        'revenue'   => 'Revenue',
        'expense'   => 'Expense',
    ];

    const TYPE_COLORS = [
        'asset'     => 'primary',
        'liability' => 'danger',
        'equity'    => 'warning',
        'revenue'   => 'success',
        'expense'   => 'info',
    ];

    public function parent(): BelongsTo   { return $this->belongsTo(Account::class,'parent_id'); }
    public function children(): HasMany   { return $this->hasMany(Account::class,'parent_id'); }
    public function journalLines(): HasMany { return $this->hasMany(JournalEntryLine::class); }

    public function getTypeColorAttribute(): string
    {
        return self::TYPE_COLORS[$this->account_type] ?? 'secondary';
    }

    public function getBalance(): float
    {
        $debits  = $this->journalLines()->sum('debit_amount');
        $credits = $this->journalLines()->sum('credit_amount');

        return in_array($this->account_type, ['asset','expense'])
            ? $debits - $credits
            : $credits - $debits;
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeOfType($query, string $type) { return $query->where('account_type', $type); }
}
