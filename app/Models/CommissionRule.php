<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionRule extends TenantModel
{
    protected $fillable = [
        'tenant_id','name','applies_to','calc_type','value','min_sale_price','is_active','created_by',
    ];

    protected $casts = [
        'value'          => 'decimal:4',
        'min_sale_price' => 'decimal:2',
        'is_active'      => 'boolean',
    ];

    const APPLIES_TO  = ['salesman' => 'Salesman', 'manager' => 'Manager', 'branch' => 'Branch'];
    const CALC_TYPES  = [
        'fixed'              => 'Fixed Amount',
        'percentage_profit'  => '% of Profit',
        'percentage_sale'    => '% of Sale Price',
    ];

    public function commissions(): HasMany { return $this->hasMany(Commission::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
    public function scopeActive($query)    { return $query->where('is_active', true); }

    public function calculate(float $saleAmount, float $profitAmount): float
    {
        if ($this->min_sale_price && $saleAmount < $this->min_sale_price) return 0;

        return match($this->calc_type) {
            'fixed'             => (float) $this->value,
            'percentage_profit' => round($profitAmount * ($this->value / 100), 2),
            'percentage_sale'   => round($saleAmount  * ($this->value / 100), 2),
            default             => 0,
        };
    }
}
