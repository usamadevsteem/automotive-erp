@extends('layouts.app')
@section('title','My Profile')
@section('breadcrumb','Profile')

@section('content')
<div class="row g-4 justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-semibold">Profile Information</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Name</label>
                        <input type="text" name="name" value="{{ old('name',$user->name) }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" value="{{ $user->email }}" class="form-control" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone',$user->phone) }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Designation</label>
                        <input type="text" name="designation" value="{{ old('designation',$user->designation) }}" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Save Profile</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-semibold">Change Password</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">New Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning btn-sm">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
