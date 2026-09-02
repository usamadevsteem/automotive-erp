<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappQuickReply extends TenantModel
{
    protected $fillable = [
        'tenant_id','title','body','category','usage_count','is_active','created_by',
    ];

    protected $casts = ['is_active' => 'boolean', 'usage_count' => 'integer'];

    public function createdBy(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
    public function scopeActive($query)    { return $query->where('is_active', true); }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
