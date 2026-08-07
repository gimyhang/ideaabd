<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermission } from '@/Composables/usePermission';

const { can } = usePermission();
import Badge from '@/Components/UI/Badge.vue';
import ConfirmDeleteModal from '@/Components/UI/ConfirmDeleteModal.vue';

interface OrderItem {
    id: number;
    order_number: string;
    customer_name: string;
    customer_phone: string;
    payment_method: string;
    payment_status: string;
    payment_status_label: string;
    order_status: string;
    order_status_label: string;
    total_amount: number;
    items_count: number;
    created_at: string;
}

interface PaginatedOrders {
    data: OrderItem[];
    total: number;
    links: any[];
}

const props = defineProps<{
    orders: PaginatedOrders;
    filters: {
        search?: string;
        order_status?: string;
        payment_status?: string;
    };
}>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.order_status || '');
const paymentFilter = ref(props.filters.payment_status || '');

let searchTimeout: any = null;
watch([search, statusFilter, paymentFilter], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            '/admin/orders',
            {
                search: search.value,
                order_status: statusFilter.value,
                payment_status: paymentFilter.value,
            },
            { preserveState: true, replace: true }
        );
    }, 300);
});

// Order Status Update Modal
const selectedOrder = ref<OrderItem | null>(null);
const showStatusModal = ref(false);
const newStatus = ref('');

function openStatusModal(order: OrderItem) {
    selectedOrder.value = order;
    newStatus.value = order.order_status;
    showStatusModal.value = true;
}

function updateOrderStatus() {
    if (!selectedOrder.value) return;
    router.put(
        `/admin/orders/${selectedOrder.value.id}`,
        { order_status: newStatus.value },
        {
            onSuccess: () => {
                showStatusModal.value = false;
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

<template>
    <AdminLayout>
        <Head title="Orders Management — Admin Portal" />

        <template #header>Customer Orders</template>

        <div class="p-6 space-y-6">
            <!-- Header Card Banner -->
            <div class="bg-gradient-to-r from-sky-950 via-slate-900 to-indigo-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row md:items-center md:justify-between gap-6 border border-slate-800">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-sky-300 text-xs font-bold uppercase tracking-wider backdrop-blur-md">
                        📦 Sales & Fulfillment Engine
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black font-heading tracking-tight text-white">
                        Customer Orders Directory
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-300 max-w-xl">
                        Manage customer purchases, update order delivery statuses with State Pattern audit logs, and monitor payment attempts.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="bg-white/10 backdrop-blur-md rounded-2xl px-5 py-3 text-center border border-white/10">
                        <span class="block text-2xl font-black font-mono text-sky-400">{{ orders.total }}</span>
                        <span class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider">Total Orders</span>
                    </div>
                </div>
            </div>

            <!-- Table Container Panel -->
            <div class="bg-white dark:bg-zinc-950 rounded-3xl border border-slate-200 dark:border-zinc-800 shadow-sm overflow-hidden space-y-4 p-6">
                <!-- Filters & Search Toolbar -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- Search Input -->
                    <div class="relative flex-1 max-w-md">
                        <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by order #, customer name, or phone..."
                            class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-900 text-xs font-medium focus:outline-none focus:border-sky-500 text-slate-900 dark:text-white"
                        />
                    </div>

                    <!-- Filter Dropdowns -->
                    <div class="flex items-center gap-3">
                        <select
                            v-model="statusFilter"
                            class="px-3 py-2.5 rounded-2xl border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-900 text-xs font-bold text-slate-700 dark:text-slate-300 focus:outline-none"
                        >
                            <option value="">All Order Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>

                        <select
                            v-model="paymentFilter"
                            class="px-3 py-2.5 rounded-2xl border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-900 text-xs font-bold text-slate-700 dark:text-slate-300 focus:outline-none"
                        >
                            <option value="">All Payment Statuses</option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-zinc-900 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200 dark:border-zinc-800">
                            <tr>
                                <th class="py-3.5 px-4">Order #</th>
                                <th class="py-3.5 px-4">Customer</th>
                                <th class="py-3.5 px-4">Method</th>
                                <th class="py-3.5 px-4">Payment</th>
                                <th class="py-3.5 px-4">Order Status</th>
                                <th class="py-3.5 px-4">Total</th>
                                <th class="py-3.5 px-4">Date</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800 font-medium">
                            <tr v-for="order in orders.data" :key="order.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-900/50 transition">
                                <td class="py-4 px-4 font-mono font-bold text-sky-600 dark:text-sky-400">
                                    {{ order.order_number }}
                                </td>
                                <td class="py-4 px-4 space-y-0.5">
                                    <span class="block font-bold text-slate-900 dark:text-white">{{ order.customer_name }}</span>
                                    <span class="block text-[11px] text-slate-400 font-mono">{{ order.customer_phone }}</span>
                                </td>
                                <td class="py-4 px-4 font-medium uppercase text-[11px]">
                                    {{ order.payment_method }}
                                </td>
                                <td class="py-4 px-4">
                                    <Badge :variant="getPaymentBadgeVariant(order.payment_status)">
                                        {{ order.payment_status_label }}
                                    </Badge>
                                </td>
                                <td class="py-4 px-4">
                                    <Badge :variant="getStatusBadgeVariant(order.order_status)">
                                        {{ order.order_status_label }}
                                    </Badge>
                                </td>
                                <td class="py-4 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                    ৳{{ order.total_amount }}
                                </td>
                                <td class="py-4 px-4 text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ order.created_at }}
                                </td>
                                <td class="py-4 px-4 text-right space-x-2">
                                    <!-- View Details Button -->
                                    <Link
                                        v-if="can('show-orders') || can('index-orders') || can('edit-orders')"
                                        :href="`/admin/orders/${order.id}`"
                                        class="inline-flex items-center justify-center p-2 rounded-xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 hover:bg-sky-100 transition"
                                        title="View Detailed Order"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </Link>

                                    <!-- Quick Change Status Button -->
                                    <button
                                        v-if="can('edit-orders') || can('index-orders')"
                                        type="button"
                                        @click="openStatusModal(order)"
                                        class="inline-flex items-center justify-center p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 hover:bg-amber-100 transition cursor-pointer"
                                        title="Update Order Status"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="orders.data.length === 0">
                                <td colspan="8" class="py-8 text-center text-slate-400">
                                    No customer orders match your search query.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Status Change Modal -->
        <div v-if="showStatusModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-white dark:bg-zinc-900 rounded-3xl p-6 shadow-2xl space-y-4 border border-slate-200 dark:border-zinc-800">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white font-heading">
                    Update Status for {{ selectedOrder?.order_number }}
                </h3>
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300">Select New Status</label>
                    <select
                        v-model="newStatus"
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs font-bold text-slate-900 dark:text-white"
                    >
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="showStatusModal = false"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-zinc-800"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="updateOrderStatus"
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-sky-600 hover:bg-sky-500 text-white shadow-md shadow-sky-600/30"
                    >
                        Save Transition
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
