<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import QuantityStepper from '@/Components/UI/QuantityStepper.vue';
import Badge from '@/Components/UI/Badge.vue';

export interface CartItemType {
    id: number;
    book_id: number;
    quantity: number;
    unit_price: number;
    total_price: number;
    book: {
        id: number;
        title: string;
        cover_url: string;
        format: string;
        price: number;
        discount_price?: number;
    };
}

const props = defineProps<{
    item: CartItemType;
}>();

function handleQuantityChange(newQty: number) {
    router.patch(route('cart.update', props.item.id), {
        quantity: newQty,
    }, {
        preserveScroll: true,
        preserveState: true,
    });
}

function handleRemove() {
    router.delete(route('cart.destroy', props.item.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <div class="flex items-center gap-3.5 p-3.5 rounded-2xl border transition-colors bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 shadow-sm">
        <!-- Book Cover Thumbnail -->
        <img
            :src="item.book.cover_url"
            :alt="item.book.title"
            class="w-12 h-16 object-cover rounded-xl border border-slate-200 dark:border-zinc-800 shrink-0 shadow-sm"
        />

        <!-- Title, Format & Pricing -->
        <div class="flex-1 min-w-0 space-y-1">
            <div class="flex items-center gap-1.5">
                <Badge variant="brand" size="sm" class="capitalize text-[10px] py-0 px-1.5">{{ item.book.format }}</Badge>
            </div>

            <h4 class="text-xs font-bold font-heading text-slate-900 dark:text-white line-clamp-1">
                {{ item.book.title }}
            </h4>

            <div class="flex items-center gap-2 font-mono text-xs">
                <span class="font-black text-slate-900 dark:text-white">৳{{ item.unit_price }}</span>
                <span v-if="item.book.discount_price && item.book.price > item.unit_price" class="text-[10px] line-through text-slate-400">
                    ৳{{ item.book.price }}
                </span>
            </div>
        </div>

        <!-- Quantity & Remove -->
        <div class="flex flex-col items-end gap-2 shrink-0">
            <QuantityStepper
                :model-value="item.quantity"
                @change="handleQuantityChange"
            />

            <button
                type="button"
                @click="handleRemove"
                class="p-1.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition cursor-pointer"
                title="Remove item"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </div>
    </div>
</template>
