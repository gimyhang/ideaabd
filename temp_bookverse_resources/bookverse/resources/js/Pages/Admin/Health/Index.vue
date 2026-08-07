<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';
import Toast from '@/Components/UI/Toast.vue';
import { usePermission } from '@/Composables/usePermission';

const { isSuperAdmin } = usePermission();

interface FailedJobItem {
    id: number;
    uuid: string;
    connection: string;
    queue: string;
    exception: string;
    failed_at: string;
}

interface SystemHealthPayload {
    database: {
        status: string;
        latency_ms: number;
        connection: string;
    };
    cache: {
        status: string;
        driver: string;
    };
    queue: {
        driver: string;
        pending_jobs: number;
        failed_jobs: number;
        status: string;
    };
    search: {
        status: string;
    };
    storage: {
        writable: boolean;
        free_disk_gb: number | string;
    };
    system: {
        php_version: string;
        laravel_version: string;
        environment: string;
    };
}

const props = defineProps<{
    health: SystemHealthPayload;
    failedJobsList: FailedJobItem[];
    availableCacheTags: string[];
}>();

const showToast = ref(false);
const toastMessage = ref('');
const flushingTag = ref<string | null>(null);

const tagForm = useForm({
    tag: '',
});

function flushCacheTag(tagName: string) {
    flushingTag.value = tagName;
    tagForm.tag = tagName;
    tagForm.post(route('admin.system.cache.flush-tag'), {
        preserveScroll: true,
        onSuccess: () => {
            flushingTag.value = null;
            toastMessage.value = `Cache tag '${tagName}' successfully flushed!`;
            showToast.value = true;
        },
        onError: () => {
            flushingTag.value = null;
        },
    });
}

function retryFailedJobs(jobId: string | number = 'all') {
    router.post(route('admin.system.queue.retry-failed'), { id: jobId }, {
        preserveScroll: true,
        onSuccess: () => {
            toastMessage.value = jobId === 'all' ? 'Retrying all failed queue jobs...' : `Retrying job #${jobId}...`;
            showToast.value = true;
        },
    });
}

function flushFailedJobs() {
    if (confirm('Are you sure you want to clear all failed queue jobs log? This action cannot be undone.')) {
        router.delete(route('admin.system.queue.flush-failed'), {
            preserveScroll: true,
            onSuccess: () => {
                toastMessage.value = 'Failed queue jobs log cleared!';
                showToast.value = true;
            },
        });
    }
}
</script>

<template>
    <AdminLayout>
        <Head title="System Health & Queue Dashboard — Admin" />

        <div class="space-y-6 font-sans">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-2xl shadow-sm border transition-colors bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white">
                <div>
                    <h1 class="text-2xl font-black font-heading tracking-tight flex items-center gap-2">
                        <span>⚡ System Health & Queue Monitor</span>
                        <Badge :variant="health.database.status === 'ok' ? 'success' : 'error'" size="sm" dot>
                            {{ health.database.status === 'ok' ? 'System Operational' : 'System Degraded' }}
                        </Badge>
                    </h1>
                    <p class="text-xs mt-1 text-slate-500 dark:text-zinc-400 font-mono">
                        PHP v{{ health.system.php_version }} • Laravel v{{ health.system.laravel_version }} • Env: {{ health.system.environment }}
                    </p>
                </div>
            </div>

            <!-- Health Probe Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Database Probe Card -->
                <div class="p-5 rounded-2xl border bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 space-y-2 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 dark:text-zinc-400 font-mono uppercase tracking-wider">Database Ping</span>
                        <span class="text-lg">🗄️</span>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-2xl font-black text-slate-900 dark:text-white font-mono">{{ health.database.latency_ms }} ms</span>
                        <Badge :variant="health.database.status === 'ok' ? 'success' : 'error'" size="sm">
                            {{ health.database.connection }}
                        </Badge>
                    </div>
                </div>

                <!-- Cache Driver Probe Card -->
                <div class="p-5 rounded-2xl border bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 space-y-2 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 dark:text-zinc-400 font-mono uppercase tracking-wider">Cache System</span>
                        <span class="text-lg">🚀</span>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-xl font-extrabold text-slate-900 dark:text-white font-mono uppercase">{{ health.cache.driver }}</span>
                        <Badge :variant="health.cache.status === 'ok' ? 'success' : 'warning'" size="sm">
                            {{ health.cache.status }}
                        </Badge>
                    </div>
                </div>

                <!-- Queue & Worker Health Card -->
                <div class="p-5 rounded-2xl border bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 space-y-2 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 dark:text-zinc-400 font-mono uppercase tracking-wider">Queue Worker</span>
                        <span class="text-lg">⚙️</span>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <div class="space-x-2">
                            <span class="text-xl font-bold text-slate-900 dark:text-white font-mono">{{ health.queue.pending_jobs }} Pending</span>
                        </div>
                        <Badge :variant="health.queue.failed_jobs > 0 ? 'error' : 'success'" size="sm">
                            {{ health.queue.failed_jobs }} Failed
                        </Badge>
                    </div>
                </div>

                <!-- Disk & Search Engine Card -->
                <div class="p-5 rounded-2xl border bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 space-y-2 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 dark:text-zinc-400 font-mono uppercase tracking-wider">Disk Free Space</span>
                        <span class="text-lg">💾</span>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-xl font-bold text-slate-900 dark:text-white font-mono">{{ health.storage.free_disk_gb }} GB</span>
                        <Badge variant="brand" size="sm">
                            Writable: {{ health.storage.writable ? 'Yes' : 'No' }}
                        </Badge>
                    </div>
                </div>
            </div>

            <!-- Safe Tagged Cache Flush Panel -->
            <div class="p-6 rounded-2xl border bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 space-y-4 shadow-sm">
                <div>
                    <h2 class="text-lg font-bold font-heading text-slate-900 dark:text-white">🏷️ Safe Tagged Cache Flush Panel</h2>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Invalidate specific domain cache tags without wiping the entire application cache store.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div
                        v-for="tag in availableCacheTags"
                        :key="tag"
                        class="p-3 rounded-xl border bg-slate-50 dark:bg-zinc-950/60 border-slate-200 dark:border-zinc-800 flex items-center gap-3"
                    >
                        <div>
                            <span class="font-mono text-xs font-bold text-slate-800 dark:text-zinc-200 uppercase">#{{ tag }}</span>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            :loading="flushingTag === tag"
                            @click="flushCacheTag(tag)"
                            class="text-[11px] font-bold py-1 px-2.5 rounded-lg border-sky-300 dark:border-sky-800 text-sky-700 dark:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-950/50"
                        >
                            Flush Tag
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Failed Queue Jobs Management Section -->
            <div class="p-6 rounded-2xl border bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 space-y-4 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold font-heading text-slate-900 dark:text-white flex items-center gap-2">
                            <span>🚨 Failed Queue Jobs Log</span>
                            <Badge :variant="failedJobsList.length > 0 ? 'error' : 'default'" size="sm" class="font-mono">
                                {{ health.queue.failed_jobs }} Failed
                            </Badge>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                            Inspect failed async background tasks, retry execution, or flush the failed job registry.
                        </p>
                    </div>

                    <div class="flex items-center gap-2" v-if="failedJobsList.length > 0">
                        <Button variant="secondary" size="sm" class="font-bold" @click="retryFailedJobs('all')">
                            Retry All Failed
                        </Button>
                        <Button v-if="isSuperAdmin" variant="destructive" size="sm" class="font-bold" @click="flushFailedJobs">
                            Clear Failed Log
                        </Button>
                    </div>
                </div>

                <div v-if="failedJobsList.length > 0" class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b uppercase tracking-wider font-semibold text-[10px] bg-slate-50 dark:bg-zinc-950/60 text-slate-500 dark:text-zinc-400 border-slate-200 dark:border-zinc-800">
                                <th class="py-3 px-4">ID / Queue</th>
                                <th class="py-3 px-4">Exception Snippet</th>
                                <th class="py-3 px-4">Failed At</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800 font-mono text-slate-700 dark:text-slate-200">
                            <tr v-for="job in failedJobsList" :key="job.id" class="hover:bg-slate-50/60 dark:hover:bg-zinc-800/30">
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                    #{{ job.id }} <span class="text-slate-400 font-normal">({{ job.queue }})</span>
                                </td>
                                <td class="py-3 px-4 text-[11px] text-rose-600 dark:text-rose-400 font-mono line-clamp-2 max-w-md">
                                    {{ job.exception }}
                                </td>
                                <td class="py-3 px-4 text-slate-500 dark:text-zinc-400">
                                    {{ job.failed_at }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <Button variant="outline" size="sm" class="text-xs font-bold" @click="retryFailedJobs(job.id)">
                                        Retry Job
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="py-8 text-center bg-slate-50/50 dark:bg-zinc-950/40 rounded-xl border border-dashed border-slate-200 dark:border-zinc-800">
                    <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 flex items-center justify-center gap-1.5">
                        <span>✨ Zero Failed Jobs in Queue Log. Everything is running smoothly!</span>
                    </p>
                </div>
            </div>
        </div>

        <Toast :show="showToast" :message="toastMessage" type="success" @close="showToast = false" />
    </AdminLayout>
</template>
