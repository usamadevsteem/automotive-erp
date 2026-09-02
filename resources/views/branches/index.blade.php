@extends('layouts.app')
@section('title','Branches')
@section('breadcrumb','Admin / Branches')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Branches</h4>
    <a href="{{ route('branches.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i> Add Branch</a>
</div>
<div class="row g-3">
    @forelse($branches as $branch)
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <h6 class="fw-bold mb-0">{{ $branch->name }}</h6>
                        <small class="text-muted">Code: <strong>{{ $branch->code }}</strong></small>
                    </div>
                    <div class="d-flex gap-1">
                        @if($branch->is_main)
                            <span class="badge bg-primary">Main</span>
                        @endif
                        <span class="badge bg-{{ $branch->is_active ? 'success' : 'danger' }}-subtle text-{{ $branch->is_active ? 'success' : 'danger' }}">
                            {{ $branch->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                @if($branch->address)<p class="text-muted small mb-1"><i class="bi bi-geo-alt me-1"></i>{{ $branch->address }}{{ $branch->city ? ', '.$branch->city : '' }}</p>@endif
                @if($branch->phone)<p class="text-muted small mb-1"><i class="bi bi-telephone me-1"></i>{{ $branch->phone }}</p>@endif
                @if($branch->email)<p class="text-muted small mb-0"><i class="bi bi-envelope me-1"></i>{{ $branch->email }}</p>@endif
                <div class="d-flex align-items-center justify-content-between mt-3 pt-3 border-top">
                    <small class="text-muted"><i class="bi bi-people me-1"></i>{{ $branch->users_count }} users</small>
                    <div class="d-flex gap-1">
                        <a href="{{ route('branches.edit', $branch) }}" class="btn btn-light btn-sm"><i class="bi bi-pencil"></i></a>
                        @if(!$branch->is_main)
                        <form method="POST" action="{{ route('branches.destroy', $branch) }}" onsubmit="return confirm('Delete this branch?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5 text-muted">
        <i class="bi bi-building fs-1 opacity-25 d-block mb-2"></i>
        <p>No branches found. Add your first branch.</p>
    </div>
    @endforelse
</div>
@endsection
