<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    startDate: string;
    endDate: string;
}>();

const start = ref(props.startDate);
const end = ref(props.endDate);

watch(() => props.startDate, (val) => start.value = val);
watch(() => props.endDate, (val) => end.value = val);

function applyFilter() {
    router.get(
        window.location.pathname,
        { start_date: start.value, end_date: end.value },
        { preserveState: true, replace: true }
    );
}

function setPreset(days: number) {
    const endObj = new Date();
    const startObj = new Date();
    startObj.setDate(endObj.getDate() - days);

    start.value = startObj.toISOString().split('T')[0];
    end.value = endObj.toISOString().split('T')[0];
    applyFilter();
}

function setThisMonthPreset() {
    const now = new Date();
    const startObj = new Date(now.getFullYear(), now.getMonth(), 1);
    const endObj = new Date(now.getFullYear(), now.getMonth() + 1, 0);

    start.value = startObj.toISOString().split('T')[0];
    end.value = endObj.toISOString().split('T')[0];
    applyFilter();
}

function setLastMonthPreset() {
    const now = new Date();
    const startObj = new Date(now.getFullYear(), now.getMonth() - 1, 1);
    const endObj = new Date(now.getFullYear(), now.getMonth(), 0);

    start.value = startObj.toISOString().split('T')[0];
    end.value = endObj.toISOString().split('T')[0];
    applyFilter();
}

// Active preset detection helper
const activePreset = computed(() => {
    const now = new Date();
    const endToday = now.toISOString().split('T')[0];

    const d7 = new Date();
    d7.setDate(now.getDate() - 6);
    const start7 = d7.toISOString().split('T')[0];

    const d30 = new Date();
    d30.setDate(now.getDate() - 29);
    const start30 = d30.toISOString().split('T')[0];

    const thisMonthStart = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
    const thisMonthEnd = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];

    const lastMonthStart = new Date(now.getFullYear(), now.getMonth() - 1, 1).toISOString().split('T')[0];
    const lastMonthEnd = new Date(now.getFullYear(), now.getMonth(), 0).toISOString().split('T')[0];

    if (start.value === start7 && end.value === endToday) return '7d';
    if (start.value === start30 && end.value === endToday) return '30d';
    if (start.value === thisMonthStart && end.value === thisMonthEnd) return 'this_month';
    if (start.value === lastMonthStart && end.value === lastMonthEnd) return 'last_month';
    return 'custom';
});
</script>

<template>
    <div class="w-full bg-gradient-to-r from-white via-slate-50 to-sky-50/40 dark:from-zinc-900 dark:via-zinc-900/90 dark:to-zinc-950 p-4 sm:p-5 rounded-3xl border border-slate-200/80 dark:border-zinc-800 shadow-sm dark:shadow-md transition-all duration-300 space-y-3 lg:space-y-0 lg:flex lg:items-center lg:justify-between gap-4">
        
        <!-- Filter Label & Presets Group -->
        <div class="flex flex-wrap items-center gap-2.5">
            <div class="flex items-center gap-2 pr-2 text-xs font-black uppercase tracking-wider text-slate-800 dark:text-zinc-200 font-heading">
                <span class="w-6 h-6 rounded-lg bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xs border border-sky-100 dark:border-sky-900/50">🗓️</span>
                <span>Period Filter</span>
            </div>

            <div class="flex flex-wrap items-center gap-1.5 bg-slate-100/80 dark:bg-zinc-800/70 p-1 rounded-2xl border border-slate-200/60 dark:border-zinc-700/60">
                <button
                    type="button"
                    @click="setPreset(6)"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all duration-200 cursor-pointer active:scale-95"
                    :class="activePreset === '7d' 
                        ? 'bg-sky-600 text-white shadow-xs font-black' 
                        : 'text-slate-600 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-zinc-700/60'"
                >
                    ⚡ Past 7 Days
                </button>
                <button
                    type="button"
                    @click="setPreset(29)"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all duration-200 cursor-pointer active:scale-95"
                    :class="activePreset === '30d' 
                        ? 'bg-sky-600 text-white shadow-xs font-black' 
                        : 'text-slate-600 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-zinc-700/60'"
                >
                    📅 Past 30 Days
                </button>
                <button
                    type="button"
                    @click="setThisMonthPreset"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all duration-200 cursor-pointer active:scale-95"
                    :class="activePreset === 'this_month' 
                        ? 'bg-sky-600 text-white shadow-xs font-black' 
                        : 'text-slate-600 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-zinc-700/60'"
                >
                    🗓️ This Month
                </button>
                <button
                    type="button"
                    @click="setLastMonthPreset"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all duration-200 cursor-pointer active:scale-95"
                    :class="activePreset === 'last_month' 
                        ? 'bg-sky-600 text-white shadow-xs font-black' 
                        : 'text-slate-600 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-zinc-700/60'"
                >
                    📊 Last Month
                </button>
            </div>
        </div>

        <!-- Custom Date Range Picker Inputs -->
        <div class="flex items-center gap-2 self-end sm:self-auto">
            <div class="flex items-center gap-2 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 px-3 py-1.5 rounded-2xl shadow-xs focus-within:ring-2 focus-within:ring-sky-500/30 transition">
                <span class="text-xs">📅</span>
                <input
                    type="date"
                    v-model="start"
                    @change="applyFilter"
                    class="bg-transparent border-0 outline-none text-xs font-bold font-mono text-slate-800 dark:text-zinc-200 focus:ring-0 p-0"
                />
            </div>
            <span class="text-xs font-black text-slate-400 dark:text-zinc-500 font-mono">→</span>
            <div class="flex items-center gap-2 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 px-3 py-1.5 rounded-2xl shadow-xs focus-within:ring-2 focus-within:ring-sky-500/30 transition">
                <span class="text-xs">📅</span>
                <input
                    type="date"
                    v-model="end"
                    @change="applyFilter"
                    class="bg-transparent border-0 outline-none text-xs font-bold font-mono text-slate-800 dark:text-zinc-200 focus:ring-0 p-0"
                />
            </div>
        </div>
    </div>
</template>
