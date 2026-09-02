<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleImportCost extends TenantModel
{
    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'auction_price',
        'auction_charges',
        'shipping_charges',
        'clearing_charges',
        'customs_duty',
        'port_charges',
        'registration_charges',
        'transportation_charges',
        'other_charges',
        'total_import_cost',
        'notes',
    ];

    protected $casts = [
        'auction_price'          => 'decimal:2',
        'auction_charges'        => 'decimal:2',
        'shipping_charges'       => 'decimal:2',
        'clearing_charges'       => 'decimal:2',
        'customs_duty'           => 'decimal:2',
        'port_charges'           => 'decimal:2',
        'registration_charges'   => 'decimal:2',
        'transportation_charges' => 'decimal:2',
        'other_charges'          => 'decimal:2',
        'total_import_cost'      => 'decimal:2',
    ];

    // ── Cost Line Labels (for UI display) ─────────────────────────────

    const COST_LINES = [
        'auction_price'          => 'Auction Purchase Price',
        'auction_charges'        => 'Auction Charges',
        'shipping_charges'       => 'Shipping Charges',
        'clearing_charges'       => 'Clearing Charges',
        'customs_duty'           => 'Customs Duty',
        'port_charges'           => 'Port Charges',
        'registration_charges'   => 'Registration Charges',
        'transportation_charges' => 'Transportation Charges',
        'other_charges'          => 'Other Charges',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    // ── Helpers ────────────────────────────────────────────────────────

    /**
     * Recompute total from all individual lines.
     * Called before saving by ImportCostService.
     */
    public function recalculate(): void
    {
        $this->total_import_cost =
            $this->auction_price
            + $this->auction_charges
            + $this->shipping_charges
            + $this->clearing_charges
            + $this->customs_duty
            + $this->port_charges
            + $this->registration_charges
            + $this->transportation_charges
            + $this->other_charges;
    }

    public function getCostBreakdown(): array
    {
        return collect(self::COST_LINES)
            ->map(fn($label, $field) => [
                'field'  => $field,
                'label'  => $label,
                'amount' => (float) ($this->{$field} ?? 0),
            ])
            ->values()
            ->toArray();
    }
}
