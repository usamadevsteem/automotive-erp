<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\SaleInvoice;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private readonly NumberingService  $numbering,
        private readonly AccountingService $accounting,
    ) {}

    public function record(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $data['payment_number'] = $this->numbering->payment();
            $data['created_by']     = auth()->id();
            $data['tenant_id']      = app('tenant')->id;

            $payment = Payment::create($data);

            // Auto-post accounting entry
            $entry = $this->accounting->postPaymentEntry($payment);
            $payment->update(['journal_entry_id' => $entry->id]);

            return $payment;
        });
    }
}
