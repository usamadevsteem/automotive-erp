<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends TenantModel
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id','branch_id','full_name','father_husband_name','cnic',
        'mobile','mobile_alt','email','address','city','occupation',
        'customer_type','source','tax_status','ntn_number',
        'assigned_to','notes','created_by',
    ];

    const SOURCES = [
        'walk_in'   => 'Walk-In',   'referral'  => 'Referral',
        'website'   => 'Website',   'facebook'  => 'Facebook',
        'instagram' => 'Instagram', 'whatsapp'  => 'WhatsApp',
        'olx'       => 'OLX',       'pakwheels' => 'PakWheels',
        'other'     => 'Other',
    ];

    const TYPES = ['buyer' => 'Buyer', 'seller' => 'Seller', 'both' => 'Both'];

    public function branch(): BelongsTo   { return $this->belongsTo(Branch::class); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class,'assigned_to'); }
    public function createdBy(): BelongsTo  { return $this->belongsTo(User::class,'created_by'); }
    public function activities(): HasMany   { return $this->hasMany(CustomerActivity::class)->latest(); }
    public function documents(): HasMany    { return $this->hasMany(CustomerDocument::class); }
    public function leads(): HasMany        { return $this->hasMany(Lead::class); }
    public function invoices(): HasMany     { return $this->hasMany(SaleInvoice::class); }
   

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source] ?? ucfirst($this->source);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('full_name','like',"%{$term}%")
              ->orWhere('mobile','like',"%{$term}%")
              ->orWhere('cnic','like',"%{$term}%")
              ->orWhere('email','like',"%{$term}%");
        });
    }

    public function getTotalPurchasesAttribute(): int
    {
        return $this->invoices()->whereIn('status',['paid','partial'])->count();
    }
}
