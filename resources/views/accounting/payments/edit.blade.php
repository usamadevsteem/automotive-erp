@extends('layouts.app')
@section('title','Edit Payment')
@section('breadcrumb','Accounting / Payments / Edit')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Edit Payment</h4>
    <a href="{{ route('payments.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<div class="row justify-content-center"><div class="col-lg-6">
<div class="card border-0 shadow-sm">
    <div class="card-body">@include('accounting.payments.partials.form')</div>
    <div class="card-footer bg-white d-flex gap-2">
        <button type="submit" form="paymentForm" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('payments.index') }}" class="btn btn-light">Cancel</a>
    </div>
</div>
</div></div>
@endsection
@push('scripts')
<script>document.addEventListener('DOMContentLoaded', () => window.initPaymentForm(document));</script>
@endpush
