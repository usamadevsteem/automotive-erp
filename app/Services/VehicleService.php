<?php

namespace App\Services;

use App\Events\VehicleStatusChanged;
use App\Models\Vehicle;
use App\Models\VehicleImportCost;
use App\Models\VehicleStatusLog;
use App\Repositories\Contracts\VehicleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VehicleService
{
    public function __construct(
        private readonly VehicleRepositoryInterface $vehicleRepo,
        private readonly QrCodeService              $qrCodeService,
        private readonly StockNumberService         $stockNumberService,
    ) {}

    // ── Create ─────────────────────────────────────────────────────────

    public function create(array $data): Vehicle
    {
        return DB::transaction(function () use ($data) {

            // 1. Generate stock number
            $data['stock_number'] = $this->stockNumberService->generate();

            // 2. Set adding user
            $data['added_by'] = auth()->id();

            // 2b. Default status
            $data['status'] = $data['status'] ?? 'pending_inspection';

            // 3. Compute costs before saving
            $data = $this->computeCosts($data);

            // 4. Create vehicle
            $vehicle = $this->vehicleRepo->create($data);

            // 5. Generate QR code
            $this->qrCodeService->generate($vehicle);

            // 6. Create import cost record if imported
            if (in_array($data['import_status'] ?? 'local', ['imported', 'auction'])) {
                VehicleImportCost::create([
                    'tenant_id'  => $vehicle->tenant_id,
                    'vehicle_id' => $vehicle->id,
                ]);
            }

            // 7. Log initial status
            VehicleStatusLog::create([
                'tenant_id'  => $vehicle->tenant_id,
                'vehicle_id' => $vehicle->id,
                'from_status'=> null,
                'to_status'  => $vehicle->status,
                'reason'     => 'Vehicle added to inventory',
                'changed_by' => auth()->id(),
            ]);

            return $vehicle->load(['make', 'vehicleModel', 'variant', 'branch']);
        });
    }

    // ── Update ─────────────────────────────────────────────────────────

    public function update(Vehicle $vehicle, array $data): Vehicle
    {
        return DB::transaction(function () use ($vehicle, $data) {

            $oldStatus = $vehicle->status;

            // Recompute costs if any cost field changed
            $data = $this->computeCosts($data, $vehicle);

            $updated = $this->vehicleRepo->update($vehicle, $data);

            // Log status change if status changed
            if (isset($data['status']) && $data['status'] !== $oldStatus) {
                $this->logStatusChange(
                    vehicle:    $updated,
                    fromStatus: $oldStatus,
                    toStatus:   $data['status'],
                    reason:     $data['status_reason'] ?? null,
                );

                event(new VehicleStatusChanged($updated, $oldStatus, $data['status']));
            }

            return $updated;
        });
    }

    // ── Status Change ──────────────────────────────────────────────────

    public function changeStatus(Vehicle $vehicle, string $newStatus, ?string $reason = null): Vehicle
    {
        $allowedTransitions = $this->getAllowedTransitions($vehicle->status);

        if (!in_array($newStatus, $allowedTransitions)) {
            throw new \InvalidArgumentException(
                "Cannot transition vehicle from [{$vehicle->status}] to [{$newStatus}]."
            );
        }

        return DB::transaction(function () use ($vehicle, $newStatus, $reason) {
            $oldStatus = $vehicle->status;

            $vehicle->update(['status' => $newStatus]);

            $this->logStatusChange($vehicle, $oldStatus, $newStatus, $reason);

            event(new VehicleStatusChanged($vehicle, $oldStatus, $newStatus));

            return $vehicle->fresh();
        });
    }

    // ── Delete ─────────────────────────────────────────────────────────

    public function delete(Vehicle $vehicle): void
    {
        if ($vehicle->isSold()) {
            throw new \LogicException('A sold vehicle cannot be deleted.');
        }

        $this->vehicleRepo->delete($vehicle);
    }

    // ── Import Costs ───────────────────────────────────────────────────

    public function updateImportCosts(Vehicle $vehicle, array $costData): VehicleImportCost
    {
        return DB::transaction(function () use ($vehicle, $costData) {

            $importCost = $vehicle->importCost ?? VehicleImportCost::create([
                'tenant_id'  => $vehicle->tenant_id,
                'vehicle_id' => $vehicle->id,
            ]);

            // Fill individual lines
            foreach (VehicleImportCost::COST_LINES as $field => $_) {
                if (isset($costData[$field])) {
                    $importCost->{$field} = $costData[$field];
                }
            }

            $importCost->notes = $costData['notes'] ?? $importCost->notes;

            // Recalculate total
            $importCost->recalculate();
            $importCost->save();

            // Sync landing_cost back to vehicle and recalculate
            $vehicle->landing_cost = $importCost->total_import_cost;
            $vehicle->recalculateCosts();
            $vehicle->save();

            return $importCost;
        });
    }

    // ── Private Helpers ────────────────────────────────────────────────

    private function computeCosts(array $data, ?Vehicle $existing = null): array
    {
        $purchase = (float) ($data['purchase_price'] ?? $existing?->purchase_price ?? 0);
        $landing  = (float) ($data['landing_cost']   ?? $existing?->landing_cost   ?? 0);
        $repair   = (float) ($data['repair_cost']    ?? $existing?->repair_cost    ?? 0);
        $misc     = (float) ($data['misc_cost']      ?? $existing?->misc_cost      ?? 0);
        $sale     = (float) ($data['sale_price']     ?? $existing?->sale_price     ?? 0);

        $totalCost            = $purchase + $landing + $repair + $misc;
        $data['total_cost']   = $totalCost;
        $data['expected_profit'] = $sale - $totalCost;

        return $data;
    }

    private function logStatusChange(
        Vehicle $vehicle,
        ?string $fromStatus,
        string  $toStatus,
        ?string $reason,
    ): void {
        VehicleStatusLog::create([
            'tenant_id'  => $vehicle->tenant_id,
            'vehicle_id' => $vehicle->id,
            'from_status'=> $fromStatus,
            'to_status'  => $toStatus,
            'reason'     => $reason,
            'changed_by' => auth()->id(),
        ]);
    }

    /**
     * Define allowed status transitions.
     * Prevents illogical jumps (e.g. delivered → available).
     */
    private function getAllowedTransitions(string $currentStatus): array
    {
        return match ($currentStatus) {
            'pending_inspection' => ['available'],
            'available'          => ['reserved', 'sold'],
            'reserved'           => ['available', 'sold'],
            'sold'               => ['delivered'],
            'delivered'          => [],          // terminal state
            default              => [],
        };
    }
}
