@extends('layouts.app')
@section('title','WhatsApp CRM')
@section('breadcrumb','WhatsApp CRM')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0">WhatsApp Shared Inbox</h4>
        @if($totalUnread > 0)<small class="text-danger fw-semibold">{{ $totalUnread }} unread conversations</small>@endif
    </div>
    <a href="{{ route('whatsapp.settings') }}" class="btn btn-light btn-sm"><i class="bi bi-gear me-1"></i> Settings</a>
</div>

@if($accounts->isEmpty())
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    No WhatsApp account connected. <a href="{{ route('whatsapp.settings') }}">Configure WhatsApp Business API</a> to start receiving messages.
</div>
@endif

{{-- Filter tabs --}}
<ul class="nav nav-pills mb-3 gap-1">
    @foreach(['' => 'All', 'open' => 'Open', 'assigned' => 'Assigned', 'resolved' => 'Resolved'] as $k => $label)
    <li class="nav-item">
        <a href="{{ route('whatsapp.index', ['status' => $k]) }}"
           class="nav-link py-1 px-3 {{ request('status','') === $k ? 'active' : 'text-muted' }}">{{ $label }}</a>
    </li>
    @endforeach
</ul>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @forelse($conversations as $conv)
        <a href="{{ route('whatsapp.conversation', $conv) }}"
           class="d-flex align-items-center gap-3 p-3 border-bottom text-decoration-none text-dark {{ $conv->unread_count > 0 ? 'bg-light' : '' }}">
            <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:44px;height:44px;">
                <i class="bi bi-person-fill text-success"></i>
            </div>
            <div class="flex-grow-1" style="min-width:0;">
                <div class="d-flex justify-content-between">
                    <span class="fw-semibold">{{ $conv->customer?->full_name ?? $conv->customer_name ?? $conv->customer_phone }}</span>
                    <small class="text-muted">{{ $conv->last_message_at?->diffForHumans() }}</small>
                </div>
                <div class="text-muted small text-truncate">{{ $conv->last_message_preview }}</div>
            </div>
            <div class="d-flex flex-column align-items-end gap-1">
                @if($conv->unread_count > 0)
                    <span class="badge bg-success rounded-pill">{{ $conv->unread_count }}</span>
                @endif
                <span class="badge bg-{{ $conv->status_color }}-subtle text-{{ $conv->status_color }} small">{{ $conv->status_label }}</span>
                @if($conv->assignedTo)<small class="text-muted">{{ $conv->assignedTo->name }}</small>@endif
            </div>
        </a>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-whatsapp fs-1 opacity-25 d-block mb-2"></i>
            <p>No conversations yet.</p>
        </div>
        @endforelse
    </div>
    @if($conversations->hasPages())
    <div class="px-4 py-3 border-top">{{ $conversations->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
