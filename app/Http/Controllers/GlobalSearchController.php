<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\SaleInvoice;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

       // Vehicles 

        Vehicle::with(['make', 'vehicleModel'])
            ->where(fn($query) => $query
                ->where('stock_number', 'like', "%{$q}%")
                ->orWhere('registration_number', 'like', "%{$q}%")
                ->orWhere('chassis_number', 'like', "%{$q}%")
                ->orWhere('color', 'like', "%{$q}%")
                ->orWhereHas('make',  fn($m) => $m->where('name', 'like', 
"%{$q}%"))
                ->orWhereHas('vehicleModel', fn($m) => $m->where('name', 
'like', "%{$q}%"))
            )
            ->limit(5)
            ->get()
            ->each(function ($v) use (&$results) {
                $results[] = [
                    'type'     => 'vehicle',
                    'icon'     => 'bi-car-front-fill',
                    'color'    => '#2563eb',
                    'bg'       => '#eff6ff',
                    'title'    => $v->make->name . ' ' . 
$v->vehicleModel->name . ' ' . $v->year,
                    'sub'      => $v->stock_number . ' · ' . 
ucfirst($v->status) . ' · PKR ' . number_format($v->sale_price),
                    'url'      => route('vehicles.show', $v),
                ];
            });

        //Customers 

        Customer::where(fn($query) => $query
                ->where('full_name', 'like', "%{$q}%")
                ->orWhere('mobile',   'like', "%{$q}%")
                ->orWhere('cnic',     'like', "%{$q}%")
                ->orWhere('email',    'like', "%{$q}%")
            )
            ->limit(4)
            ->get()
            ->each(function ($c) use (&$results) {
                $results[] = [
                    'type'  => 'customer',
                    'icon'  => 'bi-person-fill',
                    'color' => '#16a34a',
                    'bg'    => '#f0fdf4',
                    'title' => $c->full_name,
                    'sub'   => $c->mobile . ($c->city ? ' · ' . $c->city : 
''),
                    'url'   => route('customers.show', $c),
                ];
            });

        // Leads 

        Lead::where(fn($query) => $query
                ->where('full_name', 'like', "%{$q}%")
                ->orWhere('phone',   'like', "%{$q}%")
                ->orWhere('vehicle_interest', 'like', "%{$q}%")
            )
            ->limit(4)
            ->get()
            ->each(function ($l) use (&$results) {
                $results[] = [
                    'type'  => 'lead',
                    'icon'  => 'bi-funnel-fill',
                    'color' => '#d97706',
                    'bg'    => '#fffbeb',
                    'title' => $l->full_name,
                    'sub'   => $l->phone . ($l->vehicle_interest ? ' · ' . 
$l->vehicle_interest : '') . ' · ' . ucfirst($l->status),
                    'url'   => route('leads.show', $l),
                ];
            });

        // Invoices 

        SaleInvoice::with(['customer', 'vehicle.make'])
            ->where(fn($query) => $query
                ->where('invoice_number', 'like', "%{$q}%")
                ->orWhereHas('customer', fn($m) => $m->where('full_name', 
'like', "%{$q}%"))
            )
            ->limit(3)
            ->get()
            ->each(function ($inv) use (&$results) {
                $results[] = [
                    'type'  => 'invoice',
                    'icon'  => 'bi-receipt-cutoff',
                    'color' => '#7c3aed',
                    'bg'    => '#f5f3ff',
                    'title' => $inv->invoice_number . ' — ' . 
($inv->customer->full_name ?? ''),
                    'sub'   => 'PKR ' . number_format($inv->net_amount) . 
' · ' . ucfirst($inv->status),
                    'url'   => route('invoices.show', $inv),
                ];
            });

        return response()->json(['results' => $results]);
    }
}
