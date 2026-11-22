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
     * Book-sale lines of an honorarium. For each book:
     *   sale price per copy = cover price less the sales commission %
     *   total sale          = copies × sale price
     *   writer's share      = total sale × royalty %
     * e.g. ৳200 less 25% = ৳150; 100 copies = ৳15,000; 15% royalty = ৳2,250.
     *
     * @return array<int, array{title:string, book_id:?int, qty:int, price:float, disc:float, unit:float, sale:float, royalty:float, net:float}>
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
            if ($title === '' || $qty < 1) {
                continue;
            }
            $out[] = self::line([
                'title' => mb_substr($title, 0, 255), 'book_id' => !empty($r['book_id']) ? (int) $r['book_id'] : null, 'qty' => $qty,
                'price' => $r['price'] ?? 0, 'disc' => $r['disc'] ?? 0, 'royalty' => $r['royalty'] ?? 0,
            ]);
        }

        return $out;
    }

    /** Works out one line; also reads lines saved before the royalty step (commission only, writer got the rest). */
    public static function line(array $it): array
    {
        $legacy = !array_key_exists('royalty', $it) && array_key_exists('pct', $it);
        $clamp = fn ($v) => min(100, max(0, round((float) $v, 2)));
        $qty = (int) ($it['qty'] ?? 0);
        $price = round(max(0, (float) ($it['price'] ?? 0)), 2);
        $disc = $clamp($legacy ? $it['pct'] : ($it['disc'] ?? 0));
        $royalty = $legacy ? 100.0 : $clamp($it['royalty'] ?? 0);
        $unit = round($price * (100 - $disc) / 100, 2);
        $sale = round($qty * $unit, 2);

        return [
            'title' => (string) ($it['title'] ?? ''), 'book_id' => $it['book_id'] ?? null, 'qty' => $qty, 'price' => $price,
            'disc' => $disc, 'unit' => $unit, 'sale' => $sale, 'royalty' => $royalty, 'net' => round($sale * $royalty / 100, 2),
        ];
    }

    /** Book lines, worked out (also for receipts saved with the older layout). */
    public function bookLines(): array
    {
        return array_map(fn ($it) => self::line((array) $it), $this->items ?: []);
    }

    /** Totals of the book lines. */
    public function itemTotals(): array
    {
        $items = $this->bookLines();
        $royalties = array_values(array_unique(array_column($items, 'royalty')));

        return [
            'qty'     => array_sum(array_column($items, 'qty')),
            'gross'   => round(array_sum(array_map(fn ($i) => $i['qty'] * $i['price'], $items)), 2),
            'sale'    => round(array_sum(array_column($items, 'sale')), 2),
            'net'     => round(array_sum(array_column($items, 'net')), 2),
            'royalty' => count($royalties) === 1 ? $royalties[0] : null, // one rate for every book
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
