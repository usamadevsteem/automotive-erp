<div class="row g-3">
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Full Name</small>
        <div class="fw-semibold">{{ $lead->full_name }}</div>
        @if($lead->customer_id)
            <span class="badge bg-success-subtle text-success" style="font-size:10px;">Linked Customer</span>
        @endif
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Status</small>
        <span class="badge bg-{{ $lead->status_color }}-subtle text-{{ $lead->status_color }}">
            {{ $lead->status_label }}
        </span>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Phone</small>
        <div><a href="tel:{{ $lead->phone }}" class="text-decoration-none">{{ $lead->phone }}</a></div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Email</small>
        <div>{{ $lead->email ?? '—' }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Source</small>
        <span class="badge bg-light text-dark">{{ \App\Models\Lead::SOURCES[$lead->source] ?? $lead->source }}</span>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Assigned To</small>
        <div>{{ $lead->assignedTo?->name ?? 'Unassigned' }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Vehicle Interest</small>
        <div>{{ $lead->vehicle_interest ?? '—' }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Budget</small>
        <div>{{ $lead->budget ? 'PKR ' . number_format($lead->budget) : '—' }}</div>
    </div>
    <div class="col-md-6">
        <small class="text-muted d-block mb-1">Next Follow-Up</small>
        <div class="{{ $lead->next_follow_up && $lead->next_follow_up->isPast() ? 'text-danger fw-semibold' : '' }}">
            {{ $lead->next_follow_up?->format('d M Y, H:i') ?? '—' }}
        </div>
    </div>
    @if($lead->notes)
    <div class="col-12">
        <small class="text-muted d-block mb-1">Notes</small>
        <div>{{ $lead->notes }}</div>
    </div>
    @endif
</div>

<!-- <div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('leads.show', $lead) }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-up-right-square me-1"></i> Open Full Page
    </a>
</div> -->

<hr>
<h6 class="small fw-semibold text-muted text-uppercase mb-2">Change Status</h6>
<form id="leadStatusForm-{{ $lead->id }}" data-url="{{ route('leads.status', $lead) }}"
      onsubmit="return submitLeadStatusChange(event, {{ $lead->id }})">
    @csrf
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <select name="status" class="form-select form-select-sm vf-lead-status" required>
                @foreach(\App\Models\Lead::STATUSES as $key => $info)
                    <option value="{{ $key }}" {{ $lead->status === $key ? 'selected' : '' }}>{{ $info['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5 vf-lead-lost-reason" style="{{ $lead->status === 'lost' ? '' : 'display:none;' }}">
            <input type="text" name="lost_reason" class="form-control form-control-sm"
                   placeholder="Reason for losing this lead" value="{{ $lead->lost_reason }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm w-100">Update</button>
        </div>
    </div>
    <div class="alert alert-danger py-1 px-2 mt-2 mb-0 d-none small" id="leadStatusError-{{ $lead->id }}"></div>
</form>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('leads.show', $lead) }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-up-right-square me-1"></i> Open Full Page
    </a>
</div>
