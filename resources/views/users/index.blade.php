@extends('layouts.app')
@section('title','Users')
@section('breadcrumb','Admin / Users')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Users</h4>
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Add User
    </a>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($users->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people fs-1 opacity-25 d-block mb-2"></i>
                <p>No users found.</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold">{{ $user->name }}</div>
                            <small class="text-muted">{{ $user->designation ?? '—' }}</small>
                        </td>
                        <td><small>{{ $user->email }}</small></td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge bg-primary-subtle text-primary">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $user->branch->name }}</span></td>
                        <td>
                            <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}-subtle text-{{ $user->is_active ? 'success' : 'danger' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td><small class="text-muted">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</small></td>
                        <td class="pe-4 text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-light btn-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('users.toggle-status', $user) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-{{ $user->is_active ? 'warning' : 'success' }} btn-sm" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $user->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user) }}"
                                      onsubmit="return confirm('Remove user {{ $user->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-top">{{ $users->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
</div>
@endsection
