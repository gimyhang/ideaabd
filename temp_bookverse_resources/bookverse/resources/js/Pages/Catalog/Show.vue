<script setup lang="ts">
import { ref, computed, defineAsyncComponent, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import BookCard, { type CatalogBook } from '@/Components/Catalog/BookCard.vue';
import Badge from '@/Components/UI/Badge.vue';
import StarRating from '@/Components/UI/StarRating.vue';
import StarRatingInput from '@/Components/UI/StarRatingInput.vue';
import RatingDistribution from '@/Components/UI/RatingDistribution.vue';
import ReviewCard from '@/Components/Reviews/ReviewCard.vue';
import ReviewForm from '@/Components/Reviews/ReviewForm.vue';
import { useCartDrawer } from '@/Composables/useCartDrawer';

// Lazy load BookPreviewModal
const BookPreviewModal = defineAsyncComponent(() => import('@/Components/Catalog/BookPreviewModal.vue'));

// ─── Types ───────────────────────────────────────────────────────────────────

interface Author {
    id: number;
    name: string;
    slug: string;
    bio?: string;
    photo_url?: string;
    pivot?: { is_primary: boolean };
}

interface Publisher {
    id: number;
    name: string;
    slug: string;
    logo_url?: string;
    website?: string;
}

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface Book {
    id: number;
    title: string;
    slug: string;
    sku: string;
    isbn_13?: string;
    summary?: string;
    description?: string;
    format: string;
    language?: string;
    edition?: string;
    page_count?: number;
    published_year?: number;
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
    preview_type?: 'none' | 'images' | 'pdf';
    max_preview_pages?: number;
    sample_pages_urls?: string[];
    has_sample_preview?: boolean;
    authors: Author[];
    publisher?: Publisher;
    category?: Category;
    meta_title?: string;
    meta_description?: string;
}

interface Seo {
    meta_title: string;
    meta_description: string;
}

interface ReviewItem {
    id: number;
    rating: number;
    title: string;
    body: string;
    photo_url: string | null;
    helpful_count: number;
    is_verified_purchase: boolean;
    created_at: string;
    user_name: string;
    is_own: boolean;
}

interface PaginatedReviews {
    data: ReviewItem[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
}

interface UserReview {
    id: number;
    rating: number;
    title: string;
    body: string;
    photo_url: string | null;
    helpful_count?: number;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    book: Book;
    relatedBooks: CatalogBook[];
    seo: Seo;
    sample_pdf_signed_url?: string | null;
    average_rating: number;
    reviews_count: number;
    rating_distribution: Record<number, number>;
    reviews: PaginatedReviews;
    user_review: UserReview | null;
    can_review: boolean;
    sort?: string;
}>();

// ─── State ────────────────────────────────────────────────────────────────────

const activeTab = ref<'description' | 'specs' | 'authors'>('description');
const isExpanded = ref(false);
const quantity = ref(1);
const showPreviewModal = ref(false);
const isAddingToCart = ref(false);
const isBuyingNow = ref(false);
const { openCartDrawer } = useCartDrawer();

// ─── Computed ─────────────────────────────────────────────────────────────────

const primaryAuthor = computed(() => {
    if (!props.book.authors || props.book.authors.length === 0) return null;
    return props.book.authors.find(a => a.pivot?.is_primary) ?? props.book.authors[0];
});

const languageLabel: Record<string, string> = {
    bn: 'বাংলা',
    en: 'English',
    ar: 'Arabic',
    hi: 'Hindi',
};

// JSON-LD SEO Schema
const jsonLdSchema = computed(() => {
    return JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'Book',
        'name': props.book.title,
        'isbn': props.book.isbn_13,
        'image': props.book.cover_url,
        'author': primaryAuthor.value ? { '@type': 'Person', 'name': primaryAuthor.value.name } : undefined,
        'publisher': props.book.publisher ? { '@type': 'Organization', 'name': props.book.publisher.name } : undefined,
        'offers': {
            '@type': 'Offer',
            'price': props.book.price_after_discount,
            'priceCurrency': 'BDT',
            'availability': props.book.stock_quantity > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        },
    });
});

function handleAddToCart() {
    if (props.book.stock_quantity <= 0 || isAddingToCart.value) return;
    isAddingToCart.value = true;

    router.post(route('cart.store'), {
        book_id: props.book.id,
        quantity: quantity.value,
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

function handleBuyNow() {
    if (props.book.stock_quantity <= 0 || isBuyingNow.value) return;
    isBuyingNow.value = true;

    router.post(route('cart.store'), {
        book_id: props.book.id,
        quantity: quantity.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isBuyingNow.value = false;
            router.get('/cart');
        },
    });
}

function openLookInsidePreview() {
    showPreviewModal.value = true;
}

// ─── Review Edit State ────────────────────────────────────────────────────────
const editingReview = ref<UserReview | null>(null);
function startEdit(review: any) {
    editingReview.value = review;
}
function cancelEdit() {
    editingReview.value = null;
}

function handleSortChange(e: Event) {
    const val = (e.target as HTMLSelectElement).value;
    router.get(
        `/books/${props.book.slug}`,
        { sort: val },
        { preserveScroll: true, replace: true }
    );
}

onMounted(() => {
    if (window.location.hash) {
        const hash = window.location.hash;
        setTimeout(() => {
            const el = document.querySelector(hash);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth' });
            }
        }, 300);
    }
});

function handleAddToWishlist() {
    if (!props.book.id) return;
    router.post(route('wishlist.add-default'), {
        book_id: props.book.id,
    }, {
        preserveScroll: true,
    });
}

function handleShare() {
    if (navigator.share) {
        navigator.share({
            title: props.book.title,
            url: window.location.href,
        }).catch(() => {});
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('Book link copied to clipboard!');
    }
}
</script>

<template>
    <Head :title="seo.meta_title">
        <meta name="description" :content="seo.meta_description" />
        <component :is="'script'" type="application/ld+json" v-html="jsonLdSchema" />
    </Head>

    <MainLayout>
        <div class="bg-slate-50/50 min-h-screen py-6 font-sans pb-32 md:pb-12">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-6">

                <!-- Breadcrumb Bar -->
                <nav class="flex items-center gap-2 text-xs text-slate-500 font-medium overflow-x-auto py-1">
                    <Link href="/" class="hover:text-sky-600 transition-colors shrink-0">Home</Link>
                    <span>/</span>
                    <Link href="/catalog" class="hover:text-sky-600 transition-colors shrink-0">Catalog</Link>
                    <template v-if="book.category">
                        <span>/</span>
                        <Link :href="`/catalog?category=${book.category.slug}`" class="hover:text-sky-600 transition-colors shrink-0">
                            {{ book.category.name }}
                        </Link>
                    </template>
                    <span>/</span>
                    <span class="text-slate-900 font-semibold truncate">{{ book.title }}</span>
                </nav>

                <!-- Rokomari Exact 1:1 Book Detail Card -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">

                        <!-- LEFT COLUMN: Book Cover Box & Want to Read Dropdown -->
                        <div class="md:col-span-4 flex flex-col items-center">
                            <!-- Rokomari 1:1 Cover Outer Box -->
                            <div class="w-full border border-slate-200/90 rounded-2xl p-5 bg-white relative flex flex-col items-center justify-center min-h-[380px] shadow-2xs hover:shadow-xs transition duration-300">

                                <!-- Top Right "Look Inside ↓" Text Link -->
                                <button
                                    v-if="book.has_sample_preview"
                                    @click="openLookInsidePreview"
                                    class="absolute top-4 right-4 text-rose-600 hover:text-rose-700 font-extrabold text-xs sm:text-sm flex items-center gap-1 cursor-pointer transition transform hover:scale-105"
                                >
                                    <span>Look Inside</span>
                                    <span class="text-base font-normal">↓</span>
                                </button>

                                <!-- Top Left OFF Starburst Stamp Badge -->
                                <div
                                    v-if="book.discount_percentage > 0"
                                    class="absolute top-4 left-4 z-10 w-12 h-12 rounded-full bg-rose-600 text-white flex flex-col items-center justify-center text-[10px] font-extrabold shadow-md leading-none ring-2 ring-white"
                                    style="clip-path: polygon(50% 0%, 61% 11%, 75% 3%, 79% 18%, 93% 18%, 91% 33%, 100% 43%, 93% 55%, 98% 69%, 87% 76%, 86% 91%, 71% 90%, 64% 100%, 50% 93%, 36% 100%, 29% 90%, 14% 91%, 13% 76%, 2% 69%, 7% 55%, 0% 43%, 9% 33%, 7% 18%, 21% 18%, 25% 3%, 39% 11%);"
                                >
                                    <span class="text-[11px] font-black">{{ book.discount_percentage }}%</span>
                                    <span class="text-[9px] uppercase tracking-tighter mt-0.5">OFF</span>
                                </div>

                                <!-- Cover Image -->
                                <img
                                    :src="book.cover_url"
                                    :alt="`${book.title} - Cover`"
                                    class="max-h-[300px] w-auto object-contain cursor-pointer mt-6 transition duration-300 hover:scale-102"
                                    @click="openLookInsidePreview"
                                />
                            </div>

                            <!-- Want to read Button -->
                            <div class="w-full mt-3">
                                <button class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200/80 text-slate-700 font-bold text-xs flex items-center justify-center gap-2 rounded-xl transition cursor-pointer">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                    </svg>
                                    <span>Want to Read</span>
                                </button>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: Book Details & Buying Box -->
                        <div class="md:col-span-8 space-y-5 text-left">

                            <!-- Book Title -->
                            <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-slate-900 leading-tight">
                                {{ book.title }}
                            </h1>

                            <!-- Author & Publisher Line -->
                            <div class="text-xs text-slate-500 font-medium">
                                <span>by </span>
                                <Link v-if="primaryAuthor" :href="`/catalog?authors=${primaryAuthor.slug}`" class="text-sky-600 font-bold hover:underline">
                                    {{ primaryAuthor.name }}
                                </Link>
                                <template v-if="book.publisher">
                                    <span class="mx-2 text-slate-300">|</span>
                                    <Link :href="`/catalog?publisher=${book.publisher.slug}`" class="text-sky-600 font-bold hover:underline">
                                        {{ book.publisher.name }}
                                    </Link>
                                </template>
                            </div>

                            <!-- Bestseller Badge & Category -->
                            <div class="flex items-center gap-2 text-xs flex-wrap">
                                <span v-if="book.is_bestseller" class="px-2.5 py-1 rounded bg-amber-50 border border-amber-200 text-amber-800 font-bold text-[11px] flex items-center gap-1">
                                    🔥 #1 Best Seller
                                </span>
                                <span v-if="book.category" class="text-slate-500 font-medium">
                                    in <Link :href="`/catalog?category=${book.category.slug}`" class="text-sky-600 font-bold hover:underline">{{ book.category.name }}</Link>
                                </span>
                            </div>

                            <!-- Ratings & Reviews Line -->
                            <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
                                <StarRating :rating="average_rating" size="sm" />
                                <span class="font-bold text-slate-700">{{ average_rating.toFixed(1) }}</span>
                                <span class="text-slate-300">|</span>
                                <span>{{ reviews_count }} Customer Reviews</span>
                            </div>

                            <!-- Social Proof Line -->
                            <div class="text-[11px] text-slate-500 font-medium flex items-center gap-1.5 bg-slate-100 px-3 py-1.5 rounded-xl w-fit">
                                <span>❤️ Added to wishlist by {{ book.is_featured ? '3.5K+' : '2.7K+' }} readers</span>
                            </div>

                            <!-- Price Row -->
                            <div class="flex items-baseline gap-2 pt-1">
                                <span class="text-3xl font-black text-rose-600 font-mono">
                                    ৳{{ book.price_after_discount }}
                                </span>
                                <span v-if="book.discount_percentage > 0" class="text-sm font-semibold font-mono text-slate-400 line-through">
                                    ৳{{ book.price }}
                                </span>
                                <span v-if="book.discount_percentage > 0" class="text-xs font-bold text-rose-500 bg-rose-50 px-2 py-0.5 rounded-md">
                                    ({{ book.discount_percentage }}% OFF)
                                </span>
                            </div>
                            <!-- Short Description -->
                            <div class="text-xs text-slate-600 leading-relaxed pt-1">
                                <p :class="isExpanded ? '' : 'line-clamp-3'">
                                    {{ book.summary || book.description || 'A fascinating book about human life. Read it on BookVerse today.' }}
                                </p>
                                <button
                                    v-if="book.summary || book.description"
                                    @click="isExpanded = !isExpanded"
                                    class="text-sky-600 font-bold hover:underline mt-1 cursor-pointer"
                                >
                                    {{ isExpanded ? '... Read less' : '... Read more' }}
                                </button>
                            </div>

                            <!-- Stock Status Line -->
                            <div class="flex items-center gap-2 text-xs font-bold text-emerald-600 pt-1">
                                <span>🟢 In Stock ({{ book.stock_quantity }}+ copies available)</span>
                            </div>

                            <!-- Main Buy & Cart Buttons Row -->
                            <div class="flex items-center gap-3 pt-2">
                                <button
                                    @click="handleBuyNow"
                                    :disabled="book.stock_quantity <= 0 || isBuyingNow"
                                    class="flex-1 bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold py-3 px-6 rounded-xl text-xs sm:text-sm shadow-md shadow-amber-500/10 transition text-center cursor-pointer disabled:opacity-50"
                                >
                                    {{ isBuyingNow ? 'Processing...' : 'Buy Now' }}
                                </button>
                                <button
                                    @click="handleAddToCart"
                                    :disabled="book.stock_quantity <= 0 || isAddingToCart"
                                    class="flex-1 bg-sky-600 hover:bg-sky-700 text-white font-extrabold py-3 px-6 rounded-xl text-xs sm:text-sm shadow-md shadow-sky-600/10 transition text-center cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                    <span>Add to Cart</span>
                                </button>
                            </div>

                            <!-- Bottom Wishlist & Share Links -->
                            <div class="flex items-center gap-6 pt-3 text-xs text-slate-600 font-medium">
                                <button @click="handleAddToWishlist" class="hover:text-sky-600 transition flex items-center gap-1.5 cursor-pointer">
                                    <span>♡</span>
                                    <span>Add to Wishlist</span>
                                </button>
                                <button @click="handleShare" class="hover:text-sky-600 transition flex items-center gap-1.5 cursor-pointer">
                                    <span>🔗</span>
                                    <span>Share with Friends</span>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Tabs Section: Details, Specs, Authors -->
                <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-6">
                    <div class="flex items-center gap-8 border-b border-slate-100 font-heading text-sm font-bold">
                        <button
                            @click="activeTab = 'description'"
                            class="pb-3 transition-colors relative cursor-pointer"
                            :class="activeTab === 'description' ? 'text-sky-600 border-b-2 border-sky-600 font-extrabold' : 'text-slate-400 hover:text-slate-700'"
                        >
                            📖 Description
                        </button>
                        <button
                            @click="activeTab = 'specs'"
                            class="pb-3 transition-colors relative cursor-pointer"
                            :class="activeTab === 'specs' ? 'text-sky-600 border-b-2 border-sky-600 font-extrabold' : 'text-slate-400 hover:text-slate-700'"
                        >
                            📋 Specifications
                        </button>
                        <button
                            @click="activeTab = 'authors'"
                            class="pb-3 transition-colors relative cursor-pointer"
                            :class="activeTab === 'authors' ? 'text-sky-600 border-b-2 border-sky-600 font-extrabold' : 'text-slate-400 hover:text-slate-700'"
                        >
                            ✍️ Author Bio
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div class="text-left">
                        <div v-if="activeTab === 'description'" class="prose prose-slate max-w-none text-slate-700 text-xs leading-relaxed">
                            <p v-if="book.summary" class="text-sm font-semibold text-slate-900 italic mb-4">
                                {{ book.summary }}
                            </p>
                            <div v-if="book.description" class="whitespace-pre-line">
                                {{ book.description }}
                            </div>
                        </div>

                        <div v-else-if="activeTab === 'specs'" class="max-w-xl text-xs space-y-3">
                            <div class="grid grid-cols-2 py-2 border-b border-slate-100">
                                <span class="font-bold text-slate-400">SKU</span>
                                <span class="font-mono text-slate-900 font-semibold">{{ book.sku }}</span>
                            </div>
                            <div v-if="book.isbn_13" class="grid grid-cols-2 py-2 border-b border-slate-100">
                                <span class="font-bold text-slate-400">ISBN-13</span>
                                <span class="font-mono text-slate-900 font-semibold">{{ book.isbn_13 }}</span>
                            </div>
                            <div class="grid grid-cols-2 py-2 border-b border-slate-100">
                                <span class="font-bold text-slate-400">Format</span>
                                <span class="text-slate-900 font-semibold uppercase">{{ book.format }}</span>
                            </div>
                            <div v-if="book.language" class="grid grid-cols-2 py-2 border-b border-slate-100">
                                <span class="font-bold text-slate-400">Language</span>
                                <span class="text-slate-900 font-semibold">{{ languageLabel[book.language] || book.language }}</span>
                            </div>
                            <div v-if="book.page_count" class="grid grid-cols-2 py-2 border-b border-slate-100">
                                <span class="font-bold text-slate-400">Pages</span>
                                <span class="text-slate-900 font-semibold">{{ book.page_count }}</span>
                            </div>
                            <div v-if="book.published_year" class="grid grid-cols-2 py-2 border-b border-slate-100">
                                <span class="font-bold text-slate-400">Published</span>
                                <span class="text-slate-900 font-semibold">{{ book.published_year }}</span>
                            </div>
                        </div>

                        <div v-else-if="activeTab === 'authors'" class="space-y-6">
                            <div v-if="primaryAuthor" class="flex items-start gap-4">
                                <img
                                    v-if="primaryAuthor.photo_url"
                                    :src="primaryAuthor.photo_url"
                                    :alt="primaryAuthor.name"
                                    class="w-16 h-16 rounded-full object-cover shadow-sm border border-slate-200"
                                />
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm font-heading">{{ primaryAuthor.name }}</h4>
                                    <p v-if="primaryAuthor.bio" class="text-xs text-slate-600 mt-1 leading-relaxed max-w-2xl">
                                        {{ primaryAuthor.bio }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Books Section -->
                <div v-if="relatedBooks && relatedBooks.length > 0" class="space-y-6 text-left">
                    <h3 class="text-xl font-bold font-heading text-slate-900">Related Books</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <BookCard v-for="b in relatedBooks" :key="b.id" :book="b" />
                    </div>
                </div>

                <!-- ══════ REVIEWS SECTION ══════ -->
                <div id="reviews" class="space-y-8 text-left">

                    <!-- Rating Summary -->
                    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
                        <h2 class="text-xl font-bold font-heading text-slate-900 mb-5">Customer Reviews</h2>
                        <div class="flex flex-col sm:flex-row gap-8 items-start">
                            <!-- Big Average Number -->
                            <div class="text-center shrink-0">
                                <p class="text-6xl font-black text-slate-900">{{ average_rating.toFixed(1) }}</p>
                                <StarRating :rating="average_rating" size="lg" class="justify-center mt-1" />
                                <p class="text-xs text-slate-400 mt-1">{{ reviews_count }} review{{ reviews_count !== 1 ? 's' : '' }}</p>
                            </div>
                            <!-- Distribution Bars -->
                            <div class="flex-1 w-full">
                                <RatingDistribution :distribution="rating_distribution" :total="reviews_count" />
                            </div>
                        </div>
                    </div>

                    <!-- Write / Edit Review Section -->
                    <div id="write-review" class="space-y-4">
                        <!-- User already reviewed (show their review + edit form toggle) -->
                        <template v-if="user_review">
                            <div id="my-review" class="flex items-center justify-between">
                                <h3 class="text-base font-bold text-slate-900">Your Review</h3>
                                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                                    ✅ Reviewed & Verified
                                </span>
                            </div>
                            <ReviewCard
                                v-if="!editingReview"
                                :review="{ ...user_review, helpful_count: user_review.helpful_count || 0, is_verified_purchase: true, created_at: 'Your review', is_own: true, user_name: 'You' }"
                                @edit="startEdit"
                            />
                            <ReviewForm
                                v-else
                                :book-id="book.id"
                                :book-slug="book.slug"
                                :existing="editingReview"
                                @cancel="cancelEdit"
                            />
                        </template>

                        <!-- Can write a new review -->
                        <template v-else-if="can_review">
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-bold text-slate-900">Write a Verified Review</h3>
                                <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200">
                                    ⭐ Pending Review
                                </span>
                            </div>
                            <ReviewForm :book-id="book.id" :book-slug="book.slug" />
                        </template>

                        <!-- Not eligible -->
                        <template v-else>
                            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 text-center">
                                <p class="text-sm text-slate-500">
                                    <span class="font-semibold text-slate-700">Have you read this book?</span>
                                    Purchase and receive it to leave a Verified Purchase review.
                                </p>
                            </div>
                        </template>
                    </div>

                    <!-- Review List -->
                    <div id="reviews" class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 v-if="reviews.data.length > 0" class="text-base font-bold text-slate-900">
                                {{ reviews.total }} Review{{ reviews.total !== 1 ? 's' : '' }}
                            </h3>

                            <!-- Review Sort Dropdown -->
                            <div v-if="reviews.total > 1" class="flex items-center gap-2 text-xs">
                                <span class="text-slate-400 font-medium">Sort by:</span>
                                <select
                                    :value="sort || 'newest'"
                                    class="text-xs font-semibold bg-white border border-slate-200 rounded-xl px-3 py-1.5 text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500/20 shadow-xs cursor-pointer"
                                    @change="handleSortChange"
                                >
                                    <option value="newest">🕒 Newest First</option>
                                    <option value="helpful">👍 Most Helpful</option>
                                    <option value="highest">⭐ Highest Rating</option>
                                    <option value="lowest">🔻 Lowest Rating</option>
                                </select>
                            </div>
                        </div>

                        <ReviewCard
                            v-for="review in reviews.data"
                            :key="review.id"
                            :review="review"
                            @edit="startEdit"
                        />

                        <p v-if="reviews.data.length === 0" class="text-sm text-slate-400 text-center py-6">
                            No reviews yet. Be the first to share your thoughts!
                        </p>

                        <!-- Pagination -->
                        <div v-if="reviews.last_page > 1" class="flex flex-wrap gap-2 justify-center pt-2">
                            <template v-for="link in reviews.links" :key="link.label">
                                <button
                                    v-if="link.url"
                                    class="px-3 py-1.5 text-xs rounded-lg border transition cursor-pointer"
                                    :class="link.active
                                        ? 'bg-sky-600 text-white border-sky-600 font-bold'
                                        : 'border-slate-200 text-slate-600 hover:bg-slate-100'"
                                    v-html="link.label"
                                    @click="router.get(link.url, {}, { preserveScroll: true })"
                                />
                            </template>
                        </div>
                    </div>

                </div>
                <!-- ══════ END REVIEWS SECTION ══════ -->

            </div>
        </div>

        <!-- Mobile Sticky Bottom Action Bar (Phone Friendly) -->
        <div class="md:hidden fixed bottom-16 left-0 right-0 z-30 bg-white/95 border-t border-slate-200/80 p-3 flex items-center justify-between gap-4 backdrop-blur-md shadow-lg">
            <div class="flex items-center gap-2.5 min-w-0">
                <img :src="book.cover_url" :alt="book.title" class="w-10 h-12 object-contain rounded border border-slate-200 bg-slate-50 shrink-0" />
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-900 truncate">{{ book.title }}</p>
                    <p class="text-[11px] font-bold text-rose-600 font-mono">৳{{ book.price_after_discount }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button
                    @click="handleAddToCart"
                    :disabled="book.stock_quantity <= 0 || isAddingToCart"
                    class="bg-sky-600 active:bg-sky-700 disabled:opacity-50 text-white font-bold px-3 py-2 rounded-xl text-[10px] flex items-center gap-1 cursor-pointer transition shadow-md shadow-sky-600/10"
                >
                    <span>Cart</span>
                </button>
                <button
                    @click="handleBuyNow"
                    :disabled="book.stock_quantity <= 0 || isBuyingNow"
                    class="bg-amber-500 active:bg-amber-600 disabled:opacity-50 text-slate-950 font-bold px-3 py-2 rounded-xl text-[10px] cursor-pointer transition shadow-md shadow-amber-500/10"
                >
                    <span>Buy Now</span>
                </button>
            </div>
        </div>

        <!-- Rokomari Style Book Preview Modal -->
        <BookPreviewModal
            :show="showPreviewModal"
            :book="{
                id: book.id,
                title: book.title,
                slug: book.slug,
                format: book.format,
                price: book.price,
                price_after_discount: book.price_after_discount,
                stock_quantity: book.stock_quantity,
                cover_url: book.cover_url,
                preview_type: book.preview_type || 'none',
                max_preview_pages: book.max_preview_pages || 15,
                sample_pages_urls: book.sample_pages_urls || [],
                authors: book.authors
            }"
            :sample-pdf-signed-url="sample_pdf_signed_url"
            @close="showPreviewModal = false"
        />
    </MainLayout>
</template>
