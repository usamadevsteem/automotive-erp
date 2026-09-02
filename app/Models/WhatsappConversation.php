<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappConversation extends TenantModel
{
    protected $fillable = [
        'tenant_id','wa_account_id','customer_phone','customer_name',
        'customer_id','lead_id','status','assigned_to','assigned_at',
        'unread_count','last_message_at','last_message_preview',
    ];

    protected $casts = [
        'assigned_at'     => 'datetime',
        'last_message_at' => 'datetime',
        'unread_count'    => 'integer',
    ];

    const STATUSES = [
        'open'     => ['label' => 'Open',     'color' => 'primary'],
        'assigned' => ['label' => 'Assigned', 'color' => 'info'],
        'resolved' => ['label' => 'Resolved', 'color' => 'success'],
        'spam'     => ['label' => 'Spam',     'color' => 'danger'],
    ];

    public function waAccount(): BelongsTo   { return $this->belongsTo(WhatsappAccount::class,'wa_account_id'); }
    public function customer(): BelongsTo    { return $this->belongsTo(Customer::class); }
    public function lead(): BelongsTo        { return $this->belongsTo(Lead::class); }
    public function assignedTo(): BelongsTo  { return $this->belongsTo(User::class,'assigned_to'); }
    public function messages(): HasMany      { return $this->hasMany(WhatsappMessage::class,'conversation_id')->orderBy('sent_at'); }
    public function latestMessage(): HasMany { return $this->hasMany(WhatsappMessage::class,'conversation_id')->latest('sent_at')->limit(1); }

    public function getStatusLabelAttribute(): string { return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status); }
    public function getStatusColorAttribute(): string { return self::STATUSES[$this->status]['color'] ?? 'secondary'; }

    public function markAllRead(): void
    {
        $this->messages()->whereNull('read_at')->where('direction','inbound')
             ->update(['read_at' => now()]);
        $this->update(['unread_count' => 0]);
    }
}
