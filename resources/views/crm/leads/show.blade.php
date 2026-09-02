@extends('layouts.app')
@section('title', $lead->full_name . ' — Lead')
@section('breadcrumb', 'CRM / Leads / ' . $lead->full_name)

@section('content')
<div class="d-flex align-items-start justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0">{{ $lead->full_name }}</h4>
            <span class="badge bg-{{ $lead->status_color }}-subtle text-{{ $lead->status_color }} border border-{{ $lead->status_color }}-subtle">
                {{ $lead->status_label }}
            </span>
        </div>
        <small class="text-muted">
                {{ $lead->phone }}
                @if($lead->email)
                    · {{ $lead->email }}
                @endif
                · Source:
            <strong>
                {{ \App\Models\Lead::SOURCES[$lead->source] ?? $lead->source }}
            </strong>
        </small>
    </div>
    <div class="d-flex gap-2">
        @can('edit-leads')
        <a href="{{ route('leads.edit', $lead) }}" class="btn btn-light btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        @endcan
        @if(!$lead->customer_id)
        <form method="POST" action="{{ route('leads.convert', $lead) }}">
            @csrf
            <button class="btn btn-success btn-sm">
                <i class="bi bi-person-check me-1"></i> Convert to Customer
            </button>
        </form>
        @else
        <a href="{{ route('customers.show', $lead->customer_id) }}" class="btn btn-info btn-sm">
            <i class="bi bi-person me-1"></i> View Customer
        </a>
        @endif
        <a href="{{ route('leads.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
    </div>
</div>

<div class="row g-4">
    {{-- Left: Lead info + actions --}}
    <div class="col-lg-4">
        {{-- Status Update --}}
        @if($lead->isOpen())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">Update Status</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('leads.status', $lead) }}">
                    @csrf @method('PATCH')
                    <div class="mb-2">
                        <select name="status" class="form-select form-select-sm" required>
                            @foreach(\App\Models\Lead::STATUSES as $k => $meta)
                                <option value="{{ $k }}" {{ $lead->status === $k ? 'selected' : '' }}>
                                    {{ $meta['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2" id="lostReasonBox" style="{{ $lead->status === 'lost' ? '' : 'display:none' }}">
                        <input type="text" name="lost_reason" class="form-control form-control-sm"
                               placeholder="Reason for losing..." value="{{ $lead->lost_reason }}">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Update Status</button>
                </form>
            </div>
        </div>
        @endif

        {{-- Assign --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">Assignment</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('leads.assign', $lead) }}">
                    @csrf @method('PATCH')
                    <div class="d-flex gap-2">
                        <select name="assigned_to" class="form-select form-select-sm flex-grow-1">
                            <option value="">Unassigned</option>
                            @foreach($salesUsers as $u)
                                <option value="{{ $u->id }}" {{ $lead->assigned_to == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">Assign</button>
                    </div>
                </form>
                @if($lead->assignedTo)
                <small class="text-muted mt-1 d-block">Currently: {{ $lead->assignedTo->name }}</small>
                @endif
            </div>
        </div>

        {{-- Follow-up scheduler --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">Schedule Follow-Up</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('leads.follow-up', $lead) }}">
                    @csrf
                    <div class="mb-2">
                        <input type="datetime-local" name="next_follow_up"
                               value="{{ $lead->next_follow_up?->format('Y-m-d\TH:i') }}"
                               class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="note" class="form-control form-control-sm"
                               placeholder="Follow-up note (optional)">
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">Schedule</button>
                </form>
                @if($lead->next_follow_up)
                <div class="mt-2 small {{ $lead->next_follow_up->isPast() ? 'text-danger' : 'text-muted' }}">
                    <i class="bi bi-clock me-1"></i>
                    Next: {{ $lead->next_follow_up->format('d M Y H:i') }}
                    {{ $lead->next_follow_up->isPast() ? '(overdue)' : '' }}
                </div>
                @endif
            </div>
        </div>

        {{-- Lead details --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">Lead Details</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">


                    <tr><td class="text-muted">Vehicle</td><td>
                        @if($lead->vehicle_interest)
                        <strong>{{ $lead->vehicle_interest }}</strong>
                        @else
                         —
                        @endif
                        </td>
                    </tr>
                    <tr><td class="text-muted">Budget</td><td>{{ $lead->budget ? 'PKR '.number_format($lead->budget) : '—' }}</td></tr>
                    <tr><td class="text-muted">Source</td><td>{{ \App\Models\Lead::SOURCES[$lead->source] ?? $lead->source }}</td></tr>
                    <tr><td class="text-muted">Branch</td><td>{{ $lead->branch->name }}</td></tr>
                    <tr><td class="text-muted">Created</td><td>{{ $lead->created_at->format('d M Y') }}</td></tr>
                    @if($lead->converted_at)
                    <tr><td class="text-muted">Converted</td><td class="text-success">{{ $lead->converted_at->format('d M Y') }}</td></tr>
                    @endif
                    @if($lead->lost_reason)
                    <tr><td class="text-muted">Lost Reason</td><td class="text-danger small">{{ $lead->lost_reason }}</td></tr>
                    @endif
                </table>
                @if($lead->notes)
                <div class="alert alert-light border py-2 small mt-2">
                    <i class="bi bi-sticky me-1"></i>{{ $lead->notes }}
                </div>
                @endif

                @if($lead->isOpen())
                <div class="mt-3 pt-3 border-top d-flex gap-2">
                    <a href="{{ route('quotations.create', ['customer_id' => $lead->customer_id, 'lead_id' => $lead->id]) }}"
                       class="btn btn-outline-primary btn-sm flex-grow-1">
                        <i class="bi bi-file-text me-1"></i> Create Quotation
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right: Activity timeline --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-semibold">Activity Timeline</h6>
            </div>
            <div class="card-body">

           


               {{-- Log activity against lead --}}
                <form
                    method="POST"
                    action="{{ route('leads.activities.store', $lead) }}"
                    class="mb-4"
                >
                    @csrf

                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Type</label>
                            <select name="type" class="form-select form-select-sm" required>
                                @foreach(\App\Models\CustomerActivity::TYPES as $k => $meta)
                                    <option value="{{ $k }}">
                                        {{ $meta['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Subject</label>
                            <input
                                type="text"
                                name="subject"
                                class="form-control form-control-sm"
                                placeholder="Subject"
                                value="{{ old('subject') }}"
                            >
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Notes</label>
                            <input
                                type="text"
                                name="description"
                                class="form-control form-control-sm"
                                placeholder="Notes"
                                value="{{ old('description') }}"
                            >
                        </div>
                        <div class="col-md-2">
                            <button
                                type="submit"
                                class="btn btn-primary btn-sm w-100"
                            >
                                Log Activity
                            </button>
                        </div>
                    </div>
                </form>
             

                @forelse($lead->activities as $activity)
                <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                    <div class="rounded-circle bg-{{ $activity->type_color }}-subtle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:36px;height:36px;">
                        <i class="{{ $activity->type_icon }} text-{{ $activity->type_color }} small"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold small">{{ $activity->type_label }}
                                @if($activity->subject) — {{ $activity->subject }} @endif
                            </span>
                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                        </div>
                        @if($activity->description)
                        <p class="text-muted small mb-0 mt-1">{{ $activity->description }}</p>
                        @endif
                        <small class="text-muted">by {{ $activity->createdBy->name }}</small>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-clock-history fs-3 opacity-25 d-block mb-2"></i>
                    <p class="small">No activities yet.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelector('[name="status"]')?.addEventListener('change', function() {
    document.getElementById('lostReasonBox').style.display = this.value === 'lost' ? '' : 'none';
});
</script>
@endpush
