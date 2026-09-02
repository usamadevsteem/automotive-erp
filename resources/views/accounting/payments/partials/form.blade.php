<form id="paymentForm"
      action="{{ isset($payment) ? route('payments.update', $payment) : route('payments.store') }}">
    @csrf
    @if(isset($payment)) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label small fw-semibold required">Type</label>
            <select name="type" class="form-select" required>
                <option value="received" {{ old('type', $payment->type ?? '') === 'received' ? 'selected' : '' }}>Received</option>
                <option value="paid" {{ old('type', $payment->type ?? '') === 'paid' ? 'selected' : '' }}>Paid</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold required">Party Type</label>
            <select name="party_type" class="form-select vf-pay-party-type" required>
                <option value="customer" {{ old('party_type', $payment->party_type ?? '') === 'customer' ? 'selected' : '' }}>Customer</option>
                <option value="vendor" {{ old('party_type', $payment->party_type ?? '') === 'vendor' ? 'selected' : '' }}>Vendor</option>
                <option value="employee" {{ old('party_type', $payment->party_type ?? '') === 'employee' ? 'selected' : '' }}>Employee</option>
                <option value="other" {{ old('party_type', $payment->party_type ?? '') === 'other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>
        <div class="col-12 vf-pay-party-select-wrap">
            <label class="form-label small fw-semibold required">Party</label>
            <select name="party_id" class="form-select vf-pay-party-customer">
                <option value="">Select Customer</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ old('party_id', $payment->party_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->full_name }}</option>
                @endforeach
            </select>
            <select name="party_id" class="form-select vf-pay-party-vendor" style="display:none;">
                <option value="">Select Vendor</option>
                @foreach($vendors as $v)
                    <option value="{{ $v->id }}" {{ old('party_id', $payment->party_id ?? '') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                @endforeach
            </select>
            <select name="party_id" class="form-select vf-pay-party-employee" style="display:none;">
                <option value="">Select Employee</option>
                @foreach($employees as $e)
                    <option value="{{ $e->id }}" {{ old('party_id', $payment->party_id ?? '') == $e->id ? 'selected' : '' }}>{{ $e->name }}</option>
                @endforeach
            </select>
            <input type="number" name="party_id" class="form-control vf-pay-party-other" style="display:none;"
                   placeholder="Reference ID" value="{{ old('party_id', $payment->party_id ?? '') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold required">Amount (PKR)</label>
            <input type="number" name="amount" class="form-control" required min="0.01"
                   value="{{ old('amount', $payment->amount ?? '') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold required">Method</label>
            <select name="payment_method" class="form-select" required>
                @foreach(\App\Models\Payment::PAYMENT_METHODS as $k=>$v)
                    <option value="{{ $k }}" {{ old('payment_method', $payment->payment_method ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold required">Date</label>
            <input type="date" name="payment_date" class="form-control" required
                   value="{{ old('payment_date', isset($payment) ? $payment->payment_date->toDateString() : today()->toDateString()) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Reference #</label>
            <input type="text" name="reference_number" class="form-control"
                   value="{{ old('reference_number', $payment->reference_number ?? '') }}">
        </div>
        <div class="col-12">
            <label class="form-label small fw-semibold">Notes</label>
            <textarea name="notes" rows="2" class="form-control">{{ old('notes', $payment->notes ?? '') }}</textarea>
        </div>
    </div>
</form>
