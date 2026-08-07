<script setup lang="ts">
import { ref } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import Avatar from '@/Components/UI/Avatar.vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';
import Select from '@/Components/UI/Select.vue';
import Textarea from '@/Components/UI/Textarea.vue';

interface User {
    id: number;
    name: string;
    email?: string;
    is_admin?: boolean;
}

interface Comment {
    id: number;
    parent_id?: number;
    comment: string;
    is_pinned?: boolean;
    created_at: string;
    user?: User;
    replies?: Comment[];
}

const props = defineProps<{
    submissionId: number;
    comments?: Comment[];
    authorUserId?: number;
}>();

const page = usePage();
const currentUser = page.props.auth?.user as User | undefined;

// Root Comment Form
const rootForm = useForm({
    comment: '',
    parent_id: null as number | null,
});

function submitRootComment() {
    if (!currentUser) {
        router.get('/login');
        return;
    }

    rootForm.post(`/articles/${props.submissionId}/comments`, {
        preserveScroll: true,
        onSuccess: () => {
            rootForm.reset();
        },
    });
}

// Reply Form State
const activeReplyId = ref<number | null>(null);
const replyForm = useForm({
    comment: '',
    parent_id: null as number | null,
});

function toggleReply(commentId: number) {
    if (activeReplyId.value === commentId) {
        activeReplyId.value = null;
    } else {
        activeReplyId.value = commentId;
        replyForm.parent_id = commentId;
        replyForm.comment = '';
    }
}

function submitReply(parentId: number) {
    if (!currentUser) {
        router.get('/login');
        return;
    }

    replyForm.parent_id = parentId;
    replyForm.post(`/articles/${props.submissionId}/comments`, {
        preserveScroll: true,
        onSuccess: () => {
            activeReplyId.value = null;
            replyForm.reset();
        },
    });
}

function deleteComment(commentId: number) {
    if (confirm('Are you sure you want to delete this comment?')) {
        router.delete(`/comments/${commentId}`, { preserveScroll: true });
    }
}

// Report Modal State
const showReportModal = ref(false);
const reportingCommentId = ref<number | null>(null);
const reportForm = useForm({
    reason: 'spam',
    other_reason: '',
});

const reportReasons = [
    { value: 'spam', label: 'Spam or Misleading' },
    { value: 'hate', label: 'Hate Speech or Discrimination' },
    { value: 'abuse', label: 'Harassment or Abuse' },
    { value: 'adult', label: 'Inappropriate Content' },
    { value: 'misinformation', label: 'Misinformation' },
    { value: 'other', label: 'Other Reason' },
];

function openReportModal(commentId: number) {
    reportingCommentId.value = commentId;
    showReportModal.value = true;
}

function submitReport() {
    if (!reportingCommentId.value) return;

    reportForm.post(`/comments/${reportingCommentId.value}/report`, {
        preserveScroll: true,
        onSuccess: () => {
            showReportModal.value = false;
            reportForm.reset();
        },
    });
}
</script>

<template>
    <div class="space-y-8 font-sans text-xs">
        <h3 class="font-black text-xl font-heading text-slate-900 dark:text-white flex items-center gap-2">
            💬 Reader Discussion ({{ comments ? comments.length : 0 }})
        </h3>

        <!-- Post Root Comment Form -->
        <form @submit.prevent="submitRootComment" class="space-y-3 p-5 rounded-3xl bg-slate-50 dark:bg-zinc-900/60 border border-slate-200 dark:border-zinc-800">
            <div class="flex items-center gap-2 font-bold text-slate-700 dark:text-slate-300">
                <Avatar v-if="currentUser" :name="currentUser.name" size="sm" />
                <span>{{ currentUser ? `Comment as ${currentUser.name}` : 'Join the discussion' }}</span>
            </div>

            <Textarea
                v-model="rootForm.comment"
                placeholder="Share your thoughts on this article..."
                :rows="3"
                required
            />

            <div class="flex justify-end">
                <Button variant="brand" size="sm" type="submit" :loading="rootForm.processing">
                    Post Comment
                </Button>
            </div>
        </form>

        <!-- 2-Level Nested Comment List (Oldest First) -->
        <div v-if="comments && comments.length > 0" class="space-y-6">
            <div
                v-for="comment in comments"
                :key="comment.id"
                class="p-5 rounded-3xl bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 space-y-3"
            >
                <!-- Comment Header -->
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <Avatar :name="comment.user?.name || 'Anonymous'" size="sm" />
                        <div>
                            <div class="flex items-center gap-1.5 font-bold text-slate-900 dark:text-white text-xs">
                                <span>{{ comment.user?.name }}</span>

                                <span v-if="comment.user?.id === authorUserId" class="px-2 py-0.5 rounded-full bg-sky-100 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 text-[10px] font-extrabold border border-sky-200 dark:border-sky-800">
                                    Author
                                </span>

                                <span v-else-if="comment.user?.is_admin" class="px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-[10px] font-extrabold border border-indigo-200 dark:border-indigo-800">
                                    Editor
                                </span>
                            </div>
                            <span class="text-[10px] text-slate-400 font-mono">
                                {{ new Date(comment.created_at).toLocaleString() }}
                            </span>
                        </div>
                    </div>

                    <!-- Comment Actions (Delete / Report) -->
                    <div class="flex items-center gap-2">
                        <button
                            v-if="currentUser && (currentUser.id === comment.user?.id || currentUser.is_admin)"
                            @click="deleteComment(comment.id)"
                            class="text-[11px] font-bold text-rose-500 hover:underline cursor-pointer"
                        >
                            Delete
                        </button>

                        <button
                            v-if="currentUser && currentUser.id !== comment.user?.id"
                            @click="openReportModal(comment.id)"
                            class="text-[11px] font-semibold text-slate-400 hover:text-rose-500 cursor-pointer"
                        >
                            🚩 Report
                        </button>
                    </div>
                </div>

                <!-- Comment Content -->
                <p class="text-slate-700 dark:text-slate-300 text-xs leading-relaxed pl-8">
                    {{ comment.comment }}
                </p>

                <!-- Reply Trigger Button -->
                <div class="pl-8 pt-1">
                    <button
                        @click="toggleReply(comment.id)"
                        class="text-[11px] font-bold text-sky-600 dark:text-sky-400 hover:underline cursor-pointer flex items-center gap-1"
                    >
                        💬 {{ activeReplyId === comment.id ? 'Cancel Reply' : 'Reply' }}
                    </button>
                </div>

                <!-- Reply Form -->
                <div v-if="activeReplyId === comment.id" class="pl-8 pt-2">
                    <form @submit.prevent="submitReply(comment.id)" class="space-y-2 p-3 rounded-2xl bg-slate-50 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800">
                        <Textarea
                            v-model="replyForm.comment"
                            placeholder="Write a reply..."
                            :rows="2"
                            required
                        />
                        <div class="flex justify-end gap-2">
                            <Button variant="secondary" size="sm" type="button" @click="activeReplyId = null">Cancel</Button>
                            <Button variant="brand" size="sm" type="submit" :loading="replyForm.processing">Post Reply</Button>
                        </div>
                    </form>
                </div>

                <!-- 2nd-Level Nested Replies -->
                <div v-if="comment.replies && comment.replies.length > 0" class="pl-8 space-y-3 pt-3 border-t border-slate-100 dark:border-zinc-900">
                    <div
                        v-for="reply in comment.replies"
                        :key="reply.id"
                        class="p-3 rounded-2xl bg-slate-50 dark:bg-zinc-900/80 border border-slate-200 dark:border-zinc-800 space-y-2"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <Avatar :name="reply.user?.name || 'Anonymous'" size="xs" />
                                <div>
                                    <div class="flex items-center gap-1 font-bold text-slate-900 dark:text-white text-[11px]">
                                        <span>{{ reply.user?.name }}</span>
                                        <span v-if="reply.user?.id === authorUserId" class="text-[9px] text-sky-600 font-bold">● Author</span>
                                    </div>
                                    <span class="text-[9px] text-slate-400 font-mono">
                                        {{ new Date(reply.created_at).toLocaleString() }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 text-[10px]">
                                <button
                                    v-if="currentUser && (currentUser.id === reply.user?.id || currentUser.is_admin)"
                                    @click="deleteComment(reply.id)"
                                    class="font-bold text-rose-500 hover:underline cursor-pointer"
                                >
                                    Delete
                                </button>
                                <button
                                    v-if="currentUser && currentUser.id !== reply.user?.id"
                                    @click="openReportModal(reply.id)"
                                    class="text-slate-400 hover:text-rose-500 cursor-pointer"
                                >
                                    🚩 Report
                                </button>
                            </div>
                        </div>

                        <p class="text-slate-700 dark:text-slate-300 text-xs pl-6">
                            {{ reply.comment }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-6 text-slate-400 text-xs bg-slate-50 dark:bg-zinc-900/40 rounded-3xl border border-slate-200 dark:border-zinc-800">
            No comments posted yet. Be the first to share your thoughts!
        </div>

        <!-- Comment Report Modal -->
        <Modal
            :show="showReportModal"
            title="Report Inappropriate Comment"
            maxWidth="md"
            @close="showReportModal = false"
        >
            <form @submit.prevent="submitReport" class="space-y-4 font-sans text-xs">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Reason for Report <span class="text-rose-500">*</span>
                    </label>
                    <Select
                        v-model="reportForm.reason"
                        :options="reportReasons"
                    />
                </div>

                <div v-if="reportForm.reason === 'other'">
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Additional Details
                    </label>
                    <Textarea
                        v-model="reportForm.other_reason"
                        placeholder="Please describe why this comment is inappropriate..."
                        :rows="3"
                    />
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-zinc-800">
                    <Button variant="secondary" size="sm" type="button" @click="showReportModal = false">Cancel</Button>
                    <Button variant="destructive" size="sm" type="submit" :loading="reportForm.processing">
                        Submit Report
                    </Button>
                </div>
            </form>
        </Modal>
    </div>
</template>
