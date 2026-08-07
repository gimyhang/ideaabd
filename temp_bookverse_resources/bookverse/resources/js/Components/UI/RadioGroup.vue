<script setup lang="ts">
export interface RadioOption {
    value: string | number;
    label: string;
    description?: string;
}

const props = withDefaults(
    defineProps<{
        modelValue: string | number;
        options: RadioOption[];
        label?: string;
        name?: string;
        error?: string;
        disabled?: boolean;
    }>(),
    {
        name: () => `radio-group-${Math.random().toString(36).substring(2, 9)}`,
        disabled: false,
    }
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
}>();

function onSelect(val: string | number) {
    if (!props.disabled) {
        emit('update:modelValue', val);
    }
}
</script>

<template>
    <div class="space-y-2 w-full">
        <label v-if="label" class="block text-xs font-semibold text-slate-300">
            {{ label }}
        </label>

        <div class="space-y-2">
            <label
                v-for="opt in options"
                :key="opt.value"
                @click="onSelect(opt.value)"
                class="flex items-start gap-3 p-3 rounded-xl border transition cursor-pointer select-none"
                :class="[
                    modelValue === opt.value
                        ? 'bg-sky-500/10 border-sky-500/50 text-white'
                        : 'bg-slate-950/60 border-slate-800 text-slate-300 hover:border-slate-700',
                    disabled ? 'opacity-50 cursor-not-allowed' : ''
                ]"
            >
                <input
                    type="radio"
                    :name="name"
                    :value="opt.value"
                    :checked="modelValue === opt.value"
                    :disabled="disabled"
                    class="w-4 h-4 mt-0.5 text-sky-500 border-slate-700 bg-slate-900 focus:ring-sky-500/20"
                />
                <div>
                    <span class="text-sm font-semibold block">{{ opt.label }}</span>
                    <span v-if="opt.description" class="text-xs text-slate-400 block mt-0.5">{{ opt.description }}</span>
                </div>
            </label>
        </div>

        <p v-if="error" class="text-xs text-rose-400 font-medium">{{ error }}</p>
    </div>
</template>
