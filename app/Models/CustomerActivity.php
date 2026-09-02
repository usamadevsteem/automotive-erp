<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerActivity extends TenantModel
{
    protected $fillable = [
        'tenant_id','customer_id','lead_id','type',
        'subject','description','outcome',
        'scheduled_at','completed_at','created_by',
    ];

    protected $casts = [
        'scheduled_at'  => 'datetime',
        'completed_at'  => 'datetime',
    ];

    const TYPES = [
        'call'      => ['label' => 'Call',     'icon' => 'bi-telephone',     'color' => 'primary'],
        'meeting'   => ['label' => 'Meeting',  'icon' => 'bi-people',        'color' => 'success'],
        'whatsapp'  => ['label' => 'WhatsApp', 'icon' => 'bi-whatsapp',      'color' => 'success'],
        'email'     => ['label' => 'Email',    'icon' => 'bi-envelope',      'color' => 'info'],
        'note'      => ['label' => 'Note',     'icon' => 'bi-sticky',        'color' => 'warning'],
        'task'      => ['label' => 'Task',     'icon' => 'bi-check-square',  'color' => 'secondary'],
    ];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type]['label'] ?? ucfirst($this->type);
    }

    public function getTypeIconAttribute(): string
    {
        return self::TYPES[$this->type]['icon'] ?? 'bi-circle';
    }

    public function getTypeColorAttribute(): string
    {
        return self::TYPES[$this->type]['color'] ?? 'secondary';
    }

    public function isCompleted(): bool { return !is_null($this->completed_at); }
}
