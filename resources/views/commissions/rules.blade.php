@extends('layouts.app')
@section('title','Commission Rules')
@section('breadcrumb','Finance / Commission Rules')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Commission Rules</h4>
    <a href="{{ route('commissions.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<div class="row g-4">
<div class="col-lg-5">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3"><h6 class="mb-0 fw-semibold">Add Rule</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('commission-rules.store') }}">
            @csrf
            <div class="mb-2"><input type="text" name="name" class="form-control form-control-sm" placeholder="Rule Name" required></div>
            <div class="mb-2">
                <select name="applies_to" class="form-select form-select-sm" required>
                    @foreach(\App\Models\CommissionRule::APPLIES_TO as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                </select>
            </div>
            <div class="mb-2">
                <select name="calc_type" class="form-select form-select-sm" required>
                    @foreach(\App\Models\CommissionRule::CALC_TYPES as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                </select>
            </div>
            <div class="mb-2"><input type="number" name="value" step="0.01" class="form-control form-control-sm" placeholder="Value (amount or %)" required></div>
            <div class="mb-2"><input type="number" name="min_sale_price" class="form-control form-control-sm" placeholder="Min sale price (optional)"></div>
            <button class="btn btn-primary btn-sm w-100">Add Rule</button>
        </form>
    </div>
</div>
</div>
<div class="col-lg-7">
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th class="ps-3">Name</th><th>Applies To</th><th>Calc</th><th>Value</th><th></th></tr></thead>
            <tbody>
                @foreach($rules as $r)
                <tr>
                    <td class="ps-3">{{ $r->name }}</td>
                    <td><span class="badge bg-light text-dark">{{ \App\Models\CommissionRule::APPLIES_TO[$r->applies_to] }}</span></td>
                    <td class="small">{{ \App\Models\CommissionRule::CALC_TYPES[$r->calc_type] }}</td>
                    <td>{{ $r->value }}{{ $r->calc_type !== 'fixed' ? '%' : '' }}</td>
                    <td>
                        <form method="POST" action="{{ route('commission-rules.toggle', $r) }}">
                            @csrf
                            <button class="btn btn-{{ $r->is_active ? 'success' : 'secondary' }} btn-sm">{{ $r->is_active ? 'Active' : 'Inactive' }}</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</div>
</div>
@endsection
