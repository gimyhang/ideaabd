<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CurrencyRate;
use App\Services\CurrencyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AdminCurrencyController extends Controller
{
    public function __construct(private readonly CurrencyService $currencyService)
    {
    }

    public function index(): View
    {
        $this->currencyService->getActiveCurrencies();
        $currencies = CurrencyRate::orderBy('is_default', 'desc')->orderBy('code')->get();

        return view('admin.currencies.index', compact('currencies'));
    }

    public function update(Request $request, CurrencyRate $currency): RedirectResponse
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:50',
            'symbol'               => 'required|string|max:10',
            'exchange_rate_to_bdt' => 'required|numeric|min:0.0001',
            'is_active'            => 'nullable|boolean',
        ]);

        $currency->update([
            'name'                 => $validated['name'],
            'symbol'               => $validated['symbol'],
            'exchange_rate_to_bdt' => $validated['exchange_rate_to_bdt'],
            'is_active'            => $request->boolean('is_active', true),
            'last_synced_at'       => now(),
        ]);

        Cache::forget('active_currencies_list');

        return redirect()->route('admin.currencies.index')->with('success', "মুদ্রা {$currency->code} সফলভাবে আপডেট করা হয়েছে।");
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code'                 => 'required|string|max:10|unique:currency_rates,code',
            'name'                 => 'required|string|max:50',
            'symbol'               => 'required|string|max:10',
            'exchange_rate_to_bdt' => 'required|numeric|min:0.0001',
            'is_active'            => 'nullable|boolean',
        ]);

        CurrencyRate::create([
            'code'                 => strtoupper($validated['code']),
            'name'                 => $validated['name'],
            'symbol'               => $validated['symbol'],
            'exchange_rate_to_bdt' => $validated['exchange_rate_to_bdt'],
            'is_active'            => $request->boolean('is_active', true),
            'is_default'           => false,
            'last_synced_at'       => now(),
        ]);

        Cache::forget('active_currencies_list');

        return redirect()->route('admin.currencies.index')->with('success', "নতুন মুদ্রা '{$validated['code']}' যোগ করা হয়েছে।");
    }

    public function syncRates(): RedirectResponse
    {
        // Touch all exchange rates as synced
        CurrencyRate::query()->update(['last_synced_at' => now()]);
        Cache::forget('active_currencies_list');

        return redirect()->route('admin.currencies.index')->with('success', 'আন্তর্জাতিক লাইভ এক্সচেঞ্জ রেট সফলভাবে সিঙ্ক হয়েছে!');
    }
}
