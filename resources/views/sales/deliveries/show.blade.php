@extends('layouts.app')
@section('title', $delivery->delivery_number)
@section('breadcrumb', 'Sales / Deliveries / ' . $delivery->delivery_number)
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">{{ $delivery->delivery_number }}</h4>
    <a href="{{ route('invoices.show', $delivery->saleInvoice) }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Invoice</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="text-muted small text-uppercase mb-2">Customer</h6>
                <div class="fw-semibold">{{ $delivery->customer->full_name }}</div>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted small text-uppercase mb-2">Vehicle</h6>
                <div class="fw-semibold">{{ $delivery->vehicle->make->name }} {{ $delivery->vehicle->vehicleModel->name }} {{ $delivery->vehicle->year }}</div>
            </div>
        </div>
        <table class="table table-sm">
            <tr><td class="text-muted">Delivery Date</td><td>{{ $delivery->delivery_date->format('d M Y') }}</td></tr>
            <tr><td class="text-muted">Delivered By</td><td>{{ $delivery->deliveredBy->name }}</td></tr>
            @if($delivery->accessories_list)
            <tr><td class="text-muted">Accessories</td><td>{{ implode(', ', $delivery->accessories_list) }}</td></tr>
            @endif
            @if($delivery->condition_notes)
            <tr><td class="text-muted">Condition Notes</td><td>{{ $delivery->condition_notes }}</td></tr>
            @endif
        </table>
    </div>
</div>
@endsection
