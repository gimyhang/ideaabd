<script setup lang="ts">
interface User {
    id: number;
    name: string;
    email?: string;
}

interface ActivityLog {
    id: number;
    event_type: string;
    description: string;
    created_at: string;
    user?: User;
}

const props = defineProps<{
    logs?: ActivityLog[];
}>();

function getEventIcon(eventType: string) {
    switch (eventType) {
        case 'published':
            return '🟢';
        case 'scheduled':
            return '📅';
        case 'rejected':
            return '🔴';
        case 'resubmitted':
            return '🔄';
        case 'submitted':
            return '🚀';
        case 'locked':
            return '🔒';
        case 'unlocked':
            return '🔓';
        case 'comment_added':
            return '💬';
        default:
            return '📝';
    }
}
</script>

<template>
    <div class="space-y-4 font-sans text-xs">
        <h4 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
            📜 Activity & Audit Timeline
        </h4>

        <div v-if="logs && logs.length > 0" class="relative pl-6 space-y-4 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 dark:before:bg-zinc-800">
            <div
                v-for="log in logs"
                :key="log.id"
                class="relative flex items-start justify-between gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800"
            >
                <div class="absolute -left-6 top-3 w-5 h-5 rounded-full bg-white dark:bg-zinc-950 border border-slate-300 dark:border-zinc-700 flex items-center justify-center text-[10px]">
                    {{ getEventIcon(log.event_type) }}
                </div>

                <div class="space-y-0.5">
                    <p class="font-semibold text-slate-800 dark:text-slate-200">
                        {{ log.description }}
                    </p>
                    <div class="text-[10px] text-slate-400 font-mono">
                        By {{ log.user?.name || 'System' }}
                    </div>
                </div>

                <span class="text-[10px] text-slate-400 font-mono whitespace-nowrap">
                    {{ new Date(log.created_at).toLocaleString() }}
                </span>
            </div>
        </div>

        <div v-else class="text-center py-4 text-slate-400 text-xs">
            No activity logs recorded yet.
        </div>
    </div>
</template>
