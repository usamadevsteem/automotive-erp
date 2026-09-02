@extends('layouts.app')
@section('title','Account Ledger')
@section('breadcrumb','Accounting / Reports / Ledger')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Account Ledger — {{ $account->account_name }}</h4>
</div>
<form method="GET" class="card border-0 shadow-sm mb-4"><div class="card-body py-3">
    <div class="row g-2">
        <div class="col-md-3">
            <select name="account_id" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach($accounts as $a)<option value="{{ $a->id }}" {{ $account->id == $a->id ? 'selected' : '' }}>{{ $a->account_code }} — {{ $a->account_name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3"><input type="date" name="from" value="{{ $fromDate }}" class="form-control form-control-sm"></div>
        <div class="col-md-3"><input type="date" name="to" value="{{ $toDate }}" class="form-control form-control-sm"></div>
        <div class="col-md-2"><button class="btn btn-primary btn-sm">Apply</button></div>
    </div>
</div></form>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th class="ps-4">Date</th><th>Narration</th><th class="text-end">Debit</th><th class="text-end pe-4">Credit</th></tr></thead>
            <tbody>
                @forelse($lines as $line)
                <tr>
                    <td class="ps-4 small">{{ $line->journalEntry->entry_date->format('d M Y') }}</td>
                    <td class="small">{{ $line->journalEntry->narration }}</td>
                    <td class="text-end">{{ $line->debit_amount > 0 ? number_format($line->debit_amount) : '' }}</td>
                    <td class="text-end pe-4">{{ $line->credit_amount > 0 ? number_format($line->credit_amount) : '' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-4 text-muted">No transactions in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
