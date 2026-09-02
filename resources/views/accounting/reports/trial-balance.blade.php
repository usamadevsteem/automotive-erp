@extends('layouts.app')
@section('title','Trial Balance')
@section('breadcrumb','Accounting / Reports / Trial Balance')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Trial Balance</h4>
</div>
<form method="GET" class="card border-0 shadow-sm mb-4"><div class="card-body py-3">
    <div class="row g-2">
        <div class="col-md-3"><input type="date" name="from" value="{{ $fromDate }}" class="form-control form-control-sm"></div>
        <div class="col-md-3"><input type="date" name="to" value="{{ $toDate }}" class="form-control form-control-sm"></div>
        <div class="col-md-2"><button class="btn btn-primary btn-sm">Apply</button></div>
    </div>
</div></form>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th class="ps-4">Code</th><th>Account</th><th>Type</th><th class="text-end">Debit</th><th class="text-end pe-4">Credit</th></tr></thead>
            <tbody>
                @php $totalD = 0; $totalC = 0; @endphp
                @foreach($data as $row)
                @php $totalD += $row['debit']; $totalC += $row['credit']; @endphp
                <tr>
                    <td class="ps-4 font-monospace small">{{ $row['code'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td><span class="badge bg-light text-dark">{{ ucfirst($row['type']) }}</span></td>
                    <td class="text-end">{{ $row['debit'] > 0 ? number_format($row['debit']) : '' }}</td>
                    <td class="text-end pe-4">{{ $row['credit'] > 0 ? number_format($row['credit']) : '' }}</td>
                </tr>
                @endforeach
                <tr class="table-light fw-bold">
                    <td colspan="3" class="ps-4">Total</td>
                    <td class="text-end">{{ number_format($totalD) }}</td>
                    <td class="text-end pe-4">{{ number_format($totalC) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
