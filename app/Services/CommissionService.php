<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\SaleInvoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    public function __construct(private readonly PaymentService $paymentService) {}

    public function createForSale(SaleInvoice $invoice): void
    {
        $rules = CommissionRule::active()->get();

        foreach ($rules as $rule) {
            $amount = $rule->calculate($invoice->net_amount, (float)$invoice->vehicle->actual_profit);
            if ($amount <= 0) continue;

            $employeeId = null;
            if ($rule->applies_to === 'salesman') {
                $employeeId = $invoice->vehicle->sold_by;
            } elseif ($rule->applies_to === 'manager') {
                $employeeId = $invoice->createdBy->id ?? null;
            }

            Commission::create([
                'tenant_id'          => $invoice->tenant_id,
                'sale_invoice_id'    => $invoice->id,
                'vehicle_id'         => $invoice->vehicle_id,
                'commission_rule_id' => $rule->id,
                'employee_id'        => $employeeId,
                'commission_type'    => $rule->applies_to,
                'sale_amount'        => $invoice->net_amount,
                'profit_amount'      => $invoice->vehicle->actual_profit ?? 0,
                'commission_amount'  => $amount,
                'status'             => 'pending',
            ]);
        }
    }

    public function approve(Commission $commission): Commission
    {
        $commission->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        return $commission->fresh();
    }

    public function pay(Commission $commission, array $paymentData): Commission
    {
        return DB::transaction(function () use ($commission, $paymentData) {
            $payment = $this->paymentService->record([
                ...$paymentData,
                'type'           => 'paid',
                'party_type'     => 'employee',
                'party_id'       => $commission->employee_id,
                'amount'         => $commission->commission_amount,
                'reference_type' => 'commission',
                'reference_id'   => $commission->id,
            ]);

            $commission->update([
                'status'     => 'paid',
                'payment_id' => $payment->id,
            ]);

            return $commission->fresh();
        });
    }
}
