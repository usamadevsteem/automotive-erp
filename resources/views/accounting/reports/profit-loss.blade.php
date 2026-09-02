@extends('layouts.app')
@section('title','Profit & Loss')
@section('breadcrumb','Accounting / Reports / Profit & Loss')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Profit & Loss Statement</h4>
</div>
<form method="GET" class="card border-0 shadow-sm mb-4"><div class="card-body py-3">
    <div class="row g-2">
        <div class="col-md-3"><input type="date" name="from" value="{{ $fromDate }}" class="form-control form-control-sm"></div>
        <div class="col-md-3"><input type="date" name="to" value="{{ $toDate }}" class="form-control form-control-sm"></div>
        <div class="col-md-2"><button class="btn btn-primary btn-sm">Apply</button></div>
    </div>
</div></form>
<div class="row justify-content-center"><div class="col-lg-6">
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-sm">
            <tr><td>Revenue</td><td class="text-end">PKR {{ number_format($data['revenues']) }}</td></tr>
            <tr><td>Cost of Goods Sold</td><td class="text-end text-danger">- PKR {{ number_format($data['cogs']) }}</td></tr>
            <tr class="table-light fw-bold"><td>Gross Profit</td><td class="text-end">PKR {{ number_format($data['grossProfit']) }}</td></tr>
            <tr><td>Operating Expenses</td><td class="text-end text-danger">- PKR {{ number_format($data['expenses'] - $data['cogs']) }}</td></tr>
            <tr class="table-success fw-bold fs-5"><td>Net Profit</td><td class="text-end">PKR {{ number_format($data['netProfit']) }}</td></tr>
        </table>
    </div>
</div>
</div></div>
@endsection
