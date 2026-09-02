<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CustomerDocument extends TenantModel
{
    public $timestamps = false;

    protected $fillable = [
        'tenant_id','customer_id','document_type',
        'file_path','expiry_date','is_verified','uploaded_by','uploaded_at',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'is_verified' => 'boolean',
        'uploaded_at' => 'datetime',
    ];

    const TYPES = [
        'cnic_front'    => 'CNIC Front',
        'cnic_back'     => 'CNIC Back',
        'passport'      => 'Passport',
        'utility_bill'  => 'Utility Bill',
        'salary_slip'   => 'Salary Slip',
        'other'         => 'Other',
    ];

    public function customer(): BelongsTo   { return $this->belongsTo(Customer::class); }
    public function uploadedBy(): BelongsTo { return $this->belongsTo(User::class,'uploaded_by'); }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
