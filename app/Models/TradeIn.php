<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeIn extends TenantModel
{
    protected $fillable = [
        'tenant_id','branch_id','customer_id','new_vehicle_id','sale_invoice_id',
        'trade_make','trade_model','trade_year','trade_registration',
        'trade_mileage','trade_condition','trade_color',
        'chassis_number','engine_number',
        'market_value','offered_value','approved_value','difference_amount',
        'status','evaluated_by','approved_by','notes',
    ];

    protected $casts = [
        'market_value'     => 'decimal:2',
        'offered_value'    => 'decimal:2',
        'approved_value'   => 'decimal:2',
        'difference_amount'=> 'decimal:2',
    ];

    const STATUSES = [
        'pending'   => ['label' => 'Pending',   'color' => 'warning'],
        'approved'  => ['label' => 'Approved',  'color' => 'success'],
        'rejected'  => ['label' => 'Rejected',  'color' => 'danger'],
        'completed' => ['label' => 'Completed', 'color' => 'secondary'],
    ];

    public function customer(): BelongsTo    { return $this->belongsTo(Customer::class); }
    public function newVehicle(): BelongsTo  { return $this->belongsTo(Vehicle::class,'new_vehicle_id'); }
    public function saleInvoice(): BelongsTo { return $this->belongsTo(SaleInvoice::class); }
    public function evaluatedBy(): BelongsTo { return $this->belongsTo(User::class,'evaluated_by'); }
    public function approvedBy(): BelongsTo  { return $this->belongsTo(User::class,'approved_by'); }

    public function getStatusLabelAttribute(): string { return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status); }
    public function getStatusColorAttribute(): string { return self::STATUSES[$this->status]['color'] ?? 'secondary'; }
}
