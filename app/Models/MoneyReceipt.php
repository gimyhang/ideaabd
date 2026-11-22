<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A printed money receipt: honorarium paid to a writer, or bill money received.
 */
class MoneyReceipt extends Model
{
    use SoftDeletes;

    public const KINDS = [
        'honorarium' => ['label' => 'Honorarium', 'bn' => 'লেখক সম্মানী প্রদান', 'prefix' => 'HON'],
        'bill'       => ['label' => 'Receipt', 'bn' => 'বিলের অর্থ গ্রহণ', 'prefix' => 'MR'],
    ];

    public const METHODS = [
        'cash' => 'ক্যাশ', 'bkash' => 'বিকাশ', 'nagad' => 'নগদ', 'rocket' => 'রকেট',
        'bank' => 'ব্যাংক ট্রান্সফার', 'cheque' => 'চেক',
    ];

    public const HONORARIUM_CATEGORY = 'সম্মানী ও রয়্যালটি';

    protected $fillable = [
        'receipt_no', 'kind', 'receipt_date', 'author_id', 'invoice_id', 'invoice_payment_id', 'accounting_entry_id',
        'party_name', 'party_phone', 'party_address', 'purpose', 'category', 'amount', 'items', 'payment_method', 'reference',
        'notes', 'created_by',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'amount'       => 'decimal:2',
        'items'        => 'array',
    ];

    /**
     * Clean book-sale lines and work out each one: sale = copies × price,
     * commission = sale × % ÷ 100, the writer gets sale − commission.
     *
     * @return array<int, array{title:string, book_id:?int, qty:int, price:float, sale:float, pct:float, commission:float, net:float}>
     */
    public static function buildItems(array $rows): array
    {
        $out = [];
        foreach (array_slice($rows, 0, 50) as $r) {
            if (!is_array($r)) {
                continue;
            }
            $title = trim((string) ($r['title'] ?? ''));
            $qty = (int) ($r['qty'] ?? 0);
            $price = round(max(0, (float) ($r['price'] ?? 0)), 2);
            if ($title === '' || $qty < 1) {
                continue;
            }
            $pct = min(100, max(0, round((float) ($r['pct'] ?? 0), 2)));
            $sale = round($qty * $price, 2);
            $commission = round($sale * $pct / 100, 2);
            $out[] = [
                'title' => mb_substr($title, 0, 255), 'book_id' => !empty($r['book_id']) ? (int) $r['book_id'] : null,
                'qty' => $qty, 'price' => $price, 'sale' => $sale, 'pct' => $pct, 'commission' => $commission,
                'net' => round($sale - $commission, 2),
            ];
        }

        return $out;
    }

    /** Totals of the book lines. */
    public function itemTotals(): array
    {
        $items = $this->items ?: [];

        return [
            'qty'        => array_sum(array_column($items, 'qty')),
            'sale'       => round(array_sum(array_column($items, 'sale')), 2),
            'commission' => round(array_sum(array_column($items, 'commission')), 2),
            'net'        => round(array_sum(array_column($items, 'net')), 2),
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(IdeaInvoice::class, 'invoice_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isHonorarium(): bool
    {
        return $this->kind === 'honorarium';
    }

    public function methodLabel(): string
    {
        return self::METHODS[$this->payment_method] ?? (string) $this->payment_method;
    }

    /** Next number of the year for a kind: HON-2026-0001, MR-2026-0001. Call inside a transaction. */
    public static function nextNumber(string $kind, ?int $year = null): string
    {
        $prefix = (self::KINDS[$kind]['prefix'] ?? 'MR') . '-' . ($year ?? (int) date('Y')) . '-';
        $last = static::withTrashed()->where('receipt_no', 'like', $prefix . '%')->lockForUpdate()
            ->orderByDesc('receipt_no')->value('receipt_no');
        $n = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }

    public static function ready(): bool
    {
        try {
            return \App\Support\SchemaCache::has('money_receipts');
        } catch (\Throwable) {
            return false;
        }
    }
}
