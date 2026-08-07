<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue?: boolean | unknown[];
        checked?: boolean;
        value?: string | number | boolean;
        label?: string;
        description?: string;
        disabled?: boolean;
        error?: string;
    }>(),
    {
        modelValue: false,
        disabled: false,
    }
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean | unknown[]): void;
    (e: 'update:checked', value: boolean): void;
}>();

const isChecked = computed(() => {
    if (props.checked !== undefined) {
        return Boolean(props.checked);
    }
    if (Array.isArray(props.modelValue)) {
        return props.modelValue.includes(props.value);
    }
    return Boolean(props.modelValue);
});

function onChange(event: Event) {
    const target = event.target as HTMLInputElement;

    emit('update:checked', target.checked);

    if (Array.isArray(props.modelValue)) {
        const newValue = [...props.modelValue];
        if (target.checked) {
            newValue.push(props.value);
        } else {
            const index = newValue.indexOf(props.value);
            if (index !== -1) newValue.splice(index, 1);
        }
        emit('update:modelValue', newValue);
    } else {
        emit('update:modelValue', target.checked);
    }
}
</script>

<template>
    <div class="space-y-1">
        <label class="inline-flex items-center gap-2.5 cursor-pointer select-none" :class="{ 'opacity-50 cursor-not-allowed': disabled }">
            <input
                type="checkbox"
                :checked="isChecked"
                :disabled="disabled"
                @change="onChange"
                class="w-4 h-4 rounded border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-sky-600 focus:ring-2 focus:ring-sky-500/20 transition cursor-pointer"
            />
            <div>
                <span v-if="label" class="text-xs font-bold text-slate-800 dark:text-slate-200 block">{{ label }}</span>
                <span v-if="description" class="text-[11px] text-slate-500 dark:text-zinc-400 block mt-0.5">{{ description }}</span>
            </div>
        </label>

        <p v-if="error" class="text-xs text-rose-500 font-bold ml-6">{{ error }}</p>
    </div>
</template>
