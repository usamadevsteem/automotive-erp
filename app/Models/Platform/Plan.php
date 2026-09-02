<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name','slug','price_monthly','price_annual',
        'max_vehicles','max_users','max_branches','features','is_active',
    ];

    protected $casts = [
        'features'      => 'array',
        'price_monthly' => 'decimal:2',
        'price_annual'  => 'decimal:2',
        'is_active'     => 'boolean',
    ];

    public function hasFeature(string $feature): bool
    {
        return (bool) data_get($this->features, $feature, false);
    }
}
