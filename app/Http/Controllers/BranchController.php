<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\BranchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function __construct(private readonly BranchService $branchService) {}

    public function index(): View
    {
        $branches = Branch::withCount('users')->orderByDesc('is_main')->orderBy('name')->get();
        return view('branches.index', compact('branches'));
    }

    public function create(): View
    {
        return view('branches.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = app('tenant')->id;
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'code'    => ['required', 'string', 'max:20', "unique:branches,code,NULL,id,tenant_id,{$tenantId},deleted_at,NULL"],
            'address' => ['nullable', 'string'],
            'city'    => ['nullable', 'string', 'max:100'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'email'   => ['nullable', 'email', 'max:150'],
            'is_main' => ['boolean'],
        ]);

        $this->branchService->create($data);
        return redirect()->route('branches.index')->with('success', 'Branch created.');
    }

    public function edit(Branch $branch): View
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $tenantId = app('tenant')->id;
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'code'    => ['required', 'string', 'max:20', "unique:branches,code,{$branch->id},id,tenant_id,{$tenantId},deleted_at,NULL"],
            'address' => ['nullable', 'string'],
            'city'    => ['nullable', 'string', 'max:100'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'email'   => ['nullable', 'email', 'max:150'],
            'is_main' => ['boolean'],
            'is_active'=> ['boolean'],
        ]);

        $this->branchService->update($branch, $data);
        return redirect()->route('branches.index')->with('success', 'Branch updated.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        if ($branch->is_main) return back()->with('error', 'Cannot delete the main branch.');
        if ($branch->users()->exists()) return back()->with('error', 'Branch has active users.');
        $this->branchService->delete($branch);
        return redirect()->route('branches.index')->with('success', 'Branch removed.');
    }
}
