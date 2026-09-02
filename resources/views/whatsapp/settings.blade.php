@extends('layouts.app')
@section('title','WhatsApp Settings')
@section('breadcrumb','WhatsApp / Settings')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">WhatsApp Settings</h4>
    <a href="{{ route('whatsapp.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>
<div class="row justify-content-center"><div class="col-lg-6">
<form method="POST" action="{{ route('whatsapp.settings.save') }}">
    @csrf
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label small fw-semibold required">WhatsApp Business Phone Number</label>
                <input type="text" name="phone_number" value="{{ $account->phone_number ?? '' }}" class="form-control" placeholder="+923001234567" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Display Name</label>
                <input type="text" name="display_name" value="{{ $account->display_name ?? '' }}" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold required">Provider</label>
                <select name="provider" class="form-select" required>
                    <option value="meta_cloud_api" {{ ($account->provider ?? '') === 'meta_cloud_api' ? 'selected' : '' }}>Meta Cloud API</option>
                    <option value="twilio" {{ ($account->provider ?? '') === 'twilio' ? 'selected' : '' }}>Twilio</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">API Key / Access Token</label>
                <input type="password" name="api_key" class="form-control" placeholder="••••••••">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Webhook Verify Token</label>
                <input type="text" name="webhook_token" value="{{ $account->webhook_token ?? '' }}" class="form-control">
                <div class="form-text">Webhook URL: {{ route('whatsapp.webhook') }}</div>
            </div>
        </div>
        <div class="card-footer bg-white">
            <button class="btn btn-primary">Save Settings</button>
        </div>
    </div>
</form>
</div></div>
@endsection
