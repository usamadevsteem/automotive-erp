@extends('layouts.app')
@section('title','Trade-In Detail')
@section('breadcrumb','Sales / Trade-Ins / Detail')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Trade-In Evaluation</h4>
    <a href="{{ route('trade-ins.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<div class="row g-4">
<div class="col-md-8">
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <h6 class="text-muted small text-uppercase">Customer</h6>
                <div class="fw-semibold">{{ $tradeIn->customer->full_name }}</div>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted small text-uppercase">New Vehicle</h6>
                <div class="fw-semibold">{{ $tradeIn->newVehicle->make->name }} {{ $tradeIn->newVehicle->vehicleModel->name }}</div>
            </div>
        </div>
        <hr>
        <h6 class="text-muted small text-uppercase mb-2">Trade-In Vehicle</h6>
        <table class="table table-sm">
            <tr><td class="text-muted">Vehicle</td><td>{{ $tradeIn->trade_make }} {{ $tradeIn->trade_model }} {{ $tradeIn->trade_year }}</td></tr>
            <tr><td class="text-muted">Condition</td><td>{{ ucfirst($tradeIn->trade_condition) }}</td></tr>
            <tr><td class="text-muted">Mileage</td><td>{{ number_format($tradeIn->trade_mileage) }} km</td></tr>
            <tr><td class="text-muted">Market Value</td><td>PKR {{ number_format($tradeIn->market_value) }}</td></tr>
            <tr><td class="text-muted">Offered Value</td><td class="fw-bold">PKR {{ number_format($tradeIn->offered_value) }}</td></tr>
            @if($tradeIn->approved_value)
            <tr class="table-success"><td class="fw-bold">Approved Value</td><td class="fw-bold">PKR {{ number_format($tradeIn->approved_value) }}</td></tr>
            @endif
        </table>
    </div>
</div>
</div>
<div class="col-md-4">
    @if($tradeIn->status === 'pending')
    @can('approve-trade-ins')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><h6 class="mb-0 fw-semibold">Approve Trade-In</h6></div>
        <div class="card-body">
            <form method="POST" action="{{ route('trade-ins.approve', $tradeIn) }}">
                @csrf
                <label class="form-label small fw-semibold">Final Approved Value</label>
                <input type="number" name="approved_value" value="{{ $tradeIn->offered_value }}" class="form-control mb-2" required>
                <button type="submit" class="btn btn-success btn-sm w-100">Approve</button>
            </form>
        </div>
    </div>
    @endcan
    @else
    <div class="alert alert-{{ $tradeIn->status_color }}">{{ $tradeIn->status_label }}</div>
    @endif
</div>
</div>
@endsection
