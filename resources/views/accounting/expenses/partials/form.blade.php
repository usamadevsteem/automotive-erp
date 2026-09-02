<form id="expenseForm"
      action="{{ isset($expense) ? route('expenses.update', $expense) : route('expenses.store') }}">
    @csrf
    @if(isset($expense)) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label small fw-semibold required">Category</label>
            <select name="category_id" class="form-select" required>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" {{ old('category_id', $expense->category_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold required">Amount (PKR)</label>
            <input type="number" name="amount" class="form-control" required min="0.01"
                   value="{{ old('amount', $expense->amount ?? '') }}">
        </div>
        <div class="col-12">
            <label class="form-label small fw-semibold required">Description</label>
            <input type="text" name="description" class="form-control" required
                   value="{{ old('description', $expense->description ?? '') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold required">Payment Method</label>
            <select name="payment_method" class="form-select" required>
                @foreach(\App\Models\Expense::PAYMENT_METHODS as $k=>$v)
                    <option value="{{ $k }}" {{ old('payment_method', $expense->payment_method ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold required">Date</label>
            <input type="date" name="expense_date" class="form-control" required
                   value="{{ old('expense_date', isset($expense) ? $expense->expense_date->toDateString() : today()->toDateString()) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Vendor (optional)</label>
            <select name="vendor_id" class="form-select">
                <option value="">None</option>
                @foreach($vendors as $v)
                    <option value="{{ $v->id }}" {{ old('vendor_id', $expense->vendor_id ?? '') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Reference #</label>
            <input type="text" name="reference_number" class="form-control"
                   value="{{ old('reference_number', $expense->reference_number ?? '') }}">
        </div>
    </div>
</form>
