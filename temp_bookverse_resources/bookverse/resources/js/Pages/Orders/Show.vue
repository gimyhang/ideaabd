

<template>
    <MainLayout>
        <Head :title="`Order ${order.order_number} Tracking — BookVerse`" />

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 font-sans space-y-6 sm:space-y-8">
            <!-- Header Bar (Responsive Layout) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200 shadow-xs">
                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <Link href="/orders" class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
                            ← Back
                        </Link>
                        <h1 class="text-base sm:text-xl font-bold font-heading font-mono text-slate-900">
                            {{ order.order_number }}
                        </h1>
                        <Badge :variant="getStatusBadgeVariant(order.order_status)">
                            {{ order.order_status_label }}
                        </Badge>
                    </div>
                    <p class="text-[11px] sm:text-xs text-slate-500">Placed on {{ order.created_at }}</p>
                </div>

                <!-- Customer Actions (Cancel or Request Return) -->
                <div class="flex items-center gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                    <button
                        v-if="order.can_cancel"
                        type="button"
                        @click="cancelOrder"
                        class="px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs border border-rose-200 transition cursor-pointer"
                    >
                        Cancel Order
                    </button>

                    <Link
                        v-if="order.can_request_return"
                        :href="`/orders/${order.order_number}/return`"
                        class="px-4 py-2.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 font-bold text-xs border border-sky-200 transition cursor-pointer"
                    >
                        🔄 Request Return & Refund
                    </Link>
                </div>
            </div>

            <!-- Order Timeline Lifecycle Component -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200 shadow-xs space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Order Delivery Lifecycle</h2>
                <OrderTimeline :status="order.order_status" :histories="order.histories" />
            </div>

            <!-- Main Receipt Card -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200 shadow-xs space-y-6">
                <!-- Meta Info Grid (Responsive Columns) -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 border-b border-slate-100 pb-4 text-xs">
                    <div>
                        <span class="block text-slate-400 font-medium text-[11px]">Payment Method</span>
                        <span class="font-bold text-slate-800 uppercase text-xs">{{ order.payment_method_label }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-400 font-medium text-[11px]">Payment Status</span>
                        <div class="mt-0.5">
                            <Badge :variant="getPaymentBadgeVariant(order.payment_status)">
                                {{ order.payment_status_label }}
                            </Badge>
                        </div>
                    </div>
                    <div>
                        <span class="block text-slate-400 font-medium text-[11px]">Reference</span>
                        <span class="font-mono font-bold text-slate-800 text-xs truncate block">{{ order.transaction_id || 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-400 font-medium text-[11px]">Grand Total</span>
                        <span class="font-mono font-bold text-sky-600 text-sm">৳{{ order.total_amount }}</span>
                    </div>
                </div>

                <!-- Purchased Items Table -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Ordered Items</h3>
                        <span v-if="order.order_status === 'delivered' && order.all_reviews_completed" class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                            All Reviews Completed
                        </span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        <div v-for="item in order.items" :key="item.id" class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <img :src="item.cover_url" :alt="item.book_title" class="w-10 h-14 sm:w-12 sm:h-16 object-cover rounded-lg border border-slate-200 shrink-0" />
                                <div class="space-y-1 text-xs">
                                    <div class="font-bold text-slate-900 leading-snug line-clamp-1 sm:line-clamp-2">{{ item.book_title }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">SKU: {{ item.sku }} | {{ item.format }}</div>

                                    <!-- Item Level Review Action -->
                                    <div v-if="item.book_slug" class="pt-0.5">
                                        <Link
                                            v-if="item.can_review"
                                            :href="`/books/${item.book_slug}#write-review`"
                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-[11px] shadow-2xs transition"
                                        >
                                            ⭐ Write Review
                                        </Link>
                                        <div v-else-if="item.already_reviewed" class="inline-flex items-center gap-2">
                                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                                ✅ Reviewed
                                            </span>
                                            <Link
                                                :href="`/books/${item.book_slug}#my-review`"
                                                class="text-xs text-sky-600 hover:underline font-bold"
                                            >
                                                ✏️ Edit Review
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right text-xs font-mono shrink-0 self-end sm:self-center">
                                <div class="font-bold text-slate-900">৳{{ item.subtotal }}</div>
                                <div class="text-[11px] text-slate-400">{{ item.quantity }} × ৳{{ item.unit_price }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Price Breakdown -->
                <div class="pt-4 border-t border-slate-100 space-y-1.5 text-xs text-slate-600 font-mono w-full sm:max-w-xs sm:ml-auto">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span>৳{{ order.subtotal }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Shipping Fee</span>
                        <span>৳{{ order.shipping_fee }}</span>
                    </div>
                    <div v-if="order.discount_amount > 0" class="flex justify-between text-emerald-600">
                        <span>Discount</span>
                        <span>-৳{{ order.discount_amount }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-slate-200 font-bold text-sm text-slate-900">
                        <span>Total Paid</span>
                        <span class="text-sky-600">৳{{ order.total_amount }}</span>
                    </div>
                </div>
            </div>

            <!-- Recipient & Delivery Address Card -->
            <div class="bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200 shadow-xs space-y-2 text-xs">
                <h3 class="font-bold text-slate-400 uppercase tracking-wider text-[11px]">Shipping Recipient</h3>
                <div class="space-y-1">
                    <div class="font-bold text-slate-900 text-sm">{{ order.shipping_name }}</div>
                    <div class="text-slate-600">📞 {{ order.shipping_phone }}</div>
                    <div v-if="order.shipping_email" class="text-slate-600">✉️ {{ order.shipping_email }}</div>
                    <div class="pt-2 text-slate-500 font-medium leading-relaxed">📍 {{ order.delivery_address }}</div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Badge from '@/Components/UI/Badge.vue';
import OrderTimeline from '@/Components/Orders/OrderTimeline.vue';

interface HistoryRecord {
    id: number;
    old_status: string | null;
    new_status: string;
    reason: string;
    changed_by: string;
    created_at: string;
}

interface OrderItemDetail {
    id: number;
    book_id?: number;
    book_title: string;
    book_slug?: string;
    sku: string;
    unit_price: number;
    quantity: number;
    subtotal: number;
    format: string;
    cover_url: string;
    can_review?: boolean;
    already_reviewed?: boolean;
    review_id?: number | null;
}

interface OrderDetail {
    id: number;
    order_number: string;
    created_at: string;
    shipping_name: string;
    shipping_phone: string;
    shipping_email: string;
    delivery_address: string;
    payment_method: string;
    payment_method_label: string;
    payment_status: string;
    payment_status_label: string;
    order_status: string;
    order_status_label: string;
    subtotal: number;
    shipping_fee: number;
    discount_amount: number;
    total_amount: number;
    transaction_id: string | null;
    can_cancel: boolean;
    can_request_return?: boolean;
    has_pending_reviews?: boolean;
    all_reviews_completed?: boolean;
    items: OrderItemDetail[];
    histories: HistoryRecord[];
}

const props = defineProps<{
    order: OrderDetail;
}>();

function cancelOrder() {
    router.post(`/orders/${props.order.order_number}/cancel`, {
        reason: 'Cancelled by customer from order tracking page',
    });
}

function getStatusBadgeVariant(status: string): 'default' | 'brand' | 'success' | 'warning' | 'error' | 'info' {
    switch (status.toLowerCase()) {
        case 'delivered':
        case 'completed':
            return 'success';
        case 'processing':
        case 'shipped':
            return 'info';
        case 'pending':
            return 'warning';
        case 'cancelled':
        case 'refunded':
            return 'error';
        default:
            return 'default';
    }
}

function getPaymentBadgeVariant(status: string): 'default' | 'brand' | 'success' | 'warning' | 'error' | 'info' {
    switch (status.toLowerCase()) {
        case 'paid':
        case 'captured':
            return 'success';
        case 'pending':
        case 'authorized':
            return 'warning';
        case 'failed':
        case 'cancelled':
            return 'error';
        default:
            return 'default';
    }
}
</script>