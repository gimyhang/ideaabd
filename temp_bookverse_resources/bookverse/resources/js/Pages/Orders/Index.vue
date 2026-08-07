

<template>
    <MainLayout>
        <Head title="My Orders — BookVerse" />

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 font-sans space-y-6 sm:space-y-8">
            <!-- Header Title Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-4 sm:pb-6">
                <div>
                    <h1 class="text-xl sm:text-3xl font-black font-heading tracking-tight text-slate-900">
                        My Orders Directory
                    </h1>
                    <p class="text-xs text-slate-500 font-medium mt-1">
                        Track order lifecycles, view receipts, and manage your book purchases.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-xs font-bold text-slate-700">
                        Total: {{ orders.total }} Orders
                    </span>
                </div>
            </div>

            <!-- Mobile Responsive Filter & Search Toolbar -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <!-- Status Tabs (Touch Scrollable on Mobile) -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 scrollbar-none max-w-full">
                    <button
                        @click="currentStatus = ''"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition cursor-pointer shrink-0"
                        :class="currentStatus === '' ? 'bg-sky-600 text-white shadow-md shadow-sky-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        All Orders
                    </button>
                    <button
                        @click="currentStatus = 'processing'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition cursor-pointer shrink-0"
                        :class="currentStatus === 'processing' ? 'bg-sky-600 text-white shadow-md shadow-sky-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        Processing
                    </button>
                    <button
                        @click="currentStatus = 'delivered'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition cursor-pointer shrink-0"
                        :class="currentStatus === 'delivered' ? 'bg-sky-600 text-white shadow-md shadow-sky-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        Delivered
                    </button>
                    <button
                        @click="currentStatus = 'cancelled'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition cursor-pointer shrink-0"
                        :class="currentStatus === 'cancelled' ? 'bg-sky-600 text-white shadow-md shadow-sky-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        Cancelled
                    </button>
                </div>

                <!-- Search Input (Full Width on Mobile) -->
                <div class="relative w-full sm:w-64">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search order number..."
                        class="w-full pl-9 pr-4 py-2.5 sm:py-2 rounded-xl border border-slate-200 bg-white text-xs font-medium focus:outline-none focus:border-sky-500"
                    />
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Orders List -->
            <div class="space-y-4">
                <div
                    v-for="order in orders.data"
                    :key="order.id"
                    class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200 p-4 sm:p-6 shadow-xs hover:shadow-md transition space-y-4"
                >
                    <!-- Order Header Metadata (Stacked on Mobile) -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div class="space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono font-bold text-sky-600 text-sm sm:text-base">{{ order.order_number }}</span>
                                <Badge :variant="getStatusBadgeVariant(order.order_status)">
                                    {{ order.order_status_label }}
                                </Badge>
                                <Badge :variant="getPaymentBadgeVariant(order.payment_status)">
                                    {{ order.payment_status_label }}
                                </Badge>

                                <!-- Review Progress Badges for Delivered Orders -->
                                <template v-if="order.order_status === 'delivered'">
                                    <span
                                        v-if="order.has_pending_reviews"
                                        class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 px-2 py-0.5 rounded-full"
                                    >
                                        ⭐ Pending Reviews
                                    </span>
                                    <span
                                        v-else-if="order.all_reviews_completed"
                                        class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 px-2 py-0.5 rounded-full"
                                    >
                                        Reviews Completed
                                    </span>
                                </template>
                            </div>
                            <p class="text-[11px] text-slate-400 font-medium">Placed on {{ order.created_at }}</p>
                        </div>
                        <div class="flex items-center justify-between sm:justify-end gap-3 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                            <div class="text-left sm:text-right">
                                <span class="block text-[10px] text-slate-400 font-medium uppercase">Total Amount</span>
                                <span class="font-mono font-bold text-slate-900 text-sm sm:text-base">৳{{ order.total_amount }}</span>
                            </div>

                            <!-- Write Review CTA if delivered with pending reviews -->
                            <Link
                                v-if="order.order_status === 'delivered' && order.has_pending_reviews"
                                :href="`/orders/${order.order_number}`"
                                class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-xs transition whitespace-nowrap"
                            >
                                ⭐ Write Review
                            </Link>

                            <Link
                                :href="`/orders/${order.order_number}`"
                                class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition whitespace-nowrap"
                            >
                                Details →
                            </Link>
                        </div>
                    </div>

                    <!-- Book Thumbnails Stack -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 overflow-x-auto py-1 scrollbar-none">
                            <div
                                v-for="item in order.items"
                                :key="item.id"
                                class="flex items-center gap-2 bg-slate-50 p-2 rounded-xl border border-slate-100 min-w-44 sm:min-w-48 shrink-0 justify-between"
                            >
                                <div class="flex items-center gap-2 min-w-0">
                                    <img :src="item.cover_url" :alt="item.book_title" class="w-9 h-12 sm:w-10 sm:h-13 object-cover rounded-md border border-slate-200 shrink-0" />
                                    <div class="space-y-0.5 text-xs truncate min-w-0">
                                        <div class="font-bold text-slate-900 truncate max-w-24 text-[11px] sm:text-xs">{{ item.book_title }}</div>
                                        <div class="text-[10px] sm:text-[11px] text-slate-500 font-mono">Qty: {{ item.quantity }} × ৳{{ item.unit_price }}</div>
                                    </div>
                                </div>

                                <!-- Item Level Review Action Buttons -->
                                <template v-if="item.book_slug">
                                    <Link
                                        v-if="item.can_review"
                                        :href="`/books/${item.book_slug}#write-review`"
                                        class="px-2 py-1 bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-bold rounded-lg shrink-0 transition"
                                        title="Write a review for this book"
                                    >
                                        ⭐ Review
                                    </Link>
                                    <Link
                                        v-else-if="item.already_reviewed"
                                        :href="`/books/${item.book_slug}#my-review`"
                                        class="px-2 py-1 bg-sky-100 hover:bg-sky-200 text-sky-700 text-[10px] font-bold rounded-lg shrink-0 transition"
                                        title="Edit your review"
                                    >
                                        ✏️ Edit
                                    </Link>
                                </template>
                            </div>
                        </div>
                        <div class="text-xs font-bold text-slate-500 self-end sm:self-center">
                            {{ order.items_count }} {{ order.items_count === 1 ? 'Item' : 'Items' }}
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="orders.data.length === 0" class="text-center py-12 sm:py-16 bg-white rounded-2xl sm:rounded-3xl border border-slate-200 space-y-3 p-4">
                    <div class="text-3xl sm:text-4xl">📚</div>
                    <h3 class="text-base sm:text-lg font-bold text-slate-800">No Orders Found</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        You have not placed any orders matching the current filter. Explore our catalog and purchase your next favorite book!
                    </p>
                    <Link href="/catalog" class="inline-block mt-2 px-5 py-2.5 rounded-xl bg-sky-600 text-white text-xs font-bold shadow-md shadow-sky-600/20">
                        Browse Book Catalog
                    </Link>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

interface OrderBookItem {
    id: number;
    book_id?: number;
    book_title: string;
    book_slug?: string;
    cover_url: string;
    quantity: number;
    unit_price: number;
    can_review?: boolean;
    already_reviewed?: boolean;
    review_id?: number | null;
}

interface CustomerOrderItem {
    id: number;
    order_number: string;
    created_at: string;
    payment_method: string;
    payment_status: string;
    payment_status_label: string;
    order_status: string;
    order_status_label: string;
    total_amount: number;
    items_count: number;
    can_cancel: boolean;
    has_pending_reviews?: boolean;
    all_reviews_completed?: boolean;
    items: OrderBookItem[];
}

interface PaginatedCustomerOrders {
    data: CustomerOrderItem[];
    total: number;
    links: any[];
}

const props = defineProps<{
    orders: PaginatedCustomerOrders;
    filters: {
        status?: string;
        search?: string;
    };
}>();

const search = ref(props.filters.search || '');
const currentStatus = ref(props.filters.status || '');

let searchTimeout: any = null;
watch([search, currentStatus], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            '/orders',
            {
                search: search.value,
                status: currentStatus.value || undefined,
            },
            { preserveState: true, replace: true }
        );
    }, 300);
});

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