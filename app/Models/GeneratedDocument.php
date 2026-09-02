<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GeneratedDocument extends TenantModel
{
    protected $fillable = [
        'tenant_id','branch_id','document_number','template_id','document_type',
        'reference_type','reference_id','customer_id','vehicle_id',
        'resolved_data','file_path','verification_code',
        'generated_by','generated_at','printed_at','emailed_at','whatsapp_sent_at',
        'is_voided','voided_by','voided_at','void_reason',
    ];

    protected $casts = [
        'resolved_data'     => 'array',
        'generated_at'      => 'datetime',
        'printed_at'        => 'datetime',
        'emailed_at'        => 'datetime',
        'whatsapp_sent_at'  => 'datetime',
        'voided_at'         => 'datetime',
        'is_voided'         => 'boolean',
    ];

    public function template(): BelongsTo    { return $this->belongsTo(DocumentTemplate::class,'template_id'); }
    public function customer(): BelongsTo    { return $this->belongsTo(Customer::class); }
    public function vehicle(): BelongsTo     { return $this->belongsTo(Vehicle::class); }
    public function generatedBy(): BelongsTo { return $this->belongsTo(User::class,'generated_by'); }
    public function voidedBy(): BelongsTo    { return $this->belongsTo(User::class,'voided_by'); }
    public function branch(): BelongsTo      { return $this->belongsTo(Branch::class); }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }

    public function getTypeLabelAttribute(): string
    {
        return DocumentTemplate::DOCUMENT_TYPES[$this->document_type] ?? ucfirst(str_replace('_',' ',$this->document_type));
    }

    public function getVerificationUrlAttribute(): string
    {
        return route('documents.verify', $this->verification_code);
    }

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function (self $doc) {
            if (empty($doc->verification_code)) {
                $doc->verification_code = strtoupper(Str::random(10));
            }
            if (empty($doc->generated_at)) {
                $doc->generated_at = now();
            }
        });
    }
}
