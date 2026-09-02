@extends('layouts.app')

@section('title', 'Import Vehicles')
@section('breadcrumb', 'Inventory / Import Vehicles')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">Import Vehicles</h4>
        <small class="text-muted">Bulk add vehicles from an Excel or CSV file</small>
    </div>
    <a href="{{ route('vehicles.index') }}" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Inventory
    </a>
</div>

{{-- Import result summary --}}
@if(session('import_total'))
<div class="alert alert-{{ session('import_failed') > 0 ? 'warning' : 'success' }} mb-4">
    <div class="d-flex align-items-start gap-3">
        <i class="bi bi-{{ session('import_failed') > 0 ? 'exclamation-triangle' : 'check-circle' }} fs-5 mt-1"></i>
        <div>
            <div class="fw-semibold">Import Complete</div>
            <div class="small mt-1">
                <span class="text-success fw-semibold">{{ session('import_success') }} added</span>
                @if(session('import_failed') > 0)
                    · <span class="text-danger fw-semibold">{{ session('import_failed') }} failed</span>
                @endif
                · {{ session('import_total') }} total rows processed
            </div>

            @if(session('import_errors'))
            <div class="mt-3">
                <div class="fw-semibold small mb-2">Errors:</div>
                <div class="small" style="max-height:200px; overflow-y:auto;">
                    @foreach(session('import_errors') as $error)
                    <div class="text-danger">
                        Row {{ $error['row'] }}: {{ $error['message'] }}
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

<div class="row g-4">
    <div class="col-lg-7">

        {{-- Step 1: Download Template --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center
                                justify-content-center fw-bold"
                         style="width:36px;height:36px;min-width:36px;">1</div>
                    <div class="flex-grow-1">
                        <h6 class="fw-semibold mb-1">Download the import template</h6>
                        <p class="text-muted small mb-3">
                            Use our Excel template to ensure your data is formatted correctly.
                            Row 2 is an example — replace it with your actual data.
                        </p>
                        <a href="{{ route('vehicles.template') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-download me-1"></i> Download Template (.xlsx)
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 2: Upload --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center
                                justify-content-center fw-bold"
                         style="width:36px;height:36px;min-width:36px;">2</div>
                    <div class="flex-grow-1">
                        <h6 class="fw-semibold mb-1">Upload your file</h6>
                        <p class="text-muted small mb-3">
                            Supported formats: .xlsx, .xls, .csv · Max file size: 5MB
                        </p>

                        <form method="POST"
                              action="{{ route('vehicles.import.store') }}"
                              enctype="multipart/form-data">
                            @csrf

                            <div class="border-2 border-dashed rounded p-4 text-center mb-3"
                                 id="dropzone"
                                 style="border: 2px dashed #d1d5db; cursor:pointer;">
                                <i class="bi bi-cloud-upload fs-3 text-muted d-block mb-2"></i>
                                <div class="fw-semibold small mb-1">
                                    Drop your Excel file here
                                </div>
                                <div class="text-muted" style="font-size:12px;">
                                    or click to browse
                                </div>
                                <input type="file"
                                       name="file"
                                       id="fileInput"
                                       accept=".xlsx,.xls,.csv"
                                       class="d-none"
                                       required>
                            </div>

                            <div id="fileSelected" class="alert alert-info py-2 small d-none">
                                <i class="bi bi-file-earmark-excel me-1"></i>
                                <span id="fileName"></span>
                            </div>

                            @error('file')
                            <div class="alert alert-danger py-2 small">{{ $message }}</div>
                            @enderror

                            <button type="submit" class="btn btn-primary" id="importBtn" disabled>
                                <i class="bi bi-upload me-1"></i> Start Import
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Instructions sidebar --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle text-primary me-2"></i>Import Instructions
                </h6>
            </div>
            <div class="card-body">
                <div class="small text-muted">
                    <p class="fw-semibold text-dark">Required columns:</p>
                    <ul class="ps-3">
                        <li><strong>Make</strong> — e.g. Toyota, Honda</li>
                        <li><strong>Model</strong> — e.g. Corolla, Civic</li>
                        <li><strong>Year</strong> — e.g. 2022</li>
                        <li><strong>Sale Price (PKR)</strong> — e.g. 3800000</li>
                    </ul>

                    <p class="fw-semibold text-dark mt-3">Valid values:</p>
                    <ul class="ps-3">
                        <li><strong>Category:</strong> Local Car, Imported Car, SUV, Pickup, Hybrid, Electric</li>
                        <li><strong>Fuel Type:</strong> Petrol, Diesel, Hybrid, Electric, CNG</li>
                        <li><strong>Transmission:</strong> Manual, Automatic, CVT</li>
                        <li><strong>Condition:</strong> Excellent, Good, Fair, Poor</li>
                        <li><strong>Import Status:</strong> Local, Imported, Auction</li>
                    </ul>

                    <div class="alert alert-warning py-2 mt-3 small">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        New Makes and Models found in the file will be created automatically.
                    </div>

                    <div class="alert alert-info py-2 small">
                        <i class="bi bi-info-circle me-1"></i>
                        All imported vehicles will be set to
                        <strong>Pending Inspection</strong> status.
                        QR codes are generated automatically.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const dropzone  = document.getElementById('dropzone');
const fileInput = document.getElementById('fileInput');
const importBtn = document.getElementById('importBtn');
const fileSelected = document.getElementById('fileSelected');
const fileName     = document.getElementById('fileName');

dropzone.addEventListener('click', () => fileInput.click());

dropzone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropzone.style.background = '#EFF6FF';
});
dropzone.addEventListener('dragleave', () => {
    dropzone.style.background = '';
});
dropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.style.background = '';
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        handleFile(e.dataTransfer.files[0]);
    }
});

fileInput.addEventListener('change', () => {
    if (fileInput.files.length) handleFile(fileInput.files[0]);
});

function handleFile(file) {
    fileName.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
    fileSelected.classList.remove('d-none');
    importBtn.disabled = false;
}
</script>
@endpush
