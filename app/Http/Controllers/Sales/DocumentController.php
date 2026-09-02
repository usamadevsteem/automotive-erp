<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use App\Models\GeneratedDocument;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function __construct(private readonly DocumentService $documentService) {}

    // ── Template Management ────────────────────────────────────────

    public function templateIndex(): View
    {
        $this->authorize('manage-document-templates');
        $templates = DocumentTemplate::orderBy('document_type')->get();
        return view('documents.templates.index', compact('templates'));
    }

    public function templateCreate(): View
    {
        $this->authorize('manage-document-templates');
        return view('documents.templates.create');
    }

    public function templateStore(Request $request): RedirectResponse
    {
        $this->authorize('manage-document-templates');
        $data = $request->validate([
            'name'           => ['required','string','max:150'],
            'document_type'  => ['required','in:' . implode(',', array_keys(DocumentTemplate::DOCUMENT_TYPES))],
            'html_body'      => ['required','string'],
            'header_html'    => ['nullable','string'],
            'footer_html'    => ['nullable','string'],
            'page_size'      => ['required','in:A4,A5,Letter'],
            'orientation'    => ['required','in:portrait,landscape'],
            'show_logo'      => ['boolean'],
            'show_qr'        => ['boolean'],
            'watermark_text' => ['nullable','string','max:50'],
            'is_default'     => ['boolean'],
        ]);

        $data['created_by'] = auth()->id();

        // If setting as default, unset other defaults of same type
        if (!empty($data['is_default'])) {
            DocumentTemplate::where('document_type', $data['document_type'])
                ->update(['is_default' => false]);
        }

        DocumentTemplate::create($data);
        return redirect()->route('document-templates.index')
            ->with('success', 'Template created.');
    }

    public function templateEdit(DocumentTemplate $documentTemplate): View
    {
        $this->authorize('manage-document-templates');
        return view('documents.templates.edit', compact('documentTemplate'));
    }

    public function templateUpdate(Request $request, DocumentTemplate $documentTemplate): RedirectResponse
    {
        $this->authorize('manage-document-templates');
        $data = $request->validate([
            'name'           => ['required','string','max:150'],
            'html_body'      => ['required','string'],
            'header_html'    => ['nullable','string'],
            'footer_html'    => ['nullable','string'],
            'page_size'      => ['required','in:A4,A5,Letter'],
            'orientation'    => ['required','in:portrait,landscape'],
            'show_logo'      => ['boolean'],
            'show_qr'        => ['boolean'],
            'watermark_text' => ['nullable','string','max:50'],
            'is_default'     => ['boolean'],
        ]);

        if (!empty($data['is_default'])) {
            DocumentTemplate::where('document_type', $documentTemplate->document_type)
                ->where('id', '!=', $documentTemplate->id)
                ->update(['is_default' => false]);
        }

        $documentTemplate->update($data);
        return redirect()->route('document-templates.index')
            ->with('success', 'Template updated.');
    }

    // ── Document Generation ────────────────────────────────────────

    public function generate(Request $request): RedirectResponse
    {
        $this->authorize('generate-documents');

        $data = $request->validate([
            'document_type'  => ['required','string'],
            'reference_type' => ['required','string'],
            'reference_id'   => ['required','integer'],
            'customer_id'    => ['nullable','integer'],
            'vehicle_id'     => ['nullable','integer'],
        ]);

        try {
            $doc = $this->documentService->generate(
                $data['document_type'],
                $data['reference_type'],
                $data['reference_id'],
                $data['customer_id'] ?? null,
                $data['vehicle_id']  ?? null,
            );

            return redirect()->route('documents.show', $doc)
                ->with('success', ucwords(str_replace('_',' ',$data['document_type'])) . ' generated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Could not generate document: ' . $e->getMessage());
        }
    }

    public function show(GeneratedDocument $document): View
    {
        $this->authorize('generate-documents');
        $document->load(['template','customer','vehicle.make','vehicle.vehicleModel','generatedBy','branch']);
        return view('documents.show', compact('document'));
    }

    public function download(GeneratedDocument $document): Response
    {
        $this->authorize('generate-documents');
        abort_if($document->is_voided, 403, 'This document has been voided.');

        $url = $this->documentService->getTemporaryUrl($document);
        return redirect()->away($url);
    }

    public function sendWhatsApp(Request $request, GeneratedDocument $document): RedirectResponse
    {
        $this->authorize('generate-documents');
        $request->validate(['phone' => ['required','string','max:20']]);

        // Mark as sent
        $document->update(['whatsapp_sent_at' => now()]);
        return back()->with('success', 'Document sent via WhatsApp.');
    }

    public function void(Request $request, GeneratedDocument $document): RedirectResponse
    {
        $this->authorize('void-documents');
        $request->validate(['reason' => ['required','string','max:255']]);

        $document->update([
            'is_voided'   => true,
            'voided_by'   => auth()->id(),
            'voided_at'   => now(),
            'void_reason' => $request->reason,
        ]);

        return back()->with('success', 'Document voided.');
    }

    // ── Document History ───────────────────────────────────────────

    public function history(Request $request): View
    {
        $this->authorize('view-document-history');

        $documents = GeneratedDocument::with(['customer','vehicle.make','generatedBy'])
            ->when($request->document_type, fn($q,$v) => $q->where('document_type',$v))
            ->when($request->search, fn($q,$v) => $q->where('document_number','like',"%{$v}%"))
            ->latest('generated_at')->paginate(25)->withQueryString();

        return view('documents.history', compact('documents'));
    }

    // ── Public Verification ────────────────────────────────────────

    public function verify(string $code)
    {
        $doc = GeneratedDocument::where('verification_code', $code)
            ->with(['customer','vehicle.make','vehicle.vehicleModel','generatedBy.branch'])
            ->first();

        if (!$doc) {
            return view('documents.verify-fail');
        }

        return view('documents.verify', compact('doc'));
    }
}
