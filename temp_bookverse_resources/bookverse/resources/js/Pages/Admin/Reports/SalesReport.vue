<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KpiCard from '@/Components/Admin/KpiCard.vue';
import DateRangePicker from '@/Components/Admin/DateRangePicker.vue';
import ExportButton from '@/Components/Admin/ExportButton.vue';

interface SalesMetrics {
    total_orders: number;
    total_revenue: number;
    total_discounts: number;
    average_order_value: number;
}

interface SalesChart {
    labels: string[];
    revenue: number[];
    orders: number[];
}

interface PaginatedTransactions {
    data: Array<{
        id: number;
        order_number: string;
        created_at: string;
        total_amount: number;
        subtotal: number;
        shipping_fee: number;
        discount_amount: number;
        payment_method: string;
        payment_status: string;
        user?: {
            name: string;
            email: string;
        };
    }>;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

const props = defineProps<{
    metrics: SalesMetrics;
    chart: SalesChart;
    transactions: PaginatedTransactions;
    filters: {
        start_date: string;
        end_date: string;
    };
}>();

// Simple SVG Chart calculations
const maxRevenue = computed(() => {
    const max = Math.max(...props.chart.revenue, 100);
    return Math.ceil(max * 1.15);
});

const points = computed(() => {
    const data = props.chart.revenue;
    const count = data.length;
    if (count === 0) return [];

    const width = 600;
    const height = 200;
    const step = count > 1 ? width / (count - 1) : width;

    return data.map((val, i) => {
        const x = i * step;
        const y = height - (val / maxRevenue.value) * height;
        return { x, y, value: val, label: props.chart.labels[i] };
    });
});

const pathD = computed(() => {
    if (points.value.length === 0) return '';
    return points.value.reduce((acc, point, i) => {
        return i === 0 ? `M ${point.x},${point.y}` : `${acc} L ${point.x},${point.y}`;
    }, '');
});

const areaD = computed(() => {
    if (points.value.length === 0) return '';
    const lastX = points.value[points.value.length - 1].x;
    return `${pathD.value} L ${lastX},200 L 0,200 Z`;
});
</script>

<template>
    <Head title="Sales & Commerce Report — BookVerse" />

    <AdminLayout>
        <template #header>
            Sales & Commerce Reports
        </template>

        <div class="p-6 sm:p-8 space-y-8 font-sans">
            <!-- Header Title & Action Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-emerald-500/10 via-sky-500/10 to-indigo-500/10 dark:from-emerald-950/30 dark:via-sky-950/30 dark:to-indigo-950/30 p-6 rounded-3xl border border-emerald-200/50 dark:border-emerald-900/30">
                <div class="space-y-1">
                    <h1 class="text-xl sm:text-2xl font-black font-heading text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-2xl bg-emerald-500 text-white flex items-center justify-center text-lg shadow-md shadow-emerald-500/25">🛍️</span>
                        Sales & Revenue Analytics
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 font-mono">
                        Real-time revenue performance, order volume & transaction records
                    </p>
                </div>

                <ExportButton type="sales" :start-date="filters.start_date" :end-date="filters.end_date" />
            </div>

            <!-- Date Range Filter Toolbar -->
            <DateRangePicker :start-date="filters.start_date" :end-date="filters.end_date" />

            <!-- Aggregate metrics grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <KpiCard
                    title="Total Revenue"
                    :value="`৳${metrics.total_revenue.toLocaleString()}`"
                    icon="💰"
                    variant="emerald"
                    subtitle="Paid order collections in range"
                />

                <KpiCard
                    title="Total Sales"
                    :value="metrics.total_orders"
                    icon="📦"
                    variant="indigo"
                    subtitle="Fulfillment order count"
                />

                <KpiCard
                    title="Average Order Value"
                    :value="`৳${metrics.average_order_value.toLocaleString()}`"
                    icon="📊"
                    variant="sky"
                    subtitle="Average transaction size (AOV)"
                />

                <KpiCard
                    title="Discounts Claimed"
                    :value="`৳${metrics.total_discounts.toLocaleString()}`"
                    icon="🎟️"
                    variant="amber"
                    subtitle="Total promotional discount amount"
                />
            </div>

            <!-- Revenue Trend SVG Chart -->
            <div class="p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-3">
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900 dark:text-zinc-100 font-heading flex items-center gap-2">
                            📈 Revenue Growth Trend
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 font-mono">Daily sales collection breakdown in BDT</p>
                    </div>
                </div>

                <div class="relative pt-4 pb-2">
                    <svg class="w-full h-48 overflow-visible" viewBox="0 0 600 200" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="salesGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.3" />
                                <stop offset="100%" stop-color="#059669" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        <line x1="0" y1="50" x2="600" y2="50" class="stroke-slate-100 dark:stroke-zinc-800" stroke-dasharray="4 4" />
                        <line x1="0" y1="100" x2="600" y2="100" class="stroke-slate-100 dark:stroke-zinc-800" stroke-dasharray="4 4" />
                        <line x1="0" y1="150" x2="600" y2="150" class="stroke-slate-100 dark:stroke-zinc-800" stroke-dasharray="4 4" />
                        <path :d="areaD" fill="url(#salesGrad)" />
                        <path :d="pathD" class="stroke-emerald-500 dark:stroke-emerald-400" stroke-width="3.5" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>

            <!-- Detailed Transactions log table -->
            <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-slate-200/80 dark:border-zinc-800 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between">
                    <h3 class="font-extrabold text-base text-slate-900 dark:text-zinc-100 font-heading flex items-center gap-2">
                        🧾 Commerce Transactions Log
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 dark:bg-zinc-800/60 border-b border-slate-100 dark:border-zinc-800 text-slate-500 dark:text-zinc-400 font-extrabold uppercase font-mono">
                                <th class="px-6 py-3.5">Order Info</th>
                                <th class="px-6 py-3.5">Customer</th>
                                <th class="px-6 py-3.5">Payment</th>
                                <th class="px-6 py-3.5">Amount</th>
                                <th class="px-6 py-3.5 text-right">Invoice</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800 text-slate-700 dark:text-zinc-300">
                            <tr v-for="order in transactions.data" :key="order.id" class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                                <td class="px-6 py-4">
                                    <span class="font-black text-slate-900 dark:text-zinc-100 block font-mono">{{ order.order_number }}</span>
                                    <span class="text-[10px] text-slate-400 dark:text-zinc-500 font-mono">{{ order.created_at }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold block text-slate-900 dark:text-zinc-200">{{ order.user?.name ?? 'Guest Customer' }}</span>
                                    <span class="text-[10px] text-slate-400 dark:text-zinc-500 font-mono">{{ order.user?.email ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 capitalize font-bold text-slate-600 dark:text-zinc-400">
                                    {{ order.payment_method }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-black text-slate-900 dark:text-zinc-100 font-mono text-sm">৳{{ order.total_amount }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a
                                        :href="`/admin/orders/${order.id}/invoice`"
                                        target="_blank"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-50 dark:bg-sky-950/50 hover:bg-sky-100 dark:hover:bg-sky-900/60 text-sky-600 dark:text-sky-400 font-bold border border-sky-200/80 dark:border-sky-800/50 transition"
                                    >
                                        📄 View
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination footer -->
                <div class="px-6 py-4 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between bg-slate-50/40 dark:bg-zinc-800/20">
                    <span class="text-xs text-slate-500 dark:text-zinc-400 font-medium">Page {{ transactions.data.length ? 1 : 0 }}</span>
                    <div class="flex items-center gap-1.5">
                        <template v-for="link in transactions.links">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                class="px-3 py-1.5 rounded-xl border text-[11px] font-bold transition-all"
                                :class="link.active ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-white dark:bg-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 border-slate-200 dark:border-zinc-700'"
                            />
                            <span
                                v-else
                                v-html="link.label"
                                class="px-3 py-1.5 rounded-xl border text-[11px] font-bold text-slate-300 dark:text-zinc-600 bg-slate-50 dark:bg-zinc-800/40 border-slate-200 dark:border-zinc-800 cursor-not-allowed"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
