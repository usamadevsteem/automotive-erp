
<div class="row g-3 mb-4">
    @can('view-vehicle-cost')
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Total Cost</div>
                <div class="fw-bold fs-5">{{ $vehicle->total_cost_formatted }}</div>
                <small class="text-muted">Purchase + Landing + Repair + Misc</small>
            </div>
        </div>
    </div>
    @endcan
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Sale Price</div>
                <div class="fw-bold fs-5 text-primary">{{ $vehicle->sale_price_formatted }}</div>
                <small class="text-muted">Min: PKR {{ number_format($vehicle->min_sale_price) }}</small>
            </div>
        </div>
    </div>
    @can('view-vehicle-cost')
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Expected Profit</div>
                <div class="fw-bold fs-5 {{ $vehicle->expected_profit >= 0 ? 'text-success' : 'text-danger' }}">
                    PKR {{ number_format($vehicle->expected_profit) }}
                </div>
                <small class="text-muted">
                    {{ $vehicle->sale_price > 0 ? round(($vehicle->expected_profit / $vehicle->sale_price) * 100, 1) : 0 }}% margin
                </small>
            </div>
        </div>
    </div>
    @endcan
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">QR Scans</div>
                <div class="fw-bold fs-5">{{ number_format($qrStats['total']) }}</div>
                <small class="text-muted">{{ $qrStats['today'] }} today · {{ $qrStats['this_month'] }} this month</small>
            </div>
        </div>
    </div>
</div>

{{-- ── Nav Tabs ─────────────────────────────────────────────────────── --}}
<ul class="nav nav-tabs mb-0" id="vehicleTabs">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab-details">
            <i class="bi bi-info-circle me-1"></i> Details
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-costs">
            <i class="bi bi-calculator me-1"></i> Costs
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-file">
            <i class="bi bi-folder2 me-1"></i> Vehicle File
            @if($completeness < 100)
                <span class="badge bg-warning text-dark ms-1">{{ $completeness }}%</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-qr">
            <i class="bi bi-qr-code me-1"></i> QR Code
        </a>
    </li>
    @can('transfer-vehicles')
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-transfers" id="transfers">
            <i class="bi bi-arrow-left-right me-1"></i> Transfers
        </a>
    </li>
    @endcan
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-history">
            <i class="bi bi-clock-history me-1"></i> History
        </a>
    </li>
</ul>

<div class="tab-content">

    {{-- ── TAB: DETAILS ───────────────────────────────────────────── --}}
    <div class="tab-pane fade show active" id="tab-details">
        <div class="card border-0 border-top-0 shadow-sm rounded-0 rounded-bottom">
            <div class="card-body">
                <div class="row g-4">
                    {{-- Vehicle Info --}}
                    <div class="col-md-6">
                        <h6 class="text-muted text-uppercase small mb-3" style="letter-spacing:1px">Vehicle Info</h6>
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted w-40">Make</td><td class="fw-semibold">{{ $vehicle->make->name }}</td></tr>
                            <tr><td class="text-muted">Model</td><td class="fw-semibold">{{ $vehicle->vehicleModel->name }}</td></tr>
                            <tr><td class="text-muted">Variant</td><td>{{ $vehicle->variant?->name ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Year</td><td>{{ $vehicle->year }}</td></tr>
                            <tr><td class="text-muted">Category</td><td>{{ \App\Models\Vehicle::CATEGORIES[$vehicle->category] }}</td></tr>
                            <tr><td class="text-muted">Color</td><td>{{ $vehicle->color ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Mileage</td><td>{{ $vehicle->mileage_formatted }}</td></tr>
                            <tr><td class="text-muted">Fuel Type</td><td>{{ \App\Models\Vehicle::FUEL_TYPES[$vehicle->fuel_type] }}</td></tr>
                            <tr><td class="text-muted">Transmission</td><td>{{ \App\Models\Vehicle::TRANSMISSIONS[$vehicle->transmission] }}</td></tr>
                            <tr><td class="text-muted">Engine</td><td>{{ $vehicle->engine_capacity ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Condition</td><td>{{ \App\Models\Vehicle::CONDITIONS[$vehicle->condition_grade] }}</td></tr>
                        </table>
                    </div>

                    {{-- Identity & Branch --}}
                    <div class="col-md-6">
                        <h6 class="text-muted text-uppercase small mb-3" style="letter-spacing:1px">Identity</h6>
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted w-40">Reg. Number</td><td class="fw-semibold font-monospace">{{ $vehicle->registration_number ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Chassis #</td><td class="font-monospace">{{ $vehicle->chassis_number ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Engine #</td><td class="font-monospace">{{ $vehicle->engine_number ?? '—' }}</td></tr>
                            <tr><td class="text-muted">VIN</td><td class="font-monospace">{{ $vehicle->vin_number ?? '—' }}</td></tr>
                            <tr><td class="text-muted">Import Status</td><td>{{ \App\Models\Vehicle::IMPORT_STATUSES[$vehicle->import_status] }}</td></tr>
                            @if($vehicle->auction_grade)
                            <tr><td class="text-muted">Auction Grade</td><td>{{ $vehicle->auction_grade }}</td></tr>
                            @endif
                            <tr><td class="text-muted">Branch</td><td>{{ $vehicle->branch->name }}</td></tr>
                            <tr><td class="text-muted">Reg. Year</td><td>{{ $vehicle->registration_year ?? '—' }}</td></tr>
                        </table>

                        @if($vehicle->notes)
                        <h6 class="text-muted text-uppercase small mb-2 mt-3" style="letter-spacing:1px">Notes</h6>
                        <p class="text-muted small">{{ $vehicle->notes }}</p>
                        @endif
                    </div>
                </div>

                {{-- Status Change (quick action) --}}
                @can('edit-vehicles')
                @if(!$vehicle->isSold())
                <hr>
                <h6 class="text-muted text-uppercase small mb-3" style="letter-spacing:1px">Change Status</h6>
                <form id="statusForm-{{ $vehicle->id }}" data-url="{{ route('vehicles.status', $vehicle) }}"
                      class="d-flex gap-2 align-items-end flex-wrap"
                      onsubmit="return submitVehicleStatusChange(event, {{ $vehicle->id }})">
                    @csrf
                    @php
                        $allowedTransitions = match($vehicle->status) {
                            'pending_inspection' => ['available'],
                            'available'          => ['reserved'],
                            'reserved'           => ['available'],
                            default              => [],
                        };
                    @endphp
                    <div>
                        <select name="status" class="form-select form-select-sm" style="min-width:180px" required>
                            <option value="">Select new status</option>
                            @foreach($allowedTransitions as $status)
                                <option value="{{ $status }}">{{ \App\Models\Vehicle::STATUSES[$status] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <input type="text" name="reason" class="form-control form-control-sm"
                               placeholder="Reason (optional)" style="min-width:200px">
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-primary">
                        Update Status
                    </button>
                    <div class="alert alert-danger py-1 px-2 mb-0 d-none small" id="statusFormError-{{ $vehicle->id }}"></div>
                </form>
                @endif
                @endcan
            </div>
        </div>
    </div>

    {{-- ── TAB: COSTS ─────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="tab-costs">
        <div class="card border-0 border-top-0 shadow-sm rounded-0 rounded-bottom">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="fw-semibold mb-3">Cost Breakdown</h6>
                        <table class="table table-sm">
                            <tr>
                                <td>Purchase Price</td>
                                <td class="text-end fw-semibold">PKR {{ number_format($vehicle->purchase_price) }}</td>
                            </tr>
                            <tr>
                                <td>Landing Cost <small class="text-muted">(Import costs)</small></td>
                                <td class="text-end">PKR {{ number_format($vehicle->landing_cost) }}</td>
                            </tr>
                            <tr>
                                <td>Repair Cost</td>
                                <td class="text-end">PKR {{ number_format($vehicle->repair_cost) }}</td>
                            </tr>
                            <tr>
                                <td>Miscellaneous</td>
                                <td class="text-end">PKR {{ number_format($vehicle->misc_cost) }}</td>
                            </tr>
                            <tr class="table-light fw-bold">
                                <td>Total Cost</td>
                                <td class="text-end">PKR {{ number_format($vehicle->total_cost) }}</td>
                            </tr>
                            <tr>
                                <td>Sale Price</td>
                                <td class="text-end text-primary fw-bold">PKR {{ number_format($vehicle->sale_price) }}</td>
                            </tr>
                            <tr>
                                <td>Expected Profit</td>
                                <td class="text-end fw-bold {{ $vehicle->expected_profit >= 0 ? 'text-success' : 'text-danger' }}">
                                    PKR {{ number_format($vehicle->expected_profit) }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    @if($vehicle->import_status !== 'local')
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-semibold mb-0">Import Cost Breakdown</h6>
                            @can('edit-vehicles')
                            <a href="{{ route('vehicles.import-costs.edit', $vehicle) }}"
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </a>
                            @endcan
                        </div>

                        @if($vehicle->importCost)
                            <table class="table table-sm">
                                @foreach($vehicle->importCost->getCostBreakdown() as $line)
                                @if($line['amount'] > 0)
                                <tr>
                                    <td class="text-muted">{{ $line['label'] }}</td>
                                    <td class="text-end">PKR {{ number_format($line['amount']) }}</td>
                                </tr>
                                @endif
                                @endforeach
                                <tr class="table-light fw-bold">
                                    <td>Total Import Cost</td>
                                    <td class="text-end">PKR {{ number_format($vehicle->importCost->total_import_cost) }}</td>
                                </tr>
                            </table>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-receipt fs-3 d-block mb-2 opacity-25"></i>
                                No import costs recorded yet.
                                @can('edit-vehicles')
                                <div class="mt-2">
                                    <a href="{{ route('vehicles.import-costs.edit', $vehicle) }}"
                                       class="btn btn-sm btn-outline-primary">Add Import Costs</a>
                                </div>
                                @endcan
                            </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── TAB: VEHICLE FILE ───────────────────────────────────────── --}}
    <div class="tab-pane fade" id="tab-file">
        <div class="card border-0 border-top-0 shadow-sm rounded-0 rounded-bottom">
            <div class="card-body">

                {{-- Completeness bar --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="fw-semibold text-muted">File Completeness</small>
                        <small class="fw-bold {{ $completeness === 100 ? 'text-success' : 'text-warning' }}">
                            {{ $completeness }}%
                        </small>
                    </div>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar {{ $completeness === 100 ? 'bg-success' : 'bg-warning' }}"
                             style="width: {{ $completeness }}%"></div>
                    </div>
                </div>

                {{-- Checklist --}}
                <div class="row g-3 mb-4">
                    @foreach($checklist as $item)
                    <div class="col-md-6">
                        <div class="d-flex align-items-start gap-3 p-3 rounded border
                            {{ $item['uploaded'] ? 'border-success-subtle bg-success-subtle' : 'border-dashed bg-light' }}">
                            <div class="mt-1">
                                @if($item['uploaded'])
                                    @if($item['is_expired'])
                                        <i class="bi bi-exclamation-circle-fill text-danger fs-5"></i>
                                    @else
                                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                    @endif
                                @else
                                    <i class="bi bi-circle text-muted fs-5"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold small">
                                    {{ $item['label'] }}
                                    @if($item['required'])
                                        <span class="text-danger">*</span>
                                    @endif
                                </div>
                                @if($item['uploaded'] && $item['document'])
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        @if($item['document']->expiry_date)
                                            <span class="badge bg-{{ match($item['expiry_status']) {
                                                'expired' => 'danger',
                                                'critical' => 'danger',
                                                'warning' => 'warning',
                                                default => 'success'
                                            } }}-subtle text-{{ match($item['expiry_status']) {
                                                'expired' => 'danger',
                                                'critical' => 'danger',
                                                'warning' => 'warning',
                                                default => 'success'
                                            } }} small">
                                                Exp: {{ $item['document']->expiry_date->format('d M Y') }}
                                            </span>
                                        @endif
                                        @if($item['is_verified'])
                                            <span class="badge bg-success-subtle text-success small">Verified</span>
                                        @endif
                                        <a href="{{ $item['document']->file_url }}"
                                           target="_blank" class="btn btn-link btn-sm p-0">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @can('edit-vehicles')
                                        @if(!$item['is_verified'])
                                        <button type="button" class="btn btn-link btn-sm p-0 text-success"
                                                onclick="verifyVehicleDocument({{ $vehicle->id }}, {{ $item['document']->id }})">
                                            <i class="bi bi-check2-circle"></i>
                                        </button>
                                        @endif
                                        <button type="button" class="btn btn-link btn-sm p-0 text-danger"
                                                onclick="deleteVehicleDocument({{ $vehicle->id }}, {{ $item['document']->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                @else
                                    <small class="text-muted">Not uploaded</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Upload form (multi-row: add several documents, then upload all together) --}}
                @can('edit-vehicles')
                <hr>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-semibold mb-0">Upload Documents</h6>
                    <button type="button" class="btn btn-outline-primary btn-sm"
                            data-doc-types="{{ json_encode(\App\Models\VehicleFileDocument::DOCUMENT_TYPES) }}"
                            data-expirable-types="{{ json_encode(\App\Models\VehicleFileDocument::EXPIRABLE_TYPES) }}"
                            onclick="addDocumentRow('{{ $vehicle->id }}', this)">
                        <i class="bi bi-plus-circle me-1"></i> Add Document
                    </button>
                </div>

                <form id="documentBatchForm-{{ $vehicle->id }}"
                      data-url="{{ route('vehicles.documents.upload', $vehicle) }}"
                      data-vehicle-id="{{ $vehicle->id }}">
                    @csrf
                    <div id="documentRows-{{ $vehicle->id }}"></div>

                    <div class="alert alert-danger py-2 px-3 mt-2 mb-0 d-none" id="documentBatchError-{{ $vehicle->id }}"></div>

                    <div class="text-center py-3 text-muted small" id="documentRowsEmpty-{{ $vehicle->id }}">
                        Click "Add Document" to start. You can add several, then upload them all at once.
                    </div>

                    <div class="mt-3 d-none" id="documentUploadAllWrap-{{ $vehicle->id }}">
                        <button type="button" class="btn btn-primary btn-sm" onclick="submitAllDocuments('{{ $vehicle->id }}')">
                            <i class="bi bi-upload me-1"></i> Upload All
                        </button>
                    </div>
                </form>
                @endcan


            </div>
        </div>
    </div>

    {{-- ── TAB: QR CODE ────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="tab-qr">
        <div class="card border-0 border-top-0 shadow-sm rounded-0 rounded-bottom">
            <div class="card-body">
                <div class="row align-items-center g-4">
                    <div class="col-md-4 text-center">
                        @if($qrImageUrl)
                            <img src="{{ $qrImageUrl }}" alt="QR Code"
                                 class="img-fluid border rounded p-3" style="max-width:220px">
                        @else
                            <div class="text-muted py-4">
                                <i class="bi bi-qr-code fs-1 opacity-25"></i>
                                <p class="small mt-2">QR code not generated yet.</p>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <h6 class="fw-semibold mb-3">QR Code Details</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted w-40">Code</td>
                                <td class="font-monospace">{{ $vehicle->qr_code ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Public URL</td>
                                <td>
                                    @if($vehicle->qr_code)
                                        <a href="{{ route('vehicles.qr.public', $vehicle->qr_code) }}"
                                           target="_blank" class="text-break small">
                                            {{ route('vehicles.qr.public', $vehicle->qr_code) }}
                                        </a>
                                    @else
                                        <span class="text-muted small">Not generated yet — click "Regenerate QR Code" below.</span>
                                    @endif
                                </td>
                            </tr>
                            <tr><td class="text-muted">Total Scans</td><td>{{ $qrStats['total'] }}</td></tr>
                            <tr><td class="text-muted">Today</td><td>{{ $qrStats['today'] }}</td></tr>
                            <tr><td class="text-muted">This Week</td><td>{{ $qrStats['this_week'] }}</td></tr>
                            <tr><td class="text-muted">This Month</td><td>{{ $qrStats['this_month'] }}</td></tr>
                        </table>

                        @can('edit-vehicles')
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="regenerateVehicleQr({{ $vehicle->id }})">
                            <i class="bi bi-arrow-clockwise me-1"></i> Regenerate QR Code
                        </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── TAB: TRANSFERS ──────────────────────────────────────────── --}}
    @can('transfer-vehicles')
    <div class="tab-pane fade" id="tab-transfers">
        <div class="card border-0 border-top-0 shadow-sm rounded-0 rounded-bottom">
            <div class="card-body">

                {{-- Initiate transfer --}}
                @if($vehicle->canBeTransferred())
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3">Initiate Transfer</h6>
                    <form id="transferInitiateForm-{{ $vehicle->id }}" data-url="{{ route('vehicles.transfers.initiate', $vehicle) }}"
                          onsubmit="return submitVehicleTransferInitiate(event, {{ $vehicle->id }})">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold required">Transfer To</label>
                                <select name="to_branch_id" class="form-select form-select-sm" required>
                                    <option value="">Select Branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Transfer Date</label>
                                <input type="date" name="transfer_date"
                                       class="form-control form-control-sm"
                                       value="{{ today()->toDateString() }}"
                                       min="{{ today()->toDateString() }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Notes</label>
                                <input type="text" name="notes"
                                       class="form-control form-control-sm" placeholder="Optional">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-arrow-left-right me-1"></i> Request Transfer
                                </button>
                            </div>
                        </div>
                        <div class="alert alert-danger py-1 px-2 mt-2 mb-0 d-none small" id="transferInitiateError-{{ $vehicle->id }}"></div>
                    </form>
                </div>
                <hr>
                @endif

                {{-- Transfer history --}}
                <h6 class="fw-semibold mb-3">Transfer History</h6>
                @forelse($vehicle->transfers as $transfer)
                <div class="d-flex align-items-start gap-3 p-3 border rounded mb-2">
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">
                            {{ $transfer->fromBranch->name }}
                            <i class="bi bi-arrow-right mx-1"></i>
                            {{ $transfer->toBranch->name }}
                        </div>
                        <small class="text-muted">
                            {{ $transfer->transfer_date->format('d M Y') }} ·
                            By {{ $transfer->transferredBy->name }}
                            @if($transfer->notes) · {{ $transfer->notes }} @endif
                        </small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-{{ $transfer->status_color }}-subtle text-{{ $transfer->status_color }}">
                            {{ $transfer->status_label }}
                        </span>
                        @if($transfer->isPending())
                            <button type="button" class="btn btn-success btn-sm"
                                    onclick="respondToVehicleTransfer({{ $transfer->id }}, 'approve')">Approve</button>
                            <button type="button" class="btn btn-danger btn-sm"
                                    onclick="respondToVehicleTransfer({{ $transfer->id }}, 'reject')">Reject</button>
                        @endif
                        @if($transfer->status === 'approved')
                            <button type="button" class="btn btn-primary btn-sm"
                                    onclick="respondToVehicleTransfer({{ $transfer->id }}, 'complete')">Mark Completed</button>
                        @endif
                    </div>
                </div>
                @empty
                    <p class="text-muted small">No transfers recorded for this vehicle.</p>
                @endforelse

            </div>
        </div>
    </div>
    @endcan

    {{-- ── TAB: HISTORY ────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="tab-history">
        <div class="card border-0 border-top-0 shadow-sm rounded-0 rounded-bottom">
            <div class="card-body">
                <div class="timeline">
                    @forelse($vehicle->statusLogs as $log)
                    <div class="d-flex gap-3 mb-3">
                        <div class="flex-shrink-0 text-center" style="width:32px;">
                            <div class="rounded-circle bg-primary-subtle d-inline-flex align-items-center justify-content-center"
                                 style="width:32px;height:32px;">
                                <i class="bi bi-arrow-right-circle text-primary small"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">
                                @if($log->from_status)
                                    <span class="text-muted">{{ $log->from_status_label }}</span>
                                    <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                @endif
                                <span class="text-dark">{{ $log->to_status_label }}</span>
                            </div>
                            <small class="text-muted">
                                {{ $log->created_at->format('d M Y, H:i') }} ·
                                {{ $log->changedBy->name }}
                                @if($log->reason) · {{ $log->reason }} @endif
                            </small>
                        </div>
                    </div>
                    @empty
                        <p class="text-muted small">No status history recorded.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>
