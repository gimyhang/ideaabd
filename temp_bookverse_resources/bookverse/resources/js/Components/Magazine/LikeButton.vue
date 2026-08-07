<script setup lang="ts">
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    submissionId: number;
    initialLiked?: boolean;
    initialCount?: number;
}>();

const isLiked = ref(props.initialLiked || false);
const likesCount = ref(props.initialCount || 0);
const isAnimating = ref(false);

function toggleLike() {
    const page = usePage();
    if (!page.props.auth?.user) {
        router.get('/login');
        return;
    }

    // Optimistic UI Update
    isLiked.value = !isLiked.value;
    likesCount.value += isLiked.value ? 1 : -1;
    isAnimating.value = true;

    setTimeout(() => {
        isAnimating.value = false;
    }, 300);

    router.post(
        `/articles/${props.submissionId}/like`,
        {},
        {
            preserveScroll: true,
            onSuccess: (pageProps) => {
                // Keep server synced state
            },
            onError: () => {
                // Revert on error
                isLiked.value = !isLiked.value;
                likesCount.value += isLiked.value ? 1 : -1;
            },
        }
    );
}
</script>

<template>
    <button
        type="button"
        @click="toggleLike"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-xs font-bold transition cursor-pointer border shadow-xs"
        :class="isLiked
            ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-900 shadow-rose-500/10'
            : 'bg-white dark:bg-zinc-900 text-slate-700 dark:text-zinc-300 border-slate-200 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-850'"
    >
        <span
            class="text-base transition transform"
            :class="{ 'scale-130 text-rose-500': isAnimating, 'scale-100': !isAnimating }"
        >
            {{ isLiked ? '❤️' : '🤍' }}
        </span>

        <span>{{ likesCount }} {{ likesCount === 1 ? 'Like' : 'Likes' }}</span>
    </button>
</template>
