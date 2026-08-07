<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useCartDrawer } from '@/Composables/useCartDrawer';
import CartItem from '@/Components/Cart/CartItem.vue';
import Button from '@/Components/UI/Button.vue';

const { isCartDrawerOpen, closeCartDrawer } = useCartDrawer();
const page = usePage();

const cart = computed(() => page.props.cart_summary as any);
const cartCount = computed(() => cart.value?.count || 0);
const items = computed(() => cart.value?.items || []);
const subtotal = computed(() => cart.value?.subtotal || 0);

// Free shipping threshold (৳1000)
const freeShippingThreshold = 1000;
const freeShippingProgress = computed(() => {
    return Math.min(100, Math.round((subtotal.value / freeShippingThreshold) * 100));
});
const amountNeededForFreeShipping = computed(() => {
    return Math.max(0, freeShippingThreshold - subtotal.value);
});

function handleClearCart() {
    router.delete(route('cart.clear'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop Blur -->
        <Transition
            enter-active-class="transition-opacity ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isCartDrawerOpen"
                @click="closeCartDrawer"
                class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm"
            />
        </Transition>

        <!-- Slide-out Drawer Panel -->
        <Transition
            enter-active-class="transition transform ease-out duration-300"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition transform ease-in duration-200"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <div
                v-if="isCartDrawerOpen"
                class="fixed inset-y-0 right-0 z-50 w-full max-w-md bg-white dark:bg-zinc-900 border-l border-slate-200 dark:border-zinc-800 shadow-2xl flex flex-col font-sans"
            >
                <!-- Drawer Header -->
                <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
                            🛒
                        </div>
                        <div>
                            <h3 class="text-base font-black font-heading text-slate-900 dark:text-white">Shopping Cart</h3>
                            <p class="text-[11px] text-slate-500 dark:text-zinc-400 font-semibold">{{ cartCount }} item(s) in cart</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="closeCartDrawer"
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-zinc-800 transition"
                    >
                        ✕
                    </button>
                </div>

                <!-- Free Shipping Target Progress Bar -->
                <div class="p-4 bg-slate-50 dark:bg-zinc-950/60 border-b border-slate-100 dark:border-zinc-800 space-y-1.5">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span v-if="amountNeededForFreeShipping > 0" class="text-slate-700 dark:text-zinc-300">
                            Add <span class="text-sky-600 font-mono">৳{{ amountNeededForFreeShipping }}</span> more for <span class="text-emerald-600">FREE Shipping!</span>
                        </span>
                        <span v-else class="text-emerald-600 flex items-center gap-1 font-bold">
                            🎉 You unlocked FREE Delivery!
                        </span>
                        <span class="font-mono text-slate-500">{{ freeShippingProgress }}%</span>
                    </div>

                    <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-zinc-800 overflow-hidden">
                        <div
                            class="h-full bg-gradient-to-r from-sky-500 to-emerald-500 transition-all duration-300"
                            :style="{ width: `${freeShippingProgress}%` }"
                        />
                    </div>
                </div>

                <!-- Drawer Scrollable Cart Items -->
                <div class="flex-1 overflow-y-auto p-5 space-y-3">
                    <template v-if="items.length > 0">
                        <CartItem
                            v-for="item in items"
                            :key="item.id"
                            :item="item"
                        />
                    </template>

                    <!-- Empty Cart State -->
                    <div v-else class="py-16 text-center space-y-4">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-zinc-800 mx-auto flex items-center justify-center text-3xl">
                            📚
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Your cart is empty</h4>
                            <p class="text-xs text-slate-500 dark:text-zinc-400">Discover great books and start adding to cart.</p>
                        </div>
                        <Link href="/catalog" @click="closeCartDrawer">
                            <Button variant="brand" size="sm" class="font-bold">
                                Browse Catalog
                            </Button>
                        </Link>
                    </div>
                </div>

                <!-- Drawer Footer Summary -->
                <div v-if="items.length > 0" class="p-5 border-t border-slate-100 dark:border-zinc-800 space-y-4 bg-white dark:bg-zinc-900">
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between text-slate-600 dark:text-zinc-400 font-semibold">
                            <span>Subtotal</span>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">৳{{ subtotal }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600 dark:text-zinc-400 font-semibold">
                            <span>Estimated Delivery</span>
                            <span v-if="amountNeededForFreeShipping === 0" class="text-emerald-600 font-bold">FREE</span>
                            <span v-else class="font-mono font-bold text-slate-900 dark:text-white">৳60</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            @click="handleClearCart"
                            class="px-4 py-3 rounded-xl border border-slate-200 dark:border-zinc-800 text-slate-500 dark:text-zinc-400 text-xs font-bold hover:bg-slate-50 dark:hover:bg-zinc-800/50 hover:text-rose-600 dark:hover:text-rose-400 transition cursor-pointer"
                        >
                            Clear
                        </button>

                        <Link href="/cart" @click="closeCartDrawer" class="flex-1">
                            <button
                                type="button"
                                class="w-full py-3 px-4 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white font-extrabold text-xs sm:text-sm rounded-xl shadow-lg shadow-sky-500/25 transition duration-300 transform active:scale-98 cursor-pointer flex items-center justify-center gap-1.5"
                            >
                                <span>View Full Cart & Checkout</span>
                                <span class="text-base font-normal">→</span>
                            </button>
                        </Link>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
