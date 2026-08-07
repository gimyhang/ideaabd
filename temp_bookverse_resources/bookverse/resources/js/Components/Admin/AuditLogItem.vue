<script setup lang="ts">
import { ref, computed } from 'vue';

interface LogItem {
    id: number;
    event: string;
    ip_address?: string;
    user_agent?: string;
    old_values?: Record<string, any> | null;
    new_values?: Record<string, any> | null;
    created_at: string;
    user?: {
        name: string;
        email: string;
    };
}

const props = defineProps<{
    log: LogItem;
}>();

const isExpanded = ref(false);

const diffs = computed(() => {
    const oldVals = props.log.old_values || {};
    const newVals = props.log.new_values || {};
    
    const allKeys = Array.from(new Set([...Object.keys(oldVals), ...Object.keys(newVals)]));

    const result = allKeys.map(key => {
        const oldValue = oldVals[key];
        const newValue = newVals[key];
        
        // Skip if values are equal to reduce clutter
        if (JSON.stringify(oldValue) === JSON.stringify(newValue)) {
            return null;
        }

        return {
            key: key.replace(/_/g, ' '),
            old: formatValue(oldValue),
            new: formatValue(newValue),
        };
    });

    return result.filter((item): item is { key: string; old: string; new: string } => item !== null);
});

function formatValue(val: any): string {
    if (val === null || val === undefined) return 'N/A';
    if (typeof val === 'boolean') return val ? 'Enabled' : 'Disabled';
    if (typeof val === 'object') return JSON.stringify(val);
    return String(val);
}

function getEventBadgeClass(event: string): string {
    if (event.includes('create') || event.includes('store')) {
        return 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20';
    }
    if (event.includes('delete') || event.includes('destroy') || event.includes('reject')) {
        return 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20';
    }
    return 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-500/10 dark:text-sky-400 dark:border-sky-500/20';
}
</script>

<template>
    <div class="border border-slate-200/80 dark:border-zinc-800/80 rounded-3xl bg-white dark:bg-zinc-950/70 shadow-2xs overflow-hidden transition-all duration-300">
        <!-- Summary Line -->
        <div
            @click="isExpanded = !isExpanded"
            class="flex flex-col md:flex-row md:items-center justify-between gap-4 px-6 py-5 cursor-pointer hover:bg-slate-50/50 dark:hover:bg-zinc-900/30 transition duration-150 select-none"
        >
            <div class="flex items-center gap-3">
                <!-- Event Tag -->
                <span
                    class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider font-mono border"
                    :class="getEventBadgeClass(log.event)"
                >
                    {{ log.event }}
                </span>

                <div>
                    <span class="font-black text-slate-800 dark:text-zinc-100 text-sm block">
                        {{ log.user?.name ?? 'System Action' }}
                    </span>
                    <span class="text-[10px] text-slate-400 dark:text-zinc-500 font-mono">
                        {{ log.user?.email ?? 'N/A' }} | IP: {{ log.ip_address ?? '127.0.0.1' }}
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-between md:justify-end gap-4">
                <span class="text-xs font-semibold text-slate-500 dark:text-zinc-400 font-mono">
                    {{ log.created_at }}
                </span>
                
                <svg
                    class="w-4 h-4 text-slate-400 dark:text-zinc-500 transition-transform duration-200"
                    :class="isExpanded ? 'rotate-180' : ''"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>

        <!-- Expansion Area (Visual Difference layout) -->
        <div v-show="isExpanded" class="px-6 pb-6 pt-2 border-t border-slate-100 dark:border-zinc-800/80 bg-slate-50/30 dark:bg-zinc-900/10">
            <!-- Diffs exist -->
            <div v-if="diffs.length > 0" class="space-y-3.5">
                <h4 class="text-[11px] font-bold text-slate-400 dark:text-zinc-500 uppercase tracking-wider font-mono">Modifications Details</h4>
                <div class="grid grid-cols-1 gap-2.5">
                    <div
                        v-for="diff in diffs"
                        :key="diff.key"
                        class="p-4 rounded-2xl bg-white dark:bg-zinc-900/60 border border-slate-100 dark:border-zinc-800/50 flex flex-col md:flex-row md:items-center justify-between gap-4 text-xs"
                    >
                        <span class="font-extrabold text-slate-800 dark:text-zinc-300 uppercase tracking-wide font-mono text-[10px] md:w-1/4">
                            {{ diff.key }}
                        </span>
                        
                        <div class="flex-1 flex flex-col sm:flex-row sm:items-center gap-3">
                            <span class="px-2.5 py-1 rounded-xl bg-rose-50 text-rose-700 border border-rose-100 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/10 block w-full text-center truncate">
                                {{ diff.old }}
                            </span>
                            <span class="text-slate-400 dark:text-zinc-600 font-extrabold text-center block rotate-90 sm:rotate-0">➔</span>
                            <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/10 block w-full text-center truncate">
                                {{ diff.new }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Diffs / Meta Info Only -->
            <div v-else class="text-xs text-slate-500 dark:text-zinc-400 py-2">
                <span class="font-extrabold block mb-1">User Agent metadata:</span>
                <p class="font-mono text-[10.5px] bg-slate-50 dark:bg-zinc-900 p-3 rounded-xl border border-slate-100 dark:border-zinc-800/80 leading-relaxed break-all">
                    {{ log.user_agent ?? 'N/A' }}
                </p>
            </div>
        </div>
    </div>
</template>
