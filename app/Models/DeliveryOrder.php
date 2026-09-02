<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryOrder extends TenantModel
{
    protected $fillable = [
        'tenant_id','branch_id','delivery_number','sale_invoice_id',
        'customer_id','vehicle_id','delivery_date','delivered_by',
        'condition_notes','accessories_list','customer_signature','notes','created_by',
    ];

    protected $casts = [
        'delivery_date'    => 'date',
        'accessories_list' => 'array',
    ];

    public function saleInvoice(): BelongsTo { return $this->belongsTo(SaleInvoice::class); }
    public function customer(): BelongsTo    { return $this->belongsTo(Customer::class); }
    public function vehicle(): BelongsTo     { return $this->belongsTo(Vehicle::class); }
    public function deliveredBy(): BelongsTo { return $this->belongsTo(User::class,'delivered_by'); }
    public function createdBy(): BelongsTo   { return $this->belongsTo(User::class,'created_by'); }
}
