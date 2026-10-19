<?php

declare(strict_types=1);

namespace Modules\Inventory\Services;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Book\Models\Book;
use Modules\Inventory\Models\Inventory;
use Modules\Inventory\Models\InventoryMovementLog;
use Modules\Inventory\Models\Location;
use Modules\Inventory\Models\StockTransfer;
use Modules\Inventory\Models\StockTransferItem;
use Modules\Inventory\Models\StockWastage;

class InventoryService
{
    /**
     * 1. Production In:
     * ছাপা/বাঁধাই শেষে সরাসরি Central Warehouse স্টকে Stock In হওয়া।
     */
    public function recordProductionIn(
        int $bookId,
        int $quantity,
        ?int $warehouseLocationId = null,
        ?string $batchNo = null,
        ?string $notes = null,
        ?int $userId = null
    ): Inventory {
        if ($quantity <= 0) {
            throw new Exception('Stock in quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($bookId, $quantity, $warehouseLocationId, $batchNo, $notes, $userId) {
            // Find warehouse: specified or central warehouse fallback
            $location = null;
            if ($warehouseLocationId) {
                $location = Location::where('id', $warehouseLocationId)->where('is_active', true)->first();
            }
            if (!$location) {
                // Central warehouse, created on first use when none exists yet.
                $location = Location::central()->active()->first() ?? Location::warehouses()->active()->first() ?? $this->ensureCentralWarehouse();
            }

            $book = Book::findOrFail($bookId);

            // Row-level lock on inventory record
            $inventory = Inventory::where('book_id', $book->id)
                ->where('location_id', $location->id)
                ->lockForUpdate()
                ->first();

            $prevQuantity = $inventory ? $inventory->quantity : 0;
            $newQuantity = $prevQuantity + $quantity;

            if ($inventory) {
                $inventory->update(['quantity' => $newQuantity]);
            } else {
                $inventory = Inventory::create([
                    'book_id' => $book->id,
                    'location_id' => $location->id,
                    'quantity' => $newQuantity,
                    'min_alert_quantity' => 10,
                ]);
            }

            // Sync aggregate book stock
            $book->increment('stock_quantity', $quantity);

            // Audit Movement Log
            InventoryMovementLog::create([
                'book_id' => $book->id,
                'location_id' => $location->id,
                'type' => 'production_in',
                'quantity_change' => $quantity,
                'quantity_before' => $prevQuantity,
                'quantity_after' => $newQuantity,
                'reference_type' => 'ProductionBatch',
                'reference_id' => null,
                'notes' => ($batchNo ? "ব্যাচ নং: {$batchNo}. " : '') . ($notes ?? 'নতুন মুদ্রণ/বাঁধাই পরবর্তী স্টক ইন'),
                'user_id' => $userId,
            ]);

            return $inventory->fresh(['book', 'location']);
        });
    }

    /**
     * 2. Create Stock Transfer Challan (Warehouse → Showroom)
     */
    public function createTransfer(
        int $fromLocationId,
        int $toLocationId,
        array $items,
        ?string $notes = null,
        ?int $userId = null
    ): StockTransfer {
        if ($fromLocationId === $toLocationId) {
            throw new Exception('উৎস এবং গন্তব্য লোকেশন একই হতে পারবে না।');
        }

        if (empty($items)) {
            throw new Exception('চালানে অন্তত একটি বই যুক্ত করতে হবে।');
        }

        $fromLocation = Location::findOrFail($fromLocationId);
        $toLocation = Location::findOrFail($toLocationId);

        return DB::transaction(function () use ($fromLocation, $toLocation, $items, $notes, $userId) {
            // Validate availability at source location
            foreach ($items as $item) {
                $bookId = (int) $item['book_id'];
                $qty = (int) $item['quantity'];

                if ($qty <= 0) {
                    throw new Exception("বই আইডি {$bookId}-এর পরিমাণ শূন্য বা ঋণাত্মক হতে পারবে না।");
                }

                $availableStock = Inventory::where('book_id', $bookId)
                    ->where('location_id', $fromLocation->id)
                    ->value('quantity') ?? 0;

                if ($availableStock < $qty) {
                    $bookTitle = Book::where('id', $bookId)->value('title') ?? "ID: {$bookId}";
                    throw new Exception("{$fromLocation->name}-এ '{$bookTitle}' বইটির পর্যাপ্ত স্টক নেই (মজুদ: {$availableStock}, চাওয়া হয়েছে: {$qty})।");
                }
            }

            // Generate unique Challan No: CH-YYYYMMDD-XXXX
            $datePrefix = date('Ymd');
            $randomCode = strtoupper(Str::random(4));
            $challanNo = "CH-{$datePrefix}-{$randomCode}";

            $transfer = StockTransfer::create([
                'challan_no' => $challanNo,
                'from_location_id' => $fromLocation->id,
                'to_location_id' => $toLocation->id,
                'status' => 'pending',
                'transfer_date' => now()->toDateString(),
                'notes' => $notes,
                'created_by' => $userId,
            ]);

            foreach ($items as $item) {
                $book = Book::find($item['book_id']);
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'book_id' => $item['book_id'],
                    'quantity_sent' => (int) $item['quantity'],
                    'quantity_received' => null,
                    'unit_cost' => $item['unit_cost'] ?? ($book?->cost_price ?? $book?->price),
                    'remarks' => $item['remarks'] ?? null,
                ]);
            }

            return $transfer->load(['fromLocation', 'toLocation', 'items.book', 'creator']);
        });
    }

    /**
     * 3. Receive Stock Transfer Challan ( शोरুম রিসিভ করলে DB Transaction-এ স্টক ট্রান্সফার সম্পন্ন)
     */
    public function receiveTransfer(
        int $transferId,
        ?array $receivedItemMap = null,
        ?int $userId = null
    ): StockTransfer {
        return DB::transaction(function () use ($transferId, $receivedItemMap, $userId) {
            $transfer = StockTransfer::with('items.book')->lockForUpdate()->findOrFail($transferId);

            if ($transfer->isReceived()) {
                throw new Exception('এই চালানটি আগেই রিসিভ করা হয়েছে।');
            }

            if ($transfer->isCancelled()) {
                throw new Exception('বাতিলকৃত চালান রিসিভ করা সম্ভব নয়।');
            }

            foreach ($transfer->items as $item) {
                $qtySent = $item->quantity_sent;
                // Allow custom received quantity or default to sent quantity
                $qtyReceived = isset($receivedItemMap[$item->id])
                    ? (int) $receivedItemMap[$item->id]
                    : (isset($receivedItemMap[$item->book_id]) ? (int) $receivedItemMap[$item->book_id] : $qtySent);

                if ($qtyReceived < 0) {
                    throw new Exception('রিসিভ পরিমাণ ঋণাত্মক হতে পারবে না।');
                }

                // 1. Lock and decrement source location inventory
                $fromInventory = Inventory::where('book_id', $item->book_id)
                    ->where('location_id', $transfer->from_location_id)
                    ->lockForUpdate()
                    ->first();

                $fromCurrentQty = $fromInventory ? $fromInventory->quantity : 0;
                if ($fromCurrentQty < $qtySent) {
                    $bookTitle = $item->book?->title ?? "Book #{$item->book_id}";
                    throw new Exception("উৎস লোকেশনে '{$bookTitle}' এর স্টক অপর্যাপ্ত। বর্তমান স্টক: {$fromCurrentQty}, প্রেরিত: {$qtySent}");
                }

                $fromNewQty = $fromCurrentQty - $qtySent;
                $fromInventory->update(['quantity' => $fromNewQty]);

                // Movement Log for Source (transfer_out)
                InventoryMovementLog::create([
                    'book_id' => $item->book_id,
                    'location_id' => $transfer->from_location_id,
                    'type' => 'transfer_out',
                    'quantity_change' => -$qtySent,
                    'quantity_before' => $fromCurrentQty,
                    'quantity_after' => $fromNewQty,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'notes' => "চালান নং {$transfer->challan_no} মারফত স্থানান্তর আউট",
                    'user_id' => $userId,
                ]);

                // 2. Lock and increment destination location inventory
                $toInventory = Inventory::where('book_id', $item->book_id)
                    ->where('location_id', $transfer->to_location_id)
                    ->lockForUpdate()
                    ->first();

                $toPrevQty = $toInventory ? $toInventory->quantity : 0;
                $toNewQty = $toPrevQty + $qtyReceived;

                if ($toInventory) {
                    $toInventory->update(['quantity' => $toNewQty]);
                } else {
                    Inventory::create([
                        'book_id' => $item->book_id,
                        'location_id' => $transfer->to_location_id,
                        'quantity' => $toNewQty,
                        'min_alert_quantity' => 5,
                    ]);
                }

                // Movement Log for Destination (transfer_in)
                InventoryMovementLog::create([
                    'book_id' => $item->book_id,
                    'location_id' => $transfer->to_location_id,
                    'type' => 'transfer_in',
                    'quantity_change' => +$qtyReceived,
                    'quantity_before' => $toPrevQty,
                    'quantity_after' => $toNewQty,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'notes' => "চালান নং {$transfer->challan_no} মারফত স্থানান্তর ইন",
                    'user_id' => $userId,
                ]);

                // Record discrepancy if sent != received
                $item->update([
                    'quantity_received' => $qtyReceived,
                ]);
            }

            $transfer->update([
                'status' => 'received',
                'received_date' => now(),
                'received_by' => $userId,
            ]);

            return $transfer->fresh(['fromLocation', 'toLocation', 'items.book', 'receiver']);
        });
    }

    /**
     * Cancel a pending or in-transit transfer.
     */
    public function cancelTransfer(int $transferId, ?string $reason = null, ?int $userId = null): StockTransfer
    {
        return DB::transaction(function () use ($transferId, $reason, $userId) {
            $transfer = StockTransfer::lockForUpdate()->findOrFail($transferId);

            if ($transfer->isReceived()) {
                throw new Exception('গৃহীত চালান বাতিল করা সম্ভব নয়।');
            }

            if ($transfer->isCancelled()) {
                throw new Exception('এই চালানটি আগেই বাতিল করা হয়েছে।');
            }

            $transfer->update([
                'status' => 'cancelled',
                'notes' => trim(($transfer->notes ? $transfer->notes . ' | ' : '') . "বাতিলের কারণ: {$reason}"),
            ]);

            return $transfer;
        });
    }

    /**
     * 4. Sales Out (Showroom বা POS থেকে বিক্রয়):
     * শুধুমাত্র সংশ্লিষ্ট শোরুমের স্টক থেকে decrement হবে।
     */
    public function recordSaleOut(
        int $locationId,
        array $items,
        ?string $referenceNo = null,
        ?int $userId = null
    ): array {
        if (empty($items)) {
            throw new Exception('বিক্রয় তালিকায় কোনো বই পাওয়া যায়নি।');
        }

        $location = Location::findOrFail($locationId);
        if (!$location->is_active) {
            throw new Exception('এই লোকেশনটি বর্তমানে নিষ্ক্রিয় রয়েছে।');
        }

        return DB::transaction(function () use ($location, $items, $referenceNo, $userId) {
            $results = [];

            foreach ($items as $item) {
                $bookId = (int) $item['book_id'];
                $quantity = (int) $item['quantity'];

                if ($quantity <= 0) {
                    throw new Exception("বিক্রয় পরিমাণ শূন্য বা ঋণাত্মক হতে পারবে না।");
                }

                $book = Book::findOrFail($bookId);

                $inventory = Inventory::where('book_id', $book->id)
                    ->where('location_id', $location->id)
                    ->lockForUpdate()
                    ->first();

                $currentQty = $inventory ? $inventory->quantity : 0;
                if ($currentQty < $quantity) {
                    throw new Exception("{$location->name}-এ '{$book->title}' বইটির পর্যাপ্ত স্টক নেই (বর্তমান মজুদ: {$currentQty}, বিক্রি চাওয়া হয়েছে: {$quantity})।");
                }

                $newQty = $currentQty - $quantity;
                $inventory->update(['quantity' => $newQty]);

                // Sync book global stock
                $book->decrement('stock_quantity', $quantity);
                $book->increment('sales_count', $quantity);

                // Movement Log
                InventoryMovementLog::create([
                    'book_id' => $book->id,
                    'location_id' => $location->id,
                    'type' => 'sale_out',
                    'quantity_change' => -$quantity,
                    'quantity_before' => $currentQty,
                    'quantity_after' => $newQty,
                    'reference_type' => 'PosSale',
                    'reference_id' => null,
                    'notes' => $referenceNo ? "বিক্রয় রশিদ/ইনভয়েস: {$referenceNo}" : 'শোরুম/POS সরাসরি বিক্রয়',
                    'user_id' => $userId,
                ]);

                $results[] = [
                    'book_id' => $book->id,
                    'title' => $book->title,
                    'sold_quantity' => $quantity,
                    'remaining_quantity' => $newQty,
                    'is_low_stock' => $newQty <= $inventory->min_alert_quantity,
                ];
            }

            return $results;
        });
    }

    /**
     * 5. Wastage/Damaged Log:
     * ছাপা, বাঁধাই বা পরিবহনের ত্রুটিপূর্ণ বই আলাদা অ্যাডজাস্ট করার অপশন
     */
    public function recordWastage(
        int $bookId,
        int $locationId,
        int $quantity,
        string $reason,
        ?string $actionTaken = 'disposed',
        ?string $notes = null,
        ?int $userId = null
    ): StockWastage {
        if ($quantity <= 0) {
            throw new Exception('ওয়েস্টেজ পরিমাণ শূন্য বা ঋণাত্মক হতে পারবে না।');
        }

        $validReasons = [
            'production_defect', // ছাপা ত্রুটি
            'binding_defect',    // বাঁধাই ত্রুটি
            'transit_damage',    // পরিবহনকালীন ক্ষতি
            'showroom_damage',   // শোরুম ডিসপ্লে বা সেল্ফে নষ্ট
            'water_damage',      // পানি বা আর্দ্রতা জনিত ক্ষতি
            'lost',              // হারিয়ে যাওয়া
            'other'              // অন্যান্য
        ];

        if (!in_array($reason, $validReasons, true)) {
            throw new Exception('অবৈধ কারণ প্রদান করা হয়েছে। গ্রহণযোগ্য কারণ: ' . implode(', ', $validReasons));
        }

        return DB::transaction(function () use ($bookId, $locationId, $quantity, $reason, $actionTaken, $notes, $userId) {
            $book = Book::findOrFail($bookId);
            $location = Location::findOrFail($locationId);

            $inventory = Inventory::where('book_id', $book->id)
                ->where('location_id', $location->id)
                ->lockForUpdate()
                ->first();

            $currentQty = $inventory ? $inventory->quantity : 0;
            if ($currentQty < $quantity) {
                throw new Exception("{$location->name}-এ '{$book->title}' এর পর্যাপ্ত স্টক নেই (মজুদ: {$currentQty}, কমাতে চাওয়া হয়েছে: {$quantity})।");
            }

            $newQty = $currentQty - $quantity;
            $inventory->update(['quantity' => $newQty]);

            // Deduct book aggregate stock
            $book->decrement('stock_quantity', $quantity);

            $wastage = StockWastage::create([
                'book_id' => $book->id,
                'location_id' => $location->id,
                'quantity' => $quantity,
                'reason' => $reason,
                'reported_by' => $userId,
                'action_taken' => $actionTaken,
                'notes' => $notes,
            ]);

            // Movement Log
            InventoryMovementLog::create([
                'book_id' => $book->id,
                'location_id' => $location->id,
                'type' => 'wastage_out',
                'quantity_change' => -$quantity,
                'quantity_before' => $currentQty,
                'quantity_after' => $newQty,
                'reference_type' => StockWastage::class,
                'reference_id' => $wastage->id,
                'notes' => "ত্রুটিপূর্ণ/ক্ষতিগ্রস্ত স্টক বাদ: {$reason}" . ($notes ? " ({$notes})" : ''),
                'user_id' => $userId,
            ]);

            return $wastage->load(['book', 'location', 'reporter']);
        });
    }

    /**
     * 6. Low Stock Alerts for Showrooms (or any specific location)
     */
    public function getLowStockAlerts(?int $locationId = null, ?string $locationType = 'showroom')
    {
        $query = Inventory::with(['book', 'location'])
            ->lowStock()
            ->whereHas('location', function ($q) use ($locationType, $locationId) {
                $q->where('is_active', true);
                if ($locationType) {
                    $q->where('type', $locationType);
                }
                if ($locationId) {
                    $q->where('id', $locationId);
                }
            })
            ->orderBy('quantity', 'asc');

        return $query->get();
    }

    /**
     * 7. Transfer Challan History Log with Filters
     */
    public function getChallanHistory(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = StockTransfer::with(['fromLocation', 'toLocation', 'items.book', 'creator', 'receiver'])
            ->latest('id');

        if (!empty($filters['challan_no'])) {
            $query->where('challan_no', 'like', '%' . trim($filters['challan_no']) . '%');
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from_location_id'])) {
            $query->where('from_location_id', $filters['from_location_id']);
        }

        if (!empty($filters['to_location_id'])) {
            $query->where('to_location_id', $filters['to_location_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('transfer_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('transfer_date', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage);
    }

    /**
     * 8. Inventory Movement Audit Logs
     */
    public function getMovementLogs(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = InventoryMovementLog::with(['book', 'location', 'user'])->latest('id');

        if (!empty($filters['book_id'])) {
            $query->where('book_id', $filters['book_id']);
        }

        if (!empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->paginate($perPage);
    }

    /**
     * 9. Overall Stock Summary Across Locations
     */
    public function getInventoryOverview(?int $bookId = null, ?int $categoryId = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Book::with(['inventories.location'])->select([
            'id', 'title', 'isbn', 'sku', 'price', 'discount_price', 'cost_price', 'stock_quantity'
        ]);

        if ($bookId) {
            $query->where('id', $bookId);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->paginate($perPage);
    }


    /**
     * 10. Central warehouse — created on first use so stock always has a home.
     */
    public function ensureCentralWarehouse(): Location
    {
        $location = Location::central()->active()->first() ?? Location::warehouses()->active()->first();

        if ($location) {
            return $location;
        }

        return Location::create([
            'name' => 'Main Warehouse',
            'code' => 'WH-MAIN',
            'type' => 'warehouse',
            'is_central' => true,
            'is_active' => true,
        ]);
    }

    /**
     * 11. Mirror a change that already updated books.stock_quantity (purchases, rollbacks)
     * into the central warehouse so location stock and the book total stay in step.
     * Never touches books.stock_quantity itself. Decreases are clamped at the warehouse balance.
     */
    public function mirrorBookStock(
        int $bookId,
        int $delta,
        string $type,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null,
        ?int $userId = null
    ): ?Inventory {
        if ($delta === 0 || !Book::whereKey($bookId)->exists()) {
            return null;
        }

        return DB::transaction(function () use ($bookId, $delta, $type, $referenceType, $referenceId, $notes, $userId) {
            $location = $this->ensureCentralWarehouse();

            $inventory = Inventory::where('book_id', $bookId)->where('location_id', $location->id)->lockForUpdate()->first();
            $before = $inventory ? (int) $inventory->quantity : 0;
            $after = max(0, $before + $delta);

            if ($after === $before) {
                return $inventory;
            }

            if ($inventory) {
                $inventory->update(['quantity' => $after]);
            } else {
                $inventory = Inventory::create([
                    'book_id' => $bookId,
                    'location_id' => $location->id,
                    'quantity' => $after,
                    'min_alert_quantity' => 10,
                ]);
            }

            InventoryMovementLog::create([
                'book_id' => $bookId,
                'location_id' => $location->id,
                'type' => $type,
                'quantity_change' => $after - $before,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'user_id' => $userId,
            ]);

            return $inventory;
        });
    }

    /**
     * 12. Opening balance: books that carry a total stock but have no location rows yet
     * are placed in the central warehouse once.
     */
    public function backfillOpeningStock(?int $userId = null): int
    {
        $location = $this->ensureCentralWarehouse();
        $count = 0;

        Book::where('stock_quantity', '>', 0)
            ->whereDoesntHave('inventories')
            ->select('id', 'stock_quantity')
            ->chunkById(200, function ($books) use ($location, $userId, &$count) {
                foreach ($books as $book) {
                    DB::transaction(function () use ($book, $location, $userId, &$count) {
                        Inventory::create([
                            'book_id' => $book->id,
                            'location_id' => $location->id,
                            'quantity' => (int) $book->stock_quantity,
                            'min_alert_quantity' => 10,
                        ]);
                        InventoryMovementLog::create([
                            'book_id' => $book->id,
                            'location_id' => $location->id,
                            'type' => 'opening_balance',
                            'quantity_change' => (int) $book->stock_quantity,
                            'quantity_before' => 0,
                            'quantity_after' => (int) $book->stock_quantity,
                            'reference_type' => 'OpeningBalance',
                            'notes' => 'Opening stock from catalogue total',
                            'user_id' => $userId,
                        ]);
                        $count++;
                    });
                }
            });

        return $count;
    }
}
