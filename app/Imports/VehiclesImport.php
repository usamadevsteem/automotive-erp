<?php

namespace App\Imports;

use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use App\Models\VehicleVariant;
use App\Services\StockNumberService;
use App\Services\QrCodeService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class VehiclesImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public int   $totalRows    = 0;
    public int   $successRows  = 0;
    public int   $failedRows   = 0;
    public array $errors       = [];

    public function __construct(
        private readonly StockNumberService $stockNumberService,
        private readonly QrCodeService      $qrCodeService,
    ) {}

    public function chunkSize(): int
    {
        return 100;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $this->totalRows++;
            $rowNumber = $index + 2; // +2 because row 1 = heading

            try {
                $this->processRow($row->toArray(), $rowNumber);
            } catch (\Exception $e) {
                $this->failedRows++;
                $this->errors[] = [
                    'row'     => $rowNumber,
                    'message' => $e->getMessage(),
                ];
            }
        }
    }

    private function processRow(array $row, int $rowNumber): void
    {
        // Normalize keys — remove spaces, lowercase
        $row = collect($row)
            ->mapWithKeys(fn($v, $k) => [
                strtolower(str_replace([' ', '#', '(', ')'], '_', trim($k ?? ''))) => $v
            ])
            ->toArray();

        // Validate the row
        $validator = Validator::make($row, [
            'make'        => ['required', 'string'],
            'model'       => ['required', 'string'],
            'year'        => ['required', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'sale_price_pkr' => ['required', 'numeric', 'min:0'],
            'purchase_price_pkr' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            $this->failedRows++;
            $this->errors[] = [
                'row'     => $rowNumber,
                'message' => implode(', ', $validator->errors()->all()),
            ];
            return;
        }

        DB::transaction(function () use ($row) {
            $tenant = app('tenant');

            // Resolve or create make
            $make = VehicleMake::firstOrCreate(
                ['name' => trim($row['make'])],
                ['is_active' => true]
            );

            // Resolve or create model
            $model = VehicleModel::firstOrCreate(
                ['make_id' => $make->id, 'name' => trim($row['model'])],
                ['is_active' => true]
            );

            // Resolve variant if provided
            $variantId = null;
            if (!empty($row['variant'])) {
                $variant   = VehicleVariant::firstOrCreate(
                    ['model_id' => $model->id, 'name' => trim($row['variant'])],
                    ['is_active' => true]
                );
                $variantId = $variant->id;
            }

            // Resolve branch
            $branchId = auth()->user()->branch_id;
            if (!empty($row['branch'])) {
                $branch = Branch::where('name', 'like', '%' . trim($row['branch']) . '%')
                    ->orWhere('code', trim($row['branch']))
                    ->first();
                if ($branch) $branchId = $branch->id;
            }

            // Map import status
            $importStatusMap = [
                'local'    => 'local',
                'imported' => 'imported',
                'auction'  => 'auction',
            ];
            $importStatus = $importStatusMap[
                strtolower(trim($row['import_status'] ?? 'local'))
            ] ?? 'local';

            // Map fuel type
            $fuelMap = [
                'petrol'   => 'petrol',
                'diesel'   => 'diesel',
                'hybrid'   => 'hybrid',
                'electric' => 'electric',
                'cng'      => 'cng',
            ];
            $fuelType = $fuelMap[strtolower(trim($row['fuel_type'] ?? 'petrol'))] ?? 'petrol';

            // Map transmission
            $transMap = [
                'manual'    => 'manual',
                'automatic' => 'automatic',
                'auto'      => 'automatic',
                'cvt'       => 'cvt',
            ];
            $transmission = $transMap[strtolower(trim($row['transmission'] ?? 'automatic'))] ?? 'automatic';

            $purchasePrice = (float) ($row['purchase_price_pkr'] ?? 0);
            $salePrice     = (float) ($row['sale_price_pkr'] ?? 0);
            $repairCost    = (float) ($row['repair_cost_pkr'] ?? 0);
            $miscCost      = (float) ($row['misc_cost_pkr'] ?? 0);
            $totalCost     = $purchasePrice + $repairCost + $miscCost;

            $vehicle = Vehicle::create([
                'tenant_id'           => $tenant->id,
                'branch_id'           => $branchId,
                'stock_number'        => $this->stockNumberService->generate(),
                'make_id'             => $make->id,
                'model_id'            => $model->id,
                'variant_id'          => $variantId,
                'category'            => $this->mapCategory($row['category'] ?? ''),
                'year'                => (int) $row['year'],
                'color'               => trim($row['color'] ?? '') ?: null,
                'mileage'             => (int) ($row['mileage_km'] ?? 0),
                'fuel_type'           => $fuelType,
                'transmission'        => $transmission,
                'engine_capacity'     => trim($row['engine'] ?? '') ?: null,
                'condition_grade'     => $this->mapCondition($row['condition'] ?? ''),
                'registration_number' => strtoupper(trim($row['registration_'] ?? '')) ?: null,
                'chassis_number'      => strtoupper(trim($row['chassis_'] ?? '')) ?: null,
                'engine_number'       => strtoupper(trim($row['engine_'] ?? '')) ?: null,
                'import_status'       => $importStatus,
                'auction_grade'       => trim($row['auction_grade'] ?? '') ?: null,
                'purchase_price'      => $purchasePrice,
                'repair_cost'         => $repairCost,
                'misc_cost'           => $miscCost,
                'total_cost'          => $totalCost,
                'sale_price'          => $salePrice,
                'min_sale_price'      => (float) ($row['min_sale_price_pkr'] ?? 0),
                'expected_profit'     => $salePrice - $totalCost,
                'status'              => 'pending_inspection',
                'added_by'            => auth()->id(),
            ]);

            // Generate QR code
            $this->qrCodeService->generate($vehicle);
        });

        $this->successRows++;
    }

    private function mapCategory(string $raw): string
    {
        $map = [
            'local car'    => 'local_car',
            'local'        => 'local_car',
            'imported car' => 'imported_car',
            'imported'     => 'imported_car',
            'suv'          => 'suv',
            'pickup'       => 'pickup',
            'truck'        => 'pickup',
            'hybrid'       => 'hybrid',
            'electric'     => 'electric',
            'ev'           => 'electric',
        ];

        return $map[strtolower(trim($raw))] ?? 'local_car';
    }

    private function mapCondition(string $raw): string
    {
        $map = [
            'excellent' => 'excellent',
            'good'      => 'good',
            'fair'      => 'fair',
            'average'   => 'fair',
            'poor'      => 'poor',
            'bad'       => 'poor',
        ];

        return $map[strtolower(trim($raw))] ?? 'good';
    }

    /**
     * Return a sample import template for download.
     */
    public static function getTemplateHeadings(): array
    {
        return [
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
            'Purchase Price (PKR)',
            'Repair Cost (PKR)',
            'Misc Cost (PKR)',
            'Sale Price (PKR)',
            'Min Sale Price (PKR)',
        ];
    }
}
