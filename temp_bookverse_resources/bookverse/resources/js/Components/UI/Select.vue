<script setup lang="ts">
import { computed } from 'vue';

export interface SelectOption {
    value: string | number;
    label: string;
}

const props = withDefaults(
    defineProps<{
        modelValue?: string | number;
        options?: SelectOption[];
        label?: string;
        placeholder?: string;
        error?: string;
        hint?: string;
        disabled?: boolean;
        id?: string;
    }>(),
    {
        modelValue: '',
        options: () => [],
        disabled: false,
    }
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
}>();

const inputId = computed(() => props.id || `select-${Math.random().toString(36).substring(2, 9)}`);

function onChange(event: Event) {
    const target = event.target as HTMLSelectElement;
    emit('update:modelValue', target.value);
}
</script>

<template>
    <div class="space-y-1.5 w-full">
        <label v-if="label" :for="inputId" class="block text-xs font-bold text-slate-800 dark:text-slate-200">
            {{ label }}
        </label>

        <div class="relative">
            <select
                :id="inputId"
                :value="modelValue"
                :disabled="disabled"
                @change="onChange"
                class="w-full appearance-none text-xs font-semibold bg-white dark:bg-zinc-950 text-slate-900 dark:text-slate-100 rounded-xl border border-slate-300 dark:border-zinc-800 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 disabled:bg-slate-100 dark:disabled:bg-zinc-800 disabled:opacity-60 transition duration-150 pl-3.5 pr-9 py-2.5 cursor-pointer shadow-sm"
                :class="[error ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20' : '']"
            >
                <option value="" disabled selected hidden v-if="placeholder">
                    {{ placeholder }}
                </option>

                <!-- Slot options if provided -->
                <slot>
                    <option
                        v-for="opt in options"
                        :key="opt.value"
                        :value="opt.value"
                        class="bg-white dark:bg-zinc-900 text-slate-900 dark:text-slate-100 font-medium"
                    >
                        {{ opt.label }}
                    </option>
                </slot>
            </select>

            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </div>

        <p v-if="error" class="text-xs text-rose-500 font-bold">{{ error }}</p>
        <p v-else-if="hint" class="text-xs text-slate-400">{{ hint }}</p>
    </div>
</template>
