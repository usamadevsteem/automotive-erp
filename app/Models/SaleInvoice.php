<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleInvoice extends TenantModel
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id','branch_id','invoice_number','customer_id','vehicle_id',
        'booking_id','sale_price','discount','withholding_tax','net_amount',
        'payment_type','amount_paid','balance_due','invoice_date',
        'status','cancelled_reason','notes','created_by',
    ];

    protected $casts = [
        'sale_price'      => 'decimal:2',
        'discount'        => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'net_amount'      => 'decimal:2',
        'amount_paid'     => 'decimal:2',
        'balance_due'     => 'decimal:2',
        'invoice_date'    => 'date',
    ];

    const STATUSES = [
        'draft'     => ['label' => 'Draft',     'color' => 'secondary'],
        'issued'    => ['label' => 'Issued',    'color' => 'info'],
        'paid'      => ['label' => 'Paid',      'color' => 'success'],
        'partial'   => ['label' => 'Partial',   'color' => 'warning'],
        'cancelled' => ['label' => 'Cancelled', 'color' => 'danger'],
    ];

    const PAYMENT_TYPES = [
        'cash'          => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'cheque'        => 'Cheque',
    ];

    public function customer(): BelongsTo    { return $this->belongsTo(Customer::class); }
    public function vehicle(): BelongsTo     { return $this->belongsTo(Vehicle::class); }
    public function booking(): BelongsTo     { return $this->belongsTo(Booking::class); }
    public function branch(): BelongsTo      { return $this->belongsTo(Branch::class); }
    public function createdBy(): BelongsTo   { return $this->belongsTo(User::class,'created_by'); }
    public function delivery(): HasOne       { return $this->hasOne(DeliveryOrder::class); }
    public function dealFile(): HasOne       { return $this->hasOne(DealFile::class); }
    
    public function commissions(): HasMany   { return $this->hasMany(Commission::class); }
    public function payments(): HasMany      { return $this->hasMany(Payment::class,'reference_id')->where('reference_type','sale_invoice'); }

    public function getStatusLabelAttribute(): string { return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status); }
    public function getStatusColorAttribute(): string { return self::STATUSES[$this->status]['color'] ?? 'secondary'; }

    public function getNetAmountFormattedAttribute(): string { return 'PKR ' . number_format($this->net_amount); }
    public function getBalanceDueFormattedAttribute(): string { return 'PKR ' . number_format($this->balance_due); }

    public function isPaid(): bool      { return $this->status === 'paid'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }
}
