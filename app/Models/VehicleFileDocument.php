<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VehicleFileDocument extends TenantModel
{
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'document_type',
        'document_label',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'expiry_date',
        'is_original',
        'is_verified',
        'verified_by',
        'verified_at',
        'notes',
        'uploaded_by',
        'uploaded_at',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'is_original' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'uploaded_at' => 'datetime',
    ];

    const DOCUMENT_TYPES = [
        'registration_book'    => 'Registration Book',
        'smart_card'           => 'Smart Card',
        'transfer_letter'      => 'Transfer Letter',
        'open_transfer_letter' => 'Open Transfer Letter',
        'biometric_slip'       => 'Biometric Slip',
        'auction_sheet'        => 'Auction Sheet',
        'import_bill'          => 'Import Bill',
        'customs_clearance'    => 'Customs Clearance',
        'insurance'            => 'Insurance Policy',
        'token_tax'            => 'Token Tax',
        'sale_agreement'       => 'Sale Agreement',
        'affidavit'            => 'Affidavit',
        'noc'                  => 'NOC',
        'other'                => 'Other',
    ];

    // Document types that can expire
    const EXPIRABLE_TYPES = ['insurance', 'token_tax'];

    // ── Relationships ──────────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ── Helpers ────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        if ($this->document_type === 'other' && $this->document_label) {
            return $this->document_label;
        }

        return self::DOCUMENT_TYPES[$this->document_type] ?? ucfirst($this->document_type);
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) return '—';

        $units = ['B', 'KB', 'MB', 'GB'];
        $size  = $this->file_size;
        $i     = 0;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 1) . ' ' . $units[$i];
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date
            && !$this->isExpired()
            && $this->expiry_date->diffInDays(now()) <= $days;
    }

    public function getExpiryStatusAttribute(): string
    {
        if (!$this->expiry_date) return 'no_expiry';
        if ($this->isExpired()) return 'expired';
        if ($this->isExpiringSoon(7)) return 'critical';
        if ($this->isExpiringSoon(30)) return 'warning';
        return 'valid';
    }
}
