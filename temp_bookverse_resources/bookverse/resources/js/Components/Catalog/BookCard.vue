<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useCartDrawer } from '@/Composables/useCartDrawer';

export interface CatalogBook {
    id: number;
    title: string;
    slug: string;
    sku: string;
    isbn_13?: string;
    summary?: string;
    format: string;
    price: number;
    discount_price?: number;
    price_after_discount: number;
    discount_percentage: number;
    stock_quantity: number;
    stock_status: 'In Stock' | 'Low Stock' | 'Out of Stock';
    is_featured: boolean;
    is_bestseller: boolean;
    cover_url: string;
    created_at: string;
    authors?: Array<{ id: number; name: string; pivot?: { is_primary: boolean } }>;
    publisher?: { id: number; name: string };
    category?: { id: number; name: string };
}

const props = defineProps<{
    book: CatalogBook;
    isWishlisted?: boolean;
}>();

const page = usePage();
const { openCartDrawer } = useCartDrawer();
const isAddingToCart = ref(false);
const isTogglingWishlist = ref(false);
const currentCoverSrc = ref(props.book.cover_url);

const primaryAuthorName = computed(() => {
    if (!props.book.authors || props.book.authors.length === 0) return '';
    const primary = props.book.authors.find(a => a.pivot?.is_primary);
    return primary ? primary.name : props.book.authors[0].name;
});

function handleImageError() {
    currentCoverSrc.value = `https://ui-avatars.com/api/?name=${encodeURIComponent(props.book.title)}&color=0284c7&background=e0f2fe&size=512`;
}

function handleAddToCart() {
    if (props.book.stock_quantity <= 0 || isAddingToCart.value) return;
    isAddingToCart.value = true;

    router.post(route('cart.store'), {
        book_id: props.book.id,
        quantity: 1,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isAddingToCart.value = false;
            openCartDrawer();
        },
        onError: () => {
            isAddingToCart.value = false;
        },
    });
}

function handleToggleWishlist() {
    const user = page.props.auth?.user;
    if (!user) {
        router.get(route('login'));
        return;
    }

    if (isTogglingWishlist.value) return;
    isTogglingWishlist.value = true;

    router.post(route('wishlist.add-default'), {
        book_id: props.book.id,
    }, {
        preserveScroll: true,
        onFinish: () => {
            isTogglingWishlist.value = false;
        },
    });
}
</script>

<template>
    <div class="group relative rounded-lg bg-white border border-slate-200/90 hover:border-sky-400 shadow-2xs hover:shadow-md transition-all duration-300 flex flex-col justify-between overflow-hidden p-2.5 text-center">

        <!-- Top Discount Badge & Wishlist Button (BookVerse Brand Blue) -->
        <div class="relative w-full flex items-center justify-between z-10 mb-1">
            <!-- Discount Badge -->
            <span v-if="book.discount_percentage > 0" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                {{ book.discount_percentage }}% ছাড়
            </span>
            <span v-else class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">
                {{ book.format }}
            </span>

            <!-- Wishlist Heart Button -->
            <button
                type="button"
                @click.stop.prevent="handleToggleWishlist"
                :disabled="isTogglingWishlist"
                class="w-6 h-6 rounded-full bg-white hover:bg-sky-50 text-sky-600 shadow-2xs border border-slate-200 flex items-center justify-center shrink-0 cursor-pointer transition"
                title="Add to Wishlist"
            >
                <svg class="w-3 h-3" :fill="isWishlisted ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </button>
        </div>

        <!-- Book Cover Container with Overlay Add To Cart Button -->
        <div class="relative w-full flex justify-center py-1 group/cover">
            <Link :href="route('books.show', book.slug)" class="block overflow-hidden">
                <img
                    :src="currentCoverSrc"
                    :alt="book.title"
                    loading="lazy"
                    @error="handleImageError"
                    class="h-[140px] w-auto max-w-[110px] object-contain group-hover:scale-105 transition-transform duration-300 bg-white"
                />
            </Link>

            <!-- Floating Overlay Add to Cart Button (Does NOT expand card height at bottom!) -->
            <div class="absolute bottom-1 inset-x-1 z-20 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-1 group-hover:translate-y-0">
                <button
                    @click.stop.prevent="handleAddToCart"
                    :disabled="book.stock_quantity <= 0 || isAddingToCart"
                    class="w-full py-1.5 px-2 rounded bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-md transition text-center cursor-pointer flex items-center justify-center gap-1 disabled:opacity-80 disabled:bg-slate-400"
                >
                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" class="w-3.5 h-3.5">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <span>{{ isAddingToCart ? 'Adding...' : (book.stock_quantity > 0 ? 'Add To Cart' : 'Out of Stock') }}</span>
                </button>
            </div>
        </div>

        <!-- Book Information (Title, Author, Prices) -->
        <div class="space-y-1 mt-1.5">
            <!-- Book Title -->
            <Link :href="route('books.show', book.slug)" class="block">
                <h4 class="font-bold text-xs text-slate-800 line-clamp-1 group-hover:text-sky-600 transition-colors leading-snug" :title="book.title">
                    {{ book.title }}
                </h4>
            </Link>

            <!-- Author Name -->
            <p v-if="primaryAuthorName" class="text-[11px] text-slate-400 font-normal line-clamp-1">
                {{ primaryAuthorName }}
            </p>

            <!-- Price Row Centered -->
            <div class="flex items-center justify-center gap-1.5 pt-0.5 font-sans">
                <del v-if="book.discount_price && book.discount_price < book.price" class="text-[11px] text-slate-400 font-normal">
                    ৳ {{ book.price }}
                </del>
                <span class="text-xs font-extrabold text-slate-900">
                    ৳ {{ book.price_after_discount }}
                </span>
            </div>
        </div>
    </div>
</template>
