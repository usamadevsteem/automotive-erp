<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleModel extends Model
{
    protected $table = 'vehicle_models';

    protected $fillable = ['make_id', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function make(): BelongsTo
    {
        return $this->belongsTo(VehicleMake::class, 'make_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(VehicleVariant::class, 'model_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
