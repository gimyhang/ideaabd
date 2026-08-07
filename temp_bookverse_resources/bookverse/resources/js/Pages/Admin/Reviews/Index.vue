<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermission } from '@/Composables/usePermission';

const { can } = usePermission();
import Badge from '@/Components/UI/Badge.vue';
import Pagination, { PaginationLink } from '@/Components/UI/Pagination.vue';

interface Report {
    id: number;
    user_name: string;
    user_email: string;
    reason: string;
    details: string | null;
    created_at: string;
}

interface Review {
    id: number;
    rating: number;
    title: string;
    body: string;
    body_snippet: string;
    is_hidden: boolean;
    is_reported: boolean;
    reports_count: number;
    reports: Report[];
    helpful_count: number;
    deleted_at: string | null;
    created_at: string;
    user_name: string;
    user_email: string;
    book_title: string;
    book_slug: string;
    photo_url: string | null;
}

interface PaginatedReviews {
    data: Review[];
    links: PaginationLink[];
    from: number;
    to: number;
    total: number;
    current_page: number;
    last_page: number;
}

const props = defineProps<{
    reviews: PaginatedReviews;
    filters: { search: string; status: string };
    stats?: { total: number; reported: number; hidden: number };
}>();

const search = ref(props.filters.search);
const status = ref(props.filters.status || 'visible');
const activeReportsModal = ref<Review | null>(null);

function applyFilters() {
    router.get('/admin/reviews', { search: search.value, status: status.value }, { preserveState: true, replace: true });
}

function setStatusFilter(newStatus: string) {
    status.value = newStatus;
    applyFilters();
}

function toggleVisibility(reviewId: number) {
    router.patch(`/admin/reviews/${reviewId}/visibility`, {}, { preserveScroll: true });
}

function dismissReports(reviewId: number) {
    if (! confirm('Are you sure you want to dismiss all reports for this review?')) return;
    router.delete(`/admin/reviews/${reviewId}/dismiss-reports`, { preserveScroll: true });
}

function getBadgeVariant(review: Review): 'warning' | 'error' | 'success' {
    if (review.deleted_at) return 'error';
    if (review.is_hidden) return 'warning';
    return 'success';
}

function getStatusLabel(review: Review): string {
    if (review.deleted_at) return 'Deleted';
    if (review.is_hidden) return 'Hidden';
    return 'Visible';
}

const starColors = ['', '#EF4444', '#F97316', '#EAB308', '#22C55E', '#10B981'];

const reasonLabels: Record<string, string> = {
    spam: '🚫 Spam',
    offensive: '🤬 Offensive Language',
    spoiler: '🤐 Unmarked Spoiler',
    fake: '🤖 Fake / Paid Review',
    other: '❓ Other Issue',
};
</script>

<template>
    <Head title="Review Moderation — Admin" />

    <AdminLayout>
        <template #header>Review Moderation</template>

        <div class="p-6 space-y-6 font-sans">

            <!-- Header Stats & Navigation Tabs -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-zinc-950 rounded-3xl p-5 border border-slate-200 dark:border-zinc-800 shadow-xs">
                <div>
                    <h1 class="text-lg font-bold font-heading text-slate-900 dark:text-white">Customer Reviews</h1>
                    <p class="text-xs text-slate-400">Moderate reviews, review helpful votes, and handle user report flags</p>
                </div>

                <!-- Quick Filter Tabs -->
                <div class="flex items-center gap-2">
                    <button
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5"
                        :class="status === 'visible'
                            ? 'bg-sky-600 text-white shadow-xs'
                            : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
                        @click="setStatusFilter('visible')"
                    >
                        Visible
                    </button>

                    <button
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5"
                        :class="status === 'reported'
                            ? 'bg-rose-600 text-white shadow-xs'
                            : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
                        @click="setStatusFilter('reported')"
                    >
                        Reported
                        <span
                            v-if="stats?.reported"
                            class="px-1.5 py-0.5 rounded-full text-[10px] bg-rose-500 text-white"
                        >
                            {{ stats.reported }}
                        </span>
                    </button>

                    <button
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5"
                        :class="status === 'hidden'
                            ? 'bg-amber-600 text-white shadow-xs'
                            : 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200'"
                        @click="setStatusFilter('hidden')"
                    >
                        Hidden
                        <span
                            v-if="stats?.hidden"
                            class="px-1.5 py-0.5 rounded-full text-[10px] bg-amber-500 text-white"
                        >
                            {{ stats.hidden }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="flex items-center justify-between gap-4 bg-white dark:bg-zinc-950 rounded-2xl p-4 border border-slate-200 dark:border-zinc-800">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search review content, book title, or customer name..."
                    class="text-xs px-4 py-2 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-900 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 flex-1 max-w-md"
                    @keydown.enter="applyFilters"
                />
                <button
                    class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold rounded-xl transition"
                    @click="applyFilters"
                >
                    Search
                </button>
            </div>

            <!-- Reviews Table -->
            <div class="bg-white dark:bg-zinc-950 rounded-3xl border border-slate-200 dark:border-zinc-800 shadow-xs overflow-hidden">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 dark:bg-zinc-900 border-b border-slate-200 dark:border-zinc-800">
                        <tr>
                            <th class="text-left px-5 py-3 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Review Content</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Book</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Customer</th>
                            <th class="text-center px-4 py-3 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rating & Helpful</th>
                            <th class="text-center px-4 py-3 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status & Reports</th>
                            <th class="text-left px-4 py-3 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                            <th class="text-center px-4 py-3 font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        <tr
                            v-for="review in reviews.data"
                            :key="review.id"
                            class="hover:bg-slate-50 dark:hover:bg-zinc-900/50 transition"
                            :class="{ 'bg-rose-50/20 dark:bg-rose-950/10': review.is_reported }"
                        >
                            <!-- Review snippet -->
                            <td class="px-5 py-4 max-w-xs">
                                <div class="flex items-start gap-3">
                                    <img
                                        v-if="review.photo_url"
                                        :src="review.photo_url"
                                        class="w-10 h-10 object-cover rounded-lg shrink-0 border border-slate-200 dark:border-zinc-700"
                                    />
                                    <div class="space-y-0.5 min-w-0">
                                        <p class="font-semibold text-slate-800 dark:text-white truncate">{{ review.title }}</p>
                                        <p class="text-slate-400 line-clamp-2 leading-relaxed">{{ review.body_snippet }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Book -->
                            <td class="px-4 py-4 max-w-[140px]">
                                <Link
                                    :href="`/books/${review.book_slug}`"
                                    class="text-sky-600 dark:text-sky-400 hover:underline font-medium truncate block"
                                    target="_blank"
                                >
                                    {{ review.book_title }}
                                </Link>
                            </td>

                            <!-- Customer -->
                            <td class="px-4 py-4">
                                <p class="font-medium text-slate-800 dark:text-white">{{ review.user_name }}</p>
                                <p class="text-slate-400 text-[11px]">{{ review.user_email }}</p>
                            </td>

                            <!-- Rating & Helpful -->
                            <td class="px-4 py-4 text-center">
                                <div class="space-y-1">
                                    <div class="flex items-center justify-center gap-1">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24">
                                            <polygon
                                                points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"
                                                :fill="starColors[review.rating]"
                                                :stroke="starColors[review.rating]"
                                                stroke-width="1" stroke-linejoin="round"
                                            />
                                        </svg>
                                        <span class="font-bold text-slate-700 dark:text-slate-200">{{ review.rating }}/5</span>
                                    </div>
                                    <p class="text-[10px] text-slate-400">👍 {{ review.helpful_count }} votes</p>
                                </div>
                            </td>

                            <!-- Status & Reports -->
                            <td class="px-4 py-4 text-center">
                                <div class="space-y-1">
                                    <Badge :variant="getBadgeVariant(review)">
                                        {{ getStatusLabel(review) }}
                                    </Badge>

                                    <!-- Report flag button -->
                                    <div v-if="review.is_reported">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 px-2 py-0.5 rounded-full hover:bg-rose-100 transition cursor-pointer"
                                            @click="activeReportsModal = review"
                                        >
                                            <span>⚠ Reported ({{ review.reports_count }})</span>
                                        </button>
                                    </div>
                                </div>
                            </td>

                            <!-- Date -->
                            <td class="px-4 py-4 text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                {{ review.created_at }}
                            </td>

                            <!-- Action -->
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Hide / Unhide Toggle -->
                                    <button
                                        v-if="! review.deleted_at && can('delete-reviews')"
                                        class="text-xs font-semibold px-3 py-1.5 rounded-xl transition shadow-2xs"
                                        :class="review.is_hidden
                                            ? 'bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-200'
                                            : 'bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 hover:bg-amber-200'"
                                        @click="toggleVisibility(review.id)"
                                    >
                                        {{ review.is_hidden ? '✓ Unhide' : '✕ Hide' }}
                                    </button>

                                    <!-- Dismiss Reports Button -->
                                    <button
                                        v-if="review.is_reported && can('delete-reviews')"
                                        class="text-xs font-semibold px-2.5 py-1.5 rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 transition"
                                        title="Dismiss all reports and keep review"
                                        @click="dismissReports(review.id)"
                                    >
                                        Dismiss
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <tr v-if="reviews.data.length === 0">
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                No reviews found matching your filter criteria.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="px-5 py-4 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between">
                    <p class="text-xs text-slate-400">
                        Showing {{ reviews.from }}–{{ reviews.to }} of {{ reviews.total }}
                    </p>
                    <Pagination :links="reviews.links" />
                </div>
            </div>

            <!-- Reports Detail Modal -->
            <div
                v-if="activeReportsModal"
                class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4"
                @click.self="activeReportsModal = null"
            >
                <div class="bg-white dark:bg-zinc-950 rounded-3xl p-6 max-w-lg w-full space-y-4 border border-slate-200 dark:border-zinc-800 shadow-xl">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Review Reports Breakdown</h3>
                            <p class="text-xs text-slate-400">Review ID #{{ activeReportsModal.id }} — {{ activeReportsModal.reports_count }} report(s)</p>
                        </div>
                        <button
                            class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-white transition"
                            @click="activeReportsModal = null"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Review Snippet -->
                    <div class="p-3 bg-slate-50 dark:bg-zinc-900 rounded-xl text-xs space-y-1">
                        <p class="font-bold text-slate-800 dark:text-white">"{{ activeReportsModal.title }}"</p>
                        <p class="text-slate-500 italic">{{ activeReportsModal.body }}</p>
                    </div>

                    <!-- Report Items List -->
                    <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                        <div
                            v-for="rep in activeReportsModal.reports"
                            :key="rep.id"
                            class="p-3 rounded-xl border border-rose-200/80 dark:border-rose-900/40 bg-rose-50/40 dark:bg-rose-950/20 text-xs space-y-1"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-rose-700 dark:text-rose-400">
                                    {{ reasonLabels[rep.reason] || rep.reason }}
                                </span>
                                <span class="text-[10px] text-slate-400">{{ rep.created_at }}</span>
                            </div>
                            <p class="text-slate-600 dark:text-slate-300">
                                Reported by: <strong class="text-slate-800 dark:text-white">{{ rep.user_name }}</strong> ({{ rep.user_email }})
                            </p>
                            <p v-if="rep.details" class="text-slate-500 italic pt-1 border-t border-rose-200/60 dark:border-rose-900/30">
                                "{{ rep.details }}"
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-zinc-800">
                        <button
                            class="px-4 py-2 bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl hover:bg-slate-200 transition"
                            @click="activeReportsModal = null"
                        >
                            Close
                        </button>

                        <div class="flex items-center gap-2">
                            <button
                                v-if="can('delete-reviews')"
                                class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition"
                                @click="toggleVisibility(activeReportsModal.id); activeReportsModal = null;"
                            >
                                {{ activeReportsModal.is_hidden ? 'Unhide Review' : 'Hide Review' }}
                            </button>
                            <button
                                v-if="can('delete-reviews')"
                                class="px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition"
                                @click="dismissReports(activeReportsModal.id); activeReportsModal = null;"
                            >
                                Dismiss Reports
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
