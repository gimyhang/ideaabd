<template>
    <Head title="Shopping Cart — BookVerse" />

    <MainLayout>
        <div class="bg-slate-50/50 min-h-screen py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 font-sans">

                <!-- Breadcrumbs -->
                <nav class="flex items-center gap-2 text-xs text-slate-500">
                    <Link href="/" class="hover:text-sky-600 transition">Home</Link>
                    <span>/</span>
                    <span class="font-semibold text-slate-800">Shopping Cart</span>
                </nav>

                <!-- Page Title -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/80 pb-5">
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-slate-900 to-slate-700 dark:from-white dark:to-zinc-300 bg-clip-text text-transparent font-heading">Shopping Cart</h1>
                        <p class="text-xs text-slate-500 mt-1 font-medium">
                            Review your selected books, update quantities, and proceed to checkout.
                        </p>
                    </div>

                    <Link v-if="cart.items && cart.items.length > 0" href="/catalog">
                        <button class="px-4 py-2 border border-slate-200 dark:border-zinc-800 text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 font-extrabold text-xs rounded-xl transition cursor-pointer">
                            ← Continue Shopping
                        </button>
                    </Link>
                </div>

                <!-- Cart Unavailable Warning Alert -->
                <div v-if="summary.has_unavailable_items" class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center gap-3 shadow-xs">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Some items in your cart are currently out of stock or unavailable. Please review before proceeding.</span>
                </div>

                <!-- Cart Layout: Grid when items exist -->
                <div v-if="cart.items && cart.items.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                    <!-- Cart Items List (2 cols) -->
                    <div class="lg:col-span-2 space-y-4">
                        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 sm:p-6 space-y-6 shadow-xs">

                            <!-- Item Row -->
                            <div
                                v-for="item in cart.items"
                                :key="item.id"
                                class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-slate-100 last:border-0 last:pb-0"
                            >
                                <!-- Cover & Book Info -->
                                <div class="flex items-start gap-4 flex-1 min-w-0">
                                    <Link :href="`/books/${item.book?.slug}`" class="flex-shrink-0 group">
                                        <img
                                            :src="item.book?.cover_url"
                                            :alt="item.book?.title"
                                            class="w-16 h-22 object-cover rounded-xl shadow-xs border border-slate-200/80 group-hover:scale-105 transition duration-300"
                                        />
                                    </Link>

                                    <div class="space-y-1 min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <Badge variant="brand" size="sm" class="capitalize">
                                                {{ item.format }}
                                            </Badge>
                                            <Badge v-if="!item.is_available" variant="error" size="sm">
                                                Unavailable
                                            </Badge>
                                        </div>

                                        <Link :href="`/books/${item.book?.slug}`" class="font-bold text-sm text-slate-900 hover:text-sky-600 transition line-clamp-1">
                                            {{ item.book?.title || 'Unknown Book' }}
                                        </Link>

                                        <p class="text-xs text-slate-500 line-clamp-1">
                                            By {{ item.book?.authors?.[0]?.name || 'Unknown Author' }}
                                            <span v-if="item.book?.publisher"> • {{ item.book.publisher.name }}</span>
                                        </p>

                                        <!-- Price Snapshot -->
                                        <div class="flex items-center gap-2 pt-1 text-xs">
                                            <span class="font-black text-slate-900">৳{{ item.final_price }}</span>
                                            <span v-if="Number(item.unit_price) > Number(item.final_price)" class="text-slate-400 line-through text-[11px]">
                                                ৳{{ item.unit_price }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quantity Controller & Subtotal -->
                                <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto border-t sm:border-0 pt-3 sm:pt-0 border-slate-100">

                                    <!-- Quantity Selector -->
                                    <div class="inline-flex items-center rounded-xl border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-950 p-0.5 shadow-xs shrink-0">
                                        <button
                                            type="button"
                                            @click="updateQuantity(item, item.quantity - 1)"
                                            :disabled="updatingItemId === item.id"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-600 dark:text-zinc-300 hover:bg-slate-200 dark:hover:bg-zinc-800 disabled:opacity-40 disabled:cursor-not-allowed transition font-bold text-sm select-none"
                                        >
                                            -
                                        </button>

                                        <span class="w-10 text-center text-xs font-bold font-mono text-slate-900 dark:text-white select-none">
                                            {{ item.quantity }}
                                        </span>

                                        <button
                                            type="button"
                                            @click="updateQuantity(item, item.quantity + 1)"
                                            :disabled="updatingItemId === item.id || (item.book && item.quantity >= item.book.stock_quantity)"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-600 dark:text-zinc-300 hover:bg-slate-200 dark:hover:bg-zinc-800 disabled:opacity-40 disabled:cursor-not-allowed transition font-bold text-sm select-none"
                                        >
                                            +
                                        </button>
                                    </div>

                                    <!-- Line Subtotal -->
                                    <div class="text-right min-w-[80px]">
                                        <div class="text-sm font-black text-slate-900 font-mono">
                                            ৳{{ item.subtotal }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-medium">Subtotal</div>
                                    </div>

                                    <!-- Remove Button -->
                                    <button
                                        type="button"
                                        @click="promptRemoveItem(item)"
                                        class="p-1.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition cursor-pointer"
                                        title="Remove Item"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                        </div>

                        <!-- Clear Cart Action -->
                        <div class="flex justify-end pt-2">
                            <button
                                @click="showClearModal = true"
                                class="text-xs font-bold text-slate-400 hover:text-rose-600 hover:underline flex items-center gap-1 cursor-pointer transition"
                            >
                                <span>Clear Shopping Cart</span>
                            </button>
                        </div>
                    </div>

                    <!-- Order Summary Card (1 col) -->
                    <div class="bg-white border border-slate-200/80 rounded-3xl p-6 space-y-6 shadow-xs sticky top-24">
                        <h3 class="text-lg font-bold text-slate-900 font-heading border-b border-slate-100 pb-3">
                            Order Summary
                        </h3>

                        <div class="space-y-3 text-xs">
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Items Subtotal ({{ summary.count }} copies)</span>
                                <span class="font-bold text-slate-900 font-mono">৳{{ summary.subtotal }}</span>
                            </div>

                            <div v-if="summary.savings_total > 0" class="flex items-center justify-between text-emerald-600 font-semibold bg-emerald-50 p-2.5 rounded-xl border border-emerald-200">
                                <span>Total Savings</span>
                                <span class="font-bold font-mono">-৳{{ summary.savings_total }}</span>
                            </div>

                            <div class="flex items-center justify-between text-slate-600">
                                <span>Estimated Shipping</span>
                                <span class="text-slate-500 italic">Calculated at checkout</span>
                            </div>
                        </div>

                        <!-- Promo Code Input Placeholder -->
                        <div class="space-y-2 pt-2 border-t border-slate-100">
                            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Promo Code</label>
                            <div class="flex gap-2">
                                <Input
                                    v-model="promoCode"
                                    placeholder="Enter code"
                                    class="text-xs flex-1"
                                />
                                <button class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition cursor-pointer">
                                    Apply
                                </button>
                            </div>
                        </div>

                        <!-- Grand Total -->
                        <div class="pt-4 border-t border-slate-200 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-base font-bold text-slate-900 font-heading">Total Amount</span>
                                <span class="text-2xl font-black text-sky-600 font-mono">৳{{ summary.grand_total }}</span>
                            </div>
                            <p class="text-[11px] text-slate-400">Includes all taxes where applicable.</p>
                        </div>

                        <!-- Proceed to Checkout Button -->
                        <Link :href="route('checkout.index')">
                            <button
                                :disabled="summary.has_unavailable_items"
                                class="w-full py-3.5 px-4 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 disabled:opacity-50 text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-lg shadow-sky-500/25 transition duration-300 transform active:scale-98 cursor-pointer flex items-center justify-center gap-1.5"
                            >
                                <span>Proceed to Checkout</span>
                                <span class="text-base font-normal">→</span>
                            </button>
                        </Link>
                    </div>

                </div>

                <!-- Empty Cart View -->
                <div v-else class="bg-white border border-slate-200/80 rounded-3xl p-12 text-center space-y-6 max-w-2xl mx-auto shadow-xs">
                    <div class="w-20 h-20 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto shadow-inner">
                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>

                    <div class="space-y-2">
                        <h2 class="text-xl font-bold text-slate-900 font-heading">Your shopping cart is empty</h2>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">
                            Looks like you haven't added any books to your cart yet. Explore our rich catalog and discover your next favorite read!
                        </p>
                    </div>

                    <Link href="/catalog">
                        <button class="px-6 py-2.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-md transition cursor-pointer">
                            Browse Book Catalog
                        </button>
                    </Link>
                </div>

                <!-- Confirm Clear Cart Modal -->
                <ConfirmDeleteModal
                    :show="showClearModal"
                    title="Clear Shopping Cart?"
                    message="Are you sure you want to remove all items from your shopping cart? This action cannot be undone."
                    @close="showClearModal = false"
                    @confirm="confirmClearCart"
                />

                <!-- Toast Notification -->
                <Toast
                    :show="showToast"
                    type="success"
                    :message="toastMessage"
                    @close="showToast = false"
                />
            </div>
        </div>
    </MainLayout>
</template>
<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import Badge from '@/Components/UI/Badge.vue';
import Toast from '@/Components/UI/Toast.vue';
import ConfirmDeleteModal from '@/Components/UI/ConfirmDeleteModal.vue';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Author {
    id: number;
    name: string;
}

interface Publisher {
    id: number;
    name: string;
}

interface Book {
    id: number;
    title: string;
    slug: string;
    cover_url: string;
    stock_quantity: number;
    is_active: boolean;
    trashed?: boolean;
    authors?: Author[];
    publisher?: Publisher;
}

interface CartItem {
    id: number;
    cart_id: number;
    book_id: number;
    quantity: number;
    unit_price: string | number;
    discount_price: string | number;
    final_price: string | number;
    format: string;
    subtotal: string | number;
    is_available: boolean;
    book?: Book;
}

interface Cart {
    id: number;
    currency: string;
    items: CartItem[];
}

interface CartSummary {
    count: number;
    subtotal: number;
    savings_total: number;
    grand_total: number;
    items_count: number;
    has_unavailable_items: boolean;
    currency: string;
}

const props = defineProps<{
    cart: Cart;
    summary: CartSummary;
}>();

// ─── State ────────────────────────────────────────────────────────────────────

const updatingItemId = ref<number | null>(null);
const promoCode = ref('');
const showDeleteModal = ref(false);
const itemToDelete = ref<CartItem | null>(null);
const showClearModal = ref(false);
const showToast = ref(false);
const toastMessage = ref('');

// Quantity update
function updateQuantity(item: CartItem, newQty: number) {
    if (newQty < 1) {
        promptRemoveItem(item);
        return;
    }

    updatingItemId.value = item.id;
    router.patch(`/cart/items/${item.id}`, { quantity: newQty }, {
        preserveScroll: true,
        onFinish: () => {
            updatingItemId.value = null;
        },
    });
}

function promptRemoveItem(item: CartItem) {
    router.delete(`/cart/items/${item.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toastMessage.value = 'Item removed from shopping cart.';
            showToast.value = true;
        },
    });
}

function confirmClearCart() {
    router.delete('/cart', {
        preserveScroll: true,
        onSuccess: () => {
            showClearModal.value = false;
            toastMessage.value = 'Shopping cart cleared.';
            showToast.value = true;
        },
    });
}
</script>