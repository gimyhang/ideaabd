<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KpiCard from '@/Components/Admin/KpiCard.vue';
import RevenueChart from '@/Components/Admin/RevenueChart.vue';
import Avatar from '@/Components/UI/Avatar.vue';

interface MetricsPayload {
    today_revenue: number;
    total_revenue: number;
    monthly_revenue_growth: number;
    today_orders: number;
    total_orders: number;
    pending_orders: number;
    processing_orders: number;
    low_stock_books: number;
    out_of_stock_books: number;
    published_articles: number;
    pending_articles: number;
    registered_writers: number;
    new_users_today: number;
    total_users: number;
}

interface RevenueTrendPayload {
    labels: string[];
    data: number[];
    period: string;
}

interface RecentOrderItem {
    id: number;
    order_number: string;
    customer_name: string;
    customer_phone: string;
    total_amount: number;
    items_count: number;
    payment_method: string;
    payment_status: string;
    order_status: string;
    created_at: string;
}

interface PendingSubmissionItem {
    id: number;
    title: string;
    writer_pen_name: string;
    writer_avatar?: string;
    word_count: number;
    submitted_at: string;
}

const props = defineProps<{
    metrics: MetricsPayload;
    revenueTrend: RevenueTrendPayload;
    recentOrders: RecentOrderItem[];
    pendingSubmissions: PendingSubmissionItem[];
    activePeriod: string;
}>();

function orderBadgeVariant(status: string): string {
    switch (status) {
        case 'delivered':
        case 'completed':
            return 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/50';
        case 'processing':
        case 'shipped':
            return 'bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200/80 dark:border-sky-800/50';
        case 'pending':
            return 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/50';
        case 'cancelled':
            return 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/50';
        default:
            return 'bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 border border-slate-200 dark:border-zinc-700';
    }
}
</script>

<template>
    <Head title="Admin Dashboard Overview — BookVerse" />

    <AdminLayout>
        <template #header>
            Dashboard & Business Intelligence
        </template>

        <div class="p-6 sm:p-8 space-y-8 font-sans">

            <!-- Admin Quick-Action Toolbar: Dual Theme Compatible Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-indigo-50/90 via-sky-50/80 to-purple-50/90 dark:from-slate-900 dark:via-zinc-900 dark:to-slate-950 p-6 sm:p-7 rounded-3xl text-slate-800 dark:text-white border border-indigo-100/80 dark:border-slate-800 shadow-sm dark:shadow-xl relative overflow-hidden">
                <!-- Decorative background elements -->
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-indigo-200/30 dark:bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-sky-200/30 dark:bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="space-y-1 relative z-10">
                    <h1 class="text-xl sm:text-2xl font-black font-heading tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                        👑 BookVerse Control Center
                    </h1>
                    <p class="text-xs text-slate-600 dark:text-slate-400 font-mono">
                        E-Commerce Sales Fulfillment & E-Magazine Editorial Engine Overview
                    </p>
                </div>

                <!-- Quick Action Shortcuts -->
                <div class="flex flex-wrap items-center gap-2.5 relative z-10">
                    <Link
                        href="/admin/books"
                        class="px-4 py-2 rounded-2xl text-xs font-bold bg-white dark:bg-white/10 text-slate-700 dark:text-white hover:bg-slate-50 dark:hover:bg-white/20 border border-slate-200/90 dark:border-white/10 shadow-xs transition flex items-center gap-1.5 active:scale-95"
                    >
                        <span>➕ Add Book</span>
                    </Link>

                    <Link
                        href="/admin/submissions"
                        class="px-4 py-2 rounded-2xl text-xs font-bold bg-sky-100 dark:bg-sky-600 hover:bg-sky-200/80 dark:hover:bg-sky-500 text-sky-900 dark:text-white border border-sky-200/80 dark:border-sky-500 shadow-xs transition flex items-center gap-1.5 active:scale-95"
                    >
                        <span>📝 Review Queue</span>
                        <span v-if="metrics.pending_articles > 0" class="px-1.5 py-0.5 rounded-full bg-sky-600 dark:bg-white text-white dark:text-sky-900 text-[10px] font-mono font-black">
                            {{ metrics.pending_articles }}
                        </span>
                    </Link>

                    <Link
                        href="/admin/orders"
                        class="px-4 py-2 rounded-2xl text-xs font-bold bg-emerald-100 dark:bg-emerald-600 hover:bg-emerald-200/80 dark:hover:bg-emerald-500 text-emerald-900 dark:text-white border border-emerald-200/80 dark:border-emerald-500 shadow-xs transition flex items-center gap-1.5 active:scale-95"
                    >
                        <span>🛍️ Customer Orders</span>
                    </Link>

                    <Link
                        href="/admin/settings/notifications"
                        class="px-4 py-2 rounded-2xl text-xs font-bold bg-white dark:bg-white/10 text-slate-700 dark:text-white hover:bg-slate-50 dark:hover:bg-white/20 border border-slate-200/90 dark:border-white/10 shadow-xs transition flex items-center gap-1.5 active:scale-95"
                    >
                        <span>⚙️ Settings</span>
                    </Link>
                </div>
            </div>

            <!-- 8 High-Impact Enterprise KPI Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <KpiCard
                    title="Today's Revenue"
                    :value="`৳${metrics.today_revenue.toLocaleString()}`"
                    icon="💸"
                    variant="emerald"
                    subtitle="Real-time sales collected today"
                />

                <KpiCard
                    title="Total Revenue"
                    :value="`৳${metrics.total_revenue.toLocaleString()}`"
                    icon="💰"
                    :trend="metrics.monthly_revenue_growth"
                    variant="sky"
                    subtitle="Lifetime paid order revenue"
                />

                <KpiCard
                    title="Today's Orders"
                    :value="metrics.today_orders"
                    icon="📦"
                    variant="indigo"
                    :subtitle="`${metrics.pending_orders} pending fulfillment`"
                />

                <KpiCard
                    title="Low Stock Alert"
                    :value="metrics.low_stock_books"
                    icon="⚠️"
                    variant="amber"
                    :subtitle="`${metrics.out_of_stock_books} books out of stock`"
                />

                <KpiCard
                    title="Published Articles"
                    :value="metrics.published_articles"
                    icon="📰"
                    variant="purple"
                    :subtitle="`${metrics.registered_writers} verified writers`"
                />

                <KpiCard
                    title="Editorial Queue"
                    :value="metrics.pending_articles"
                    icon="📝"
                    variant="rose"
                    subtitle="Submissions awaiting review"
                />

                <KpiCard
                    title="New Users Today"
                    :value="metrics.new_users_today"
                    icon="✨"
                    variant="sky"
                    subtitle="Fresh user registrations"
                />

                <KpiCard
                    title="Total Registered Users"
                    :value="metrics.total_users"
                    icon="👥"
                    variant="emerald"
                    subtitle="Active customer accounts"
                />
            </div>

            <!-- Revenue Analytics Line Chart -->
            <RevenueChart :trend="revenueTrend" :active-period="activePeriod" />

            <!-- Dual Operational Panels (Recent Orders & Pending Submissions) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Recent Orders Quick Action Panel -->
                <div class="p-6 rounded-3xl bg-white dark:bg-zinc-950 border border-slate-200/80 dark:border-zinc-800 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-900 pb-3">
                        <h3 class="font-extrabold text-sm text-slate-900 dark:text-white font-heading flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xs border border-sky-100 dark:border-sky-900/50">🛍️</span>
                            Recent Customer Orders
                        </h3>

                        <Link href="/admin/orders" class="text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline">
                            View All →
                        </Link>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-zinc-900">
                        <div v-for="order in recentOrders" :key="order.id" class="py-3 flex items-center justify-between gap-3 text-xs hover:bg-slate-50/60 dark:hover:bg-zinc-900/50 px-2 rounded-xl transition">
                            <div class="space-y-0.5 min-w-0">
                                <div class="flex items-center gap-2">
                                    <Link :href="`/admin/orders/${order.id}`" class="font-bold text-slate-900 dark:text-white hover:text-sky-600 dark:hover:text-sky-400 font-mono">
                                        {{ order.order_number }}
                                    </Link>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold capitalize" :class="orderBadgeVariant(order.order_status)">
                                        {{ order.order_status }}
                                    </span>
                                </div>
                                <p class="text-slate-500 dark:text-zinc-400 truncate text-[11px]">
                                    {{ order.customer_name }} • {{ order.items_count }} items
                                </p>
                            </div>

                            <div class="text-right shrink-0">
                                <span class="font-extrabold text-slate-900 dark:text-white font-mono text-sm block">
                                    ৳{{ order.total_amount }}
                                </span>
                                <span class="text-[10px] text-slate-400 dark:text-zinc-500 font-mono">
                                    {{ order.created_at }}
                                </span>
                            </div>
                        </div>

                        <div v-if="recentOrders.length === 0" class="py-8 text-center text-slate-400 dark:text-zinc-500 text-xs font-mono">
                            No customer orders placed yet.
                        </div>
                    </div>
                </div>

                <!-- Pending Editorial Submissions Panel -->
                <div class="p-6 rounded-3xl bg-white dark:bg-zinc-950 border border-slate-200/80 dark:border-zinc-800 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-900 pb-3">
                        <h3 class="font-extrabold text-sm text-slate-900 dark:text-white font-heading flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xs border border-purple-100 dark:border-purple-900/50">📝</span>
                            Pending Editorial Submissions
                        </h3>

                        <Link href="/admin/submissions" class="text-xs font-bold text-purple-600 dark:text-purple-400 hover:underline">
                            Review Portal →
                        </Link>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-zinc-900">
                        <div v-for="sub in pendingSubmissions" :key="sub.id" class="py-3 flex items-center justify-between gap-3 text-xs hover:bg-slate-50/60 dark:hover:bg-zinc-900/50 px-2 rounded-xl transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <Avatar :name="sub.writer_pen_name" size="sm" />
                                <div class="space-y-0.5 min-w-0">
                                    <h4 class="font-bold text-slate-900 dark:text-white truncate hover:text-purple-600 dark:hover:text-purple-400">
                                        {{ sub.title }}
                                    </h4>
                                    <p class="text-slate-500 dark:text-zinc-400 text-[11px]">
                                        By {{ sub.writer_pen_name }} • {{ sub.word_count }} words
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0 text-right">
                                <Link :href="`/admin/submissions/${sub.id}/review`" class="px-3 py-1 rounded-xl bg-purple-50 dark:bg-purple-900/30 hover:bg-purple-100 dark:hover:bg-purple-900/50 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-700/50 font-bold text-[11px] inline-block shadow-xs transition">
                                    Review
                                </Link>
                            </div>
                        </div>

                        <div v-if="pendingSubmissions.length === 0" class="py-8 text-center text-slate-400 dark:text-zinc-500 text-xs font-mono">
                            No pending article submissions in queue.
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </AdminLayout>
</template>
