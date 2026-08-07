<script setup lang="ts">
import { computed, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        src?: string;
        alt?: string;
        name?: string;
        size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl';
        status?: 'online' | 'offline' | 'away';
    }>(),
    {
        size: 'md',
        alt: 'Avatar',
    }
);
const hasError = ref(false);

watch(() => props.src, () => {
    hasError.value = false;
});
const sizeClasses = computed(() => {
    switch (props.size) {
        case 'xs': return 'w-6 h-6 text-[10px]';
        case 'sm': return 'w-8 h-8 text-xs';
        case 'lg': return 'w-12 h-12 text-base';
        case 'xl': return 'w-16 h-16 text-xl';
        case 'md':
        default: return 'w-10 h-10 text-sm';
    }
});

const initials = computed(() => {
    if (!props.name) return 'U';
    const parts = props.name.trim().split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }
    return props.name.substring(0, 2).toUpperCase();
});

const statusClasses = computed(() => {
    switch (props.status) {
        case 'online': return 'bg-emerald-500';
        case 'away': return 'bg-amber-500';
        case 'offline': return 'bg-slate-500';
        default: return '';
    }
});
</script>

<template>
    <div class="relative inline-block">
        <div
            class="rounded-full overflow-hidden flex items-center justify-center font-bold bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 text-sky-400 select-none shadow-md"
            :class="sizeClasses"
        >
            <img v-if="src && !hasError" :src="src" :alt="alt" class="w-full h-full object-cover" @error="hasError = true" />
            <span v-else>{{ initials }}</span>
        </div>

        <span
            v-if="status"
            class="absolute bottom-0 right-0 rounded-full ring-2 ring-slate-950"
            :class="[
                statusClasses,
                size === 'xs' || size === 'sm' ? 'w-2 h-2' : 'w-3 h-3'
            ]"
        ></span>
    </div>
</template>
