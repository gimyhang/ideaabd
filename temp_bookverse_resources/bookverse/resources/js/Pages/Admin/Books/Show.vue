<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermission } from '@/Composables/usePermission';

const { can } = usePermission();
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';
import ConfirmDeleteModal from '@/Components/UI/ConfirmDeleteModal.vue';
import { useAdminTheme } from '@/Composables/useAdminTheme';

const { isDark } = useAdminTheme();

interface Author {
    id: number;
    name: string;
    pivot?: { is_primary: boolean };
}

interface Publisher {
    id: number;
    name: string;
}

interface Category {
    id: number;
    name: string;
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
    language: string;
    edition?: string;
    page_count?: number;
    published_year?: number;
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
    category?: Category;
    publisher?: Publisher;
    authors?: Author[];
    created_at: string;
}

const props = defineProps<{
    book: Book;
}>();

const showDeleteModal = ref(false);

function confirmDelete() {
    router.delete(`/admin/books/${props.book.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
}
</script>

<template>
    <Head :title="`${book.title} — Admin Details`" />

    <AdminLayout>
        <template #header>{{ book.title }} Details</template>

        <div class="max-w-5xl mx-auto space-y-8 font-sans">
            <!-- Header Banner -->
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-2xl shadow-sm border transition-colors"
                :class="isDark
                    ? 'bg-zinc-900 border-zinc-800 text-white'
                    : 'bg-white border-slate-200 text-slate-900'"
            >
                <div class="flex items-center gap-4">
                    <img :src="book.cover_url" :alt="book.title" class="w-14 h-20 object-cover rounded-xl shadow border border-slate-200 dark:border-zinc-800" />
                    <div>
                        <div class="flex items-center gap-2">
                            <Badge variant="brand" size="sm" class="capitalize">{{ book.format }}</Badge>
                            <Badge v-if="book.is_bestseller" variant="warning" size="sm">Bestseller</Badge>
                            <Badge v-else-if="book.is_featured" variant="brand" size="sm">Featured</Badge>
                        </div>
                        <h1 class="text-2xl font-black font-heading tracking-tight mt-1">{{ book.title }}</h1>
                        <p class="text-xs font-mono" :class="isDark ? 'text-zinc-400' : 'text-slate-500'">
                            SKU: {{ book.sku }} <span v-if="book.isbn_13">• ISBN: {{ book.isbn_13 }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Link href="/admin/books">
                        <Button variant="secondary" size="sm" class="text-xs">
                            ← Back
                        </Button>
                    </Link>
                    <Link v-if="can('edit-books')" :href="`/admin/books/${book.id}/edit`">
                        <Button variant="brand" size="sm" class="text-xs">
                            Edit Book
                        </Button>
                    </Link>
                    <Button v-if="can('delete-books')" variant="destructive" size="sm" class="text-xs" @click="showDeleteModal = true">
                        Delete
                    </Button>
                </div>
            </div>

            <!-- Grid Key Metrics -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div
                    class="border rounded-2xl p-5 shadow-sm space-y-1 transition-colors"
                    :class="isDark ? 'bg-zinc-900 border-zinc-800' : 'bg-white border-slate-200'"
                >
                    <span class="text-[11px] font-bold uppercase tracking-wider" :class="isDark ? 'text-zinc-400' : 'text-slate-400'">Regular Price</span>
                    <div class="text-xl font-black font-mono" :class="isDark ? 'text-white' : 'text-slate-900'">৳{{ book.price }}</div>
                </div>

                <div
                    class="border rounded-2xl p-5 shadow-sm space-y-1 transition-colors"
                    :class="isDark ? 'bg-zinc-900 border-zinc-800' : 'bg-white border-slate-200'"
                >
                    <span class="text-[11px] font-bold uppercase tracking-wider" :class="isDark ? 'text-zinc-400' : 'text-slate-400'">Selling Price</span>
                    <div class="text-xl font-black text-sky-600 font-mono">৳{{ book.price_after_discount }}</div>
                    <span v-if="book.discount_percentage > 0" class="text-[10px] text-emerald-500 font-semibold block">{{ book.discount_percentage }}% Discount</span>
                </div>

                <div
                    class="border rounded-2xl p-5 shadow-sm space-y-1 transition-colors"
                    :class="isDark ? 'bg-zinc-900 border-zinc-800' : 'bg-white border-slate-200'"
                >
                    <span class="text-[11px] font-bold uppercase tracking-wider" :class="isDark ? 'text-zinc-400' : 'text-slate-400'">Stock Inventory</span>
                    <div class="text-xl font-black font-mono" :class="isDark ? 'text-white' : 'text-slate-900'">{{ book.stock_quantity }}</div>
                    <span class="text-[10px] font-semibold" :class="book.stock_status === 'In Stock' ? 'text-emerald-500' : 'text-amber-500'">{{ book.stock_status }}</span>
                </div>

                <div
                    class="border rounded-3xl p-5 shadow-sm space-y-1 transition-colors"
                    :class="isDark ? 'bg-zinc-900 border-zinc-800' : 'bg-white border-slate-200'"
                >
                    <span class="text-[11px] font-bold uppercase tracking-wider" :class="isDark ? 'text-zinc-400' : 'text-slate-400'">Visibility Status</span>
                    <div class="text-xl font-black">
                        <span v-if="book.is_active" class="text-emerald-500">Active</span>
                        <span v-else class="text-rose-500">Disabled</span>
                    </div>
                </div>
            </div>

            <!-- Complete Information Sections -->
            <div
                class="border rounded-3xl p-8 space-y-8 shadow-sm transition-colors"
                :class="isDark ? 'bg-zinc-900 border-zinc-800 text-slate-100' : 'bg-white border-slate-200 text-slate-900'"
            >
                <!-- Authors & Classification -->
                <div class="space-y-4">
                    <h3
                        class="text-sm font-bold font-heading uppercase tracking-wider border-b pb-2"
                        :class="isDark ? 'text-white border-zinc-800' : 'text-slate-900 border-slate-100'"
                    >
                        Classification & Metadata
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs">
                        <div>
                            <span class="block font-medium" :class="isDark ? 'text-zinc-400' : 'text-slate-400'">Category</span>
                            <span class="font-bold text-sm" :class="isDark ? 'text-white' : 'text-slate-900'">{{ book.category?.name || 'Uncategorized' }}</span>
                        </div>

                        <div>
                            <span class="block font-medium" :class="isDark ? 'text-zinc-400' : 'text-slate-400'">Primary Author</span>
                            <span class="font-bold text-sm" :class="isDark ? 'text-white' : 'text-slate-900'">{{ book.authors?.[0]?.name || 'N/A' }}</span>
                        </div>

                        <div>
                            <span class="block font-medium" :class="isDark ? 'text-zinc-400' : 'text-slate-400'">Publisher</span>
                            <span class="font-bold text-sm" :class="isDark ? 'text-white' : 'text-slate-900'">{{ book.publisher?.name || 'Self-Published' }}</span>
                        </div>

                        <div>
                            <span class="block font-medium" :class="isDark ? 'text-zinc-400' : 'text-slate-400'">Language</span>
                            <span class="font-bold uppercase" :class="isDark ? 'text-white' : 'text-slate-900'">{{ book.language }}</span>
                        </div>

                        <div>
                            <span class="block font-medium" :class="isDark ? 'text-zinc-400' : 'text-slate-400'">Published Year / Edition</span>
                            <span class="font-bold" :class="isDark ? 'text-white' : 'text-slate-900'">{{ book.published_year || 'N/A' }} <span v-if="book.edition">({{ book.edition }})</span></span>
                        </div>

                        <div>
                            <span class="block font-medium" :class="isDark ? 'text-zinc-400' : 'text-slate-400'">Page Count</span>
                            <span class="font-bold" :class="isDark ? 'text-white' : 'text-slate-900'">{{ book.page_count ? `${book.page_count} pages` : 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Synopsis -->
                <div v-if="book.summary || book.description" class="space-y-4 pt-4 border-t" :class="isDark ? 'border-zinc-800' : 'border-slate-100'">
                    <h3
                        class="text-sm font-bold font-heading uppercase tracking-wider border-b pb-2"
                        :class="isDark ? 'text-white border-zinc-800' : 'text-slate-900 border-slate-100'"
                    >
                        Synopsis & Description
                    </h3>

                    <div v-if="book.summary" class="p-4 rounded-2xl border" :class="isDark ? 'bg-zinc-800/40 border-zinc-800' : 'bg-slate-50 border-slate-100'">
                        <span class="text-[11px] font-bold uppercase tracking-wider block mb-1" :class="isDark ? 'text-zinc-400' : 'text-slate-400'">Short Summary</span>
                        <p class="text-xs leading-relaxed" :class="isDark ? 'text-zinc-200' : 'text-slate-700'">{{ book.summary }}</p>
                    </div>

                    <div v-if="book.description" class="space-y-1">
                        <span class="text-[11px] font-bold uppercase tracking-wider block" :class="isDark ? 'text-zinc-400' : 'text-slate-400'">Full Description</span>
                        <p class="text-xs whitespace-pre-line leading-relaxed" :class="isDark ? 'text-zinc-200' : 'text-slate-700'">{{ book.description }}</p>
                    </div>
                </div>

            </div>

            <!-- Confirm Delete Modal -->
            <ConfirmDeleteModal
                :show="showDeleteModal"
                title="Delete Book?"
                :item-name="book.title"
                message="Are you sure you want to delete this book? This will soft-delete the record."
                @close="showDeleteModal = false"
                @confirm="confirmDelete"
            />
        </div>
    </AdminLayout>
</template>
