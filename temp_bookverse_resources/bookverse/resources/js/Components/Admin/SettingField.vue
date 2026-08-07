<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        label: string;
        helpText?: string;
        type?: 'text' | 'textarea' | 'select' | 'switch' | 'file' | 'color' | 'range';
        options?: Array<{ value: any; label: string }>;
        min?: number;
        max?: number;
        step?: number;
        modelValue: any;
    }>(),
    { type: 'text', min: 0, max: 100, step: 1 }
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: any): void;
    (e: 'change-file', file: File): void;
    (e: 'remove-file'): void;
}>();

const value = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
});

function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        emit('change-file', target.files[0]);
    }
}

function handleRemoveFile() {
    emit('update:modelValue', '');
    emit('remove-file');
}
</script>

<template>
    <div class="space-y-2.5">
        <div class="flex items-center justify-between gap-3">
            <label class="block text-base sm:text-lg font-black text-slate-900 dark:text-zinc-100 tracking-wide font-sans">
                {{ label }}
            </label>

            <!-- Switch/Toggle (Right aligned if type === switch) -->
            <button
                v-if="type === 'switch'"
                type="button"
                @click="value = !value"
                class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                :class="value ? 'bg-sky-600' : 'bg-slate-300 dark:bg-zinc-700'"
            >
                <span
                    class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                    :class="value ? 'translate-x-5' : 'translate-x-0'"
                />
            </button>
        </div>

        <p v-if="helpText" class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 font-medium leading-relaxed">
            {{ helpText }}
        </p>

        <!-- Text Input -->
        <input
            v-if="type === 'text'"
            type="text"
            v-model="value"
            class="w-full text-base font-bold px-4 py-3.5 bg-slate-50 dark:bg-zinc-950 border border-slate-300 dark:border-zinc-700/80 rounded-2xl text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-sky-500/40 focus:border-sky-500 dark:focus:border-sky-400 transition shadow-xs"
        />

        <!-- Pure Native Color Picker Only -->
        <div v-else-if="type === 'color'" class="flex items-center">
            <label class="w-full flex items-center gap-4 bg-slate-50 dark:bg-zinc-950 p-3 rounded-2xl border border-slate-300 dark:border-zinc-700/80 cursor-pointer shadow-xs hover:border-sky-500 transition group">
                <input
                    type="color"
                    v-model="value"
                    class="w-12 h-12 rounded-xl cursor-pointer border-0 bg-transparent p-0 shrink-0 shadow-xs ring-2 ring-slate-200 dark:ring-zinc-800"
                />
                <div class="flex flex-col">
                    <span class="text-sm font-black text-slate-800 dark:text-zinc-100 font-sans tracking-wide">
                        Choose Color (Color Dialog)
                    </span>
                    <span class="text-xs font-mono font-bold text-sky-600 dark:text-sky-400 uppercase">
                        Selected Hex: {{ value || '#0f172a' }}
                    </span>
                </div>
            </label>
        </div>

        <!-- Range Slider Input -->
        <div v-else-if="type === 'range'" class="flex items-center gap-4 bg-slate-50 dark:bg-zinc-950 p-3.5 rounded-2xl border border-slate-300 dark:border-zinc-700/80">
            <input
                type="range"
                :min="min"
                :max="max"
                :step="step"
                v-model="value"
                class="w-full accent-sky-500 cursor-pointer h-2.5 bg-slate-200 dark:bg-zinc-800 rounded-lg"
            />
            <span class="text-sm font-mono font-black text-slate-800 dark:text-white bg-slate-200 dark:bg-zinc-800 px-3.5 py-1.5 rounded-xl shrink-0">
                {{ value }}%
            </span>
        </div>

        <!-- Textarea -->
        <textarea
            v-else-if="type === 'textarea'"
            v-model="value"
            rows="3"
            class="w-full text-base font-bold px-4 py-3.5 bg-slate-50 dark:bg-zinc-950 border border-slate-300 dark:border-zinc-700/80 rounded-2xl text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-sky-500/40 focus:border-sky-500 dark:focus:border-sky-400 transition shadow-xs leading-relaxed"
        ></textarea>

        <!-- Select -->
        <select
            v-else-if="type === 'select'"
            v-model="value"
            class="w-full text-base font-bold px-4 py-3.5 bg-slate-50 dark:bg-zinc-950 border border-slate-300 dark:border-zinc-700/80 rounded-2xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-sky-500/40 focus:border-sky-500 dark:focus:border-sky-400 transition shadow-xs cursor-pointer"
        >
            <option v-for="opt in options" :key="opt.value" :value="opt.value" class="bg-white dark:bg-zinc-900 text-slate-900 dark:text-white">
                {{ opt.label }}
            </option>
        </select>

        <!-- File upload with Remove Option -->
        <div v-else-if="type === 'file'" class="flex flex-wrap items-center gap-3.5">
            <img
                v-if="modelValue && typeof modelValue === 'string' && modelValue.trim() !== ''"
                :src="modelValue"
                class="w-16 h-16 object-contain rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-950 p-1 shrink-0 shadow-xs"
                alt="Preview"
            />
            <label class="px-6 py-3 bg-sky-600 hover:bg-sky-700 text-white font-extrabold text-xs sm:text-sm rounded-2xl cursor-pointer transition uppercase font-sans tracking-wider shadow-xs active:scale-95">
                Upload File
                <input type="file" @change="handleFileChange" class="hidden" accept="image/*" />
            </label>

            <!-- Remove Image Button -->
            <button
                v-if="modelValue && typeof modelValue === 'string' && modelValue.trim() !== ''"
                type="button"
                @click="handleRemoveFile"
                class="px-5 py-3 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 font-extrabold text-xs sm:text-sm rounded-2xl cursor-pointer transition uppercase font-sans tracking-wider border border-rose-200 dark:border-rose-900/50 active:scale-95 flex items-center gap-1.5 shadow-xs"
            >
                <span>🗑️</span>
                <span>Remove Image</span>
            </button>
        </div>
    </div>
</template>
