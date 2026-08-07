<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

export interface BreadcrumbItem {
    label: string;
    href?: string;
}

defineProps<{
    items: BreadcrumbItem[];
}>();
</script>

<template>
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2 text-xs font-medium text-slate-400">
            <li class="inline-flex items-center">
                <Link href="/" class="hover:text-slate-200 transition inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Home
                </Link>
            </li>

            <li v-for="(item, index) in items" :key="index">
                <div class="flex items-center">
                    <svg class="w-3.5 h-3.5 text-slate-600 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>

                    <Link
                        v-if="item.href && index !== items.length - 1"
                        :href="item.href"
                        class="hover:text-slate-200 transition"
                    >
                        {{ item.label }}
                    </Link>
                    <span v-else class="text-slate-200 font-semibold">{{ item.label }}</span>
                </div>
            </li>
        </ol>
    </nav>
</template>
