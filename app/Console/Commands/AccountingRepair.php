<?php

namespace App\Console\Commands;

use App\Models\IdeaAccountingEntry;
use App\Models\IdeaInvoice;
use App\Models\IdeaInvoicePayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AccountingRepair extends Command
{
    protected $signature = 'accounting:repair {--fix : Apply the corrections (default only reports)}';

    protected $description = 'Report and correct invoice / payment / income-entry inconsistencies';

    public function handle(): int
    {
        $fix = (bool) $this->option('fix');
        $found = 0;

        // 1. Payments and income entries left behind by deleted invoices.
        $liveIds = IdeaInvoice::pluck('id');
        $orphanPayments = IdeaInvoicePayment::whereNotIn('invoice_id', $liveIds)->get();
        $orphanEntries = IdeaAccountingEntry::whereNotNull('invoice_id')->whereNotIn('invoice_id', $liveIds)->get();
        $this->line("Orphan payments: {$orphanPayments->count()} · orphan income entries: {$orphanEntries->count()}");
        $found += $orphanPayments->count() + $orphanEntries->count();

        // 2. Payments without an income entry.
        $missingEntry = IdeaInvoicePayment::whereIn('invoice_id', $liveIds)->get()->filter(
            fn ($p) => !IdeaAccountingEntry::where('voucher_no', $p->payment_no)->exists()
        );
        $this->line("Payments without income entry: {$missingEntry->count()}");
        $found += $missingEntry->count();

        // 3. Totals that disagree with the payment rows, or payments on quotations.
        $mismatch = collect();
        foreach (IdeaInvoice::with('payments')->get() as $inv) {
            $paid = in_array($inv->type, ['invoice', 'challan'], true) ? round((float) $inv->payments->sum('amount'), 2) : 0.0;
            $legacy = $inv->payments->isEmpty() && (float) $inv->paid_amount > 0 && $inv->acceptsPayments();
            if (!$legacy && abs($paid - (float) $inv->paid_amount) > 0.004) {
                $mismatch->push($inv);
            } elseif ($legacy) {
                $mismatch->push($inv);
            } elseif (!$inv->acceptsPayments() && (float) $inv->paid_amount > 0) {
                $mismatch->push($inv);
            }
        }
        $this->line("Invoices with inconsistent paid/due: {$mismatch->count()}");
        $found += $mismatch->count();

        if ($found === 0) {
            $this->info('Nothing to correct.');

            return self::SUCCESS;
        }

        if (!$fix) {
            $this->warn('Run with --fix to apply the corrections.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($orphanPayments, $orphanEntries, $missingEntry, $mismatch) {
            $orphanPayments->each->delete();
            $orphanEntries->each->delete();

            foreach ($mismatch as $inv) {
                // Legacy rows that only carry a paid_amount become a real payment first.
                if ($inv->acceptsPayments() && $inv->payments->isEmpty() && (float) $inv->paid_amount > 0) {
                    IdeaInvoicePayment::record($inv, [
                        'payment_date'   => $inv->invoice_date,
                        'amount'         => min((float) $inv->paid_amount, (float) $inv->grand_total),
                        'payment_method' => $inv->payment_method ?: 'cash',
                        'note'           => 'Backfilled from legacy paid amount',
                    ], null);
                }
                $inv->recalculatePayments();
            }

            foreach ($missingEntry as $payment) {
                $inv = $payment->invoice;
                if (!$inv || IdeaAccountingEntry::where('voucher_no', $payment->payment_no)->exists()) {
                    continue;
                }
                IdeaAccountingEntry::create([
                    'entry_no'       => IdeaAccountingEntry::generateEntryNo('INC'),
                    'type'           => 'income',
                    'category'       => $inv->incomeCategory(),
                    'title'          => "Invoice #{$inv->invoice_no} payment — {$inv->customer_name}",
                    'amount'         => $payment->effective_net_amount,
                    'entry_date'     => $payment->payment_date,
                    'payment_method' => $payment->payment_method,
                    'party_name'     => $inv->customer_name,
                    'voucher_no'     => $payment->payment_no,
                    'invoice_id'     => $inv->id,
                    'notes'          => "Receipt #{$payment->payment_no} (backfilled)",
                    'created_by'     => null,
                ]);
            }
        });

        $this->info('Corrections applied.');

        return self::SUCCESS;
    }
}
