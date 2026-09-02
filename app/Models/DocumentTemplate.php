<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentTemplate extends TenantModel
{
    protected $fillable = [
        'tenant_id','name','document_type','html_body','header_html','footer_html',
        'page_size','orientation','show_logo','show_qr','watermark_text',
        'is_default','is_active','created_by',
    ];

    protected $casts = [
        'show_logo' => 'boolean',
        'show_qr'   => 'boolean',
        'is_default'=> 'boolean',
        'is_active' => 'boolean',
    ];

    const DOCUMENT_TYPES = [
        'sale_invoice'         => 'Sale Invoice',
        'proforma_invoice'     => 'Proforma Invoice',
        'booking_receipt'      => 'Booking Receipt',
        'payment_receipt'      => 'Payment Receipt',
        'delivery_order'       => 'Delivery Order',
        'affidavit'            => 'Affidavit',
        'transfer_letter'      => 'Transfer Letter',
        'open_transfer_letter' => 'Open Transfer Letter',
        'sale_agreement'       => 'Sale Agreement',
        'possession_letter'    => 'Possession Letter',
        'authority_letter'     => 'Authority Letter',
        'handover_certificate' => 'Handover Certificate',
        'customer_declaration' => 'Customer Declaration',
        'commission_voucher'   => 'Commission Voucher',
        'cash_receipt_voucher' => 'Cash Receipt Voucher',
        'cash_payment_voucher' => 'Cash Payment Voucher',
        'journal_voucher'      => 'Journal Voucher',
    ];

    public function createdBy(): BelongsTo      { return $this->belongsTo(User::class,'created_by'); }
    public function generatedDocuments(): HasMany{ return $this->hasMany(GeneratedDocument::class,'template_id'); }
    public function scopeActive($query)          { return $query->where('is_active', true); }
    public function scopeDefault($query)         { return $query->where('is_default', true); }
}
