<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — v1
|--------------------------------------------------------------------------
| All API routes require Sanctum authentication.
| Tenant is resolved via X-Tenant-ID header or subdomain.
*/

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

    // Auth
    Route::post('/auth/login',  [\App\Http\Controllers\Auth\LoginController::class, 'login']);
    Route::post('/auth/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout']);
    Route::get('/auth/me', fn(Request $r) => $r->user());

    // Dashboard KPIs
    Route::get('/dashboard/kpis', [\App\Http\Controllers\DashboardController::class, 'index']);

    // Vehicles
    Route::apiResource('vehicles', \App\Http\Controllers\Inventory\VehicleController::class)->names('api.vehicles');
    Route::get('/vehicles/{vehicle}/file-documents', [\App\Http\Controllers\Inventory\VehicleController::class, 'show']);
    Route::get('/vehicles/{vehicle}/qr', [\App\Http\Controllers\Inventory\VehicleController::class, 'regenerateQr']);

    // Export
    Route::get('/export/vehicles', [\App\Http\Controllers\Inventory\VehicleImportExportController::class, 'export']);
});


/*
|--------------------------------------------------------------------------
| Public API Routes — No authentication required
| Used by the public showroom website
|--------------------------------------------------------------------------
*/
Route::prefix('public')->group(function () {

    // Available vehicles (listing + filters)
    Route::get('/vehicles', [\App\Http\Controllers\Api\PublicApiController::class, 'vehicles']);

    // Single vehicle detail
    Route::get('/vehicles/{vehicle}', [\App\Http\Controllers\Api\PublicApiController::class, 'vehicle']);

    // Branches / locations
    Route::get('/branches', [\App\Http\Controllers\Api\PublicApiController::class, 'branches']);

    // Submit a lead (contact form, test drive request, trade-in enquiry)
    Route::post('/leads', [\App\Http\Controllers\Api\PublicApiController::class, 'submitLead']);
});