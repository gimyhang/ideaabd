<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KpiCard from '@/Components/Admin/KpiCard.vue';
import DateRangePicker from '@/Components/Admin/DateRangePicker.vue';
import ExportButton from '@/Components/Admin/ExportButton.vue';

interface WriterMetrics {
    active_writers_count: number;
    total_follower_growth: number;
}

interface PaginatedWriters {
    data: Array<{
        id: number;
        pen_name: string;
        avatar?: string;
        verification_badge: boolean;
        total_submissions: number;
        total_published: number;
        total_views: number;
        total_likes: number;
        follower_growth: number;
        followers_count: number;
        user?: {
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
    metrics: WriterMetrics;
    writers: PaginatedWriters;
    filters: {
        start_date: string;
        end_date: string;
    };
}>();
</script>

<template>
    <Head title="Writers Analytics & Engagement — BookVerse" />

    <AdminLayout>
        <template #header>
            Writers Performance & Engagement Report
        </template>

        <div class="p-6 sm:p-8 space-y-8 font-sans">
            <!-- Header Title & Action Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-purple-500/10 via-pink-500/10 to-indigo-500/10 dark:from-purple-950/30 dark:via-pink-950/30 dark:to-indigo-950/30 p-6 rounded-3xl border border-purple-200/50 dark:border-purple-900/30">
                <div class="space-y-1">
                    <h1 class="text-xl sm:text-2xl font-black font-heading text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-lg shadow-md shadow-purple-500/25">✍️</span>
                        Writers Performance & Engagement
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 font-mono">
                        Author contribution metrics, follower acquisitions & article engagement rankings
                    </p>
                </div>

                <ExportButton type="writers" :start-date="filters.start_date" :end-date="filters.end_date" />
            </div>

            <!-- Date Range Filter Toolbar -->
            <DateRangePicker :start-date="filters.start_date" :end-date="filters.end_date" />

            <!-- KPI Summary Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 max-w-2xl">
                <KpiCard
                    title="Active Contributors"
                    :value="metrics.active_writers_count"
                    icon="✍️"
                    variant="indigo"
                    subtitle="Approved platform content creators"
                />

                <KpiCard
                    title="Follower Signups"
                    :value="`+${metrics.total_follower_growth}`"
                    icon="👥"
                    variant="emerald"
                    subtitle="Audience followers added in range"
                />
            </div>

            <!-- Writers Ranking Table -->
            <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-slate-200/80 dark:border-zinc-800 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between">
                    <h3 class="font-extrabold text-base text-slate-900 dark:text-zinc-100 font-heading flex items-center gap-2">
                        🌟 Content Writer Leaderboard
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 dark:bg-zinc-800/60 border-b border-slate-100 dark:border-zinc-800 text-slate-500 dark:text-zinc-400 font-extrabold uppercase font-mono">
                                <th class="px-6 py-3.5">Writer Details</th>
                                <th class="px-6 py-3.5">Follower Growth</th>
                                <th class="px-6 py-3.5">Content Metrics</th>
                                <th class="px-6 py-3.5">All-time Views</th>
                                <th class="px-6 py-3.5">All-time Likes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800 text-slate-700 dark:text-zinc-300">
                            <tr v-for="writer in writers.data" :key="writer.id" class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/40 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-purple-500 to-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-sm uppercase">
                                            {{ writer.pen_name.substring(0, 2) }}
                                        </div>
                                        <div>
                                            <span class="font-black text-slate-900 dark:text-zinc-100 block leading-tight">
                                                {{ writer.pen_name }}
                                                <span v-if="writer.verification_badge" class="text-sky-500 text-[10px] ml-0.5">✔️</span>
                                            </span>
                                            <span class="text-[10px] text-slate-400 dark:text-zinc-500 font-mono">{{ writer.user?.email ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-black text-emerald-600 dark:text-emerald-400 block font-mono text-sm">+{{ writer.follower_growth }}</span>
                                    <span class="text-[10px] text-slate-400 dark:text-zinc-500 font-mono">Total: {{ writer.followers_count }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-semibold text-slate-800 dark:text-zinc-200 block">{{ writer.total_published }} published</span>
                                    <span class="text-[10px] text-slate-400 dark:text-zinc-500 font-mono">{{ writer.total_submissions }} submissions</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-black text-slate-950 dark:text-zinc-100 font-mono">{{ writer.total_views.toLocaleString() }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-black text-slate-950 dark:text-zinc-100 font-mono">{{ writer.total_likes.toLocaleString() }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination footer -->
                <div class="px-6 py-4 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between bg-slate-50/40 dark:bg-zinc-800/20">
                    <span class="text-xs text-slate-500 dark:text-zinc-400 font-medium">Page {{ writers.data.length ? 1 : 0 }}</span>
                    <div class="flex items-center gap-1.5">
                        <template v-for="link in writers.links">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                class="px-3 py-1.5 rounded-xl border text-[11px] font-bold transition-all"
                                :class="link.active ? 'bg-purple-600 text-white border-purple-600 shadow-sm' : 'bg-white dark:bg-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 border-slate-200 dark:border-zinc-700'"
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
