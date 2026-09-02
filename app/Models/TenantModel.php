<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

abstract class TenantModel extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model) {
            if (empty($model->tenant_id)) {
                $tenant = app()->bound('tenant') ? app('tenant') : null;
                if (!$tenant) {
                    throw new \RuntimeException(
                        'Attempting to create a tenant record without an active tenant context. Model: ' . static::class
                    );
                }
                $model->tenant_id = $tenant->id;
            }
        });
    }
}
