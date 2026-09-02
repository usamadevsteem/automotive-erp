@extends('layouts.app')
@section('title','Conversation')
@section('breadcrumb','WhatsApp CRM / Conversation')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="fw-bold mb-0">{{ $conversation->customer?->full_name ?? $conversation->customer_name ?? $conversation->customer_phone }}</h5>
        <small class="text-muted">{{ $conversation->customer_phone }}</small>
    </div>
    <div class="d-flex gap-2">
        @if(!$conversation->lead_id)
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#leadModal">
            <i class="bi bi-funnel me-1"></i> Create Lead
        </button>
        @else
        <a href="{{ route('leads.show', $conversation->lead_id) }}" class="btn btn-outline-info btn-sm">
            <i class="bi bi-funnel me-1"></i> View Lead
        </a>
        @endif
        @if($conversation->customer_id)
        <a href="{{ route('customers.show', $conversation->customer_id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-person me-1"></i> Customer Profile
        </a>
        @endif
        <form method="POST" action="{{ route('whatsapp.resolve', $conversation) }}">
            @csrf
            <button class="btn btn-success btn-sm"><i class="bi bi-check2 me-1"></i> Resolve</button>
        </form>
        <a href="{{ route('whatsapp.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-body" style="height:500px; overflow-y:auto; background:#e5ddd5;" id="chatBox">
                @foreach($conversation->messages as $msg)
                <div class="d-flex mb-2 {{ $msg->isOutbound() ? 'justify-content-end' : 'justify-content-start' }}">
                    <div class="p-2 rounded-3 shadow-sm" style="max-width:70%; background:{{ $msg->isOutbound() ? '#dcf8c6' : '#fff' }};">
                        <div class="small">{{ $msg->content }}</div>
                        <div class="text-end" style="font-size:10px;" class="text-muted">
                            {{ $msg->sent_at->format('H:i') }}
                            @if($msg->isOutbound() && $msg->sentBy) · {{ $msg->sentBy->name }} @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="card-footer bg-white">
                <form id="sendForm" class="d-flex gap-2">
                    @csrf
                    <input type="text" id="messageInput" class="form-control" placeholder="Type a message...">
                    <button type="submit" class="btn btn-success"><i class="bi bi-send"></i></button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2"><h6 class="mb-0 fw-semibold small">Assign</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('whatsapp.assign', $conversation) }}">
                    @csrf
                    <select name="assigned_to" class="form-select form-select-sm mb-2" required>
                        <option value="">Select user</option>
                        @foreach(\App\Models\User::active()->get() as $u)
                            <option value="{{ $u->id }}" {{ $conversation->assigned_to == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary btn-sm w-100">Assign</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2"><h6 class="mb-0 fw-semibold small">Quick Replies</h6></div>
            <div class="card-body p-2" style="max-height:300px; overflow-y:auto;">
                @forelse($quickReplies as $qr)
                <button class="btn btn-light btn-sm w-100 text-start mb-1 quick-reply" data-text="{{ $qr->body }}">
                    {{ $qr->title }}
                </button>
                @empty
                <small class="text-muted">No quick replies yet.</small>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Create Lead Modal --}}
<div class="modal fade" id="leadModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('whatsapp.create-lead', $conversation) }}">
                @csrf
                <div class="modal-header"><h6 class="modal-title">Create Lead from Conversation</h6></div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small">Full Name</label>
                        <input type="text" name="full_name" value="{{ $conversation->customer_name }}" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Vehicle Interest</label>
                        <input type="text" name="vehicle_interest" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-sm">Create Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const chatBox = document.getElementById('chatBox');
chatBox.scrollTop = chatBox.scrollHeight;

document.getElementById('sendForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const input = document.getElementById('messageInput');
    const text  = input.value.trim();
    if (!text) return;

    fetch('{{ route("whatsapp.send", $conversation) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ message: text }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            chatBox.insertAdjacentHTML('beforeend', `
                <div class="d-flex mb-2 justify-content-end">
                    <div class="p-2 rounded-3 shadow-sm" style="max-width:70%; background:#dcf8c6;">
                        <div class="small">${data.message.content}</div>
                        <div class="text-end text-muted" style="font-size:10px;">${data.message.sent_at} · ${data.message.sent_by}</div>
                    </div>
                </div>`);
            chatBox.scrollTop = chatBox.scrollHeight;
            input.value = '';
        }
    });
});

document.querySelectorAll('.quick-reply').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('messageInput').value = this.dataset.text;
    });
});
</script>
@endpush
