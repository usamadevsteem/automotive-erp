<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\VehiclesExport;
use App\Http\Controllers\Controller;
use App\Imports\VehiclesImport;
use App\Services\QrCodeService;
use App\Services\StockNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VehicleImportExportController extends Controller
{
    // ── Export ─────────────────────────────────────────────────────────

    public function export(Request $request): BinaryFileResponse
    {
        $this->authorize('view-vehicles');

        $filters      = $request->only([
            'status', 'make_id', 'model_id', 'category',
            'branch_id', 'year', 'import_status',
        ]);
        $includeCosts = $request->boolean('include_costs')
                        && auth()->user()->can('view-vehicle-cost');

        $filename = 'vehicles-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new VehiclesExport($filters, $includeCosts),
            $filename
        );
    }

    // ── Import Template Download ────────────────────────────────────────

    public function downloadTemplate(): BinaryFileResponse
    {
        $this->authorize('create-vehicles');

        // Build a simple template with headings and one example row
        $headings = \App\Imports\VehiclesImport::getTemplateHeadings();

        $example = [
            'Toyota', 'Corolla', '1.8 Altis Grande', 'Local Car',
            2022, 'Pearl White', 15000, 'Petrol', 'Automatic', '1800cc',
            'Good', 'LHR-2022-AB-1234', 'NZ7BUEA8XPE123456', 'K54-123456',
            'Local', '', 'Main', 3200000, 50000, 20000, 3800000, 3600000,
        ];

        $filename = 'vehicle-import-template.xlsx';

        return Excel::download(
            new \App\Exports\TemplateExport($headings, [$example]),
            $filename
        );
    }

    // ── Import ─────────────────────────────────────────────────────────

    public function showImport()
    {
        $this->authorize('create-vehicles');

        return view('inventory.import');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('create-vehicles');

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $import = new VehiclesImport(
            app(StockNumberService::class),
            app(QrCodeService::class),
        );

        Excel::import($import, $request->file('file'));

        $message = sprintf(
            'Import complete. %d added, %d failed out of %d total rows.',
            $import->successRows,
            $import->failedRows,
            $import->totalRows,
        );

        $sessionData = [
            'import_success' => $import->successRows,
            'import_failed'  => $import->failedRows,
            'import_total'   => $import->totalRows,
            'import_errors'  => $import->errors,
        ];

        if ($import->failedRows > 0) {
            return redirect()
                ->route('vehicles.import')
                ->with('warning', $message)
                ->with($sessionData);
        }

        return redirect()
            ->route('vehicles.index')
            ->with('success', $message)
            ->with($sessionData);
    }
}
