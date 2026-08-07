<script setup lang="ts">
import { useAdminTheme } from '@/Composables/useAdminTheme';

const { isDark, toggle } = useAdminTheme();
</script>

<template>
    <button
        type="button"
        @click="toggle"
        class="p-2 rounded-xl border transition-all duration-300 relative overflow-hidden group shadow-xs cursor-pointer active:scale-95 bg-slate-50 hover:bg-slate-100 border-slate-200 dark:bg-zinc-900/50 dark:hover:bg-zinc-800/80 dark:border-zinc-800"
        title="Toggle Light/Dark Theme"
    >
        <!-- Icon wrapper with micro-animation -->
        <span class="flex items-center justify-center w-5 h-5 leading-none transition-transform duration-500 group-hover:rotate-12">
            <span v-if="isDark" class="text-base text-sky-400">🌙</span>
            <span v-else class="text-base text-amber-500">☀️</span>
        </span>
    </button>
</template>
