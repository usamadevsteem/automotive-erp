<?php

namespace App\Repositories\Contracts;

use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface VehicleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function findById(int $id, array $with = []): Vehicle;

    public function findByStockNumber(string $stockNumber): ?Vehicle;

    public function findByQrCode(string $qrCode): ?Vehicle;

    public function create(array $data): Vehicle;

    public function update(Vehicle $vehicle, array $data): Vehicle;

    public function delete(Vehicle $vehicle): void;

    public function countByStatus(): array;

    public function totalInventoryValue(): float;

    public function getAvailableForTransfer(int $branchId): Collection;
}
