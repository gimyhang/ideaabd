<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/UI/Button.vue';

export interface OrderItem {
    id: number;
    book_title: string;
    sku: string;
    unit_price: number;
    quantity: number;
    subtotal: number;
    format: string;
    cover_url: string;
}

export interface OrderReceipt {
    id: number;
    order_number: string;
    currency: string;
    shipping_name: string;
    shipping_phone: string;
    shipping_email: string;
    delivery_address: string;
    payment_method: string;
    payment_method_label?: string;
    payment_status: string;
    payment_status_label?: string;
    order_status: string;
    order_status_label?: string;
    subtotal: number;
    shipping_fee: number;
    discount_amount: number;
    coupon_code?: string | null;
    coupon_discount?: number;
    total_amount: number;
    transaction_id: string;
    payment_attempts_count?: number;
    max_payment_attempts?: number;
    created_at: string;
    items: OrderItem[];
}

const props = defineProps<{
    order: OrderReceipt;
}>();

const isPaid = computed(() => (props.order.payment_status || '').toLowerCase() === 'paid');
const isFailed = computed(() => (props.order.payment_status || '').toLowerCase() === 'failed');

function retryPayment() {
    router.post(`/payment/${props.order.order_number}/initiate`, {
        gateway: props.order.payment_method,
    });
}

function printReceipt() {
    if (typeof window !== 'undefined') {
        window.print();
    }
}
</script>

<template>
    <MainLayout>
        <Head :title="`Order ${order.order_number} Confirmed — BookVerse`" />

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 font-sans space-y-8">

            <!-- Success Hero Card -->
            <div class="bg-white p-8 border border-slate-200 rounded-2xl shadow-sm text-center space-y-4">
                <div
                    class="w-16 h-16 mx-auto rounded-full flex items-center justify-center text-3xl font-bold shadow-xs"
                    :class="isPaid ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600'"
                >
                    {{ isPaid ? '✓' : '⌛' }}
                </div>
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold font-heading text-slate-900">
                        {{ isPaid ? 'Thank You! Your Order is Confirmed' : 'Order Received — Payment Pending' }}
                    </h1>
                    <p class="text-xs text-slate-500 font-medium">
                        Order Number: <span class="font-bold text-sky-600 font-mono text-sm">{{ order.order_number }}</span>
                    </p>
                </div>

                <!-- Timeline Progress Steps -->
                <div class="pt-4 max-w-xl mx-auto border-t border-slate-100 grid grid-cols-4 gap-2 text-center">
                    <div class="space-y-1">
                        <span class="w-7 h-7 mx-auto rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center">1</span>
                        <span class="block text-[11px] font-bold text-emerald-600">Placed</span>
                    </div>
                    <div class="space-y-1" :class="{ 'opacity-60': order.order_status === 'pending' }">
                        <span
                            class="w-7 h-7 mx-auto rounded-full font-bold text-xs flex items-center justify-center"
                            :class="order.order_status !== 'pending' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600'"
                        >2</span>
                        <span class="block text-[11px] font-medium" :class="order.order_status !== 'pending' ? 'text-emerald-600 font-bold' : 'text-slate-600'">Processing</span>
                    </div>
                    <div class="space-y-1 opacity-60">
                        <span class="w-7 h-7 mx-auto rounded-full bg-slate-200 text-slate-600 font-bold text-xs flex items-center justify-center">3</span>
                        <span class="block text-[11px] font-medium text-slate-600">Shipped</span>
                    </div>
                    <div class="space-y-1 opacity-60">
                        <span class="w-7 h-7 mx-auto rounded-full bg-slate-200 text-slate-600 font-bold text-xs flex items-center justify-center">4</span>
                        <span class="block text-[11px] font-medium text-slate-600">Delivered</span>
                    </div>
                </div>
            </div>

            <!-- Printable Receipt Details Card -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden p-6 space-y-6">

                <!-- Meta Details Header -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 border-b border-slate-100 pb-4 text-xs">
                    <div>
                        <span class="block text-slate-400 font-medium">Order Date</span>
                        <span class="font-bold text-slate-800">{{ order.created_at }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-400 font-medium">Payment Method</span>
                        <span class="font-bold text-slate-800 uppercase">{{ order.payment_method_label || order.payment_method }}</span>
                    </div>
                    <div>
                        <span class="block text-slate-400 font-medium">Payment Status</span>
                        <span
                            class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border"
                            :class="isPaid
                                ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                : isFailed
                                ? 'bg-rose-50 text-rose-700 border-rose-200'
                                : 'bg-amber-50 text-amber-700 border-amber-200'"
                        >
                            {{ order.payment_status_label || order.payment_status }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-slate-400 font-medium">Transaction Reference</span>
                        <span class="font-mono font-bold text-slate-800">{{ order.transaction_id || 'N/A' }}</span>
                    </div>
                </div>

                <!-- Payment Retry Alert Container -->
                <div v-if="!isPaid && order.payment_method !== 'cod'" class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <div>
                        <div class="font-bold">⚠️ Payment Verification Pending or Failed</div>
                        <div class="text-[11px] text-amber-700 mt-0.5">
                            Attempt {{ order.payment_attempts_count || 1 }} of {{ order.max_payment_attempts || 5 }}. You can retry completing your payment below.
                        </div>
                    </div>
                    <button
                        @click="retryPayment"
                        class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs transition-all shadow-sm cursor-pointer shrink-0"
                    >
                        💳 Retry Payment Now
                    </button>
                </div>

                <!-- Shipping Address Snapshot -->
                <div class="bg-slate-50 p-4 rounded-xl text-xs space-y-1">
                    <h4 class="font-bold text-slate-900 font-heading">Shipping Recipient</h4>
                    <p class="text-slate-800 font-bold">{{ order.shipping_name }} ({{ order.shipping_phone }})</p>
                    <p class="text-slate-600">{{ order.delivery_address }}</p>
                </div>

                <!-- Itemized Receipt Table -->
                <div class="space-y-3">
                    <h4 class="font-bold text-xs text-slate-900 font-heading">Purchased Items</h4>
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="p-3">Book Title</th>
                                    <th class="p-3 text-center">Unit Price</th>
                                    <th class="p-3 text-center">Qty</th>
                                    <th class="p-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                <tr v-for="item in order.items" :key="item.id">
                                    <td class="p-3">
                                        <div class="flex items-center gap-2.5">
                                            <img :src="item.cover_url" :alt="item.book_title" class="w-8 h-11 object-contain rounded border border-slate-200 bg-white shrink-0" />
                                            <div>
                                                <span class="font-bold text-slate-900 block">{{ item.book_title }}</span>
                                                <span class="text-[11px] text-slate-400 font-mono">SKU: {{ item.sku }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3 text-center">৳{{ item.unit_price }}</td>
                                    <td class="p-3 text-center font-bold">{{ item.quantity }}</td>
                                    <td class="p-3 text-right font-bold text-slate-900">৳{{ item.subtotal }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Total Amount Summary -->
                <div class="flex flex-col items-end text-xs space-y-1 pt-2">
                    <div class="w-full sm:w-64 space-y-2 border-t border-slate-200 pt-3">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal</span>
                            <span>৳{{ order.subtotal }}</span>
                        </div>
                        <div v-if="order.coupon_code && order.discount_amount > 0" class="flex justify-between text-emerald-600 font-medium">
                            <span class="flex items-center gap-1">🎟️ Coupon ({{ order.coupon_code }})</span>
                            <span class="font-bold">-৳{{ order.discount_amount }}</span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Shipping Fee</span>
                            <span v-if="order.shipping_fee == 0" class="font-bold text-emerald-600">FREE</span>
                            <span v-else>৳{{ order.shipping_fee }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-sm text-slate-900 border-t border-slate-200 pt-2">
                            <span>Total Amount</span>
                            <span class="text-sky-600 font-mono">৳{{ order.total_amount }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action CTAs -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <Button variant="outline" size="sm" @click="printReceipt" class="cursor-pointer">
                    🖨️ Print Receipt
                </Button>

                <Link :href="route('catalog.index')">
                    <Button variant="brand" size="md" class="cursor-pointer font-bold">
                        Continue Shopping 📚
                    </Button>
                </Link>
            </div>
        </div>
    </MainLayout>
</template>
