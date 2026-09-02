<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleVariant extends Model
{
    protected $fillable = ['model_id', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function model(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class, 'model_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
