@extends('layouts.app')
@section('title','Edit User')
@section('breadcrumb','Admin / Users / Edit')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Edit User</h4>
    <a href="{{ route('users.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold required">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold required">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">New Password <small class="text-muted">(leave blank to keep)</small></label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold required">Branch</label>
                    <select name="branch_id" class="form-select" required>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id', $user->branch_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Role</label>
                    <select name="role" class="form-select">
                        <option value="">No Change</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}" {{ optional($userRole)->name == $r->name ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">CNIC</label>
                    <input type="text" name="cnic" value="{{ old('cnic', $user->cnic) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Designation</label>
                    <input type="text" name="designation" value="{{ old('designation', $user->designation) }}" class="form-control">
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
