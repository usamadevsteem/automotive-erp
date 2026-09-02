<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Vehicle extends TenantModel implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useFallbackUrl('/images/vehicle-placeholder.svg');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(200)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('gallery')
            ->width(1000)
            ->height(700)
            ->nonQueued();
    }

    public function featuredImage(): ?Media
    {
        return $this->getMedia('images')
            ->first(fn ($m) => $m->getCustomProperty('is_featured') === true)
            ?? $this->getMedia('images')->first();
    }

    public function featuredImageUrl(string $conversion = 'thumb'): ?string
    {
        $image = $this->featuredImage();
        return $image ? $image->getUrl($conversion) : null;
    }

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'stock_number',
        'make_id',
        'model_id',
        'variant_id',
        'category',
        'year',
        'registration_year',
        'color',
        'mileage',
        'fuel_type',
        'transmission',
        'engine_capacity',
        'condition_grade',
        'registration_number',
        'chassis_number',
        'engine_number',
        'vin_number',
        'import_status',
        'auction_grade',
        'purchase_price',
        'landing_cost',
        'repair_cost',
        'misc_cost',
        'total_cost',
        'sale_price',
        'min_sale_price',
        'expected_profit',
        'actual_profit',
        'status',
        'qr_code',
        'qr_image_path',
        'added_by',
        'sold_by',
        'sold_at',
        'notes',
    ];

    protected $casts = [
        'year'              => 'integer',
        'registration_year' => 'integer',
        'mileage'           => 'integer',
        'purchase_price'    => 'decimal:2',
        'landing_cost'      => 'decimal:2',
        'repair_cost'       => 'decimal:2',
        'misc_cost'         => 'decimal:2',
        'total_cost'        => 'decimal:2',
        'sale_price'        => 'decimal:2',
        'min_sale_price'    => 'decimal:2',
        'expected_profit'   => 'decimal:2',
        'actual_profit'     => 'decimal:2',
        'sold_at'           => 'datetime',
    ];

    // ── Status Constants ───────────────────────────────────────────────

    const STATUS_AVAILABLE          = 'available';
    const STATUS_RESERVED           = 'reserved';
    const STATUS_SOLD               = 'sold';
    const STATUS_DELIVERED          = 'delivered';
    const STATUS_PENDING_INSPECTION = 'pending_inspection';

    const STATUSES = [
        self::STATUS_AVAILABLE          => 'Available',
        self::STATUS_RESERVED           => 'Reserved',
        self::STATUS_SOLD               => 'Sold',
        self::STATUS_DELIVERED          => 'Delivered',
        self::STATUS_PENDING_INSPECTION => 'Pending Inspection',
    ];

    const STATUS_COLORS = [
        self::STATUS_AVAILABLE          => 'success',
        self::STATUS_RESERVED           => 'warning',
        self::STATUS_SOLD               => 'danger',
        self::STATUS_DELIVERED          => 'secondary',
        self::STATUS_PENDING_INSPECTION => 'info',
    ];

    // ── Category Constants ─────────────────────────────────────────────

    const CATEGORIES = [
        'local_car'    => 'Local Car',
        'imported_car' => 'Imported Car',
        'suv'          => 'SUV',
        'pickup'       => 'Pickup / Truck',
        'hybrid'       => 'Hybrid',
        'electric'     => 'Electric',
    ];

    // ── Fuel Type Constants ────────────────────────────────────────────

    const FUEL_TYPES = [
        'petrol'   => 'Petrol',
        'diesel'   => 'Diesel',
        'hybrid'   => 'Hybrid',
        'electric' => 'Electric',
        'cng'      => 'CNG',
    ];

    // ── Transmission Constants ─────────────────────────────────────────

    const TRANSMISSIONS = [
        'manual'    => 'Manual',
        'automatic' => 'Automatic',
        'cvt'       => 'CVT',
    ];

    // ── Condition Constants ────────────────────────────────────────────

    const CONDITIONS = [
        'excellent' => 'Excellent',
        'good'      => 'Good',
        'fair'      => 'Fair',
        'poor'      => 'Poor',
    ];

    // ── Import Status Constants ────────────────────────────────────────

    const IMPORT_STATUSES = [
        'local'    => 'Local',
        'imported' => 'Imported',
        'auction'  => 'Auction',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function make(): BelongsTo
    {
        return $this->belongsTo(VehicleMake::class, 'make_id');
    }

    public function vehicleModel(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class, 'model_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(VehicleVariant::class, 'variant_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function soldBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by');
    }

    public function importCost(): HasOne
    {
        return $this->hasOne(VehicleImportCost::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(VehicleStatusLog::class)->latest();
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(VehicleTransfer::class)->latest();
    }

    public function fileDocuments(): HasMany
    {
        return $this->hasMany(VehicleFileDocument::class);
    }

    public function qrScans(): HasMany
    {
        return $this->hasMany(VehicleQrScan::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeForSale($query)
    {
        return $query->whereIn('status', [self::STATUS_AVAILABLE, self::STATUS_RESERVED]);
    }

    public function scopeInBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('stock_number', 'like', "%{$term}%")
              ->orWhere('registration_number', 'like', "%{$term}%")
              ->orWhere('chassis_number', 'like', "%{$term}%")
              ->orWhere('engine_number', 'like', "%{$term}%")
              ->orWhere('color', 'like', "%{$term}%")
              ->orWhereHas('make', fn($q) => $q->where('name', 'like', "%{$term}%"))
              ->orWhereHas('vehicleModel', fn($q) => $q->where('name', 'like', "%{$term}%"));
        });
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['status'] ?? null,      fn($q, $v) => $q->where('status', $v))
            ->when($filters['make_id'] ?? null,      fn($q, $v) => $q->where('make_id', $v))
            ->when($filters['model_id'] ?? null,     fn($q, $v) => $q->where('model_id', $v))
            ->when($filters['category'] ?? null,     fn($q, $v) => $q->where('category', $v))
            ->when($filters['branch_id'] ?? null,    fn($q, $v) => $q->where('branch_id', $v))
            ->when($filters['fuel_type'] ?? null,    fn($q, $v) => $q->where('fuel_type', $v))
            ->when($filters['transmission'] ?? null, fn($q, $v) => $q->where('transmission', $v))
            ->when($filters['year'] ?? null,         fn($q, $v) => $q->where('year', $v))
            ->when($filters['import_status'] ?? null, fn($q, $v) => $q->where('import_status', $v))
            ->when($filters['price_min'] ?? null,    fn($q, $v) => $q->where('sale_price', '>=', $v))
            ->when($filters['price_max'] ?? null,    fn($q, $v) => $q->where('sale_price', '<=', $v))
            ->when($filters['search'] ?? null,       fn($q, $v) => $q->search($v));
    }

    // ── Computed Helpers ───────────────────────────────────────────────

    /**
     * Recalculate total_cost and expected_profit.
     * Called by VehicleService whenever costs change.
     */
    public function recalculateCosts(): void
    {
        $this->total_cost      = $this->purchase_price + $this->landing_cost
                               + $this->repair_cost    + $this->misc_cost;
        $this->expected_profit = $this->sale_price - $this->total_cost;
    }

    public function getFullNameAttribute(): string
    {
        return implode(' ', array_filter([
            $this->year,
            $this->make?->name,
            $this->vehicleModel?->name,
            $this->variant?->name,
        ]));
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    public function getMileageFormattedAttribute(): string
    {
        return number_format($this->mileage) . ' km';
    }

    public function getSalePriceFormattedAttribute(): string
    {
        return 'PKR ' . number_format($this->sale_price, 0);
    }

    public function getTotalCostFormattedAttribute(): string
    {
        return 'PKR ' . number_format($this->total_cost, 0);
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isSold(): bool
    {
        return in_array($this->status, [self::STATUS_SOLD, self::STATUS_DELIVERED]);
    }

    public function canBeTransferred(): bool
    {
        return in_array($this->status, [self::STATUS_AVAILABLE, self::STATUS_PENDING_INSPECTION]);
    }

    /**
     * Documents checklist — which types are uploaded vs missing.
     */
    public function getDocumentChecklist(): array
    {
        $uploaded = $this->fileDocuments->pluck('document_type')->toArray();

        $required = [
            'registration_book' => 'Registration Book',
            'smart_card'        => 'Smart Card',
            'transfer_letter'   => 'Transfer Letter',
        ];

        if ($this->import_status !== 'local') {
            $required['auction_sheet']     = 'Auction Sheet';
            $required['import_bill']       = 'Import Bill';
            $required['customs_clearance'] = 'Customs Clearance';
        }

        $checklist = [];
        foreach ($required as $type => $label) {
            $checklist[] = [
                'type'      => $type,
                'label'     => $label,
                'uploaded'  => in_array($type, $uploaded),
                'required'  => true,
            ];
        }

        return $checklist;
    }

    public function getDocumentCompletenessAttribute(): int
    {
        $checklist = $this->getDocumentChecklist();
        $total     = count($checklist);

        if ($total === 0) return 100;

        $uploaded = count(array_filter($checklist, fn($item) => $item['uploaded']));

        return (int) round(($uploaded / $total) * 100);
    }
}
