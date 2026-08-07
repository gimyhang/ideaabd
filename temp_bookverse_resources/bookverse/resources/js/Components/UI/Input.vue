<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue?: string | number;
        type?: string;
        label?: string;
        placeholder?: string;
        error?: string;
        disabled?: boolean;
        required?: boolean;
    }>(),
    {
        modelValue: '',
        type: 'text',
        label: '',
        placeholder: '',
        error: '',
        disabled: false,
        required: false,
    }
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const inputClasses = computed(() => {
    return [
        'w-full px-3.5 py-2.5 rounded-xl text-xs font-semibold transition duration-200 outline-none border shadow-sm',
        'bg-white dark:bg-zinc-900 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-zinc-500',
        props.error
            ? 'border-rose-500 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20'
            : 'border-slate-300 dark:border-zinc-800 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20',
        props.disabled ? 'opacity-60 cursor-not-allowed bg-slate-100 dark:bg-zinc-800' : '',
    ].join(' ');
});
</script>

<template>
    <div class="space-y-1.5 w-full">
        <label v-if="label" class="block text-xs font-bold text-slate-800 dark:text-slate-200">
            {{ label }}
            <span v-if="required" class="text-rose-500 ml-0.5">*</span>
        </label>

        <input
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :required="required"
            :class="inputClasses"
            @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />

        <p v-if="error" class="text-[11px] font-bold text-rose-500 pt-0.5">
            {{ error }}
        </p>
    </div>
</template>
