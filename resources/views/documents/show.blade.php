@extends('layouts.app')

@section('title', $document->document_number)
@section('breadcrumb', 'Documents / ' . $document->document_number)

@push('styles')
<style>
    /*
    |--------------------------------------------------------------------------
    | Document Detail / Preview Page
    |--------------------------------------------------------------------------
    |
    | This view can be opened directly or inside the Document Preview iframe.
    | The normal ERP navigation is intentionally hidden here because the
    | document itself is the focus of the page.
    |
    */

    #sidebar,
    #topbar,
    .sidebar-overlay {
        display: none !important;
    }

    #main {
        margin-left: 0 !important;
        width: 100% !important;
        min-height: 100vh;
    }

    #content {
        padding: 24px !important;
        min-height: 100vh;
    }

    .document-page {
        max-width: 1180px;
        margin: 0 auto;
    }

    .document-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }

    .document-title {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        color: #0f172a;
    }

    .document-meta {
        margin-top: 5px;
        color: #64748b;
        font-size: 13px;
    }

    .document-status {
        flex-shrink: 0;
    }

    .document-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .document-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        background: #fff;
    }

    .document-card-title {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
    }

    .document-card-body {
        padding: 20px;
    }

    .document-info-label {
        margin-bottom: 4px;
        color: #64748b;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .7px;
        text-transform: uppercase;
    }

    .document-info-value {
        color: #0f172a;
        font-size: 14px;
        font-weight: 600;
    }

    .pdf-placeholder {
        min-height: 260px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        text-align: center;
    }

    .pdf-placeholder-icon {
        color: #dc2626;
        font-size: 52px;
        line-height: 1;
        margin-bottom: 12px;
    }

    .pdf-placeholder-title {
        color: #334155;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 3px;
    }

    .pdf-placeholder-text {
        color: #94a3b8;
        font-size: 12px;
        margin: 0;
    }

    .action-list {
        display: grid;
        gap: 9px;
    }

    .action-list .btn {
        min-height: 38px;
        font-weight: 600;
    }

    .verification-code {
        display: inline-block;
        padding: 7px 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 7px;
        color: #0f172a;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .4px;
    }

    .verification-url {
        display: block;
        margin-top: 10px;
        color: #2563eb;
        font-size: 12px;
        line-height: 1.5;
        word-break: break-all;
    }

    .void-alert {
        margin-top: 18px;
        margin-bottom: 0;
    }

    @media (max-width: 768px) {
        #content {
            padding: 16px !important;
        }

        .document-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .document-title {
            font-size: 19px;
        }
    }
</style>
@endpush

@section('content')

<div class="document-page">

    {{-- Document Header --}}
    <div class="document-header">
        <div>
            <h1 class="document-title">
                {{ $document->type_label }}
            </h1>

            <div class="document-meta">
                {{ $document->document_number }}
                &middot;
                Generated {{ $document->generated_at->format('d M Y H:i') }}
            </div>
        </div>

        @if($document->is_voided)
            <div class="document-status">
                <span class="badge bg-danger px-3 py-2">
                    VOIDED
                </span>
            </div>
        @endif
    </div>


    <div class="row g-4">

        {{-- Main Document Information --}}
        <div class="col-lg-8">

            <div class="document-card">

                <div class="document-card-header">
                    <h2 class="document-card-title">
                        Document Information
                    </h2>
                </div>

                <div class="document-card-body">

                    <div class="row g-4 mb-4">

                        @if($document->customer)
                            <div class="col-md-6">
                                <div class="document-info-label">
                                    Customer
                                </div>

                                <div class="document-info-value">
                                    {{ $document->customer->full_name }}
                                </div>
                            </div>
                        @endif

                        @if($document->vehicle)
                            <div class="col-md-6">
                                <div class="document-info-label">
                                    Vehicle
                                </div>

                                <div class="document-info-value">
                                    {{ $document->vehicle->make->name }}
                                    {{ $document->vehicle->vehicleModel->name }}
                                </div>
                            </div>
                        @endif

                    </div>


                    {{-- PDF Area --}}
                    <div class="pdf-placeholder">

                        <div class="pdf-placeholder-icon">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </div>

                        <div class="pdf-placeholder-title">
                            PDF Document Ready
                        </div>

                        <p class="pdf-placeholder-text">
                            Use the Download PDF button to download this document.
                        </p>

                    </div>


                    {{-- Void Information --}}
                    @if($document->is_voided)

                        <div class="alert alert-danger void-alert">

                            <div class="fw-semibold mb-1">
                                Document Voided
                            </div>

                            <div>
                                {{ $document->void_reason }}

                                @if($document->voidedBy)
                                    &middot;
                                    by {{ $document->voidedBy->name }}
                                @endif

                                @if($document->voided_at)
                                    on {{ $document->voided_at->format('d M Y') }}
                                @endif
                            </div>

                        </div>

                    @endif

                </div>
            </div>

        </div>


        {{-- Right Side --}}
        <div class="col-lg-4">

            {{-- Actions --}}
            <div class="document-card mb-4">

                <div class="document-card-header">
                    <h2 class="document-card-title">
                        Actions
                    </h2>
                </div>

                <div class="document-card-body">

                    <div class="action-list">

                        @if(!$document->is_voided)

                            <a
                                href="{{ route('documents.download', $document) }}"
                                class="btn btn-primary btn-sm"
                            >
                                <i class="bi bi-download me-1"></i>
                                Download PDF
                            </a>

                            <form
                                method="POST"
                                action="{{ route('documents.send-whatsapp', $document) }}"
                            >
                                @csrf

                                <input
                                    type="hidden"
                                    name="phone"
                                    value="{{ $document->customer?->mobile }}"
                                >

                                <button
                                    type="submit"
                                    class="btn btn-success btn-sm w-100"
                                >
                                    <i class="bi bi-whatsapp me-1"></i>
                                    Send via WhatsApp
                                </button>
                            </form>

                            @can('void-documents')

                                <button
                                    type="button"
                                    class="btn btn-outline-danger btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#voidModal"
                                >
                                    <i class="bi bi-x-circle me-1"></i>
                                    Void Document
                                </button>

                            @endcan

                        @else

                            <div class="alert alert-danger mb-0 small">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                This document has been voided.
                            </div>

                        @endif

                    </div>

                </div>
            </div>


            {{-- Verification --}}
            <div class="document-card">

                <div class="document-card-header">
                    <h2 class="document-card-title">
                        Verification
                    </h2>
                </div>

                <div class="document-card-body">

                    <div class="document-info-label">
                        Verification Code
                    </div>

                    <div class="verification-code">
                        {{ $document->verification_code }}
                    </div>

                    <a
                        href="{{ $document->verification_url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="verification-url"
                    >
                        {{ $document->verification_url }}
                    </a>

                </div>
            </div>

        </div>

    </div>

</div>


{{-- Void Document Modal --}}
@can('void-documents')

<div
    class="modal fade"
    id="voidModal"
    tabindex="-1"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form
                method="POST"
                action="{{ route('documents.void', $document) }}"
            >

                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">
                        Void Document
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>

                <div class="modal-body">

                    <label
                        for="voidReason"
                        class="form-label fw-semibold"
                    >
                        Reason for voiding
                    </label>

                    <input
                        id="voidReason"
                        type="text"
                        name="reason"
                        class="form-control"
                        placeholder="Enter reason for voiding"
                        required
                    >

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-danger"
                    >
                        Confirm Void
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

@endcan

@endsection
