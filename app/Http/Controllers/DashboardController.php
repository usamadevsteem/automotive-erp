<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\SaleInvoice;
use App\Models\Vehicle;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $tenant = app('tenant');

        // ── Inventory ────────────────────────────────────────────
        $statusCounts = Vehicle::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $kpis = [
            'total_vehicles'    => $statusCounts->sum(),
            'available'         => $statusCounts->get('available', 0),
            'reserved'          => $statusCounts->get('reserved', 0),
            'sold_this_month'   => Vehicle::whereMonth('sold_at', now()->month)
                                          ->whereYear('sold_at', now()->year)
                                          ->whereIn('status', ['sold','delivered'])
                                          ->count(),
            'inventory_value'   => (float) Vehicle::whereIn('status', ['available','reserved','pending_inspection'])
                                                   ->sum('total_cost'),
            'pending_inspection'=> $statusCounts->get('pending_inspection', 0),
            'added_this_week'   => Vehicle::whereBetween('created_at', [
                                    now()->startOfWeek(),
                                    now()->endOfWeek(),
                                ])->count(),
        ];

        $recentVehicles = Vehicle::with(['make','vehicleModel','branch'])
            ->latest()
            ->limit(8)
            ->get();

        // ── CRM ──────────────────────────────────────────────────
        $leadStatusCounts = Lead::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $crm = [
            'total_customers'   => Customer::count(),
            'new_customers'     => Customer::whereMonth('created_at', now()->month)
                                            ->whereYear('created_at', now()->year)
                                            ->count(),
            'total_leads'       => $leadStatusCounts->sum(),
            'new_leads'         => $leadStatusCounts->get('new', 0),
            'qualified_leads'   => $leadStatusCounts->get('qualified', 0),
            'won_leads'         => $leadStatusCounts->get('won', 0),
            'new_leads_this_week' => Lead::whereBetween('created_at', [
                                        now()->startOfWeek(),
                                        now()->endOfWeek(),
                                    ])->count(),
        ];

        $recentLeads = Lead::with('assignedTo')
            ->latest()
            ->limit(5)
            ->get();
        $pipelineLeads = Lead::latest()
            ->get();

        $recentActivity = $recentVehicles
            ->map(function ($vehicle) {
                return [
                    'type' => 'vehicle',
                    'title' => ($vehicle->make->name ?? '') . ' ' . ($vehicle->vehicleModel->name ?? '') . ' added',
                    'subtitle' => $vehicle->stock_number,
                    'created_at' => $vehicle->created_at,
                ];
            })
            ->concat(
                $recentLeads->map(function ($lead) {
                    return [
                        'type' => 'lead',
                        'title' => 'Lead ' . $lead->full_name . ' created',
                        'subtitle' => $lead->vehicle_interest ?? 'General enquiry',
                        'created_at' => $lead->created_at,
                    ];
                })
            )
            ->sortByDesc('created_at')
            ->take(5)
            ->values();

        // ── Sales ────────────────────────────────────────────────
        $sales = [
            'pending_quotations' => Quotation::where('status', 'sent')->count(),
            'active_bookings'   => Booking::where('status', 'active')->count(),
            'invoices_this_month'=> SaleInvoice::whereMonth('invoice_date', now()->month)
                                                ->whereYear('invoice_date', now()->year)
                                                ->count(),
            'revenue_this_month'=> (float) SaleInvoice::whereMonth('invoice_date', now()->month)
                                                       ->whereYear('invoice_date', now()->year)
                                                       ->whereIn('status', ['issued','paid','partial'])
                                                       ->sum('net_amount'),
            'outstanding_balance'=> (float) SaleInvoice::whereIn('status', ['issued','partial'])
                                                        ->sum('balance_due'),
        ];

        $previousMonthRevenue = (float) SaleInvoice::whereMonth(
        'invoice_date',
                now()->subMonth()->month
            )
            ->whereYear(
                'invoice_date',
                now()->subMonth()->year
            )
            ->whereIn('status', ['issued', 'paid', 'partial'])
            ->sum('net_amount');

        $currentMonthRevenue = $sales['revenue_this_month'];

        $sales['revenue_growth'] = $previousMonthRevenue > 0
            ? (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100
            : 0;
        // ── Accounting ───────────────────────────────────────────
        $accounting = [
            'expenses_this_month'=> (float) Expense::whereMonth('expense_date', now()->month)
                                                    ->whereYear('expense_date', now()->year)
                                                    ->where('status', 'approved')
                                                    ->sum('amount'),
            'payments_received' => (float) Payment::where('type', 'received')
                                                    ->whereMonth('payment_date', now()->month)
                                                    ->whereYear('payment_date', now()->year)
                                                    ->sum('amount'),
            'payments_paid_this_month'=> (float) Payment::where('type', 'paid')
                                                    ->whereMonth('payment_date', now()->month)
                                                    ->whereYear('payment_date', now()->year)
                                                    ->sum('amount'),
        ];
        $accounting['net_this_month'] = $sales['revenue_this_month'] - $accounting['expenses_this_month'];



     // Sales chart — last 30 days
        $chartLabels = [];
        $chartValues = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);

            $chartLabels[] = $date->format('d M');

            $chartValues[] = (float) SaleInvoice::whereDate('invoice_date', $date->toDateString())
                ->whereIn('status', ['issued', 'paid', 'partial'])
                ->sum('net_amount');
        }


       $data = [
        'inventory' => [
        'total' => $kpis['total_vehicles'] ?? 0,
        'available' => $kpis['available'] ?? 0,
        'reserved' => $kpis['reserved'] ?? 0,
        'added_this_week' => $kpis['added_this_week'] ?? 0,
        'delivered' => $statusCounts->get('delivered', 0),
        'sold' => $statusCounts->get('sold', 0),
        'pending_inspection' => $kpis['pending_inspection'] ?? 0,
        'inventory_value' => $kpis['inventory_value'] ?? 0,
    ],

    'crm' => $crm,
    'sales' => $sales,
    'accounting' => $accounting,
    'recent_vehicles' => $recentVehicles,
    'recent_leads' => $recentLeads,
    'recent_activity' => $recentActivity,
    'pipeline_leads' => $pipelineLeads,

    'chart_labels' => $chartLabels,
    'chart_values' => $chartValues,
];

return view('dashboard', compact('data', 'tenant'));
    }
}
