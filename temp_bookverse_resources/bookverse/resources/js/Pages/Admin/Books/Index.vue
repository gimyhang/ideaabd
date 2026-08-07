
<template>
    <Head title="Book Catalog Management — Admin" />

    <AdminLayout>
        <template #header>Book Catalog Management</template>

        <div class="space-y-6 font-sans">
            <!-- Header Banner (rounded-2xl) -->
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-2xl shadow-sm border transition-colors bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white"
            >
                <div>
                    <h1 class="text-2xl font-black font-heading tracking-tight">Book Catalog Management</h1>
                    <p class="text-xs mt-1 text-slate-500 dark:text-zinc-400">
                        Manage book inventory, authors, categories, pricing snapshots, and stock status.
                    </p>
                </div>
                <Link v-if="can('create-books')" href="/admin/books/create">
                    <Button variant="brand" size="md" class="font-bold shadow-lg shadow-sky-600/20">
                        <span>Add New Book</span>
                    </Button>
                </Link>
            </div>

            <!-- Filter Controls Panel (rounded-2xl) -->
            <div
                class="border rounded-2xl p-5 shadow-sm space-y-4 transition-colors bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-slate-100"
            >
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                    <Input
                        :model-value="filters.search || ''"
                        placeholder="Search title, SKU..."
                        class="text-xs"
                        @input="onSearchInput"
                    />

                    <select
                        :value="filters.category_id || ''"
                        class="w-full text-xs font-semibold bg-white dark:bg-zinc-900 text-slate-900 dark:text-slate-100 rounded-xl border border-slate-300 dark:border-zinc-800 px-3 py-2.5 cursor-pointer shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                        @change="filterBy('category_id', ($event.target as HTMLSelectElement).value)"
                    >
                        <option value="">All Categories</option>
                        <option v-for="c in categories" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>

                    <select
                        :value="filters.author_id || ''"
                        class="w-full text-xs font-semibold bg-white dark:bg-zinc-900 text-slate-900 dark:text-slate-100 rounded-xl border border-slate-300 dark:border-zinc-800 px-3 py-2.5 cursor-pointer shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                        @change="filterBy('author_id', ($event.target as HTMLSelectElement).value)"
                    >
                        <option value="">All Authors</option>
                        <option v-for="a in authors" :key="a.id" :value="String(a.id)">{{ a.name }}</option>
                    </select>

                    <select
                        :value="filters.publisher_id || ''"
                        class="w-full text-xs font-semibold bg-white dark:bg-zinc-900 text-slate-900 dark:text-slate-100 rounded-xl border border-slate-300 dark:border-zinc-800 px-3 py-2.5 cursor-pointer shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                        @change="filterBy('publisher_id', ($event.target as HTMLSelectElement).value)"
                    >
                        <option value="">All Publishers</option>
                        <option v-for="p in publishers" :key="p.id" :value="String(p.id)">{{ p.name }}</option>
                    </select>

                    <select
                        :value="filters.format || ''"
                        class="w-full text-xs font-semibold bg-white dark:bg-zinc-900 text-slate-900 dark:text-slate-100 rounded-xl border border-slate-300 dark:border-zinc-800 px-3 py-2.5 cursor-pointer shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                        @change="filterBy('format', ($event.target as HTMLSelectElement).value)"
                    >
                        <option value="">All Formats</option>
                        <option value="paperback">Paperback</option>
                        <option value="hardcover">Hardcover</option>
                        <option value="ebook">eBook</option>
                        <option value="pdf">PDF</option>
                    </select>

                    <select
                        :value="filters.stock_status || ''"
                        class="w-full text-xs font-semibold bg-white dark:bg-zinc-900 text-slate-900 dark:text-slate-100 rounded-xl border border-slate-300 dark:border-zinc-800 px-3 py-2.5 cursor-pointer shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20"
                        @change="filterBy('stock_status', ($event.target as HTMLSelectElement).value)"
                    >
                        <option value="">All Stock Status</option>
                        <option value="in_stock">In Stock</option>
                        <option value="low_stock">Low Stock</option>
                        <option value="out_of_stock">Out of Stock</option>
                    </select>
                </div>

                <div class="flex items-center justify-between border-t border-slate-100 dark:border-zinc-800 pt-3">
                    <span class="text-xs text-slate-500 dark:text-zinc-400">
                        Showing <strong>{{ books.data.length }}</strong> of <strong>{{ books.total }}</strong> books
                    </span>
                    <button @click="resetFilters" class="text-xs font-bold text-sky-600 dark:text-sky-400 hover:text-sky-700">
                        Reset Filters
                    </button>
                </div>
            </div>

            <!-- Books Table Panel (rounded-2xl) -->
            <div class="border rounded-2xl overflow-hidden shadow-sm transition-colors bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="uppercase tracking-wider font-semibold border-b bg-slate-50 dark:bg-zinc-950/60 text-slate-500 dark:text-zinc-400 border-slate-200 dark:border-zinc-800">
                            <tr>
                                <th class="py-4 px-6">Book Title</th>
                                <th class="py-4 px-4">Author & Publisher</th>
                                <th class="py-4 px-4">Format</th>
                                <th class="py-4 px-4">Price</th>
                                <th class="py-4 px-4">Stock</th>
                                <th class="py-4 px-4 text-center">Status</th>
                                <th class="py-4 px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                            <tr
                                v-for="book in books.data"
                                :key="book.id"
                                class="transition-colors hover:bg-slate-50/80 dark:hover:bg-zinc-800/40"
                            >
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <img
                                            :src="book.cover_url"
                                            :alt="book.title"
                                            @error="(e: any) => e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(book.title)}&color=0284c7&background=e0f2fe&size=512`"
                                            class="w-10 h-14 object-cover rounded-lg shadow-sm border border-slate-200 dark:border-zinc-800"
                                        />
                                        <div>
                                            <Link
                                                :href="`/admin/books/${book.id}`"
                                                class="font-bold transition line-clamp-1 text-slate-900 dark:text-white hover:text-sky-600 dark:hover:text-sky-400"
                                            >
                                                {{ book.title }}
                                            </Link>
                                            <div class="text-[10px] font-mono mt-0.5 text-slate-400 dark:text-zinc-500">
                                                SKU: {{ book.sku }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-4">
                                    <div class="font-medium line-clamp-1 text-slate-800 dark:text-zinc-200">
                                        {{ book.authors?.[0]?.name || 'N/A' }}
                                    </div>
                                    <div class="text-[10px] line-clamp-1 text-slate-400 dark:text-zinc-500">
                                        {{ book.publisher?.name }}
                                    </div>
                                </td>

                                <td class="py-4 px-4">
                                    <Badge variant="brand" size="sm" class="capitalize">
                                        {{ book.format }}
                                    </Badge>
                                </td>

                                <td class="py-4 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                    ৳{{ book.price_after_discount }}
                                    <span v-if="book.discount_price" class="block text-[10px] line-through text-slate-400 dark:text-zinc-500">
                                        ৳{{ book.price }}
                                    </span>
                                </td>

                                <td class="py-4 px-4">
                                    <div class="font-mono font-semibold text-slate-800 dark:text-zinc-200">
                                        {{ book.stock_quantity }} copies
                                    </div>
                                    <span
                                        class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full mt-0.5"
                                        :class="{
                                            'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400': book.stock_status === 'In Stock',
                                            'bg-amber-500/15 text-amber-600 dark:text-amber-400': book.stock_status === 'Low Stock',
                                            'bg-rose-500/15 text-rose-600 dark:text-rose-400': book.stock_status === 'Out of Stock'
                                        }"
                                    >
                                        {{ book.stock_status }}
                                    </span>
                                </td>

                                <td class="py-4 px-4 text-center">
                                    <span v-if="book.is_active" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                        Active
                                    </span>
                                    <span v-else class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-500/10 text-slate-500 border border-slate-500/20">
                                        Disabled
                                    </span>
                                </td>

                                <td class="py-4 px-6 text-right">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <!-- View Details -->
                                        <Link
                                            v-if="can('view-books')"
                                            :href="`/admin/books/${book.id}`"
                                            class="p-2 rounded-xl border transition-all duration-200 shadow-sm bg-sky-50 hover:bg-sky-600 text-sky-600 hover:text-white border-sky-200/80 dark:bg-sky-500/10 dark:hover:bg-sky-600 dark:text-sky-400 dark:hover:text-white dark:border-sky-500/20"
                                            title="View Details"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>

                                        <!-- Edit Book -->
                                        <Link
                                            v-if="can('edit-books')"
                                            :href="`/admin/books/${book.id}/edit`"
                                            class="p-2 rounded-xl border transition-all duration-200 shadow-sm bg-amber-50 hover:bg-amber-500 text-amber-600 hover:text-white border-amber-200/80 dark:bg-amber-500/10 dark:hover:bg-amber-500 dark:text-amber-400 dark:hover:text-white dark:border-amber-500/20"
                                            title="Edit Book"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>

                                        <!-- Delete Book -->
                                        <button
                                            v-if="can('delete-books')"
                                            @click="promptDeleteBook(book)"
                                            class="p-2 rounded-xl border transition-all duration-200 shadow-sm bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border-rose-200/80 dark:bg-rose-500/10 dark:hover:bg-rose-600 dark:text-rose-400 dark:hover:text-white dark:border-rose-500/20"
                                            title="Delete Book"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer Pagination -->
                <div class="border-t border-slate-100 dark:border-zinc-800 px-6 py-3 bg-slate-50/50 dark:bg-zinc-950/40">
                    <Pagination :links="books.links" />
                </div>
            </div>

            <!-- Confirm Delete Modal -->
            <ConfirmDeleteModal
                :show="showDeleteModal"
                title="Delete Book?"
                :item-name="bookToDelete?.title"
                message="Are you sure you want to delete this book from the catalog? This will soft-delete the record."
                @close="showDeleteModal = false"
                @confirm="confirmDeleteBook"
            />

            <!-- Toast -->
            <Toast :show="showToast" type="success" :message="toastMessage" @close="showToast = false" />
        </div>
    </AdminLayout>
</template>
<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermission } from '@/Composables/usePermission';

const { can } = usePermission();
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';
import Toast from '@/Components/UI/Toast.vue';
import ConfirmDeleteModal from '@/Components/UI/ConfirmDeleteModal.vue';
import Pagination, { type PaginationLink } from '@/Components/UI/Pagination.vue';
import { useAdminTheme } from '@/Composables/useAdminTheme';

const { isDark } = useAdminTheme();

const showDeleteModal = ref(false);
const bookToDelete = ref<BookItem | null>(null);
const showToast = ref(false);
const toastMessage = ref('');

export interface BookItem {
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
    is_active: boolean;
    is_featured: boolean;
    is_bestseller: boolean;
    cover_url: string;
    authors: Array<{ id: number; name: string; pivot: { is_primary: boolean } }>;
    publisher?: { id: number; name: string };
    category?: { id: number; name: string };
}

interface PaginationMeta {
    data: BookItem[];
    current_page: number;
    last_page: number;
    total: number;
    links: PaginationLink[];
}

const props = defineProps<{
    books: PaginationMeta;
    filters: Record<string, string>;
    categories: Array<{ id: number; name: string }>;
    authors: Array<{ id: number; name: string }>;
    publishers: Array<{ id: number; name: string }>;
}>();

// Filter Controls State (stateless, props-driven)
const searchQuery = ref(props.filters.search || '');
let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;

watch(() => props.filters.search, (newSearch) => {
    searchQuery.value = newSearch || '';
});

function filterBy(key: string, value: any) {
    const params: Record<string, any> = {
        search: searchQuery.value || undefined,
        category_id: props.filters.category_id || undefined,
        author_id: props.filters.author_id || undefined,
        publisher_id: props.filters.publisher_id || undefined,
        format: props.filters.format || undefined,
        stock_status: props.filters.stock_status || undefined,
        [key]: value || undefined,
    };

    router.get('/admin/books', params, { preserveScroll: true });
}

function onSearchInput(e: Event) {
    const val = (e.target as HTMLInputElement).value;
    searchQuery.value = val;
    if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        filterBy('search', val);
    }, 300);
}

function resetFilters() {
    searchQuery.value = '';
    router.get('/admin/books', {}, { preserveScroll: true });
}

function promptDeleteBook(book: BookItem) {
    bookToDelete.value = book;
    showDeleteModal.value = true;
}

function confirmDeleteBook() {
    if (!bookToDelete.value) return;

    router.delete(`/admin/books/${bookToDelete.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            toastMessage.value = `Book '${bookToDelete.value?.title}' deleted successfully.`;
            showToast.value = true;
            bookToDelete.value = null;
        },
    });
}
</script>
