@extends('layouts.app')
@section('title', $invoice->invoice_number)
@section('breadcrumb', 'Sales / Invoices / ' . $invoice->invoice_number)

@section('content')
<div class="d-flex align-items-start justify-content-between mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="fw-bold mb-0">{{ $invoice->invoice_number }}</h4>
            <span class="badge bg-{{ $invoice->status_color }}-subtle text-{{ $invoice->status_color }} border border-{{ $invoice->status_color }}-subtle">
                {{ $invoice->status_label }}
            </span>
        </div>
        <small class="text-muted">
            {{ $invoice->invoice_date->format('d M Y') }} · {{ $invoice->branch->name }} · by {{ $invoice->createdBy->name }}
        </small>
    </div>
    <div class="d-flex gap-2">
        @if(!$invoice->delivery && !$invoice->isCancelled())
        <a href="{{ route('deliveries.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success btn-sm">
            <i class="bi bi-truck me-1"></i> Create Delivery
        </a>
        @endif
        <a href="{{ route('invoices.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Invoice Summary --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted small text-uppercase mb-2">Customer</h6>
                        <a href="{{ route('customers.show', $invoice->customer) }}" class="fw-semibold text-dark text-decoration-none">
                            {{ $invoice->customer->full_name }}
                        </a>
                        <div class="text-muted small">{{ $invoice->customer->mobile }}</div>
                        @if($invoice->customer->cnic)
                        <div class="text-muted small font-monospace">{{ $invoice->customer->cnic }}</div>
                        @endif
                        <span class="badge bg-{{ $invoice->customer->tax_status === 'filer' ? 'success' : 'secondary' }}-subtle text-{{ $invoice->customer->tax_status === 'filer' ? 'success' : 'secondary' }} small">
                            {{ ucfirst($invoice->customer->tax_status) }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted small text-uppercase mb-2">Vehicle</h6>
                        <a href="{{ route('vehicles.show', $invoice->vehicle) }}" class="fw-semibold text-dark text-decoration-none">
                            {{ $invoice->vehicle->make->name }} {{ $invoice->vehicle->vehicleModel->name }} {{ $invoice->vehicle->year }}
                        </a>
                        <div class="text-muted small">Stock# {{ $invoice->vehicle->stock_number }}</div>
                        @if($invoice->vehicle->registration_number)
                        <div class="text-muted small font-monospace">{{ $invoice->vehicle->registration_number }}</div>
                        @endif
                    </div>
                </div>

                <table class="table table-sm">
                    <tr><td class="text-muted">Sale Price</td><td class="text-end">PKR {{ number_format($invoice->sale_price) }}</td></tr>
                    @if($invoice->discount > 0)
                    <tr><td class="text-muted">Discount</td><td class="text-end text-danger">- PKR {{ number_format($invoice->discount) }}</td></tr>
                    @endif
                    @if($invoice->withholding_tax > 0)
                    <tr><td class="text-muted">Withholding Tax</td><td class="text-end text-warning">- PKR {{ number_format($invoice->withholding_tax) }}</td></tr>
                    @endif
                    <tr class="table-light fw-bold fs-6">
                        <td>Net Amount</td>
                        <td class="text-end text-primary">PKR {{ number_format($invoice->net_amount) }}</td>
                    </tr>
                    <tr><td class="text-muted">Amount Paid</td><td class="text-end text-success">PKR {{ number_format($invoice->amount_paid) }}</td></tr>
                    <tr class="{{ $invoice->balance_due > 0 ? 'table-danger' : 'table-success' }}">
                        <td class="fw-bold">Balance Due</td>
                        <td class="text-end fw-bold">PKR {{ number_format($invoice->balance_due) }}</td>
                    </tr>
                    <tr><td class="text-muted">Payment Type</td><td class="text-end">{{ \App\Models\SaleInvoice::PAYMENT_TYPES[$invoice->payment_type] }}</td></tr>
                </table>

                @if($invoice->notes)
                <div class="alert alert-light border py-2 small"><i class="bi bi-sticky me-1"></i>{{ $invoice->notes }}</div>
                @endif
            </div>
        </div>

      

        {{-- Commissions --}}
        @can('view-commissions')
        @if($invoice->commissions->count())
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-semibold">Commissions</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th class="ps-3">Type</th><th>Employee</th><th>Amount</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->commissions as $comm)
                        <tr>
                            <td class="ps-3">{{ \App\Models\Commission::TYPES[$comm->commission_type] }}</td>
                            <td>{{ $comm->employee?->name ?? $comm->referrer_name ?? '—' }}</td>
                            <td class="fw-semibold">{{ $comm->amount_formatted }}</td>
                            <td><span class="badge bg-{{ $comm->status_color }}-subtle text-{{ $comm->status_color }}">{{ $comm->status_label }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        @endcan
    </div>

    {{-- Right panel --}}
    <div class="col-lg-4">
        {{-- Record Payment --}}
        @if($invoice->balance_due > 0 && !$invoice->isCancelled())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-semibold">Record Payment</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('invoices.payment', $invoice) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Amount (max: PKR {{ number_format($invoice->balance_due) }})</label>
                        <input type="number" name="amount" class="form-control form-control-sm"
                               max="{{ $invoice->balance_due }}" min="1" required>
                    </div>
                    <div class="mb-2">
                        <select name="payment_method" class="form-select form-select-sm" required>
                            @foreach(\App\Models\Payment::PAYMENT_METHODS as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="date" name="payment_date" value="{{ today()->toDateString() }}"
                               class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="reference_number" class="form-control form-control-sm"
                               placeholder="Ref # (optional)">
                    </div>
                    <button type="submit" class="btn btn-success btn-sm w-100">
                        <i class="bi bi-check2 me-1"></i> Record Payment
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Documents --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-semibold">Generate Documents</h6></div>
            <div class="card-body d-grid gap-2">
                @foreach(['sale_invoice' => 'Sale Invoice', 'affidavit' => 'Affidavit', 'transfer_letter' => 'Transfer Letter', 'sale_agreement' => 'Sale Agreement', 'payment_receipt' => 'Payment Receipt', 'delivery_order' => 'Delivery Order'] as $type => $label)
                <form method="POST" action="{{ route('documents.generate') }}">
                    @csrf
                    <input type="hidden" name="document_type" value="{{ $type }}">
                    <input type="hidden" name="reference_type" value="sale_invoice">
                    <input type="hidden" name="reference_id" value="{{ $invoice->id }}">
                    <input type="hidden" name="customer_id" value="{{ $invoice->customer_id }}">
                    <input type="hidden" name="vehicle_id" value="{{ $invoice->vehicle_id }}">
                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100 text-start">
                        <i class="bi bi-file-pdf me-2 text-danger"></i>{{ $label }}
                    </button>
                </form>
                @endforeach
            </div>
        </div>

        {{-- Deal File --}}
        @if($invoice->dealFile)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between">
                <h6 class="mb-0 fw-semibold">Deal File</h6>
                <span class="badge bg-{{ $invoice->dealFile->status === 'complete' ? 'success' : 'warning' }}-subtle text-{{ $invoice->dealFile->status === 'complete' ? 'success' : 'warning' }}">
                    {{ $invoice->dealFile->completeness }}% complete
                </span>
            </div>
            <div class="card-body">
                <div class="progress mb-2" style="height:6px;">
                    <div class="progress-bar {{ $invoice->dealFile->completeness === 100 ? 'bg-success' : 'bg-warning' }}"
                         style="width:{{ $invoice->dealFile->completeness }}%"></div>
                </div>
                <small class="text-muted">{{ $invoice->dealFile->deal_number }}</small>
            </div>
        </div>
        @endif

        {{-- Cancel --}}
        @if(!$invoice->isCancelled() && $invoice->status !== 'paid')
        <div class="card border-0 shadow-sm border-danger-subtle">
            <div class="card-body">
                <form method="POST" action="{{ route('invoices.cancel', $invoice) }}"
                      onsubmit="return confirm('Cancel invoice and release vehicle?')">
                    @csrf
                    <input type="text" name="reason" class="form-control form-control-sm mb-2"
                           placeholder="Cancellation reason" required>
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                        <i class="bi bi-x-circle me-1"></i> Cancel Invoice
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
