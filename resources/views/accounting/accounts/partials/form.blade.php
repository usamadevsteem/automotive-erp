<form id="accountForm"
      action="{{ isset($account) ? route('accounts.update', $account) : route('accounts.store') }}">
    @csrf
    @if(isset($account)) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label small fw-semibold required">Code</label>
            <input type="text" name="account_code" class="form-control"
                   value="{{ old('account_code', $account->account_code ?? '') }}"
                   {{ isset($account) && $account->is_system ? 'readonly' : '' }} required>
        </div>
        <div class="col-md-8">
            <label class="form-label small fw-semibold required">Name</label>
            <input type="text" name="account_name" class="form-control"
                   value="{{ old('account_name', $account->account_name ?? '') }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold required">Type</label>
            <select name="account_type" class="form-select" {{ isset($account) && $account->is_system ? 'disabled' : '' }} required>
                @foreach(\App\Models\Account::TYPES as $k=>$v)
                    <option value="{{ $k }}" {{ old('account_type', $account->account_type ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
            @if(isset($account) && $account->is_system)
                <input type="hidden" name="account_type" value="{{ $account->account_type }}">
                <div class="form-text">System account type cannot be changed.</div>
            @endif
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Parent Account</label>
            <select name="parent_id" class="form-select">
                <option value="">None</option>
                @foreach($parents as $p)
                    <option value="{{ $p->id }}" {{ old('parent_id', $account->parent_id ?? '') == $p->id ? 'selected' : '' }}>
                        {{ $p->account_code }} — {{ $p->account_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
            <label class="form-label small fw-semibold">Description</label>
            <textarea name="description" rows="2" class="form-control">{{ old('description', $account->description ?? '') }}</textarea>
        </div>

        @if(isset($account))
        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                       id="accountIsActive" {{ old('is_active', $account->is_active) ? 'checked' : '' }}>
                <label class="form-check-label small fw-semibold" for="accountIsActive">
                    Active (uncheck to deactivate without deleting)
                </label>
            </div>
        </div>
        @if($account->is_system)
        <div class="col-12">
            <div class="alert alert-info py-2 px-3 small mb-0">
                <i class="bi bi-info-circle me-1"></i> This is a system account. Code and type are locked, but you can rename it, change its parent, or deactivate it.
            </div>
        </div>
        @endif
        @endif
    </div>
</form>
