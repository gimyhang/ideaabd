<script setup lang="ts">
import { computed, watch, onMounted } from 'vue';

const props = withDefaults(
    defineProps<{
        show: boolean;
        title?: string;
        message: string;
        type?: 'success' | 'error' | 'warning' | 'info';
        duration?: number;
    }>(),
    {
        type: 'success',
        duration: 4500,
    }
);

const emit = defineEmits<{
    (e: 'close'): void;
}>();

let timer: ReturnType<typeof setTimeout> | null = null;

function startTimer() {
    if (props.duration > 0) {
        timer = setTimeout(() => {
            emit('close');
        }, props.duration);
    }
}

function clearTimer() {
    if (timer) {
        clearTimeout(timer);
        timer = null;
    }
}

watch(
    () => props.show,
    (val) => {
        if (val) startTimer();
        else clearTimer();
    }
);

onMounted(() => {
    if (props.show) startTimer();
});

const toastStyleClasses = computed(() => {
    switch (props.type) {
        case 'success':
            return 'bg-emerald-950/95 border-emerald-500/40 text-emerald-100 shadow-emerald-900/30 shadow-xl';
        case 'error':
            return 'bg-rose-950/95 border-rose-500/40 text-rose-100 shadow-rose-900/30 shadow-xl';
        case 'warning':
            return 'bg-amber-950/95 border-amber-500/40 text-amber-100 shadow-amber-900/30 shadow-xl';
        case 'info':
        default:
            return 'bg-slate-950/95 border-sky-500/40 text-sky-100 shadow-sky-900/30 shadow-xl';
    }
});

const badgeBgClasses = computed(() => {
    switch (props.type) {
        case 'success': return 'bg-emerald-500 text-white shadow-emerald-500/50 shadow-md';
        case 'error': return 'bg-rose-500 text-white shadow-rose-500/50 shadow-md';
        case 'warning': return 'bg-amber-500 text-white shadow-amber-500/50 shadow-md';
        case 'info':
        default: return 'bg-sky-500 text-white shadow-sky-500/50 shadow-md';
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="ease-out duration-300"
            enter-from-class="opacity-0 -translate-y-4 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 -translate-y-4 scale-95"
        >
            <div
                v-if="show"
                class="fixed top-5 right-5 z-50 max-w-sm w-full backdrop-blur-xl border rounded-2xl p-4 flex items-start gap-3 select-none transition-all"
                :class="toastStyleClasses"
            >
                <!-- Icon Badge -->
                <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5" :class="badgeBgClasses">
                    <!-- Success Icon -->
                    <svg v-if="type === 'success'" class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <!-- Error Icon -->
                    <svg v-else-if="type === 'error'" class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <!-- Warning / Info Icon -->
                    <svg v-else class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <!-- Text Content -->
                <div class="flex-1 space-y-0.5 min-w-0">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400 font-heading" v-if="title || type === 'success'">
                        {{ title || (type === 'success' ? 'Success' : 'Notice') }}
                    </h4>
                    <p class="text-xs font-medium text-slate-100 leading-snug">
                        {{ message }}
                    </p>
                </div>

                <!-- Close Button -->
                <button
                    @click="$emit('close')"
                    class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors flex-shrink-0"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </Transition>
    </Teleport>
</template>
