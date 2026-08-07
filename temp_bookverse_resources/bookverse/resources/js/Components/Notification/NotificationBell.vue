<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';

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

const props = withDefaults(defineProps<{
    scope?: 'all' | 'admin' | 'frontend';
}>(), {
    scope: 'all',
});

const page = usePage();
const isOpen = ref(false);
const unreadCount = ref<number>((page.props.auth as any)?.user?.unread_notifications_count || 0);
const notifications = ref<NotificationItem[]>([]);
const isLoading = ref(false);

function fetchUnreadList() {
    if (!page.props.auth?.user) return;
    isLoading.value = true;
    fetch(`/notifications/unread-list?scope=${props.scope}`, {
        headers: { 'Accept': 'application/json' },
    })
        .then(res => res.json())
        .then(data => {
            unreadCount.value = data.unread_count;
            notifications.value = data.notifications;
            isLoading.value = false;
        })
        .catch(() => {
            isLoading.value = false;
        });
}

function toggleDropdown() {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        fetchUnreadList();
    }
}

function markSingleRead(id: string, url?: string) {
    router.post(`/notifications/${id}/read`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            unreadCount.value = Math.max(0, unreadCount.value - 1);
            const found = notifications.value.find(n => n.id === id);
            if (found) {
                found.read_at = new Date().toISOString();
            }
            if (url && typeof url === 'string' && url.trim().length > 0) {
                isOpen.value = false;
                router.get(url);
            }
        },
    });
}

function markAllRead() {
    router.post('/notifications/read-all', {}, {
        preserveScroll: true,
        onSuccess: () => {
            unreadCount.value = 0;
            notifications.value.forEach(n => {
                n.read_at = new Date().toISOString();
            });
        },
    });
}
</script>

<template>
    <div class="relative font-sans text-xs">
        <!-- Bell Icon Button (Modern SVG & Animated Badge) -->
        <button
            type="button"
            @click="toggleDropdown"
            class="relative p-2.5 rounded-2xl bg-slate-50/80 dark:bg-zinc-900/80 hover:bg-sky-50 dark:hover:bg-zinc-800 border border-slate-200/60 dark:border-zinc-800 transition-all duration-200 shadow-xs cursor-pointer group flex items-center justify-center"
            title="Notifications"
        >
            <svg
                class="w-5 h-5 text-slate-600 dark:text-zinc-300 group-hover:text-sky-600 dark:group-hover:text-sky-400 group-hover:rotate-12 transition transform duration-200"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>

            <!-- Unread Badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 min-w-4 h-4 px-1 rounded-full bg-gradient-to-r from-rose-500 to-red-600 text-white font-extrabold text-[10px] flex items-center justify-center shadow-md shadow-rose-500/30 border-2 border-white dark:border-zinc-950 font-mono animate-pulse"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Notification Dropdown Panel -->
        <div
            v-if="isOpen"
            class="absolute right-0 mt-2 w-80 sm:w-96 rounded-3xl bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 shadow-2xl z-50 overflow-hidden space-y-2 p-4"
        >
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-900 pb-3">
                <div class="flex items-center gap-2">
                    <h3 class="font-extrabold text-sm text-slate-900 dark:text-white font-heading">
                        Notifications
                    </h3>
                    <span v-if="unreadCount > 0" class="px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 text-[10px] font-extrabold">
                        {{ unreadCount }} new
                    </span>
                </div>

                <button
                    v-if="unreadCount > 0"
                    @click="markAllRead"
                    class="text-[11px] font-bold text-sky-600 dark:text-sky-400 hover:underline cursor-pointer"
                >
                    Mark All Read
                </button>
            </div>

            <!-- List -->
            <div v-if="isLoading" class="py-8 text-center text-slate-400 text-xs font-mono">
                Loading alerts...
            </div>

            <div v-else-if="notifications.length > 0" class="divide-y divide-slate-100 dark:divide-zinc-800/80 max-h-80 overflow-y-auto">
                <div
                    v-for="item in notifications"
                    :key="item.id"
                    @click="markSingleRead(item.id, item.data.url)"
                    class="py-3 px-2.5 hover:bg-slate-50 dark:hover:bg-zinc-900/60 transition cursor-pointer flex items-start gap-3 group rounded-xl"
                    :class="item.read_at ? 'opacity-70' : 'bg-sky-500/5 dark:bg-sky-500/10'"
                >
                    <span class="text-base shrink-0 mt-0.5">
                        {{ item.data.icon || '🔔' }}
                    </span>

                    <div class="space-y-1 flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-slate-900 dark:text-white text-xs group-hover:text-sky-600 dark:group-hover:text-sky-400 transition">
                                {{ item.data.title || 'Notification' }}
                            </h4>
                            <span class="text-[9px] text-slate-400 font-mono">
                                {{ new Date(item.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                            </span>
                        </div>

                        <p class="text-slate-600 dark:text-zinc-300 text-xs leading-relaxed line-clamp-2">
                            {{ item.data.message }}
                        </p>
                    </div>
                </div>
            </div>

            <div v-else class="py-8 text-center text-slate-400 text-xs space-y-1">
                <div class="text-xl">✨</div>
                <p>No unread notifications.</p>
            </div>

            <!-- Footer Links -->
            <div class="pt-2 border-t border-slate-100 dark:border-zinc-900 flex items-center justify-center text-xs">
                <Link
                    :href="props.scope === 'admin' ? '/admin/notifications' : '/notifications'"
                    @click="isOpen = false"
                    class="font-bold text-sky-600 dark:text-sky-400 hover:underline"
                >
                    View All Alerts →
                </Link>
            </div>
        </div>
    </div>
</template>
