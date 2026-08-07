<script setup lang="ts">
import { watch, onUnmounted } from 'vue';

const props = withDefaults(
    defineProps<{
        show?: boolean;
        title?: string;
        maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
    }>(),
    {
        show: false,
        title: '',
        maxWidth: 'md',
    }
);

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const maxWidthClass = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
}[props.maxWidth];

watch(
    () => props.show,
    (val) => {
        if (val) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
);

onUnmounted(() => {
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center"
            >
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
                    @click="emit('close')"
                ></div>

                <!-- Dialog Modal Panel -->
                <div
                    class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-3xl overflow-hidden shadow-2xl transform transition-all sm:w-full z-10 p-6 space-y-4 text-slate-900 dark:text-zinc-100"
                    :class="maxWidthClass"
                >
                    <div v-if="title || $slots.header" class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-zinc-800">
                        <h3 class="text-lg font-bold font-heading text-slate-900 dark:text-white">
                            <slot name="header">{{ title }}</slot>
                        </h3>
                        <button
                            @click="emit('close')"
                            class="text-slate-400 hover:text-slate-700 dark:hover:text-white transition p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-zinc-800"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="text-xs sm:text-sm text-slate-700 dark:text-zinc-200">
                        <slot />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
