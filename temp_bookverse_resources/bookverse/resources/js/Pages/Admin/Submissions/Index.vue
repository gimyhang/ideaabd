<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermission } from '@/Composables/usePermission';

const { can } = usePermission();
import Avatar from '@/Components/UI/Avatar.vue';
import Badge from '@/Components/UI/Badge.vue';
import Input from '@/Components/UI/Input.vue';
import Pagination from '@/Components/UI/Pagination.vue';

interface WriterProfile {
    id: number;
    pen_name: string;
    avatar_url: string;
    user?: {
        name: string;
        email: string;
    };
}

interface SubmissionItem {
    id: number;
    title: string;
    slug: string;
    excerpt?: string;
    status: 'draft' | 'pending_review' | 'approved' | 'published' | 'rejected' | 'archived';
    rejection_severity?: 'minor' | 'major';
    word_count: number;
    read_time_minutes: number;
    updated_at: string;
    created_at: string;
    writer_profile?: WriterProfile;
}

interface PaginatedSubmissions {
    data: SubmissionItem[];
    links: any[];
    current_page: number;
    last_page: number;
    total: number;
}

interface Counts {
    all: number;
    pending: number;
    published: number;
    rejected: number;
}

const props = defineProps<{
    submissions: PaginatedSubmissions;
    filters: {
        status?: string;
        search?: string;
    };
    counts: Counts;
}>();

const selectedStatus = ref(props.filters.status || 'pending_review');
const search = ref(props.filters.search || '');

let searchTimeout: any = null;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

function selectStatusFilter(statusKey: string) {
    selectedStatus.value = statusKey;
    applyFilters();
}

function applyFilters() {
    router.get(
        '/admin/submissions',
        {
            status: selectedStatus.value || undefined,
            search: search.value || undefined,
        },
        { preserveState: true, replace: true }
    );
}

function statusBadgeVariant(status: string) {
    switch (status) {
        case 'published':
            return 'success';
        case 'pending_review':
            return 'warning';
        case 'rejected':
            return 'error';
        default:
            return 'default';
    }
}
</script>

<template>
    <AdminLayout>
        <template #header>Editorial Moderation Queue</template>

        <div class="space-y-6 font-sans">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-3xl shadow-xs border transition-colors bg-white dark:bg-zinc-950 border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white">
                <div>
                    <h1 class="text-xl font-extrabold font-heading tracking-tight">Editorial Article Moderation</h1>
                    <p class="text-xs mt-0.5 text-slate-500 dark:text-zinc-400">
                        Review pending writer submissions, give editorial feedback, and publish articles live to the E-Magazine.
                    </p>
                </div>
                <a
                    href="/feed"
                    target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-md shadow-amber-500/20 transition shrink-0 cursor-pointer"
                    title="Open Live RSS XML Feed"
                >
                    <span>📡 Live RSS Feed</span>
                </a>
            </div>

            <!-- KPI Analytics Cards Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-zinc-950 p-5 rounded-3xl border border-slate-200 dark:border-zinc-800 shadow-xs space-y-1">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-500">Pending Review</span>
                    <p class="text-2xl font-black text-amber-600 dark:text-amber-400 font-heading">
                        {{ counts.pending }}
                    </p>
                    <span class="text-[10px] text-slate-400">Urgent moderation queue</span>
                </div>

                <div class="bg-white dark:bg-zinc-950 p-5 rounded-3xl border border-slate-200 dark:border-zinc-800 shadow-xs space-y-1">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-500">Published Live</span>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-heading">
                        {{ counts.published }}
                    </p>
                    <span class="text-[10px] text-slate-400">Active in magazine</span>
                </div>

                <div class="bg-white dark:bg-zinc-950 p-5 rounded-3xl border border-slate-200 dark:border-zinc-800 shadow-xs space-y-1">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-rose-500">Rejected</span>
                    <p class="text-2xl font-black text-rose-600 dark:text-rose-400 font-heading">
                        {{ counts.rejected }}
                    </p>
                    <span class="text-[10px] text-slate-400">Sent back for fixes</span>
                </div>

                <div class="bg-white dark:bg-zinc-950 p-5 rounded-3xl border border-slate-200 dark:border-zinc-800 shadow-xs space-y-1">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Total Submissions</span>
                    <p class="text-2xl font-black text-slate-900 dark:text-white font-heading">
                        {{ counts.all }}
                    </p>
                    <span class="text-[10px] text-slate-400">All submissions</span>
                </div>
            </div>

            <!-- Status Filter Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                <button
                    @click="selectStatusFilter('pending_review')"
                    class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all border cursor-pointer"
                    :class="selectedStatus === 'pending_review'
                        ? 'bg-amber-500 text-slate-950 border-amber-500 shadow-xs font-bold'
                        : 'bg-white dark:bg-zinc-950 border-slate-200 dark:border-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-900'"
                >
                    ⏳ Pending Review <span class="ml-1 opacity-70">({{ counts.pending }})</span>
                </button>

                <button
                    @click="selectStatusFilter('published')"
                    class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all border cursor-pointer"
                    :class="selectedStatus === 'published'
                        ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs font-bold'
                        : 'bg-white dark:bg-zinc-950 border-slate-200 dark:border-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-900'"
                >
                    ✨ Published Live <span class="ml-1 opacity-70">({{ counts.published }})</span>
                </button>

                <button
                    @click="selectStatusFilter('rejected')"
                    class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all border cursor-pointer"
                    :class="selectedStatus === 'rejected'
                        ? 'bg-rose-600 text-white border-rose-600 shadow-xs font-bold'
                        : 'bg-white dark:bg-zinc-950 border-slate-200 dark:border-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-900'"
                >
                    🔴 Rejected <span class="ml-1 opacity-70">({{ counts.rejected }})</span>
                </button>

                <button
                    @click="selectStatusFilter('all')"
                    class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all border cursor-pointer"
                    :class="selectedStatus === 'all'
                        ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 border-slate-900 dark:border-white shadow-xs font-bold'
                        : 'bg-white dark:bg-zinc-950 border-slate-200 dark:border-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-900'"
                >
                    All Submissions <span class="ml-1 opacity-70">({{ counts.all }})</span>
                </button>
            </div>

            <!-- Submissions Table Card -->
            <div class="border rounded-3xl overflow-hidden shadow-xs transition-colors bg-white dark:bg-zinc-950 border-slate-200 dark:border-zinc-800 space-y-4 p-5">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-slate-100 dark:border-zinc-900 pb-4">
                    <div class="w-full sm:w-80">
                        <Input
                            v-model="search"
                            placeholder="Search by title, pen name..."
                        />
                    </div>

                    <span class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Total Submissions: {{ submissions.total }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b uppercase tracking-wider font-semibold text-[10px] bg-slate-50 dark:bg-zinc-900 text-slate-500 dark:text-zinc-400 border-slate-200 dark:border-zinc-800">
                                <th class="py-3.5 px-5">Article Title</th>
                                <th class="py-3.5 px-5">Author</th>
                                <th class="py-3.5 px-5">Metrics</th>
                                <th class="py-3.5 px-5">Status</th>
                                <th class="py-3.5 px-5">Submitted Date</th>
                                <th class="py-3.5 px-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-900 text-slate-700 dark:text-slate-200">
                            <tr
                                v-for="sub in submissions.data"
                                :key="sub.id"
                                class="hover:bg-slate-50/80 dark:hover:bg-zinc-900/50 transition"
                            >
                                <td class="py-3.5 px-5">
                                    <div class="font-bold text-sm text-slate-900 dark:text-white line-clamp-1">
                                        {{ sub.title }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-zinc-400 line-clamp-1 mt-0.5">
                                        {{ sub.excerpt || 'No excerpt' }}
                                    </div>
                                </td>

                                <td class="py-3.5 px-5 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <Avatar :name="sub.writer_profile?.pen_name || 'Author'" size="sm" />
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-white">
                                                {{ sub.writer_profile?.pen_name }}
                                            </div>
                                            <div class="text-[10px] text-slate-400">
                                                {{ sub.writer_profile?.user?.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-3.5 px-5 whitespace-nowrap font-mono text-[11px]">
                                    <div>{{ sub.word_count }} words</div>
                                    <div class="text-sky-600 dark:text-sky-400 text-[10px]">⏱️ {{ sub.read_time_minutes }} min read</div>
                                </td>

                                <td class="py-3.5 px-5 whitespace-nowrap">
                                    <Badge :variant="statusBadgeVariant(sub.status)" size="sm" class="capitalize font-bold">
                                        {{ sub.status.replace('_', ' ') }}
                                    </Badge>
                                    <div v-if="sub.rejection_severity" class="text-[9px] text-rose-500 font-bold uppercase mt-0.5">
                                        {{ sub.rejection_severity }} severity
                                    </div>
                                </td>

                                <td class="py-3.5 px-5 whitespace-nowrap text-slate-500 text-[11px]">
                                    {{ new Date(sub.updated_at).toLocaleDateString() }}
                                </td>

                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                    <Link v-if="can('review-submissions')" :href="`/admin/submissions/${sub.id}/review`">
                                        <button class="px-3 py-1.5 rounded-xl text-[11px] font-bold bg-sky-600 hover:bg-sky-700 text-white shadow-xs transition cursor-pointer">
                                            🔍 Review Article
                                        </button>
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="submissions.data.length === 0">
                                <td colspan="6" class="py-8 px-5 text-center text-slate-400 text-xs">
                                    No articles in this moderation queue.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="submissions.links.length > 3" class="pt-3 border-t border-slate-100 dark:border-zinc-900">
                    <Pagination :links="submissions.links" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
