<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermission } from '@/Composables/usePermission';

const { can } = usePermission();
import Badge from '@/Components/UI/Badge.vue';

interface HistoryItem {
    id: number;
    old_status: string | null;
    new_status: string;
    reason: string;
    changed_by: string;
    created_at: string;
}

interface PaymentAttemptItem {
    id: number;
    attempt_number: number;
    payment_method: string;
    gateway_transaction_id: string | null;
    status: string;
    ip_address: string | null;
    created_at: string;
}

interface OrderItemDetail {
    id: number;
    book_title: string;
    sku: string;
    unit_price: number;
    quantity: number;
    subtotal: number;
    format: string;
    cover_url: string;
}

interface OrderDetail {
    id: number;
    order_number: string;
    shipping_name: string;
    shipping_phone: string;
    shipping_email: string;
    delivery_address: string;
    payment_method: string;
    payment_status: string;
    payment_status_label: string;
    order_status: string;
    order_status_label: string;
    subtotal: number;
    shipping_fee: number;
    discount_amount: number;
    total_amount: number;
    transaction_id: string | null;
    created_at: string;
    notes: string | null;
    items: OrderItemDetail[];
    histories: HistoryItem[];
    payment_attempts: PaymentAttemptItem[];
}

const props = defineProps<{
    order: OrderDetail;
}>();

const newStatus = ref(props.order.order_status);
const transitionReason = ref('');

function submitStatusUpdate() {
    router.put(
        `/admin/orders/${props.order.id}`,
        {
            order_status: newStatus.value,
            reason: transitionReason.value || undefined,
        },
        {
            onSuccess: () => {
                transitionReason.value = '';
            },
        }
    );
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

function printInvoice() {
    const iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    iframe.src = `/admin/orders/${props.order.id}/invoice`;

    iframe.onload = () => {
        setTimeout(() => {
            iframe.contentWindow?.focus();
            iframe.contentWindow?.print();
            setTimeout(() => {
                if (document.body.contains(iframe)) {
                    document.body.removeChild(iframe);
                }
            }, 2000);
        }, 200);
    };

    document.body.appendChild(iframe);
}
</script>

<template>
    <AdminLayout>
        <Head :title="`Order ${order.order_number} Details — Admin Portal`" />

        <template #header>Order Details — {{ order.order_number }}</template>

        <div class="p-6 space-y-6 font-sans">
            <!-- Header Navigation Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-zinc-950 rounded-3xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <Link href="/admin/orders" class="p-2 rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 transition">
                            ← Back to Orders
                        </Link>
                        <h1 class="text-xl font-bold font-heading text-slate-900 dark:text-white font-mono">
                            {{ order.order_number }}
                        </h1>
                        <Badge :variant="getStatusBadgeVariant(order.order_status)">
                            {{ order.order_status_label }}
                        </Badge>
                    </div>
                    <p class="text-xs text-slate-400">Placed on {{ order.created_at }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="printInvoice"
                        class="px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-md shadow-sky-600/20 transition flex items-center gap-1.5 cursor-pointer"
                    >
                        📄 Print Invoice
                    </button>
                </div>
            </div>

            <!-- Content Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left 2-Column Main Order Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Items Table Card -->
                    <div class="bg-white dark:bg-zinc-950 rounded-3xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-4">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Purchased Line Items
                        </h2>
                        <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                            <div v-for="item in order.items" :key="item.id" class="py-4 flex items-center gap-4">
                                <img :src="item.cover_url" :alt="item.book_title" class="w-12 h-16 object-cover rounded-lg border border-slate-200 dark:border-zinc-800" />
                                <div class="flex-1 space-y-0.5">
                                    <h4 class="font-bold text-xs text-slate-900 dark:text-white">{{ item.book_title }}</h4>
                                    <p class="text-[11px] text-slate-400 font-mono">SKU: {{ item.sku }} | Format: {{ item.format }}</p>
                                </div>
                                <div class="text-right text-xs font-mono">
                                    <div class="font-bold text-slate-900 dark:text-white">৳{{ item.subtotal }}</div>
                                    <div class="text-[11px] text-slate-400">{{ item.quantity }} × ৳{{ item.unit_price }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Subtotal & Total Breakdown -->
                        <div class="pt-4 border-t border-slate-100 dark:border-zinc-800 space-y-1.5 text-xs text-slate-600 dark:text-slate-400 font-mono max-w-xs ml-auto">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span>৳{{ order.subtotal }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Shipping Fee</span>
                                <span>৳{{ order.shipping_fee }}</span>
                            </div>
                            <div v-if="order.discount_amount > 0" class="flex justify-between text-emerald-600 dark:text-emerald-400">
                                <span>Discount</span>
                                <span>-৳{{ order.discount_amount }}</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-slate-200 dark:border-zinc-800 font-bold text-sm text-slate-900 dark:text-white">
                                <span>Grand Total</span>
                                <span class="text-sky-600 dark:text-sky-400">৳{{ order.total_amount }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Timeline Audit Log Card -->
                    <div class="bg-white dark:bg-zinc-950 rounded-3xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-4">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Order State Pattern Timeline Audit Log
                        </h2>
                        <div class="space-y-4 relative border-l-2 border-slate-100 dark:border-zinc-800 pl-4 ml-2">
                            <div v-for="history in order.histories" :key="history.id" class="relative space-y-1">
                                <div class="absolute -left-6 top-1.5 w-3 h-3 rounded-full bg-sky-500 ring-4 ring-white dark:ring-zinc-950"></div>
                                <div class="text-xs font-bold text-slate-900 dark:text-white uppercase">
                                    {{ history.old_status || 'Start' }} → {{ history.new_status }}
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ history.reason }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">By {{ history.changed_by }} at {{ history.created_at }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right 1-Column Action Sidebar -->
                <div class="space-y-6">
                    <!-- Update Status Action Panel -->
                    <div v-if="can('edit-orders') || can('index-orders')" class="bg-white dark:bg-zinc-950 rounded-3xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-4">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Transition Order Status
                        </h2>
                        <form @submit.prevent="submitStatusUpdate" class="space-y-3 text-xs">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">State Machine</label>
                                <select v-model="newStatus" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 font-bold">
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Reason / Note</label>
                                <input v-model="transitionReason" type="text" placeholder="Reason for status change..." class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-900" />
                            </div>
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-md">
                                Update Status
                            </button>
                        </form>
                    </div>

                    <!-- Customer & Delivery Address Card -->
                    <div class="bg-white dark:bg-zinc-950 rounded-3xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-3 text-xs">
                        <h2 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider">Customer Delivery</h2>
                        <div class="space-y-1 text-slate-600 dark:text-slate-300">
                            <div class="font-bold text-slate-900 dark:text-white text-sm">{{ order.shipping_name }}</div>
                            <div>📞 {{ order.shipping_phone }}</div>
                            <div v-if="order.shipping_email">✉️ {{ order.shipping_email }}</div>
                            <div class="pt-2 text-[11px] text-slate-500 font-medium">📍 {{ order.delivery_address }}</div>
                        </div>
                    </div>

                    <!-- Payment Attempts Card -->
                    <div class="bg-white dark:bg-zinc-950 rounded-3xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-3 text-xs">
                        <h2 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider">Payment Attempts</h2>
                        <div v-for="attempt in order.payment_attempts" :key="attempt.id" class="p-3 rounded-xl bg-slate-50 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 font-mono space-y-1">
                            <div class="font-bold text-slate-800 dark:text-slate-200">Attempt #{{ attempt.attempt_number }} ({{ attempt.payment_method }})</div>
                            <div class="text-[10px] text-slate-400">Ref: {{ attempt.gateway_transaction_id || 'N/A' }}</div>
                            <div class="text-[10px] uppercase font-bold text-emerald-600">{{ attempt.status }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
