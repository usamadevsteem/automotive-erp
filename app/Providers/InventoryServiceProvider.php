<?php

namespace App\Providers;

use App\Repositories\Contracts\VehicleRepositoryInterface;
use App\Repositories\Eloquent\EloquentVehicleRepository;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            VehicleRepositoryInterface::class,
            EloquentVehicleRepository::class,
        );

        $this->app->singleton(\App\Services\StockNumberService::class);
        $this->app->singleton(\App\Services\QrCodeService::class);
        $this->app->singleton(\App\Services\NumberingService::class);
        $this->app->singleton(\App\Services\AccountingService::class);
        $this->app->singleton(\App\Services\SaleService::class);
        $this->app->singleton(\App\Services\PaymentService::class);
        $this->app->singleton(\App\Services\CommissionService::class);
        $this->app->singleton(\App\Services\DocumentService::class);
        $this->app->singleton(\App\Services\WhatsAppService::class);
    }

    public function boot(): void
    {
        //
    }
}
