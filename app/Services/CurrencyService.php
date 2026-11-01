<?php

namespace App\Services;

use App\Models\CurrencyRate;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    /**
     * Get all active currencies, with automatic default seeding.
     */
    public function getActiveCurrencies(): array
    {
        return Cache::remember('active_currencies_list', 3600, function () {
            if (CurrencyRate::count() === 0) {
                $this->seedDefaultCurrencies();
            }
            return CurrencyRate::where('is_active', true)->orderBy('is_default', 'desc')->get()->toArray();
        });
    }

    /** @var array<string, float>|null */
    private static ?array $ratesMap = null;

    /**
     * Get map of currency code => exchange_rate_to_bdt (cached in memory & store).
     */
    private function getRatesMap(): array
    {
        if (self::$ratesMap !== null) {
            return self::$ratesMap;
        }

        $map = [];
        foreach ($this->getActiveCurrencies() as $row) {
            $code = strtoupper((string) ($row['code'] ?? ''));
            if ($code !== '') {
                $map[$code] = (float) ($row['exchange_rate_to_bdt'] ?? 0);
            }
        }
        // Fallback defaults if not in active list
        $map += [
            'BDT' => 1.0,
            'USD' => 121.5,
            'EUR' => 131.2,
            'GBP' => 154.8,
        ];

        return self::$ratesMap = $map;
    }

    /**
     * Convert an amount in BDT to a target currency code.
     */
    public function convertFromBdt(float $amountBdt, string $targetCode = 'USD'): float
    {
        $code = strtoupper($targetCode);
        if ($code === 'BDT') {
            return $amountBdt;
        }

        $rates = $this->getRatesMap();
        $rate = $rates[$code] ?? null;
        if ($rate === null) {
            $currency = CurrencyRate::where('code', $code)->first();
            $rate = $currency ? (float) $currency->exchange_rate_to_bdt : 0.0;
            self::$ratesMap[$code] = $rate;
        }

        if ($rate <= 0) {
            return $amountBdt;
        }

        return round($amountBdt / $rate, 2);
    }

    /**
     * Convert an amount in a foreign currency back to BDT.
     */
    public function convertToBdt(float $amountForeign, string $sourceCode = 'USD'): float
    {
        $code = strtoupper($sourceCode);
        if ($code === 'BDT') {
            return $amountForeign;
        }

        $rates = $this->getRatesMap();
        $rate = $rates[$code] ?? null;
        if ($rate === null) {
            $currency = CurrencyRate::where('code', $code)->first();
            $rate = $currency ? (float) $currency->exchange_rate_to_bdt : 0.0;
            self::$ratesMap[$code] = $rate;
        }

        if ($rate <= 0) {
            return $amountForeign;
        }

        return round($amountForeign * $rate, 2);
    }

    /**
     * Format an amount with currency symbol.
     */
    public function format(float $amount, string $currencyCode = 'BDT'): string
    {
        $currency = CurrencyRate::where('code', strtoupper($currencyCode))->first();
        $symbol = $currency ? $currency->symbol : ($currencyCode === 'BDT' ? '৳' : '$');

        if (strtoupper($currencyCode) === 'BDT') {
            return $symbol . number_format($amount, 2);
        }

        return $symbol . number_format($amount, 2) . ' ' . strtoupper($currencyCode);
    }

    /**
     * Seed global default currencies if table is empty.
     */
    public function seedDefaultCurrencies(): void
    {
        $defaults = [
            ['code' => 'BDT', 'name' => 'Bangladeshi Taka', 'symbol' => '৳', 'exchange_rate_to_bdt' => 1.0000, 'is_default' => true, 'is_active' => true],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_bdt' => 121.5000, 'is_default' => false, 'is_active' => true],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'exchange_rate_to_bdt' => 131.2000, 'is_default' => false, 'is_active' => true],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'exchange_rate_to_bdt' => 154.8000, 'is_default' => false, 'is_active' => true],
            ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'د.إ', 'exchange_rate_to_bdt' => 33.1000, 'is_default' => false, 'is_active' => true],
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => '﷼', 'exchange_rate_to_bdt' => 32.4000, 'is_default' => false, 'is_active' => true],
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹', 'exchange_rate_to_bdt' => 1.4300, 'is_default' => false, 'is_active' => true],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'exchange_rate_to_bdt' => 88.5000, 'is_default' => false, 'is_active' => true],
        ];

        foreach ($defaults as $curr) {
            CurrencyRate::updateOrCreate(['code' => $curr['code']], $curr);
        }

        Cache::forget('active_currencies_list');
    }
}
