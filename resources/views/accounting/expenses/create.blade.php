@extends('layouts.app')
@section('title','Add Expense')
@section('breadcrumb','Accounting / Expenses / Add')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Add Expense</h4>
    <a href="{{ route('expenses.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<div class="row justify-content-center"><div class="col-lg-6">
<div class="card border-0 shadow-sm">
    <div class="card-body">@include('accounting.expenses.partials.form')</div>
    <div class="card-footer bg-white d-flex gap-2">
        <button type="submit" form="expenseForm" class="btn btn-primary">Record Expense</button>
        <a href="{{ route('expenses.index') }}" class="btn btn-light">Cancel</a>
    </div>
</div>
</div></div>
@endsection
