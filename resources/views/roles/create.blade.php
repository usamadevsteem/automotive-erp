@extends('layouts.app')
@section('title','Create Role')
@section('breadcrumb','Admin / Roles / Create')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Create Role</h4>
    <a href="{{ route('roles.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<form method="POST" action="{{ route('roles.store') }}">
    @csrf
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="mb-3" style="max-width:300px;">
                <label class="form-label small fw-semibold required">Role Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3"><h6 class="mb-0 fw-semibold">Permissions</h6></div>
        <div class="card-body">
            @foreach($permissions as $module => $perms)
            <div class="mb-4">
                <div class="fw-semibold text-uppercase small text-muted mb-2" style="letter-spacing:1px;">{{ $module }}</div>
                <div class="row g-2">
                    @foreach($perms as $perm)
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="permissions[]" value="{{ $perm->name }}"
                                   id="perm_{{ $perm->id }}"
                                   {{ in_array($perm->name, old('permissions', [])) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="perm_{{ $perm->id }}">
                                {{ $perm->name }}
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Create Role</button>
        <a href="{{ route('roles.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>
@endsection
