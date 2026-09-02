@extends('layouts.app')
@section('title', isset($customer) ? 'Edit Customer' : 'Add Customer')
@section('breadcrumb', 'CRM / Customers / ' . (isset($customer) ? 'Edit' : 'Add'))

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">{{ isset($customer) ? 'Edit Customer' : 'Add Customer' }}</h4>
    <a href="{{ route('customers.index') }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

@include('crm.customers.partials.form')

<div class="d-flex justify-content-end gap-2 mt-3 mb-5">
    <a href="{{ route('customers.index') }}" class="btn btn-light">Cancel</a>
    <button type="submit" form="customerForm" class="btn btn-primary">
        <i class="bi bi-check2 me-1"></i>
        {{ isset($customer) ? 'Save Changes' : 'Add Customer' }}
    </button>
</div>
@endsection
