<form id="vendorForm"
      action="{{ isset($vendor) ? route('vendors.update', $vendor) : route('vendors.store') }}">
    @csrf
    @if(isset($vendor)) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label small fw-semibold required">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $vendor->name ?? '') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold required">Type</label>
            <select name="vendor_type" class="form-select" required>
                @foreach(\App\Models\Vendor::TYPES as $k=>$v)
                    <option value="{{ $k }}" {{ old('vendor_type', $vendor->vendor_type ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Phone</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $vendor->phone ?? '') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $vendor->email ?? '') }}">
        </div>
        <div class="col-12">
            <label class="form-label small fw-semibold">Address</label>
            <textarea name="address" rows="2" class="form-control">{{ old('address', $vendor->address ?? '') }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">City</label>
            <input type="text" name="city" class="form-control" value="{{ old('city', $vendor->city ?? '') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">NTN Number</label>
            <input type="text" name="ntn_number" class="form-control" value="{{ old('ntn_number', $vendor->ntn_number ?? '') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Bank Name</label>
            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $vendor->bank_name ?? '') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Account Number</label>
            <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $vendor->account_number ?? '') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Opening Balance</label>
            <input type="number" name="opening_balance" class="form-control" value="{{ old('opening_balance', $vendor->opening_balance ?? 0) }}">
        </div>
        @if(isset($vendor))
        <div class="col-md-6 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="vendorIsActive"
                       {{ old('is_active', $vendor->is_active) ? 'checked' : '' }}>
                <label class="form-check-label small fw-semibold" for="vendorIsActive">Active</label>
            </div>
        </div>
        @endif
        <div class="col-12">
            <label class="form-label small fw-semibold">Notes</label>
            <textarea name="notes" rows="2" class="form-control">{{ old('notes', $vendor->notes ?? '') }}</textarea>
        </div>
    </div>
</form>
