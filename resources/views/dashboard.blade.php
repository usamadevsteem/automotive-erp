@extends('layouts.app')
@section('title', 'Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<style>
.greeting { font-size: 1.4rem; font-weight: 800; color: #0f172a; }
.greeting-sub { font-size: 13px; color: #64748b; margin-top: 2px; }
.qa-bar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.qa-btn { display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;border:1.5px solid #e2e8f0;background:#fff;color:#1e293b;transition:.15s;text-decoration:none; }
.qa-btn:hover { border-color:#2563eb;color:#2563eb;background:#eff6ff; }
.qa-btn.primary { background:#2563eb;color:#fff;border-color:#2563eb; }
.qa-btn.primary:hover { background:#1d4ed8; }

/* KPI Cards */
.kpi-grid { display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:20px; }
.kpi-card { background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px 20px;position:relative;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06); }
.kpi-label { font-size:12px;color:#64748b;font-weight:500;margin-bottom:6px; }
.kpi-value { font-size:1.55rem;font-weight:800;color:#0f172a;line-height:1.1; }
.kpi-sub { font-size:11.5px;color:#64748b;margin-top:5px; }
.kpi-trend { font-size:11.5px;font-weight:600; }
.kpi-trend.up { color:#16a34a; }
.kpi-trend.down { color:#dc2626; }
.kpi-icon { position:absolute;top:16px;right:16px;width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem; }
.kpi-sparkline { margin-top:8px;height:36px; }

/* Mid row */
.mid-grid { display:grid;grid-template-columns:1fr 320px 320px;gap:16px;margin-bottom:20px; }
.card-clean { background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.06); }
.card-head { padding:16px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #f1f5f9; }
.card-head-title { font-size:14px;font-weight:700;color:#0f172a; }
.card-head-action { font-size:12px;color:#2563eb;font-weight:600;text-decoration:none; }
.card-body { padding:16px 20px; }

/* Sales chart */
.sales-total { font-size:1.5rem;font-weight:800;color:#0f172a; }
.sales-trend { font-size:12px;font-weight:600;margin-left:8px; }
.sales-trend.up { color:#16a34a; }
.sales-trend.down { color:#dc2626; }

/* Donut */
.donut-wrap { display:flex;align-items:center;gap:20px; }
.donut-canvas-wrap { position:relative;width:160px;height:160px;flex-shrink:0; }
.donut-center { position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center; }
.donut-center-num { font-size:1.6rem;font-weight:800;color:#0f172a; }
.donut-center-label { font-size:11px;color:#64748b; }
.donut-legend { display:flex;flex-direction:column;gap:8px; }
.donut-legend-item { display:flex;align-items:center;gap:8px;font-size:12px; }
.donut-dot { width:10px;height:10px;border-radius:50%; }
.donut-legend-val { margin-left:auto;font-weight:600;color:#0f172a; }

/* Activity */
.activity-item { display:flex;gap:12px;padding:11px 0;border-bottom:1px solid #f8fafc; }
.activity-item:last-child { border-bottom:none; }
.activity-icon { width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0; }
.activity-title { font-size:13px;font-weight:600;color:#0f172a; }
.activity-sub { font-size:11.5px;color:#64748b; }
.activity-time { font-size:11px;color:#94a3b8;white-space:nowrap;margin-left:auto; }

/* Bottom row */
.vehicles-pipeline-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px; }

/* Vehicle table */
.vt { width:100%;border-collapse:collapse; }
.vt th { font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#64748b;font-weight:600;padding:8px 12px;border-bottom:1px solid #f1f5f9;text-align:left; }
.vt td { font-size:13px;padding:10px 12px;border-bottom:1px solid #f8fafc;vertical-align:middle; }
.vt tr:last-child td { border-bottom:none; }
.vt tr:hover td { background:#f8fafc; }
.vt-make { font-weight:700;color:#0f172a; }
.vt-sub { font-size:11px;color:#94a3b8; }

/* Pipeline */
.pipeline-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:10px; }
.pipeline-col { background:#f8fafc;border-radius:8px;padding:10px; }
.pipeline-col-label { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px; }
.pipeline-col-count { font-size:11px;color:#64748b;margin-bottom:8px; }
.pipeline-card { background:#fff;border:1px solid #e2e8f0;border-radius:7px;padding:9px 10px;margin-bottom:7px; }
.pipeline-card:last-child { margin-bottom:0; }
.pipeline-card-name { font-size:12.5px;font-weight:600;color:#0f172a; }
.pipeline-card-vehicle { font-size:11.5px;color:#64748b; }
.pipeline-card-date { font-size:11px;color:#94a3b8;margin-top:3px; }
.pipeline-more { font-size:11.5px;color:#2563eb;font-weight:600;margin-top:6px;cursor:pointer; }

/* Bottom stats */
.bottom-stats { display:grid;grid-template-columns:repeat(5,1fr);gap:16px; }
.stat-card { background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,.06); }
.stat-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;float:right; }
.stat-label { font-size:12px;color:#64748b;font-weight:500;margin-bottom:4px; }
.stat-value { font-size:1.25rem;font-weight:800;color:#0f172a;margin-bottom:2px; }
.stat-sub { font-size:11.5px;color:#94a3b8; }

@media(max-width:1200px){
    .kpi-grid { grid-template-columns:repeat(3,1fr); }
    .mid-grid { grid-template-columns:1fr 1fr; }
    .bottom-stats { grid-template-columns:repeat(3,1fr); }
}
@media(max-width:900px){
    .kpi-grid { grid-template-columns:repeat(2,1fr); }
    .mid-grid { grid-template-columns:1fr; }
    .vehicles-pipeline-grid { grid-template-columns:1fr; }
    .bottom-stats { grid-template-columns:repeat(2,1fr); }
    .pipeline-grid { grid-template-columns:repeat(2,1fr); }
}
</style>
@endpush

@section('content')

@php
    $hour = now()->setTimezone('Asia/Karachi')->hour;
    $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
    $inv   = $data['inventory']   ?? [];
    $crm   = $data['crm']         ?? [];
    $sales = $data['sales']       ?? [];
    $acct  = $data['accounting']  ?? [];
    $recentVehicles = $data['recent_vehicles'] ?? collect();
    $leads          = $data['recent_leads']    ?? collect();
@endphp

{{-- ── Header row ── --}}
<div class="d-flex align-items-start justify-content-between mb-4" style="flex-wrap:wrap;gap:12px;">
    <div>
        <div class="greeting">{{ $greeting }}, {{ auth()->user()->name ?? 'Ramiz' }}! 👋</div>
        <div class="greeting-sub">Here's what's happening with your business today.</div>
    </div>
    <div class="qa-bar">
        @can('create-vehicles')
        <button class="qa-btn primary" onclick="openVehicleCreateModal()">
            <i class="bi bi-plus"></i> Add Vehicle
        </button>
        @endcan
        @can('create-leads')
        <button class="qa-btn" onclick="openLeadFormModal('{{ route('leads.create') }}', null)">
            <i class="bi bi-plus"></i> Add Lead
        </button>
        @endcan
        @can('create-bookings')
        <button class="qa-btn" onclick="openBookingFormModal('{{ route('bookings.create') }}')">
            <i class="bi bi-plus"></i> Booking
        </button>
        @endcan
        @can('create-payments')
        <button class="qa-btn" onclick="openPaymentFormModal('{{ route('payments.create') }}', null)">
            <i class="bi bi-plus"></i> Payment
        </button>
        @endcan
        <button class="qa-btn" style="padding:7px 10px;">
            <i class="bi bi-three-dots"></i>
        </button>
    </div>
</div>

{{-- ── KPI Cards ── --}}
<div class="kpi-grid">
    {{-- Total Revenue --}}
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#eff6ff;">💰</div>
        <div class="kpi-label">Total Revenue</div>
        <div class="kpi-value">PKR {{ number_format(($sales['revenue_this_month'] ?? 0) / 1000000, 1) }}M</div>
        <div class="kpi-sub">
           @php
            $revenueGrowth = $sales['revenue_growth'] ?? 0;
        @endphp

        <span class="kpi-trend {{ $revenueGrowth >= 0 ? 'up' : 'down' }}">
            {{ $revenueGrowth >= 0 ? '↑' : '↓' }}
            {{ number_format(abs($revenueGrowth), 1) }}%
        </span>
            <span> vs last month</span>
        </div>
        <canvas id="sparkRevenue" class="kpi-sparkline"></canvas>
    </div>
    {{-- Total Vehicles --}}
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#f0fdf4;">🚗</div>
        <div class="kpi-label">Total Vehicles</div>
        <div class="kpi-value">{{ $inv['total'] ?? 0 }}</div>
        <div class="kpi-sub">
            <span class="kpi-trend up">↑ {{ $inv['added_this_week'] ?? 0 }}</span>
            <span> this week</span>
        </div>
    </div>
    {{-- Total Leads --}}
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fefce8;">👤</div>
        <div class="kpi-label">Total Leads</div>
        <div class="kpi-value">{{ $crm['total_leads'] ?? 0 }}</div>
        <div class="kpi-sub">
            <span class="kpi-trend up">↑ {{ $crm['new_leads_this_week'] ?? 0 }}</span>
            <span> this week</span>
        </div>
    </div>
    {{-- Inventory Value --}}
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fff7ed;">📦</div>
        <div class="kpi-label">Inventory Value</div>
        <div class="kpi-value">PKR {{ number_format(($inv['inventory_value'] ?? 0) / 1000000, 1) }}M</div>
        <div class="kpi-sub" style="color:#64748b;">at cost price</div>
    </div>
    {{-- Pending Inspection --}}
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fef2f2;">📋</div>
        <div class="kpi-label">Pending Inspection</div>
        <div class="kpi-value">{{ $inv['pending_inspection'] ?? 0 }}</div>
        <div class="kpi-sub" style="color:{{ ($inv['pending_inspection'] ?? 0) > 0 ? '#dc2626' : '#94a3b8' }}">
            {{ ($inv['pending_inspection'] ?? 0) > 0 ? 'Needs attention' : 'No pending inspection' }}
        </div>
    </div>
</div>

{{-- ── Mid Row: Sales Chart | Donut | Activity ── --}}
<div class="mid-grid">

    {{-- Sales Overview --}}
    <div class="card-clean">
        <div class="card-head">
            <div>
                <div class="card-head-title">Sales Overview</div>
                <div style="margin-top:4px;">
                    <span class="sales-total">PKR {{ number_format(($sales['revenue_this_month'] ?? 0) / 1000000, 1) }}M</span>
                   @php
                        $salesGrowth = $sales['revenue_growth'] ?? 0;
                    @endphp

                    <span class="sales-trend {{ $salesGrowth >= 0 ? 'up' : 'down' }}">
                        {{ $salesGrowth >= 0 ? '↑' : '↓' }}
                        {{ number_format(abs($salesGrowth), 1) }}% vs last month
                    </span>
                </div>
            </div>
            <select style="font-size:12px;border:1px solid #e2e8f0;border-radius:7px;padding:4px 10px;color:#64748b;background:#fff;">
                <option>Last 30 Days</option>
                <option>This Month</option>
                <option>This Year</option>
            </select>
        </div>
        <div class="card-body" style="padding-top:12px;">
            <canvas id="salesChart" height="160"></canvas>
        </div>
    </div>

    {{-- Vehicles by Status (Donut) --}}
    <div class="card-clean">
        <div class="card-head">
            <div class="card-head-title">Vehicles by Status</div>
        </div>
        <div class="card-body">
            <div class="donut-wrap">
                <div class="donut-canvas-wrap">
                    <canvas id="statusDonut"></canvas>
                    <div class="donut-center">
                        <div class="donut-center-num">{{ $inv['total'] ?? 0 }}</div>
                        <div class="donut-center-label">Total</div>
                    </div>
                </div>
                <div class="donut-legend">
                    <div class="donut-legend-item">
                        <div class="donut-dot" style="background:#22c55e;"></div>
                        <span>Available</span>
                        <span class="donut-legend-val">{{ $inv['available'] ?? 0 }} ({{ $inv['total'] ? round(($inv['available'] ?? 0) / $inv['total'] * 100) : 0 }}%)</span>
                    </div>
                    <div class="donut-legend-item">
                        <div class="donut-dot" style="background:#f59e0b;"></div>
                        <span>Reserved</span>
                        <span class="donut-legend-val">{{ $inv['reserved'] ?? 0 }} ({{ $inv['total'] ? round(($inv['reserved'] ?? 0) / $inv['total'] * 100) : 0 }}%)</span>
                    </div>
                    <div class="donut-legend-item">
                        <div class="donut-dot" style="background:#3b82f6;"></div>
                        <span>Delivered</span>
                        <span class="donut-legend-val">{{ $inv['delivered'] ?? 0 }} ({{ $inv['total'] ? round(($inv['delivered'] ?? 0) / $inv['total'] * 100) : 0 }}%)</span>
                    </div>
                    <div class="donut-legend-item">
                        <div class="donut-dot" style="background:#a855f7;"></div>
                        <span>Sold</span>
                        <span class="donut-legend-val">{{ $inv['sold'] ?? 0 }} ({{ $inv['total'] ? round(($inv['sold'] ?? 0) / $inv['total'] * 100) : 0 }}%)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
<div class="card-clean">
    <div class="card-head">
        <div class="card-head-title">Recent Activity</div>
        <a href="{{ route('vehicles.index') }}" class="card-head-action">View All</a>
    </div>
    <div class="card-body" style="padding:0 20px;">
        @forelse($data['recent_activity'] ?? [] as $activity)
            <div class="activity-item">
                @if($activity['type'] === 'vehicle')
                    <div class="activity-icon" style="background:#eff6ff;">
                        <i class="bi bi-car-front-fill" style="color:#2563eb;"></i>
                    </div>
                @else
                    <div class="activity-icon" style="background:#f0fdf4;">
                        <i class="bi bi-person-fill" style="color:#16a34a;"></i>
                    </div>
                @endif
                <div style="min-width:0;flex:1;">
                    <div class="activity-title">
                        {{ $activity['title'] }}
                    </div>
                    <div class="activity-sub">
                        {{ $activity['subtitle'] }}
                    </div>
                </div>
                <div class="activity-time">
                    {{ $activity['created_at']->diffForHumans(null, true) }} ago
                </div>
            </div>

        @empty
            <p class="text-muted" style="font-size:13px;padding:16px 0;">
                No recent activity.
            </p>
        @endforelse
    </div>
</div>

</div>

{{-- ── Recent Vehicles + CRM Pipeline ── --}}
<div class="vehicles-pipeline-grid">

    {{-- Recent Vehicles --}}
    <div class="card-clean">
        <div class="card-head">
            <div class="card-head-title">Recent Vehicles</div>
            <a href="{{ route('vehicles.index') }}" class="card-head-action">View All</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="vt">
                <thead>
                    <tr>
                        <th>Stock #</th>
                        <th>Vehicle</th>
                        <th>Branch</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentVehicles->take(6) as $v)
                    <tr>
                        <td><span style="font-weight:600;color:#0f172a;font-size:12px;">{{ $v->stock_number }}</span></td>
                        <td>
                            <div class="vt-make">{{ $v->make->name ?? '' }} {{ $v->vehicleModel->name ?? '' }}</div>
                            <div class="vt-sub">{{ $v->year }} · {{ ucfirst($v->color ?? '') }}</div>
                        </td>
                        <td style="font-size:12px;color:#64748b;">{{ $v->branch->name ?? '—' }}</td>
                        <td style="font-weight:600;color:#0f172a;">PKR {{ number_format($v->sale_price) }}</td>
                        <td>
                            @php
                                $sc = match($v->status) {
                                    'available' => 'background:#dcfce7;color:#166534;',
                                    'reserved'  => 'background:#fef3c7;color:#92400e;',
                                    'sold'      => 'background:#fee2e2;color:#991b1b;',
                                    'delivered' => 'background:#dbeafe;color:#1e40af;',
                                    default     => 'background:#f1f5f9;color:#475569;',
                                };
                            @endphp
                            <span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;{{ $sc }}">
                                {{ ucfirst($v->status) }}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm" style="background:none;border:none;color:#94a3b8;padding:2px 6px;"
                                    onclick="openViewModal('{{ route('vehicles.show', $v) }}','{{ $v->stock_number }}')">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;padding:32px;color:#94a3b8;font-size:13px;">No vehicles yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- CRM Pipeline --}}
    <div class="card-clean">
        <div class="card-head">
            <div class="card-head-title">CRM Pipeline</div>
            <a href="{{ route('leads.index') }}" class="card-head-action">View All</a>
        </div>
        <div class="card-body">
            @php
                $newLeads    = $data['pipeline_leads']->where('status', 'new')->take(3);
                $qualified   = $data['pipeline_leads']->where('status', 'qualified')->take(2);
                $negotiation = $data['pipeline_leads']->where('status', 'negotiation')->take(2);
                $won         = $data['pipeline_leads']->where('status', 'won')->take(2);

                $newCount    = $data['pipeline_leads']->where('status', 'new')->count();
                $qualCount   = $data['pipeline_leads']->where('status', 'qualified')->count();
                $negCount    = $data['pipeline_leads']->where('status', 'negotiation')->count();
                $wonCount    = $data['pipeline_leads']->where('status', 'won')->count();
            @endphp
            <div class="pipeline-grid">
                {{-- New --}}
                <div class="pipeline-col">
                    <div class="pipeline-col-label" style="color:#2563eb;">New Leads</div>
                    <div class="pipeline-col-count">{{ $newCount }} Leads</div>
                    @foreach($newLeads as $l)
                    <div class="pipeline-card">
                        <div class="pipeline-card-name">{{ $l->full_name }}</div>
                        <div class="pipeline-card-vehicle">{{ $l->vehicle_interest ?? 'General' }}</div>
                        <div class="pipeline-card-date">{{ $l->created_at->format('d M Y') }}</div>
                    </div>
                    @endforeach
                    @if($newCount > 3)<div class="pipeline-more">+ {{ $newCount - 3 }} more</div>@endif
                </div>
                {{-- Qualified --}}
                <div class="pipeline-col">
                    <div class="pipeline-col-label" style="color:#16a34a;">Qualified</div>
                    <div class="pipeline-col-count">{{ $qualCount }} Leads</div>
                    @foreach($qualified as $l)
                    <div class="pipeline-card">
                        <div class="pipeline-card-name">{{ $l->full_name }}</div>
                        <div class="pipeline-card-vehicle">{{ $l->vehicle_interest ?? 'General' }}</div>
                        <div class="pipeline-card-date">{{ $l->created_at->format('d M Y') }}</div>
                    </div>
                    @endforeach
                    @if($qualCount > 2)<div class="pipeline-more">+ {{ $qualCount - 2 }} more</div>@endif
                </div>
                {{-- Negotiation --}}
                <div class="pipeline-col">
                    <div class="pipeline-col-label" style="color:#d97706;">Negotiation</div>
                    <div class="pipeline-col-count">{{ $negCount }} Leads</div>
                    @foreach($negotiation as $l)
                    <div class="pipeline-card">
                        <div class="pipeline-card-name">{{ $l->full_name }}</div>
                        <div class="pipeline-card-vehicle">{{ $l->vehicle_interest ?? 'General' }}</div>
                        <div class="pipeline-card-date">{{ $l->created_at->format('d M Y') }}</div>
                    </div>
                    @endforeach
                    @if($negCount > 2)<div class="pipeline-more">+ {{ $negCount - 2 }} more</div>@endif
                </div>
                {{-- Won --}}
                <div class="pipeline-col">
                    <div class="pipeline-col-label" style="color:#16a34a;">Won</div>
                    <div class="pipeline-col-count">{{ $wonCount }} Leads</div>
                    @foreach($won as $l)
                    <div class="pipeline-card">
                        <div class="pipeline-card-name">{{ $l->full_name }}</div>
                        <div class="pipeline-card-vehicle">{{ $l->vehicle_interest ?? 'General' }}</div>
                        <div class="pipeline-card-date">{{ $l->created_at->format('d M Y') }}</div>
                    </div>
                    @endforeach
                    @if($wonCount > 2)<div class="pipeline-more">+ {{ $wonCount - 2 }} more</div>@endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Bottom Stats ── --}}
<div class="bottom-stats">
    <div class="stat-card">
        <div class="stat-icon" style="background:#eff6ff;color:#2563eb;"><i class="bi bi-calendar-check-fill"></i></div>
        <div class="stat-label">Active Bookings</div>
        <div class="stat-value">{{ $sales['active_bookings'] ?? 0 }}</div>
        <div class="stat-sub">In progress</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-file-text-fill"></i></div>
        <div class="stat-label">Pending Quotations</div>
        <div class="stat-value">{{ $sales['pending_quotations'] ?? 0 }}</div>
        <div class="stat-sub">Awaiting response</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef2f2;color:#dc2626;"><i class="bi bi-cash-stack"></i></div>
        <div class="stat-label">Outstanding Balance</div>
        <div class="stat-value">PKR {{ number_format(($sales['outstanding_balance'] ?? 0) / 1000000, 1) }}M</div>
        <div class="stat-sub">Unpaid invoices</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fff7ed;color:#d97706;"><i class="bi bi-credit-card-fill"></i></div>
        <div class="stat-label">Expenses This Month</div>
        <div class="stat-value">PKR {{ number_format(($acct['expenses_this_month'] ?? 0) / 1000, 0) }}K</div>
        <div class="stat-sub">Approved expenses</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;color:#16a34a;"><i class="bi bi-cash-coin"></i></div>
        <div class="stat-label">Payments Received</div>
        <div class="stat-value">PKR {{ number_format(($acct['payments_received_this_month'] ?? 0) / 1000, 0) }}K</div>
        <div class="stat-sub">This month</div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Sparkline (revenue mini chart) ──────────────────────
    const sparkCtx = document.getElementById('sparkRevenue')?.getContext('2d');
    if (sparkCtx) {
        new Chart(sparkCtx, {
            type: 'line',
            data: {
                labels: ['','','','','','',''],
                datasets: [{
                    data: [8, 12, 9, 15, 13, 18, {{ ($sales['revenue_this_month'] ?? 21) / 1000000 }}],
                    borderColor: '#a78bfa',
                    borderWidth: 2,
                    fill: true,
                    backgroundColor: 'rgba(167,139,250,.15)',
                    tension: 0.4,
                    pointRadius: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: { x: { display: false }, y: { display: false } },
            }
        });
    }

    // ── Sales Line Chart ─────────────────────────────────────
    const salesCtx = document.getElementById('salesChart')?.getContext('2d');
    if (salesCtx) {
        const labels = {!! json_encode($data['chart_labels'] ?? []) !!};
        const values = {!! json_encode($data['chart_values'] ?? []) !!};
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Revenue',
                    data: values,
                    borderColor: '#2563eb',
                    borderWidth: 2.5,
                    fill: true,
                    backgroundColor: (ctx) => {
                        const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 200);
                        g.addColorStop(0, 'rgba(37,99,235,.15)');
                        g.addColorStop(1, 'rgba(37,99,235,0)');
                        return g;
                    },
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => 'PKR ' + Number(ctx.raw).toLocaleString('en-PK'),
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { size: 11 }, color: '#94a3b8',
                            callback: v => 'PKR ' + (v >= 1000000 ? (v/1000000).toFixed(0)+'M' : (v/1000).toFixed(0)+'K'),
                        }
                    }
                }
            }
        });
    }

    // ── Status Donut Chart ───────────────────────────────────
    const donutCtx = document.getElementById('statusDonut')?.getContext('2d');
    if (donutCtx) {
        const available = {{ $inv['available'] ?? 0 }};
        const reserved  = {{ $inv['reserved']  ?? 0 }};
        const delivered = {{ $inv['delivered'] ?? 0 }};
        const sold      = {{ $inv['sold']      ?? 0 }};
        const pending   = {{ $inv['pending_inspection'] ?? 0 }};
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Available','Reserved','Delivered','Sold','Pending'],
                datasets: [{
                    data: [available, reserved, delivered, sold, pending],
                    backgroundColor: ['#22c55e','#f59e0b','#3b82f6','#a855f7','#94a3b8'],
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.raw}` } }
                }
            }
        });
    }
});
</script>
@endpush
