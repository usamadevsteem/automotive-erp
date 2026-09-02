<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\DealFile;
use App\Models\DeliveryOrder;
use App\Models\Quotation;
use App\Models\SaleInvoice;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(
        private readonly NumberingService  $numbering,
        private readonly AccountingService $accounting,
        private readonly CommissionService $commissionService,
    ) {}

    // ── Quotation ──────────────────────────────────────────────────

    public function createQuotation(array $data): Quotation
    {
        $data['quotation_number'] = $this->numbering->quotation();
        $data['created_by']       = auth()->id();
        $data['net_price']        = $data['sale_price'] - ($data['discount'] ?? 0);
        $data['tenant_id']        = app('tenant')->id;

        return Quotation::create($data);
    }

    public function updateQuotationStatus(Quotation $quotation, string $status): Quotation
    {
        $quotation->update(['status' => $status]);
        return $quotation->fresh();
    }

    // ── Booking ────────────────────────────────────────────────────

    public function createBooking(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $data['booking_number'] = $this->numbering->booking();
            $data['created_by']     = auth()->id();
            $data['tenant_id']      = app('tenant')->id;

            $booking = Booking::create($data);

            // Mark vehicle as reserved
            $booking->vehicle->update(['status' => Vehicle::STATUS_RESERVED]);

            // Update quotation status if linked
            if ($data['quotation_id'] ?? null) {
                Quotation::find($data['quotation_id'])?->update(['status' => 'accepted']);
            }

            return $booking;
        });
    }

    public function cancelBooking(Booking $booking, string $reason): Booking
    {
        return DB::transaction(function () use ($booking, $reason) {
            $booking->update(['status' => 'cancelled', 'cancellation_reason' => $reason]);

            // Release vehicle back to available
            $booking->vehicle->update(['status' => Vehicle::STATUS_AVAILABLE]);

            return $booking->fresh();
        });
    }

    // ── Sale Invoice ───────────────────────────────────────────────

    public function createInvoice(array $data): SaleInvoice
    {
        return DB::transaction(function () use ($data) {
            $data['invoice_number'] = $this->numbering->invoice();
            $data['created_by']     = auth()->id();
            $data['tenant_id']      = app('tenant')->id;
            $data['balance_due']    = $data['net_amount'] - ($data['amount_paid'] ?? 0);

            $invoice = SaleInvoice::create($data);

            // Record initial payment if an amount was paid at invoice creation
            if (($data['amount_paid'] ?? 0) > 0) {

                $paymentSvc = app(PaymentService::class);

                $paymentSvc->record([
                    'type'             => 'received',
                    'party_type'       => 'customer',
                    'party_id'         => $invoice->customer_id,
                    'reference_type'   => 'sale_invoice',
                    'reference_id'     => $invoice->id,
                    'amount'           => $data['amount_paid'],
                    'payment_method'   => $data['payment_type'] === 'bank_transfer'
                                            ? 'bank_transfer'
                                            : ($data['payment_type'] === 'cheque'
                                                ? 'cheque'
                                                : 'cash'),
                    'payment_date'     => $invoice->invoice_date,
                    'reference_number' => null,
                    'notes'            => 'Initial payment recorded with invoice creation.',
                    'branch_id'        => $invoice->branch_id,
                ]);
            }



            // Mark vehicle sold
            $vehicle = $invoice->vehicle;
            $vehicle->update([
                'status'       => Vehicle::STATUS_SOLD,
                'sold_by'      => auth()->id(),
                'sold_at'      => now(),
                'actual_profit'=> $invoice->net_amount - $vehicle->total_cost,
            ]);

            // Convert booking to converted
            if ($data['booking_id'] ?? null) {
                Booking::find($data['booking_id'])?->update(['status' => 'converted']);
            }

            // Auto-post accounting entry
            $invoice->load('vehicle','customer');
            $journalEntry = $this->accounting->postSaleEntry($invoice);
            // link entry to invoice
            $invoice->update([]);

            // Auto-calculate commissions
            $this->commissionService->createForSale($invoice);

            // Create deal file
            $this->createDealFile($invoice);

            return $invoice->fresh();
        });
    }

    public function recordPayment(SaleInvoice $invoice, array $paymentData): SaleInvoice
    {
        return DB::transaction(function () use ($invoice, $paymentData) {
            $paymentSvc = app(PaymentService::class);
            $paymentSvc->record([
                ...$paymentData,
                'type'           => 'received',
                'party_type'     => 'customer',
                'party_id'       => $invoice->customer_id,
                'reference_type' => 'sale_invoice',
                'reference_id'   => $invoice->id,
                'branch_id'      => $invoice->branch_id,
            ]);

            $newPaid = $invoice->amount_paid + $paymentData['amount'];
            $status  = $newPaid >= $invoice->net_amount ? 'paid' : 'partial';

            $invoice->update([
                'amount_paid' => $newPaid,
                'balance_due' => $invoice->net_amount - $newPaid,
                'status'      => $status,
            ]);

            return $invoice->fresh();
        });
    }

    public function cancelInvoice(SaleInvoice $invoice, string $reason): SaleInvoice
    {
        return DB::transaction(function () use ($invoice, $reason) {
            if ($invoice->isCancelled()) {
                throw new \LogicException('Invoice is already cancelled.');
            }

            $invoice->update(['status' => 'cancelled', 'cancelled_reason' => $reason]);

            // Restore vehicle to available
            $invoice->vehicle->update([
                'status'        => Vehicle::STATUS_AVAILABLE,
                'sold_by'       => null,
                'sold_at'       => null,
                'actual_profit' => null,
            ]);

            return $invoice->fresh();
        });
    }

    // ── Delivery ───────────────────────────────────────────────────

    public function createDelivery(array $data): DeliveryOrder
    {
        return DB::transaction(function () use ($data) {
            $data['delivery_number'] = $this->numbering->delivery();
            $data['created_by']      = auth()->id();
            $data['tenant_id']       = app('tenant')->id;

            $delivery = DeliveryOrder::create($data);

            // Mark vehicle as delivered
            $delivery->vehicle->update(['status' => Vehicle::STATUS_DELIVERED]);

            // Update deal file
            $delivery->saleInvoice->dealFile?->markDocumentDone('delivery_order');

            return $delivery;
        });
    }

    // ── Deal File ──────────────────────────────────────────────────

    private function createDealFile(SaleInvoice $invoice): DealFile
    {
        return DealFile::create([
            'tenant_id'      => $invoice->tenant_id,
            'branch_id'      => $invoice->branch_id,
            'deal_number'    => $this->numbering->dealFile(),
            'sale_invoice_id'=> $invoice->id,
            'customer_id'    => $invoice->customer_id,
            'vehicle_id'     => $invoice->vehicle_id,
            'deal_type'      => 'cash',
            'checklist'      => ['sale_invoice' => true],
            'status'         => 'in_progress',
            'created_by'     => auth()->id(),
        ]);
    }
}
