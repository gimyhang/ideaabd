<?php

declare(strict_types=1);

namespace Modules\BulkOrder\Services;

use Modules\BulkOrder\Models\BulkOrder;

class BulkOrderService
{
    /**
     * Create a new bulk order.
     *
     * @param array $data
     * @return BulkOrder
     */
    public function create(array $data): BulkOrder
    {
        return BulkOrder::create($data);
    }

    /**
     * Add items to order.
     *
     * @param BulkOrder $order
     * @param array $items
     * @return void
     */
    public function addItems(BulkOrder $order, array $items): void
    {
        foreach ($items as $item) {
            $order->items()->create($item);
        }
    }

    /**
     * Calculate bulk discount.
     *
     * @param int $quantity
     * @return float
     */
    public function calculateDiscount(int $quantity): float
    {
        if ($quantity >= 500) return 25;
        if ($quantity >= 200) return 20;
        if ($quantity >= 100) return 15;
        if ($quantity >= 50) return 10;
        if ($quantity >= 20) return 5;
        return 0;
    }

    /**
     * Get educational discount.
     *
     * @param string $type
     * @return float
     */
    public function getEducationalDiscount(string $type): float
    {
        return match ($type) {
            'school' => 20,
            'college' => 25,
            'university' => 30,
            'library' => 35,
            default => 15,
        };
    }

    /**
     * Approve order.
     *
     * @param BulkOrder $order
     * @param array $data
     * @return BulkOrder
     */
    public function approve(BulkOrder $order, array $data = []): BulkOrder
    {
        $order->update([
            'status' => 'approved',
            'estimated_delivery_date' => $data['estimated_delivery_date'] ?? now()->addDays(7)->toDateString(),
            'notes' => $data['notes'] ?? $order->notes,
        ]);
        return $order;
    }

    /**
     * Reject order.
     *
     * @param BulkOrder $order
     * @param string $reason
     * @return BulkOrder
     */
    public function reject(BulkOrder $order, string $reason): BulkOrder
    {
        $order->update([
            'status' => 'rejected',
            'notes' => $reason,
        ]);
        return $order;
    }

    /**
     * Get pending orders.
     *
     * @param int $limit
     * @return mixed
     */
    public function getPending(int $limit = 10)
    {
        return BulkOrder::pending()->with('user')->latest()->take($limit)->get();
    }

    /**
     * Get statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total_pending' => BulkOrder::pending()->count(),
            'total_approved' => BulkOrder::approved()->count(),
            'total_orders' => BulkOrder::count(),
            'total_quantity' => BulkOrder::sum('quantity'),
            'total_revenue' => BulkOrder::where('status', 'completed')->sum('total_amount'),
        ];
    }
}
