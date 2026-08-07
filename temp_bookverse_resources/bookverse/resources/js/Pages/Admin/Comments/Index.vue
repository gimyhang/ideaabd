<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermission } from '@/Composables/usePermission';

const { can } = usePermission();
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import Avatar from '@/Components/UI/Avatar.vue';

interface CommentReportItem {
    id: number;
    reason: string;
    reason_label: string;
    details?: string;
    reporter: string;
    created_at: string;
}

interface CommentItem {
    id: number;
    comment: string;
    is_hidden: boolean;
    is_deleted: boolean;
    created_at: string;
    reports_count: number;
    user: {
        id: number;
        name: string;
        email: string;
        avatar_url?: string;
    };
    article: {
        id: number;
        title: string;
        slug?: string;
    };
    reports: CommentReportItem[];
}

interface PaginatedComments {
    data: CommentItem[];
    links: any[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    comments: PaginatedComments;
    counts: {
        total: number;
        reported: number;
        hidden: number;
        trashed: number;
    };
    filters: {
        status?: string;
        search?: string;
    };
}>();

const search = ref(props.filters.search || '');
const selectedIds = ref<number[]>([]);
const isAllSelected = ref(false);

function applySearch() {
    router.get('/admin/comments', {
        status: props.filters.status || 'all',
        search: search.value,
    }, { preserveState: true });
}

function filterStatus(status: string) {
    router.get('/admin/comments', {
        status,
        search: search.value,
    }, { preserveState: true });
}

function toggleSelectAll() {
    isAllSelected.value = !isAllSelected.value;
    if (isAllSelected.value) {
        selectedIds.value = props.comments.data.map(c => c.id);
    } else {
        selectedIds.value = [];
    }
}

function toggleVisibility(id: number) {
    router.patch(`/admin/comments/${id}/toggle-visibility`, {}, { preserveScroll: true });
}

function dismissReports(id: number) {
    router.delete(`/admin/comments/${id}/dismiss-reports`, { preserveScroll: true });
}

function deleteComment(id: number) {
    if (confirm('Are you sure you want to delete this comment?')) {
        router.delete(`/admin/comments/${id}`, { preserveScroll: true });
    }
}

function restoreComment(id: number) {
    router.post(`/admin/comments/${id}/restore`, {}, { preserveScroll: true });
}

function executeBulkAction(action: string) {
    if (selectedIds.value.length === 0) return;

    if (confirm(`Perform bulk '${action}' on ${selectedIds.value.length} selected comments?`)) {
        router.post('/admin/comments/bulk', {
            action,
            comment_ids: selectedIds.value,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedIds.value = [];
                isAllSelected.value = false;
            },
        });
    }
}
</script>

<template>
    <Head title="Comment Moderation — Admin Panel" />

    <AdminLayout>
        <template #header>
            Comment Moderation
        </template>

        <div class="p-6 sm:p-8 space-y-6 font-sans">
            <!-- Header Title Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-zinc-800 pb-5">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black font-heading text-slate-900 dark:text-white flex items-center gap-2">
                        💬 Article Comment Moderation Queue
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                        Review reported abuse, manage hidden comments, perform bulk moderation, or restore deleted threads.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        @click="filterStatus('all')"
                        class="px-3.5 py-1.5 rounded-2xl text-xs font-bold transition border"
                        :class="(filters.status || 'all') === 'all'
                            ? 'bg-slate-900 text-white border-slate-900 dark:bg-white dark:text-slate-900'
                            : 'bg-white dark:bg-zinc-950 text-slate-600 dark:text-zinc-400 border-slate-200 dark:border-zinc-800 hover:bg-slate-50'"
                    >
                        All ({{ counts.total }})
                    </button>

                    <button
                        @click="filterStatus('reported')"
                        class="px-3.5 py-1.5 rounded-2xl text-xs font-bold transition border flex items-center gap-1.5"
                        :class="filters.status === 'reported'
                            ? 'bg-rose-600 text-white border-rose-600 shadow-rose-500/20'
                            : 'bg-white dark:bg-zinc-950 text-rose-600 border-rose-200 dark:border-rose-900/50 hover:bg-rose-50'"
                    >
                        <span>🚨 Reported</span>
                        <span class="px-1.5 py-0.2 rounded-full bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300 text-[10px]">
                            {{ counts.reported }}
                        </span>
                    </button>

                    <button
                        @click="filterStatus('hidden')"
                        class="px-3.5 py-1.5 rounded-2xl text-xs font-bold transition border"
                        :class="filters.status === 'hidden'
                            ? 'bg-amber-600 text-white border-amber-600'
                            : 'bg-white dark:bg-zinc-950 text-amber-600 border-amber-200 dark:border-amber-900/50 hover:bg-amber-50'"
                    >
                        🙈 Hidden ({{ counts.hidden }})
                    </button>

                    <button
                        @click="filterStatus('trashed')"
                        class="px-3.5 py-1.5 rounded-2xl text-xs font-bold transition border"
                        :class="filters.status === 'trashed'
                            ? 'bg-zinc-800 text-white border-zinc-800'
                            : 'bg-white dark:bg-zinc-950 text-slate-600 border-slate-200 dark:border-zinc-800 hover:bg-slate-50'"
                    >
                        🗑️ Trashed ({{ counts.trashed }})
                    </button>
                </div>
            </div>

            <!-- Search & Bulk Toolbar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <form @submit.prevent="applySearch" class="w-full sm:w-80">
                    <Input
                        v-model="search"
                        placeholder="Search comments, author, article..."
                        class="text-xs"
                    />
                </form>

                <!-- Bulk Action Bar -->
                <div v-if="selectedIds.length > 0" class="flex items-center gap-2 bg-sky-50 dark:bg-sky-950/40 p-2 px-4 rounded-2xl border border-sky-200 dark:border-sky-800">
                    <span class="text-xs font-bold text-sky-800 dark:text-sky-300 font-mono">
                        {{ selectedIds.length }} Selected
                    </span>

                    <button
                        v-if="can('hide-comments')"
                        @click="executeBulkAction('hide')"
                        class="px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-500 text-white shadow-xs cursor-pointer"
                    >
                        Bulk Hide
                    </button>

                    <button
                        v-if="can('hide-comments')"
                        @click="executeBulkAction('dismiss')"
                        class="px-2.5 py-1 rounded-xl text-xs font-bold bg-sky-600 hover:bg-sky-500 text-white shadow-xs cursor-pointer"
                    >
                        Bulk Dismiss
                    </button>

                    <button
                        v-if="can('delete-comments')"
                        @click="executeBulkAction('delete')"
                        class="px-2.5 py-1 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white shadow-xs cursor-pointer"
                    >
                        Bulk Delete
                    </button>
                </div>
            </div>

            <!-- Comments Table -->
            <div class="bg-white dark:bg-zinc-950 rounded-3xl border border-slate-200 dark:border-zinc-800 overflow-hidden shadow-xs">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-zinc-800 bg-slate-50/50 dark:bg-zinc-900/50 text-slate-500 font-bold uppercase tracking-wider">
                            <th class="py-4 px-4 w-10 text-center">
                                <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll" class="rounded text-sky-600 focus:ring-sky-500" />
                            </th>
                            <th class="py-4 px-4">Comment & Article</th>
                            <th class="py-4 px-4">Author</th>
                            <th class="py-4 px-4 text-center">Status</th>
                            <th class="py-4 px-4 text-center">Reports</th>
                            <th class="py-4 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60">
                        <tr
                            v-for="c in comments.data"
                            :key="c.id"
                            class="hover:bg-slate-50/60 dark:hover:bg-zinc-900/40 transition"
                            :class="{ 'bg-rose-50/30 dark:bg-rose-950/10': c.reports_count > 0, 'opacity-60 bg-slate-50 dark:bg-zinc-900/20': c.is_deleted }"
                        >
                            <td class="py-4 px-4 text-center">
                                <input type="checkbox" :value="c.id" v-model="selectedIds" class="rounded text-sky-600 focus:ring-sky-500" />
                            </td>

                            <td class="py-4 px-4 space-y-1.5 max-w-md">
                                <p class="text-slate-900 dark:text-slate-100 font-medium leading-relaxed">
                                    "{{ c.comment }}"
                                </p>

                                <div class="flex items-center gap-2 text-[11px] text-slate-500 font-mono">
                                    <span>Article:</span>
                                    <Link v-if="c.article.slug" :href="`/articles/${c.article.slug}`" class="font-bold text-sky-600 dark:text-sky-400 hover:underline truncate max-w-xs">
                                        {{ c.article.title }}
                                    </Link>
                                    <span v-else class="font-bold text-slate-700 dark:text-zinc-300 truncate max-w-xs">
                                        {{ c.article.title }}
                                    </span>
                                </div>

                                <!-- Reports Snippet -->
                                <div v-if="c.reports.length > 0" class="pt-1.5 space-y-1">
                                    <div v-for="r in c.reports" :key="r.id" class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/50 text-[11px] text-rose-800 dark:text-rose-300">
                                        <span class="font-extrabold uppercase">🚨 {{ r.reason_label }}</span>
                                        <span v-if="r.details" class="italic ml-1">- "{{ r.details }}"</span>
                                        <span class="text-[10px] text-rose-500 ml-2 font-mono">({{ r.reporter }})</span>
                                    </div>
                                </div>
                            </td>

                            <td class="py-4 px-4">
                                <div class="flex items-center gap-2.5">
                                    <Avatar :name="c.user.name" size="sm" />
                                    <div>
                                        <p class="font-bold text-slate-900 dark:text-white text-xs">
                                            {{ c.user.name }}
                                        </p>
                                        <p class="text-[10px] text-slate-400 font-mono">
                                            {{ c.user.email }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="py-4 px-4 text-center">
                                <span v-if="c.is_deleted" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-zinc-200 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                    Deleted
                                </span>
                                <span v-else-if="c.is_hidden" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                    Hidden
                                </span>
                                <span v-else class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                    Visible
                                </span>
                            </td>

                            <td class="py-4 px-4 text-center font-mono font-bold">
                                <span v-if="c.reports_count > 0" class="px-2 py-0.5 rounded-full bg-rose-500 text-white text-[10px]">
                                    {{ c.reports_count }}
                                </span>
                                <span v-else class="text-slate-400">0</span>
                            </td>

                            <td class="py-4 px-4 text-right space-x-1.5 whitespace-nowrap">
                                <template v-if="c.is_deleted">
                                    <button
                                        v-if="can('delete-comments')"
                                        @click="restoreComment(c.id)"
                                        class="px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs cursor-pointer"
                                    >
                                        Restore
                                    </button>
                                </template>

                                <template v-else>
                                    <button
                                        v-if="can('hide-comments')"
                                        @click="toggleVisibility(c.id)"
                                        class="px-2.5 py-1 rounded-xl text-xs font-bold shadow-xs cursor-pointer"
                                        :class="c.is_hidden ? 'bg-emerald-600 hover:bg-emerald-500 text-white' : 'bg-amber-600 hover:bg-amber-500 text-white'"
                                    >
                                        {{ c.is_hidden ? 'Unhide' : 'Hide' }}
                                    </button>

                                    <button
                                        v-if="c.reports_count > 0 && can('hide-comments')"
                                        @click="dismissReports(c.id)"
                                        class="px-2.5 py-1 rounded-xl text-xs font-bold bg-sky-600 hover:bg-sky-500 text-white shadow-xs cursor-pointer"
                                    >
                                        Dismiss
                                    </button>

                                    <button
                                        v-if="can('delete-comments')"
                                        @click="deleteComment(c.id)"
                                        class="px-2.5 py-1 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white shadow-xs cursor-pointer"
                                    >
                                        Delete
                                    </button>
                                </template>
                            </td>
                        </tr>

                        <tr v-if="comments.data.length === 0">
                            <td colspan="6" class="py-12 text-center text-slate-400 text-xs font-mono">
                                No comments found matching criteria.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="comments.links.length > 3" class="pt-4 flex justify-center">
                <Pagination :links="comments.links" />
            </div>
        </div>
    </AdminLayout>
</template>
