<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Avatar from '@/Components/UI/Avatar.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Pagination, { type PaginationLink } from '@/Components/UI/Pagination.vue';

interface SharedWishlist {
    id: number;
    name: string;
    user: {
        name: string;
        avatar_url: string;
    };
}

interface SharedBook {
    id: number;
    title: string;
    slug: string;
    cover_url: string;
    format: string;
    price: number;
    discount_price?: number;
    price_after_discount: number;
    stock_status: string;
    authors?: Array<{ name: string }>;
}

interface PaginatedItems {
    data: SharedBook[];
    links: PaginationLink[];
    total: number;
}

const props = defineProps<{
    wishlist: SharedWishlist;
    items: PaginatedItems;
}>();

function handleAddToCart(bookId: number) {
    router.post(route('cart.store'), {
        book_id: bookId,
        quantity: 1,
    }, { preserveScroll: true });
}
</script>

<template>
    <Head :title="`${wishlist.name} — Shared Wishlist`" />

    <MainLayout>
        <div class="bg-slate-50/50 min-h-screen py-10">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 font-sans">
                <!-- Header Banner -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-2xl shadow-sm border transition-colors bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white">
                    <div class="flex items-center gap-4">
                        <Avatar :name="wishlist.user.name" size="lg" />
                        <div>
                            <div class="flex items-center gap-2">
                                <Badge variant="brand" size="sm" class="uppercase tracking-wider text-[9px] font-extrabold">Public Wishlist</Badge>
                            </div>
                            <h1 class="text-2xl font-black font-heading tracking-tight mt-1 bg-gradient-to-r from-slate-900 to-slate-700 dark:from-white dark:to-zinc-300 bg-clip-text text-transparent">{{ wishlist.name }}</h1>
                            <p class="text-xs text-slate-500 dark:text-zinc-400 font-medium">
                                Shared by <span class="font-bold text-slate-900 dark:text-white">{{ wishlist.user.name }}</span> • {{ items.total }} book(s)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Items List Card -->
                <div class="border rounded-2xl p-6 shadow-sm space-y-4 transition-colors bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-slate-100">
                    <div v-if="items.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            v-for="book in items.data"
                            :key="book.id"
                            class="p-4 rounded-2xl border flex items-center justify-between gap-4 bg-slate-50/50 dark:bg-zinc-950/40 border-slate-200/80 dark:border-zinc-800 hover:border-slate-300 transition duration-300"
                        >
                            <div class="flex items-center gap-3.5 min-w-0">
                                <img
                                    :src="book.cover_url"
                                    :alt="book.title"
                                    class="w-12 h-16 object-cover rounded-xl border border-slate-200 dark:border-zinc-800 shrink-0 shadow-sm"
                                />
                                <div class="space-y-1 min-w-0">
                                    <Badge variant="brand" size="sm" class="capitalize text-[10px]">{{ book.format }}</Badge>
                                    <h4 class="text-xs font-bold font-heading text-slate-900 dark:text-white line-clamp-1">{{ book.title }}</h4>
                                    <div class="font-mono text-xs font-bold text-slate-900 dark:text-white">৳{{ book.price_after_discount }}</div>
                                </div>
                            </div>

                            <button
                                @click="handleAddToCart(book.id)"
                                class="px-3.5 py-2 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-sky-500/10 transition duration-300 transform active:scale-98 cursor-pointer flex items-center justify-center gap-1"
                            >
                                🛒 Add to Cart
                            </button>
                        </div>
                    </div>

                    <div v-else class="text-center py-16 text-slate-400">
                        This shared wishlist currently has no public books.
                    </div>

                    <div v-if="items.links.length > 3" class="pt-4 border-t border-slate-100 dark:border-zinc-800">
                        <Pagination :links="items.links" />
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
