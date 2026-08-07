
<template>
    <MainLayout>
        <Head title="BookVerse — অনলাইন বই ও সাহিত্য সাময়িকী" />

        <!-- 1. Stunning Hero Section with Search -->
        <section
            class="relative text-white overflow-hidden py-10 sm:py-14 border-b border-indigo-900/30 bg-cover bg-center transition-all duration-500"
            :class="!homepageSettings?.hero_bg_image_url ? 'bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900' : ''"
            :style="heroSectionStyle"
        >
            <!-- Background lights effects -->
            <div v-if="!homepageSettings?.hero_bg_image_url || (homepageSettings?.enable_hero_overlay === true || String(homepageSettings?.enable_hero_overlay) === 'true' || String(homepageSettings?.enable_hero_overlay) === '1')" class="absolute inset-0 z-0">
                <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] rounded-full bg-sky-500/10 blur-[120px]"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] rounded-full bg-indigo-500/10 blur-[120px]"></div>
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(255,255,255,0.015),transparent)]"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    <div class="space-y-4 text-left" :class="isFeaturedBookCardVisible ? 'lg:col-span-7' : 'lg:col-span-12 max-w-4xl mx-auto text-center flex flex-col items-center'">
                        <Badge variant="brand" size="sm" class="bg-sky-500/10 text-sky-300 border border-sky-400/20 font-semibold tracking-wider uppercase text-[10px]">
                            {{ homepageSettings?.promo_banner_text || '📚 বাংলাদেশের বৃহত্তম ডিজিটাল বুক স্টোর' }}
                        </Badge>
                        <h1 class="text-2xl sm:text-4xl lg:text-4xl font-black font-heading tracking-tight leading-tight whitespace-pre-line">
                            {{ homepageSettings?.headline_text || 'আপনার পছন্দের বইটি খুঁজুন বুকভার্স-এ' }}
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-300 font-serif leading-relaxed" :class="isFeaturedBookCardVisible ? 'max-w-lg' : 'max-w-xl'">
                            {{ homepageSettings?.subheadline_text || 'হাজার হাজার গল্প, উপন্যাস, কবিতা ও সাহিত্য ম্যাগাজিনের এক সুবিশাল সংগ্রহশালা। আজই পড়ুন ডিজিটাল অথবা অর্ডার করুন প্রিন্টেড কপি।' }}
                        </p>

                        <!-- Search Bar in Hero -->
                        <form @submit.prevent="handleSearch" class="w-full max-w-md flex items-center gap-2 bg-white/5 border border-white/10 p-1 rounded-xl backdrop-blur-md focus-within:border-sky-400/50 transition">
                            <input
                                v-model="searchQuery"
                                type="text"
                                :placeholder="homepageSettings?.search_placeholder || 'বইয়ের নাম, লেখক বা বিষয় দিয়ে খুঁজুন...'"
                                class="flex-1 bg-transparent border-0 outline-none text-white text-xs px-3 py-2 placeholder:text-slate-400"
                            />
                            <Button type="submit" variant="brand" size="sm" class="shrink-0 bg-sky-500 hover:bg-sky-600 text-white font-bold rounded-lg shadow-md shadow-sky-500/10 text-xs px-4 py-2">
                                {{ homepageSettings?.search_button_text || 'খুঁজুন' }}
                            </Button>
                        </form>

                        <!-- Stats Grid (Dynamically Calculated from Database) -->
                        <div class="grid grid-cols-3 gap-4 sm:gap-6 pt-3 border-t border-slate-800 text-center sm:text-left w-full" :class="isFeaturedBookCardVisible ? '' : 'max-w-xl justify-center'">
                            <div>
                                <div class="text-base sm:text-xl font-black text-white">{{ dynamicStats?.books || '১,৫০০+' }}</div>
                                <div class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">বইসমূহ</div>
                            </div>
                            <div class="border-l border-slate-800/60 pl-4 sm:pl-0 sm:border-l-0">
                                <div class="text-base sm:text-xl font-black text-white">{{ dynamicStats?.writers || '৫০+' }}</div>
                                <div class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">লেখকবৃন্দ</div>
                            </div>
                            <div class="border-l border-slate-800/60 pl-4 sm:pl-0 sm:border-l-0">
                                <div class="text-base sm:text-xl font-black text-white">{{ dynamicStats?.publishers || '২০+' }}</div>
                                <div class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">প্রকাশনী</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Standalone 3D Hardcover Book Display -->
                    <div v-if="isFeaturedBookCardVisible" class="lg:col-span-5 flex flex-col items-center justify-center relative mt-6 lg:mt-0">
                        <div v-if="activeFeaturedBook" class="relative group cursor-pointer flex flex-col items-center">
                            <!-- Ambient Glow Effect behind book -->
                            <div class="absolute -inset-2 bg-gradient-to-r from-sky-500/30 to-indigo-600/30 rounded-2xl blur-xl opacity-50 group-hover:opacity-90 transition duration-500"></div>

                            <!-- Rating Badge Floating above Book -->
                            <div class="absolute -top-3 right-1 z-20">
                                <span class="text-[10px] font-bold text-amber-300 flex items-center gap-1 bg-slate-900/90 backdrop-blur-md px-2.5 py-0.5 rounded-full border border-amber-500/30 shadow-md">
                                    ⭐️ {{ (activeFeaturedBook as any).rating_avg ? Number((activeFeaturedBook as any).rating_avg).toFixed(1) : ((activeFeaturedBook as any).ratings_avg_rating ? Number((activeFeaturedBook as any).ratings_avg_rating).toFixed(1) : ((activeFeaturedBook as any).average_rating ? Number((activeFeaturedBook as any).average_rating).toFixed(1) : '4.9')) }}
                                </span>
                            </div>

                            <!-- 3D Book Container -->
                            <Link :href="'/books/' + (activeFeaturedBook.slug || activeFeaturedBook.id)" class="block relative group-hover:-translate-y-1.5 transition-all duration-300">
                                <div class="relative w-36 h-52 sm:w-40 sm:h-56 rounded-r-lg rounded-l-xs overflow-hidden shadow-xl border-r border-t border-b border-white/20 bg-slate-900 transform transition-all duration-500 group-hover:shadow-sky-500/20">
                                    <!-- Book Spine Shadow Effect (Left border) -->
                                    <div class="absolute left-0 top-0 bottom-0 w-2.5 bg-gradient-to-r from-black/60 via-black/20 to-transparent z-10"></div>
                                    <div class="absolute left-2.5 top-0 bottom-0 w-[1px] bg-white/20 z-10"></div>

                                    <!-- Book Cover Image -->
                                    <img
                                        :src="(activeFeaturedBook as any).cover_url || (activeFeaturedBook as any).cover_image_url || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(activeFeaturedBook.title) + '&background=0284c7&color=ffffff&size=256')"
                                        :alt="activeFeaturedBook.title"
                                        class="w-full h-full object-cover"
                                    />
                                </div>
                            </Link>

                            <!-- Book Title & Action Link under 3D Book -->
                            <div class="text-center mt-3 space-y-0.5 max-w-[170px]">
                                <h3 class="text-xs sm:text-sm font-extrabold text-white line-clamp-1 font-heading">
                                    {{ activeFeaturedBook.title }}
                                </h3>
                                <p class="text-[11px] text-sky-300 font-medium truncate">
                                    {{ (activeFeaturedBook as any).authors?.[0]?.name || (activeFeaturedBook as any).primary_author?.name || (activeFeaturedBook as any).author_name || 'হুমায়ূন আহমেদ' }}
                                </p>
                                <Link
                                    :href="'/books/' + (activeFeaturedBook.slug || activeFeaturedBook.id)"
                                    class="inline-flex items-center gap-1 text-[11px] font-bold text-sky-400 hover:text-sky-300 pt-0.5 transition"
                                >
                                    <span>এখনই পড়ুন</span>
                                    <span>→</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16">
            <!-- 2. Categories Carousel/Grid -->
            <section class="space-y-6">
                <div class="relative flex flex-col items-center justify-center border-b border-slate-100 pb-3 text-center">
                    <h2 class="text-base sm:text-2xl font-black font-heading text-slate-800">জনপ্রিয় ক্যাটাগরি</h2>
                    <p class="text-xs text-slate-400 font-serif mt-0.5">আপনার পছন্দের বিষয় নির্বাচন করে পড়া শুরু করুন</p>
                    <Link href="/catalog" class="absolute right-0 top-1/2 -translate-y-1/2 text-xs font-bold text-sky-600 hover:text-sky-700">সকল ক্যাটাগরি →</Link>
                </div>

                <!-- Single Line Horizontal Touch-Scrollable Container (Center-aligned when space permits) -->
                <div class="flex items-center gap-3.5 overflow-x-auto pb-3 pt-1 scroll-smooth snap-x snap-mandatory justify-start md:justify-center [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                    <Link
                        v-for="cat in categories"
                        :key="cat.id"
                        :href="`/catalog?category=${cat.slug}`"
                        class="shrink-0 w-36 sm:w-40 p-4 rounded-2xl bg-white border border-slate-200/80 shadow-2xs hover:shadow-md hover:border-sky-400 transition-all duration-300 text-center flex flex-col items-center justify-center gap-2.5 cursor-pointer snap-start group"
                    >
                        <div class="w-12 h-12 rounded-xl bg-slate-50 group-hover:bg-sky-50 flex items-center justify-center transition-colors overflow-hidden">
                            <img
                                v-if="cat.image_url && !cat.image_url.includes('ui-avatars.com')"
                                :src="cat.image_url"
                                :alt="cat.name"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                            />
                            <span v-else class="text-2xl">{{ getCategoryIcon(cat.slug) }}</span>
                        </div>
                        <h4 class="font-bold text-xs text-slate-800 group-hover:text-sky-600 transition-colors line-clamp-1">
                            {{ cat.name }}
                        </h4>
                    </Link>
                </div>
            </section>

            <!-- 3. Popular Authors Section (Placed directly after Categories) -->
            <section v-if="featuredAuthors && featuredAuthors.length > 0" class="space-y-6">
                <div class="relative flex flex-col items-center justify-center border-b border-slate-100 pb-3 text-center">
                    <h2 class="text-base sm:text-2xl font-black font-heading text-slate-800">জনপ্রিয় লেখকবৃন্দ</h2>
                    <p class="text-xs text-slate-400 font-serif mt-0.5">আমাদের প্রিয় সাহিত্যিকদের জীবন ও সাহিত্য</p>
                    <Link href="/catalog" class="absolute right-0 top-1/2 -translate-y-1/2 text-xs font-bold text-sky-600 hover:text-sky-700">সকল লেখক →</Link>
                </div>

                <!-- Single Line Horizontal Touch-Scrollable Container for Authors (Center-aligned when space permits) -->
                <div class="flex items-center gap-6 overflow-x-auto pb-3 pt-1 scroll-smooth snap-x snap-mandatory justify-start md:justify-center [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
                    <Link
                        v-for="author in featuredAuthors"
                        :key="author.id"
                        :href="`/catalog?authors=${author.slug}`"
                        class="shrink-0 group flex flex-col items-center text-center space-y-2.5 cursor-pointer snap-start w-24 sm:w-28"
                    >
                        <div class="w-20 h-20 sm:w-22 sm:h-22 rounded-full overflow-hidden border-2 border-slate-100 group-hover:border-sky-500 shadow-xs group-hover:shadow-md transition-all duration-300">
                            <img
                                :src="author.photo_url"
                                :alt="author.name"
                                @error="(e: any) => e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(author.name)}&color=0284c7&background=e0f2fe&size=256`"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                        </div>
                        <div>
                            <h4 class="font-bold text-xs text-slate-800 group-hover:text-sky-600 transition-colors line-clamp-1">
                                {{ author.name }}
                            </h4>
                        </div>
                    </Link>
                </div>
            </section>

            <!-- 4. Featured Books Grid -->
            <section v-if="featuredBooks && featuredBooks.length > 0" class="space-y-6">
                <div class="relative flex flex-col items-center justify-center border-b border-slate-100 pb-3 text-center">
                    <h2 class="text-base sm:text-2xl font-black font-heading text-slate-800">বিশেষ ফিচার্ড বইসমূহ</h2>
                    <p class="text-xs text-slate-400 font-serif mt-0.5">আমাদের বিশেষ নির্বাচনের আকর্ষণীয় সংগ্রহ</p>
                    <Link href="/catalog?is_featured=1" class="absolute right-0 top-1/2 -translate-y-1/2 text-xs font-bold text-sky-600 hover:text-sky-700">সকল ফিচার্ড বই →</Link>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <BookCard v-for="book in featuredBooks" :key="book.id" :book="book" />
                </div>
            </section>

            <!-- 5. E-Magazine Promotional Banner (Redesigned 2-Column Showcase) -->
            <section class="relative rounded-3xl bg-gradient-to-br from-indigo-950 via-slate-900 to-sky-950 text-white p-8 sm:p-12 shadow-2xl border border-white/10 overflow-hidden">
                <!-- Ambient Background Glow & Lights -->
                <div class="absolute inset-0 pointer-events-none z-0">
                    <div class="absolute -top-12 -right-12 w-80 h-80 bg-sky-500/20 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-12 -left-12 w-64 h-64 bg-indigo-600/20 rounded-full blur-3xl"></div>
                </div>

                <div class="relative z-10 grid lg:grid-cols-12 gap-8 items-center">
                    <!-- Left Content Column -->
                    <div class="lg:col-span-7 space-y-4 text-left">
                        <Badge variant="brand" size="sm" class="bg-sky-500/20 text-sky-300 border border-sky-400/30 font-bold uppercase tracking-wider">
                            ✨ বুকভার্স সাহিত্য সাময়িকী
                        </Badge>
                        <h2 class="text-2xl sm:text-4xl font-black font-heading leading-tight text-white">
                            বুকভার্স ই-ম্যাগাজিন
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-xl">
                            প্রথিতযশা ও তরুণ সাহিত্যিকদের অনন্য গল্প, প্রবন্ধ ও কবিতার ডিজিটাল সাময়িকী। যেকোনো সময় যেকোনো ডিভাইসে উন্মুক্ত সাহিত্য পড়ুন।
                        </p>
                        <div class="pt-2 flex items-center gap-4">
                            <Link href="/magazine">
                                <button
                                    type="button"
                                    class="px-6 py-3 rounded-2xl bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-400 hover:to-indigo-500 text-white font-extrabold text-xs shadow-lg shadow-sky-500/20 transition-all flex items-center gap-2 cursor-pointer"
                                >
                                    <span>ম্যাগাজিন পড়ুন</span>
                                    <span>→</span>
                                </button>
                            </Link>
                        </div>
                    </div>

                    <!-- Right Column: 3D Magazine Cover Artwork Showcase -->
                    <div class="lg:col-span-5 flex justify-center relative mt-6 lg:mt-0">
                        <div class="relative w-64 h-44 sm:h-48 flex items-center justify-center">
                            <!-- Background stacked magazine card 1 -->
                            <div class="absolute w-32 h-44 bg-gradient-to-br from-indigo-800 to-slate-900 rounded-xl shadow-xl border border-white/20 -rotate-12 -translate-x-10 scale-90 opacity-70">
                                <div class="p-3 text-[9px] font-mono text-indigo-300 uppercase">Issue #01</div>
                            </div>
                            <!-- Background stacked magazine card 2 -->
                            <div class="absolute w-32 h-44 bg-gradient-to-br from-sky-800 to-indigo-950 rounded-xl shadow-xl border border-white/20 rotate-12 translate-x-10 scale-90 opacity-70">
                                <div class="p-3 text-[9px] font-mono text-sky-300 uppercase">Issue #02</div>
                            </div>
                            <!-- Main Foreground Magazine Card -->
                            <div class="relative w-36 h-48 bg-gradient-to-br from-slate-900 to-indigo-950 rounded-2xl shadow-2xl border border-white/25 p-4 flex flex-col justify-between transform hover:scale-105 transition-transform duration-300 z-10">
                                <div class="flex justify-between items-start">
                                    <span class="text-[8px] font-black tracking-widest text-sky-400 font-mono">E-MAGAZINE</span>
                                    <span class="text-[10px] text-amber-300">✨ 2026</span>
                                </div>
                                <div class="my-auto space-y-1">
                                    <span class="text-[10px] text-sky-300 font-bold block">বুকভার্স বিশেষ প্রকাশনা</span>
                                    <h4 class="text-xs font-black text-white line-clamp-2">চিন্তাশীল গল্প ও আধুনিক সাহিত্য</h4>
                                </div>
                                <div class="text-[9px] text-slate-400 font-mono border-t border-white/10 pt-2 flex justify-between">
                                    <span>BookVerse Edition</span>
                                    <span>Read Free</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 6. Bestseller Books Grid -->
            <section v-if="bestsellerBooks && bestsellerBooks.length > 0" class="space-y-6">
                <div class="relative flex flex-col items-center justify-center border-b border-slate-100 pb-3 text-center">
                    <h2 class="text-base sm:text-2xl font-black font-heading text-slate-800">সর্বাধিক বিক্রিত বই</h2>
                    <p class="text-xs text-slate-400 font-serif mt-0.5">পাঠকদের সবচেয়ে পছন্দের জনপ্রিয় বইসমূহ</p>
                    <Link href="/catalog" class="absolute right-0 top-1/2 -translate-y-1/2 text-xs font-bold text-sky-600 hover:text-sky-700">সকল বই →</Link>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <BookCard v-for="book in bestsellerBooks" :key="book.id" :book="book" />
                </div>
            </section>
        </div>
    </MainLayout>
</template>
<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import BookCard, { CatalogBook } from '@/Components/Catalog/BookCard.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';

interface CategoryItem {
    id: number;
    name: string;
    slug: string;
    image_url?: string;
}

interface AuthorItem {
    id: number;
    name: string;
    slug: string;
    photo_url?: string;
}

const props = defineProps<{
    featuredBook?: CatalogBook;
    featuredBooks: CatalogBook[];
    bestsellerBooks: CatalogBook[];
    recentBooks: CatalogBook[];
    categories: CategoryItem[];
    featuredAuthors: AuthorItem[];
    homepageSettings?: {
        headline_text?: string;
        promo_banner_text?: string;
        subheadline_text?: string;
        search_placeholder?: string;
        search_button_text?: string;
        hero_image_url?: string;
        hero_bg_image_url?: string;
        enable_hero_overlay?: boolean | string | number;
        hero_overlay_color?: string;
        hero_overlay_opacity?: number | string;
        show_featured_book_card?: boolean | string | number;
        show_bestseller_section?: boolean | string | number;
    };
    dynamicStats?: {
        books: string;
        writers: string;
        publishers: string;
    };
}>();

const searchQuery = ref('');

const heroSectionStyle = computed(() => {
    const bgUrl = props.homepageSettings?.hero_bg_image_url;
    if (!bgUrl) return {};

    const rawOverlay = props.homepageSettings?.enable_hero_overlay;
    const enableOverlay = rawOverlay === true || rawOverlay === 1 || rawOverlay === '1' || rawOverlay === 'true';

    if (!enableOverlay) {
        return {
            backgroundImage: `url('${bgUrl}')`,
            backgroundColor: 'transparent',
        };
    }

    const hexColor = props.homepageSettings?.hero_overlay_color || '#0f172a';
    const opacityPercent = Number(props.homepageSettings?.hero_overlay_opacity ?? 85) / 100;

    let r = 15, g = 23, b = 42;
    if (hexColor.startsWith('#') && hexColor.length === 7) {
        r = parseInt(hexColor.slice(1, 3), 16);
        g = parseInt(hexColor.slice(3, 5), 16);
        b = parseInt(hexColor.slice(5, 7), 16);
    }

    const rgba1 = `rgba(${r}, ${g}, ${b}, ${Math.min(1, opacityPercent + 0.1)})`;
    const rgba2 = `rgba(${r}, ${g}, ${b}, ${opacityPercent})`;

    return {
        backgroundImage: `linear-gradient(to right, ${rgba1}, ${rgba2}), url('${bgUrl}')`,
    };
});

const isFeaturedBookCardVisible = computed(() => {
    const raw = props.homepageSettings?.show_featured_book_card;
    if (raw === false || String(raw) === 'false' || String(raw) === '0' || String(raw) === 'off') return false;
    return true;
});

const activeFeaturedBook = computed(() => {
    if (props.featuredBook) return props.featuredBook;
    if (props.featuredBooks && props.featuredBooks.length > 0) return props.featuredBooks[0];
    if (props.recentBooks && props.recentBooks.length > 0) return props.recentBooks[0];
    return {
        id: 1,
        title: 'জোছনা ও জননীর গল্প',
        slug: 'jochna-o-jononir-golpo',
        author_name: 'হুমায়ূন আহমেদ',
        authors: [{ name: 'হুমায়ূন আহমেদ' }],
        rating_avg: '4.9',
        cover_image_url: 'https://ui-avatars.com/api/?name=BookVerse&background=0284c7&color=ffffff&size=256',
    };
});

function handleSearch() {
    if (searchQuery.value.trim()) {
        router.get('/catalog', { search: searchQuery.value });
    }
}

function getCategoryIcon(slug: string): string {
    const icons: Record<string, string> = {
        'fiction': '📚',
        'poetry': '✍️',
        'novel': '📖',
        'science': '🔬',
        'history': '⏳',
        'islamic': '🕌',
        'academic': '🎓',
        'children': '🧸',
    };
    return icons[slug] || '📚';
}
</script>
