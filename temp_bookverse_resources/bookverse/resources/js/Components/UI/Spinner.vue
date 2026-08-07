<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        size?: 'xs' | 'sm' | 'md' | 'lg';
        variant?: 'current' | 'sky' | 'white';
    }>(),
    {
        size: 'md',
        variant: 'current',
    }
);

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'xs': return 'w-3 h-3';
        case 'sm': return 'w-4 h-4';
        case 'lg': return 'w-8 h-8';
        case 'md':
        default: return 'w-5 h-5';
    }
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'sky': return 'text-sky-500';
        case 'white': return 'text-white';
        case 'current':
        default: return 'text-current';
    }
});
</script>

<template>
    <svg
        class="animate-spin"
        :class="[sizeClasses, variantClasses]"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
    >
        <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
        ></circle>
        <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        ></path>
    </svg>
</template>
