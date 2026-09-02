@extends('layouts.app')
@section('title','Quick Replies')
@section('breadcrumb','WhatsApp / Quick Replies')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Quick Replies</h4>
    <a href="{{ route('whatsapp.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<div class="row g-4">
<div class="col-lg-5">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3"><h6 class="mb-0 fw-semibold">Add Quick Reply</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('whatsapp.quick-replies.store') }}">
            @csrf
            <div class="mb-2"><input type="text" name="title" class="form-control form-control-sm" placeholder="Title" required></div>
            <div class="mb-2"><textarea name="body" rows="3" class="form-control form-control-sm" placeholder="Message text" required></textarea></div>
            <div class="mb-2"><input type="text" name="category" class="form-control form-control-sm" placeholder="Category (optional)"></div>
            <button class="btn btn-primary btn-sm w-100">Save</button>
        </form>
    </div>
</div>
</div>
<div class="col-lg-7">
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th class="ps-3">Title</th><th>Body</th><th>Used</th></tr></thead>
            <tbody>
                @foreach($replies as $r)
                <tr><td class="ps-3">{{ $r->title }}</td><td class="small text-muted">{{ Str::limit($r->body,50) }}</td><td>{{ $r->usage_count }}x</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</div>
</div>
@endsection
