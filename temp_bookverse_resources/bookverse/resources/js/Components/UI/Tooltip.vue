<script setup lang="ts">
import { ref, computed } from 'vue';

const props = withDefaults(
    defineProps<{
        content: string;
        position?: 'top' | 'bottom' | 'left' | 'right';
    }>(),
    {
        position: 'top',
    }
);

const isVisible = ref(false);

const positionClasses = computed(() => {
    switch (props.position) {
        case 'bottom':
            return 'top-full mt-2 left-1/2 -translate-x-1/2';
        case 'left':
            return 'right-full mr-2 top-1/2 -translate-y-1/2';
        case 'right':
            return 'left-full ml-2 top-1/2 -translate-y-1/2';
        case 'top':
        default:
            return 'bottom-full mb-2 left-1/2 -translate-x-1/2';
    }
});
</script>

<template>
    <div
        class="relative inline-block"
        @mouseenter="isVisible = true"
        @mouseleave="isVisible = false"
        @focus="isVisible = true"
        @blur="isVisible = false"
    >
        <slot />

        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="isVisible"
                class="absolute z-50 px-2.5 py-1 text-xs font-semibold text-slate-100 bg-slate-900 border border-slate-700 rounded-lg shadow-xl whitespace-nowrap pointer-events-none"
                :class="positionClasses"
            >
                {{ content }}
            </div>
        </Transition>
    </div>
</template>
