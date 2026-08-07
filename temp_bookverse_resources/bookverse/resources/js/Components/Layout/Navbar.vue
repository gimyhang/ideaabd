<script setup lang="ts">
import { ref, computed, defineAsyncComponent } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import Avatar from '@/Components/UI/Avatar.vue';
import Badge from '@/Components/UI/Badge.vue';
import NotificationBell from '@/Components/Notification/NotificationBell.vue';
import { useCartDrawer } from '@/Composables/useCartDrawer';

// Lazy load CartDrawer to optimize initial page bundle size
const CartDrawer = defineAsyncComponent(() => import('@/Components/Cart/CartDrawer.vue'));

const { openCartDrawer } = useCartDrawer();
const page = usePage();
const searchQuery = ref('');
const showUserDropdown = ref(false);
const showMobileMenu = ref(false);

const cartCount = computed(() => (page.props.cart_summary as any)?.count || 0);
const wishlistCount = computed(() => (page.props as any)?.wishlist_count || 0);

function handleLogout() {
    showUserDropdown.value = false;
    router.post('/logout');
}

function handleSearchRedirect() {
    if (searchQuery.value.trim()) {
        router.get('/catalog', { search: searchQuery.value });
        showMobileMenu.value = false;
    }
}
</script>

<template>
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <!-- Brand Logo -->
            <Link href="/" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-600 to-indigo-600 flex items-center justify-center shadow-md shadow-sky-600/15 ring-1 ring-white group-hover:scale-105 transition transform">
                    <span class="text-xl font-bold text-white tracking-wider">B</span>
                </div>
                <div class="hidden sm:block">
                    <span class="text-xl font-extrabold tracking-tight text-slate-900 font-heading">BookVerse</span>
                    <span class="block text-[10px] text-slate-500 tracking-wide font-medium">BOOKS & E-MAGAZINE</span>
                </div>
            </Link>

            <!-- Navigation Links (Desktop) -->
            <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-700">
                <Link href="/" class="hover:text-sky-600 transition">Home</Link>
                <Link href="/catalog" class="hover:text-sky-600 transition">Books</Link>
                <Link href="/magazine" class="hover:text-sky-600 transition">E-Magazine</Link>
                <Link href="/writers" class="hover:text-sky-600 transition">Writers</Link>
            </nav>

            <!-- Search Bar Placeholder -->
            <div class="hidden lg:flex items-center flex-1 max-w-xs relative">
                <input
                    type="text"
                    v-model="searchQuery"
                    @keyup.enter="handleSearchRedirect"
                    placeholder="Search books, authors..."
                    class="w-full text-xs bg-slate-100 text-slate-900 placeholder-slate-400 rounded-xl border border-slate-200 focus:border-sky-500 focus:bg-white focus:ring-1 focus:ring-sky-500 pl-9 pr-3 py-2 transition"
                />
                <svg class="w-4 h-4 text-slate-400 absolute left-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>

            <!-- Right Controls: Wishlist, Cart & Profile -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Wishlist Button -->
                <Link
                    href="/wishlist"
                    class="relative p-2 rounded-xl text-slate-600 hover:text-rose-600 hover:bg-slate-100 transition"
                    title="My Wishlists"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <span v-if="wishlistCount > 0" class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-rose-500 text-white font-bold text-[10px] flex items-center justify-center shadow-sm">
                        {{ wishlistCount }}
                    </span>
                </Link>

                <!-- Cart Button (Opens Slide-out Drawer) -->
                <button
                    type="button"
                    @click="openCartDrawer"
                    class="relative p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition"
                    title="Shopping Cart"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <span v-if="cartCount > 0" class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-sky-600 text-white font-bold text-[10px] flex items-center justify-center shadow-sm">
                        {{ cartCount }}
                    </span>
                </button>

                <!-- Notification Bell Icon (Authenticated Reader / Customer Alerts) -->
                <NotificationBell v-if="page.props.auth?.user" scope="frontend" />

                <!-- Auth Navigation / User Dropdown -->
                <template v-if="page.props.auth?.user">
                    <div class="relative">
                        <button
                            @click="showUserDropdown = !showUserDropdown"
                            class="flex items-center gap-2 p-1 rounded-xl hover:bg-slate-100 transition"
                        >
                            <Avatar :name="page.props.auth.user.name" size="sm" status="online" />
                        </button>

                        <div
                            v-if="showUserDropdown"
                            class="absolute right-0 mt-2 w-52 rounded-2xl bg-white border border-slate-200 shadow-xl py-2 z-50 text-xs font-medium space-y-1"
                        >
                            <!-- User Info Header -->
                            <div class="px-4 py-2 border-b border-slate-100">
                                <p class="font-bold text-slate-900 text-xs truncate">{{ page.props.auth.user.name }}</p>
                                <p class="text-[11px] text-slate-500 truncate">{{ page.props.auth.user.email }}</p>
                            </div>

                            <Link href="/profile" class="block px-4 py-2 text-slate-700 hover:text-slate-900 hover:bg-slate-50 font-semibold">My Profile</Link>
                            <Link href="/orders" class="block px-4 py-2 text-slate-700 hover:text-slate-900 hover:bg-slate-50">My Orders</Link>
                            <Link href="/notifications" class="block px-4 py-2 text-slate-700 hover:text-slate-900 hover:bg-slate-50">Notifications Center</Link>
                            <Link href="/wishlist" class="block px-4 py-2 text-slate-700 hover:text-slate-900 hover:bg-slate-50">My Wishlists</Link>
                            <Link
                                v-if="page.props.auth?.user?.is_admin"
                                href="/admin/dashboard"
                                class="flex items-center gap-2 px-4 py-2.5 text-sky-600 hover:text-sky-700 hover:bg-sky-50 font-bold border-t border-b border-slate-100 my-1 transition-all"
                            >
                                <svg class="w-4 h-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>{{ page.props.auth?.user?.panel_title || 'Admin Panel' }}</span>
                            </Link>

                            <!-- Logout Button -->
                            <div class="border-t border-slate-100 pt-1">
                                <button
                                    type="button"
                                    @click="handleLogout"
                                    class="w-full text-left px-4 py-2 text-rose-600 hover:bg-slate-50 font-semibold flex items-center gap-2 transition"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    <span>Log out</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <template v-else>
                    <Link href="/login" class="text-xs font-semibold text-slate-700 hover:text-slate-900 px-3 py-2 rounded-lg transition">Log in</Link>
                    <Link href="/register" class="text-xs font-semibold bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-xl transition shadow-sm">Sign up</Link>
                </template>

                <!-- Hamburger Menu Button (Mobile) -->
                <button
                    type="button"
                    @click="showMobileMenu = !showMobileMenu"
                    class="md:hidden p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition shrink-0 cursor-pointer"
                    title="Open Menu"
                >
                    <svg v-if="!showMobileMenu" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Off-Canvas Right Slide Mobile Drawer Overlay -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showMobileMenu" class="fixed inset-0 z-[100] flex justify-end">
                    <!-- Dark Backdrop overlay -->
                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showMobileMenu = false"></div>

                    <!-- Slide Panel -->
                    <Transition
                        enter-active-class="transition duration-300 ease-out transform"
                        enter-from-class="translate-x-full"
                        enter-to-class="translate-x-0"
                        leave-active-class="transition duration-200 ease-in transform"
                        leave-from-class="translate-x-0"
                        leave-to-class="translate-x-full"
                    >
                        <div v-if="showMobileMenu" class="relative z-10 w-80 max-w-[85vw] bg-white h-full shadow-2xl p-6 overflow-y-auto flex flex-col justify-between font-sans">
                            <div class="space-y-6">
                                <!-- Top Bar: Logo, Title & Close Button -->
                                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-sky-600 to-indigo-600 flex items-center justify-center text-white font-bold text-base shadow-md">
                                            B
                                        </div>
                                        <span class="text-base font-extrabold text-slate-900 font-heading">Menu</span>
                                    </div>
                                    <button
                                        type="button"
                                        @click="showMobileMenu = false"
                                        class="p-2 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Auth / User Action Button -->
                                <div v-if="!page.props.auth?.user">
                                    <Link
                                        href="/login"
                                        @click="showMobileMenu = false"
                                        class="w-full py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-lg shadow-emerald-600/20 flex items-center justify-center gap-2 transition"
                                    >
                                        <span>➔ Log in</span>
                                    </Link>
                                </div>
                                <div v-else class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center gap-3">
                                    <Avatar :src="page.props.auth.user.avatar_url" :name="page.props.auth.user.name" size="sm" />
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-xs text-slate-900 truncate">{{ page.props.auth.user.name }}</p>
                                        <p class="text-[10px] text-slate-500 truncate">{{ page.props.auth.user.email }}</p>
                                    </div>
                                </div>

                                <!-- Navigation Links Section -->
                                <div class="space-y-4">
                                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Menu Navigation</span>

                                    <nav class="space-y-1 text-xs font-bold text-slate-700">
                                        <Link
                                            href="/"
                                            @click="showMobileMenu = false"
                                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-100 hover:text-slate-900 transition"
                                        >
                                            <span class="text-base">🏠</span>
                                            <span>Home</span>
                                        </Link>
                                        <Link
                                            href="/catalog"
                                            @click="showMobileMenu = false"
                                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-100 hover:text-slate-900 transition"
                                        >
                                            <span class="text-base">📚</span>
                                            <span>Books</span>
                                        </Link>
                                        <Link
                                            href="/magazine"
                                            @click="showMobileMenu = false"
                                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-100 hover:text-slate-900 transition"
                                        >
                                            <span class="text-base">📰</span>
                                            <span>E-Magazine</span>
                                        </Link>
                                        <Link
                                            href="/writers"
                                            @click="showMobileMenu = false"
                                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-100 hover:text-slate-900 transition"
                                        >
                                            <span class="text-base">✍️</span>
                                            <span>Writers</span>
                                        </Link>

                                        <template v-if="page.props.auth?.user">
                                            <Link
                                                href="/orders"
                                                @click="showMobileMenu = false"
                                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-100 hover:text-slate-900 transition text-sky-700"
                                            >
                                                <span class="text-base">📦</span>
                                                <span>My Orders</span>
                                            </Link>
                                            <Link
                                                href="/profile"
                                                @click="showMobileMenu = false"
                                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-100 hover:text-slate-900 transition"
                                            >
                                                <span class="text-base">👤</span>
                                                <span>My Profile</span>
                                            </Link>
                                        </template>

                                        <Link
                                            href="/wishlist"
                                            @click="showMobileMenu = false"
                                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-100 hover:text-slate-900 transition"
                                        >
                                            <span class="text-base">💖</span>
                                            <span>Wishlist</span>
                                        </Link>
                                    </nav>
                                </div>

                                <!-- Mobile Search Input -->
                                <div class="relative pt-2">
                                    <input
                                        type="text"
                                        v-model="searchQuery"
                                        @keyup.enter="handleSearchRedirect"
                                        placeholder="Search books, authors..."
                                        class="w-full text-xs bg-slate-100 text-slate-900 placeholder-slate-400 rounded-xl border border-slate-200 focus:border-sky-500 focus:bg-white focus:ring-1 focus:ring-sky-500 pl-9 pr-3 py-2.5 transition"
                                    />
                                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Footer Contact Info -->
                            <div class="border-t border-slate-100 pt-4 mt-6 space-y-2 text-[11px] text-slate-500">
                                <span class="font-bold text-slate-400 block uppercase tracking-wider text-[10px]">Contact Info</span>
                                <div class="flex items-center gap-2">
                                    <span>📞</span>
                                    <span>+880 1641-413210</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span>✉️</span>
                                    <span class="truncate">support@bookverse.com</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span>📍</span>
                                    <span>Dhaka, Bangladesh</span>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- Async Loaded Cart Drawer -->
        <CartDrawer />
    </header>
</template>
