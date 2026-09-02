<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class NumberingService
{
    private function next(string $prefix, string $table, string $column): string
    {
        $tenant = app('tenant');
        $year   = now()->year;
        $lock   = "numbering:{$tenant->id}:{$prefix}:{$year}";

        return Cache::lock($lock, 5)->block(3, function () use ($tenant, $year, $prefix, $table, $column) {
            $last = \Illuminate\Support\Facades\DB::table($table)
                ->where('tenant_id', $tenant->id)
                ->where($column, 'like', "{$prefix}-{$year}-%")
                ->orderByDesc($column)
                ->value($column);

            $next = 1;
            if ($last) {
                $parts = explode('-', $last);
                $next  = ((int) end($parts)) + 1;
            }

            return sprintf('%s-%d-%04d', $prefix, $year, $next);
        });
    }

    public function quotation(): string      { return $this->next('QT',   'quotations',    'quotation_number'); }
    public function booking(): string        { return $this->next('BK',   'bookings',      'booking_number'); }
    public function invoice(): string        { return $this->next('INV',  'sale_invoices', 'invoice_number'); }
    public function delivery(): string       { return $this->next('DEL',  'delivery_orders','delivery_number'); }
    public function payment(): string        { return $this->next('RCP',  'payments',      'payment_number'); }
    public function expense(): string        { return $this->next('EXP',  'expenses',      'expense_number'); }
    public function journalEntry(): string   { return $this->next('JV',   'journal_entries','entry_number'); }
    public function dealFile(): string       { return $this->next('DEAL', 'deal_files',    'deal_number'); }
    public function document(): string       { return $this->next('DOC',  'generated_documents','document_number'); }
}
