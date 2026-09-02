<?php

namespace App\Services;

use Spatie\Permission\Models\Role;

class RoleService
{
    public function create(string $name, array $permissions = []): Role
    {
        $role = Role::create([
            'name'       => $name,
            'guard_name' => 'web',
            'tenant_id'  => app('tenant')->id,
        ]);

        if (!empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        return $role;
    }

    public function update(Role $role, string $name, array $permissions = []): Role
    {
        $role->update(['name' => $name]);
        $role->syncPermissions($permissions);
        return $role->fresh();
    }
}
