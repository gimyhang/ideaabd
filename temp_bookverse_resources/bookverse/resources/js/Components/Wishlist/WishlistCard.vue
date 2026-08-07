<script setup lang="ts">
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';

export interface WishlistBook {
    id: number;
    title: string;
    slug: string;
    cover_url: string;
    format: string;
    price: number;
    discount_price?: number;
    price_after_discount: number;
    stock_quantity: number;
    stock_status: 'In Stock' | 'Low Stock' | 'Out of Stock';
    authors?: Array<{ id: number; name: string }>;
}

const props = defineProps<{
    book: WishlistBook;
    selected?: boolean;
}>();

const emit = defineEmits<{
    (e: 'toggle-select', id: number): void;
    (e: 'move-to-cart', id: number): void;
    (e: 'remove', id: number): void;
}>();
</script>

<template>
    <div
        class="group p-4 rounded-2xl border transition-all duration-200 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-slate-100 hover:border-slate-300 dark:hover:border-zinc-700"
        :class="{ 'ring-2 ring-sky-500/50 border-sky-500': selected }"
    >
        <div class="flex items-center gap-3.5 min-w-0 flex-1">
            <!-- Select Checkbox -->
            <input
                type="checkbox"
                :checked="selected"
                @change="emit('toggle-select', book.id)"
                class="w-4 h-4 rounded border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-sky-600 focus:ring-sky-500/20 cursor-pointer"
            />

            <!-- Book Cover -->
            <img
                :src="book.cover_url"
                :alt="book.title"
                class="w-14 h-20 object-cover rounded-xl border border-slate-200 dark:border-zinc-800 shrink-0 shadow"
            />

            <!-- Details -->
            <div class="space-y-1 min-w-0">
                <div class="flex items-center gap-2">
                    <Badge variant="brand" size="sm" class="capitalize text-[10px]">{{ book.format }}</Badge>
                    <span
                        class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                        :class="{
                            'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400': book.stock_status === 'In Stock',
                            'bg-amber-500/15 text-amber-600 dark:text-amber-400': book.stock_status === 'Low Stock',
                            'bg-rose-500/15 text-rose-600 dark:text-rose-400': book.stock_status === 'Out of Stock'
                        }"
                    >
                        {{ book.stock_status }}
                    </span>
                </div>

                <h4 class="text-sm font-bold font-heading text-slate-900 dark:text-white line-clamp-1">
                    {{ book.title }}
                </h4>

                <p class="text-xs text-slate-500 dark:text-zinc-400 line-clamp-1">
                    {{ book.authors?.[0]?.name || 'Unknown Author' }}
                </p>

                <div class="flex items-center gap-2 font-mono text-xs pt-0.5">
                    <span class="font-black text-slate-900 dark:text-white">৳{{ book.price_after_discount }}</span>
                    <span v-if="book.discount_price && book.price > book.price_after_discount" class="text-[10px] line-through text-slate-400">
                        ৳{{ book.price }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
            <Button
                variant="brand"
                size="sm"
                class="gap-1.5 font-bold text-xs shadow-sm"
                :disabled="book.stock_status === 'Out of Stock'"
                @click="emit('move-to-cart', book.id)"
            >
                <span>Move to Cart</span>
            </Button>

            <button
                type="button"
                @click="emit('remove', book.id)"
                class="p-2 rounded-xl text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition"
                title="Remove from Wishlist"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    </div>
</template>
