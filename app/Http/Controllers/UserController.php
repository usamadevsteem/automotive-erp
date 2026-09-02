<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(): View
    {
        $users = User::with(['branch', 'roles'])->latest()->paginate(20);
        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $branches = Branch::active()->get();
        $roles    = Role::where('tenant_id', app('tenant')->id)->get();
        return view('users.create', compact('branches', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', 'max:150', \Illuminate\Validation\Rule::unique('users', 'email')->where('tenant_id', app('tenant')->id)],
            'phone'       => ['nullable', 'string', 'max:20'],
            'password'    => ['required', 'min:8'],
            'cnic'        => ['nullable', 'string', 'max:20'],
            'designation' => ['nullable', 'string', 'max:100'],
            'branch_id'   => ['required', 'exists:branches,id'],
            'role'        => ['required', 'string'],
        ]);

        $this->userService->create($data);
        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $branches = Branch::active()->get();
        $roles    = Role::where('tenant_id', app('tenant')->id)->get();
        $userRole = $user->roles->first();
        return view('users.edit', compact('user', 'branches', 'roles', 'userRole'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $tenantId = app('tenant')->id;
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', "unique:users,email,{$user->id},id,tenant_id,{$tenantId},deleted_at,NULL"],
            'phone'       => ['nullable', 'string', 'max:20'],
            'password'    => ['nullable', 'min:8'],
            'cnic'        => ['nullable', 'string', 'max:20'],
            'designation' => ['nullable', 'string', 'max:100'],
            'branch_id'   => ['required', 'exists:branches,id'],
            'role'        => ['nullable', 'string'],
        ]);

        $this->userService->update($user, $data);
        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $this->userService->delete($user);
        return redirect()->route('users.index')->with('success', 'User removed.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', 'User status updated.');
    }

    public function assignRole(Request $request, User $user): RedirectResponse
    {
        $request->validate(['role' => ['required', 'string']]);
        $this->userService->assignRole($user, $request->role);
        return back()->with('success', 'Role assigned.');
    }
}
