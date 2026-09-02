<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Lead;
use App\Models\Platform\Tenant;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicApiController extends Controller
{
    private function tenant(): ?Tenant
    {
        // Resolve tenant from subdomain or X-Tenant header 
        $subdomain = request()->header('X-Tenant')
            ?? explode('.', request()->getHost())[0]
            ?? null;

        return $subdomain ? Tenant::where('subdomain', 
$subdomain)->first() : null;
    }

    public function vehicles(Request $request): JsonResponse
    {
        $tenant = $this->tenant();
        if (!$tenant) return response()->json(['error' => 'Tenant not 
found'], 404);

        app()->instance('tenant', $tenant);

        $query = Vehicle::with(['make', 'vehicleModel', 'variant', 
'branch'])
            ->where('status', 'available')
            ->when($request->make,      fn($q,$v) => $q->whereHas('make', 
fn($q) => $q->where('name', $v)))
            ->when($request->model,     fn($q,$v) => 
$q->whereHas('vehicleModel', fn($q) => $q->where('name', $v)))
            ->when($request->year,      fn($q,$v) => $q->where('year', 
$v))
            ->when($request->min_price, fn($q,$v) => 
$q->where('sale_price', '>=', $v))
            ->when($request->max_price, fn($q,$v) => 
$q->where('sale_price', '<=', $v))
            ->when($request->fuel_type, fn($q,$v) => 
$q->where('fuel_type', $v))
            ->when($request->transmission, fn($q,$v) => 
$q->where('transmission', $v))
            ->when($request->branch_id, fn($q,$v) => 
$q->where('branch_id', $v));

        $sort = $request->sort ?? 'newest';
        match ($sort) {
            'price_asc'  => $query->orderBy('sale_price'),
            'price_desc' => $query->orderByDesc('sale_price'),
            'mileage'    => $query->orderBy('mileage'),
            default      => $query->latest(),
        };

        $vehicles = $query->paginate($request->per_page ?? 12);

        return response()->json([
            'data' => $vehicles->map(fn($v) => $this->formatVehicle($v)),
            'meta' => [
                'total'        => $vehicles->total(),
                'per_page'     => $vehicles->perPage(),
                'current_page' => $vehicles->currentPage(),
                'last_page'    => $vehicles->lastPage(),
            ],
        ]);
    }

    public function vehicle(Request $request, Vehicle $vehicle): 
JsonResponse
    {
        $tenant = $this->tenant();
        if (!$tenant) return response()->json(['error' => 'Tenant not 
found'], 404);

        // Security: only expose available vehicles from this tenant
        if ($vehicle->tenant_id !== $tenant->id || $vehicle->status !== 
'available') {
            return response()->json(['error' => 'Vehicle not found'], 
404);
        }

        $vehicle->load(['make', 'vehicleModel', 'variant', 
'branch']);

        return response()->json([
            'data' => $this->formatVehicle($vehicle, detailed: true),
        ]);
    }

    public function branches(Request $request): JsonResponse
    {
        $tenant = $this->tenant();
        if (!$tenant) return response()->json(['error' => 'Tenant not 
found'], 404);

        app()->instance('tenant', $tenant);

        $branches = Branch::active()->get()->map(fn($b) => [
            'id'      => $b->id,
            'name'    => $b->name,
            'address' => $b->address,
            'city'    => $b->city,
            'phone'   => $b->phone,
            'email'   => $b->email,
        ]);

        return response()->json(['data' => $branches]);
    }

    public function submitLead(Request $request): JsonResponse
    {
        $tenant = $this->tenant();
        if (!$tenant) return response()->json(['error' => 'Tenant not 
found'], 404);

        app()->instance('tenant', $tenant);

        $data = $request->validate([
            'full_name'        => ['required', 'string', 'max:100'],
            'phone'            => ['required', 'string', 'max:20'],
            'email'            => ['nullable', 'email'],
            'source'           => ['nullable', 'string'],
            'vehicle_interest' => ['nullable', 'string', 'max:200'],
            'budget'           => ['nullable', 'numeric', 'min:0'],
            'notes'            => ['nullable', 'string', 'max:500'],
            'enquiry_type'     => ['nullable', 
'in:general,test_drive,trade_in,finance'],
        ]);

        Lead::create([
            'tenant_id'        => $tenant->id,
            'branch_id'        => Branch::where('tenant_id', 
$tenant->id)->first()?->id,
            'full_name'        => $data['full_name'],
            'phone'            => $data['phone'],
            'email'            => $data['email'] ?? null,
            'source'           => 'website',
            'vehicle_interest' => $data['vehicle_interest'] ?? null,
            'budget'           => $data['budget'] ?? null,
            'notes'            => trim(($data['enquiry_type'] ? 
ucfirst(str_replace('_', ' ', $data['enquiry_type'])) . ' enquiry. ' : '') 
. ($data['notes'] ?? '')),
            'status'           => 'new',
            'created_by'       => 1, // system/website submission
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you! We will contact you shortly.',
        ], 201);
    }

    private function formatVehicle(Vehicle $v, bool $detailed = false): 
array
    {
        $base = [
    'id'              => $v->id,
    'stock_number'    => $v->stock_number,
    'make'            => $v->make->name,
    'model'           => $v->vehicleModel->name,
    'variant'         => $v->variant?->name,
    'year'            => $v->year,
    'color'           => $v->color,
    'mileage'         => $v->mileage,
    'fuel_type'       => $v->fuel_type,
    'transmission'    => $v->transmission,
    'sale_price'      => $v->sale_price,
    'branch'          => $v->branch->name,
    'branch_id'       => $v->branch_id,
    'featured_image' => $v->featuredImageUrl('gallery'),

    'images' => $v->getMedia('images')->map(fn ($media) => [
    'id'       => $media->id,
    'thumb'    => $media->getUrl('thumb'),
    'gallery'  => $media->getUrl('gallery'),
    'original' => $media->getUrl(),
])->values()->all(),

];

        if ($detailed) {
            $base = array_merge($base, [
                'engine_capacity'     => $v->engine_capacity,
                'condition_grade'     => $v->condition_grade,
                'category'            => $v->category,
                'registration_number' => $v->registration_number,
                'import_status'       => $v->import_status,
                'registration_year'   => $v->registration_year,
                'notes'               => $v->notes,
                'qr_code'             => $v->qr_code,
            ]);
        }

        return $base;
    }
}
