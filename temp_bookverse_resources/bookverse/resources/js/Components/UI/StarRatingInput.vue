<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
    modelValue: number;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: number];
}>();

const hovered = ref(0);

function setRating(value: number) {
    emit('update:modelValue', value);
}

const labels: Record<number, string> = {
    1: 'Poor',
    2: 'Fair',
    3: 'Good',
    4: 'Very Good',
    5: 'Excellent',
};
</script>

<template>
    <div class="space-y-1">
        <div class="flex items-center gap-1" role="radiogroup" aria-label="Star rating">
            <button
                v-for="i in 5"
                :key="i"
                type="button"
                :aria-label="`${i} stars - ${labels[i]}`"
                :aria-pressed="modelValue === i"
                class="focus:outline-none transition-transform hover:scale-110 active:scale-95"
                @mouseenter="hovered = i"
                @mouseleave="hovered = 0"
                @click="setRating(i)"
                @keydown.enter="setRating(i)"
                @keydown.space.prevent="setRating(i)"
            >
                <svg class="w-8 h-8 transition-colors" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <polygon
                        points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"
                        :fill="(hovered || modelValue) >= i ? '#F59E0B' : '#E5E7EB'"
                        :stroke="(hovered || modelValue) >= i ? '#D97706' : '#D1D5DB'"
                        stroke-width="1.5"
                        stroke-linejoin="round"
                    />
                </svg>
            </button>
        </div>
        <p v-if="hovered || modelValue" class="text-xs font-medium text-amber-600 dark:text-amber-400 h-4">
            {{ labels[hovered || modelValue] }}
        </p>
        <p v-else class="text-xs text-slate-400 h-4">Click to rate</p>
    </div>
</template>
