@extends('layouts.app')
@section('title','Edit Branch')
@section('breadcrumb','Admin / Branches / Edit')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Edit Branch</h4>
    <a href="{{ route('branches.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<div class="row justify-content-center"><div class="col-lg-6">
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('branches.update', $branch) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label small fw-semibold required">Branch Name</label>
                    <input type="text" name="name" value="{{ old('name', $branch->name) }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold required">Branch Code</label>
                    <input type="text" name="code" value="{{ old('code', $branch->code) }}" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Address</label>
                    <textarea name="address" rows="2" class="form-control">{{ old('address', $branch->address) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">City</label>
                    <input type="text" name="city" value="{{ old('city', $branch->city) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Email</label>
                    <input type="email" name="email" value="{{ old('email', $branch->email) }}" class="form-control">
                </div>
                <div class="col-12 d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_main" value="1" id="isMain" {{ old('is_main', $branch->is_main) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="isMain">Main branch</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" {{ old('is_active', $branch->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="isActive">Active</label>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('branches.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection
