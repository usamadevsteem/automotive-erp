@extends('layouts.app')
@section('title','Add Branch')
@section('breadcrumb','Admin / Branches / Add')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Add Branch</h4>
    <a href="{{ route('branches.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<div class="row justify-content-center"><div class="col-lg-6">
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('branches.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label small fw-semibold required">Branch Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold required">Branch Code</label>
                    <input type="text" name="code" value="{{ old('code') }}" class="form-control @error('code') is-invalid @enderror" placeholder="e.g. LHR1" required>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold">Address</label>
                    <textarea name="address" rows="2" class="form-control">{{ old('address') }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">City</label>
                    <input type="text" name="city" value="{{ old('city') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control">
                </div>
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_main" value="1" id="isMain" {{ old('is_main') ? 'checked' : '' }}>
                        <label class="form-check-label small" for="isMain">Set as main branch</label>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Create Branch</button>
                <a href="{{ route('branches.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div></div>
@endsection
