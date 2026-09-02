<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends TenantModel
{
    protected $fillable = [
        'tenant_id','user_id','type','title','message','data','read_at',
    ];

    protected $casts = [
        'data'    => 'array',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function scopeUnread($query) { return $query->whereNull('read_at'); }

    public function markRead(): void
    {
        $this->update(['read_at' => now()]);
    }
}
