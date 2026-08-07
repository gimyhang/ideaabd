<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        variant?: 'text' | 'avatar' | 'card' | 'image' | 'custom';
        width?: string;
        height?: string;
    }>(),
    {
        variant: 'text',
    }
);

const classes = computed(() => {
    switch (props.variant) {
        case 'avatar':
            return 'w-10 h-10 rounded-full';
        case 'card':
            return 'w-full h-48 rounded-2xl';
        case 'image':
            return 'w-full h-32 rounded-xl';
        case 'custom':
            return 'rounded-lg';
        case 'text':
        default:
            return 'w-full h-4 rounded-md';
    }
});
</script>

<template>
    <div
        class="animate-pulse bg-slate-800/60"
        :class="classes"
        :style="{ width, height }"
    ></div>
</template>
