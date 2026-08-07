<script setup lang="ts">
const props = defineProps<{
    distribution: Record<number, number>;
    total: number;
}>();

function pct(count: number): number {
    if (props.total === 0) return 0;
    return Math.round((count / props.total) * 100);
}
</script>

<template>
    <div class="space-y-1.5">
        <div
            v-for="star in [5, 4, 3, 2, 1]"
            :key="star"
            class="flex items-center gap-3 text-xs"
        >
            <!-- Star label -->
            <div class="flex items-center gap-1 w-10 shrink-0">
                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ star }}</span>
                <svg class="w-3 h-3" viewBox="0 0 24 24">
                    <polygon
                        points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"
                        fill="#F59E0B" stroke="#F59E0B" stroke-width="1" stroke-linejoin="round"
                    />
                </svg>
            </div>

            <!-- Progress bar -->
            <div class="flex-1 bg-slate-100 dark:bg-zinc-800 rounded-full h-2 overflow-hidden">
                <div
                    class="h-full bg-amber-400 rounded-full transition-all duration-700"
                    :style="{ width: pct(distribution[star] ?? 0) + '%' }"
                />
            </div>

            <!-- Percentage -->
            <span class="w-8 text-right text-slate-500 dark:text-slate-400 shrink-0">
                {{ pct(distribution[star] ?? 0) }}%
            </span>

            <!-- Count -->
            <span class="w-8 text-right text-slate-400 dark:text-slate-500 shrink-0">
                ({{ distribution[star] ?? 0 }})
            </span>
        </div>
    </div>
</template>
