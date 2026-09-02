<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;

class DocumentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = app('tenant');
        $userId = \App\Models\User::where('tenant_id', $tenant->id)->first()?->id ?? 1;

        $templates = [
            'sale_invoice' => [
                'name' => 'Sale Invoice',
                'html' => $this->saleInvoiceHtml(),
            ],
            'proforma_invoice' => [
                'name' => 'Proforma Invoice',
                'html' => $this->proformaInvoiceHtml(),
            ],
            'booking_receipt' => [
                'name' => 'Booking Receipt',
                'html' => $this->bookingReceiptHtml(),
            ],
            'payment_receipt' => [
                'name' => 'Payment Receipt',
                'html' => $this->paymentReceiptHtml(),
            ],
            'delivery_order' => [
                'name' => 'Delivery Order',
                'html' => $this->deliveryOrderHtml(),
            ],
            'affidavit' => [
                'name' => 'Affidavit',
                'html' => $this->affidavitHtml(),
            ],
            'transfer_letter' => [
                'name' => 'Transfer Letter',
                'html' => $this->transferLetterHtml(),
            ],
            'open_transfer_letter' => [
                'name' => 'Open Transfer Letter',
                'html' => $this->openTransferLetterHtml(),
            ],
            'sale_agreement' => [
                'name' => 'Sale Agreement',
                'html' => $this->saleAgreementHtml(),
            ],
            'possession_letter' => [
                'name' => 'Possession Letter',
                'html' => $this->possessionLetterHtml(),
            ],
            'authority_letter' => [
                'name' => 'Authority Letter',
                'html' => $this->authorityLetterHtml(),
            ],
            'handover_certificate' => [
                'name' => 'Vehicle Handover Certificate',
                'html' => $this->handoverCertificateHtml(),
            ],
            'customer_declaration' => [
                'name' => 'Customer Declaration',
                'html' => $this->customerDeclarationHtml(),
            ],
            'installment_agreement' => [
                'name' => 'Installment Agreement',
                'html' => $this->installmentAgreementHtml(),
            ],
            'commission_voucher' => [
                'name' => 'Commission Voucher',
                'html' => $this->commissionVoucherHtml(),
            ],
            'cash_receipt_voucher' => [
                'name' => 'Cash Receipt Voucher',
                'html' => $this->cashReceiptVoucherHtml(),
            ],
            'cash_payment_voucher' => [
                'name' => 'Cash Payment Voucher',
                'html' => $this->cashPaymentVoucherHtml(),
            ],
            'journal_voucher' => [
                'name' => 'Journal Voucher',
                'html' => $this->journalVoucherHtml(),
            ],
        ];

        foreach ($templates as $type => $tpl) {
            DocumentTemplate::updateOrCreate(
                ['tenant_id' => $tenant->id, 'document_type' => $type, 'is_default' => true],
                [
                    'name'        => $tpl['name'],
                    'html_body'   => $tpl['html'],
                    'page_size'   => 'A4',
                    'orientation' => 'portrait',
                    'show_logo'   => true,
                    'show_qr'     => true,
                    'is_active'   => true,
                    'created_by'  => $userId,
                ]
            );
        }
    }

    private function saleInvoiceHtml(): string
    {
        return "
        <table style='margin-bottom:20px;'>
            <tr><td><strong>Invoice #:</strong> {{invoice_number}}</td><td><strong>Date:</strong> {{invoice_date}}</td></tr>
        </table>
        <h4>Bill To:</h4>
        <p>{{customer_name}}<br>{{customer_address}}<br>{{customer_mobile}}<br>CNIC: {{customer_cnic}}</p>
        <h4>Vehicle Details:</h4>
        <table>
            <tr><td>Make / Model</td><td>{{vehicle_make}} {{vehicle_model}} {{vehicle_variant}}</td></tr>
            <tr><td>Year</td><td>{{vehicle_year}}</td></tr>
            <tr><td>Color</td><td>{{vehicle_color}}</td></tr>
            <tr><td>Chassis #</td><td>{{chassis_number}}</td></tr>
            <tr><td>Engine #</td><td>{{engine_number}}</td></tr>
            <tr><td>Registration #</td><td>{{registration_number}}</td></tr>
        </table>
        <h4>Payment Summary:</h4>
        <table>
            <tr><td>Sale Price</td><td class='text-right'>{{sale_price}}</td></tr>
            <tr><td>Discount</td><td class='text-right'>{{discount}}</td></tr>
            <tr><td>Withholding Tax</td><td class='text-right'>{{withholding_tax}}</td></tr>
            <tr class='fw-bold'><td>Net Amount</td><td class='text-right'>{{net_amount}}</td></tr>
        </table>
        <p><strong>Amount in Words:</strong> {{amount_in_words}}</p>
        <div class='signature-box'>
            <div>_______________________<br>Customer Signature</div>
            <div>_______________________<br>Authorized Signatory</div>
        </div>";
    }

    private function proformaInvoiceHtml(): string
    {
        return "<p><strong>Proforma Invoice</strong></p>
        <p>Customer: {{customer_name}}<br>Vehicle: {{vehicle_make}} {{vehicle_model}} {{vehicle_year}}</p>
        <table><tr><td>Sale Price</td><td>{{sale_price}}</td></tr></table>
        <p>This is a proforma invoice and not a demand for payment.</p>";
    }

    private function bookingReceiptHtml(): string
    {
        return "<h4>Booking Receipt</h4>
        <p>Received from <strong>{{customer_name}}</strong> the sum of <strong>{{amount_paid}}</strong>
        as advance payment for <strong>{{vehicle_make}} {{vehicle_model}} {{vehicle_year}}</strong> (Stock# {{stock_number}}).</p>
        <p>Agreed Sale Price: {{sale_price}}<br>Balance Due: {{balance_due}}</p>
        <div class='signature-box'><div>_______________<br>Customer</div><div>_______________<br>Dealer</div></div>";
    }

    private function paymentReceiptHtml(): string
    {
        return "<h4>Payment Receipt</h4>
        <p>Received from <strong>{{customer_name}}</strong> an amount of <strong>{{net_amount}}</strong>
        towards Invoice #{{invoice_number}} dated {{invoice_date}}.</p>
        <p>Balance Due: {{balance_due}}</p>";
    }

    private function deliveryOrderHtml(): string
    {
        return "<h4>Delivery Order</h4>
        <p>This certifies that the vehicle <strong>{{vehicle_make}} {{vehicle_model}} {{vehicle_year}}</strong>
        (Chassis: {{chassis_number}}) has been delivered to <strong>{{customer_name}}</strong> on {{document_date}}.</p>
        <div class='signature-box'><div>_______________<br>Received By</div><div>_______________<br>Delivered By</div></div>";
    }

    private function affidavitHtml(): string
    {
        return "<h4 class='text-center'>AFFIDAVIT</h4>
        <p>I, <strong>{{customer_name}}</strong>, S/O <strong>{{customer_father_name}}</strong>,
        holder of CNIC No. <strong>{{customer_cnic}}</strong>, resident of {{customer_address}},
        do hereby solemnly affirm and declare as under:</p>
        <p>1. That I am the lawful owner/purchaser of vehicle Make/Model
        <strong>{{vehicle_make}} {{vehicle_model}}</strong>, Year <strong>{{vehicle_year}}</strong>,
        Registration No. <strong>{{registration_number}}</strong>, Chassis No. <strong>{{chassis_number}}</strong>,
        Engine No. <strong>{{engine_number}}</strong>.</p>
        <p>2. That the said vehicle has been sold/purchased for a total consideration of
        <strong>{{sale_price}}</strong> ({{amount_in_words}}) on {{sale_date}}.</p>
        <p>3. That the contents of this affidavit are true and correct to the best of my knowledge and belief.</p>
        <div class='signature-box'><div>_______________<br>Deponent</div><div>_______________<br>Witness</div></div>";
    }

    private function transferLetterHtml(): string
    {
        return "<h4 class='text-center'>TRANSFER LETTER</h4>
        <p>This is to certify that I, <strong>{{customer_name}}</strong>, CNIC <strong>{{customer_cnic}}</strong>,
        hereby transfer all rights, title, and interest in vehicle
        <strong>{{vehicle_make}} {{vehicle_model}} {{vehicle_year}}</strong>,
        Registration No. <strong>{{registration_number}}</strong>, Chassis No. <strong>{{chassis_number}}</strong>
        in favor of the buyer effective {{document_date}}.</p>
        <div class='signature-box'><div>_______________<br>Seller</div><div>_______________<br>Buyer</div></div>";
    }

    private function openTransferLetterHtml(): string
    {
        return "<h4 class='text-center'>OPEN TRANSFER LETTER</h4>
        <p>I, <strong>{{customer_name}}</strong>, CNIC <strong>{{customer_cnic}}</strong>, being the registered
        owner of vehicle <strong>{{vehicle_make}} {{vehicle_model}}</strong>, Registration No.
        <strong>{{registration_number}}</strong>, hereby authorize the transfer of ownership of the said
        vehicle to any person presenting this letter, effective {{document_date}}.</p>
        <div class='signature-box'><div>_______________<br>Seller / Registered Owner</div></div>";
    }

    private function saleAgreementHtml(): string
    {
        return "<h4 class='text-center'>VEHICLE SALE AGREEMENT</h4>
        <p>This Sale Agreement is made on {{document_date}} between <strong>{{dealer_name}}</strong> (Seller)
        and <strong>{{customer_name}}</strong> (Buyer), CNIC {{customer_cnic}}, regarding the sale of the
        following vehicle:</p>
        <table>
            <tr><td>Make / Model</td><td>{{vehicle_make}} {{vehicle_model}}</td></tr>
            <tr><td>Year</td><td>{{vehicle_year}}</td></tr>
            <tr><td>Chassis #</td><td>{{chassis_number}}</td></tr>
            <tr><td>Sale Price</td><td>{{sale_price}}</td></tr>
        </table>
        <p>Both parties agree to the terms and conditions of this sale as documented herein.</p>
        <div class='signature-box'><div>_______________<br>Seller</div><div>_______________<br>Buyer</div></div>";
    }

    private function possessionLetterHtml(): string
    {
        return "<h4 class='text-center'>POSSESSION LETTER</h4>
        <p>This is to certify that possession of vehicle <strong>{{vehicle_make}} {{vehicle_model}} {{vehicle_year}}</strong>,
        Chassis No. <strong>{{chassis_number}}</strong>, has been handed over to <strong>{{customer_name}}</strong>
        on {{document_date}}.</p>
        <div class='signature-box'><div>_______________<br>Recipient</div><div>_______________<br>Dealer Representative</div></div>";
    }

    private function authorityLetterHtml(): string
    {
        return "<h4 class='text-center'>AUTHORITY LETTER</h4>
        <p>I, <strong>{{customer_name}}</strong>, CNIC <strong>{{customer_cnic}}</strong>, hereby authorize
        the bearer to act on my behalf for all matters related to vehicle registration, transfer, and
        documentation of <strong>{{vehicle_make}} {{vehicle_model}}</strong>, Chassis No. <strong>{{chassis_number}}</strong>.</p>
        <div class='signature-box'><div>_______________<br>Authorizing Person</div></div>";
    }

    private function handoverCertificateHtml(): string
    {
        return "<h4 class='text-center'>VEHICLE HANDOVER CERTIFICATE</h4>
        <p>Vehicle: <strong>{{vehicle_make}} {{vehicle_model}} {{vehicle_year}}</strong><br>
        Chassis #: {{chassis_number}}<br>Handed over to: <strong>{{customer_name}}</strong> on {{document_date}}</p>
        <p>Condition at handover: Good. Accessories and documents handed over as per checklist.</p>
        <div class='signature-box'><div>_______________<br>Customer</div><div>_______________<br>Dealer</div></div>";
    }

    private function customerDeclarationHtml(): string
    {
        return "<h4 class='text-center'>CUSTOMER DECLARATION</h4>
        <p>I, <strong>{{customer_name}}</strong>, CNIC <strong>{{customer_cnic}}</strong>, hereby declare that
        I am purchasing vehicle <strong>{{vehicle_make}} {{vehicle_model}}</strong> for my personal use and
        all information provided by me is true and correct.</p>
        <div class='signature-box'><div>_______________<br>Customer Signature</div></div>";
    }

    private function installmentAgreementHtml(): string
    {
        return "<h4 class='text-center'>INSTALLMENT SALE AGREEMENT</h4>
        <p>This agreement is entered into between <strong>{{dealer_name}}</strong> and
        <strong>{{customer_name}}</strong>, CNIC {{customer_cnic}}, for the sale of vehicle
        <strong>{{vehicle_make}} {{vehicle_model}}</strong> on an installment basis.</p>
        <p>Total Price: {{sale_price}}<br>Terms as per the attached payment schedule.</p>
        <div class='signature-box'><div>_______________<br>Customer</div><div>_______________<br>Dealer</div></div>";
    }

    private function commissionVoucherHtml(): string
    {
        return "<h4 class='text-center'>COMMISSION VOUCHER</h4>
        <p>Sale Invoice: {{invoice_number}}<br>Vehicle: {{vehicle_make}} {{vehicle_model}}<br>
        Commission Amount: {{net_amount}}</p>
        <div class='signature-box'><div>_______________<br>Recipient</div><div>_______________<br>Approved By</div></div>";
    }

    private function cashReceiptVoucherHtml(): string
    {
        return "<h4 class='text-center'>CASH RECEIPT VOUCHER</h4>
        <p>Received from: <strong>{{customer_name}}</strong><br>Amount: {{net_amount}}<br>Date: {{document_date}}</p>
        <div class='signature-box'><div>_______________<br>Received By</div><div>_______________<br>Payer Signature</div></div>";
    }

    private function cashPaymentVoucherHtml(): string
    {
        return "<h4 class='text-center'>CASH PAYMENT VOUCHER</h4>
        <p>Paid to: __________________<br>Amount: {{net_amount}}<br>Date: {{document_date}}</p>
        <div class='signature-box'><div>_______________<br>Paid By</div><div>_______________<br>Received By</div></div>";
    }

    private function journalVoucherHtml(): string
    {
        return "<h4 class='text-center'>JOURNAL VOUCHER</h4>
        <p>Date: {{document_date}}<br>Narration: __________________</p>
        <table><tr><th>Account</th><th>Debit</th><th>Credit</th></tr><tr><td></td><td></td><td></td></tr></table>
        <div class='signature-box'><div>_______________<br>Prepared By</div><div>_______________<br>Approved By</div></div>";
    }
}
