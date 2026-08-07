<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Avatar from '@/Components/UI/Avatar.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';
import Input from '@/Components/UI/Input.vue';
import ActivityTimeline from '@/Components/Writer/ActivityTimeline.vue';

interface User {
    id: number;
    name: string;
    email: string;
}

interface ActivityLog {
    id: number;
    event_type: string;
    description: string;
    created_at: string;
    user?: User;
}

interface Comment {
    id: number;
    parent_id?: number;
    type: 'editor' | 'author' | 'system';
    comment: string;
    created_at: string;
    user?: User;
    replies?: Comment[];
}

interface WriterProfile {
    id: number;
    pen_name: string;
    bio?: string;
    avatar_url: string;
    user?: User;
}

interface Submission {
    id: number;
    title: string;
    slug: string;
    excerpt?: string;
    content?: string;
    cover_url?: string;
    cover_image_alt?: string;
    status: 'draft' | 'pending_review' | 'approved' | 'published' | 'rejected' | 'archived';
    rejection_severity?: 'minor' | 'major';
    rejection_reason?: string;
    visibility: 'public' | 'members' | 'private';
    word_count: number;
    character_count: number;
    read_time_minutes: number;
    published_at?: string;
    rejected_at?: string;
    scheduled_at?: string;
    is_locked?: boolean;
    locked_at?: string;
    updated_at: string;
    writer_profile?: WriterProfile;
    publisher?: User;
    rejector?: User;
    locker?: User;
    comments?: Comment[];
    activity_logs?: ActivityLog[];
}

const props = defineProps<{
    submission: Submission;
}>();

// Schedule Modal State
const showScheduleModal = ref(false);
const scheduleForm = useForm({
    scheduled_at: props.submission.scheduled_at || '',
});

function confirmSchedule() {
    scheduleForm.post(`/admin/submissions/${props.submission.id}/schedule`, {
        preserveScroll: true,
        onSuccess: () => {
            showScheduleModal.value = false;
        },
    });
}

function toggleLock() {
    router.post(`/admin/submissions/${props.submission.id}/lock`, {}, { preserveScroll: true });
}

// Reject modal state
const showRejectModal = ref(false);
const rejectForm = useForm({
    reason: '',
    severity: 'major',
});

function confirmReject() {
    rejectForm.post(`/admin/submissions/${props.submission.id}/reject`, {
        preserveScroll: true,
        onSuccess: () => {
            showRejectModal.value = false;
            rejectForm.reset();
        },
    });
}

function publishLive() {
    if (confirm(`Publish "${props.submission.title}" live in E-Magazine?`)) {
        router.post(`/admin/submissions/${props.submission.id}/publish`, {}, { preserveScroll: true });
    }
}

// Comment Form State
const commentForm = useForm({
    comment: '',
    parent_id: null as number | null,
});

const replyingToComment = ref<Comment | null>(null);

function startReply(c: Comment) {
    replyingToComment.value = c;
    commentForm.parent_id = c.id;
}

function cancelReply() {
    replyingToComment.value = null;
    commentForm.parent_id = null;
}

function submitComment() {
    commentForm.post(`/admin/submissions/${props.submission.id}/comments`, {
        preserveScroll: true,
        onSuccess: () => {
            commentForm.reset();
            cancelReply();
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
    <AdminLayout>
        <template #header>Review Article Submission</template>

        <div class="max-w-5xl mx-auto space-y-8 font-sans">
            <!-- Header Moderation Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-3xl bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 shadow-xs">
                <div class="flex items-center gap-3">
                    <Link href="/admin/submissions" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 text-slate-700 dark:text-slate-200 transition">
                        ← Back to Submissions
                    </Link>
                    <Badge :variant="statusBadgeVariant(submission.status)" size="sm" class="capitalize font-bold">
                        {{ submission.status.replace('_', ' ') }}
                    </Badge>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Editorial Lock Toggle Button -->
                    <button
                        @click="toggleLock"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer border flex items-center gap-1.5"
                        :class="submission.is_locked
                            ? 'bg-amber-500 text-slate-950 border-amber-500 shadow-xs'
                            : 'bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-zinc-700'"
                    >
                        {{ submission.is_locked ? '🔒 Locked for Review' : '🔓 Lock Review' }}
                    </button>

                    <!-- Schedule Publish Button -->
                    <button
                        v-if="submission.status !== 'published'"
                        @click="showScheduleModal = true"
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 hover:bg-sky-600 hover:text-white border border-sky-200 dark:border-sky-800 transition cursor-pointer flex items-center gap-1.5"
                    >
                        📅 Schedule Publish
                    </button>

                    <!-- Reject Button -->
                    <button
                        v-if="submission.status !== 'published'"
                        @click="showRejectModal = true"
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white border border-rose-200 dark:border-rose-900 transition cursor-pointer"
                    >
                        🔴 Reject Article
                    </button>

                    <!-- Publish Live Button -->
                    <button
                        v-if="submission.status !== 'published'"
                        @click="publishLive"
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20 transition cursor-pointer flex items-center gap-1.5"
                    >
                        🟢 Publish Live
                    </button>
                </div>
            </div>

            <!-- Audit Trail Alert Info Box -->
            <div v-if="submission.status === 'published' && submission.publisher" class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900 flex items-center gap-3 text-xs text-emerald-800 dark:text-emerald-300 font-semibold">
                <span class="text-base">✨</span>
                <span>Published Live by <strong>{{ submission.publisher.name }}</strong> on {{ new Date(submission.published_at!).toLocaleString() }}.</span>
            </div>

            <div v-if="submission.status === 'rejected' && submission.rejector" class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900 flex items-center gap-3 text-xs text-rose-800 dark:text-rose-300 font-semibold">
                <span class="text-base">🔴</span>
                <span>Rejected with <strong>{{ submission.rejection_severity }} severity</strong> by <strong>{{ submission.rejector.name }}</strong> on {{ new Date(submission.rejected_at!).toLocaleString() }}.</span>
            </div>

            <!-- Reader Preview Canvas Card -->
            <div class="p-6 sm:p-10 rounded-3xl bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-6">
                <!-- Cover Image -->
                <div v-if="submission.cover_url" class="rounded-2xl overflow-hidden max-h-96 w-full bg-slate-100 dark:bg-zinc-900">
                    <img :src="submission.cover_url" :alt="submission.cover_image_alt || submission.title" class="w-full h-full object-cover" />
                </div>

                <!-- Author Meta Header -->
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-zinc-900">
                    <Avatar :name="submission.writer_profile?.pen_name || 'Author'" size="lg" />
                    <div>
                        <h4 class="font-extrabold text-slate-900 dark:text-white text-base">
                            {{ submission.writer_profile?.pen_name }}
                        </h4>
                        <p class="text-xs text-slate-500 font-mono">
                            ⏱️ {{ submission.read_time_minutes }} min read ({{ submission.word_count }} words)
                        </p>
                    </div>
                </div>

                <!-- Title & Excerpt -->
                <div class="space-y-3">
                    <h1 class="text-3xl font-black font-heading text-slate-900 dark:text-white leading-tight">
                        {{ submission.title }}
                    </h1>
                    <p v-if="submission.excerpt" class="text-sm text-slate-600 dark:text-zinc-400 italic bg-slate-50 dark:bg-zinc-900/50 p-4 rounded-2xl border border-slate-100 dark:border-zinc-800">
                        “{{ submission.excerpt }}”
                    </p>
                </div>

                <!-- Article Body HTML Content -->
                <div class="prose dark:prose-invert prose-sky max-w-none text-slate-800 dark:text-slate-200 pt-4" v-html="submission.content"></div>
            </div>

            <!-- Threaded Editorial Comments Thread Section -->
            <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-6 font-sans">
                <h3 class="font-black text-lg text-slate-900 dark:text-white flex items-center gap-2">
                    💬 Editorial Feedback & Discussion Thread
                </h3>

                <!-- Threaded Comments List -->
                <div class="space-y-4" v-if="submission.comments && submission.comments.length > 0">
                    <div
                        v-for="c in submission.comments"
                        :key="c.id"
                        class="p-4 rounded-2xl bg-slate-50 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 space-y-3"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <Avatar :name="c.user?.name || 'User'" size="sm" />
                                <span class="font-bold text-xs text-slate-900 dark:text-white">{{ c.user?.name }}</span>
                                <Badge size="sm" :variant="c.type === 'editor' ? 'brand' : 'default'" class="text-[10px] capitalize">
                                    {{ c.type }}
                                </Badge>
                            </div>
                            <span class="text-[10px] text-slate-400 font-mono">{{ new Date(c.created_at).toLocaleString() }}</span>
                        </div>

                        <p class="text-xs text-slate-700 dark:text-slate-300 whitespace-pre-line pl-8">
                            {{ c.comment }}
                        </p>

                        <div class="pl-8 pt-1">
                            <button @click="startReply(c)" class="text-[11px] font-bold text-sky-600 hover:underline">
                                💬 Reply
                            </button>
                        </div>

                        <!-- Nested Replies (parent_id) -->
                        <div v-if="c.replies && c.replies.length > 0" class="pl-8 space-y-3 pt-2 border-l-2 border-slate-200 dark:border-zinc-800 ml-4">
                            <div
                                v-for="reply in c.replies"
                                :key="reply.id"
                                class="p-3 rounded-xl bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 space-y-1 text-xs"
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

                <div v-else class="text-center py-6 text-slate-400 text-xs">
                    No editorial comments in this thread yet. Post feedback below.
                </div>

                <!-- Add Comment Form -->
                <form @submit.prevent="submitComment" class="space-y-3 pt-4 border-t border-slate-100 dark:border-zinc-900">
                    <div v-if="replyingToComment" class="flex items-center justify-between p-2 rounded-xl bg-sky-50 dark:bg-sky-950/40 text-xs text-sky-700 dark:text-sky-300">
                        <span>Replying to <strong>{{ replyingToComment.user?.name }}</strong></span>
                        <button type="button" @click="cancelReply" class="font-bold text-rose-500">✕ Cancel</button>
                    </div>

                    <textarea
                        v-model="commentForm.comment"
                        rows="3"
                        placeholder="Write editorial feedback or instructions for the author..."
                        required
                        class="w-full text-xs rounded-2xl border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-900 p-3.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-sky-500"
                    ></textarea>

                    <div class="flex justify-end">
                        <Button variant="brand" size="sm" type="submit" :loading="commentForm.processing">
                            Post Editorial Comment
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Reject Modal with Severity Options -->
            <Modal
                :show="showRejectModal"
                title="Reject Article Submission"
                maxWidth="md"
                @close="showRejectModal = false"
            >
                <form @submit.prevent="confirmReject" class="space-y-4 font-sans text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Rejection Severity Level <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="rejectForm.severity"
                            class="w-full text-xs rounded-xl border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-900 text-slate-900 dark:text-white p-2.5"
                        >
                            <option value="minor">Minor Fixes Required (Quick typo/format fix)</option>
                            <option value="major">Major Revision Required (Substantial structural changes)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Rejection Feedback Reason <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            v-model="rejectForm.reason"
                            rows="4"
                            placeholder="Explain constructive feedback and required changes for the author..."
                            required
                            class="w-full text-xs rounded-xl border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-900 text-slate-900 dark:text-white p-3"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-zinc-800">
                        <Button variant="secondary" size="sm" type="button" @click="showRejectModal = false">Cancel</Button>
                        <Button variant="destructive" size="sm" type="submit" :loading="rejectForm.processing">
                            Confirm Rejection
                        </Button>
                    </div>
                </form>
            </Modal>

            <!-- Schedule Publish Modal -->
            <Modal
                :show="showScheduleModal"
                title="Schedule Automated Article Release"
                maxWidth="md"
                @close="showScheduleModal = false"
            >
                <form @submit.prevent="confirmSchedule" class="space-y-4 font-sans text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Publication Date & Time <span class="text-rose-500">*</span>
                        </label>
                        <Input
                            v-model="scheduleForm.scheduled_at"
                            type="datetime-local"
                            required
                        />
                        <p class="text-[11px] text-slate-400 mt-1">
                            The automated scheduler task will publish this article live at the selected time.
                        </p>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-zinc-800">
                        <Button variant="secondary" size="sm" type="button" @click="showScheduleModal = false">Cancel</Button>
                        <Button variant="brand" size="sm" type="submit" :loading="scheduleForm.processing">
                            Confirm Schedule
                        </Button>
                    </div>
                </form>
            </Modal>

            <!-- Activity Timeline Container -->
            <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 shadow-xs">
                <ActivityTimeline :logs="submission.activity_logs" />
            </div>
        </div>
    </AdminLayout>
</template>
