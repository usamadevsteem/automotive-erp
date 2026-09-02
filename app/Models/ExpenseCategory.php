<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends TenantModel
{
    public $timestamps = false;

    protected $fillable = ['tenant_id','name','account_id','is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function expenses(): HasMany { return $this->hasMany(Expense::class,'category_id'); }
    public function account(): BelongsTo { return $this->belongsTo(Account::class); }
    public function scopeActive($query) { return $query->where('is_active', true); }
}
