<?php

namespace App\Support;

use App\Models\PublisherPurchase;
use App\Models\PublisherPurchaseItem;
use App\Models\RawMaterial;
use App\Models\RawMaterialMove;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Raw-material stock book.
 *
 * Every line of a raw-material purchase (purchase_category = raw_materials) becomes an "in" move of a material;
 * the press then records what it used for which book / job ("out"), count corrections ("adjust") and the
 * stock it had when the book was started ("opening"). Balance = opening + in − out ± adjust, valued at the
 * weighted average cost of everything that came in.
 *
 * Purchase lines are deleted and re-created on every purchase edit, so the moves of a purchase are rebuilt
 * from scratch by syncPurchase() — call it after store, update and destroy.
 */
class RawMaterialStock
{
    private const SCHEMA_FLAG = 'rms:schema:v1';
    private const BACKFILL_FLAG = 'rms:backfill:v1';

    // ── schema ───────────────────────────────────────────────────────────────────────────────────────────────

    public static function createTables(): void
    {
        if (!Schema::hasTable('raw_materials')) {
            Schema::create('raw_materials', function (Blueprint $t) {
                $t->id();
                $t->string('key', 191)->unique();          // normalized name|spec|unit — matches purchase lines
                $t->string('name');
                $t->string('group', 20)->default('other')->index();
                $t->string('unit', 30)->default('পিস');
                $t->string('spec')->nullable();            // size / GSM / quality
                $t->decimal('reorder_level', 14, 3)->default(0);
                $t->text('notes')->nullable();
                $t->boolean('is_active')->default(true);
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('raw_material_moves')) {
            Schema::create('raw_material_moves', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('raw_material_id')->index();
                $t->string('type', 10)->index();           // opening | in | out | adjust
                $t->decimal('qty', 14, 3);                 // adjust: signed; others: positive
                $t->decimal('unit_cost', 14, 4)->default(0);
                $t->decimal('amount', 14, 2)->default(0);  // value of the move (always positive, except a negative adjust)
                $t->unsignedBigInteger('purchase_id')->nullable()->index();
                $t->unsignedBigInteger('purchase_item_id')->nullable();
                $t->unsignedBigInteger('book_id')->nullable()->index();
                $t->string('job')->nullable();            // print job / book name when not in the catalogue
                $t->string('note', 500)->nullable();
                $t->date('moved_at')->index();
                $t->unsignedBigInteger('created_by')->nullable();
                $t->timestamps();
            });
        }
    }

    /** Creates the tables on the web database and fills them from the existing purchases (once). */
    public static function ensureSchema(): bool
    {
        if (Cache::get(self::SCHEMA_FLAG) && Cache::get(self::BACKFILL_FLAG)) {
            return true;
        }
        try {
            self::createTables();
            Cache::put(self::SCHEMA_FLAG, true, 86400);
            if (!Cache::get(self::BACKFILL_FLAG)) {
                self::backfill();
                Cache::forever(self::BACKFILL_FLAG, true);
            }
            return true;
        } catch (\Throwable $e) {
            Log::warning('RawMaterialStock::ensureSchema: ' . $e->getMessage());
            return false;
        }
    }

    public static function ready(): bool
    {
        try {
            return SchemaCache::has('raw_material_moves') || Schema::hasTable('raw_material_moves');
        } catch (\Throwable) {
            return false;
        }
    }

    /** Re-reads every raw-material purchase (missing ones are added, edited ones corrected). Returns the count. */
    public static function backfill(): int
    {
        $n = 0;
        PublisherPurchase::where('purchase_category', 'raw_materials')->with('items')->orderBy('id')
            ->chunkById(100, function ($rows) use (&$n) {
                foreach ($rows as $p) {
                    self::syncPurchase($p, false);
                    $n++;
                }
            });
        // moves of purchases that were deleted
        RawMaterialMove::whereNotNull('purchase_id')
            ->whereNotIn('purchase_id', PublisherPurchase::where('purchase_category', 'raw_materials')->select('id'))
            ->delete();

        return $n;
    }

    // ── purchases → "in" moves ───────────────────────────────────────────────────────────────────────────────

    /** Rebuilds the "in" moves of one purchase. Safe to call for any purchase (non-raw ones just lose their moves). */
    public static function syncPurchase(PublisherPurchase $purchase, bool $guard = true): void
    {
        // no DDL here: this runs inside the purchase transaction (CREATE TABLE would commit it on MySQL).
        // ensureSchema() is called by the controller before the transaction starts.
        if ($guard && !self::ready()) {
            return;
        }
        try {
            RawMaterialMove::where('purchase_id', $purchase->id)->delete();
            if ($purchase->trashed() || $purchase->purchase_category !== 'raw_materials') {
                return;
            }
            $items = $purchase->relationLoaded('items') ? $purchase->items : $purchase->items()->get();
            // the invoice discount lowers the real cost of every line in proportion
            $total = (float) $purchase->total_amount;
            $ratio = $total > 0 ? max(0, min(1, (float) $purchase->grand_total / $total)) : 1;

            foreach ($items as $item) {
                [$qty, $unit] = self::lineQty($item);
                if ($qty <= 0) {
                    continue;
                }
                $material = self::materialFor($item, $unit);
                $amount = round((float) $item->subtotal * $ratio, 2);
                RawMaterialMove::create([
                    'raw_material_id'  => $material->id,
                    'type'             => 'in',
                    'qty'              => $qty,
                    'unit_cost'        => $qty > 0 ? round($amount / $qty, 4) : 0,
                    'amount'           => $amount,
                    'purchase_id'      => $purchase->id,
                    'purchase_item_id' => $item->id,
                    'note'             => $purchase->party_name,
                    'moved_at'         => optional($purchase->purchase_date)->toDateString() ?? now()->toDateString(),
                    'created_by'       => $purchase->created_by,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning("RawMaterialStock::syncPurchase #{$purchase->id}: " . $e->getMessage());
        }
    }

    /**
     * The counted quantity of a purchase line, as it was billed (PublisherPurchaseController: quantity when given,
     * else reams) and its unit. A line billed by the ream is kept in reams.
     */
    public static function lineQty(PublisherPurchaseItem $item): array
    {
        $qty = (float) $item->quantity;
        $reams = (float) $item->reams_quantity;
        $cost = (float) $item->unit_cost_price;
        $sub = (float) $item->subtotal;
        // quantity is stored as an integer: the billed count is subtotal / rate when that is exact enough
        $billed = $cost > 0 ? round($sub / $cost, 3) : ($qty > 0 ? $qty : $reams);
        if ($billed <= 0) {
            $billed = $qty > 0 ? $qty : $reams;
        }
        $unit = trim((string) $item->unit) ?: 'পিস';
        if ($reams > 0 && abs($billed - $reams) < 0.0015) {
            $unit = 'রিম';
        }

        return [$billed, $unit];
    }

    public static function materialFor(PublisherPurchaseItem $item, string $unit): RawMaterial
    {
        $name = trim((string) ($item->item_name ?: $item->book_title)) ?: 'নাম ছাড়া উপকরণ';
        $spec = collect([$item->size_spec, $item->quality_spec, $item->author_name])
            ->map(fn ($v) => trim((string) $v))
            // the old form saved the hidden book "cover type" into quality_spec of raw lines
            ->filter(fn ($v) => $v !== '' && !in_array(mb_strtolower($v), ['paperback', 'hardcover', 'standard'], true))
            ->unique()->implode(', ');
        $key = self::key($name, $spec, $unit);

        $m = RawMaterial::where('key', $key)->first();
        if ($m) {
            return $m;
        }

        return RawMaterial::create([
            'key'   => $key,
            'name'  => mb_substr($name, 0, 250),
            'spec'  => $spec !== '' ? mb_substr($spec, 0, 250) : null,
            'unit'  => $unit,
            'group' => self::guessGroup($name . ' ' . $spec),
        ]);
    }

    public static function key(string $name, ?string $spec, string $unit): string
    {
        $norm = fn ($s) => preg_replace('/\s+/u', ' ', mb_strtolower(trim((string) $s)));
        $k = $norm($name) . '|' . $norm($spec) . '|' . $norm($unit);

        return mb_strlen($k) > 180 ? mb_substr($k, 0, 140) . '#' . sha1($k) : $k;
    }

    /** Group from the words in the name (Bangla and English). */
    public static function guessGroup(string $text): string
    {
        $t = mb_strtolower($text);
        $map = [
            // services first: "ছাপা বিল", "বাঁধাই মজুরি" are bills, not materials
            'service'    => ['মজুরি', 'মজুরী', 'বিল', 'চার্জ', 'ছাপা', 'ছাপানো', 'মুদ্রণ', 'প্রিন্টিং', 'printing', 'charge', 'labour', 'labor', 'service', 'ডিজাইন', 'design', 'ভাড়া', 'পরিবহন', 'transport', 'কাটিং চার্জ'],
            'plate'      => ['প্লেট', 'plate', 'ctp', 'সিটিপি'],
            'ink'        => ['কালি', 'ink', 'কালার', 'toner', 'টোনার'],
            'board'      => ['বোর্ড', 'board', 'আর্ট কার্ড', 'আর্টকার্ড', 'art card', 'card', 'কার্ড', 'মিলবোর্ড', 'ডুপ্লেক্স', 'duplex'],
            'paper'      => ['কাগজ', 'paper', 'অফসেট', 'offset', 'নিউজপ্রিন্ট', 'newsprint', 'নিউজ', 'gsm', 'জিএসএম', 'বসুন্ধরা', 'মেপলিথো', 'হোয়াইট প্রিন্ট', 'আর্ট পেপার', 'রিম'],
            'lamination' => ['ল্যামিনেশন', 'লেমিনেশন', 'laminat', 'ফয়েল', 'foil', 'ইউভি', 'uv', 'ম্যাট', 'matt', 'গ্লসি', 'gloss'],
            'binding'    => ['বাঁধাই', 'বাধাই', 'binding', 'সুতা', 'সুতো', 'thread', 'আঠা', 'গাম', 'glue', 'gum', 'সেলাই', 'পিন', 'staple', 'রেক্সিন', 'rexine', 'কাপড়'],
            'packaging'  => ['প্যাক', 'pack', 'কার্টন', 'carton', 'বস্তা', 'পলি', 'poly', 'বক্স', 'box', 'টেপ', 'tape'],
        ];
        foreach ($map as $group => $words) {
            foreach ($words as $w) {
                if (str_contains($t, $w)) {
                    return $group;
                }
            }
        }

        return 'other';
    }

    // ── balances ─────────────────────────────────────────────────────────────────────────────────────────────

    /**
     * Per-material totals: [id => [in_qty, in_amount, out_qty, out_amount, adj_qty, adj_amount, balance, avg_cost, value, last_at]].
     * $until limits to moves on or before a date.
     */
    public static function totals(?array $materialIds = null, ?string $until = null): array
    {
        $rows = DB::table('raw_material_moves')
            ->when($materialIds !== null, fn ($q) => $q->whereIn('raw_material_id', $materialIds ?: [0]))
            ->when($until, fn ($q) => $q->where('moved_at', '<=', $until))
            ->groupBy('raw_material_id')
            ->selectRaw("raw_material_id,
                SUM(CASE WHEN type IN ('in','opening') THEN qty ELSE 0 END) AS in_qty,
                SUM(CASE WHEN type IN ('in','opening') THEN amount ELSE 0 END) AS in_amount,
                SUM(CASE WHEN type = 'out' THEN qty ELSE 0 END) AS out_qty,
                SUM(CASE WHEN type = 'out' THEN amount ELSE 0 END) AS out_amount,
                SUM(CASE WHEN type = 'adjust' THEN qty ELSE 0 END) AS adj_qty,
                SUM(CASE WHEN type = 'adjust' THEN amount ELSE 0 END) AS adj_amount,
                MAX(moved_at) AS last_at")
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $inQty = (float) $r->in_qty;
            $avg = $inQty > 0 ? (float) $r->in_amount / $inQty : 0.0;
            $bal = round($inQty - (float) $r->out_qty + (float) $r->adj_qty, 3);
            $out[(int) $r->raw_material_id] = [
                'in_qty'     => $inQty,
                'in_amount'  => (float) $r->in_amount,
                'out_qty'    => (float) $r->out_qty,
                'out_amount' => (float) $r->out_amount,
                'adj_qty'    => (float) $r->adj_qty,
                'adj_amount' => (float) $r->adj_amount,
                'balance'    => $bal,
                'avg_cost'   => round($avg, 4),
                'value'      => round(max(0, $bal) * $avg, 2),
                'last_at'    => $r->last_at,
            ];
        }

        return $out;
    }

    public static function emptyTotals(): array
    {
        return ['in_qty' => 0, 'in_amount' => 0, 'out_qty' => 0, 'out_amount' => 0, 'adj_qty' => 0, 'adj_amount' => 0,
            'balance' => 0, 'avg_cost' => 0, 'value' => 0, 'last_at' => null];
    }

    /** Average cost of a material up to a date (used to value what is taken out). */
    public static function averageCost(int $materialId, ?string $until = null): float
    {
        return (float) (self::totals([$materialId], $until)[$materialId]['avg_cost'] ?? 0);
    }

    /** Moves of a material, oldest first, with the running balance. */
    public static function ledger(RawMaterial $material, ?string $from = null, ?string $to = null): array
    {
        $opening = 0.0;
        if ($from) {
            $opening = (float) DB::table('raw_material_moves')->where('raw_material_id', $material->id)->where('moved_at', '<', $from)
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'out' THEN -qty ELSE qty END), 0) AS b")->value('b');
        }
        $moves = RawMaterialMove::with(['purchase:id,purchase_no', 'book:id,title', 'creator:id,name'])
            ->where('raw_material_id', $material->id)
            ->when($from, fn ($q) => $q->where('moved_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('moved_at', '<=', $to))
            ->orderBy('moved_at')->orderBy('id')->get();

        $bal = $opening;
        $rows = [];
        foreach ($moves as $m) {
            $bal = round($bal + $m->signedQty(), 3);
            $rows[] = ['move' => $m, 'balance' => $bal];
        }

        return ['opening' => $opening, 'rows' => $rows, 'closing' => $bal];
    }
}
