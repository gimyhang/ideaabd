<?php

namespace App\Support;

/**
 * One rule for which price columns a book keeps, used by the full book form, Quick Add and Quick Edit.
 *
 *  - paperback: price / discount_price; no hardcover prices.
 *  - hardcover: hardcover_price / hardcover_discount_price, and `price` / `discount_price` mirror them
 *               (lists, carts, sorting and price filters read `price`). A hardcover price left empty takes
 *               the price typed in the other box.
 *  - both:      each binding keeps its own price; `price` falls back to the hardcover price when empty.
 *
 * An offer (discount) price is kept only when it is below its MRP; otherwise there is no discount.
 */
class BookPricing
{
    /**
     * @param  float|string|null  $price  paperback MRP (or the only MRP typed)
     * @return array{price: float, discount_price: ?float, hardcover_price: ?float, hardcover_discount_price: ?float}
     */
    public static function forBinding(?string $binding, $price, $discount, $hardPrice = null, $hardDiscount = null): array
    {
        $num = static fn ($v) => ($v === null || $v === '' || ! is_numeric($v)) ? null : round(max(0, (float) $v), 2);
        $offer = static fn (?float $base, ?float $d) => ($base !== null && $base > 0 && $d !== null && $d > 0 && $d < $base) ? $d : null;

        $p = $num($price);
        $d = $num($discount);
        $hp = $num($hardPrice);
        $hd = $num($hardDiscount);
        $binding = in_array($binding, ['paperback', 'hardcover', 'both'], true) ? $binding : 'paperback';

        if ($binding === 'hardcover') {
            if ($hp === null || $hp <= 0) {
                $hp = $p;
                $hd = $hd ?? $d;
            }
            $hd = $offer($hp, $hd);

            return ['price' => $hp ?? 0.0, 'discount_price' => $hd, 'hardcover_price' => $hp, 'hardcover_discount_price' => $hd];
        }

        if ($binding === 'both') {
            $hdOk = $offer($hp, $hd);
            if ($p === null || $p <= 0) {
                return ['price' => $hp ?? 0.0, 'discount_price' => $hdOk, 'hardcover_price' => $hp, 'hardcover_discount_price' => $hdOk];
            }

            return ['price' => $p, 'discount_price' => $offer($p, $d), 'hardcover_price' => ($hp ?: null), 'hardcover_discount_price' => $hdOk];
        }

        // paperback
        if ($p === null || $p <= 0) {
            return ['price' => $hp ?? 0.0, 'discount_price' => $offer($hp, $hd ?? $d), 'hardcover_price' => null, 'hardcover_discount_price' => null];
        }

        return ['price' => $p, 'discount_price' => $offer($p, $d), 'hardcover_price' => null, 'hardcover_discount_price' => null];
    }
}
