<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

defineProps<{
    links: PaginationLink[];
}>();
</script>

<template>
    <nav v-if="links && links.length > 3" class="flex flex-wrap items-center justify-center gap-1.5 py-4 font-sans">
        <template v-for="(link, index) in links" :key="index">
            <div
                v-if="!link.url"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-400 dark:text-zinc-600 bg-slate-100/60 dark:bg-zinc-800/40 cursor-not-allowed border border-transparent"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200"
                :class="
                    link.active
                        ? 'bg-sky-600 text-white shadow-sm font-bold'
                        : 'bg-white dark:bg-zinc-900 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-zinc-800 border border-slate-200 dark:border-zinc-800'
                "
                v-html="link.label"
                preserve-scroll
            />
        </template>
    </nav>
</template>
