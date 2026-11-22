<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IdeaAccountingEntry;
use App\Models\IdeaInvoice;
use App\Models\IdeaInvoicePayment;
use App\Models\MoneyReceipt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * /admin/receipts — money receipts for honorarium paid to writers and for
 * bill money received. Every receipt is also booked in the accounts; a bill
 * receipt against an invoice is recorded as that invoice's payment.
 */
class MoneyReceiptController extends Controller
{
    public function index(Request $request): View
    {
        if (!MoneyReceipt::ready()) {
            return view('admin.receipts.index', ['ready' => false]);
        }

        $kind = in_array($request->query('kind'), array_keys(MoneyReceipt::KINDS), true) ? $request->query('kind') : '';
        $search = trim((string) $request->query('search', ''));
        $month = preg_match('/^\d{4}-\d{2}$/', (string) $request->query('month')) ? $request->query('month') : '';

        $receipts = MoneyReceipt::query()
            ->when($kind, fn ($q) => $q->where('kind', $kind))
            ->when($month, fn ($q) => $q->whereBetween('receipt_date', [$month . '-01', date('Y-m-t', strtotime($month . '-01'))]))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
                $q->where(fn ($w) => $w->where('receipt_no', 'like', $like)->orWhere('party_name', 'like', $like)
                    ->orWhere('party_phone', 'like', $like)->orWhere('purpose', 'like', $like)->orWhere('reference', 'like', $like));
            })
            ->orderByDesc('receipt_date')->orderByDesc('id')
            ->paginate(25)->withQueryString();

        $start = now()->startOfMonth()->toDateString();
        $sum = fn (string $k, ?string $from = null) => (float) MoneyReceipt::where('kind', $k)->when($from, fn ($q) => $q->where('receipt_date', '>=', $from))->sum('amount');

        $stats = [
            'honorarium_month' => $sum('honorarium', $start),
            'bill_month'       => $sum('bill', $start),
            'honorarium_all'   => $sum('honorarium'),
            'bill_all'         => $sum('bill'),
            'count_month'      => MoneyReceipt::where('receipt_date', '>=', $start)->count(),
        ];

        return view('admin.receipts.index', compact('receipts', 'stats', 'kind', 'search', 'month') + ['ready' => true]);
    }

    /** Search writers (for honorarium) or invoices with money due (for bill receipts). */
    public function lookup(Request $request): JsonResponse
    {
        $type = in_array($request->query('type'), ['invoice', 'book'], true) ? $request->query('type') : 'author';
        $q = trim((string) $request->query('q', ''));
        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';

        if ($type === 'book') {
            // the chosen writer's books come first
            $authorId = (int) $request->query('author_id', 0);
            $rows = \Modules\Book\Models\Book::query()
                ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('title', 'like', $like)->orWhere('title_en', 'like', $like)->orWhere('isbn', 'like', $like)))
                ->when($authorId, fn ($w) => $w->orderByRaw('CASE WHEN author_link_id = ? THEN 0 ELSE 1 END', [$authorId]))
                ->orderBy('title')->limit(8)->get(['id', 'title', 'author_name', 'price', 'discount_price']);

            return response()->json(['items' => $rows->map(fn ($b) => [
                'id' => $b->id, 'name' => $b->title, 'price' => (float) $b->price,
                'sub' => trim(($b->author_name ?: '') . ' · মূল্য ৳' . number_format((float) $b->price, 0), ' ·'),
            ])]);
        }

        if ($type === 'author') {
            $rows = \Modules\Author\Models\Author::query()
                ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', $like)->orWhere('name_bn', 'like', $like)->orWhere('phone', 'like', $like)))
                ->orderBy('name')->limit(8)->get(['id', 'name', 'name_bn', 'phone', 'royalty_percentage']);

            return response()->json(['items' => $rows->map(fn ($a) => [
                'id' => $a->id, 'name' => $a->name_bn ?: $a->name, 'phone' => $a->phone, 'sub' => $a->phone ?: '',
                'royalty' => $a->royalty_percentage !== null ? (float) $a->royalty_percentage : null,
            ])]);
        }

        $rows = IdeaInvoice::query()
            ->whereIn('type', ['invoice', 'challan'])
            ->where('due_amount', '>', 0)
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('invoice_no', 'like', $like)->orWhere('customer_name', 'like', $like)->orWhere('customer_phone', 'like', $like)->orWhere('customer_org', 'like', $like)))
            ->orderByDesc('invoice_date')->limit(8)
            ->get(['id', 'invoice_no', 'customer_name', 'customer_org', 'customer_phone', 'customer_address', 'grand_total', 'due_amount', 'invoice_date']);

        return response()->json(['items' => $rows->map(fn ($i) => [
            'id' => $i->id, 'no' => $i->invoice_no, 'name' => $i->customer_name ?: $i->customer_org, 'phone' => $i->customer_phone,
            'address' => $i->customer_address, 'due' => (float) $i->due_amount, 'total' => (float) $i->grand_total,
            'sub' => '#' . $i->invoice_no . ' · বকেয়া ৳' . number_format((float) $i->due_amount, 2),
        ])]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if (!MoneyReceipt::ready()) {
            return $this->fail($request, 'আগে ড্যাশবোর্ড থেকে ডাটাবেজ Update চালান (money_receipts টেবিল নেই)।', 503);
        }

        $v = $request->validate([
            'kind'           => ['required', Rule::in(array_keys(MoneyReceipt::KINDS))],
            'receipt_date'   => 'required|date|before_or_equal:' . now()->addDay()->toDateString(),
            'author_id'      => 'nullable|integer',
            'invoice_id'     => 'nullable|integer',
            'party_name'     => 'required|string|max:191',
            'party_phone'    => ['nullable', 'string', 'max:40', 'regex:/^[0-9+\-\s()]*$/'],
            'party_address'  => 'nullable|string|max:255',
            'purpose'        => 'required|string|max:255',
            'category'       => 'nullable|string|max:100',
            'amount'         => 'required|numeric|min:1|max:99999999',
            'payment_method' => ['required', Rule::in(array_keys(MoneyReceipt::METHODS))],
            'reference'      => 'nullable|string|max:120',
            'notes'          => 'nullable|string|max:1000',
            'items'          => 'nullable|array|max:50',
            'items.*.title'  => 'nullable|string|max:255',
            'items.*.book_id'=> 'nullable|integer',
            'items.*.qty'    => 'nullable|integer|min:0|max:1000000',
            'items.*.price'  => 'nullable|numeric|min:0|max:10000000',
            'items.*.disc'   => 'nullable|numeric|min:0|max:100',
            'items.*.royalty'=> 'nullable|numeric|min:0|max:100',
        ], [
            'party_name.required' => 'নাম লিখুন।',
            'purpose.required'    => 'কীসের জন্য — লিখুন।',
            'amount.required'     => 'টাকার পরিমাণ দিন।',
            'amount.min'          => 'টাকার পরিমাণ কমপক্ষে ১ টাকা।',
        ]);

        $v['amount'] = round((float) $v['amount'], 2);
        $kind = $v['kind'];

        // book-sale lines (honorarium): the amount is what the lines add up to, worked out here
        $items = $kind === 'honorarium' ? MoneyReceipt::buildItems((array) $request->input('items', [])) : [];
        if ($items) {
            $v['amount'] = round(array_sum(array_column($items, 'net')), 2);
            if ($v['amount'] < 1) {
                throw ValidationException::withMessages(['amount' => 'বই বিক্রির হিসাবে লেখকের প্রাপ্য কমপক্ষে ১ টাকা হতে হবে।']);
            }
        }

        if ($kind === 'honorarium' && !empty($v['author_id']) && !\Modules\Author\Models\Author::whereKey($v['author_id'])->exists()) {
            throw ValidationException::withMessages(['author_id' => 'লেখক পাওয়া যায়নি।']);
        }
        if ($kind !== 'bill') {
            $v['invoice_id'] = null;
        }
        if ($kind !== 'honorarium') {
            $v['author_id'] = null;
        }

        try {
            $receipt = DB::transaction(function () use ($v, $kind, $items) {
                $no = MoneyReceipt::nextNumber($kind, (int) date('Y', strtotime($v['receipt_date'])));
                $userId = auth()->id();
                $paymentId = null;
                $entryId = null;
                $category = $v['category'] ?? null;

                if ($kind === 'bill' && !empty($v['invoice_id'])) {
                    // against an invoice: an invoice payment (which books the income itself)
                    $invoice = IdeaInvoice::whereKey($v['invoice_id'])->lockForUpdate()->first();
                    if (!$invoice || !$invoice->acceptsPayments()) {
                        throw ValidationException::withMessages(['invoice_id' => 'এই বিলে টাকা জমা নেওয়া যায় না।']);
                    }
                    if ($invoice->payments()->count() === 0 && (float) $invoice->paid_amount > 0) {
                        IdeaInvoicePayment::record($invoice, [
                            'payment_date' => $invoice->invoice_date, 'amount' => (float) $invoice->paid_amount,
                            'payment_method' => $invoice->payment_method ?: 'cash', 'note' => 'অগ্রিম জমা (Advance Payment)',
                        ], $invoice->created_by);
                    }
                    $due = $invoice->remainingDue();
                    if ($v['amount'] > $due + 0.004) {
                        throw ValidationException::withMessages(['amount' => 'বিল #' . $invoice->invoice_no . '-এর বকেয়া ৳' . number_format($due, 2) . ' — এর বেশি জমা নেওয়া যাবে না।']);
                    }
                    $payment = IdeaInvoicePayment::record($invoice, [
                        'payment_date'    => $v['receipt_date'],
                        'amount'          => $v['amount'],
                        'payment_method'  => $v['payment_method'],
                        'transaction_ref' => $v['reference'] ?? null,
                        'note'            => trim(($v['purpose'] ?? '') . ' · ' . $no),
                    ], $userId);
                    $invoice->recalculatePayments();
                    $paymentId = $payment->id;
                    $entryId = IdeaAccountingEntry::where('voucher_no', $payment->payment_no)->value('id');
                    $category = $invoice->incomeCategory();
                } else {
                    $isPay = $kind === 'honorarium';
                    $category = $isPay ? MoneyReceipt::HONORARIUM_CATEGORY : ($category ?: 'বিবিধ আয়');
                    $entryId = IdeaAccountingEntry::create([
                        'entry_no'       => IdeaAccountingEntry::generateEntryNo($isPay ? 'EXP' : 'INC'),
                        'type'           => $isPay ? 'expense' : 'income',
                        'category'       => $category,
                        'title'          => ($isPay ? 'লেখক সম্মানী — ' : 'অর্থ গ্রহণ — ') . $v['party_name'] . ' (' . $v['purpose'] . ')',
                        'amount'         => $v['amount'],
                        'entry_date'     => $v['receipt_date'],
                        'voucher_no'     => $no,
                        'payment_method' => $v['payment_method'],
                        'party_name'     => $v['party_name'],
                        'notes'          => trim((!empty($v['reference']) ? 'Ref: ' . $v['reference'] . "\n" : '') . ($v['notes'] ?? '')) ?: null,
                        'created_by'     => $userId,
                    ])->id;
                }

                return MoneyReceipt::create([
                    'receipt_no'          => $no,
                    'kind'                => $kind,
                    'receipt_date'        => $v['receipt_date'],
                    'author_id'           => $v['author_id'] ?? null,
                    'invoice_id'          => $v['invoice_id'] ?? null,
                    'invoice_payment_id'  => $paymentId,
                    'accounting_entry_id' => $entryId,
                    'party_name'          => $v['party_name'],
                    'party_phone'         => $v['party_phone'] ?? null,
                    'party_address'       => $v['party_address'] ?? null,
                    'purpose'             => $v['purpose'],
                    'category'            => $category,
                    'amount'              => $v['amount'],
                    'items'               => $items ?: null,
                    'payment_method'      => $v['payment_method'],
                    'reference'           => $v['reference'] ?? null,
                    'notes'               => $v['notes'] ?? null,
                    'created_by'          => $userId,
                ]);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Money receipt failed: ' . $e->getMessage());
            return $this->fail($request, 'রসিদ সংরক্ষণ করা যায়নি। আবার চেষ্টা করুন।', 500);
        }

        $this->log('money_receipt_created', "{$receipt->receipt_no} · {$receipt->party_name} · ৳{$receipt->amount}");
        $msg = ($receipt->isHonorarium() ? 'সম্মানী প্রদানের রসিদ ' : 'মানি রিসিট ') . $receipt->receipt_no . ' তৈরি হয়েছে';
        $printUrl = route('admin.receipts.print', $receipt);

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $msg, 'receipt_no' => $receipt->receipt_no, 'print_url' => $printUrl])
            : redirect()->route('admin.receipts.index')->with('success', $msg)->with('print_url', $printUrl);
    }

    public function print(Request $request, MoneyReceipt $receipt): View
    {
        $settings = IdeaAccountingController::getInvoiceSettings();
        $copies = $request->query('copies') === '2' ? 2 : 1;

        return view('admin.receipts.print', compact('receipt', 'settings', 'copies'));
    }

    /** Cancel a receipt and take its money out of the accounts again. */
    public function destroy(Request $request, MoneyReceipt $receipt): JsonResponse|RedirectResponse
    {
        DB::transaction(function () use ($receipt) {
            if ($receipt->invoice_payment_id && ($payment = IdeaInvoicePayment::find($receipt->invoice_payment_id))) {
                $invoice = $payment->invoice;
                if ($payment->payment_no) {
                    IdeaAccountingEntry::where('voucher_no', $payment->payment_no)->delete();
                }
                $payment->delete();
                $invoice?->recalculatePayments();
            } elseif ($receipt->accounting_entry_id) {
                IdeaAccountingEntry::whereKey($receipt->accounting_entry_id)->delete();
            }
            $receipt->delete();
        });
        $this->log('money_receipt_cancelled', "{$receipt->receipt_no} · ৳{$receipt->amount}");
        $msg = "রসিদ {$receipt->receipt_no} বাতিল হয়েছে, হিসাব থেকেও বাদ গেছে।";

        return $request->expectsJson() ? response()->json(['success' => true, 'message' => $msg]) : back()->with('success', $msg);
    }

    private function fail(Request $request, string $message, int $status): JsonResponse|RedirectResponse
    {
        return $request->expectsJson() ? response()->json(['success' => false, 'message' => $message], $status) : back()->withInput()->with('error', $message);
    }

    private function log(string $action, string $details): void
    {
        try {
            app(\App\Services\AdminAccessService::class)->log($action, $details);
        } catch (\Throwable) {
        }
    }
}
