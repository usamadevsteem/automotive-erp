@extends('layouts.app')
@section('title','Dashboard')
@section('breadcrumb','Dashboard')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Dashboard</h4>
        <small class="text-muted">{{ now()->format('l, d F Y') }}</small>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Vehicles</span>
                    <span class="bg-primary-subtle p-2 rounded"><i class="bi bi-car-front text-primary"></i></span>
                </div>
                <div class="fw-bold fs-4">{{ number_format($kpis['total_vehicles']) }}</div>
                <small class="text-muted">{{ $kpis['available'] }} available</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Sold This Month</span>
                    <span class="bg-success-subtle p-2 rounded"><i class="bi bi-bag-check text-success"></i></span>
                </div>
                <div class="fw-bold fs-4">{{ number_format($kpis['sold_this_month']) }}</div>
                <small class="text-muted">{{ now()->format('F Y') }}</small>
            </div>
        </div>
    </div>
    @can('view-vehicle-cost')
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Inventory Value</span>
                    <span class="bg-warning-subtle p-2 rounded"><i class="bi bi-currency-exchange text-warning"></i></span>
                </div>
                <div class="fw-bold fs-5">PKR {{ number_format($kpis['inventory_value'] / 1000000, 1) }}M</div>
                <small class="text-muted">at cost price</small>
            </div>
        </div>
    </div>
    @endcan
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Pending Inspection</span>
                    <span class="bg-info-subtle p-2 rounded"><i class="bi bi-search text-info"></i></span>
                </div>
                <div class="fw-bold fs-4">{{ number_format($kpis['pending_inspection']) }}</div>
                <small class="text-muted">awaiting approval</small>
            </div>
        </div>
    </div>
</div>

{{-- Recent Vehicles --}}
@can('view-vehicles')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-semibold">Recently Added Vehicles</h6>
        <a href="{{ route('vehicles.index') }}" class="btn btn-light btn-sm">View All</a>
    </div>
    <div class="card-body p-0">
        @if($recentVehicles->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-car-front fs-1 opacity-25 d-block mb-2"></i>
                <p class="mb-0">No vehicles in inventory yet.</p>
                <a href="{{ route('vehicles.create') }}" class="btn btn-primary btn-sm mt-3">
                    <i class="bi bi-plus me-1"></i> Add First Vehicle
                </a>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Stock #</th>
                        <th>Vehicle</th>
                        <th>Branch</th>
                        <th>Sale Price</th>
                        <th>Status</th>
                        <th class="pe-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentVehicles as $v)
                    <tr>
                        <td class="ps-4">
                            <a href="{{ route('vehicles.show', $v) }}"
                               class="fw-semibold text-decoration-none text-dark">
                                {{ $v->stock_number }}
                            </a>
                        </td>
                        <td>
                            <div class="fw-semibold small">{{ $v->make->name }} {{ $v->vehicleModel->name }}</div>
                            <small class="text-muted">{{ $v->year }} · {{ $v->color }}</small>
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $v->branch->name }}</span></td>
                        <td class="fw-semibold">PKR {{ number_format($v->sale_price) }}</td>
                        <td>
                            <span class="badge bg-{{ $v->status_color }}-subtle text-{{ $v->status_color }}">
                                {{ $v->status_label }}
                            </span>
                        </td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('vehicles.show', $v) }}" class="btn btn-light btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endcan

{{-- CRM Section --}}
@can('view-customers')
<h6 class="fw-bold text-uppercase text-muted small mb-2 mt-1">CRM</h6>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Customers</span>
                    <span class="bg-primary-subtle p-2 rounded"><i class="bi bi-people text-primary"></i></span>
                </div>
                <div class="fw-bold fs-4">{{ number_format($crm['total_customers']) }}</div>
                <small class="text-muted">{{ $crm['new_customers'] }} new this month</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Leads</span>
                    <span class="bg-info-subtle p-2 rounded"><i class="bi bi-funnel text-info"></i></span>
                </div>
                <div class="fw-bold fs-4">{{ number_format($crm['total_leads']) }}</div>
                <small class="text-muted">{{ $crm['new_leads'] }} new</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Qualified Leads</span>
                    <span class="bg-warning-subtle p-2 rounded"><i class="bi bi-star text-warning"></i></span>
                </div>
                <div class="fw-bold fs-4">{{ number_format($crm['qualified_leads']) }}</div>
                <small class="text-muted">in pipeline</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Won Leads</span>
                    <span class="bg-success-subtle p-2 rounded"><i class="bi bi-trophy text-success"></i></span>
                </div>
                <div class="fw-bold fs-4">{{ number_format($crm['won_leads']) }}</div>
                <small class="text-muted">converted</small>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-semibold">Recent Leads</h6>
        <a href="{{ route('leads.index') }}" class="btn btn-light btn-sm">View All</a>
    </div>
    <div class="card-body p-0">
        @if($recentLeads->isEmpty())
            <div class="text-center py-4 text-muted">
                <p class="mb-0">No leads yet.</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Name</th>
                        <th>Interested In</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th class="pe-4">Follow Up</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentLeads as $lead)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $lead->full_name }}</td>
                        <td class="small text-muted">{{ $lead->vehicle_interest ?? '—' }}</td>
                        <td><span class="badge bg-light text-dark text-capitalize">{{ $lead->status }}</span></td>
                        <td class="small">{{ $lead->assignedTo->name ?? 'Unassigned' }}</td>
                        <td class="pe-4 small text-muted">{{ $lead->next_follow_up?->format('d M Y') ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endcan

{{-- Sales Section --}}
@can('view-quotations')
<h6 class="fw-bold text-uppercase text-muted small mb-2 mt-1">Sales</h6>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Pending Quotations</span>
                    <span class="bg-info-subtle p-2 rounded"><i class="bi bi-file-earmark-text text-info"></i></span>
                </div>
                <div class="fw-bold fs-4">{{ number_format($sales['quotations_pending']) }}</div>
                <small class="text-muted">awaiting response</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Active Bookings</span>
                    <span class="bg-primary-subtle p-2 rounded"><i class="bi bi-calendar-check text-primary"></i></span>
                </div>
                <div class="fw-bold fs-4">{{ number_format($sales['active_bookings']) }}</div>
                <small class="text-muted">in progress</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Revenue This Month</span>
                    <span class="bg-success-subtle p-2 rounded"><i class="bi bi-graph-up-arrow text-success"></i></span>
                </div>
                <div class="fw-bold fs-5">PKR {{ number_format($sales['revenue_this_month'] / 1000000, 1) }}M</div>
                <small class="text-muted">{{ $sales['invoices_this_month'] }} invoices</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Outstanding Balance</span>
                    <span class="bg-danger-subtle p-2 rounded"><i class="bi bi-exclamation-circle text-danger"></i></span>
                </div>
                <div class="fw-bold fs-5">PKR {{ number_format($sales['outstanding_balance'] / 1000000, 1) }}M</div>
                <small class="text-muted">unpaid invoices</small>
            </div>
        </div>
    </div>
</div>
@endcan

{{-- Accounting Section --}}
@can('view-vehicle-cost')
<h6 class="fw-bold text-uppercase text-muted small mb-2 mt-1">Accounting</h6>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Expenses This Month</span>
                    <span class="bg-danger-subtle p-2 rounded"><i class="bi bi-receipt text-danger"></i></span>
                </div>
                <div class="fw-bold fs-5">PKR {{ number_format($accounting['expenses_this_month'] / 1000, 0) }}K</div>
                <small class="text-muted">approved expenses</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Payments Received</span>
                    <span class="bg-success-subtle p-2 rounded"><i class="bi bi-arrow-down-circle text-success"></i></span>
                </div>
                <div class="fw-bold fs-5">PKR {{ number_format($accounting['payments_received_this_month'] / 1000, 0) }}K</div>
                <small class="text-muted">this month</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Payments Paid</span>
                    <span class="bg-warning-subtle p-2 rounded"><i class="bi bi-arrow-up-circle text-warning"></i></span>
                </div>
                <div class="fw-bold fs-5">PKR {{ number_format($accounting['payments_paid_this_month'] / 1000, 0) }}K</div>
                <small class="text-muted">this month</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Net This Month</span>
                    <span class="bg-{{ $accounting['net_this_month'] >= 0 ? 'success' : 'danger' }}-subtle p-2 rounded">
                        <i class="bi bi-cash-stack text-{{ $accounting['net_this_month'] >= 0 ? 'success' : 'danger' }}"></i>
                    </span>
                </div>
                <div class="fw-bold fs-5">PKR {{ number_format($accounting['net_this_month'] / 1000, 0) }}K</div>
                <small class="text-muted">revenue − expenses</small>
            </div>
        </div>
    </div>
</div>
@endcan
@endsection
