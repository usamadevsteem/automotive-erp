<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends TenantModel
{
    protected $fillable = [
        'tenant_id','branch_id','booking_number','customer_id','vehicle_id',
        'quotation_id','booking_amount','agreed_sale_price','expected_delivery_date',
        'payment_method','payment_reference','status','cancellation_reason','notes','created_by',
    ];

    protected $casts = [
        'booking_amount'        => 'decimal:2',
        'agreed_sale_price'     => 'decimal:2',
        'expected_delivery_date'=> 'date',
    ];

    const STATUSES = [
        'active'    => ['label' => 'Active',    'color' => 'success'],
        'cancelled' => ['label' => 'Cancelled', 'color' => 'danger'],
        'converted' => ['label' => 'Converted', 'color' => 'secondary'],
    ];

    const PAYMENT_METHODS = ['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'cheque' => 'Cheque', 'online' => 'Online'];

    public function customer(): BelongsTo  { return $this->belongsTo(Customer::class); }
    public function vehicle(): BelongsTo   { return $this->belongsTo(Vehicle::class); }
    public function quotation(): BelongsTo { return $this->belongsTo(Quotation::class); }
    public function branch(): BelongsTo    { return $this->belongsTo(Branch::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
    public function invoice(): HasOne      { return $this->hasOne(SaleInvoice::class,'booking_id'); }

    public function getStatusLabelAttribute(): string { return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status); }
    public function getStatusColorAttribute(): string { return self::STATUSES[$this->status]['color'] ?? 'secondary'; }
}
