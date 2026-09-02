<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends TenantModel implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, SoftDeletes, Notifiable, HasRoles, HasApiTokens;

    protected $fillable = [
        'tenant_id','branch_id','name','email','phone','password',
        'cnic','designation','profile_photo',
        'two_factor_secret','two_factor_enabled','is_active','last_login_at',
    ];

    protected $hidden = ['password','remember_token','two_factor_secret'];

    protected $casts = [
        'two_factor_enabled' => 'boolean',
        'is_active'          => 'boolean',
        'last_login_at'      => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Platform\Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function isActive(): bool { return $this->is_active === true; }



    public function scopeActive($query) { return $query->where('is_active', true); }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled && !empty($this->two_factor_secret);
    }

    public function guardName(): string { return 'web'; }
}
