<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Pagination from '@/Components/UI/Pagination.vue';

interface NotificationItem {
    id: string;
    type: string;
    data: {
        title?: string;
        message?: string;
        url?: string;
        icon?: string;
    };
    read_at: string | null;
    created_at: string;
}

interface PaginatedNotifications {
    data: NotificationItem[];
    links: any[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    notifications: PaginatedNotifications;
}>();

function markSingleRead(id: string, url?: string) {
    router.post(`/notifications/${id}/read`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            if (url) {
                router.get(url);
            }
        },
    });
}

function markAllRead() {
    router.post('/notifications/read-all', { preserveScroll: true });
}
</script>

<template>
    <Head title="Notifications Center — BookVerse">
        <meta name="description" content="View all in-app notifications and alerts on BookVerse E-Magazine." />
    </Head>

    <MainLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8 font-sans">
            <!-- Header Title Banner -->
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-800 pb-5">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black font-heading text-slate-900 dark:text-white flex items-center gap-2">
                        🔔 Notifications Center ({{ notifications.total }})
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                        All in-app alerts, publication approvals, comment replies, and author updates.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button variant="secondary" size="sm" @click="markAllRead">
                        ✓ Mark All Read
                    </Button>
                </div>
            </div>

            <!-- Notifications List -->
            <div v-if="notifications.data.length > 0" class="space-y-4">
                <div
                    v-for="item in notifications.data"
                    :key="item.id"
                    class="p-5 rounded-3xl border transition flex items-start gap-4"
                    :class="item.read_at
                        ? 'bg-white dark:bg-zinc-950 border-slate-200 dark:border-zinc-800 opacity-80'
                        : 'bg-sky-50/40 dark:bg-sky-950/20 border-sky-200 dark:border-sky-900/60 shadow-xs'"
                >
                    <span class="text-2xl shrink-0 mt-1">
                        {{ item.data.icon || '🔔' }}
                    </span>

                    <div class="space-y-1 flex-1">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                            <h3 class="font-extrabold text-sm text-slate-900 dark:text-white font-heading">
                                {{ item.data.title || 'Notification' }}
                            </h3>
                            <span class="text-[10px] text-slate-400 font-mono">
                                {{ new Date(item.created_at).toLocaleString() }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-600 dark:text-zinc-300 leading-relaxed">
                            {{ item.data.message }}
                        </p>

                        <div class="pt-2 flex items-center gap-3">
                            <button
                                v-if="item.data.url"
                                @click="markSingleRead(item.id, item.data.url)"
                                class="text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline cursor-pointer flex items-center gap-1"
                            >
                                View Details →
                            </button>

                            <button
                                v-if="!item.read_at"
                                @click="markSingleRead(item.id)"
                                class="text-xs font-semibold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
                            >
                                Mark as Read
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-16 bg-white dark:bg-zinc-950 rounded-3xl border border-slate-200 dark:border-zinc-800 text-slate-400 text-xs space-y-2">
                <div class="text-3xl">✨</div>
                <p class="font-semibold text-slate-600 dark:text-zinc-400">Your notification center is clear.</p>
                <p>Alerts for article approvals, comment replies, and new publications will appear here.</p>
            </div>

            <!-- Pagination -->
            <div v-if="notifications.links.length > 3" class="pt-4 flex justify-center">
                <Pagination :links="notifications.links" />
            </div>
        </div>
    </MainLayout>
</template>
