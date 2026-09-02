<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends TenantModel
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id','name','vendor_type','phone','email','address','city',
        'ntn_number','bank_name','account_number','opening_balance','notes','is_active',
    ];

    protected $casts = ['opening_balance' => 'decimal:2', 'is_active' => 'boolean'];

    const TYPES = [
        'supplier'       => 'Supplier',
        'import_agent'   => 'Import Agent',
        'parts_vendor'   => 'Parts Vendor',
        'service_vendor' => 'Service Vendor',
    ];

    public function expenses(): HasMany { return $this->hasMany(Expense::class); }
    public function scopeActive($query) { return $query->where('is_active', true); }
}
