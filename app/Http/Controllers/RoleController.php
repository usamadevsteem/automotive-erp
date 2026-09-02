<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roleService) {}

    public function index(): View
    {
        $roles = Role::where('tenant_id', app('tenant')->id)->withCount('users')->get();
        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissions = Permission::all()->groupBy(fn($p) => explode('-', $p->name)[1] ?? 'general');
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'          => [
                'required', 'string', 'max:100',
                \Illuminate\Validation\Rule::unique('roles', 'name')
                    ->where('tenant_id', app('tenant')->id)
                    ->where('guard_name', 'web'),
            ],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $this->roleService->create($data['name'], $data['permissions'] ?? []);
        return redirect()->route('roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role): View
    {
        abort_if($role->tenant_id !== app('tenant')->id, 404);

        $permissions     = Permission::all()->groupBy(fn($p) => explode('-', $p->name)[1] ?? 'general');
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->tenant_id !== app('tenant')->id, 404);

        $data = $request->validate([
            'name'          => [
                'required', 'string', 'max:100',
                \Illuminate\Validation\Rule::unique('roles', 'name')
                    ->where('tenant_id', app('tenant')->id)
                    ->where('guard_name', 'web')
                    ->ignore($role->id),
            ],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $this->roleService->update($role, $data['name'], $data['permissions'] ?? []);
        return redirect()->route('roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_if($role->tenant_id !== app('tenant')->id, 404);

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete a role assigned to users.');
        }
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted.');
    }

    public function syncPermissions(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->tenant_id !== app('tenant')->id, 404);

        $request->validate(['permissions' => ['nullable','array'], 'permissions.*' => ['string']]);
        $role->syncPermissions($request->permissions ?? []);
        return back()->with('success', 'Permissions updated.');
    }
}
