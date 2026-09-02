<?php

namespace App\Repositories\Eloquent;

use App\Models\Vehicle;
use App\Repositories\Contracts\VehicleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentVehicleRepository implements VehicleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return Vehicle::with(['make', 'vehicleModel', 'variant', 'branch'])
            ->filter($filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(int $id, array $with = []): Vehicle
    {
        return Vehicle::with($with ?: [
            'make',
            'vehicleModel',
            'variant',
            'branch',
            'addedBy',
            'importCost',
            'fileDocuments.uploadedBy',
            'statusLogs.changedBy',
            'transfers.fromBranch',
            'transfers.toBranch',
        ])->findOrFail($id);
    }

    public function findByStockNumber(string $stockNumber): ?Vehicle
    {
        return Vehicle::where('stock_number', $stockNumber)->first();
    }

    public function findByQrCode(string $qrCode): ?Vehicle
    {
        // QR lookups bypass tenant scope — the code is globally unique
        return Vehicle::withoutTenantScope()
            ->with(['make', 'vehicleModel', 'variant', 'branch.tenant'])
            ->where('qr_code', $qrCode)
            ->first();
    }

    public function create(array $data): Vehicle
    {
        return Vehicle::create($data);
    }

    public function update(Vehicle $vehicle, array $data): Vehicle
    {
        $vehicle->update($data);
        return $vehicle->fresh();
    }

    public function delete(Vehicle $vehicle): void
    {
        $vehicle->delete();
    }

    public function countByStatus(): array
    {
        return Vehicle::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public function totalInventoryValue(): float
    {
        return (float) Vehicle::whereIn('status', ['available', 'reserved', 'pending_inspection'])
            ->sum('total_cost');
    }

    public function getAvailableForTransfer(int $branchId): Collection
    {
        return Vehicle::where('branch_id', $branchId)
            ->whereIn('status', ['available', 'pending_inspection'])
            ->with(['make', 'vehicleModel'])
            ->orderBy('stock_number')
            ->get();
    }
}
