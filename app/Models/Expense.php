<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends TenantModel
{
    protected $fillable = [
        'tenant_id','branch_id','expense_number','category_id','description',
        'amount','payment_method','vendor_id','expense_date','reference_number',
        'receipt_path','journal_entry_id','status','approved_by','created_by',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
    ];

    const STATUSES = [
        'pending'  => ['label' => 'Pending',  'color' => 'warning'],
        'approved' => ['label' => 'Approved', 'color' => 'success'],
        'rejected' => ['label' => 'Rejected', 'color' => 'danger'],
    ];

    const PAYMENT_METHODS = [
        'cash'          => 'Cash',
        'cheque'        => 'Cheque',
        'bank_transfer' => 'Bank Transfer',
        'online'        => 'Online',
    ];

    public function category(): BelongsTo    { return $this->belongsTo(ExpenseCategory::class,'category_id'); }
    public function vendor(): BelongsTo      { return $this->belongsTo(Vendor::class); }
    public function approvedBy(): BelongsTo  { return $this->belongsTo(User::class,'approved_by'); }
    public function createdBy(): BelongsTo   { return $this->belongsTo(User::class,'created_by'); }
    public function branch(): BelongsTo      { return $this->belongsTo(Branch::class); }

    public function getStatusLabelAttribute(): string { return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status); }
    public function getStatusColorAttribute(): string { return self::STATUSES[$this->status]['color'] ?? 'secondary'; }
    public function getAmountFormattedAttribute(): string { return 'PKR '.number_format($this->amount); }
}
