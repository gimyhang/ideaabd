@extends('layouts.admin')

@section('title', 'বইমেলা স্টল পিওএস টার্মিনাল — Boi Mela Stall POS')
@section('heading', 'বইমেলা স্টল পিওএস টার্মিনাল (Boi Mela Stall POS Terminal)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">বইমেলা POS টার্মিনাল</li>
@endsection

@push('styles')
<style>
    /* POS Terminal Pro UI */
    .pos-wrap {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Hind Siliguri", sans-serif;
    }
    
    /* Mobile View Segmented Controller */
    .pos-mobile-tabs {
        display: none;
    }
    @media (max-width: 1199.98px) {
        .pos-mobile-tabs {
            display: flex !important;
            position: sticky;
            top: 60px;
            z-index: 1020;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
        }
        .pos-panel-books {
            display: block;
        }
        .pos-panel-cart {
            display: none;
        }
        body.pos-view-cart .pos-panel-books {
            display: none !important;
        }
        body.pos-view-cart .pos-panel-cart {
            display: block !important;
        }
    }

    /* Book Cards */
    .pos-book-card {
        transition: transform 0.12s ease, box-shadow 0.12s ease, border-color 0.12s ease;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        cursor: pointer;
        user-select: none;
        position: relative;
        border-radius: 12px;
    }
    .pos-book-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08);
        border-color: #3b82f6;
    }
    .pos-book-card:active {
        transform: scale(0.97);
    }
    .pos-book-card.is-idea {
        border-color: #bfdbfe;
        background: linear-gradient(180deg, #f8faff 0%, #ffffff 100%);
    }
    .pos-book-thumb {
        width: 100%;
        aspect-ratio: 1 / 1.36;
        object-fit: cover;
        border-radius: 8px;
        background: #f1f5f9;
    }

    /* Category & Filter Pills */
    .pos-pill {
        white-space: nowrap;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 50rem;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        cursor: pointer;
        transition: all 0.12s ease;
    }
    .pos-pill:hover, .pos-pill.active {
        background: #0f172a;
        color: #ffffff;
        border-color: #0f172a;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.18);
    }
    .pos-pill.pill-idea {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #93c5fd;
    }
    .pos-pill.pill-idea.active {
        background: #1d4ed8;
        color: #ffffff;
        border-color: #1d4ed8;
    }

    /* Payment Methods */
    .pay-tile {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        font-weight: 700;
        font-size: 0.84rem;
        padding: 8px 6px;
        border-radius: 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.12s ease;
    }
    .pay-tile:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }
    .btn-check:checked + .pay-tile {
        background: #0f172a;
        color: #ffffff;
        border-color: #0f172a;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
    }
    .btn-check:checked + .pay-tile.tile-cash { background: #16a34a; border-color: #16a34a; }
    .btn-check:checked + .pay-tile.tile-bkash { background: #d12053; border-color: #d12053; }
    .btn-check:checked + .pay-tile.tile-nagad { background: #ea580c; border-color: #ea580c; }
    .btn-check:checked + .pay-tile.tile-rocket { background: #7c3aed; border-color: #7c3aed; }
    .btn-check:checked + .pay-tile.tile-card { background: #0284c7; border-color: #0284c7; }
    .btn-check:checked + .pay-tile.tile-split { background: #475569; border-color: #475569; }

    /* Discount Buttons */
    .disc-btn {
        font-size: 0.76rem;
        font-weight: 700;
        padding: 4px 7px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        cursor: pointer;
    }
    .disc-btn.active, .disc-btn:hover {
        background: #dc2626;
        color: #ffffff;
        border-color: #dc2626;
    }

    .quick-cash-chip {
        font-size: 0.74rem;
        font-weight: 600;
        padding: 3px 7px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #1e293b;
        cursor: pointer;
    }
    .quick-cash-chip:hover {
        background: #e2e8f0;
    }

    /* Change Return Callout */
    .change-callout {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-radius: 10px;
        padding: 8px 12px;
    }
    .change-val {
        font-size: 1.35rem;
        font-weight: 800;
        color: #15803d;
        font-family: 'JetBrains Mono', monospace;
        line-height: 1;
    }

    /* Mobile Floating Cart Summary Bar */
    .mobile-floating-bar {
        display: none;
    }
    @media (max-width: 1199.98px) {
        body:not(.pos-view-cart) .mobile-floating-bar {
            display: flex !important;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            background: #0f172a;
            color: #ffffff;
            padding: 10px 16px;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.25);
            align-items: center;
            justify-content: space-between;
        }
    }

    /* Offline Bar */
    .offline-status-bar {
        background: #fef3c7;
        border: 1px solid #fde68a;
        color: #92400e;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
    }
</style>
@endpush

@section('actions')
    <div class="d-flex align-items-center gap-1.5 flex-wrap">
        {{-- Offline Status & Sync Trigger --}}
        <div id="offlineIndicatorBox" class="d-none">
            <button type="button" class="btn btn-sm btn-warning rounded-pill px-2.5 py-1 fw-bold shadow-xs" onclick="triggerManualOfflineSync()">
                <i class="fas fa-wifi text-danger me-1"></i> <span id="offlineQueueCount">0</span> Offline Sync
            </button>
        </div>

        {{-- Sound Toggle --}}
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 shadow-xs" id="btnToggleSound" onclick="toggleSoundFeedback()" title="Audio Beep On/Off">
            <i class="fas fa-volume-high" id="soundIcon"></i>
        </button>

        {{-- Held Bills --}}
        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2.5 py-1 shadow-xs fw-semibold position-relative" onclick="openHeldSalesModal()" title="Held Bills (F9)">
            <i class="fas fa-pause me-1"></i> Hold (<span id="heldSalesBadge">0</span>)
        </button>

        {{-- Z-Report --}}
        <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-2.5 py-1 shadow-xs fw-semibold" onclick="openShiftReportModal()">
            <i class="fas fa-file-invoice-dollar me-1"></i> Z-Report
        </button>

        {{-- Shortcuts --}}
        <button type="button" class="btn btn-sm btn-light border rounded-pill px-2 py-1 shadow-xs" data-bs-toggle="modal" data-bs-target="#posShortcutsModal" title="Shortcuts">
            <i class="fas fa-keyboard text-muted"></i>
        </button>
    </div>
@endsection

@section('content')
<div class="pos-wrap">

    {{-- Offline Notice Bar --}}
    <div id="offlineBanner" class="offline-status-bar d-none mb-2.5 d-flex align-items-center justify-content-between">
        <div>
            <i class="fas fa-circle-exclamation me-1.5 text-danger"></i>
            <strong>Offline Mode Active:</strong> Bills will be saved locally and auto-synced when internet reconnects.
        </div>
        <button type="button" class="btn btn-dark btn-sm rounded-pill py-0 px-2.5" onclick="triggerManualOfflineSync()">
            <i class="fas fa-rotate me-1"></i> Sync Now
        </button>
    </div>

    {{-- Mobile View Segmented Switcher (< 1200px) --}}
    <div class="pos-mobile-tabs mb-2 p-1 bg-white border rounded-pill shadow-xs">
        <button type="button" class="btn btn-sm btn-dark flex-fill rounded-pill py-1.5 fw-bold" id="btnTabBooks" onclick="switchMobileView('books')">
            <i class="fas fa-book-open me-1"></i> 1. Books & Scan
        </button>
        <button type="button" class="btn btn-sm btn-light flex-fill rounded-pill py-1.5 fw-bold text-primary position-relative" id="btnTabCart" onclick="switchMobileView('cart')">
            <i class="fas fa-shopping-cart me-1"></i> 2. Cart & Pay
            <span class="badge bg-danger rounded-pill ms-1" id="mobileTabCartCount">0</span>
        </button>
    </div>

    {{-- POS Main Dual Panel Grid --}}
    <div class="row g-2.5">

        <!-- ========================================================= -->
        <!-- LEFT PANEL: Scanner, Filters & Book Cards (Panel 1)       -->
        <!-- ========================================================= -->
        <div class="col-12 col-xl-7 pos-panel-books">
            <div class="adm-card bg-white h-100 d-flex flex-column shadow-sm rounded-4 border-0">
                
                {{-- Search Bar & Camera Trigger --}}
                <div class="p-2.5 border-bottom bg-light rounded-top-4">
                    <div class="row g-1.5 align-items-center">
                        <div class="col">
                            <div class="input-group shadow-xs">
                                <span class="input-group-text bg-white border-end-0 text-primary py-2">
                                    <i class="fas fa-barcode fs-5"></i>
                                </span>
                                <input type="search" id="posSearchInput" 
                                       class="form-control border-start-0 ps-0 fw-semibold" 
                                       placeholder="Scan barcode or type book / author (F2)..." 
                                       autocomplete="off" autofocus 
                                       oninput="handleSearchDebounced(this.value)"
                                       onkeydown="handleSearchKeydown(event)">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-outline-dark rounded-3 px-2.5 py-2 shadow-xs fw-bold" onclick="openCameraScannerModal()" title="Continuous Camera Scanner">
                                <i class="fas fa-camera text-primary me-1"></i> Camera
                            </button>
                        </div>
                    </div>

                    {{-- Category & Priority Filters --}}
                    <div class="d-flex align-items-center gap-1.5 overflow-auto mt-2 pb-0.5" id="categoryPillContainer" style="scrollbar-width: thin;">
                        {{-- Idea Prokashon Priority Filter --}}
                        <button type="button" class="pos-pill pill-idea fw-bold active" onclick="filterByIdea(this)">
                            ⭐ Idea Publications
                        </button>
                        <button type="button" class="pos-pill" onclick="filterByCategory('all', this)">
                            All Books
                        </button>
                        @foreach($categories as $cat)
                            <button type="button" class="pos-pill" onclick="filterByCategory({{ $cat->id }}, this)">
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Book Cards Grid Container --}}
                <div class="adm-card__body p-2.5 flex-grow-1 overflow-auto position-relative" style="max-height: 620px; min-height: 440px;" id="bookGridContainer">
                    <div class="row g-2" id="posBookGrid">
                        @foreach($books as $b)
                            <div class="col-6 col-sm-4 col-md-3 pos-book-item" data-category="{{ $b['category_id'] }}" data-idea="{{ $b['is_idea'] ? '1' : '0' }}">
                                <div class="pos-book-card p-2 text-center h-100 d-flex flex-column justify-content-between {{ $b['is_idea'] ? 'is-idea' : '' }}"
                                     onclick="addToCart({{ $b['id'] }}, '{{ addslashes($b['title']) }}', {{ $b['final_price'] }}, {{ $b['stock_quantity'] }}, '{{ $b['sku'] ?? $b['isbn'] }}')">
                                    <div>
                                        @if($b['cover_url'])
                                            <img src="{{ $b['cover_url'] }}" alt="{{ $b['title'] }}" class="pos-book-thumb mb-1 shadow-xs" loading="lazy">
                                        @else
                                            <div class="pos-book-thumb d-flex align-items-center justify-content-center text-muted mb-1">
                                                <i class="fas fa-book fs-3 opacity-25"></i>
                                            </div>
                                        @endif
                                        <div class="fw-bold small text-dark line-clamp-1 mb-0.5" title="{{ $b['title'] }}" style="font-size: 0.8rem;">
                                            {{ $b['title'] }}
                                        </div>
                                        <div class="text-muted small line-clamp-1 mb-1" style="font-size: 0.7rem;">
                                            {{ $b['author_name'] }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-primary font-monospace fs-6 mb-0.5">
                                            ৳{{ number_format($b['final_price'], 0) }}
                                            @if($b['price'] > $b['final_price'])
                                                <small class="text-muted text-decoration-line-through fw-normal ms-1" style="font-size: 9px;">৳{{ number_format($b['price'], 0) }}</small>
                                            @endif
                                        </div>
                                        <span class="badge {{ $b['stock_quantity'] > 5 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} rounded-pill" style="font-size: 9px;">
                                            Stock: {{ $b['stock_quantity'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Empty Notice --}}
                    <div id="noBookMatchNotice" class="d-none text-center py-5 text-muted">
                        <i class="fas fa-box-open fs-1 opacity-25 mb-1.5"></i>
                        <h6 class="fw-bold text-dark mb-0">No Books Found</h6>
                        <small class="text-muted">Try a different title, barcode or category.</small>
                    </div>
                </div>

            </div>
        </div>

        <!-- ========================================================= -->
        <!-- RIGHT PANEL: Cart, Discount & Instant Pay (Panel 2)       -->
        <!-- ========================================================= -->
        <div class="col-12 col-xl-5 pos-panel-cart">
            <div class="adm-card bg-white h-100 d-flex flex-column shadow-sm rounded-4 border-0 border-top border-4 border-primary">
                
                {{-- Cart Header --}}
                <div class="p-2.5 border-bottom d-flex justify-content-between align-items-center bg-light rounded-top-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle bg-primary text-white p-1.5 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                            <i class="fas fa-receipt" style="font-size: 12px;"></i>
                        </span>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.92rem;">Stall Cart</h6>
                            <span class="small text-muted font-monospace" id="cartItemCountLabel" style="font-size: 11px;">0 items</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2 py-0.5 fw-bold" onclick="holdCurrentSale()" title="Hold Sale (F8)">
                            <i class="fas fa-pause me-1"></i> Hold
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0.5" onclick="clearCart(true)" title="Clear Cart (Esc)">
                            <i class="fas fa-trash me-1"></i> Clear
                        </button>
                    </div>
                </div>

                {{-- Cart Items Table --}}
                <div class="adm-card__body p-0 flex-grow-1 overflow-auto" style="max-height: 250px; min-height: 160px;">
                    <table class="table table-sm adm-table align-middle mb-0">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th class="ps-3" style="width: 48%;">Item</th>
                                <th class="text-center" style="width: 26%;">Qty</th>
                                <th class="text-end" style="width: 18%;">Total</th>
                                <th class="text-center pe-2" style="width: 8%;"></th>
                            </tr>
                        </thead>
                        <tbody id="cartTableBody">
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small">
                                    <i class="fas fa-barcode fs-3 text-muted opacity-25 d-block mb-1"></i>
                                    Cart is empty. Scan barcode or tap books to add.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Billing & Payment Checkout Footer --}}
                <div class="p-2.5 bg-light border-top rounded-bottom-4">

                    {{-- Customer Quick Info --}}
                    <div class="row g-1.5 mb-2">
                        <div class="col-6">
                            <input type="tel" id="txtCustomerPhone" class="form-control form-control-sm rounded-3 font-monospace" 
                                   placeholder="Phone (01...)" oninput="handleCustomerPhoneLookup(this.value)">
                        </div>
                        <div class="col-6">
                            <input type="text" id="txtCustomerName" class="form-control form-control-sm rounded-3" 
                                   placeholder="Customer Name (Optional)">
                        </div>
                    </div>

                    {{-- Subtotal --}}
                    <div class="d-flex justify-content-between align-items-center small text-muted mb-1">
                        <span class="fw-semibold">Subtotal:</span>
                        <span class="fw-bold text-dark font-monospace" id="lblSubtotal">৳0.00</span>
                    </div>

                    {{-- Discount Engine --}}
                    <div class="p-2 bg-white rounded-3 border mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
                            <span class="small fw-bold text-danger">Stall Discount:</span>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 active fw-bold" id="btnDiscModePercent" onclick="setDiscountMode('percent')">%</button>
                                <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 fw-bold" id="btnDiscModeFlat" onclick="setDiscountMode('flat')">৳</button>
                            </div>
                        </div>

                        {{-- Quick Presets --}}
                        <div class="d-flex align-items-center gap-1 flex-wrap mb-1">
                            <button type="button" class="disc-btn active" onclick="applyPresetDiscount(0, this)">0%</button>
                            <button type="button" class="disc-btn" onclick="applyPresetDiscount(10, this)">10%</button>
                            <button type="button" class="disc-btn" onclick="applyPresetDiscount(15, this)">15%</button>
                            <button type="button" class="disc-btn" onclick="applyPresetDiscount(20, this)">20%</button>
                            <button type="button" class="disc-btn" onclick="applyPresetDiscount(25, this)">25%</button>
                            <button type="button" class="disc-btn" onclick="applyPresetDiscount(30, this)">30%</button>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="input-group input-group-sm" style="max-width: 120px;">
                                <input type="number" id="txtDiscountInput" value="0" min="0" step="any" class="form-control form-control-sm text-end font-monospace fw-bold" oninput="calculateTotal()">
                                <span class="input-group-text bg-light text-muted small fw-bold" id="lblDiscountUnit">%</span>
                            </div>
                            <div class="text-danger fw-bold font-monospace small">
                                -<span id="lblCalculatedDiscount">৳0.00</span>
                            </div>
                        </div>
                    </div>

                    {{-- Net Total --}}
                    <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-dark text-white mb-2">
                        <span class="fw-bold" style="font-size: 0.95rem;">Payable Total:</span>
                        <span class="fs-4 fw-bold font-monospace text-warning" id="lblTotal">৳0.00</span>
                    </div>

                    {{-- Payment Methods Selector --}}
                    <div class="row g-1 mb-2">
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="posPayMethod" id="payCash" value="cash" checked onchange="handlePaymentMethodChange()">
                            <label class="pay-tile tile-cash w-100 d-block" for="payCash">
                                <i class="fas fa-money-bill-wave d-block mb-0.5"></i> Cash
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="posPayMethod" id="payBkash" value="bkash" onchange="handlePaymentMethodChange()">
                            <label class="pay-tile tile-bkash w-100 d-block" for="payBkash">
                                <i class="fas fa-mobile-screen d-block mb-0.5"></i> bKash
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="posPayMethod" id="payNagad" value="nagad" onchange="handlePaymentMethodChange()">
                            <label class="pay-tile tile-nagad w-100 d-block" for="payNagad">
                                <i class="fas fa-wallet d-block mb-0.5"></i> Nagad
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="posPayMethod" id="payCard" value="card" onchange="handlePaymentMethodChange()">
                            <label class="pay-tile tile-card w-100 d-block" for="payCard">
                                <i class="fas fa-credit-card d-block mb-0.5"></i> Card
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="posPayMethod" id="payRocket" value="rocket" onchange="handlePaymentMethodChange()">
                            <label class="pay-tile tile-rocket w-100 d-block" for="payRocket">
                                <i class="fas fa-paper-plane d-block mb-0.5"></i> Rocket
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="posPayMethod" id="paySplit" value="split" onchange="handlePaymentMethodChange()">
                            <label class="pay-tile tile-split w-100 d-block" for="paySplit">
                                <i class="fas fa-arrows-split-up-and-left d-block mb-0.5"></i> Split
                            </label>
                        </div>
                    </div>

                    {{-- Split Payment Inputs (Conditional) --}}
                    <div id="splitPaymentDrawer" class="d-none p-2 bg-white border rounded-3 mb-2">
                        <div class="row g-1.5">
                            <div class="col-6">
                                <label class="small text-muted" style="font-size: 10px;">Cash Paid (৳):</label>
                                <input type="number" id="txtSplitCash" class="form-control form-control-sm font-monospace" placeholder="0" oninput="calculateChange()">
                            </div>
                            <div class="col-6">
                                <label class="small text-muted" style="font-size: 10px;">Online Paid (৳):</label>
                                <input type="number" id="txtSplitOnline" class="form-control form-control-sm font-monospace" placeholder="0" oninput="calculateChange()">
                            </div>
                        </div>
                    </div>

                    {{-- Digital TrxID Box --}}
                    <div id="digitalTrxBox" class="d-none mb-1.5">
                        <input type="text" id="txtTrxId" class="form-control form-control-sm font-monospace rounded-3" placeholder="TrxID (Optional)">
                    </div>

                    {{-- Cash Tendered & Change Return Box --}}
                    <div id="cashTenderBox" class="change-callout mb-2.5">
                        <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
                            <span class="small fw-bold text-dark">Tendered Cash:</span>
                            <div class="d-flex gap-1">
                                <button type="button" class="quick-cash-chip" onclick="setExactCash()">Exact</button>
                                <button type="button" class="quick-cash-chip" onclick="addTenderedCash(50)">+50</button>
                                <button type="button" class="quick-cash-chip" onclick="addTenderedCash(100)">+100</button>
                                <button type="button" class="quick-cash-chip" onclick="addTenderedCash(500)">+500</button>
                                <button type="button" class="quick-cash-chip" onclick="addTenderedCash(1000)">+1000</button>
                            </div>
                        </div>

                        <div class="row g-1.5 align-items-center">
                            <div class="col-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white">৳</span>
                                    <input type="number" id="txtTenderedCash" class="form-control form-control-sm font-monospace fw-bold" placeholder="0" oninput="calculateChange()">
                                </div>
                            </div>
                            <div class="col-6 text-end">
                                <div class="small text-muted" style="font-size: 10px;">Change:</div>
                                <div class="change-val" id="lblChangeReturn">৳0.00</div>
                            </div>
                        </div>
                    </div>

                    {{-- Complete Sale Button --}}
                    <button type="button" id="btnCompleteSale" class="btn btn-success btn-lg w-100 rounded-pill fw-bold shadow py-2 fs-6" onclick="handleCheckout()">
                        <i class="fas fa-check-circle me-1.5"></i> Complete & Print (F4)
                    </button>
                </div>

            </div>
        </div>

    </div>

    {{-- Mobile Floating Bottom Checkout Bar (When in Books mode) --}}
    <div class="mobile-floating-bar">
        <div>
            <div class="small text-white-50" style="font-size: 11px;" id="lblMobileFloatingItems">0 items</div>
            <div class="fw-bold font-monospace text-warning fs-5" id="lblMobileFloatingTotal">৳0.00</div>
        </div>
        <button type="button" class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark shadow" onclick="switchMobileView('cart')">
            View Cart & Pay <i class="fas fa-arrow-right ms-1"></i>
        </button>
    </div>

    <!-- ========================================================= -->
    <!-- RECENT STALL TRANSACTIONS                                 -->
    <!-- ========================================================= -->
    <div class="adm-card bg-white mt-3 shadow-sm rounded-4 border-0">
        <div class="p-2.5 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-clock-rotate-left text-primary me-1"></i> Today's Transactions</h6>
                <small class="text-muted">Total Completed: <strong>{{ $todayOrdersCount }}</strong> | Void: <strong class="text-danger">{{ $todayVoidCount }}</strong></small>
            </div>
            <div style="max-width: 240px;">
                <input type="search" class="form-control form-control-sm rounded-pill font-monospace" placeholder="Search receipt / phone..." oninput="filterRecentTransactions(this.value)">
            </div>
        </div>

        <div class="adm-card__body p-0">
            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Receipt #</th>
                            <th>Time</th>
                            <th>Customer</th>
                            <th>Cashier</th>
                            <th>Method</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-end pe-3" style="min-width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="recentTxTableBody">
                        @forelse($recentSales as $sale)
                            <tr id="saleRow{{ $sale->id }}" class="{{ $sale->isVoided() ? 'table-danger text-muted opacity-75' : '' }}">
                                <td class="ps-3 fw-bold text-primary font-monospace">#{{ $sale->receipt_no }}</td>
                                <td class="small">{{ $sale->created_at->format('h:i A') }}</td>
                                <td class="small">
                                    <div class="fw-semibold text-dark">{{ $sale->customer_name }}</div>
                                    @if($sale->customer_phone)
                                        <small class="text-muted font-monospace">{{ $sale->customer_phone }}</small>
                                    @endif
                                </td>
                                <td class="small">{{ $sale->cashier->name ?? 'Staff' }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border text-uppercase font-monospace">{{ $sale->payment_method }}</span>
                                </td>
                                <td class="fw-bold text-dark font-monospace">৳{{ number_format($sale->total, 2) }}</td>
                                <td>
                                    @if($sale->isVoided())
                                        <span class="badge bg-danger text-white rounded-pill px-2 py-0.5">Void</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5">Success</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 text-nowrap">
                                    <a href="{{ route('admin.pos.receipt', $sale->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0.5 shadow-xs" title="Print Receipt">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    @if(!$sale->isVoided())
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0.5 shadow-xs ms-1" onclick="voidSale({{ $sale->id }}, '{{ $sale->receipt_no }}')" title="Void Sale">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-4 text-muted small">No transactions today yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ========================================================= --}}
{{-- 1. CONTINUOUS MULTI-BARCODE CAMERA SCANNER MODAL          --}}
{{-- ========================================================= --}}
<div class="modal fade" id="cameraScannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2.5 px-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-camera text-primary"></i>
                    <h6 class="modal-title fw-bold mb-0">Multi-Barcode Camera Scanner</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="stopCameraScanner()"></button>
            </div>
            <div class="modal-body p-3 text-center position-relative">
                <div class="position-relative overflow-hidden rounded-3 mb-2 bg-black">
                    <video id="cameraScannerVideo" playsinline style="width: 100%; max-height: 280px; object-fit: cover;"></video>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 220px; height: 130px; border: 2px dashed #22c55e; border-radius: 8px; box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.45); pointer-events: none;"></div>
                </div>
                
                {{-- Live Multi-Scan Info Bar --}}
                <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-light border mb-2">
                    <span class="small fw-semibold text-dark">Items Scanned: <strong id="cameraScannedCounter" class="text-primary font-monospace">0</strong></span>
                    <span id="scannerStatusText" class="badge bg-success text-white rounded-pill px-2.5 py-1">Scanner Active</span>
                </div>

                {{-- Last Scanned Item Alert --}}
                <div id="cameraLastScannedBox" class="alert alert-success p-2 small mb-0 d-none text-start">
                    <i class="fas fa-check-circle me-1 text-success"></i> <span id="cameraLastScannedTitle" class="fw-bold"></span> added to cart!
                </div>
            </div>
            <div class="modal-footer bg-light p-2">
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold w-100" data-bs-dismiss="modal" onclick="stopCameraScanner(); switchMobileView('cart');">
                    Done Scanning (View Cart)
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- 2. HELD BILLS MODAL                                       --}}
{{-- ========================================================= --}}
<div class="modal fade" id="heldSalesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark py-2.5 px-3">
                <h6 class="modal-title fw-bold mb-0">
                    <i class="fas fa-pause-circle me-1"></i> Held Bills Queue (F9)
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div id="heldSalesListContainer" class="table-responsive"></div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- 3. Z-REPORT MODAL                                         --}}
{{-- ========================================================= --}}
<div class="modal fade" id="shiftReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2.5 px-3">
                <h6 class="modal-title fw-bold mb-0">
                    <i class="fas fa-file-invoice-dollar me-1 text-info"></i> Daily Shift Report (Z-Report)
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3" id="shiftReportContent">
                <div class="text-center py-4"><i class="fas fa-spinner fa-spin fs-3 text-primary"></i></div>
            </div>
            <div class="modal-footer bg-light p-2">
                <button type="button" class="btn btn-outline-dark rounded-pill px-3 btn-sm" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print Report
                </button>
                <button type="button" class="btn btn-secondary rounded-pill px-3 btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- 4. SHORTCUTS MODAL                                        --}}
{{-- ========================================================= --}}
<div class="modal fade" id="posShortcutsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-dark text-white py-2.5 px-3">
                <h6 class="modal-title fw-bold mb-0">Keyboard Shortcuts</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <table class="table table-sm table-bordered align-middle mb-0 font-monospace small">
                    <tbody>
                        <tr><td><kbd>F2</kbd> / <kbd>/</kbd></td><td>Focus Search & Barcode Input</td></tr>
                        <tr><td><kbd>F4</kbd> / <kbd>Ctrl+Enter</kbd></td><td>Complete Sale & Print Receipt</td></tr>
                        <tr><td><kbd>F8</kbd></td><td>Hold Current Sale</td></tr>
                        <tr><td><kbd>F9</kbd></td><td>View Held Bills</td></tr>
                        <tr><td><kbd>Esc</kbd></td><td>Clear Cart / Close Modal</td></tr>
                        <tr><td><kbd>Enter</kbd> (in search)</td><td>Add exact / first match to cart</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
{{-- Load Barcode Detector Polyfill / Library for fast continuous barcode reading --}}
<script src="https://cdn.jsdelivr.net/npm/@ericblade/quagga2@1.8.4/dist/quagga.min.js"></script>

<script>
// State Management
let cart = [];
let heldSales = [];
let offlineCatalog = [];
let offlineSalesQueue = [];
let discountMode = 'percent';
let soundEnabled = true;
let audioCtx = null;
let searchDebounceTimer = null;
let cameraScanningActive = false;
let lastScannedCode = null;
let lastScannedTime = 0;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

document.addEventListener('DOMContentLoaded', () => {
    loadCartFromStorage();
    loadHeldSalesFromStorage();
    loadOfflineQueueFromStorage();
    syncOfflineCatalogFromServer();
    setupKeyboardShortcuts();
    checkNetworkStatus();

    window.addEventListener('online', () => {
        checkNetworkStatus();
        triggerManualOfflineSync();
    });
    window.addEventListener('offline', () => {
        checkNetworkStatus();
    });
});

// -------------------------------------------------------------
// Offline Mode & Auto-Sync Engine
// -------------------------------------------------------------
function checkNetworkStatus() {
    const isOnline = navigator.onLine;
    const banner = document.getElementById('offlineBanner');
    const indicator = document.getElementById('offlineIndicatorBox');

    if (!isOnline) {
        banner?.classList.remove('d-none');
    } else {
        banner?.classList.add('d-none');
    }

    if (offlineSalesQueue.length > 0) {
        indicator?.classList.remove('d-none');
        document.getElementById('offlineQueueCount').textContent = offlineSalesQueue.length;
    } else {
        indicator?.classList.add('d-none');
    }
}

function syncOfflineCatalogFromServer() {
    fetch("{{ route('admin.pos.offline-catalog') }}", { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.books) {
                offlineCatalog = data.books;
                localStorage.setItem('pos_offline_catalog', JSON.stringify(offlineCatalog));
            }
        })
        .catch(() => {
            try {
                const saved = localStorage.getItem('pos_offline_catalog');
                if (saved) offlineCatalog = JSON.parse(saved);
            } catch(e) {}
        });
}

function saveOfflineQueueToStorage() {
    localStorage.setItem('pos_offline_sales_queue', JSON.stringify(offlineSalesQueue));
    checkNetworkStatus();
}

function loadOfflineQueueFromStorage() {
    try {
        const saved = localStorage.getItem('pos_offline_sales_queue');
        if (saved) offlineSalesQueue = JSON.parse(saved) || [];
    } catch(e) {}
    checkNetworkStatus();
}

function triggerManualOfflineSync() {
    if (offlineSalesQueue.length === 0) {
        SwalToast('info', 'No offline sales to sync.');
        return;
    }
    if (!navigator.onLine) {
        SwalToast('warning', 'Internet connection is still offline.');
        return;
    }

    fetch("{{ route('admin.pos.offline-sync') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ sales: offlineSalesQueue })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            soundSuccess();
            SwalToast('success', `${data.synced_count} offline sales synced successfully!`);
            offlineSalesQueue = [];
            saveOfflineQueueToStorage();
            setTimeout(() => location.reload(), 1200);
        } else {
            SwalToast('error', data.message || 'Offline sync failed.');
        }
    })
    .catch(() => {
        SwalToast('warning', 'Server unreachable. Will retry automatically.');
    });
}

// -------------------------------------------------------------
// Audio Engine
// -------------------------------------------------------------
function getAudioContext() {
    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    return audioCtx;
}

function playTone(freq, type, duration) {
    if (!soundEnabled) return;
    try {
        const ctx = getAudioContext();
        if (ctx.state === 'suspended') ctx.resume();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = type;
        osc.frequency.setValueAtTime(freq, ctx.currentTime);
        gain.gain.setValueAtTime(0.12, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + duration);
    } catch(e) {}
}

function soundBeep() { playTone(880, 'sine', 0.08); }
function soundSuccess() {
    playTone(587, 'triangle', 0.1);
    setTimeout(() => playTone(880, 'triangle', 0.18), 90);
}
function soundError() { playTone(220, 'sawtooth', 0.2); }

function toggleSoundFeedback() {
    soundEnabled = !soundEnabled;
    document.getElementById('soundIcon').className = soundEnabled ? 'fas fa-volume-high' : 'fas fa-volume-xmark text-danger';
    if (soundEnabled) soundBeep();
}

// -------------------------------------------------------------
// Mobile Segmented View Switching
// -------------------------------------------------------------
function switchMobileView(view) {
    if (view === 'cart') {
        document.body.classList.add('pos-view-cart');
        document.getElementById('btnTabBooks')?.classList.replace('btn-dark', 'btn-light');
        document.getElementById('btnTabCart')?.classList.replace('btn-light', 'btn-dark');
        document.getElementById('btnTabCart')?.classList.remove('text-primary');
    } else {
        document.body.classList.remove('pos-view-cart');
        document.getElementById('btnTabBooks')?.classList.replace('btn-light', 'btn-dark');
        document.getElementById('btnTabCart')?.classList.replace('btn-dark', 'btn-light');
        document.getElementById('btnTabCart')?.classList.add('text-primary');
        document.getElementById('posSearchInput')?.focus();
    }
}

// -------------------------------------------------------------
// Cart Management
// -------------------------------------------------------------
function addToCart(id, title, price, maxStock, sku = '') {
    const existing = cart.find(item => item.id === id);
    if (existing) {
        if (existing.qty < maxStock) {
            existing.qty++;
            soundBeep();
        } else {
            soundError();
            SwalToast('warning', `Stock limit reached (${maxStock})`);
            return;
        }
    } else {
        cart.push({ id, title, price: parseFloat(price), qty: 1, maxStock: parseInt(maxStock), sku });
        soundBeep();
    }

    saveCartToStorage();
    renderCart();

    const searchInput = document.getElementById('posSearchInput');
    if (searchInput) {
        searchInput.value = '';
        searchInput.focus();
    }
}

function updateQty(id, delta) {
    const item = cart.find(i => i.id === id);
    if (item) {
        if (delta > 0 && item.qty >= item.maxStock) {
            soundError();
            SwalToast('warning', `Max stock is ${item.maxStock}`);
            return;
        }
        item.qty += delta;
        if (item.qty <= 0) {
            removeItem(id);
            return;
        }
        soundBeep();
    }
    saveCartToStorage();
    renderCart();
}

function setQtyDirect(id, inputEl) {
    const val = parseInt(inputEl.value) || 1;
    const item = cart.find(i => i.id === id);
    if (item) {
        item.qty = Math.min(Math.max(1, val), item.maxStock);
        soundBeep();
    }
    saveCartToStorage();
    renderCart();
}

function removeItem(id) {
    cart = cart.filter(i => i.id !== id);
    saveCartToStorage();
    renderCart();
}

function clearCart(notify = false) {
    if (cart.length === 0) return;
    cart = [];
    saveCartToStorage();
    renderCart();
    if (notify) SwalToast('info', 'Cart cleared.');
}

function renderCart() {
    const tbody = document.getElementById('cartTableBody');
    const countLabel = document.getElementById('cartItemCountLabel');
    const mobileCount = document.getElementById('mobileTabCartCount');
    const mobileFloatingItems = document.getElementById('lblMobileFloatingItems');
    const mobileFloatingTotal = document.getElementById('lblMobileFloatingTotal');

    const totalQty = cart.reduce((acc, item) => acc + item.qty, 0);

    if (mobileCount) mobileCount.textContent = totalQty;
    if (mobileFloatingItems) mobileFloatingItems.textContent = `${cart.length} items (${totalQty} copies)`;

    if (cart.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center py-4 text-muted small">
                    <i class="fas fa-barcode fs-3 text-muted opacity-25 d-block mb-1"></i>
                    Cart is empty. Scan barcode or tap books to add.
                </td>
            </tr>`;
        countLabel.textContent = '0 items';
        document.getElementById('lblSubtotal').textContent = '৳0.00';
        document.getElementById('lblCalculatedDiscount').textContent = '৳0.00';
        document.getElementById('lblTotal').textContent = '৳0.00';
        document.getElementById('lblChangeReturn').textContent = '৳0.00';
        if (mobileFloatingTotal) mobileFloatingTotal.textContent = '৳0.00';
        return;
    }

    countLabel.textContent = `${cart.length} items (${totalQty} pcs)`;

    let html = '';
    cart.forEach(item => {
        const itemTotal = item.price * item.qty;
        html += `
            <tr id="cartRow${item.id}">
                <td class="ps-3">
                    <div class="fw-semibold text-dark text-truncate small" style="max-width: 160px;" title="${item.title}">${item.title}</div>
                    <small class="text-muted font-monospace" style="font-size: 10px;">৳${item.price.toFixed(0)} × ${item.qty}</small>
                </td>
                <td class="text-center">
                    <div class="d-inline-flex align-items-center gap-1 bg-light border rounded-pill px-1.5 py-0.5">
                        <button type="button" class="btn btn-sm btn-link p-0 text-dark text-decoration-none" style="width: 20px; height: 20px; line-height: 1;" onclick="updateQty(${item.id}, -1)">
                            <i class="fas fa-minus" style="font-size: 9px;"></i>
                        </button>
                        <input type="number" min="1" max="${item.maxStock}" value="${item.qty}" class="form-control form-control-sm p-0 text-center border-0 bg-transparent font-monospace fw-bold" style="width: 28px;" onchange="setQtyDirect(${item.id}, this)">
                        <button type="button" class="btn btn-sm btn-link p-0 text-dark text-decoration-none" style="width: 20px; height: 20px; line-height: 1;" onclick="updateQty(${item.id}, 1)">
                            <i class="fas fa-plus" style="font-size: 9px;"></i>
                        </button>
                    </div>
                </td>
                <td class="text-end fw-bold text-dark font-monospace">৳${itemTotal.toFixed(0)}</td>
                <td class="text-center pe-2">
                    <button type="button" class="btn btn-sm text-danger p-0" onclick="removeItem(${item.id})" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    calculateTotal();
}

function setDiscountMode(mode) {
    discountMode = mode;
    document.getElementById('btnDiscModePercent').classList.toggle('active', mode === 'percent');
    document.getElementById('btnDiscModeFlat').classList.toggle('active', mode === 'flat');
    document.getElementById('lblDiscountUnit').textContent = mode === 'percent' ? '%' : '৳';
    calculateTotal();
}

function applyPresetDiscount(percent, btnEl) {
    document.querySelectorAll('.disc-btn').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');
    setDiscountMode('percent');
    document.getElementById('txtDiscountInput').value = percent;
    calculateTotal();
}

function calculateTotal() {
    const subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
    const discInput = parseFloat(document.getElementById('txtDiscountInput').value) || 0;
    
    let calculatedDiscount = discountMode === 'percent' ? (subtotal * discInput) / 100 : discInput;
    calculatedDiscount = Math.min(subtotal, Math.max(0, calculatedDiscount));

    const netTotal = Math.max(0, subtotal - calculatedDiscount);

    document.getElementById('lblSubtotal').textContent = '৳' + subtotal.toFixed(2);
    document.getElementById('lblCalculatedDiscount').textContent = '৳' + calculatedDiscount.toFixed(2);
    document.getElementById('lblTotal').textContent = '৳' + netTotal.toFixed(2);
    
    const mobileFloatingTotal = document.getElementById('lblMobileFloatingTotal');
    if (mobileFloatingTotal) mobileFloatingTotal.textContent = '৳' + netTotal.toFixed(2);

    calculateChange();
}

function handlePaymentMethodChange() {
    const selectedMethod = document.querySelector('input[name="posPayMethod"]:checked')?.value || 'cash';
    const isSplit = selectedMethod === 'split';
    const isDigital = ['bkash', 'nagad', 'rocket', 'card', 'split'].includes(selectedMethod);
    const isCash = selectedMethod === 'cash';

    document.getElementById('splitPaymentDrawer').classList.toggle('d-none', !isSplit);
    document.getElementById('digitalTrxBox').classList.toggle('d-none', !isDigital);
    document.getElementById('cashTenderBox').classList.toggle('d-none', !isCash && !isSplit);

    calculateChange();
}

function setExactCash() {
    const subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
    const discInput = parseFloat(document.getElementById('txtDiscountInput').value) || 0;
    let calculatedDiscount = discountMode === 'percent' ? (subtotal * discInput) / 100 : discInput;
    const netTotal = Math.max(0, subtotal - calculatedDiscount);

    document.getElementById('txtTenderedCash').value = Math.ceil(netTotal);
    calculateChange();
}

function addTenderedCash(delta) {
    const current = parseFloat(document.getElementById('txtTenderedCash').value) || 0;
    document.getElementById('txtTenderedCash').value = current + delta;
    calculateChange();
}

function calculateChange() {
    const subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
    const discInput = parseFloat(document.getElementById('txtDiscountInput').value) || 0;
    let calculatedDiscount = discountMode === 'percent' ? (subtotal * discInput) / 100 : discInput;
    const netTotal = Math.max(0, subtotal - calculatedDiscount);

    const tendered = parseFloat(document.getElementById('txtTenderedCash').value) || 0;
    const change = Math.max(0, tendered - netTotal);

    document.getElementById('lblChangeReturn').textContent = '৳' + change.toFixed(2);
}

// -------------------------------------------------------------
// Checkout / Offline Queue
// -------------------------------------------------------------
function handleCheckout() {
    if (cart.length === 0) {
        soundError();
        SwalToast('warning', 'Please select books first.');
        return;
    }

    const subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
    const discInput = parseFloat(document.getElementById('txtDiscountInput').value) || 0;
    const calculatedDiscount = discountMode === 'percent' ? (subtotal * discInput) / 100 : discInput;
    const total = Math.max(0, subtotal - calculatedDiscount);

    const method = document.querySelector('input[name="posPayMethod"]:checked')?.value || 'cash';
    const customerPhone = document.getElementById('txtCustomerPhone').value.trim();
    const customerName = document.getElementById('txtCustomerName').value.trim();
    const trxId = document.getElementById('txtTrxId')?.value.trim();
    const tendered = parseFloat(document.getElementById('txtTenderedCash').value) || total;
    const change = Math.max(0, tendered - total);

    let paidCash = (method === 'cash') ? total : (method === 'split' ? (parseFloat(document.getElementById('txtSplitCash').value) || 0) : 0);
    let paidOnline = (method !== 'cash' && method !== 'split') ? total : (method === 'split' ? (parseFloat(document.getElementById('txtSplitOnline').value) || 0) : 0);

    const btn = document.getElementById('btnCompleteSale');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';

    // Check if offline
    if (!navigator.onLine) {
        handleOfflineCheckout({
            items: [...cart],
            subtotal,
            discount: calculatedDiscount,
            discount_percent: discountMode === 'percent' ? discInput : 0,
            total,
            payment_method: method,
            trx_id: trxId,
            paid_cash: paidCash,
            paid_online: paidOnline,
            customer_phone: customerPhone,
            customer_name: customerName,
            timestamp: new Date().toISOString(),
        });
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1.5"></i> Complete & Print (F4)';
        return;
    }

    fetch("{{ route('admin.pos.checkout') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            items: cart,
            subtotal,
            discount: calculatedDiscount,
            discount_percent: discountMode === 'percent' ? discInput : 0,
            total,
            payment_method: method,
            trx_id: trxId,
            paid_cash: paidCash,
            paid_online: paidOnline,
            tendered_amount: tendered,
            change_amount: change,
            customer_phone: customerPhone,
            customer_name: customerName,
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            soundSuccess();
            SwalToast('success', data.message);
            window.open(data.receipt_url + '?print=1', '_blank', 'width=420,height=650');
            clearCart();
            document.getElementById('txtCustomerPhone').value = '';
            document.getElementById('txtCustomerName').value = '';
            document.getElementById('txtTenderedCash').value = '';
            switchMobileView('books');
            setTimeout(() => location.reload(), 1400);
        } else {
            soundError();
            Swal.fire({ title: 'Error', text: data.message || 'Checkout failed.', icon: 'error' });
        }
    })
    .catch(() => {
        // Fallback to offline queue on network drop
        handleOfflineCheckout({
            items: [...cart],
            subtotal,
            discount: calculatedDiscount,
            discount_percent: discountMode === 'percent' ? discInput : 0,
            total,
            payment_method: method,
            trx_id: trxId,
            paid_cash: paidCash,
            paid_online: paidOnline,
            customer_phone: customerPhone,
            customer_name: customerName,
            timestamp: new Date().toISOString(),
        });
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1.5"></i> Complete & Print (F4)';
    });
}

function handleOfflineCheckout(payload) {
    const receiptNo = 'POS-OFF-' + Date.now().toString().slice(-6);
    payload.receipt_no = receiptNo;
    offlineSalesQueue.push(payload);
    saveOfflineQueueToStorage();

    soundSuccess();
    Swal.fire({
        title: 'Offline Bill Created',
        html: `Bill <strong>#${receiptNo}</strong> created in Offline Mode.<br><span class="text-success small">Will automatically sync to server when connected.</span>`,
        icon: 'success',
        confirmButtonText: 'OK'
    });

    clearCart();
    switchMobileView('books');
}

// -------------------------------------------------------------
// Hold / Recall (F8 / F9)
// -------------------------------------------------------------
function holdCurrentSale() {
    if (cart.length === 0) {
        soundError();
        SwalToast('warning', 'Cart is empty.');
        return;
    }

    const holdItem = {
        id: Date.now(),
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        items: [...cart],
        customerName: document.getElementById('txtCustomerName').value.trim() || 'Customer #' + (heldSales.length + 1),
        customerPhone: document.getElementById('txtCustomerPhone').value.trim(),
        total: cart.reduce((acc, i) => acc + (i.price * i.qty), 0)
    };

    heldSales.push(holdItem);
    saveHeldSalesToStorage();
    updateHeldSalesBadge();

    clearCart();
    soundBeep();
    SwalToast('info', `Bill for ${holdItem.customerName} held.`);
}

function openHeldSalesModal() {
    renderHeldSalesList();
    new bootstrap.Modal(document.getElementById('heldSalesModal')).show();
}

function renderHeldSalesList() {
    const container = document.getElementById('heldSalesListContainer');
    if (heldSales.length === 0) {
        container.innerHTML = `<div class="text-center py-4 text-muted small">No held bills currently.</div>`;
        return;
    }

    let html = `
        <table class="table table-sm align-middle mb-0 font-monospace small">
            <thead class="table-light">
                <tr><th>#</th><th>Time</th><th>Customer</th><th>Items</th><th>Total</th><th class="text-end">Action</th></tr>
            </thead>
            <tbody>
    `;

    heldSales.forEach((h, idx) => {
        html += `
            <tr>
                <td>${idx + 1}</td>
                <td>${h.time}</td>
                <td class="fw-semibold">${h.customerName}</td>
                <td><span class="badge bg-light text-dark border">${h.items.length} items</span></td>
                <td class="fw-bold">৳${h.total.toFixed(0)}</td>
                <td class="text-end">
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-2.5 py-0.5 fw-bold" onclick="recallHeldSale(${h.id})">Resume</button>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0.5 ms-1" onclick="deleteHeldSale(${h.id})"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
    });

    html += `</tbody></table>`;
    container.innerHTML = html;
}

function recallHeldSale(id) {
    const found = heldSales.find(h => h.id === id);
    if (!found) return;

    cart = [...found.items];
    document.getElementById('txtCustomerName').value = found.customerName || '';
    document.getElementById('txtCustomerPhone').value = found.customerPhone || '';
    
    heldSales = heldSales.filter(h => h.id !== found.id);
    saveHeldSalesToStorage();
    updateHeldSalesBadge();
    saveCartToStorage();
    renderCart();

    bootstrap.Modal.getInstance(document.getElementById('heldSalesModal'))?.hide();
    switchMobileView('cart');
    soundSuccess();
    SwalToast('success', 'Held bill resumed.');
}

function deleteHeldSale(id) {
    heldSales = heldSales.filter(h => h.id !== id);
    saveHeldSalesToStorage();
    updateHeldSalesBadge();
    renderHeldSalesList();
    SwalToast('info', 'Held bill deleted.');
}

function updateHeldSalesBadge() {
    const badge = document.getElementById('heldSalesBadge');
    if (badge) badge.textContent = heldSales.length;
}

function saveCartToStorage() { localStorage.setItem('pos_active_cart', JSON.stringify(cart)); }
function loadCartFromStorage() {
    try {
        const saved = localStorage.getItem('pos_active_cart');
        if (saved) cart = JSON.parse(saved) || [];
        renderCart();
    } catch(e) {}
}

function saveHeldSalesToStorage() { localStorage.setItem('pos_held_sales', JSON.stringify(heldSales)); }
function loadHeldSalesFromStorage() {
    try {
        const saved = localStorage.getItem('pos_held_sales');
        if (saved) heldSales = JSON.parse(saved) || [];
        updateHeldSalesBadge();
    } catch(e) {}
}

// -------------------------------------------------------------
// Filters & Search
// -------------------------------------------------------------
function filterByIdea(btnEl) {
    document.querySelectorAll('.pos-pill').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');

    executeSearch('', null, true);
}

function filterByCategory(catId, btnEl) {
    document.querySelectorAll('.pos-pill').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');

    const searchVal = document.getElementById('posSearchInput').value.trim();
    executeSearch(searchVal, catId, false);
}

function handleSearchDebounced(val) {
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        executeSearch(val);
    }, 180);
}

function executeSearch(query = '', catId = null, onlyIdea = false) {
    if (catId === null) {
        const activePill = document.querySelector('.pos-pill.active');
        if (activePill && activePill.classList.contains('pill-idea')) {
            onlyIdea = true;
        } else {
            catId = activePill?.getAttribute('onclick')?.match(/\(([^,]+)/)?.[1] || 'all';
        }
    }

    // If offline, search local offline catalog
    if (!navigator.onLine && offlineCatalog.length > 0) {
        let filtered = offlineCatalog;
        if (onlyIdea) {
            filtered = filtered.filter(b => b.is_idea);
        } else if (catId && catId !== 'all') {
            filtered = filtered.filter(b => b.category_id == catId);
        }
        if (query) {
            const qLower = query.toLowerCase();
            filtered = filtered.filter(b => 
                (b.title && b.title.toLowerCase().includes(qLower)) ||
                (b.author_name && b.author_name.toLowerCase().includes(qLower)) ||
                (b.isbn && b.isbn.includes(query)) ||
                (b.sku && b.sku.toLowerCase().includes(qLower))
            );
        }
        renderBookGrid(filtered.slice(0, 60));
        return;
    }

    const url = `/admin/pos/search?q=${encodeURIComponent(query)}&category_id=${catId || ''}&only_idea=${onlyIdea ? 1 : 0}`;
    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(books => renderBookGrid(books));
}

function renderBookGrid(books) {
    const grid = document.getElementById('posBookGrid');
    const emptyNotice = document.getElementById('noBookMatchNotice');

    if (!books || books.length === 0) {
        grid.innerHTML = '';
        emptyNotice.classList.remove('d-none');
        return;
    }

    emptyNotice.classList.add('d-none');
    let html = '';
    books.forEach(b => {
        html += `
            <div class="col-6 col-sm-4 col-md-3 pos-book-item" data-category="${b.category_id}">
                <div class="pos-book-card p-2 text-center h-100 d-flex flex-column justify-content-between ${b.is_idea ? 'is-idea' : ''}"
                     onclick="addToCart(${b.id}, '${b.title.replace(/'/g, "\\'")}', ${b.final_price}, ${b.stock_quantity}, '${b.sku || b.isbn || ''}')">
                    <div>
                        ${b.cover_url ? 
                            `<img src="${b.cover_url}" alt="${b.title}" class="pos-book-thumb mb-1 shadow-xs" loading="lazy">` : 
                            `<div class="pos-book-thumb d-flex align-items-center justify-content-center text-muted mb-1"><i class="fas fa-book fs-3 opacity-25"></i></div>`
                        }
                        <div class="fw-bold small text-dark line-clamp-1 mb-0.5" title="${b.title}" style="font-size: 0.8rem;">${b.title}</div>
                        <div class="text-muted small line-clamp-1 mb-1" style="font-size: 0.7rem;">${b.author_name}</div>
                    </div>
                    <div>
                        <div class="fw-bold text-primary font-monospace fs-6 mb-0.5">
                            ৳${b.final_price.toFixed(0)}
                            ${b.price > b.final_price ? `<small class="text-muted text-decoration-line-through fw-normal ms-1" style="font-size: 9px;">৳${b.price.toFixed(0)}</small>` : ''}
                        </div>
                        <span class="badge ${b.stock_quantity > 5 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle'} rounded-pill" style="font-size: 9px;">
                            Stock: ${b.stock_quantity}
                        </span>
                    </div>
                </div>
            </div>
        `;
    });
    grid.innerHTML = html;
}

function handleSearchKeydown(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const inputVal = e.target.value.trim();
        if (!inputVal) return;

        fetch(`/admin/pos/search?q=${encodeURIComponent(inputVal)}`, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(books => {
                if (books && books.length > 0) {
                    const b = books[0];
                    addToCart(b.id, b.title, b.final_price, b.stock_quantity, b.sku || b.isbn);
                } else {
                    soundError();
                    SwalToast('error', 'Book not found.');
                }
            });
    }
}

// -------------------------------------------------------------
// Continuous Multi-Barcode Camera Scanner
// -------------------------------------------------------------
let cameraScanCount = 0;

function openCameraScannerModal() {
    new bootstrap.Modal(document.getElementById('cameraScannerModal')).show();
    cameraScanningActive = true;
    cameraScanCount = 0;
    document.getElementById('cameraScannedCounter').textContent = '0';
    document.getElementById('cameraLastScannedBox').classList.add('d-none');

    const video = document.getElementById('cameraScannerVideo');
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } } })
            .then(stream => {
                video.srcObject = stream;
                video.play();
                initContinuousQuagga();
            })
            .catch(err => {
                console.error("Camera error:", err);
                document.getElementById('scannerStatusText').textContent = 'Camera Denied';
            });
    }
}

function initContinuousQuagga() {
    if (typeof Quagga === 'undefined') return;

    Quagga.init({
        inputStream: {
            name: "Live",
            type: "LiveStream",
            target: document.getElementById('cameraScannerVideo'),
            constraints: { facingMode: "environment" }
        },
        decoder: {
            readers: ["ean_reader", "ean_8_reader", "code_128_reader", "upc_reader", "code_39_reader"]
        }
    }, function(err) {
        if (!err) {
            Quagga.start();
        }
    });

    Quagga.onDetected(function(result) {
        if (!cameraScanningActive) return;
        const code = result?.codeResult?.code;
        const now = Date.now();

        // Prevent duplicate spam scans within 1.5 seconds for same barcode
        if (code && (code !== lastScannedCode || now - lastScannedTime > 1500)) {
            lastScannedCode = code;
            lastScannedTime = now;
            handleCameraBarcodeDetected(code);
        }
    });
}

function handleCameraBarcodeDetected(code) {
    fetch(`/admin/pos/search?q=${encodeURIComponent(code)}`, { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(books => {
            if (books && books.length > 0) {
                const b = books[0];
                addToCart(b.id, b.title, b.final_price, b.stock_quantity, b.sku || b.isbn);
                cameraScanCount++;
                document.getElementById('cameraScannedCounter').textContent = cameraScanCount;
                
                const alertBox = document.getElementById('cameraLastScannedBox');
                document.getElementById('cameraLastScannedTitle').textContent = b.title;
                alertBox.classList.remove('d-none');
            } else {
                soundError();
            }
        });
}

function stopCameraScanner() {
    cameraScanningActive = false;
    if (typeof Quagga !== 'undefined') {
        try { Quagga.stop(); } catch(e) {}
    }
    const video = document.getElementById('cameraScannerVideo');
    if (video && video.srcObject) {
        video.srcObject.getTracks().forEach(t => t.stop());
        video.srcObject = null;
    }
}

// -------------------------------------------------------------
// Void, Z-Report & Shortcuts Setup
// -------------------------------------------------------------
function voidSale(saleId, receiptNo) {
    Swal.fire({
        title: `Void Bill #${receiptNo}?`,
        text: 'This will restore book inventory and deduct from cash drawer.',
        input: 'text',
        inputPlaceholder: 'Reason for void (e.g., Customer refund)',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Void Sale',
        confirmButtonColor: '#ef4444'
    }).then(res => {
        if (res.isConfirmed) {
            fetch(`/admin/pos/void/${saleId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ reason: res.value || '' })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    SwalToast('success', data.message);
                    setTimeout(() => location.reload(), 1200);
                } else {
                    Swal.fire({ title: 'Error', text: data.message || 'Error occurred.', icon: 'error' });
                }
            });
        }
    });
}

function openShiftReportModal() {
    new bootstrap.Modal(document.getElementById('shiftReportModal')).show();
    const content = document.getElementById('shiftReportContent');
    content.innerHTML = `<div class="text-center py-4"><i class="fas fa-spinner fa-spin fs-3 text-primary"></i></div>`;

    fetch("{{ route('admin.pos.shift-report') }}", { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = `
                    <div class="text-center mb-2.5">
                        <h6 class="fw-bold mb-0.5">${data.register_name}</h6>
                        <small class="text-muted font-monospace">${data.location} | Date: ${data.date}</small>
                    </div>
                    <div class="border rounded-3 p-2.5 bg-light mb-2 font-monospace small">
                        <div class="d-flex justify-content-between mb-1"><span>Opening Cash:</span><span class="fw-bold">৳${data.opening_cash.toFixed(2)}</span></div>
                        <div class="d-flex justify-content-between mb-1"><span>Gross Sales:</span><span class="fw-bold">৳${data.gross_sales.toFixed(2)}</span></div>
                        <div class="d-flex justify-content-between mb-1 text-danger"><span>Discounts:</span><span class="fw-bold">-৳${data.total_discount.toFixed(2)}</span></div>
                        <div class="d-flex justify-content-between fw-bold border-top pt-1 text-dark"><span>Net Sales:</span><span class="text-primary">৳${data.net_sales.toFixed(2)}</span></div>
                    </div>
                    <div class="border rounded-3 p-2.5 bg-light mb-2 font-monospace small">
                        <div class="d-flex justify-content-between mb-1"><span>Cash Collected:</span><span class="fw-bold text-success">৳${data.total_cash_collected.toFixed(2)}</span></div>
                        <div class="d-flex justify-content-between mb-1"><span>bKash Collected:</span><span class="fw-bold text-danger">৳${data.bkash_sales.toFixed(2)}</span></div>
                        <div class="d-flex justify-content-between"><span>Other Digital:</span><span class="fw-bold text-info">৳${(data.total_online - data.bkash_sales).toFixed(2)}</span></div>
                    </div>
                    <div class="p-2.5 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3 text-center">
                        <div class="small fw-bold text-success">Expected Drawer Cash:</div>
                        <div class="fs-4 fw-bold font-monospace text-success">৳${data.expected_drawer_cash.toFixed(2)}</div>
                        <small class="text-muted" style="font-size: 10px;">Total Bills: ${data.total_bills} | Total Copies: ${data.total_books_sold}</small>
                    </div>
                `;
            }
        });
}

function handleCustomerPhoneLookup(phone) {
    if (phone.length >= 4) {
        fetch(`/admin/pos/customers/search?q=${encodeURIComponent(phone)}`, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(customers => {
                if (customers && customers.length > 0) {
                    const nameInput = document.getElementById('txtCustomerName');
                    if (nameInput && !nameInput.value) nameInput.value = customers[0].name;
                }
            });
    }
}

function setupKeyboardShortcuts() {
    document.addEventListener('keydown', (e) => {
        if (e.key === 'F2' || (e.key === '/' && document.activeElement.tagName !== 'INPUT')) {
            e.preventDefault();
            switchMobileView('books');
            document.getElementById('posSearchInput')?.focus();
        }
        if (e.key === 'F4' || (e.ctrlKey && e.key === 'Enter')) {
            e.preventDefault();
            handleCheckout();
        }
        if (e.key === 'F8') {
            e.preventDefault();
            holdCurrentSale();
        }
        if (e.key === 'F9') {
            e.preventDefault();
            openHeldSalesModal();
        }
    });
}

function filterRecentTransactions(q) {
    const query = q.toLowerCase();
    document.querySelectorAll('#recentTxTableBody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
    });
}
</script>
@endpush
@endsection
