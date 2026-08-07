<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        variant?: 'default' | 'brand' | 'success' | 'warning' | 'error' | 'info';
        size?: 'sm' | 'md';
        dot?: boolean;
    }>(),
    {
        variant: 'default',
        size: 'md',
        dot: false,
    }
);

const sizeClasses = computed(() => {
    return props.size === 'sm' ? 'px-2 py-0.5 text-[10px]' : 'px-2.5 py-1 text-xs';
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'brand':
            return 'bg-sky-50 text-sky-700 border-sky-200';
        case 'success':
            return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        case 'warning':
            return 'bg-amber-50 text-amber-800 border-amber-200';
        case 'error':
            return 'bg-rose-50 text-rose-700 border-rose-200';
        case 'info':
            return 'bg-indigo-50 text-indigo-700 border-indigo-200';
        case 'default':
        default:
            return 'bg-slate-100 text-slate-700 border-slate-200';
    }
});

const dotClasses = computed(() => {
    switch (props.variant) {
        case 'brand': return 'bg-sky-600';
        case 'success': return 'bg-emerald-600';
        case 'warning': return 'bg-amber-600';
        case 'error': return 'bg-rose-600';
        case 'info': return 'bg-indigo-600';
        case 'default':
        default: return 'bg-slate-500';
    }
});
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 font-semibold rounded-md border"
        :class="[sizeClasses, variantClasses]"
    >
        <span v-if="dot" class="w-1.5 h-1.5 rounded-full" :class="dotClasses"></span>
        <slot />
    </span>
</template>
