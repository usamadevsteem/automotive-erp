@extends('layouts.app')
@section('title','New Template')
@section('breadcrumb','Documents / Templates / New')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">New Document Template</h4>
    <a href="{{ route('document-templates.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<form method="POST" action="{{ route('document-templates.store') }}">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label small fw-semibold required">Name</label><input type="text" name="name" class="form-control" required></div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold required">Document Type</label>
                            <select name="document_type" class="form-select" required>
                                @foreach(\App\Models\DocumentTemplate::DOCUMENT_TYPES as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold required">HTML Body</label>
                            <textarea name="html_body" rows="14" class="form-control font-monospace small" required placeholder="Use {{variable_name}} for dynamic fields"></textarea>
                            <div class="form-text">
                                Available: {{customer_name}}, {{customer_cnic}}, {{customer_address}}, {{vehicle_make}}, {{vehicle_model}}, {{chassis_number}}, {{engine_number}}, {{registration_number}}, {{sale_price}}, {{net_amount}}, {{amount_in_words}}, {{document_date}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3"><label class="form-label small fw-semibold">Page Size</label>
                        <select name="page_size" class="form-select form-select-sm"><option>A4</option><option>A5</option><option>Letter</option></select>
                    </div>
                    <div class="mb-3"><label class="form-label small fw-semibold">Orientation</label>
                        <select name="orientation" class="form-select form-select-sm"><option>portrait</option><option>landscape</option></select>
                    </div>
                    <div class="form-check mb-2"><input type="checkbox" name="show_logo" value="1" class="form-check-input" checked><label class="form-check-label small">Show Logo</label></div>
                    <div class="form-check mb-2"><input type="checkbox" name="show_qr" value="1" class="form-check-input"><label class="form-check-label small">Show QR Code</label></div>
                    <div class="form-check mb-3"><input type="checkbox" name="is_default" value="1" class="form-check-input" checked><label class="form-check-label small">Set as Default</label></div>
                    <button class="btn btn-primary btn-sm w-100">Create Template</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
