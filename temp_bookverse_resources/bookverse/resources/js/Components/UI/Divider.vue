<script setup lang="ts">
withDefaults(
    defineProps<{
        label?: string;
    }>(),
    {
        label: '',
    }
);
</script>

<template>
    <div class="relative my-4 flex items-center justify-center">
        <div class="w-full border-t border-slate-200"></div>
        <span
            v-if="label"
            class="absolute bg-white px-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider"
        >
            {{ label }}
        </span>
    </div>
</template>
