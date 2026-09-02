<?php

namespace App\Events;

use App\Models\Vehicle;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VehicleStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Vehicle $vehicle,
        public readonly ?string $fromStatus,
        public readonly string  $toStatus,
    ) {}
}
