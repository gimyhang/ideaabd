<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        password?: string;
    }>(),
    {
        password: '',
    }
);

const strengthScore = computed(() => {
    const val = props.password;
    if (!val) return 0;
    let score = 0;
    if (val.length >= 8) score += 1;
    if (val.length >= 12) score += 1;
    if (/[A-Z]/.test(val)) score += 1;
    if (/[0-9]/.test(val)) score += 1;
    if (/[^A-Za-z0-9]/.test(val)) score += 1;
    return score;
});

const label = computed(() => {
    switch (strengthScore.value) {
        case 0: return 'Too Short';
        case 1:
        case 2: return 'Weak';
        case 3: return 'Fair';
        case 4: return 'Good';
        case 5:
        default: return 'Strong & Secure';
    }
});

const colorClasses = computed(() => {
    switch (strengthScore.value) {
        case 0: return 'bg-slate-300 text-slate-500';
        case 1:
        case 2: return 'bg-rose-500 text-rose-600';
        case 3: return 'bg-amber-500 text-amber-600';
        case 4: return 'bg-sky-500 text-sky-600';
        case 5:
        default: return 'bg-emerald-500 text-emerald-600';
    }
});

const barWidth = computed(() => {
    return `${(strengthScore.value / 5) * 100}%`;
});
</script>

<template>
    <div v-if="password" class="space-y-1.5 pt-1">
        <div class="flex items-center justify-between text-[11px] font-semibold">
            <span class="text-slate-500">Password Strength:</span>
            <span :class="colorClasses.split(' ')[1]">{{ label }}</span>
        </div>

        <!-- Progress Track -->
        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
            <div
                class="h-full transition-all duration-300 rounded-full"
                :class="colorClasses.split(' ')[0]"
                :style="{ width: barWidth }"
            ></div>
        </div>
    </div>
</template>
