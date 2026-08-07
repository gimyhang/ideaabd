<script setup lang="ts">
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    writerProfileId: number;
    initialFollowing?: boolean;
    initialCount?: number;
}>();

const isFollowing = ref(props.initialFollowing || false);
const followersCount = ref(props.initialCount || 0);
const isHovering = ref(false);

function toggleFollow() {
    const page = usePage();
    if (!page.props.auth?.user) {
        router.get('/login');
        return;
    }

    if (isFollowing.value) {
        // RESTful DELETE /writers/{id}/follow
        isFollowing.value = false;
        followersCount.value = Math.max(0, followersCount.value - 1);

        router.delete(`/writers/${props.writerProfileId}/follow`, {
            preserveScroll: true,
            onError: () => {
                isFollowing.value = true;
                followersCount.value += 1;
            },
        });
    } else {
        // RESTful POST /writers/{id}/follow
        isFollowing.value = true;
        followersCount.value += 1;

        router.post(`/writers/${props.writerProfileId}/follow`, {}, {
            preserveScroll: true,
            onError: () => {
                isFollowing.value = false;
                followersCount.value = Math.max(0, followersCount.value - 1);
            },
        });
    }
}
</script>

<template>
    <button
        type="button"
        @click="toggleFollow"
        @mouseenter="isHovering = true"
        @mouseleave="isHovering = false"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-xs font-bold transition cursor-pointer border shadow-xs"
        :class="isFollowing
            ? (isHovering
                ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-900'
                : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900')
            : 'bg-sky-600 hover:bg-sky-700 text-white border-sky-600 shadow-sky-600/20'"
    >
        <span class="text-sm">
            {{ isFollowing ? (isHovering ? '✖' : '✓') : '➕' }}
        </span>

        <span>
            {{ isFollowing ? (isHovering ? 'Unfollow' : 'Following') : 'Follow Author' }}
        </span>

        <span v-if="followersCount > 0" class="px-1.5 py-0.5 rounded-full text-[10px] bg-black/10 dark:bg-white/10 font-mono">
            {{ followersCount }}
        </span>
    </button>
</template>
