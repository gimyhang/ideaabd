<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import MainLayout from '@/Layouts/MainLayout.vue';
import Badge from '@/Components/UI/Badge.vue';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';
import TiptapEditor from '@/Components/Writer/TiptapEditor.vue';

interface Revision {
    id: number;
    version: number;
    reason: string;
    title: string;
    created_at: string;
    creator?: {
        name: string;
    };
}

interface Comment {
    id: number;
    parent_id?: number;
    type: 'editor' | 'author' | 'system';
    comment: string;
    created_at: string;
    user?: {
        name: string;
    };
    replies?: Comment[];
}

interface Submission {
    id: number;
    title: string;
    slug: string;
    excerpt?: string;
    content?: string;
    content_json?: any;
    cover_image?: string;
    cover_image_alt?: string;
    status: 'draft' | 'pending_review' | 'approved' | 'published' | 'rejected' | 'archived';
    rejection_severity?: 'minor' | 'major';
    rejection_reason?: string;
    is_locked?: boolean;
    visibility: 'public' | 'members' | 'private';
    word_count: number;
    character_count: number;
    read_time_minutes: number;
    meta_title?: string;
    meta_description?: string;
    canonical_url?: string;
    allow_comments: boolean;
    revisions?: Revision[];
    comments?: Comment[];
    updated_at: string;
}

const props = defineProps<{
    submission: Submission;
}>();

const form = useForm({
    title: props.submission.title,
    excerpt: props.submission.excerpt || '',
    content: props.submission.content || '',
    content_json: props.submission.content_json,
    cover_image_alt: props.submission.cover_image_alt || '',
    visibility: props.submission.visibility || 'public',
    meta_title: props.submission.meta_title || '',
    meta_description: props.submission.meta_description || '',
    canonical_url: props.submission.canonical_url || '',
    allow_comments: props.submission.allow_comments ?? true,
});

const autoSaveStatus = ref<'saved' | 'saving' | 'dirty' | 'idle'>('idle');
const lastSavedAt = ref('');
const wordCount = ref(props.submission.word_count);
const readTimeMinutes = ref(props.submission.read_time_minutes);

const showRevisionsDrawer = ref(false);
const showOptionsDrawer = ref(false);
const showRestoreModal = ref(false);
const selectedRevisionToRestore = ref<Revision | null>(null);

// 2-Second Debounced Auto-Save
let autoSaveTimer: any = null;

watch(
    () => [form.title, form.excerpt, form.content, form.visibility, form.cover_image_alt],
    () => {
        autoSaveStatus.value = 'dirty';
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => {
            triggerAutoSave();
        }, 2000);
    },
    { deep: true }
);

async function triggerAutoSave() {
    autoSaveStatus.value = 'saving';
    try {
        const response = await axios.post(`/writer/articles/${props.submission.id}/auto-save`, {
            title: form.title,
            excerpt: form.excerpt,
            content: form.content,
            content_json: form.content_json,
            cover_image_alt: form.cover_image_alt,
            visibility: form.visibility,
            meta_title: form.meta_title,
            meta_description: form.meta_description,
            canonical_url: form.canonical_url,
            allow_comments: form.allow_comments,
        });

        if (response.data.success) {
            wordCount.value = response.data.word_count;
            readTimeMinutes.value = response.data.read_time_minutes;
            lastSavedAt.value = response.data.saved_at;
            autoSaveStatus.value = 'saved';
        }
    } catch (error) {
        autoSaveStatus.value = 'dirty';
    }
}

function handleManualSaveRevision() {
    router.post(`/writer/articles/${props.submission.id}/save-revision`, { reason: 'manual_save' }, { preserveScroll: true });
}

function submitForReview() {
    if (confirm(`Submit "${props.submission.title}" for editorial review?`)) {
        router.post(`/writer/articles/${props.submission.id}/submit`, {}, { preserveScroll: true });
    }
}

function resubmitForReview() {
    if (confirm(`Resubmit "${props.submission.title}" for editorial review?`)) {
        router.post(`/writer/articles/${props.submission.id}/resubmit`, {}, { preserveScroll: true });
    }
}

function promptRestoreRevision(rev: Revision) {
    selectedRevisionToRestore.value = rev;
    showRestoreModal.value = true;
}

const commentForm = useForm({
    comment: '',
    parent_id: null as number | null,
});

function submitAuthorComment() {
    commentForm.post(`/writer/articles/${props.submission.id}/comments`, {
        preserveScroll: true,
        onSuccess: () => {
            commentForm.reset();
        },
    });
}

function confirmRestoreRevision() {
    if (!selectedRevisionToRestore.value) return;

    router.post(`/writer/articles/${props.submission.id}/restore-revision/${selectedRevisionToRestore.value.id}`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            showRestoreModal.value = false;
            showRevisionsDrawer.value = false;
            selectedRevisionToRestore.value = null;
        },
    });
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
    <Head :title="`Editing: ${form.title} | BookVerse E-Magazine`" />

    <MainLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6 font-sans">
            <!-- Top Navbar Controls -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-xs">
                <div class="flex items-center gap-3">
                    <Link href="/writer/articles" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 text-slate-700 dark:text-slate-200 transition">
                        ← Articles
                    </Link>
                    <Badge :variant="statusBadgeVariant(submission.status)" size="sm" class="capitalize font-bold">
                        {{ submission.status.replace('_', ' ') }}
                    </Badge>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Revisions History Drawer Button -->
                    <button
                        type="button"
                        @click="showRevisionsDrawer = !showRevisionsDrawer"
                        class="px-3 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-zinc-700 transition cursor-pointer flex items-center gap-1.5"
                    >
                        📜 History ({{ submission.revisions?.length || 0 }})
                    </button>

                    <!-- Article Options Drawer Button -->
                    <button
                        type="button"
                        @click="showOptionsDrawer = !showOptionsDrawer"
                        class="px-3 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-zinc-700 transition cursor-pointer flex items-center gap-1.5"
                    >
                        ⚙️ Settings
                    </button>

                    <!-- Manual Save Version Snapshot -->
                    <button
                        type="button"
                        @click="handleManualSaveRevision"
                        class="px-3 py-2 rounded-xl text-xs font-bold bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 hover:bg-sky-600 hover:text-white border border-sky-200 dark:border-sky-800 transition cursor-pointer"
                    >
                        💾 Save Version
                    </button>

                    <!-- Submit / Resubmit for Review -->
                    <Button v-if="submission.status === 'draft'" variant="brand" size="sm" @click="submitForReview">
                        🚀 Submit for Review
                    </Button>

                    <Button v-if="submission.status === 'rejected'" variant="brand" size="sm" @click="resubmitForReview">
                        🔄 Re-submit for Review
                    </Button>
                </div>
            </div>

            <!-- Editorial Locked Warning Banner -->
            <div v-if="submission.is_locked" class="p-4 rounded-3xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 text-xs text-amber-800 dark:text-amber-300 font-semibold flex items-center gap-3">
                <span class="text-lg">🔒</span>
                <div>
                    <strong>Article Locked for Review:</strong> An editor is currently reviewing this submission. Editing is disabled until review is completed.
                </div>
            </div>

            <!-- Rejection Feedback Banner -->
            <div v-if="submission.status === 'rejected'" class="p-5 rounded-3xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="font-black text-rose-800 dark:text-rose-300 text-sm flex items-center gap-2">
                        🛑 Article Rejected by Editorial Moderation
                    </span>
                    <Badge variant="error" size="sm" class="uppercase font-extrabold text-[10px]">
                        {{ submission.rejection_severity || 'Major' }} Revision Required
                    </Badge>
                </div>
                <p v-if="submission.rejection_reason" class="text-rose-700 dark:text-rose-200 bg-white/70 dark:bg-zinc-950/60 p-3 rounded-2xl border border-rose-200 dark:border-rose-900">
                    “{{ submission.rejection_reason }}”
                </p>
                <p class="text-[11px] text-rose-600 dark:text-rose-400">
                    Please make the requested edits in the editor below and click <strong>Re-submit for Review</strong> to send your updated article back to the moderation queue.
                </p>
            </div>

            <!-- Title & Excerpt Inputs -->
            <div class="space-y-4">
                <input
                    v-model="form.title"
                    type="text"
                    placeholder="Article Title..."
                    required
                    class="w-full text-2xl sm:text-3xl font-black font-heading bg-transparent border-none focus:outline-hidden text-slate-900 dark:text-white placeholder-slate-300 dark:placeholder-zinc-700 px-0"
                />

                <textarea
                    v-model="form.excerpt"
                    rows="2"
                    placeholder="Short summary excerpt..."
                    class="w-full text-xs sm:text-sm rounded-2xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-3.5 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-sky-500"
                ></textarea>
            </div>

            <!-- Tiptap Editor Canvas -->
            <TiptapEditor
                v-model="form.content"
                :auto-save-status="autoSaveStatus"
                :last-saved-at="lastSavedAt"
                @update:model-value="(html) => form.content = html"
                @change-json="(json) => form.content_json = json"
                @save-revision="handleManualSaveRevision"
            />

            <!-- Options Drawer -->
            <div v-if="showOptionsDrawer" class="p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-lg space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-3">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">Article Options & Settings</h3>
                    <button type="button" @click="showOptionsDrawer = false" class="text-xs text-slate-400 font-bold">✕ Close</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Cover Image Alt Text (SEO)</label>
                        <Input v-model="form.cover_image_alt" placeholder="Descriptive image alt tag" />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Visibility</label>
                        <select v-model="form.visibility" class="w-full text-xs rounded-xl border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-950 p-2.5 text-slate-800 dark:text-slate-200">
                            <option value="public">Public Access</option>
                            <option value="members">Members Only</option>
                            <option value="private">Private Draft</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Threaded Editorial Discussion & Feedback Section -->
            <div class="p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-4 text-xs font-sans">
                <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                    💬 Editorial Discussion & Feedback Thread
                </h3>

                <div class="space-y-3" v-if="submission.comments && submission.comments.length > 0">
                    <div
                        v-for="c in submission.comments"
                        :key="c.id"
                        class="p-3.5 rounded-2xl bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 space-y-2"
                    >
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900 dark:text-white">{{ c.user?.name }} ({{ c.type }})</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ new Date(c.created_at).toLocaleString() }}</span>
                        </div>
                        <p class="text-slate-700 dark:text-slate-300 text-xs whitespace-pre-line">{{ c.comment }}</p>

                        <!-- Nested Replies -->
                        <div v-if="c.replies && c.replies.length > 0" class="pl-4 space-y-2 pt-2 border-l-2 border-slate-200 dark:border-zinc-800 ml-2">
                            <div
                                v-for="reply in c.replies"
                                :key="reply.id"
                                class="p-2.5 rounded-xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 text-xs"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ reply.user?.name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ new Date(reply.created_at).toLocaleString() }}</span>
                                </div>
                                <p class="text-slate-600 dark:text-slate-300 text-xs">{{ reply.comment }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-4 text-slate-400 text-xs">
                    No editorial comments yet.
                </div>

                <!-- Post Reply Comment -->
                <form @submit.prevent="submitAuthorComment" class="space-y-2 pt-3 border-t border-slate-100 dark:border-zinc-800">
                    <textarea
                        v-model="commentForm.comment"
                        rows="2"
                        placeholder="Reply to editor feedback..."
                        required
                        class="w-full text-xs rounded-xl border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-950 p-3 text-slate-900 dark:text-white"
                    ></textarea>
                    <div class="flex justify-end">
                        <Button variant="secondary" size="sm" type="submit" :loading="commentForm.processing">
                            Post Comment Reply
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Revision History Drawer Modal -->
            <div v-if="showRevisionsDrawer" class="p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-lg space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-3">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">Revision Snapshot History</h3>
                    <button type="button" @click="showRevisionsDrawer = false" class="text-xs text-slate-400 font-bold">✕ Close</button>
                </div>

                <div class="space-y-3" v-if="submission.revisions && submission.revisions.length > 0">
                    <div
                        v-for="rev in submission.revisions"
                        :key="rev.id"
                        class="p-3.5 rounded-2xl bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 flex items-center justify-between gap-3 text-xs"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 dark:text-white">Version #{{ rev.version }}</span>
                                <Badge size="sm" variant="default" class="capitalize text-[10px]">
                                    {{ rev.reason.replace('_', ' ') }}
                                </Badge>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                Saved by {{ rev.creator?.name || 'Author' }} on {{ new Date(rev.created_at).toLocaleString() }}
                            </p>
                        </div>

                        <button
                            @click="promptRestoreRevision(rev)"
                            class="px-3 py-1.5 rounded-xl font-bold bg-sky-600 hover:bg-sky-700 text-white text-xs transition cursor-pointer"
                        >
                            Restore Version
                        </button>
                    </div>
                </div>

                <div v-else class="text-center py-6 text-slate-400 text-xs">
                    No manual version snapshots saved yet. Press Ctrl+S to create a version checkpoint!
                </div>
            </div>

            <!-- Restore Revision Confirmation Modal -->
            <Modal
                :show="showRestoreModal"
                title="Restore Revision Version"
                maxWidth="md"
                @close="showRestoreModal = false"
            >
                <div class="space-y-4 font-sans text-xs">
                    <p class="text-slate-600 dark:text-slate-300">
                        Are you sure you want to restore <strong>Version #{{ selectedRevisionToRestore?.version }}</strong>? A backup snapshot of your current draft will be automatically saved before restoring.
                    </p>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-zinc-800">
                        <Button variant="secondary" size="sm" @click="showRestoreModal = false">Cancel</Button>
                        <Button variant="brand" size="sm" @click="confirmRestoreRevision">
                            Yes, Restore Version #{{ selectedRevisionToRestore?.version }}
                        </Button>
                    </div>
                </div>
            </Modal>
        </div>
    </MainLayout>
</template>
