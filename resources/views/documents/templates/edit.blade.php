@extends('layouts.app')
@section('title','Edit Template')
@section('breadcrumb','Documents / Templates / Edit')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Edit Template — {{ $documentTemplate->name }}</h4>
    <a href="{{ route('document-templates.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<form method="POST" action="{{ route('document-templates.update', $documentTemplate) }}">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label small fw-semibold required">Name</label>
                            <input type="text" name="name" value="{{ old('name', $documentTemplate->name) }}" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label small fw-semibold">Type</label>
                            <input type="text" value="{{ \App\Models\DocumentTemplate::DOCUMENT_TYPES[$documentTemplate->document_type] }}" class="form-control" disabled></div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold required">HTML Body</label>
                            <textarea name="html_body" rows="16" class="form-control font-monospace small" required>{{ old('html_body', $documentTemplate->html_body) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3"><label class="form-label small fw-semibold">Page Size</label>
                        <select name="page_size" class="form-select form-select-sm">
                            @foreach(['A4','A5','Letter'] as $size)<option {{ $documentTemplate->page_size===$size?'selected':'' }}>{{ $size }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label small fw-semibold">Orientation</label>
                        <select name="orientation" class="form-select form-select-sm">
                            @foreach(['portrait','landscape'] as $o)<option {{ $documentTemplate->orientation===$o?'selected':'' }}>{{ $o }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-check mb-2"><input type="checkbox" name="show_logo" value="1" class="form-check-input" {{ $documentTemplate->show_logo?'checked':'' }}><label class="form-check-label small">Show Logo</label></div>
                    <div class="form-check mb-2"><input type="checkbox" name="show_qr" value="1" class="form-check-input" {{ $documentTemplate->show_qr?'checked':'' }}><label class="form-check-label small">Show QR Code</label></div>
                    <div class="form-check mb-3"><input type="checkbox" name="is_default" value="1" class="form-check-input" {{ $documentTemplate->is_default?'checked':'' }}><label class="form-check-label small">Default Template</label></div>
                    <button class="btn btn-primary btn-sm w-100">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
