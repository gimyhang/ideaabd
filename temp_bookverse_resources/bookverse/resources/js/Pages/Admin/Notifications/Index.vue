<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
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
        order_number?: string;
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
    <Head title="Admin Notifications & Alerts — BookVerse Admin" />

    <AdminLayout>
        <template #header>
            Admin Notifications Center
        </template>

        <div class="p-6 sm:p-8 space-y-6 font-sans">
            <!-- Header Title Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-zinc-800 pb-5">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black font-heading text-slate-900 dark:text-white flex items-center gap-2">
                        🔔 Admin Alerts & Order Notifications ({{ notifications.total }})
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                        Live updates for new customer orders, sales transactions, and system activity.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button variant="secondary" size="sm" @click="markAllRead">
                        ✓ Mark All Read
                    </Button>
                </div>
            </div>

            <!-- Notifications List Table Card -->
            <div v-if="notifications.data.length > 0" class="space-y-3">
                <div
                    v-for="item in notifications.data"
                    :key="item.id"
                    class="p-5 rounded-3xl border transition flex items-start gap-4"
                    :class="item.read_at
                        ? 'bg-white dark:bg-zinc-950 border-slate-200 dark:border-zinc-800 opacity-80'
                        : 'bg-sky-50/60 dark:bg-sky-950/30 border-sky-200 dark:border-sky-800 shadow-xs'"
                >
                    <span class="text-2xl shrink-0 mt-1">
                        {{ item.data.icon || '🛍️' }}
                    </span>

                    <div class="space-y-1 flex-1">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                            <h3 class="font-extrabold text-sm text-slate-900 dark:text-white font-heading">
                                {{ item.data.title || 'Admin Alert' }}
                            </h3>
                            <span class="text-[10px] text-slate-400 font-mono">
                                {{ new Date(item.created_at).toLocaleString() }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-700 dark:text-zinc-300 leading-relaxed font-medium">
                            {{ item.data.message }}
                        </p>

                        <div class="pt-2 flex items-center gap-3">
                            <button
                                v-if="item.data.url"
                                @click="markSingleRead(item.id, item.data.url)"
                                class="px-3 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-extrabold text-xs shadow-xs transition cursor-pointer flex items-center gap-1"
                            >
                                View Order Details →
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
                <p class="font-semibold text-slate-600 dark:text-zinc-400">No pending admin notifications.</p>
                <p>Alerts for new customer orders will appear here in real time.</p>
            </div>

            <!-- Pagination -->
            <div v-if="notifications.links.length > 3" class="pt-4 flex justify-center">
                <Pagination :links="notifications.links" />
            </div>
        </div>
    </AdminLayout>
</template>
