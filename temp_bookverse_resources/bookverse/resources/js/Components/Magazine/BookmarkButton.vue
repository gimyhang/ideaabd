<script setup lang="ts">
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    submissionId: number;
    initialBookmarked?: boolean;
}>();

const isBookmarked = ref(props.initialBookmarked || false);

function toggleBookmark() {
    const page = usePage();
    if (!page.props.auth?.user) {
        router.get('/login');
        return;
    }

    isBookmarked.value = !isBookmarked.value;

    router.post(
        `/articles/${props.submissionId}/bookmark`,
        {},
        {
            preserveScroll: true,
            onError: () => {
                isBookmarked.value = !isBookmarked.value;
            },
        }
    );
}
</script>

<template>
    <button
        type="button"
        @click="toggleBookmark"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-xs font-bold transition cursor-pointer border shadow-xs"
        :class="isBookmarked
            ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-900 shadow-amber-500/10'
            : 'bg-white dark:bg-zinc-900 text-slate-700 dark:text-zinc-300 border-slate-200 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-850'"
    >
        <span class="text-base">
            {{ isBookmarked ? '🔖' : '📑' }}
        </span>

        <span>{{ isBookmarked ? 'Saved to Reading List' : 'Save for Later' }}</span>
    </button>
</template>
