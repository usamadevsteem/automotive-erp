<?php

namespace App\Exports;

use App\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class VehiclesExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithTitle
{
    public function __construct(
        private readonly array $filters = [],
        private readonly bool  $includeCosts = false,
    ) {}

    public function title(): string
    {
        return 'Vehicle Inventory';
    }

    public function query()
    {
        return Vehicle::with(['make', 'vehicleModel', 'variant', 'branch'])
            ->filter($this->filters)
            ->latest();
    }

    public function headings(): array
    {
        $base = [
            'Stock #',
            'Make',
            'Model',
            'Variant',
            'Category',
            'Year',
            'Color',
            'Mileage (km)',
            'Fuel Type',
            'Transmission',
            'Engine',
            'Condition',
            'Registration #',
            'Chassis #',
            'Engine #',
            'Import Status',
            'Auction Grade',
            'Branch',
            'Status',
            'Sale Price (PKR)',
        ];

        if ($this->includeCosts) {
            array_splice($base, 19, 0, [
                'Purchase Price (PKR)',
                'Landing Cost (PKR)',
                'Repair Cost (PKR)',
                'Misc Cost (PKR)',
                'Total Cost (PKR)',
                'Expected Profit (PKR)',
            ]);
        }

        $base[] = 'Added On';

        return $base;
    }

    public function map($vehicle): array
    {
        $base = [
            $vehicle->stock_number,
            $vehicle->make->name,
            $vehicle->vehicleModel->name,
            $vehicle->variant?->name ?? '',
            Vehicle::CATEGORIES[$vehicle->category] ?? $vehicle->category,
            $vehicle->year,
            $vehicle->color ?? '',
            $vehicle->mileage,
            Vehicle::FUEL_TYPES[$vehicle->fuel_type] ?? $vehicle->fuel_type,
            Vehicle::TRANSMISSIONS[$vehicle->transmission] ?? $vehicle->transmission,
            $vehicle->engine_capacity ?? '',
            Vehicle::CONDITIONS[$vehicle->condition_grade] ?? $vehicle->condition_grade,
            $vehicle->registration_number ?? '',
            $vehicle->chassis_number ?? '',
            $vehicle->engine_number ?? '',
            Vehicle::IMPORT_STATUSES[$vehicle->import_status] ?? $vehicle->import_status,
            $vehicle->auction_grade ?? '',
            $vehicle->branch->name,
            Vehicle::STATUSES[$vehicle->status] ?? $vehicle->status,
        ];

        if ($this->includeCosts) {
            array_splice($base, 19, 0, [
                (float) $vehicle->purchase_price,
                (float) $vehicle->landing_cost,
                (float) $vehicle->repair_cost,
                (float) $vehicle->misc_cost,
                (float) $vehicle->total_cost,
                (float) $vehicle->expected_profit,
            ]);
        }

        $base[] = $vehicle->created_at->format('d/m/Y');

        return $base;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Bold header row
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1A56DB'],
                ],
            ],
        ];
    }
}
