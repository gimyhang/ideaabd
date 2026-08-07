<script setup lang="ts">
import { computed } from 'vue';
import Spinner from './Spinner.vue';

const props = withDefaults(
    defineProps<{
        variant?: 'primary' | 'secondary' | 'brand' | 'ghost' | 'destructive' | 'outline';
        size?: 'sm' | 'md' | 'lg';
        type?: 'button' | 'submit' | 'reset';
        loading?: boolean;
        disabled?: boolean;
    }>(),
    {
        variant: 'primary',
        size: 'md',
        type: 'button',
        loading: false,
        disabled: false,
    }
);

const baseClasses = 'inline-flex items-center justify-center font-semibold rounded-xl transition duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-950 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98] select-none';

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm': return 'px-3 py-1.5 text-xs gap-1.5';
        case 'lg': return 'px-6 py-3 text-base gap-2.5';
        case 'md':
        default: return 'px-4 py-2.5 text-sm gap-2';
    }
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'brand':
            return 'bg-sky-500 hover:bg-sky-400 text-slate-950 shadow-md shadow-sky-500/20 focus:ring-sky-400';
        case 'secondary':
            return 'bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 focus:ring-slate-500';
        case 'outline':
            return 'bg-transparent hover:bg-slate-900 text-slate-300 border border-slate-800 focus:ring-slate-600';
        case 'ghost':
            return 'bg-transparent hover:bg-slate-900 text-slate-400 hover:text-slate-200 focus:ring-slate-700';
        case 'destructive':
            return 'bg-rose-600 hover:bg-rose-500 text-white shadow-md shadow-rose-600/20 focus:ring-rose-500';
        case 'primary':
        default:
            return 'bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white shadow-md shadow-sky-500/25 focus:ring-sky-400';
    }
});
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :class="[baseClasses, sizeClasses, variantClasses]"
    >
        <Spinner v-if="loading" :size="size === 'lg' ? 'md' : 'sm'" />
        <slot v-else name="icon" />

        <span><slot /></span>

        <slot name="suffix" />
    </button>
</template>
