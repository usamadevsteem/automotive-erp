@extends('layouts.app')
@section('title','Journal Entries')
@section('breadcrumb','Accounting / Journal Entries')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Journal Entries</h4>
    @can('create-journal-entries')
    <a href="{{ route('journal-entries.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i> New Entry</a>
    @endcan
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th class="ps-4">Entry #</th><th>Date</th><th>Narration</th><th>Type</th><th>Debit</th><th>Credit</th><th class="pe-4"></th></tr>
            </thead>
            <tbody>
                @forelse($entries as $e)
                <tr>
                    <td class="ps-4 fw-semibold">{{ $e->entry_number }}</td>
                    <td><small>{{ $e->entry_date->format('d M Y') }}</small></td>
                    <td class="small">{{ $e->narration }} @if($e->is_auto)<span class="badge bg-info-subtle text-info ms-1">Auto</span>@endif</td>
                    <td><span class="badge bg-light text-dark">{{ ucfirst($e->entry_type) }}</span></td>
                    <td>PKR {{ number_format($e->total_debit) }}</td>
                    <td>PKR {{ number_format($e->total_credit) }}</td>
                    <td class="pe-4"><a href="{{ route('journal-entries.show', $e) }}" class="btn btn-light btn-sm"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5 text-muted">No journal entries found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-top">{{ $entries->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
