<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\InitiateTransferRequest;
use App\Http\Requests\Inventory\StoreVehicleRequest;
use App\Http\Requests\Inventory\UpdateImportCostRequest;
use App\Http\Requests\Inventory\UpdateVehicleRequest;
use App\Http\Requests\Inventory\UploadVehicleDocumentRequest;
use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\VehicleFileDocument;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use App\Models\VehicleTransfer;
use App\Models\VehicleVariant;
use App\Services\QrCodeService;
use App\Services\VehicleFileService;
use App\Services\VehicleService;
use App\Services\VehicleTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class VehicleController extends Controller
{
    public function __construct(
        private readonly VehicleService         $vehicleService,
        private readonly VehicleFileService     $fileService,
        private readonly VehicleTransferService $transferService,
        private readonly QrCodeService          $qrCodeService,
    ) {}

    // ══════════════════════════════════════════════════════════════════
    // INVENTORY LIST
    // ══════════════════════════════════════════════════════════════════

    public function index(Request $request): View
    {
        $filters  = $request->only([
            'status', 'make_id', 'model_id', 'category',
            'branch_id', 'fuel_type', 'transmission',
            'year', 'import_status', 'price_min', 'price_max', 'search',
        ]);

        $vehicles = Vehicle::with(['make', 'vehicleModel', 'variant', 'branch'])
            ->filter($filters)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Data for filter dropdowns
        $makes    = VehicleMake::active()->orderBy('name')->get();
        $branches = Branch::active()->orderBy('name')->get();

        // Summary counts for status tabs
        $statusCounts = Vehicle::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('inventory.index', compact(
            'vehicles', 'filters', 'makes', 'branches', 'statusCounts'
        ));
    }

    // ══════════════════════════════════════════════════════════════════
    // CREATE
    // ══════════════════════════════════════════════════════════════════

    public function create(): View
    {
        $makes    = VehicleMake::active()->orderBy('name')->get();
        $branches = Branch::active()->orderBy('name')->get();

        if (request()->ajax()) {
            return view('inventory.partials.create-form', compact('makes', 'branches'));
        }

        return view('inventory.create', compact('makes', 'branches'));
    }

  

    public function store(StoreVehicleRequest $request): RedirectResponse|JsonResponse
{

     \Log::info('Store request files', [
        'files' => array_keys($request->allFiles()),
        'images' => $request->hasFile('images') ? count($request->file('images')) : 0,
    ]);
    
    $vehicle = $this->vehicleService->create($request->validated());

    // ── Vehicle Images ─────────────────────────────────────────
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $index => $file) {
            $media = $vehicle
                ->addMedia($file)
                ->toMediaCollection('images');

            // First uploaded image becomes the featured image.
            if ($index === 0) {
                $media->setCustomProperty('is_featured', true);
                $media->save();
            }
        }
    }

    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => "Vehicle [{$vehicle->stock_number}] added to inventory.",
        ]);
    }

    return redirect()
        ->route('vehicles.show', $vehicle)
        ->with('success', "Vehicle [{$vehicle->stock_number}] added to inventory.");
}

    // ══════════════════════════════════════════════════════════════════
    // SHOW / DETAIL
    // ══════════════════════════════════════════════════════════════════

    public function show(Vehicle $vehicle): View
    {
        $vehicle->load([
            'make', 'vehicleModel', 'variant', 'branch',
            'addedBy', 'soldBy',
            'importCost',
            'fileDocuments.uploadedBy',
            'statusLogs.changedBy',
            'transfers.fromBranch',
            'transfers.toBranch',
            'transfers.transferredBy',
        ]);

        $checklist   = $this->fileService->getChecklist($vehicle);
        $completeness = $this->fileService->getCompleteness($vehicle);
        $qrStats     = $this->qrCodeService->getScanStats($vehicle);
        $qrImageUrl  = $this->qrCodeService->getImageUrl($vehicle);
        $branches    = Branch::active()->where('id', '!=', $vehicle->branch_id)->get();

        if (request()->ajax()) {
            return view('inventory.partials.full-view-modal', compact(
                'vehicle', 'checklist', 'completeness', 'qrStats', 'qrImageUrl', 'branches'
            ));
        }

        return view('inventory.show', compact(
            'vehicle', 'checklist', 'completeness',
            'qrStats', 'qrImageUrl', 'branches'
        ));
    }

    // ══════════════════════════════════════════════════════════════════
    // EDIT / UPDATE
    // ══════════════════════════════════════════════════════════════════

    public function edit(Vehicle $vehicle): View
    {
        $makes    = VehicleMake::active()->orderBy('name')->get();
        $models   = VehicleModel::where('make_id', $vehicle->make_id)->orderBy('name')->get();
        $variants = VehicleVariant::where('model_id', $vehicle->model_id)->orderBy('name')->get();
        $branches = Branch::active()->orderBy('name')->get();

        if (request()->ajax()) {
            return view('inventory.partials.edit-form', compact(
                'vehicle', 'makes', 'models', 'variants', 'branches'
            ));
        }

        return view('inventory.edit', compact(
            'vehicle', 'makes', 'models', 'variants', 'branches'
        ));
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): RedirectResponse|JsonResponse
    {
        $this->vehicleService->update($vehicle, $request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Vehicle details updated.',
            ]);
        }

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('success', 'Vehicle details updated.');
    }

    // ══════════════════════════════════════════════════════════════════
    // DELETE
    // ══════════════════════════════════════════════════════════════════

    public function destroy(Vehicle $vehicle): RedirectResponse|JsonResponse
    {
        $this->authorize('delete-vehicles');

        try {
            $stockNumber = $vehicle->stock_number;
            $this->vehicleService->delete($vehicle);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Vehicle [{$stockNumber}] removed from inventory.",
                ]);
            }

            return redirect()
                ->route('vehicles.index')
                ->with('success', "Vehicle [{$stockNumber}] removed from inventory.");

        } catch (\LogicException $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════
    // STATUS CHANGE
    // ══════════════════════════════════════════════════════════════════

    public function updateStatus(Request $request, Vehicle $vehicle): RedirectResponse|JsonResponse
    {
        $this->authorize('edit-vehicles');

        $request->validate([
            'status' => ['required', 'string', 'in:available,reserved,pending_inspection'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->vehicleService->changeStatus(
                vehicle:   $vehicle,
                newStatus: $request->status,
                reason:    $request->reason,
            );

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Vehicle status updated.']);
            }

            return back()->with('success', 'Vehicle status updated.');

        } catch (\InvalidArgumentException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════
    // IMPORT COSTS
    // ══════════════════════════════════════════════════════════════════

    public function editImportCosts(Vehicle $vehicle): View
    {
        $this->authorize('edit-vehicles');

        $importCost = $vehicle->importCost ?? new \App\Models\VehicleImportCost();

        return view('inventory.import-costs', compact('vehicle', 'importCost'));
    }

    public function updateImportCosts(UpdateImportCostRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $this->vehicleService->updateImportCosts($vehicle, $request->validated());

        return redirect()
            ->route('vehicles.show', $vehicle)
            ->with('success', 'Import costs updated. Landing cost synced to vehicle.');
    }

    // ══════════════════════════════════════════════════════════════════
    // VEHICLE FILE — DOCUMENT UPLOADS
    // ══════════════════════════════════════════════════════════════════

    public function uploadDocument(Request $request, Vehicle $vehicle): RedirectResponse|JsonResponse
    {
        $this->authorize('edit-vehicles');

        $data = $request->validate([
            'documents'                    => ['required', 'array', 'min:1'],
            'documents.*.document_type'    => ['required', Rule::in(array_keys(VehicleFileDocument::DOCUMENT_TYPES))],
            'documents.*.document_label'   => ['nullable', 'string', 'max:100'],
            'documents.*.file'             => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp'],
            'documents.*.expiry_date'      => ['nullable', 'date', 'after:today'],
            'documents.*.is_original'      => ['nullable', 'boolean'],
        ]);

        foreach ($data['documents'] as $doc) {
            $this->fileService->upload($vehicle, $doc['file'], [
                'document_type'  => $doc['document_type'],
                'document_label' => $doc['document_label'] ?? null,
                'expiry_date'    => $doc['expiry_date'] ?? null,
                'is_original'    => $doc['is_original'] ?? false,
            ]);
        }

        $count = count($data['documents']);
        $message = $count > 1 ? "{$count} documents uploaded to vehicle file." : 'Document uploaded to vehicle file.';

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function deleteDocument(Request $request, Vehicle $vehicle, VehicleFileDocument $document): RedirectResponse|JsonResponse
    {
        $this->authorize('edit-vehicles');

        // Ensure document belongs to this vehicle
        abort_if($document->vehicle_id !== $vehicle->id, 404);

        $this->fileService->delete($document);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Document removed.']);
        }

        return back()->with('success', 'Document removed.');
    }

    public function verifyDocument(Request $request, Vehicle $vehicle, VehicleFileDocument $document): RedirectResponse|JsonResponse
    {
        $this->authorize('edit-vehicles');
        abort_if($document->vehicle_id !== $vehicle->id, 404);

        $this->fileService->verify($document);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Document marked as verified.']);
        }

        return back()->with('success', 'Document marked as verified.');
    }

    // ══════════════════════════════════════════════════════════════════
    // VEHICLE IMAGES
    // ══════════════════════════════════════════════════════════════════

    public function uploadImages(Request $request, Vehicle $vehicle): RedirectResponse|JsonResponse
    {
        $this->authorize('edit-vehicles');

        $request->validate([
            'images'   => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $hasExisting = $vehicle->getMedia('images')->isNotEmpty();

        foreach ($request->file('images') as $i => $file) {
            $media = $vehicle->addMedia($file)->toMediaCollection('images');

            // First image ever uploaded for this vehicle becomes featured automatically
            if (!$hasExisting && $i === 0) {
                $media->setCustomProperty('is_featured', true)->save();
            }
        }

        $message = 'Image(s) uploaded successfully.';

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function setFeaturedImage(Request $request, Vehicle $vehicle, Media $media): RedirectResponse|JsonResponse
    {
        $this->authorize('edit-vehicles');
        abort_if($media->model_id !== $vehicle->id || $media->model_type !== Vehicle::class, 404);

        // Unset any previously featured image, then set this one
        foreach ($vehicle->getMedia('images') as $m) {
            $m->setCustomProperty('is_featured', false)->save();
        }
        $media->setCustomProperty('is_featured', true)->save();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Featured image updated.']);
        }

        return back()->with('success', 'Featured image updated.');
    }

    public function deleteImage(Request $request, Vehicle $vehicle, Media $media): RedirectResponse|JsonResponse
    {
        $this->authorize('edit-vehicles');
        abort_if($media->model_id !== $vehicle->id || $media->model_type !== Vehicle::class, 404);

        $wasFeatured = $media->getCustomProperty('is_featured') === true;
        $media->delete();

        // Promote another image to featured if the deleted one was featured
        if ($wasFeatured) {
            $next = $vehicle->getMedia('images')->first();
            $next?->setCustomProperty('is_featured', true)->save();
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Image removed.']);
        }

        return back()->with('success', 'Image removed.');
    }

    // ══════════════════════════════════════════════════════════════════
    // BRANCH TRANSFERS
    // ══════════════════════════════════════════════════════════════════

    public function initiateTransfer(InitiateTransferRequest $request, Vehicle $vehicle): RedirectResponse|JsonResponse
    {
        try {
            $this->transferService->initiate($vehicle, $request->validated());

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transfer request submitted. Awaiting branch approval.',
                ]);
            }

            return back()->with('success', 'Transfer request submitted. Awaiting branch approval.');

        } catch (\LogicException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function approveTransfer(Request $request, VehicleTransfer $transfer): RedirectResponse|JsonResponse
    {
        $this->authorize('transfer-vehicles');

        try {
            $this->transferService->approve($transfer);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Transfer approved.']);
            }

            return back()->with('success', 'Transfer approved.');

        } catch (\LogicException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function completeTransfer(Request $request, VehicleTransfer $transfer): RedirectResponse|JsonResponse
    {
        $this->authorize('transfer-vehicles');

        try {
            $this->transferService->complete($transfer);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Transfer completed. Vehicle moved to new branch.']);
            }

            return back()->with('success', 'Transfer completed. Vehicle moved to new branch.');

        } catch (\LogicException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function rejectTransfer(Request $request, VehicleTransfer $transfer): RedirectResponse|JsonResponse
    {
        $this->authorize('transfer-vehicles');

        $request->validate(['reason' => ['required', 'string', 'max:255']]);

        try {
            $this->transferService->reject($transfer, $request->reason);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Transfer rejected.']);
            }

            return back()->with('success', 'Transfer rejected.');

        } catch (\LogicException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════
    // QR CODE
    // ══════════════════════════════════════════════════════════════════

    public function regenerateQr(Request $request, Vehicle $vehicle): RedirectResponse|JsonResponse
    {
        $this->authorize('edit-vehicles');

        $this->qrCodeService->regenerate($vehicle);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'QR code regenerated.']);
        }

        return back()->with('success', 'QR code regenerated.');
    }

    /**
     * Public QR landing page — no auth required.
     * Accessible at: platform.com/v/{qrCode}
     */
    public function publicQrPage(string $qrCode, Request $request): View|\Illuminate\Http\Response
    {
        $vehicle = (new \App\Repositories\Eloquent\EloquentVehicleRepository)
            ->findByQrCode($qrCode);

        if (!$vehicle || !$vehicle->isAvailable()) {
            return response()->view('inventory.qr-not-found', [], 404);
        }

        // Log the scan
        $this->qrCodeService->logScan($vehicle, $request);

        return view('inventory.qr-public', compact('vehicle'));
    }

    // ══════════════════════════════════════════════════════════════════
    // AJAX HELPERS
    // ══════════════════════════════════════════════════════════════════

    /**
     * Return models for a given make — called via AJAX on create/edit form.
     */
    public function getModels(Request $request): JsonResponse
    {
        $models = VehicleModel::where('make_id', $request->make_id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($models);
    }

    /**
     * Return variants for a given model — called via AJAX.
     */
    public function getVariants(Request $request): JsonResponse
    {
        $variants = VehicleVariant::where('model_id', $request->model_id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($variants);
    }
}
