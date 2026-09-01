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

    /**
     * Convert an amount in BDT to a target currency code.
     */
    public function convertFromBdt(float $amountBdt, string $targetCode = 'USD'): float
    {
        if (strtoupper($targetCode) === 'BDT') {
            return $amountBdt;
        }

        $currency = CurrencyRate::where('code', strtoupper($targetCode))->first();
        if (!$currency || $currency->exchange_rate_to_bdt <= 0) {
            return $amountBdt;
        }

        return round($amountBdt / (float)$currency->exchange_rate_to_bdt, 2);
    }

    /**
     * Convert an amount in a foreign currency back to BDT.
     */
    public function convertToBdt(float $amountForeign, string $sourceCode = 'USD'): float
    {
        if (strtoupper($sourceCode) === 'BDT') {
            return $amountForeign;
        }

        $currency = CurrencyRate::where('code', strtoupper($sourceCode))->first();
        if (!$currency) {
            return $amountForeign;
        }

        return round($amountForeign * (float)$currency->exchange_rate_to_bdt, 2);
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
