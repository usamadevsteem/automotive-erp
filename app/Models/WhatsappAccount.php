<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappAccount extends TenantModel
{
    protected $fillable = [
        'tenant_id','phone_number','display_name','provider',
        'api_key','webhook_token','status','is_active',
    ];

    protected $hidden  = ['api_key'];
    protected $casts   = ['is_active' => 'boolean'];

    public function conversations(): HasMany
    {
        return $this->hasMany(WhatsappConversation::class,'wa_account_id');
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
}
