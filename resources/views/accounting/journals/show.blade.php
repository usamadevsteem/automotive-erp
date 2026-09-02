@extends('layouts.app')
@section('title', $journalEntry->entry_number)
@section('breadcrumb', 'Accounting / Journal Entries / ' . $journalEntry->entry_number)
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">{{ $journalEntry->entry_number }}</h4>
    <a href="{{ route('journal-entries.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <p class="text-muted">{{ $journalEntry->narration }}</p>
        <table class="table table-sm">
            <thead><tr><th>Account</th><th class="text-end">Debit</th><th class="text-end">Credit</th></tr></thead>
            <tbody>
                @foreach($journalEntry->lines as $line)
                <tr>
                    <td>{{ $line->account->account_code }} — {{ $line->account->account_name }}</td>
                    <td class="text-end">{{ $line->debit_amount > 0 ? number_format($line->debit_amount) : '' }}</td>
                    <td class="text-end">{{ $line->credit_amount > 0 ? number_format($line->credit_amount) : '' }}</td>
                </tr>
                @endforeach
                <tr class="fw-bold table-light">
                    <td>Total</td>
                    <td class="text-end">{{ number_format($journalEntry->total_debit) }}</td>
                    <td class="text-end">{{ number_format($journalEntry->total_credit) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
