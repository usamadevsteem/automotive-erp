<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends TenantModel
{
    protected $fillable = [
        'tenant_id','sale_invoice_id','vehicle_id','commission_rule_id',
        'employee_id','referrer_name','referrer_phone','commission_type',
        'sale_amount','profit_amount','commission_amount',
        'status','approved_by','approved_at','payment_id','journal_entry_id','notes',
    ];

    protected $casts = [
        'sale_amount'       => 'decimal:2',
        'profit_amount'     => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'approved_at'       => 'datetime',
    ];

    const STATUSES = [
        'pending'  => ['label' => 'Pending',  'color' => 'warning'],
        'approved' => ['label' => 'Approved', 'color' => 'info'],
        'paid'     => ['label' => 'Paid',     'color' => 'success'],
    ];

    const TYPES = ['salesman' => 'Salesman', 'manager' => 'Manager', 'referral' => 'Referral'];

    public function saleInvoice(): BelongsTo  { return $this->belongsTo(SaleInvoice::class); }
    public function vehicle(): BelongsTo      { return $this->belongsTo(Vehicle::class); }
    public function commissionRule(): BelongsTo { return $this->belongsTo(CommissionRule::class); }
    public function employee(): BelongsTo     { return $this->belongsTo(User::class,'employee_id'); }
    public function approvedBy(): BelongsTo   { return $this->belongsTo(User::class,'approved_by'); }

    public function getStatusLabelAttribute(): string { return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status); }
    public function getStatusColorAttribute(): string { return self::STATUSES[$this->status]['color'] ?? 'secondary'; }
    public function getAmountFormattedAttribute(): string { return 'PKR '.number_format($this->commission_amount); }
}
