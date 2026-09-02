<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tenant extends Model
{
    protected $fillable = [
        'uuid','subdomain','company_name','owner_name',
        'email','phone','address','city','logo_path',
        'plan_id','trial_ends_at','plan_expires_at','status','settings',
    ];

    protected $casts = [
        'settings'        => 'array',
        'trial_ends_at'   => 'datetime',
        'plan_expires_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn(Tenant $t) => $t->uuid = (string) Str::uuid());
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isActive(): bool   { return $this->status === 'active'; }
    public function isOnTrial(): bool  { return $this->status === 'trial' && $this->trial_ends_at?->isFuture(); }
    public function isSuspended(): bool{ return $this->status === 'suspended'; }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function getTimezone(): string
    {
        return $this->getSetting('timezone', 'Asia/Karachi');
    }

    public function getDateFormat(): string
    {
        return $this->getSetting('date_format', 'd/m/Y');
    }
}
