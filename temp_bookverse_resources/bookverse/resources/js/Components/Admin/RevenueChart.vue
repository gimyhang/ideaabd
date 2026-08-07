<script setup lang="ts">
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';

interface RevenueTrend {
    labels: string[];
    data: number[];
    period: string;
}

const props = defineProps<{
    trend: RevenueTrend;
    activePeriod: string;
}>();

const hoveredIndex = ref<number | null>(null);

const maxVal = computed(() => {
    const max = Math.max(...props.trend.data, 100);
    return Math.ceil(max * 1.15);
});

const points = computed(() => {
    const data = props.trend.data;
    const count = data.length;
    if (count === 0) return [];

    const width = 600;
    const height = 200;
    const step = count > 1 ? width / (count - 1) : width;

    return data.map((val, i) => {
        const x = i * step;
        const y = height - (val / maxVal.value) * height;
        return { x, y, value: val, label: props.trend.labels[i] };
    });
});

const pathD = computed(() => {
    if (points.value.length === 0) return '';
    return points.value.reduce((acc, point, i) => {
        return i === 0 ? `M ${point.x},${point.y}` : `${acc} L ${point.x},${point.y}`;
    }, '');
});

const areaD = computed(() => {
    if (points.value.length === 0) return '';
    const lastX = points.value[points.value.length - 1].x;
    return `${pathD.value} L ${lastX},200 L 0,200 Z`;
});

function switchPeriod(period: string) {
    router.get('/admin', { period }, { preserveState: true, preserveScroll: true });
}
</script>

<template>
    <div class="p-6 rounded-3xl bg-white dark:bg-zinc-950 border border-slate-200/80 dark:border-zinc-800 shadow-sm space-y-4">
        <!-- Header Controls -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-zinc-900 pb-4">
            <div>
                <h3 class="font-extrabold text-sm text-slate-900 dark:text-white font-heading flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xs border border-sky-100 dark:border-sky-900/50">📈</span>
                    Revenue Performance Analytics
                </h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400 font-mono">
                    Real-time BDT sales trendline and transaction performance
                </p>
            </div>

            <!-- Period Switcher -->
            <div class="flex items-center bg-slate-100/80 dark:bg-zinc-900 p-1 rounded-2xl border border-slate-200/80 dark:border-zinc-800">
                <button
                    v-for="p in ['weekly', 'monthly', 'yearly']"
                    :key="p"
                    @click="switchPeriod(p)"
                    class="px-3 py-1 text-xs font-extrabold rounded-xl capitalize transition cursor-pointer"
                    :class="activePeriod === p ? 'bg-white dark:bg-zinc-800 text-sky-600 dark:text-sky-400 shadow-xs border border-slate-200/60 dark:border-zinc-700' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white'"
                >
                    {{ p }}
                </button>
            </div>
        </div>

        <!-- SVG Trendline Chart Canvas -->
        <div class="relative pt-4 pb-2">
            <svg class="w-full h-48 overflow-visible" viewBox="0 0 600 200" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="revenueGradient" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#0284c7" stop-opacity="0.25" />
                        <stop offset="100%" stop-color="#38bdf8" stop-opacity="0.0" />
                    </linearGradient>
                </defs>

                <!-- Gridlines -->
                <line x1="0" y1="50" x2="600" y2="50" class="stroke-slate-100 dark:stroke-zinc-900" stroke-dasharray="4 4" />
                <line x1="0" y1="100" x2="600" y2="100" class="stroke-slate-100 dark:stroke-zinc-900" stroke-dasharray="4 4" />
                <line x1="0" y1="150" x2="600" y2="150" class="stroke-slate-100 dark:stroke-zinc-900" stroke-dasharray="4 4" />

                <!-- Gradient Area Fill -->
                <path :d="areaD" fill="url(#revenueGradient)" />

                <!-- Stroke Line -->
                <path :d="pathD" fill="none" class="stroke-sky-600 dark:stroke-sky-400" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />

                <!-- Interactive Data Points -->
                <g v-for="(pt, idx) in points" :key="idx">
                    <circle
                        :cx="pt.x"
                        :cy="pt.y"
                        r="5"
                        class="fill-white dark:fill-zinc-950 stroke-sky-600 dark:stroke-sky-400 hover:r-7 transition cursor-pointer"
                        stroke-width="3"
                        @mouseenter="hoveredIndex = idx"
                        @mouseleave="hoveredIndex = null"
                    />
                </g>
            </svg>

            <!-- Tooltip Overlay -->
            <div
                v-if="hoveredIndex !== null && points[hoveredIndex]"
                class="absolute -top-2 transform -translate-x-1/2 bg-slate-900 dark:bg-zinc-800 text-white text-[11px] font-bold px-2.5 py-1 rounded-xl shadow-md border border-slate-700 dark:border-zinc-700 pointer-events-none font-mono"
                :style="{ left: `${(hoveredIndex / (points.length - 1)) * 100}%` }"
            >
                {{ points[hoveredIndex].label }}: ৳{{ points[hoveredIndex].value.toLocaleString() }}
            </div>
        </div>

        <!-- X-Axis Labels -->
        <div class="flex justify-between items-center text-[10px] text-slate-400 dark:text-zinc-500 font-mono border-t border-slate-100 dark:border-zinc-900 pt-2 px-1">
            <span v-for="(lbl, idx) in trend.labels" :key="idx" class="truncate max-w-[60px] text-center font-bold">
                {{ lbl }}
            </span>
        </div>
    </div>
</template>
