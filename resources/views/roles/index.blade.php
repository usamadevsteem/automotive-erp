@extends('layouts.app')
@section('title','Roles & Permissions')
@section('breadcrumb','Admin / Roles')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Roles & Permissions</h4>
    <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i> Add Role</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @forelse($roles as $role)
        <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
            <div>
                <div class="fw-semibold">{{ $role->name }}</div>
                <small class="text-muted">{{ $role->users_count }} users assigned · {{ $role->permissions->count() }} permissions</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('roles.edit', $role) }}" class="btn btn-light btn-sm"><i class="bi bi-pencil me-1"></i> Edit</a>
                @if($role->users_count == 0)
                <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Delete role {{ $role->name }}?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-shield fs-1 opacity-25 d-block mb-2"></i>
            <p>No roles found. Roles are created automatically when a dealership is registered.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
