<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'tenant_id','plan_id','billing_cycle',
        'amount','started_at','expires_at','status','payment_ref',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'expires_at'  => 'datetime',
        'amount'      => 'decimal:2',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function plan(): BelongsTo   { return $this->belongsTo(Plan::class); }
}
