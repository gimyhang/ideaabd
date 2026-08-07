<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    title: string;
    value: string | number;
    icon?: string;
    trend?: number | null;
    subtitle?: string;
    variant?: 'sky' | 'rose' | 'amber' | 'emerald' | 'purple' | 'indigo';
}>();

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'rose':
            return {
                cardBg: 'bg-white dark:bg-zinc-900 border-slate-200/80 dark:border-zinc-800 hover:border-rose-300 dark:hover:border-rose-800/60 shadow-xs hover:shadow-md',
                iconBg: 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/40',
                titleColor: 'text-slate-500 dark:text-zinc-400',
                valueColor: 'text-slate-900 dark:text-zinc-100',
            };
        case 'amber':
            return {
                cardBg: 'bg-white dark:bg-zinc-900 border-slate-200/80 dark:border-zinc-800 hover:border-amber-300 dark:hover:border-amber-800/60 shadow-xs hover:shadow-md',
                iconBg: 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900/40',
                titleColor: 'text-slate-500 dark:text-zinc-400',
                valueColor: 'text-slate-900 dark:text-zinc-100',
            };
        case 'emerald':
            return {
                cardBg: 'bg-white dark:bg-zinc-900 border-slate-200/80 dark:border-zinc-800 hover:border-emerald-300 dark:hover:border-emerald-800/60 shadow-xs hover:shadow-md',
                iconBg: 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40',
                titleColor: 'text-slate-500 dark:text-zinc-400',
                valueColor: 'text-slate-900 dark:text-zinc-100',
            };
        case 'purple':
            return {
                cardBg: 'bg-white dark:bg-zinc-900 border-slate-200/80 dark:border-zinc-800 hover:border-purple-300 dark:hover:border-purple-800/60 shadow-xs hover:shadow-md',
                iconBg: 'bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-900/40',
                titleColor: 'text-slate-500 dark:text-zinc-400',
                valueColor: 'text-slate-900 dark:text-zinc-100',
            };
        case 'indigo':
            return {
                cardBg: 'bg-white dark:bg-zinc-900 border-slate-200/80 dark:border-zinc-800 hover:border-indigo-300 dark:hover:border-indigo-800/60 shadow-xs hover:shadow-md',
                iconBg: 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/40',
                titleColor: 'text-slate-500 dark:text-zinc-400',
                valueColor: 'text-slate-900 dark:text-zinc-100',
            };
        default:
            return {
                cardBg: 'bg-white dark:bg-zinc-900 border-slate-200/80 dark:border-zinc-800 hover:border-sky-300 dark:hover:border-sky-800/60 shadow-xs hover:shadow-md',
                iconBg: 'bg-sky-50 dark:bg-sky-950/50 text-sky-600 dark:text-sky-400 border border-sky-100 dark:border-sky-900/40',
                titleColor: 'text-slate-500 dark:text-zinc-400',
                valueColor: 'text-slate-900 dark:text-zinc-100',
            };
    }
});
</script>

<template>
    <div
        class="p-5 rounded-3xl border transition-all duration-300 flex flex-col justify-between space-y-3"
        :class="variantClasses.cardBg"
    >
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider font-mono" :class="variantClasses.titleColor">
                {{ title }}
            </span>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-base font-bold" :class="variantClasses.iconBg">
                {{ icon || '📊' }}
            </div>
        </div>

        <div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-black font-heading tracking-tight" :class="variantClasses.valueColor">
                    {{ value }}
                </span>

                <span
                    v-if="trend !== undefined && trend !== null"
                    class="px-2 py-0.5 rounded-full text-[10px] font-extrabold font-mono flex items-center gap-0.5"
                    :class="trend >= 0 ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/40' : 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/40'"
                >
                    {{ trend >= 0 ? '▲ +' : '▼ ' }}{{ trend }}%
                </span>
            </div>

            <p v-if="subtitle" class="text-[11px] text-slate-400 dark:text-zinc-500 font-medium mt-1">
                {{ subtitle }}
            </p>
        </div>
    </div>
</template>
