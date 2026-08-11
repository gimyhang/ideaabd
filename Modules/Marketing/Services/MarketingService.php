<?php

declare(strict_types=1);

namespace Modules\Marketing\Services;

use Modules\Marketing\Models\HotDeal;
use Modules\Marketing\Models\Promotion;

class MarketingService
{
    /**
     * Get active promotions.
     *
     * @param int $limit
     * @return mixed
     */
    public function getActivePromotions(int $limit = 10)
    {
        return Promotion::active()
            ->where('is_featured', true)
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get active hot deals.
     *
     * @param int $limit
     * @return mixed
     */
    public function getActiveHotDeals(int $limit = 6)
    {
        return HotDeal::featured()->take($limit)->get();
    }

    /**
     * Validate promotion code.
     *
     * @param string $code
     * @return Promotion|null
     */
    public function validatePromotion(string $code): ?Promotion
    {
        $promotion = Promotion::where('code', strtoupper($code))->first();

        if ($promotion && $promotion->isValid()) {
            return $promotion;
        }

        return null;
    }

    /**
     * Apply promotion to order.
     *
     * @param string $code
     * @param float $orderAmount
     * @return array
     */
    public function applyPromotion(string $code, float $orderAmount): array
    {
        $promotion = $this->validatePromotion($code);

        if (!$promotion) {
            return ['success' => false, 'message' => 'Invalid or expired promotion code'];
        }

        $discount = $promotion->calculateDiscount($orderAmount);

        if ($discount === 0) {
            return ['success' => false, 'message' => 'This promotion does not apply to your order'];
        }

        $promotion->incrementUsage();

        return [
            'success' => true,
            'discount' => $discount,
            'code' => $code,
            'final_amount' => $orderAmount - $discount,
        ];
    }

    /**
     * Get trending hot deals.
     *
     * @param int $limit
     * @return mixed
     */
    public function getTrendingDeals(int $limit = 5)
    {
        return HotDeal::active()
            ->orderBy('order_count', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get expiring hot deals.
     *
     * @param int $hoursLimit
     * @return mixed
     */
    public function getExpiringDeals(int $hoursLimit = 24)
    {
        return HotDeal::active()
            ->where('ended_at', '<=', now()->addHours($hoursLimit))
            ->where('ended_at', '>', now())
            ->orderBy('ended_at')
            ->get();
    }

    /**
     * Create promotion.
     *
     * @param array $data
     * @return Promotion
     */
    public function createPromotion(array $data): Promotion
    {
        $data['code'] = strtoupper($data['code'] ?? uniqid('PROMO'));
        return Promotion::create($data);
    }

    /**
     * Create hot deal.
     *
     * @param array $data
     * @return HotDeal
     */
    public function createHotDeal(array $data): HotDeal
    {
        $data['slug'] = $data['slug'] ?? str_slug($data['title']);
        return HotDeal::create($data);
    }

    /**
     * Get marketing statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'active_promotions' => Promotion::active()->count(),
            'active_hot_deals' => HotDeal::active()->count(),
            'total_promotion_uses' => Promotion::sum('usage_count'),
            'top_deal' => HotDeal::active()->orderBy('order_count', 'desc')->first(),
            'expiring_soon' => $this->getExpiringDeals(24)->count(),
        ];
    }
}
