<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use App\Models\GeneratedDocument;
use App\Models\SaleInvoice;
use App\Models\DealFile;
use App\Models\Customer;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    public function __construct(private readonly NumberingService $numbering) {}

    /**
     * Generate a document from a template type and a reference.
     */
    public function generate(
        string  $documentType,
        string  $referenceType,
        int     $referenceId,
        ?int    $customerId = null,
        ?int    $vehicleId  = null
    ): GeneratedDocument {

        $template = DocumentTemplate::where('document_type', $documentType)
            ->where('is_default', true)
            ->where('is_active', true)
            ->firstOrFail();

        // Resolve all variables
        $data = $this->resolveVariables($documentType, $referenceType, $referenceId, $customerId, $vehicleId);

        // Render HTML with variables replaced
        $html = $this->renderTemplate($template, $data);

        // Generate PDF
        $pdf      = Pdf::loadHTML($html)->setPaper($template->page_size, $template->orientation);
        $filename = "{$documentType}-" . now()->format('YmdHis') . '.pdf';
        $path     = "tenants/" . app('tenant')->id . "/documents/{$filename}";
        Storage::disk('s3')->put($path, $pdf->output(), 'private');

        $doc = GeneratedDocument::create([
        'tenant_id'         => app('tenant')->id,
        'branch_id'         => auth()->user()->branch_id,
        'document_number'   => $this->numbering->document(),
        'template_id'       => $template->id,
        'document_type'     => $documentType,
        'reference_type'    => $referenceType,
        'reference_id'      => $referenceId,
        'customer_id'       => $customerId,
        'vehicle_id'        => $vehicleId,
        'resolved_data'     => $data,
        'file_path'         => $path,
        'verification_code' => strtoupper(Str::random(10)),
        'generated_by'      => auth()->id(),
        'generated_at'      => now(),
        ]);

        // Update the Deal File when a sales document is generated.
        if ($referenceType === 'sale_invoice') {
            $dealFile = DealFile::where('sale_invoice_id', $referenceId)->first();

            if ($dealFile) {
                $dealFile->markDocumentDone($documentType);
            }
        }

            return $doc;
        }

    /**
     * Resolve all template variables from DB records.
     */
    private function resolveVariables(
        string $documentType,
        string $referenceType,
        int    $referenceId,
        ?int   $customerId,
        ?int   $vehicleId
    ): array {
        $data = [];
        $tenant = app('tenant');

        // Dealer info
        $data['dealer_name']    = $tenant->company_name;
        $data['dealer_address'] = $tenant->address ?? '';
        $data['dealer_phone']   = $tenant->phone   ?? '';
        $data['dealer_city']    = $tenant->city     ?? '';
        $data['document_date']  = now()->format('d/m/Y');

        // Customer
        if ($customerId) {
            $customer = Customer::find($customerId);
            if ($customer) {
                $data['customer_name']        = $customer->full_name;
                $data['customer_father_name'] = $customer->father_husband_name ?? '';
                $data['customer_cnic']        = $customer->cnic ?? '';
                $data['customer_mobile']      = $customer->mobile;
                $data['customer_email']       = $customer->email ?? '';
                $data['customer_address']     = $customer->address ?? '';
                $data['customer_city']        = $customer->city   ?? '';
                $data['customer_occupation']  = $customer->occupation ?? '';
                $data['customer_tax_status']  = ucfirst($customer->tax_status);
            }
        }

        // Vehicle
        if ($vehicleId) {
            $vehicle = Vehicle::with(['make','vehicleModel','variant'])->find($vehicleId);
            if ($vehicle) {
                $data['vehicle_make']          = $vehicle->make->name;
                $data['vehicle_model']         = $vehicle->vehicleModel->name;
                $data['vehicle_variant']       = $vehicle->variant?->name ?? '';
                $data['vehicle_year']          = $vehicle->year;
                $data['vehicle_color']         = $vehicle->color ?? '';
                $data['vehicle_engine_cc']     = $vehicle->engine_capacity ?? '';
                $data['vehicle_fuel_type']     = ucfirst($vehicle->fuel_type);
                $data['vehicle_transmission']  = ucfirst($vehicle->transmission);
                $data['registration_number']   = $vehicle->registration_number ?? '';
                $data['chassis_number']        = $vehicle->chassis_number ?? '';
                $data['engine_number']         = $vehicle->engine_number ?? '';
                $data['stock_number']          = $vehicle->stock_number;
            }
        }

        // Sale Invoice
        if ($referenceType === 'sale_invoice') {
            $invoice = SaleInvoice::find($referenceId);
            if ($invoice) {
                $data['invoice_number']    = $invoice->invoice_number;
                $data['invoice_date']      = $invoice->invoice_date->format('d/m/Y');
                $data['sale_price']        = 'PKR ' . number_format($invoice->sale_price);
                $data['discount']          = 'PKR ' . number_format($invoice->discount);
                $data['net_amount']        = 'PKR ' . number_format($invoice->net_amount);
                $data['amount_in_words']   = $this->numberToWords($invoice->net_amount);
                $data['payment_type']      = ucfirst($invoice->payment_type);
                $data['amount_paid']       = 'PKR ' . number_format($invoice->amount_paid);
                $data['balance_due']       = 'PKR ' . number_format($invoice->balance_due);
                $data['withholding_tax']   = 'PKR ' . number_format($invoice->withholding_tax);
            }
        }

        return $data;
    }

    private function renderTemplate(DocumentTemplate $template, array $data): string
    {
        $html = $template->html_body;

        foreach ($data as $key => $value) {
            $html = str_replace('{{' . $key . '}}', htmlspecialchars((string)$value), $html);
            $html = str_replace('{{ ' . $key . ' }}', htmlspecialchars((string)$value), $html);
        }

        // Wrap in a full HTML document
        $logo = '';
        if ($template->show_logo) {
            $tenant  = app('tenant');
            $logoUrl = $tenant->logo_path ? Storage::url($tenant->logo_path) : '';
            $logo    = $logoUrl ? "<img src='{$logoUrl}' style='max-height:60px;'>" : '';
        }

        return "<!DOCTYPE html><html><head><meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 20px; }
                .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; }
                td, th { padding: 6px 8px; border: 1px solid #ddd; }
                .fw-bold { font-weight: bold; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .signature-box { margin-top: 40px; display: flex; justify-content: space-between; }
            </style></head>
            <body>
            <div class='header'>{$logo}<h2>{$template->name}</h2></div>
            {$html}
            </body></html>";
    }

    private function numberToWords(float $number): string
    {
        $ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine',
                 'Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen',
                 'Sixteen','Seventeen','Eighteen','Nineteen'];
        $tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];

        $n = (int) round($number);

        if ($n === 0) return 'Zero Rupees Only';

        $convert = function (int $n) use (&$convert, $ones, $tens): string {
            if ($n < 20)      return $ones[$n];
            if ($n < 100)     return $tens[(int)($n/10)] . ($n % 10 ? ' ' . $ones[$n % 10] : '');
            if ($n < 1000)    return $ones[(int)($n/100)] . ' Hundred' . ($n % 100 ? ' ' . $convert($n % 100) : '');
            if ($n < 100000)  return $convert((int)($n/1000)) . ' Thousand' . ($n % 1000 ? ' ' . $convert($n % 1000) : '');
            if ($n < 10000000)return $convert((int)($n/100000)) . ' Lakh' . ($n % 100000 ? ' ' . $convert($n % 100000) : '');
            return $convert((int)($n/10000000)) . ' Crore' . ($n % 10000000 ? ' ' . $convert($n % 10000000) : '');
        };

        return $convert($n) . ' Rupees Only';
    }

    public function getTemporaryUrl(GeneratedDocument $doc): string
    {
        return Storage::disk('s3')->temporaryUrl($doc->file_path, now()->addMinutes(30));
    }
}
