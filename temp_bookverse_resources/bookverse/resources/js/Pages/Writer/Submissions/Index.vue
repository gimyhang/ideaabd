<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import ConfirmDeleteModal from '@/Components/UI/ConfirmDeleteModal.vue';

interface SubmissionItem {
    id: number;
    title: string;
    slug: string;
    excerpt?: string;
    cover_url?: string;
    status: 'draft' | 'pending_review' | 'approved' | 'published' | 'rejected' | 'archived';
    word_count: number;
    read_time_minutes: number;
    updated_at: string;
    created_at: string;
}

interface PaginatedSubmissions {
    data: SubmissionItem[];
    links: any[];
    current_page: number;
    last_page: number;
    total: number;
}

interface StatusCounts {
    all: number;
    drafts: number;
    pending: number;
    published: number;
    archived: number;
}

const props = defineProps<{
    submissions: PaginatedSubmissions;
    filters: {
        status?: string;
    };
    counts: StatusCounts;
}>();

const selectedStatus = ref(props.filters.status || '');

function selectStatusFilter(statusKey: string) {
    selectedStatus.value = statusKey;
    router.get(
        '/writer/articles',
        { status: statusKey || undefined },
        { preserveState: true, replace: true }
    );
}

const showDeleteModal = ref(false);
const itemToDelete = ref<SubmissionItem | null>(null);

function promptDelete(article: SubmissionItem) {
    itemToDelete.value = article;
    showDeleteModal.value = true;
}

function confirmDelete() {
    if (!itemToDelete.value) return;
    router.delete(`/writer/articles/${itemToDelete.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            showDeleteModal.value = false;
            itemToDelete.value = null;
        },
    });
}

function submitForReview(article: SubmissionItem) {
    if (confirm(`Submit "${article.title}" for editorial review?`)) {
        router.post(`/writer/articles/${article.id}/submit`, {}, { preserveScroll: true });
    }
}

function statusBadgeVariant(status: string) {
    switch (status) {
        case 'published': return 'success';
        case 'approved': return 'brand';
        case 'pending_review': return 'warning';
        case 'rejected': return 'error';
        case 'archived': return 'default';
        default: return 'default';
    }
}

function statusEmoji(status: string) {
    switch (status) {
        case 'published': return '✨';
        case 'approved': return '✅';
        case 'pending_review': return '⏳';
        case 'rejected': return '❌';
        case 'archived': return '📦';
        default: return '📝';
    }
}
</script>

<template>
    <Head title="My Articles & Drafts | BookVerse E-Magazine" />

    <MainLayout>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6 font-sans">

            <!-- Hero Header Banner -->
            <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-sky-600 via-indigo-700 to-violet-800 p-6 sm:p-8 shadow-xl shadow-indigo-500/20">
                <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_70%_50%,white,transparent)]"></div>
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                    <div class="space-y-2">
                        <Link href="/writer/dashboard" class="inline-flex items-center gap-1.5 text-[11px] font-bold text-white/70 hover:text-white transition">
                            ← Writer Workspace
                        </Link>
                        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight leading-tight">
                            My Articles & Drafts
                        </h1>
                        <p class="text-xs text-white/60 max-w-md">
                            Manage your stories, submit for editorial review, and track article status across your workspace.
                        </p>
                    </div>
                    <Link href="/writer/articles/create" class="shrink-0 self-start sm:self-auto">
                        <span class="inline-flex items-center gap-2 px-5 py-3 bg-white text-indigo-700 font-extrabold text-xs rounded-2xl shadow-lg shadow-black/10 hover:bg-indigo-50 transition-all duration-200 active:scale-95 cursor-pointer">
                            ✏️ Write New Article
                        </span>
                    </Link>
                </div>

                <!-- Quick Stats Row -->
                <div class="relative z-10 mt-6 grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-4 py-3 text-center border border-white/10">
                        <p class="text-xl font-black text-white">{{ counts.all }}</p>
                        <p class="text-[10px] font-bold text-white/60 uppercase tracking-wider">Total</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-4 py-3 text-center border border-white/10">
                        <p class="text-xl font-black text-white">{{ counts.drafts }}</p>
                        <p class="text-[10px] font-bold text-white/60 uppercase tracking-wider">Drafts</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-4 py-3 text-center border border-white/10">
                        <p class="text-xl font-black text-white">{{ counts.pending }}</p>
                        <p class="text-[10px] font-bold text-white/60 uppercase tracking-wider">Pending</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-4 py-3 text-center border border-white/10">
                        <p class="text-xl font-black text-white">{{ counts.published }}</p>
                        <p class="text-[10px] font-bold text-white/60 uppercase tracking-wider">Published</p>
                    </div>
                </div>
            </div>

            <!-- Status Filter Tabs (horizontally scrollable on mobile) -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                <button
                    v-for="tab in [
                        { key: '', label: 'All', count: counts.all, color: 'bg-slate-900 dark:bg-white text-white dark:text-slate-900' },
                        { key: 'draft', label: '📝 Drafts', count: counts.drafts, color: 'bg-sky-600 text-white' },
                        { key: 'pending_review', label: '⏳ Pending', count: counts.pending, color: 'bg-amber-500 text-slate-900' },
                        { key: 'published', label: '✨ Published', count: counts.published, color: 'bg-emerald-600 text-white' },
                        { key: 'archived', label: '📦 Archived', count: counts.archived, color: 'bg-slate-600 text-white' },
                    ]"
                    :key="tab.key"
                    @click="selectStatusFilter(tab.key)"
                    class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all border cursor-pointer shrink-0"
                    :class="selectedStatus === tab.key
                        ? tab.color + ' border-transparent shadow-sm'
                        : 'bg-white dark:bg-zinc-950 border-slate-200 dark:border-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-900'"
                >
                    {{ tab.label }} <span class="ml-1 opacity-70">({{ tab.count }})</span>
                </button>
            </div>

            <!-- Articles Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" v-if="submissions.data.length > 0">
                <div
                    v-for="article in submissions.data"
                    :key="article.id"
                    class="group relative flex flex-col bg-white dark:bg-zinc-950 rounded-3xl border border-slate-200 dark:border-zinc-800 overflow-hidden shadow-xs hover:shadow-md hover:border-sky-300 dark:hover:border-sky-800 transition-all duration-300"
                >
                    <!-- Top Color Accent -->
                    <div class="h-1.5 w-full"
                        :class="{
                            'bg-gradient-to-r from-emerald-400 to-teal-500': article.status === 'published',
                            'bg-gradient-to-r from-amber-400 to-orange-400': article.status === 'pending_review',
                            'bg-gradient-to-r from-sky-400 to-indigo-500': article.status === 'draft',
                            'bg-gradient-to-r from-rose-400 to-pink-500': article.status === 'rejected',
                            'bg-gradient-to-r from-slate-400 to-slate-500': article.status === 'archived',
                            'bg-gradient-to-r from-blue-400 to-indigo-500': article.status === 'approved',
                        }"
                    ></div>

                    <div class="p-5 flex flex-col flex-1 space-y-4">
                        <!-- Status + Meta Row -->
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <Badge :variant="statusBadgeVariant(article.status)" size="sm" class="font-extrabold capitalize shrink-0">
                                {{ statusEmoji(article.status) }} {{ article.status.replace('_', ' ') }}
                            </Badge>
                            <span class="text-[10px] text-slate-400 font-mono shrink-0">
                                ⏱ {{ article.read_time_minutes }}m · {{ article.word_count }} words
                            </span>
                        </div>

                        <!-- Title & Excerpt -->
                        <div class="flex-1 space-y-1">
                            <h3 class="font-black text-base leading-snug text-slate-900 dark:text-white line-clamp-2 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">
                                {{ article.title }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-zinc-400 line-clamp-2 leading-relaxed">
                                {{ article.excerpt || 'No excerpt summary provided yet...' }}
                            </p>
                        </div>

                        <!-- Footer -->
                        <div class="pt-3 border-t border-slate-100 dark:border-zinc-900 flex items-center justify-between gap-2 flex-wrap">
                            <span class="text-[10px] text-slate-400 font-medium">
                                Updated {{ new Date(article.updated_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                            </span>

                            <div class="flex items-center gap-1.5 flex-wrap justify-end">
                                <!-- Edit -->
                                <Link :href="`/writer/articles/${article.id}/edit`">
                                    <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-[11px] font-bold bg-slate-100 hover:bg-sky-600 hover:text-white dark:bg-zinc-800 dark:hover:bg-sky-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-zinc-700 transition cursor-pointer">
                                        ✏️ Edit
                                    </button>
                                </Link>

                                <!-- Submit for Review -->
                                <button
                                    v-if="article.status === 'draft'"
                                    @click="submitForReview(article)"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-[11px] font-bold bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 hover:bg-sky-600 hover:text-white border border-sky-200 dark:border-sky-800 transition cursor-pointer"
                                >
                                    🚀 Submit
                                </button>

                                <!-- Delete -->
                                <button
                                    @click="promptDelete(article)"
                                    class="p-1.5 rounded-xl text-[11px] font-bold bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-500 border border-rose-100 dark:bg-rose-950/30 dark:border-rose-900 dark:text-rose-400 transition cursor-pointer"
                                    title="Move to trash"
                                >
                                    🗑️
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-20 rounded-3xl bg-white dark:bg-zinc-950 border border-dashed border-slate-200 dark:border-zinc-800 space-y-4">
                <span class="text-5xl block">✍️</span>
                <h3 class="font-black text-lg text-slate-900 dark:text-white">No articles found</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
                    You haven't created any stories in this tab yet. Start writing your next article using the rich editor!
                </p>
                <Link href="/writer/articles/create">
                    <Button variant="brand" size="sm" class="mt-2">
                        + Create Your First Article
                    </Button>
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="submissions.links.length > 3" class="pt-2">
                <Pagination :links="submissions.links" />
            </div>

            <!-- Delete Confirmation Modal -->
            <ConfirmDeleteModal
                :show="showDeleteModal"
                title="Move Article to Trash"
                :item-name="itemToDelete?.title"
                message="Are you sure you want to move this article to trash? An admin can restore it if needed."
                @confirm="confirmDelete"
                @close="showDeleteModal = false"
            />
        </div>
    </MainLayout>
</template>
