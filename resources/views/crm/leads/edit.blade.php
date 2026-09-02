@extends('layouts.app')
@section('title', isset($lead) ? 'Edit Lead' : 'Add Lead')
@section('breadcrumb', 'CRM / Leads / ' . (isset($lead) ? 'Edit' : 'New'))

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">{{ isset($lead) ? 'Edit Lead' : 'New Lead' }}</h4>
    <a href="{{ route('leads.index') }}" class="btn btn-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>

@include('crm.leads.partials.form')

<div class="d-flex justify-content-end gap-2 mt-3 mb-5">
    <a href="{{ route('leads.index') }}" class="btn btn-light">Cancel</a>
    <button type="submit" form="leadForm" class="btn btn-primary">
        <i class="bi bi-check2 me-1"></i>
        {{ isset($lead) ? 'Save Changes' : 'Create Lead' }}
    </button>
</div>
@endsection
