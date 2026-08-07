<script setup lang="ts">
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import Sidebar from '@/Components/Layout/Sidebar.vue';
import Avatar from '@/Components/UI/Avatar.vue';
import Toast from '@/Components/UI/Toast.vue';
import NotificationBell from '@/Components/Notification/NotificationBell.vue';
import DarkModeToggle from '@/Components/Layout/DarkModeToggle.vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import { useAdminTheme } from '@/Composables/useAdminTheme';

const page = usePage();
const { isDark, toggle, enableAdminTheme, disableAdminTheme } = useAdminTheme();

onMounted(() => {
    enableAdminTheme();
});

onUnmounted(() => {
    disableAdminTheme();
});

const showProfileMenu = ref(false);

// Toast Notification State
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref<'success' | 'error'>('success');

watch(
    () => page.props.flash as { success?: string; error?: string } | undefined,
    (flash) => {
        if (flash?.success) {
            toastMessage.value = String(flash.success);
            toastType.value = 'success';
            showToast.value = true;
        } else if (flash?.error) {
            toastMessage.value = String(flash.error);
            toastType.value = 'error';
            showToast.value = true;
        }
    },
    { immediate: true, deep: true }
);

function logout() {
    showProfileMenu.value = false;
    router.post('/logout');
}

const createMenuItem = (label: string, href: string, icon: string, permission: string | null = null) => ({
    label,
    href,
    icon,
    permission,
});

const createParentItem = (label: string, icon: string, items: any[]) => ({
    label,
    icon,
    items,
});

const menuItems = ref([
    createMenuItem('Dashboard', '/admin', 'dashboard', 'index-dashboard'),

    createParentItem('Catalog Setup', 'books', [
        createMenuItem('Book Catalog', '/admin/books', 'books', 'index-books'),
        createMenuItem('Categories', '/admin/categories', 'categories', 'index-categories'),
        createMenuItem('Authors', '/admin/authors', 'authors', 'index-authors'),
        createMenuItem('Publishers', '/admin/publishers', 'publishers', 'index-publishers'),
    ]),

    createParentItem('Sales & Orders', 'orders', [
        createMenuItem('Customer Orders', '/admin/orders', 'orders', 'index-orders'),
        createMenuItem('Return Requests', '/admin/returns', 'returns', 'index-returns'),
        createMenuItem('Reviews Moderation', '/admin/reviews', 'reviews', 'index-reviews'),
    ]),

    createParentItem('Reports & Analytics', 'reports', [
        createMenuItem('Sales Report', '/admin/reports/sales', 'orders', 'index-reports'),
        createMenuItem('Top Books', '/admin/reports/books', 'books', 'index-reports'),
        createMenuItem('Top Writers', '/admin/reports/writers', 'writers', 'index-reports'),
        createMenuItem('User Growth', '/admin/reports/users', 'users', 'index-reports'),
    ]),

    createParentItem('Marketing & Promos', 'coupons', [
        createMenuItem('Discount Coupons', '/admin/coupons', 'coupons', 'index-coupons'),
    ]),

    createParentItem('Website CMS', 'submissions', [
        createMenuItem('Article Submissions', '/admin/submissions', 'submissions', 'index-submissions'),
        createMenuItem('Comment Moderation', '/admin/comments', 'comments', 'index-comments'),
    ]),

    createParentItem('User Management', 'users', [
        createMenuItem('User Accounts', '/admin/users', 'users', 'index-users'),
        createMenuItem('Writer Applications', '/admin/writers', 'writers', 'index-writers'),
        createMenuItem('Roles & Permissions', '/admin/roles', 'roles', 'index-roles'),
        createMenuItem('Audit Logs', '/admin/audit-logs', 'submissions', 'view-audit-logs'),
    ]),

    createMenuItem('System Settings', '/admin/settings', 'settings', 'index-settings'),
    createMenuItem('System Health', '/admin/system/health', 'settings', 'index-dashboard'),
]);

const filteredNavItems = computed(() => {
    const userPermissions = (page.props.auth?.user as any)?.permissions || [];

    const filterItems = (items: any[]): any[] => {
        return items
            .map((item) => {
                if (item.items) {
                    const subItems = filterItems(item.items);
                    if (subItems.length > 0) {
                        return { ...item, items: subItems };
                    }
                    return null;
                }
                if (item.permission && !userPermissions.includes(item.permission)) {
                    return null;
                }
                return item;
            })
            .filter(Boolean);
    };

    return filterItems(menuItems.value);
});

</script>

<template>
    <div class="min-h-screen flex font-sans antialiased transition-colors duration-300 bg-slate-100 dark:bg-zinc-950 text-slate-900 dark:text-slate-100">
        <!-- Admin Sidebar -->
        <Sidebar
            title="BookVerse Admin"
            :items="filteredNavItems"
            class="z-30"
        />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">

            <!-- Topbar (relative z-40 guarantees dropdown sits on top of all dashboard cards) -->
            <header class="h-16 backdrop-blur-md border-b px-6 flex items-center justify-between transition-colors duration-300 bg-white/90 dark:bg-zinc-950/90 border-slate-200/80 dark:border-zinc-900 shadow-xs relative z-40">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-black uppercase tracking-wider px-2.5 py-1 rounded-full border text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 border-indigo-200 dark:border-indigo-900/50 shadow-xs">
                        Admin Portal
                    </span>
                    <h2 class="text-sm font-black font-heading text-slate-900 dark:text-white">
                        <slot name="header">Overview</slot>
                    </h2>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Notification Bell Icon (Admin Dashboard Alerts) -->
                    <NotificationBell v-if="page.props.auth?.user" scope="admin" />

                    <!-- Dark Mode Toggle Button -->
                    <DarkModeToggle />

                    <!-- Profile Dropdown -->
                    <div class="relative">
                        <button
                            id="admin-profile-menu-btn"
                            @click="showProfileMenu = !showProfileMenu"
                            class="flex items-center gap-2.5 rounded-xl px-2 py-1.5 transition-colors hover:bg-slate-100 dark:hover:bg-white/5"
                        >
                            <span class="text-xs font-medium hidden sm:block text-slate-600 dark:text-slate-300">
                                {{ page.props.auth?.user?.name || 'Administrator' }}
                            </span>
                            <Avatar :name="page.props.auth?.user?.name || 'Admin'" size="sm" status="online" />
                            <svg
                                class="w-3.5 h-3.5 transition-transform duration-200 text-slate-500 dark:text-slate-400"
                                :class="showProfileMenu ? 'rotate-180' : ''"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu (z-50) -->
                        <Transition
                            enter-active-class="transition ease-out duration-150"
                            enter-from-class="opacity-0 scale-95 translate-y-1"
                            enter-to-class="opacity-100 scale-100 translate-y-0"
                            leave-active-class="transition ease-in duration-100"
                            leave-from-class="opacity-100 scale-100 translate-y-0"
                            leave-to-class="opacity-0 scale-95 translate-y-1"
                        >
                            <div v-if="showProfileMenu">
                                <div class="fixed inset-0 z-40" @click="showProfileMenu = false"></div>
                                <div class="absolute right-0 top-full mt-2 w-56 rounded-2xl border shadow-2xl overflow-hidden z-50 bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-700 shadow-slate-300/80 dark:shadow-black/60">
                                    <div class="px-4 py-3 border-b border-slate-100 dark:border-zinc-800">
                                        <p class="text-xs font-bold truncate text-slate-900 dark:text-white">
                                            {{ page.props.auth?.user?.name }}
                                        </p>
                                        <p class="text-[11px] truncate mt-0.5 text-slate-500 dark:text-slate-400">
                                            {{ page.props.auth?.user?.email }}
                                        </p>
                                    </div>

                                    <div class="py-1.5">
                                        <Link href="/profile" class="flex items-center gap-3 px-4 py-2.5 text-xs font-medium transition-colors text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-900 dark:hover:text-white">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            View Profile
                                        </Link>
                                        <a href="/health" target="_blank" class="flex items-center justify-between px-4 py-2.5 text-xs font-medium transition-colors text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-900 dark:hover:text-white">
                                            <div class="flex items-center gap-3">
                                                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                                <span>Health Status API</span>
                                            </div>
                                            <span class="text-[10px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-mono font-bold px-1.5 py-0.5 rounded">/health</span>
                                        </a>
                                        <Link href="/" class="flex items-center gap-3 px-4 py-2.5 text-xs font-medium transition-colors text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-900 dark:hover:text-white">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                            Visit Public Site
                                        </Link>
                                    </div>

                                    <!-- Dark / Light Mode Toggle inside Dropdown ONLY -->
                                    <div class="py-2.5 px-4 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between">
                                        <span class="text-xs font-semibold text-slate-700 dark:text-zinc-300 flex items-center gap-2">
                                            <span v-if="isDark">🌙 Dark Mode</span>
                                            <span v-else>☀️ Light Mode</span>
                                        </span>
                                        <button
                                            type="button"
                                            @click.stop="toggle"
                                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                            :class="isDark ? 'bg-sky-600' : 'bg-slate-300 dark:bg-zinc-700'"
                                        >
                                            <span
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                                                :class="isDark ? 'translate-x-5' : 'translate-x-0'"
                                            />
                                        </button>
                                    </div>

                                    <!-- Logout Action -->
                                    <div class="py-1.5 border-t border-slate-100 dark:border-zinc-800">
                                        <button
                                            id="admin-logout-btn"
                                            type="button"
                                            @click="logout"
                                            class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 hover:text-rose-700 dark:hover:text-rose-300 transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Logout
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </header>

            <!-- Scrollable Page Content -->
            <main class="flex-1 overflow-y-auto p-6 transition-colors duration-300">
                <slot />
            </main>

            <!-- Global Toast Notification -->
            <Toast :show="showToast" :type="toastType" :message="toastMessage" @close="showToast = false" />
        </div>
    </div>
</template>
