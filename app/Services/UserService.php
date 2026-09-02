<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function create(array $data): User
    {
        $user = User::create([
            'tenant_id'   => app('tenant')->id,
            'branch_id'   => $data['branch_id'],
            'name'        => $data['name'],
            'email'       => $data['email'],
            'phone'       => $data['phone'] ?? null,
            'password'    => Hash::make($data['password']),
            'cnic'        => $data['cnic'] ?? null,
            'designation' => $data['designation'] ?? null,
            'is_active'   => true,
        ]);

        if (!empty($data['role'])) {
            setPermissionsTeamId(app('tenant')->id);
            $user->assignRole($data['role']);
        }

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $update = [
            'branch_id'   => $data['branch_id'],
            'name'        => $data['name'],
            'email'       => $data['email'],
            'phone'       => $data['phone'] ?? null,
            'cnic'        => $data['cnic'] ?? null,
            'designation' => $data['designation'] ?? null,
        ];

        if (!empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $user->update($update);

        if (!empty($data['role'])) {
            setPermissionsTeamId(app('tenant')->id);
            $user->syncRoles([$data['role']]);
        }

        return $user->fresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function assignRole(User $user, string $roleName): void
    {
        setPermissionsTeamId(app('tenant')->id);
        $user->syncRoles([$roleName]);
    }
}
