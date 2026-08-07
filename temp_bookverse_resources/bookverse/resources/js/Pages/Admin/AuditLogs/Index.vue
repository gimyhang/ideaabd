<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AuditLogItem from '@/Components/Admin/AuditLogItem.vue';
import Icon from '@/Components/UI/Icon.vue';

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

interface PaginatedLogs {
    data: LogItem[];
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

const props = defineProps<{
    logs: PaginatedLogs;
    actors: Array<{ id: number; name: string }>;
    distinctEvents: string[];
    filters: {
        search?: string;
        start_date?: string;
        end_date?: string;
        event?: string;
        actor_id?: string | number;
        resource_type?: string;
        resource_id?: string | number;
    };
}>();

const search = ref(props.filters.search || '');
const eventFilter = ref(props.filters.event || '');
const actorFilter = ref(props.filters.actor_id || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');
const resType = ref(props.filters.resource_type || '');
const resId = ref(props.filters.resource_id || '');

function applyFilters() {
    router.get(
        '/admin/audit-logs',
        {
            search: search.value,
            event: eventFilter.value,
            actor_id: actorFilter.value,
            start_date: startDate.value,
            end_date: endDate.value,
            resource_type: resType.value,
            resource_id: resId.value,
        },
        { preserveState: true, replace: true }
    );
}

function clearFilters() {
    search.value = '';
    eventFilter.value = '';
    actorFilter.value = '';
    startDate.value = '';
    endDate.value = '';
    resType.value = '';
    resId.value = '';
    applyFilters();
}

// Watch filters to auto-apply on drop downs
watch([eventFilter, actorFilter, startDate, endDate], () => {
    applyFilters();
});
</script>

<template>
    <Head title="Platform Activity Audit Logs — BookVerse" />

    <AdminLayout>
        <template #header>
            Platform Activity Audit Logs
        </template>

        <div class="p-6 sm:p-8 space-y-7 font-sans bg-slate-50/50 dark:bg-zinc-950/20 min-h-screen">
            
            <!-- Filters Panel -->
            <div class="p-5 rounded-3xl bg-white dark:bg-zinc-950/70 border border-slate-200/80 dark:border-zinc-800/80 shadow-xs space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search Input -->
                    <div class="flex flex-col gap-1">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase font-mono tracking-wider">Search Logs</span>
                        <input
                            type="text"
                            v-model="search"
                            @keyup.enter="applyFilters"
                            placeholder="Search actor, event..."
                            class="w-full text-xs font-semibold px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800/80 rounded-2xl text-slate-800 dark:text-zinc-100 outline-none focus:ring-1 focus:ring-sky-500"
                        />
                    </div>

                    <!-- Actor Filter -->
                    <div class="flex flex-col gap-1">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase font-mono tracking-wider">Filter by Actor</span>
                        <select
                            v-model="actorFilter"
                            class="w-full text-xs font-semibold px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800/80 rounded-2xl text-slate-800 dark:text-zinc-100 outline-none focus:ring-1 focus:ring-sky-500"
                        >
                            <option value="">All Actors</option>
                            <option v-for="actor in actors" :key="actor.id" :value="actor.id">
                                {{ actor.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Event Filter -->
                    <div class="flex flex-col gap-1">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase font-mono tracking-wider">Filter by Event</span>
                        <select
                            v-model="eventFilter"
                            class="w-full text-xs font-semibold px-3.5 py-2.5 bg-slate-50 dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800/80 rounded-2xl text-slate-800 dark:text-zinc-100 outline-none focus:ring-1 focus:ring-sky-500"
                        >
                            <option value="">All Events</option>
                            <option v-for="evt in distinctEvents" :key="evt" :value="evt">
                                {{ evt }}
                            </option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div class="flex flex-col gap-1">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-zinc-500 uppercase font-mono tracking-wider">Date range</span>
                        <div class="flex items-center gap-2">
                            <input
                                type="date"
                                v-model="startDate"
                                class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800/80 rounded-2xl text-slate-800 dark:text-zinc-100"
                            />
                            <span class="text-xs text-slate-400 font-mono">to</span>
                            <input
                                type="date"
                                v-model="endDate"
                                class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800/80 rounded-2xl text-slate-800 dark:text-zinc-100"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4 pt-2 border-t border-slate-100 dark:border-zinc-800/80">
                    <!-- Advanced auditable filters -->
                    <div class="flex items-center gap-3">
                        <input
                            type="text"
                            v-model="resType"
                            placeholder="Resource Type (e.g. App\Models\Book)"
                            class="text-[11px] font-semibold px-3.5 py-2 bg-slate-50 dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800/80 rounded-xl text-slate-800 dark:text-zinc-100 w-56"
                        />
                        <input
                            type="text"
                            v-model="resId"
                            placeholder="Resource ID"
                            class="text-[11px] font-semibold px-3.5 py-2 bg-slate-50 dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800/80 rounded-xl text-slate-800 dark:text-zinc-100 w-28"
                        />
                        <button
                            type="button"
                            @click="applyFilters"
                            class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-300 font-bold text-[11px] rounded-xl transition"
                        >
                            Filter Resource
                        </button>
                    </div>

                    <button
                        type="button"
                        @click="clearFilters"
                        class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-200"
                    >
                        Clear All Filters
                    </button>
                </div>
            </div>

            <!-- Logs Listing -->
            <div class="space-y-4">
                <template v-if="logs.data.length > 0">
                    <AuditLogItem
                        v-for="log in logs.data"
                        :key="log.id"
                        :log="log"
                    />
                </template>

                <div v-else class="p-8 text-center bg-white dark:bg-zinc-950/70 border border-slate-200/80 dark:border-zinc-800/80 rounded-3xl text-slate-400 dark:text-zinc-500 font-medium">
                    No activity logs found matching the filters.
                </div>
            </div>

            <!-- Pagination footer -->
            <div class="flex items-center justify-between p-4 bg-white dark:bg-zinc-950/70 border border-slate-200/80 dark:border-zinc-800/80 rounded-3xl">
                <span class="text-xs text-slate-500 dark:text-zinc-400 font-medium">Page {{ logs.data.length ? 1 : 0 }}</span>
                <div class="flex items-center gap-1.5">
                    <template v-for="link in logs.links">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="px-2.5 py-1.5 rounded-lg border text-[11px] font-bold transition-all"
                            :class="link.active ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-white dark:bg-zinc-900 hover:bg-slate-50 dark:hover:bg-zinc-800 text-slate-700 dark:text-zinc-300 border-slate-200 dark:border-zinc-800/80'"
                        />
                        <span
                            v-else
                            v-html="link.label"
                            class="px-2.5 py-1.5 rounded-lg border text-[11px] font-bold text-slate-300 dark:text-zinc-600 bg-slate-50 dark:bg-zinc-900/50 border-slate-200 dark:border-zinc-800/50 cursor-not-allowed"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
