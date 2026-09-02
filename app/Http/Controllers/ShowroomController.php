<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Lead;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShowroomController extends Controller
{
    public function home(): View
    {
        $vehicles = Vehicle::with([
            'make',
            'vehicleModel',
            'variant',
            'branch',
        ])
        ->where('status', Vehicle::STATUS_AVAILABLE)
        ->latest()
        ->take(6)
        ->get();

        return view('showroom.home', compact('vehicles'));
    }



    public function about(): View
    {
    return view('showroom.about');
    }
    
    public function contact(Request $request): View
    {
    $vehicle = null;

    if ($request->filled('vehicle_id')) {
        $vehicle = Vehicle::with([
            'make',
            'vehicleModel',
            'variant',
        ])->find($request->vehicle_id);
    }

    return view('showroom.contact', compact('vehicle'));
    }

public function submitContact(Request $request): RedirectResponse
{

    
   $data = $request->validate([
    'name' => ['required', 'string', 'max:100'],
    'phone' => ['required', 'string', 'max:20'],
    'email' => ['nullable', 'email', 'max:150'],
    'subject' => [
        'required',
        'in:vehicle-enquiry,test-drive,sell-trade,financing,general',
    ],
    'message' => ['required', 'string', 'max:1000'],
    'vehicle_id' => ['nullable', 'exists:vehicles,id'],
]);

        $vehicle = null;

        if (!empty($data['vehicle_id'])) {
            $vehicle = Vehicle::with([
                'make',
                'vehicleModel',
                'variant',
            ])->find($data['vehicle_id']);
        }


    $tenant = app()->bound('tenant')
        ? app('tenant')
        : null;

    abort_unless($tenant, 404, 'Showroom tenant not found.');

    $branch = Branch::where('tenant_id', $tenant->id)
        ->where('is_active', true)
        ->first();

    abort_unless($branch, 404, 'Showroom branch not found.');

    Lead::create([
        'tenant_id' => $tenant->id,
        'branch_id' => $branch->id,
        'full_name' => $data['name'],
        'phone' => $data['phone'],
        'email' => $data['email'] ?? null,
        'source' => 'website',
        'vehicle_interest' => $vehicle
                                ? $vehicle->make->name . ' '
                                    . $vehicle->vehicleModel->name . ' '
                                    . $vehicle->year
                                : null,
        'budget' => null,
        'status' => 'new',
        'created_by' => 1,
        'notes' => ucfirst(str_replace('-', ' ', $data['subject']))
                . ".\n\n"
                . $data['message'],
    ]);

    return redirect()
        ->route('showroom.contact')
        ->with('contact_success', 'Thank you! We will contact you shortly.');
}

    public function inventory(): View
{
    $query = Vehicle::with([
        'make',
        'vehicleModel',
        'variant',
        'branch',
    ])
    ->where('status', Vehicle::STATUS_AVAILABLE);

    // Search
    if (request('search')) {
        $search = request('search');

        $query->where(function ($q) use ($search) {
            $q->where('stock_number', 'like', "%{$search}%")
                ->orWhereHas('make', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('vehicleModel', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        });
    }

    // Make / Brand
    if (request('make')) {
        $query->where('make_id', request('make'));
    }

    // Fuel type
    if (request('fuel_type')) {
        $query->where('fuel_type', request('fuel_type'));
    }

    // Transmission
    if (request('transmission')) {
        $query->where('transmission', request('transmission'));
    }

    // Minimum price
    if (request('min_price')) {
        $query->where('sale_price', '>=', request('min_price'));
    }

    // Maximum price
    if (request('max_price')) {
        $query->where('sale_price', '<=', request('max_price'));
    }

    // Sorting
    switch (request('sort')) {
        case 'price_low':
            $query->orderBy('sale_price', 'asc');
            break;

        case 'price_high':
            $query->orderBy('sale_price', 'desc');
            break;

        case 'oldest':
            $query->oldest();
            break;

        default:
            $query->latest();
            break;
    }

    $vehicles = $query
        ->paginate(12)
        ->withQueryString();

    $makes = \App\Models\VehicleMake::orderBy('name')->get();

    return view('showroom.inventory', compact(
        'vehicles',
        'makes'
    ));


}


 public function show(Vehicle $vehicle): View
{
    abort_unless(
        $vehicle->status === Vehicle::STATUS_AVAILABLE,
        404
    );

    $vehicle->load([
        'make',
        'vehicleModel',
        'variant',
        'branch',
        'media',
    ]);

    $vehicleImages = $vehicle->getMedia('images');

    $featuredMedia = $vehicleImages
        ->first(
            fn ($media) =>
                $media->getCustomProperty('is_featured') === true
        )
        ?? $vehicleImages->first();

    $featuredImageUrl = $featuredMedia?->getUrl();

    $similarVehicles = Vehicle::with([
        'make',
        'vehicleModel',
        'variant',
    ])
        ->where('status', Vehicle::STATUS_AVAILABLE)
        ->where('id', '!=', $vehicle->id)
        ->where('make_id', $vehicle->make_id)
        ->latest()
        ->take(3)
        ->get();

    return view('showroom.vehicle', compact(
        'vehicle',
        'vehicleImages',
        'featuredImageUrl',
        'similarVehicles'
    ));
}

  
}