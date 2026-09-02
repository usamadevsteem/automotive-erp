<?php

namespace App\Services;

use App\Models\Branch;

class BranchService
{
    public function create(array $data): Branch
    {
        $isFirst = Branch::count() === 0;

        return Branch::create([
            'tenant_id' => app('tenant')->id,
            'name'      => $data['name'],
            'code'      => strtoupper($data['code']),
            'address'   => $data['address'] ?? null,
            'city'      => $data['city'] ?? null,
            'phone'     => $data['phone'] ?? null,
            'email'     => $data['email'] ?? null,
            'is_main'   => $isFirst || ($data['is_main'] ?? false),
            'is_active' => true,
        ]);
    }

    public function update(Branch $branch, array $data): Branch
    {
        if (!empty($data['is_main'])) {
            Branch::where('id', '!=', $branch->id)->update(['is_main' => false]);
        }

        $branch->update([
            'name'      => $data['name'],
            'code'      => strtoupper($data['code']),
            'address'   => $data['address'] ?? null,
            'city'      => $data['city'] ?? null,
            'phone'     => $data['phone'] ?? null,
            'email'     => $data['email'] ?? null,
            'is_main'   => $data['is_main'] ?? $branch->is_main,
            'is_active' => $data['is_active'] ?? $branch->is_active,
        ]);

        return $branch->fresh();
    }

    public function delete(Branch $branch): void
    {
        $branch->delete();
    }
}
