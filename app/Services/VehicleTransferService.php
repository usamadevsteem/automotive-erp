<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleTransfer;
use Illuminate\Support\Facades\DB;

class VehicleTransferService
{
    public function __construct(
        private readonly VehicleService $vehicleService,
    ) {}

    /**
     * Initiate a transfer request.
     */
    public function initiate(Vehicle $vehicle, array $data): VehicleTransfer
    {
        if (!$vehicle->canBeTransferred()) {
            throw new \LogicException(
                "Vehicle [{$vehicle->stock_number}] cannot be transferred in its current status [{$vehicle->status}]."
            );
        }

        // Check no pending transfer already exists
        $pending = VehicleTransfer::where('vehicle_id', $vehicle->id)
            ->where('status', 'pending')
            ->exists();

        if ($pending) {
            throw new \LogicException('A transfer request is already pending for this vehicle.');
        }

        return VehicleTransfer::create([
            'tenant_id'      => $vehicle->tenant_id,
            'vehicle_id'     => $vehicle->id,
            'from_branch_id' => $vehicle->branch_id,
            'to_branch_id'   => $data['to_branch_id'],
            'transfer_date'  => $data['transfer_date'] ?? today(),
            'transferred_by' => auth()->id(),
            'status'         => 'pending',
            'notes'          => $data['notes'] ?? null,
        ]);
    }

    /**
     * Approve a pending transfer — called by branch manager of destination branch.
     */
    public function approve(VehicleTransfer $transfer): VehicleTransfer
    {
        if (!$transfer->isPending()) {
            throw new \LogicException('Only pending transfers can be approved.');
        }

        return DB::transaction(function () use ($transfer) {
            $transfer->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
            ]);

            return $transfer->fresh();
        });
    }

    /**
     * Complete a transfer — vehicle physically moved, branch_id updated.
     */
    public function complete(VehicleTransfer $transfer): VehicleTransfer
    {
        if ($transfer->status !== 'approved') {
            throw new \LogicException('Only approved transfers can be completed.');
        }

        return DB::transaction(function () use ($transfer) {

            // Move the vehicle to new branch
            $transfer->vehicle->update([
                'branch_id' => $transfer->to_branch_id,
            ]);

            // Log status (same status, different branch)
            $this->vehicleService->changeStatus(
                vehicle:   $transfer->vehicle,
                newStatus: $transfer->vehicle->status,
                reason:    "Transferred to branch: {$transfer->toBranch->name}",
            );

            $transfer->update(['status' => 'completed']);

            return $transfer->fresh();
        });
    }

    /**
     * Reject a transfer request.
     */
    public function reject(VehicleTransfer $transfer, string $reason): VehicleTransfer
    {
        if (!$transfer->isPending()) {
            throw new \LogicException('Only pending transfers can be rejected.');
        }

        $transfer->update([
            'status'           => 'rejected',
            'rejection_reason' => $reason,
            'approved_by'      => auth()->id(),
        ]);

        return $transfer->fresh();
    }
}
