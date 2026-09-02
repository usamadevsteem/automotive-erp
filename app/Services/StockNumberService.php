<?php

namespace App\Services;

use App\Models\Vehicle;
use Illuminate\Support\Facades\Cache;

class StockNumberService
{
    public function generate(): string
    {
        $tenant = app('tenant');
        $year = now()->year;

        $lockKey = "stock_number_lock:{$tenant->id}:{$year}";

        return Cache::lock($lockKey, 10)->block(5, function () use ($tenant, $year) {

            $stockNumbers = Vehicle::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('stock_number', 'like', "STK-{$year}-%")
                ->pluck('stock_number');

            $highest = 0;

            foreach ($stockNumbers as $stockNumber) {

                if (preg_match(
                    '/^STK-' . $year . '-(\d+)$/',
                    $stockNumber,
                    $matches
                )) {
                    $number = (int) $matches[1];

                    if ($number > $highest) {
                        $highest = $number;
                    }
                }
            }

            $next = $highest + 1;

            return sprintf(
                'STK-%d-%04d',
                $year,
                $next
            );
        });
    }
}