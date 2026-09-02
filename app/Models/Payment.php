<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends TenantModel
{
    protected $fillable = [
        'tenant_id','branch_id','payment_number','type','party_type','party_id',
        'reference_type','reference_id','amount','payment_method',
        'cheque_number','cheque_date','bank_name','reference_number',
        'payment_date','journal_entry_id','notes','created_by',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
        'cheque_date'  => 'date',
    ];

    const PAYMENT_METHODS = [
        'cash'          => 'Cash',
        'cheque'        => 'Cheque',
        'bank_transfer' => 'Bank Transfer',
        'online'        => 'Online',
    ];

    public function branch(): BelongsTo    { return $this->belongsTo(Branch::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class,'created_by'); }
    public function journalEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class); }

    public function getAmountFormattedAttribute(): string
    {
        return 'PKR ' . number_format($this->amount);
    }

    public function getPartyNameAttribute(): string
    {
        $model = match ($this->party_type) {
            'customer' => Customer::find($this->party_id),
            'vendor'   => Vendor::find($this->party_id),
            'employee' => User::find($this->party_id),
            default    => null,
        };

        if (!$model) {
            return ucfirst($this->party_type) . " #{$this->party_id} (not found)";
        }

        return $model->full_name ?? $model->name ?? "Unknown {$this->party_type} #{$this->party_id}";
    }
}
