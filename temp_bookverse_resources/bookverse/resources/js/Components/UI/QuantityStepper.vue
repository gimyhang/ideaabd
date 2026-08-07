<script setup lang="ts">
import { ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: number;
        min?: number;
        max?: number;
        step?: number;
        disabled?: boolean;
        loading?: boolean;
    }>(),
    {
        min: 1,
        max: 99,
        step: 1,
        disabled: false,
        loading: false,
    }
);

const emit = defineEmits<{
    (e: 'update:modelValue', val: number): void;
    (e: 'change', val: number): void;
}>();

// Local optimistic count state
const displayValue = ref(props.modelValue);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

watch(() => props.modelValue, (newVal) => {
    displayValue.value = newVal;
});

function update(newVal: number) {
    if (props.disabled || props.loading) return;

    let clamped = Math.max(props.min, Math.min(props.max, newVal));
    displayValue.value = clamped;

    if (debounceTimer) clearTimeout(debounceTimer);

    // 200ms debounce to prevent multi-click HTTP spamming
    debounceTimer = setTimeout(() => {
        emit('update:modelValue', clamped);
        emit('change', clamped);
    }, 200);
}

function decrement() {
    update(displayValue.value - props.step);
}

function increment() {
    update(displayValue.value + props.step);
}

function onInput(e: Event) {
    const target = e.target as HTMLInputElement;
    const val = parseInt(target.value, 10);
    if (!isNaN(val)) {
        update(val);
    }
}
</script>

<template>
    <div class="inline-flex items-center rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-0.5 shadow-sm">
        <button
            type="button"
            @click="decrement"
            :disabled="disabled || loading || displayValue <= min"
            class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 disabled:opacity-40 disabled:cursor-not-allowed transition font-bold text-sm select-none"
        >
            -
        </button>

        <input
            type="number"
            :value="displayValue"
            @input="onInput"
            :min="min"
            :max="max"
            :disabled="disabled || loading"
            class="w-10 text-center text-xs font-bold font-mono text-slate-900 dark:text-white bg-transparent border-0 focus:ring-0 p-0 select-all"
        />

        <button
            type="button"
            @click="increment"
            :disabled="disabled || loading || displayValue >= max"
            class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 disabled:opacity-40 disabled:cursor-not-allowed transition font-bold text-sm select-none"
        >
            +
        </button>
    </div>
</template>

<style scoped>
/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}
</style>
