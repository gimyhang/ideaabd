<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintRate extends Model
{
    public const GROUPS = ['Paper', 'CTP', 'Press', 'Lamination', 'Cutting', 'Binding', 'Packing', 'Transport', 'Design', 'Other'];

    /** Rates the old calculator shipped with (BDT per ream) — loaded once into an empty sheet. */
    public const PAPER_STARTER = [
        ['80', 'White', 3200], ['70', 'White', 2800], ['70', 'Cream', 3500], ['80', 'Cream', 4000],
        ['100', 'Art', 4500], ['120', 'Matt', 5200], ['65', 'White', 2400], ['55', 'Newsprint', 2000], ['80', 'Kraft', 3000],
    ];

    protected $fillable = ['group', 'item', 'kind', 'rate', 'note', 'updated_by'];

    protected $casts = ['rate' => 'decimal:2'];

    public static function seedStarterIfEmpty(): void
    {
        if (static::query()->exists()) {
            return;
        }

        foreach (self::PAPER_STARTER as [$item, $kind, $rate]) {
            static::query()->firstOrCreate(['group' => 'Paper', 'item' => $item, 'kind' => $kind], ['rate' => $rate]);
        }
    }
}
