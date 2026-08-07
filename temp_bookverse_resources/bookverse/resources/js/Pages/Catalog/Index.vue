

<template>
    <MainLayout>
        <Head title="Book Catalog & Facet Search — BookVerse" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6 font-sans">
            <!-- Breadcrumb Navigation Banner -->
            <div class="p-4 sm:p-6 rounded-2xl bg-white border border-slate-200 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                        <a href="/" class="hover:text-slate-700">Home</a>
                        <span>/</span>
                        <span class="text-sky-600">Catalog Listing</span>
                    </nav>
                    <h1 class="text-xl sm:text-2xl font-bold font-heading text-slate-900">
                        Book Catalog & Facet Search
                    </h1>
                    <p class="text-xs text-slate-500 font-serif italic mt-0.5">
                        Discover literature, novels, historical archives, science & poetry.
                    </p>
                </div>

                <!-- Unified Search Input -->
                <div class="w-full md:w-80">
                    <Input
                        v-model="searchQuery"
                        placeholder="Search books, authors, ISBN..."
                        class="w-full text-xs rounded-xl"
                        @keyup.enter="applyFilters"
                    />
                </div>
            </div>

            <!-- Mobile Sticky Filter Toggle Bar -->
            <div class="block lg:hidden sticky top-16 z-20 bg-white/95 backdrop-blur-md p-3 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-800">Filter Books</span>
                    <span v-if="activeFilterCount > 0" class="px-2 py-0.5 rounded-full bg-sky-600 text-white text-[10px] font-bold">
                        {{ activeFilterCount }} active
                    </span>
                </div>
                <Button variant="brand" size="sm" @click="showMobileFilters = true" class="cursor-pointer font-bold flex items-center gap-1.5 rounded-xl">
                    ⚙️ Filter & Sort
                </Button>
            </div>

            <!-- Layout Grid: Left Sidebar Filters + Right Books Catalog -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
                <!-- Desktop Sidebar Facet Filters -->
                <aside class="hidden lg:block lg:col-span-1 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-6 sticky top-20">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                            Facet Filters
                        </h3>
                        <button
                            v-if="activeFilterCount > 0"
                            @click="resetAllFilters"
                            class="text-xs font-semibold text-sky-600 hover:underline cursor-pointer"
                        >
                            Reset All
                        </button>
                    </div>

                    <!-- 1. Category Collapsible Menu & Sub-menu -->
                    <div v-if="categoriesTree && categoriesTree.length > 0" class="space-y-2">
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Categories</h4>
                        <div class="space-y-1 text-xs max-h-56 overflow-y-auto pr-1">
                            <div v-for="cat in categoriesTree" :key="cat.id" class="space-y-1">
                                <div class="flex items-center justify-between group rounded-lg hover:bg-slate-50 transition-colors">
                                    <button
                                        @click="selectCategory(cat.slug)"
                                        class="flex-1 text-left px-2 py-1.5 rounded-lg flex items-center justify-between font-medium cursor-pointer"
                                        :class="selectedCategory === cat.slug ? 'bg-sky-50 text-sky-700 font-bold' : 'text-slate-700 hover:text-slate-900'"
                                    >
                                        <span>{{ cat.name }}</span>
                                        <span v-if="cat.books_count !== undefined" class="text-[10px] text-slate-400 font-mono">({{ cat.books_count }})</span>
                                    </button>
                                    <button
                                        v-if="cat.children_recursive && cat.children_recursive.length > 0"
                                        @click.stop="toggleCategoryExpand(cat.id)"
                                        class="p-1.5 text-slate-400 hover:text-slate-700 transition cursor-pointer"
                                    >
                                        <span class="text-[10px] inline-block transition-transform duration-200" :class="isExpanded(cat.id) ? 'rotate-90 text-sky-600 font-bold' : ''">▶</span>
                                    </button>
                                </div>

                                <!-- Sub-menu -->
                                <div v-if="isExpanded(cat.id) && cat.children_recursive && cat.children_recursive.length > 0" class="ml-3 pl-2 border-l-2 border-sky-100 space-y-1 py-1">
                                    <button
                                        v-for="sub in cat.children_recursive"
                                        :key="sub.id"
                                        @click="selectCategory(sub.slug)"
                                        class="w-full text-left px-2 py-1 rounded-lg text-[11px] flex items-center justify-between transition-colors cursor-pointer"
                                        :class="selectedCategory === sub.slug ? 'bg-sky-50 text-sky-700 font-bold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'"
                                    >
                                        <span>{{ sub.name }}</span>
                                        <span v-if="sub.books_count !== undefined" class="text-[10px] text-slate-400 font-mono">({{ sub.books_count }})</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Authors Checkboxes -->
                    <div v-if="authorsFacet && authorsFacet.length > 0" class="space-y-2 border-t border-slate-100 pt-4">
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Authors</h4>
                        <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1">
                            <label
                                v-for="author in authorsFacet"
                                :key="author.id"
                                class="flex items-center justify-between text-xs text-slate-600 hover:text-slate-900 cursor-pointer"
                            >
                                <div class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        :checked="selectedAuthors.includes(author.slug)"
                                        @change="toggleAuthor(author.slug)"
                                        class="rounded text-sky-600 focus:ring-sky-500 border-slate-300"
                                    />
                                    <span>{{ author.name }}</span>
                                </div>
                                <span class="text-[10px] text-slate-400 font-mono">({{ author.books_count }})</span>
                            </label>
                        </div>
                    </div>

                    <!-- 3. Publishers Checkboxes -->
                    <div v-if="publishersFacet && publishersFacet.length > 0" class="space-y-2 border-t border-slate-100 pt-4">
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Publishers</h4>
                        <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1">
                            <label
                                v-for="pub in publishersFacet"
                                :key="pub.id"
                                class="flex items-center justify-between text-xs text-slate-600 hover:text-slate-900 cursor-pointer"
                            >
                                <div class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        :checked="selectedPublishers.includes(pub.slug)"
                                        @change="togglePublisher(pub.slug)"
                                        class="rounded text-sky-600 focus:ring-sky-500 border-slate-300"
                                    />
                                    <span>{{ pub.name }}</span>
                                </div>
                                <span class="text-[10px] text-slate-400 font-mono">({{ pub.books_count }})</span>
                            </label>
                        </div>
                    </div>

                    <!-- 4. Format Select Filter -->
                    <div class="space-y-2 border-t border-slate-100 pt-4">
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Format</h4>
                        <select
                            v-model="selectedFormat"
                            @change="applyFilters"
                            class="w-full px-3 py-1.5 rounded-xl text-xs bg-slate-50 border border-slate-200 focus:border-sky-500 outline-none"
                        >
                            <option value="">All Formats</option>
                            <option value="hardcover">Hardcover</option>
                            <option value="paperback">Paperback</option>
                            <option value="e-book">E-Book</option>
                            <option value="audiobook">Audiobook</option>
                        </select>
                    </div>

                    <!-- 5. In Stock Only Toggle -->
                    <div class="border-t border-slate-100 pt-4">
                        <Checkbox
                            v-model="inStockOnly"
                            label="In Stock Only"
                            @change="applyFilters"
                        />
                    </div>

                    <!-- 6. Price Range Inputs -->
                    <div class="space-y-2 border-t border-slate-100 pt-4">
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Price Range (৳)</h4>
                        <div class="flex items-center gap-2">
                            <Input
                                v-model="minPrice"
                                placeholder="Min"
                                type="number"
                                class="text-xs rounded-xl"
                                @keyup.enter="applyFilters"
                            />
                            <span class="text-slate-400">-</span>
                            <Input
                                v-model="maxPrice"
                                placeholder="Max"
                                type="number"
                                class="text-xs rounded-xl"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <Button variant="secondary" size="sm" class="w-full justify-center text-xs mt-2 rounded-xl" @click="applyFilters">
                            Apply Price
                        </Button>
                    </div>
                </aside>

                <!-- Books Grid & Sort Top Bar -->
                <main class="lg:col-span-3 space-y-6">
                    <!-- Top Sort & Status Control Bar -->
                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-semibold text-slate-600">
                                Showing <strong class="text-slate-900">{{ books.total }}</strong> books
                            </span>
                        </div>

                        <!-- 7 Sorting Options Dropdown -->
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <span class="text-xs font-bold text-slate-500 whitespace-nowrap">Sort By:</span>
                            <select
                                v-model="selectedSort"
                                class="w-full sm:w-auto px-3.5 py-1.5 rounded-xl text-xs font-bold bg-white text-slate-900 border border-slate-300 focus:border-sky-500 outline-none"
                            >
                                <option value="latest">Newest Arrivals</option>
                                <option value="price_asc">Price: Low to High</option>
                                <option value="price_desc">Price: High to Low</option>
                                <option value="bestseller">Bestsellers First</option>
                                <option value="featured">Featured First</option>
                                <option value="title_asc">Title: A to Z</option>
                                <option value="title_desc">Title: Z to A</option>
                            </select>
                        </div>
                    </div>

                    <!-- Phone Friendly 2-Column Responsive Books Cards Grid -->
                    <div v-if="books.data && books.data.length > 0" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6">
                        <BookCard
                            v-for="book in books.data"
                            :key="book.id"
                            :book="book"
                        />
                    </div>

                    <!-- Zero Results State -->
                    <div v-else class="text-center py-16 bg-white rounded-2xl border border-slate-200 p-8 space-y-3">
                        <span class="text-4xl">📚</span>
                        <h3 class="text-base font-bold text-slate-900">No matching books found</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">
                            Try adjusting your category, price range, or search criteria.
                        </p>
                        <Button variant="outline" size="sm" @click="resetAllFilters" class="rounded-xl cursor-pointer">
                            Reset All Filters
                        </Button>
                    </div>

                    <!-- Pagination Controls -->
                    <div v-if="books.links && books.links.length > 3" class="pt-4 flex justify-center">
                        <Pagination :links="books.links" />
                    </div>
                </main>
            </div>
        </div>

        <!-- Phone Friendly Slide-Over Mobile Filters Modal -->
        <Modal :show="showMobileFilters" @close="showMobileFilters = false" max-width="md">
            <div class="p-6 space-y-6 font-sans max-h-[85vh] overflow-y-auto text-left">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">Filter Books</h3>
                    <button @click="showMobileFilters = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold cursor-pointer">
                        ✕
                    </button>
                </div>

                <!-- 1. Categories in Mobile Modal -->
                <div v-if="categoriesTree && categoriesTree.length > 0" class="space-y-2">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Categories</h4>
                    <div class="space-y-1 text-xs max-h-48 overflow-y-auto pr-1">
                        <div v-for="cat in categoriesTree" :key="cat.id" class="space-y-1">
                            <div class="flex items-center justify-between rounded-lg hover:bg-slate-50">
                                <button
                                    @click="selectCategory(cat.slug); showMobileFilters = false;"
                                    class="flex-1 text-left px-2 py-2 text-xs flex items-center justify-between font-medium cursor-pointer"
                                    :class="selectedCategory === cat.slug ? 'bg-sky-50 text-sky-700 font-bold' : 'text-slate-700'"
                                >
                                    <span>{{ cat.name }}</span>
                                    <span v-if="cat.books_count !== undefined" class="text-[10px] text-slate-400">({{ cat.books_count }})</span>
                                </button>
                                <button
                                    v-if="cat.children_recursive && cat.children_recursive.length > 0"
                                    @click.stop="toggleCategoryExpand(cat.id)"
                                    class="p-1.5 text-slate-400 hover:text-slate-700 transition cursor-pointer"
                                >
                                    <span class="text-[10px] inline-block transition-transform duration-200" :class="isExpanded(cat.id) ? 'rotate-90 text-sky-600 font-bold' : ''">▶</span>
                                </button>
                            </div>

                            <!-- Mobile Subcategories -->
                            <div v-if="isExpanded(cat.id) && cat.children_recursive && cat.children_recursive.length > 0" class="ml-3 pl-2 border-l-2 border-sky-100 space-y-1 py-1">
                                <button
                                    v-for="sub in cat.children_recursive"
                                    :key="sub.id"
                                    @click="selectCategory(sub.slug); showMobileFilters = false;"
                                    class="w-full text-left px-2 py-1.5 rounded-lg text-[11px] flex items-center justify-between transition-colors cursor-pointer"
                                    :class="selectedCategory === sub.slug ? 'bg-sky-50 text-sky-700 font-bold' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'"
                                >
                                    <span>{{ sub.name }}</span>
                                    <span v-if="sub.books_count !== undefined" class="text-[10px] text-slate-400 font-mono">({{ sub.books_count }})</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Authors in Mobile Modal -->
                <div v-if="authorsFacet && authorsFacet.length > 0" class="space-y-2 border-t border-slate-100 pt-4">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Authors</h4>
                    <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
                        <label v-for="author in authorsFacet" :key="author.id" class="flex items-center justify-between text-xs text-slate-700 cursor-pointer">
                            <div class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    :checked="selectedAuthors.includes(author.slug)"
                                    @change="toggleAuthor(author.slug)"
                                    class="rounded text-sky-600 focus:ring-sky-500 border-slate-300"
                                />
                                <span>{{ author.name }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400">({{ author.books_count }})</span>
                        </label>
                    </div>
                </div>

                <!-- 3. Publishers in Mobile Modal -->
                <div v-if="publishersFacet && publishersFacet.length > 0" class="space-y-2 border-t border-slate-100 pt-4">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Publishers</h4>
                    <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
                        <label v-for="pub in publishersFacet" :key="pub.id" class="flex items-center justify-between text-xs text-slate-700 cursor-pointer">
                            <div class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    :checked="selectedPublishers.includes(pub.slug)"
                                    @change="togglePublisher(pub.slug)"
                                    class="rounded text-sky-600 focus:ring-sky-500 border-slate-300"
                                />
                                <span>{{ pub.name }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400">({{ pub.books_count }})</span>
                        </label>
                    </div>
                </div>

                <!-- 4. Format Select in Mobile Modal -->
                <div class="space-y-2 border-t border-slate-100 pt-4">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Format</h4>
                    <select
                        v-model="selectedFormat"
                        @change="applyFilters"
                        class="w-full px-3 py-2 rounded-xl text-xs bg-slate-50 border border-slate-200 focus:border-sky-500 outline-none"
                    >
                        <option value="">All Formats</option>
                        <option value="hardcover">Hardcover</option>
                        <option value="paperback">Paperback</option>
                        <option value="e-book">E-Book</option>
                        <option value="audiobook">Audiobook</option>
                    </select>
                </div>

                <!-- 5. In Stock Only in Mobile Modal -->
                <div class="border-t border-slate-100 pt-4">
                    <Checkbox
                        v-model="inStockOnly"
                        label="In Stock Only"
                        @change="applyFilters"
                    />
                </div>

                <!-- 6. Price Range in Mobile Modal -->
                <div class="space-y-2 border-t border-slate-100 pt-4">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Price Range (৳)</h4>
                    <div class="flex items-center gap-2">
                        <Input
                            v-model="minPrice"
                            placeholder="Min"
                            type="number"
                            class="text-xs rounded-xl"
                            @keyup.enter="applyFilters"
                        />
                        <span class="text-slate-400">-</span>
                        <Input
                            v-model="maxPrice"
                            placeholder="Max"
                            type="number"
                            class="text-xs rounded-xl"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                </div>

                <!-- Actions Buttons -->
                <div class="pt-4 border-t border-slate-100 flex gap-3">
                    <Button variant="brand" size="md" class="flex-1 rounded-xl font-bold cursor-pointer" @click="showMobileFilters = false">
                        Apply & Show Results
                    </Button>
                    <Button variant="outline" size="md" class="rounded-xl font-bold cursor-pointer" @click="resetAllFilters(); showMobileFilters = false;">
                        Reset
                    </Button>
                </div>
            </div>
        </Modal>
    </MainLayout>
</template>
<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';
import Checkbox from '@/Components/UI/Checkbox.vue';
import Pagination, { PaginationLink } from '@/Components/UI/Pagination.vue';
import BookCard, { CatalogBook } from '@/Components/Catalog/BookCard.vue';
import Modal from '@/Components/UI/Modal.vue';

interface CategoryNode {
    id: number;
    name: string;
    slug: string;
    books_count?: number;
    children_recursive?: CategoryNode[];
}

interface FacetItem {
    id: number;
    name: string;
    slug: string;
    books_count: number;
}

interface PaginationMeta {
    data: CatalogBook[];
    current_page: number;
    last_page: number;
    total: number;
    links: PaginationLink[];
}

const props = defineProps<{
    books: PaginationMeta;
    filters: {
        search: string;
        category: string;
        authors: string[];
        publishers: string[];
        format: string;
        in_stock_only: boolean;
        min_price: number | string | null;
        max_price: number | string | null;
        sort: string;
    };
    categoriesTree: CategoryNode[];
    authorsFacet: FacetItem[];
    publishersFacet: FacetItem[];
    priceBounds: {
        min: number;
        max: number;
    };
}>();

// Filter States
const searchQuery = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');
const selectedAuthors = ref<string[]>([...props.filters.authors]);
const selectedPublishers = ref<string[]>([...props.filters.publishers]);
const selectedFormat = ref(props.filters.format || '');
const inStockOnly = ref(props.filters.in_stock_only || false);
const minPrice = ref<number | string>(props.filters.min_price ?? '');
const maxPrice = ref<number | string>(props.filters.max_price ?? '');
const selectedSort = ref(props.filters.sort || 'latest');

const showMobileFilters = ref(false);
const expandedCategories = ref<number[]>([]);

const activeFilterCount = computed(() => {
    let count = 0;
    if (selectedCategory.value) count++;
    if (selectedAuthors.value.length > 0) count += selectedAuthors.value.length;
    if (selectedPublishers.value.length > 0) count += selectedPublishers.value.length;
    if (selectedFormat.value) count++;
    if (inStockOnly.value) count++;
    if (minPrice.value !== '' || maxPrice.value !== '') count++;
    return count;
});

function toggleCategoryExpand(id: number) {
    const idx = expandedCategories.value.indexOf(id);
    if (idx > -1) {
        expandedCategories.value.splice(idx, 1);
    } else {
        expandedCategories.value.push(id);
    }
}

function isExpanded(id: number): boolean {
    return expandedCategories.value.includes(id);
}

function applyFilters() {
    router.get(
        route('catalog.index'),
        {
            search: searchQuery.value || undefined,
            category: selectedCategory.value || undefined,
            authors: selectedAuthors.value.length > 0 ? selectedAuthors.value.join(',') : undefined,
            publishers: selectedPublishers.value.length > 0 ? selectedPublishers.value.join(',') : undefined,
            format: selectedFormat.value || undefined,
            in_stock_only: inStockOnly.value ? '1' : undefined,
            min_price: minPrice.value !== '' ? minPrice.value : undefined,
            max_price: maxPrice.value !== '' ? maxPrice.value : undefined,
            sort: selectedSort.value !== 'latest' ? selectedSort.value : undefined,
        },
        { preserveState: true, replace: true, preserveScroll: true }
    );
}

function toggleAuthor(slug: string) {
    const idx = selectedAuthors.value.indexOf(slug);
    if (idx > -1) {
        selectedAuthors.value.splice(idx, 1);
    } else {
        selectedAuthors.value.push(slug);
    }
    applyFilters();
}

function togglePublisher(slug: string) {
    const idx = selectedPublishers.value.indexOf(slug);
    if (idx > -1) {
        selectedPublishers.value.splice(idx, 1);
    } else {
        selectedPublishers.value.push(slug);
    }
    applyFilters();
}

function selectCategory(slug: string) {
    if (selectedCategory.value === slug) {
        selectedCategory.value = '';
    } else {
        selectedCategory.value = slug;
    }
    applyFilters();
}

function resetAllFilters() {
    searchQuery.value = '';
    selectedCategory.value = '';
    selectedAuthors.value = [];
    selectedPublishers.value = [];
    selectedFormat.value = '';
    inStockOnly.value = false;
    minPrice.value = '';
    maxPrice.value = '';
    selectedSort.value = 'latest';
    applyFilters();
}

watch(selectedSort, () => {
    applyFilters();
});
</script>