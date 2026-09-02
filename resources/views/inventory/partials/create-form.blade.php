<form id="vehicleForm"
      action="{{ route('vehicles.store') }}"
      data-make-id=""
      data-model-id=""
      data-variant-id=""
      data-landing-cost="0">
    @csrf

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
                                    <option value="{{ $make->id }}" {{ old('make_id') == $make->id ? 'selected' : '' }}>
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
                            </select>
                            @error('model_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Variant</label>
                            <select name="variant_id" class="form-select vf-variant-select">
                                <option value="">No Variant</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small required">Category</label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                <option value="">Select Category</option>
                                @foreach(\App\Models\Vehicle::CATEGORIES as $key => $label)
                                    <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small required">Branch</label>
                            <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                <option value="">Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}"
                                        {{ old('branch_id', auth()->user()->branch_id) == $branch->id ? 'selected' : '' }}>
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
                                    <option value="{{ $key }}" {{ old('import_status', 'local') === $key ? 'selected' : '' }}>{{ $label }}</option>
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
                            <input type="number" name="year" value="{{ old('year') }}"
                                   class="form-control @error('year') is-invalid @enderror"
                                   min="1990" max="{{ date('Y') + 1 }}" placeholder="{{ date('Y') }}" required>
                            @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Reg. Year</label>
                            <input type="number" name="registration_year" value="{{ old('registration_year') }}"
                                   class="form-control" min="1990" max="{{ date('Y') + 1 }}" placeholder="Optional">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small required">Mileage (km)</label>
                            <input type="number" name="mileage" value="{{ old('mileage', 0) }}"
                                   class="form-control @error('mileage') is-invalid @enderror" min="0" required>
                            @error('mileage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Color</label>
                            <input type="text" name="color" value="{{ old('color') }}"
                                   class="form-control" placeholder="e.g. Pearl White">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small required">Fuel Type</label>
                            <select name="fuel_type" class="form-select @error('fuel_type') is-invalid @enderror" required>
                                @foreach(\App\Models\Vehicle::FUEL_TYPES as $key => $label)
                                    <option value="{{ $key }}" {{ old('fuel_type', 'petrol') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('fuel_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small required">Transmission</label>
                            <select name="transmission" class="form-select @error('transmission') is-invalid @enderror" required>
                                @foreach(\App\Models\Vehicle::TRANSMISSIONS as $key => $label)
                                    <option value="{{ $key }}" {{ old('transmission', 'automatic') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('transmission')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Engine Capacity</label>
                            <input type="text" name="engine_capacity" value="{{ old('engine_capacity') }}"
                                   class="form-control" placeholder="e.g. 1800cc">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold small required">Condition</label>
                            <select name="condition_grade" class="form-select @error('condition_grade') is-invalid @enderror" required>
                                @foreach(\App\Models\Vehicle::CONDITIONS as $key => $label)
                                    <option value="{{ $key }}" {{ old('condition_grade', 'good') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('condition_grade')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3 vf-auction-grade-field" style="display:none;">
                            <label class="form-label fw-semibold small">Auction Grade</label>
                            <input type="text" name="auction_grade" value="{{ old('auction_grade') }}"
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
                            <input type="text" name="registration_number" value="{{ old('registration_number') }}"
                                   class="form-control text-uppercase" placeholder="e.g. LHR-2023-AB-1234">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Chassis Number</label>
                            <input type="text" name="chassis_number" value="{{ old('chassis_number') }}"
                                   class="form-control text-uppercase" placeholder="17-character VIN/Chassis">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Engine Number</label>
                            <input type="text" name="engine_number" value="{{ old('engine_number') }}"
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
                    <label class="form-label fw-semibold small">
                        Upload Photos
                    </label>
                    <input
                        type="file"
                        name="images[]"
                        id="vehicleImages"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp"
                        multiple>
                    <div class="form-text">
                        Upload up to 10 photos. JPG, PNG or WEBP. Maximum 5 MB per image.
                        The first photo will be used as the featured image.
                    </div>

                    <div
                        id="vehicleImagePreview"
                        class="d-flex flex-wrap gap-3 mt-3"></div>

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
                    <textarea name="notes" rows="3" class="form-control"
                              placeholder="Internal notes about this vehicle...">{{ old('notes') }}</textarea>
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
                        <label class="form-label fw-semibold small required">Purchase Price</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small">PKR</span>
                            <input type="text" name="purchase_price" class="form-control text-end vf-purchase-price @error('purchase_price') is-invalid @enderror"
                                   value="{{ old('purchase_price', 0) }}" required>
                        </div>
                        @error('purchase_price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Repair Cost</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small">PKR</span>
                            <input type="text" name="repair_cost" class="form-control text-end vf-repair-cost"
                                   value="{{ old('repair_cost', 0) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Miscellaneous Cost</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small">PKR</span>
                            <input type="text" name="misc_cost" class="form-control text-end vf-misc-cost"
                                   value="{{ old('misc_cost', 0) }}">
                        </div>
                    </div>

                    <div class="bg-light rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted fw-semibold">Total Cost</small>
                            <span class="fw-bold vf-total-cost-display">PKR 0</span>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small required">Sale Price</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small">PKR</span>
                            <input type="text" name="sale_price" class="form-control text-end vf-sale-price @error('sale_price') is-invalid @enderror"
                                   value="{{ old('sale_price', 0) }}" required>
                        </div>
                        @error('sale_price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Minimum Sale Price</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small">PKR</span>
                            <input type="text" name="min_sale_price" class="form-control text-end"
                                   value="{{ old('min_sale_price', 0) }}">
                        </div>
                        <div class="form-text">Minimum acceptable price for negotiations.</div>
                    </div>

                    <div class="rounded p-3 mb-2 vf-profit-box" style="background:#f0fdf4;">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted fw-semibold">Expected Profit</small>
                            <span class="fw-bold text-success vf-profit-display">PKR 0</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</form>
