<script setup lang="ts">
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    reviewId: number;
    helpfulCount: number;
    isVoted?: boolean;
    isOwn?: boolean;
}>();

const page = usePage();
const isSubmitting = ref(false);

function toggleVote() {
    if (! page.props.auth?.user) {
        router.get('/login');
        return;
    }
    if (props.isOwn || isSubmitting.value) return;

    isSubmitting.value = true;

    router.post(
        `/reviews/${props.reviewId}/vote`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isSubmitting.value = false;
            },
        }
    );
}
</script>

<template>
    <button
        type="button"
        :disabled="isOwn || isSubmitting"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 border"
        :class="[
            isVoted
                ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30 font-bold'
                : 'bg-slate-100 dark:bg-zinc-800/80 text-slate-600 dark:text-slate-300 border-slate-200/80 dark:border-zinc-700 hover:border-amber-400/60 hover:text-amber-600',
            isOwn ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
        ]"
        :title="isOwn ? 'You cannot vote on your own review' : (isVoted ? 'Click to remove helpful vote' : 'Mark as helpful')"
        @click="toggleVote"
    >
        <!-- Thumbs up icon -->
        <svg
            class="w-3.5 h-3.5 transition-transform"
            :class="{ 'scale-110 fill-amber-500 stroke-amber-500': isVoted, 'stroke-current fill-none': !isVoted }"
            viewBox="0 0 24 24"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3" />
        </svg>

        <span>Helpful</span>
        <span class="ml-0.5 font-bold" :class="isVoted ? 'text-amber-600 dark:text-amber-400' : 'text-slate-500 dark:text-slate-400'">
            ({{ helpfulCount }})
        </span>
    </button>
</template>
