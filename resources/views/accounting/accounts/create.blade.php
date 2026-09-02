@extends('layouts.app')
@section('title','Add Account')
@section('breadcrumb','Accounting / Accounts / Add')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Add Account</h4>
    <a href="{{ route('accounts.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<div class="row justify-content-center"><div class="col-lg-6">
<div class="card border-0 shadow-sm">
    <div class="card-body">
        @include('accounting.accounts.partials.form')
    </div>
    <div class="card-footer bg-white d-flex gap-2">
        <button type="submit" form="accountForm" class="btn btn-primary">Create Account</button>
        <a href="{{ route('accounts.index') }}" class="btn btn-light">Cancel</a>
    </div>
</div>
</div></div>
@endsection
