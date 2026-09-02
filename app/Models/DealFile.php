<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DealFile extends TenantModel
{
    protected $fillable = [
        'tenant_id','branch_id','deal_number','sale_invoice_id',
        'customer_id','vehicle_id','deal_type','checklist','status','created_by',
    ];

    protected $casts = ['checklist' => 'array'];

    const DEAL_TYPES = [
        'cash'        => 'Cash',
        'trade_in'    => 'Trade-In',
    ];

   const REQUIRED_DOCS = [

    'cash' => [
        'sale_invoice'   => 'Sale Invoice',
        'delivery_order' => 'Delivery Order',
        'affidavit'      => 'Affidavit',
        'transfer_letter'=> 'Transfer Letter',
    ],


    'trade_in' => [
        'sale_invoice'         => 'Sale Invoice',
        'delivery_order'       => 'Delivery Order',
        'affidavit'            => 'Affidavit',
        'transfer_letter'      => 'Transfer Letter',
        'handover_certificate' => 'Handover Certificate',
    ],

    ];

    public function saleInvoice(): BelongsTo { return $this->belongsTo(SaleInvoice::class); }
    public function customer(): BelongsTo    { return $this->belongsTo(Customer::class); }
    public function vehicle(): BelongsTo     { return $this->belongsTo(Vehicle::class); }
    public function branch(): BelongsTo      { return $this->belongsTo(Branch::class); }
    public function createdBy(): BelongsTo   { return $this->belongsTo(User::class,'created_by'); }

    public function generatedDocuments()
    {
        return GeneratedDocument::where('reference_type','sale_invoice')
            ->where('reference_id', $this->sale_invoice_id)
            ->get();
    }

    public function getCompletenessAttribute(): int
    {
        $checklist = $this->checklist ?? [];
        $requiredDocs = self::REQUIRED_DOCS[$this->deal_type]
            ?? self::REQUIRED_DOCS['cash'];
        $total = count($requiredDocs);

        if ($total === 0) {
            return 100;
        }

        $done = 0;
        foreach (array_keys($requiredDocs) as $docType) {
            if (!empty($checklist[$docType])) {
                $done++;
            }
        }
        return (int) round(($done / $total) * 100);
    }

    public function markDocumentDone(string $docType): void
    {
        $checklist = $this->checklist ?? [];
        $checklist[$docType] = true;
        $requiredDocs = self::REQUIRED_DOCS[$this->deal_type]
            ?? self::REQUIRED_DOCS['cash'];
        $allComplete = true;
        foreach (array_keys($requiredDocs) as $requiredDoc) {
            if (empty($checklist[$requiredDoc])) {
                $allComplete = false;
                break;
            }
        }

        $this->update([
            'checklist' => $checklist,
            'status'    => $allComplete ? 'complete' : 'in_progress',
        ]);
    }
}
