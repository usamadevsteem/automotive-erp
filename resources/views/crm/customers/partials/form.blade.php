<form id="customerForm"
      action="{{ isset($customer) ? route('customers.update', $customer) : route('customers.store') }}">
    @csrf
    @if(isset($customer)) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-person text-primary me-2"></i>Personal Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold required">Full Name</label>
                            <input type="text" name="full_name" value="{{ old('full_name', $customer->full_name ?? '') }}"
                                   class="form-control @error('full_name') is-invalid @enderror" required>
                            @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Father / Husband Name</label>
                            <input type="text" name="father_husband_name"
                                   value="{{ old('father_husband_name', $customer->father_husband_name ?? '') }}"
                                   class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">CNIC</label>
                            <input type="text" name="cnic" value="{{ old('cnic', $customer->cnic ?? '') }}"
                                   class="form-control" placeholder="42101-1234567-1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold required">Mobile</label>
                            <input type="text" name="mobile" value="{{ old('mobile', $customer->mobile ?? '') }}"
                                   class="form-control @error('mobile') is-invalid @enderror" required placeholder="03XX-XXXXXXX">
                            @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Alt Mobile</label>
                            <input type="text" name="mobile_alt" value="{{ old('mobile_alt', $customer->mobile_alt ?? '') }}"
                                   class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" name="email" value="{{ old('email', $customer->email ?? '') }}"
                                   class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Occupation</label>
                            <input type="text" name="occupation" value="{{ old('occupation', $customer->occupation ?? '') }}"
                                   class="form-control">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Address</label>
                            <textarea name="address" rows="2" class="form-control">{{ old('address', $customer->address ?? '') }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">City</label>
                            <input type="text" name="city" value="{{ old('city', $customer->city ?? '') }}"
                                   class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Notes</label>
                            <textarea name="notes" rows="2" class="form-control">{{ old('notes', $customer->notes ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-gear text-primary me-2"></i>Classification</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold required">Customer Type</label>
                        <select name="customer_type" class="form-select" required>
                            @foreach(\App\Models\Customer::TYPES as $key => $label)
                                <option value="{{ $key }}" {{ old('customer_type', $customer->customer_type ?? 'buyer') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold required">Source</label>
                        <select name="source" class="form-select" required>
                            @foreach(\App\Models\Customer::SOURCES as $key => $label)
                                <option value="{{ $key }}" {{ old('source', $customer->source ?? 'walk_in') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tax Status</label>
                        <select name="tax_status" class="form-select">
                            <option value="unknown"    {{ old('tax_status', $customer->tax_status ?? 'unknown') === 'unknown' ? 'selected' : '' }}>Unknown</option>
                            <option value="filer"      {{ old('tax_status', $customer->tax_status ?? '') === 'filer' ? 'selected' : '' }}>Filer</option>
                            <option value="non_filer"  {{ old('tax_status', $customer->tax_status ?? '') === 'non_filer' ? 'selected' : '' }}>Non-Filer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Assigned To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($salesUsers as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to', $customer->assigned_to ?? '') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
