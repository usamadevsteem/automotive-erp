<form id="vehicleForm"
      action="{{ route('vehicles.update', $vehicle) }}"
      data-make-id="{{ $vehicle->make_id }}"
      data-model-id="{{ $vehicle->model_id }}"
      data-variant-id="{{ $vehicle->variant_id ?? '' }}"
      data-landing-cost="{{ (float) $vehicle->landing_cost }}">
    @csrf
    @method('PUT')

    <div class="row g-4"> 

        {{-- ── LEFT COLUMN ──────────────────────────────────────────── --}}
        <div class="col-lg-8">

            {{-- Classification --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-car-front text-primary me-2"></i>Vehicle Classification
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small required">Make</label>
                            <select name="make_id" class="form-select vf-make-select @error('make_id') is-invalid @enderror" required>
                                <option value="">Select Make</option>
                                @foreach($makes as $make)
                                    <option value="{{ $make->id }}"
                                        {{ old('make_id', $vehicle->make_id) == $make->id ? 'selected' : '' }}>
                                        {{ $make->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('make_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small required">Model</label>
                            <select name="model_id" class="form-select vf-model-select @error('model_id') is-invalid @enderror" required>
                                <option value="">Select Model</option>
                                @foreach($models as $model)
                                    <option value="{{ $model->id }}"
                                        {{ old('model_id', $vehicle->model_id) == $model->id ? 'selected' : '' }}>
                                        {{ $model->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('model_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Variant</label>
                            <select name="variant_id" class="form-select vf-variant-select">
                                <option value="">No Variant</option>
                                @foreach($variants as $variant)
                                    <option value="{{ $variant->id }}"
                                        {{ old('variant_id', $vehicle->variant_id) == $variant->id ? 'selected' : '' }}>
                                        {{ $variant->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small required">Category</label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                @foreach(\App\Models\Vehicle::CATEGORIES as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('category', $vehicle->category) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small required">Branch</label>
                            <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ old('branch_id', $vehicle->branch_id) == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small required">Import Status</label>
                            <select name="import_status" class="form-select vf-import-status @error('import_status') is-invalid @enderror" required>
                                @foreach(\App\Models\Vehicle::IMPORT_STATUSES as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('import_status', $vehicle->import_status) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('import_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Physical Details --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-info-circle text-primary me-2"></i>Physical Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small required">Year</label>
                            <input type="number" name="year"
                                   value="{{ old('year', $vehicle->year) }}"
                                   class="form-control @error('year') is-invalid @enderror"
                                   min="1990" max="{{ date('Y') + 1 }}" required>
                            @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Reg. Year</label>
                            <input type="number" name="registration_year"
                                   value="{{ old('registration_year', $vehicle->registration_year) }}"
                                   class="form-control"
                                   min="1990" max="{{ date('Y') + 1 }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small required">Mileage (km)</label>
                            <input type="number" name="mileage"
                                   value="{{ old('mileage', $vehicle->mileage) }}"
                                   class="form-control @error('mileage') is-invalid @enderror"
                                   min="0" required>
                            @error('mileage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Color</label>
                            <input type="text" name="color"
                                   value="{{ old('color', $vehicle->color) }}"
                                   class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small required">Fuel Type</label>
                            <select name="fuel_type" class="form-select @error('fuel_type') is-invalid @enderror" required>
                                @foreach(\App\Models\Vehicle::FUEL_TYPES as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('fuel_type', $vehicle->fuel_type) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('fuel_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small required">Transmission</label>
                            <select name="transmission" class="form-select @error('transmission') is-invalid @enderror" required>
                                @foreach(\App\Models\Vehicle::TRANSMISSIONS as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('transmission', $vehicle->transmission) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('transmission')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Engine Capacity</label>
                            <input type="text" name="engine_capacity"
                                   value="{{ old('engine_capacity', $vehicle->engine_capacity) }}"
                                   class="form-control" placeholder="e.g. 1800cc">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small required">Condition</label>
                            <select name="condition_grade" class="form-select @error('condition_grade') is-invalid @enderror" required>
                                @foreach(\App\Models\Vehicle::CONDITIONS as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('condition_grade', $vehicle->condition_grade) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('condition_grade')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3 vf-auction-grade-field"
                             style="{{ in_array($vehicle->import_status, ['imported','auction']) ? '' : 'display:none;' }}">
                            <label class="form-label fw-semibold small">Auction Grade</label>
                            <input type="text" name="auction_grade"
                                   value="{{ old('auction_grade', $vehicle->auction_grade) }}"
                                   class="form-control" placeholder="e.g. 4, 4.5, S">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Identity Numbers --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-fingerprint text-primary me-2"></i>Identity Numbers
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Registration Number</label>
                            <input type="text" name="registration_number"
                                   value="{{ old('registration_number', $vehicle->registration_number) }}"
                                   class="form-control text-uppercase">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Chassis Number</label>
                            <input type="text" name="chassis_number"
                                   value="{{ old('chassis_number', $vehicle->chassis_number) }}"
                                   class="form-control text-uppercase">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Engine Number</label>
                            <input type="text" name="engine_number"
                                   value="{{ old('engine_number', $vehicle->engine_number) }}"
                                   class="form-control text-uppercase">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">VIN Number</label>
                            <input type="text" name="vin_number"
                                   value="{{ old('vin_number', $vehicle->vin_number) }}"
                                   class="form-control text-uppercase">
                        </div>
                    </div>
                </div>
            </div>



                        {{-- Vehicle Photos --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-images text-primary me-2"></i>Vehicle Photos
                    </h6>
                </div>

                <div class="card-body">

                    {{-- Existing Images --}}

                    <div class="d-flex align-items-center justify-content-between mb-3">
                       <div class="fw-semibold">Vehicle Photos
                                <span class="text-muted small" id="selectedVehicleImagesCount">
                                    0 selected
                                </span>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                  onclick="selectAllVehicleImages()">Select All</button>

                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    id="deleteSelectedVehicleImagesBtn"
                                    onclick="deleteSelectedVehicleImages()"
                                    disabled>
                                    <i class="bi bi-trash me-1"></i>Delete Selected</button>
                            </div>
                        </div>




                    <div class="row g-3 mb-4" id="vehicleImageGrid">
                        @forelse($vehicle->getMedia('images') as $media)
                            <div class="col-6 col-md-4 col-xl-3">
                                <div class="card h-100 border position-relative overflow-hidden">
                                      {{-- Select image --}}
                                        <div class="position-absolute top-0 start-0 p-2" style="z-index:10;">
                                            <input
                                                type="checkbox"
                                                class="form-check-input vehicle-image-checkbox"
                                                value="{{ $media->id }}"
                                                data-vehicle-id="{{ $vehicle->id }}"
                                                style="width:22px;height:22px;"
                                                onchange="updateSelectedVehicleImages()">
                                        </div>
                                    <div style="height:140px;background:#f8f9fa;">
                                        <img
                                            src="{{ $media->getUrl('thumb') }}"
                                            alt="Vehicle photo"
                                            class="w-100 h-100"
                                            style="object-fit:cover;">
                                    </div>
                                    <div class="card-body p-2">
                                        @if($media->getCustomProperty('is_featured') === true)
                                            <span class="badge bg-primary mb-2">
                                                <i class="bi bi-star-fill me-1"></i>Featured
                                            </span>
                                        @endif
                                        <div class="d-flex gap-1 flex-wrap">

                                            @if($media->getCustomProperty('is_featured') !== true)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    onclick="setFeaturedVehicleImage({{ $vehicle->id }}, {{ $media->id }})">
                                                    <i class="bi bi-star me-1"></i>
                                                    Featured
                                                </button>
                                            @endif

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="deleteVehicleImage({{ $vehicle->id }}, {{ $media->id }})">
                                                <i class="bi bi-trash me-1"></i>
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty

                            <div class="col-12">
                                <div class="text-center py-4 border rounded bg-light">
                                    <i class="bi bi-image fs-2 text-muted"></i>
                                    <p class="text-muted mb-0 mt-2">
                                        No photos uploaded yet.
                                    </p>
                                </div>
                            </div>

                        @endforelse
                    </div>

                    {{-- Upload New Images --}}
                    <div class="border rounded p-3 bg-light">
                        <label class="form-label fw-semibold small">
                            <i class="bi bi-cloud-upload me-1"></i>
                            Upload More Photos
                        </label>
                        <input
                            type="file"
                            id="vehicleImagesInput"
                            class="form-control"
                            accept="image/jpeg,image/png,image/webp"
                            multiple>
                        <div class="form-text">
                            JPG, PNG or WEBP. Maximum 5 MB per image.
                            You can select multiple photos.
                        </div>
                    


                    <div class="mt-3 d-none" id="selectedImagesPreview"></div>
                        <button
                            type="button"
                            class="btn btn-primary mt-3"
                            onclick="uploadVehicleImages({{ $vehicle->id }})">
                            <i class="bi bi-upload me-1"></i>
                            Upload All Photos
                        </button>

                    </div>
                </div>
            </div>





            {{-- Notes --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-chat-left-text text-primary me-2"></i>Notes
                    </h6>
                </div>
                <div class="card-body">
                    <textarea name="notes" rows="3"
                              class="form-control">{{ old('notes', $vehicle->notes) }}</textarea>
                </div>
            </div>

        </div>

        {{-- ── RIGHT COLUMN — PRICING ───────────────────────────────── --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-currency-exchange text-primary me-2"></i>Pricing (PKR)
                    </h6>
                </div>
                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Stock Number</label>
                        <input type="text" class="form-control bg-light"
                               value="{{ $vehicle->stock_number }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small required">Purchase Price</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small">PKR</span>
                            <input type="text" name="purchase_price" class="form-control text-end vf-purchase-price @error('purchase_price') is-invalid @enderror"
                                   value="{{ old('purchase_price', number_format($vehicle->purchase_price, 0, '.', '')) }}"
                                   required>
                        </div>
                        @error('purchase_price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Repair Cost</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small">PKR</span>
                            <input type="text" name="repair_cost" class="form-control text-end vf-repair-cost"
                                   value="{{ old('repair_cost', number_format($vehicle->repair_cost, 0, '.', '')) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Miscellaneous Cost</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small">PKR</span>
                            <input type="text" name="misc_cost" class="form-control text-end vf-misc-cost"
                                   value="{{ old('misc_cost', number_format($vehicle->misc_cost, 0, '.', '')) }}">
                        </div>
                    </div>

                    @if($vehicle->landing_cost > 0)
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Landing cost <strong>PKR {{ number_format($vehicle->landing_cost) }}</strong>
                        is auto-synced from Import Costs.
                    </div>
                    @endif

                    <div class="bg-light rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted fw-semibold">Total Cost</small>
                            <span class="fw-bold vf-total-cost-display">
                                PKR {{ number_format($vehicle->total_cost) }}
                            </span>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small required">Sale Price</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small">PKR</span>
                            <input type="text" name="sale_price" class="form-control text-end vf-sale-price @error('sale_price') is-invalid @enderror"
                                   value="{{ old('sale_price', number_format($vehicle->sale_price, 0, '.', '')) }}"
                                   required>
                        </div>
                        @error('sale_price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Minimum Sale Price</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small">PKR</span>
                            <input type="text" name="min_sale_price" class="form-control text-end"
                                   value="{{ old('min_sale_price', number_format($vehicle->min_sale_price, 0, '.', '')) }}">
                        </div>
                    </div>

                    <div class="rounded p-3 mb-2 vf-profit-box"
                         style="background: {{ $vehicle->expected_profit >= 0 ? '#f0fdf4' : '#fef2f2' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted fw-semibold">Expected Profit</small>
                            <span class="fw-bold {{ $vehicle->expected_profit >= 0 ? 'text-success' : 'text-danger' }} vf-profit-display">
                                PKR {{ number_format($vehicle->expected_profit) }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</form>
