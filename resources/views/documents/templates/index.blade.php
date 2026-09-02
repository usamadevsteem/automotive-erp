@extends('layouts.app')
@section('title','Document Templates')
@section('breadcrumb','Documents / Templates')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Document Templates</h4>
    <a href="{{ route('document-templates.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i> New Template</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th class="ps-4">Name</th><th>Type</th><th>Default</th><th>Status</th><th class="pe-4 text-end">Actions</th></tr></thead>
            <tbody>
                @forelse($templates as $t)
                <tr>
                    <td class="ps-4 fw-semibold">{{ $t->name }}</td>
                    <td><span class="badge bg-light text-dark">{{ \App\Models\DocumentTemplate::DOCUMENT_TYPES[$t->document_type] ?? $t->document_type }}</span></td>
                    <td>@if($t->is_default)<span class="badge bg-success">Default</span>@endif</td>
                    <td><span class="badge bg-{{ $t->is_active ? 'success' : 'secondary' }}-subtle text-{{ $t->is_active ? 'success' : 'secondary' }}">{{ $t->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td class="pe-4 text-end"><a href="{{ route('document-templates.edit', $t) }}" class="btn btn-light btn-sm"><i class="bi bi-pencil"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-5 text-muted">No templates found. Run DocumentTemplateSeeder to load 18 default templates.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
