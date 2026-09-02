<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Document Verification</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"></head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
<div class="card shadow-sm" style="max-width:480px;width:100%;">
<div class="card-body text-center p-5">
    @if($doc->is_voided)
        <i class="bi bi-x-circle-fill text-danger" style="font-size:3rem;"></i>
        <h5 class="fw-bold mt-3">Document Voided</h5>
    @else
        <i class="bi bi-patch-check-fill text-success" style="font-size:3rem;"></i>
        <h5 class="fw-bold mt-3">Document Verified</h5>
    @endif
    <p class="text-muted">{{ $doc->document_number }}</p>
    <table class="table table-sm text-start mt-3">
        <tr><td class="text-muted">Type</td><td>{{ $doc->type_label }}</td></tr>
        @if($doc->customer)<tr><td class="text-muted">Customer</td><td>{{ $doc->customer->full_name }}</td></tr>@endif
        @if($doc->vehicle)<tr><td class="text-muted">Vehicle</td><td>{{ $doc->vehicle->make->name }} {{ $doc->vehicle->vehicleModel->name }}</td></tr>@endif
        <tr><td class="text-muted">Generated</td><td>{{ $doc->generated_at->format('d M Y') }}</td></tr>
        <tr><td class="text-muted">Dealer</td><td>{{ $doc->generatedBy->branch->tenant->company_name ?? '' }}</td></tr>
    </table>
</div></div>
</body></html>
