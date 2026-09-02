<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMessage extends TenantModel
{
    public $timestamps = false;

    protected $fillable = [
        'tenant_id','conversation_id','wa_message_id','direction','message_type',
        'content','media_url','media_path','template_name',
        'status','sent_by','sent_at','delivered_at','read_at',
    ];

    protected $casts = [
        'sent_at'      => 'datetime',
        'delivered_at' => 'datetime',
        'read_at'      => 'datetime',
    ];

    const TYPES = [
        'text'     => 'Text',
        'image'    => 'Image',
        'document' => 'Document',
        'audio'    => 'Audio',
        'video'    => 'Video',
        'template' => 'Template',
    ];

    public function conversation(): BelongsTo { return $this->belongsTo(WhatsappConversation::class,'conversation_id'); }
    public function sentBy(): BelongsTo       { return $this->belongsTo(User::class,'sent_by'); }

    public function isInbound(): bool  { return $this->direction === 'inbound'; }
    public function isOutbound(): bool { return $this->direction === 'outbound'; }
    public function isRead(): bool     { return !is_null($this->read_at); }
}
