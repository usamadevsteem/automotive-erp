@extends('layouts.app')
@section('title','New Journal Entry')
@section('breadcrumb','Accounting / Journal Entries / New')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">New Journal Entry</h4>
    <a href="{{ route('journal-entries.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<form method="POST" action="{{ route('journal-entries.store') }}">
    @csrf
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-4"><label class="form-label small fw-semibold required">Date</label><input type="date" name="entry_date" value="{{ today()->toDateString() }}" class="form-control" required></div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold required">Type</label>
                    <select name="entry_type" class="form-select" required>
                        @foreach(['adjustment'=>'Adjustment','opening'=>'Opening Balance','receipt'=>'Receipt','payment'=>'Payment'] as $k=>$v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label small fw-semibold required">Narration</label><input type="text" name="narration" class="form-control" required></div>
            </div>

            <table class="table table-sm" id="linesTable">
                <thead><tr><th>Account</th><th style="width:150px">Debit</th><th style="width:150px">Credit</th><th style="width:200px">Description</th><th></th></tr></thead>
                <tbody>
                    @for($i=0;$i<2;$i++)
                    <tr>
                        <td>
                            <select name="lines[{{ $i }}][account_id]" class="form-select form-select-sm" required>
                                <option value="">Select</option>
                                @foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->account_code }} — {{ $a->account_name }}</option>@endforeach
                            </select>
                        </td>
                        <td><input type="number" name="lines[{{ $i }}][debit_amount]" class="form-control form-control-sm debit-input" step="0.01" min="0"></td>
                        <td><input type="number" name="lines[{{ $i }}][credit_amount]" class="form-control form-control-sm credit-input" step="0.01" min="0"></td>
                        <td><input type="text" name="lines[{{ $i }}][description]" class="form-control form-control-sm"></td>
                        <td></td>
                    </tr>
                    @endfor
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td>Total</td>
                        <td id="totalDebit">0.00</td>
                        <td id="totalCredit">0.00</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
            <button type="button" id="addLineBtn" class="btn btn-light btn-sm"><i class="bi bi-plus"></i> Add Line</button>
        </div>
        <div class="card-footer bg-white d-flex gap-2">
            <button type="submit" class="btn btn-primary">Post Journal Entry</button>
            <a href="{{ route('journal-entries.index') }}" class="btn btn-light">Cancel</a>
        </div>
    </div>
</form>
@endsection
@push('scripts')
<script>
let lineIndex = 2;
document.getElementById('addLineBtn').addEventListener('click', function() {
    const tbody = document.querySelector('#linesTable tbody');
    const row = document.createElement('tr');
    row.innerHTML = `<td><select name="lines[${lineIndex}][account_id]" class="form-select form-select-sm">
        <option value="">Select</option>@foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->account_code }} — {{ $a->account_name }}</option>@endforeach
        </select></td>
        <td><input type="number" name="lines[${lineIndex}][debit_amount]" class="form-control form-control-sm debit-input" step="0.01" min="0"></td>
        <td><input type="number" name="lines[${lineIndex}][credit_amount]" class="form-control form-control-sm credit-input" step="0.01" min="0"></td>
        <td><input type="text" name="lines[${lineIndex}][description]" class="form-control form-control-sm"></td>
        <td><button type="button" class="btn btn-link btn-sm text-danger remove-line">×</button></td>`;
    tbody.appendChild(row);
    lineIndex++;
    attachListeners();
});

function recalcTotals() {
    let debit = 0, credit = 0;
    document.querySelectorAll('.debit-input').forEach(el => debit += parseFloat(el.value) || 0);
    document.querySelectorAll('.credit-input').forEach(el => credit += parseFloat(el.value) || 0);
    document.getElementById('totalDebit').textContent = debit.toFixed(2);
    document.getElementById('totalCredit').textContent = credit.toFixed(2);
}

function attachListeners() {
    document.querySelectorAll('.debit-input, .credit-input').forEach(el => {
        el.removeEventListener('input', recalcTotals);
        el.addEventListener('input', recalcTotals);
    });
    document.querySelectorAll('.remove-line').forEach(btn => {
        btn.addEventListener('click', function() { this.closest('tr').remove(); recalcTotals(); });
    });
}
attachListeners();
</script>
@endpush
