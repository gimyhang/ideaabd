<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KpiCard from '@/Components/Admin/KpiCard.vue';
import DateRangePicker from '@/Components/Admin/DateRangePicker.vue';
import ExportButton from '@/Components/Admin/ExportButton.vue';

interface UserMetrics {
    total_users: number;
    new_users_range: number;
}

interface UserChart {
    labels: string[];
    new_users: number[];
    cumulative: number[];
}

interface PaginatedTable {
    data: Array<{
        date: string;
        new_signups: number;
        cumulative_total: number;
    }>;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

const props = defineProps<{
    metrics: UserMetrics;
    chart: UserChart;
    table: PaginatedTable;
    filters: {
        start_date: string;
        end_date: string;
    };
}>();

// Simple SVG Area Chart calculations
const maxNewUsers = computed(() => {
    const max = Math.max(...props.chart.new_users, 10);
    return Math.ceil(max * 1.15);
});

const points = computed(() => {
    const data = props.chart.new_users;
    const count = data.length;
    if (count === 0) return [];

    const width = 600;
    const height = 200;
    const step = count > 1 ? width / (count - 1) : width;

    return data.map((val, i) => {
        const x = i * step;
        const y = height - (val / maxNewUsers.value) * height;
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
    <Head title="User Growth Analytics — BookVerse" />

    <AdminLayout>
        <template #header>
            User Growth & Registrations Analytics
        </template>

        <div class="p-6 sm:p-8 space-y-8 font-sans">
            <!-- Header Title & Action Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-sky-500/10 via-blue-500/10 to-teal-500/10 dark:from-sky-950/30 dark:via-blue-950/30 dark:to-teal-950/30 p-6 rounded-3xl border border-sky-200/50 dark:border-sky-900/30">
                <div class="space-y-1">
                    <h1 class="text-xl sm:text-2xl font-black font-heading text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-2xl bg-sky-500 text-white flex items-center justify-center text-lg shadow-md shadow-sky-500/25">👥</span>
                        User Growth & Registration Rate
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 font-mono">
                        Audience onboarding trends, daily signups & cumulative registered accounts
                    </p>
                </div>

                <ExportButton type="users" :start-date="filters.start_date" :end-date="filters.end_date" />
            </div>

            <!-- Date Range Filter Toolbar -->
            <DateRangePicker :start-date="filters.start_date" :end-date="filters.end_date" />

            <!-- KPI Summary Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 max-w-2xl">
                <KpiCard
                    title="Platform User Base"
                    :value="metrics.total_users.toLocaleString()"
                    icon="👥"
                    variant="indigo"
                    subtitle="All-time registered accounts"
                />

                <KpiCard
                    title="Fresh Registrations"
                    :value="`+${metrics.new_users_range.toLocaleString()}`"
                    icon="✨"
                    variant="emerald"
                    subtitle="New signup acquisitions in range"
                />
            </div>

            <!-- Signup Trend SVG Chart -->
            <div class="p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-3">
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900 dark:text-zinc-100 font-heading flex items-center gap-2">
                            📉 Daily Signup Acquisition Trend
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 font-mono">Date-grouped user onboarding rate</p>
                    </div>
                </div>

                <div class="relative pt-4 pb-2">
                    <svg class="w-full h-48 overflow-visible" viewBox="0 0 600 200" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="userGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#0284c7" stop-opacity="0.25" />
                                <stop offset="100%" stop-color="#0284c7" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        <line x1="0" y1="50" x2="600" y2="50" class="stroke-slate-100 dark:stroke-zinc-800" stroke-dasharray="4 4" />
                        <line x1="0" y1="100" x2="600" y2="100" class="stroke-slate-100 dark:stroke-zinc-800" stroke-dasharray="4 4" />
                        <line x1="0" y1="150" x2="600" y2="150" class="stroke-slate-100 dark:stroke-zinc-800" stroke-dasharray="4 4" />
                        <path :d="areaD" fill="url(#userGrad)" />
                        <path :d="pathD" class="stroke-sky-500 dark:stroke-sky-400" stroke-width="3.5" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>

            <!-- Signup Growth Table -->
            <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-slate-200/80 dark:border-zinc-800 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between">
                    <h3 class="font-extrabold text-base text-slate-900 dark:text-zinc-100 font-heading flex items-center gap-2">
                        📅 Daily Onboarding Breakdown
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 dark:bg-zinc-800/60 border-b border-slate-100 dark:border-zinc-800 text-slate-500 dark:text-zinc-400 font-extrabold uppercase font-mono">
                                <th class="px-6 py-3.5">Date</th>
                                <th class="px-6 py-3.5">New Signups</th>
                                <th class="px-6 py-3.5">Running Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800 text-slate-700 dark:text-zinc-300">
                            <tr v-for="row in table.data" :key="row.date" class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                                <td class="px-6 py-4 font-mono font-bold text-slate-700 dark:text-zinc-300">
                                    {{ row.date }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-black text-sky-600 dark:text-sky-400 font-mono text-sm">+{{ row.new_signups }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-black text-slate-900 dark:text-zinc-100 font-mono text-sm">{{ row.cumulative_total.toLocaleString() }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination footer -->
                <div class="px-6 py-4 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between bg-slate-50/40 dark:bg-zinc-800/20">
                    <span class="text-xs text-slate-500 dark:text-zinc-400 font-medium">Page {{ table.data.length ? 1 : 0 }}</span>
                    <div class="flex items-center gap-1.5">
                        <template v-for="link in table.links">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                class="px-3 py-1.5 rounded-xl border text-[11px] font-bold transition-all"
                                :class="link.active ? 'bg-sky-600 text-white border-sky-600 shadow-sm' : 'bg-white dark:bg-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 border-slate-200 dark:border-zinc-700'"
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
