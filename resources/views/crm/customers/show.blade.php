@extends('layouts.app')
@section('title', $customer->full_name)
@section('breadcrumb', 'CRM / Customers / ' . $customer->full_name)

@section('content')
<div class="d-flex align-items-start justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">{{ $customer->full_name }}</h4>
        <div class="text-muted small">
            @if($customer->father_husband_name) S/O {{ $customer->father_husband_name }} · @endif
            {{ $customer->mobile }}
            @if($customer->cnic) · {{ $customer->cnic }} @endif
        </div>
    </div>
    <div class="d-flex gap-2">
        @can('edit-customers')
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-light btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        @endcan
        @if($customer->mobile)
        <a href="https://wa.me/92{{ ltrim($customer->mobile,'0') }}"
           target="_blank" class="btn btn-success btn-sm">
            <i class="bi bi-whatsapp me-1"></i> WhatsApp
        </a>
        @endif
        <a href="{{ route('customers.index') }}" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
</div>

{{-- KPI Row --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Purchases</div>
                <div class="fw-bold fs-4">{{ $customer->total_purchases }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-1">Active Leads</div>
                <div class="fw-bold fs-4">{{ $customer->leads->where('status','!=','won')->where('status','!=','lost')->count() }}</div>
            </div>
        </div>
    </div>
   
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small mb-1">Activities</div>
                <div class="fw-bold fs-4">{{ $customer->activities->count() }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-0">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-info"><i class="bi bi-person me-1"></i>Info</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-activities"><i class="bi bi-clock-history me-1"></i>Activities ({{ $customer->activities->count() }})</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-purchases"><i class="bi bi-receipt me-1"></i>Purchases ({{ $customer->invoices->count() }})</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-leads"><i class="bi bi-funnel me-1"></i>Leads ({{ $customer->leads->count() }})</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-docs"><i class="bi bi-folder2 me-1"></i>Documents</a></li>
</ul>

<div class="tab-content">
    {{-- Info --}}
    <div class="tab-pane fade show active" id="tab-info">
        <div class="card border-0 border-top-0 shadow-sm rounded-0 rounded-bottom">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted w-40">Mobile</td><td>{{ $customer->mobile }}</td></tr>
                            <tr><td class="text-muted">Alt Mobile</td><td>{{ $customer->mobile_alt ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Email</td><td>{{ $customer->email ?? '—' }}</td></tr>
                            <tr><td class="text-muted">CNIC</td><td class="font-monospace">{{ $customer->cnic ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Occupation</td><td>{{ $customer->occupation ?? '—' }}</td></tr>
                            <tr><td class="text-muted">City</td><td>{{ $customer->city ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Address</td><td>{{ $customer->address ?? '—' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted w-40">Type</td><td>{{ \App\Models\Customer::TYPES[$customer->customer_type] }}</td></tr>
                            <tr><td class="text-muted">Source</td><td>{{ $customer->source_label }}</td></tr>
                            <tr><td class="text-muted">Tax Status</td><td><span class="badge bg-{{ $customer->tax_status === 'filer' ? 'success' : 'secondary' }}-subtle text-{{ $customer->tax_status === 'filer' ? 'success' : 'secondary' }}">{{ ucfirst($customer->tax_status) }}</span></td></tr>
                            <tr><td class="text-muted">Assigned To</td><td>{{ $customer->assignedTo?->name ?? 'Unassigned' }}</td></tr>
                            <tr><td class="text-muted">Branch</td><td>{{ $customer->branch->name }}</td></tr>
                            <tr><td class="text-muted">Added</td><td>{{ $customer->created_at->format('d M Y') }}</td></tr>
                        </table>
                        @if($customer->notes)
                        <div class="alert alert-light border py-2 small mt-2">
                            <i class="bi bi-sticky me-1"></i>{{ $customer->notes }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Activities --}}
    <div class="tab-pane fade" id="tab-activities">
        <div class="card border-0 border-top-0 shadow-sm rounded-0 rounded-bottom">
            <div class="card-body">
                {{-- Log Activity --}}
                <form method="POST" action="{{ route('customers.activities.store', $customer) }}" class="mb-4">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <select name="type" class="form-select form-select-sm" required>
                                @foreach(\App\Models\CustomerActivity::TYPES as $key => $meta)
                                    <option value="{{ $key }}">{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="subject" class="form-control form-control-sm" placeholder="Subject">
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="description" class="form-control form-control-sm" placeholder="Notes / outcome">
                        </div>
                        <div class="col-md-2">
                            <input type="datetime-local" name="scheduled_at" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Log</button>
                        </div>
                    </div>
                </form>

                {{-- Timeline --}}
                @forelse($customer->activities as $activity)
                <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                    <div class="rounded-circle bg-{{ $activity->type_color }}-subtle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:36px;height:36px;">
                        <i class="{{ $activity->type_icon }} text-{{ $activity->type_color }} small"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <span class="fw-semibold small">{{ $activity->type_label }}</span>
                                @if($activity->subject) — <span class="small">{{ $activity->subject }}</span> @endif
                            </div>
                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                        </div>
                        @if($activity->description)<p class="text-muted small mb-0 mt-1">{{ $activity->description }}</p>@endif
                        @if($activity->outcome)<p class="text-success small mb-0"><i class="bi bi-check2 me-1"></i>{{ $activity->outcome }}</p>@endif
                        <small class="text-muted">by {{ $activity->createdBy->name }}</small>
                    </div>
                </div>
                @empty
                <p class="text-muted small">No activities logged yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Purchases --}}
    <div class="tab-pane fade" id="tab-purchases">
        <div class="card border-0 border-top-0 shadow-sm rounded-0 rounded-bottom">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Invoice #</th>
                            <th>Vehicle</th>
                            <th>Amount</th>
                            <th>Balance Due</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="pe-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->invoices as $invoice)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->vehicle->make->name }} {{ $invoice->vehicle->vehicleModel->name }} {{ $invoice->vehicle->year }}</td>
                            <td>{{ $invoice->net_amount_formatted }}</td>
                            <td class="{{ $invoice->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                                {{ $invoice->balance_due_formatted }}
                            </td>
                            <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                            <td><span class="badge bg-{{ $invoice->status_color }}-subtle text-{{ $invoice->status_color }}">{{ $invoice->status_label }}</span></td>
                            <td class="pe-4"><a href="{{ route('invoices.show', $invoice) }}" class="btn btn-light btn-sm"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">No purchases yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Leads --}}
    <div class="tab-pane fade" id="tab-leads">
        <div class="card border-0 border-top-0 shadow-sm rounded-0 rounded-bottom">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Interest</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Follow Up</th>
                            <th class="pe-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->leads as $lead)
                        <tr>
                            <td class="ps-4">{{ $lead->vehicle_interest ?? '—' }}</td>
                            <td><span class="badge bg-light text-dark">{{ \App\Models\Customer::SOURCES[$lead->source] ?? $lead->source }}</span></td>
                            <td><span class="badge bg-{{ $lead->status_color }}-subtle text-{{ $lead->status_color }}">{{ $lead->status_label }}</span></td>
                            <td><small class="text-muted">{{ $lead->next_follow_up?->format('d M Y H:i') ?? '—' }}</small></td>
                            <td class="pe-4"><a href="{{ route('leads.show', $lead) }}" class="btn btn-light btn-sm"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">No leads.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Documents --}}
    <div class="tab-pane fade" id="tab-docs">
        <div class="card border-0 border-top-0 shadow-sm rounded-0 rounded-bottom">
            <div class="card-body">
                <form method="POST" action="{{ route('customers.documents.upload', $customer) }}"
                      enctype="multipart/form-data" class="mb-4">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <select name="document_type" class="form-select form-select-sm" required>
                                @foreach(\App\Models\CustomerDocument::TYPES as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="file" name="file" class="form-control form-control-sm"
                                   accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                        </div>
                    </div>
                </form>

                <div class="row g-3">
                    @forelse($customer->documents as $doc)
                    <div class="col-md-4">
                        <div class="border rounded p-3 d-flex align-items-center gap-3">
                            <i class="bi bi-file-earmark fs-4 text-primary"></i>
                            <div class="flex-grow-1">
                                <div class="fw-semibold small">{{ \App\Models\CustomerDocument::TYPES[$doc->document_type] }}</div>
                                <small class="text-muted">{{ $doc->uploaded_at->format('d M Y') }}</small>
                            </div>
                            <div class="d-flex gap-1">
                                <a href="{{ $doc->file_url }}" target="_blank" class="btn btn-light btn-sm"><i class="bi bi-eye"></i></a>
                                <form method="POST" action="{{ route('customers.documents.delete', [$customer,$doc]) }}"
                                      onsubmit="return confirm('Remove document?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12"><p class="text-muted small">No documents uploaded.</p></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
