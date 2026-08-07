<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex flex-col items-center justify-center p-8 lg:p-12 text-center rounded-2xl bg-slate-900/40 border border-slate-800/80 space-y-4">
        <div class="w-16 h-16 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-400 shadow-inner">
            <slot name="icon">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </slot>
        </div>

        <div class="space-y-1 max-w-sm">
            <h3 class="text-base font-bold text-white font-heading">{{ title }}</h3>
            <p v-if="description" class="text-xs text-slate-400 leading-relaxed">{{ description }}</p>
        </div>

        <div v-if="$slots.action" class="pt-2">
            <slot name="action" />
        </div>
    </div>
</template>
