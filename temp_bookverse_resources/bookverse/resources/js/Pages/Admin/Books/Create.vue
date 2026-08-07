<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Input from '@/Components/UI/Input.vue';
import Textarea from '@/Components/UI/Textarea.vue';
import Select from '@/Components/UI/Select.vue';
import Checkbox from '@/Components/UI/Checkbox.vue';
import Button from '@/Components/UI/Button.vue';

const props = defineProps<{
    categories: Array<{ id: number; name: string }>;
    authors: Array<{ id: number; name: string }>;
    publishers: Array<{ id: number; name: string }>;
    formats: Array<{ value: string; label: string }>;
    preview_types: Array<{ value: string; label: string }>;
}>();

const coverPreview = ref('');
const hasPreviewError = ref(false);

const form = useForm({
    title: '',
    slug: '',
    sku: '',
    isbn_13: '',
    summary: '',
    description: '',
    category_id: '' as string | number,
    publisher_id: '' as string | number,
    authors: [] as number[],
    primary_author_id: '' as string | number,
    format: 'paperback',
    language: 'bn',
    price: '' as string | number,
    discount_price: '' as string | number,
    stock_quantity: 10,
    edition: '',
    page_count: '' as string | number,
    published_year: '' as string | number,
    meta_title: '',
    meta_description: '',
    is_active: true,
    is_featured: false,
    is_bestseller: false,
    cover: null as File | null,
    cover_url: '',
    preview_type: 'none',
    max_preview_pages: 15,
    sample_pages_upload: [] as File[],
    sample_pdf_upload: null as File | null,
});

function handleCoverChange(e: Event) {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.cover = file;
        coverPreview.value = URL.createObjectURL(file);
        hasPreviewError.value = false;
        form.cover_url = '';
    }
}

function clearCover() {
    form.cover = null;
    coverPreview.value = '';
    hasPreviewError.value = false;
    form.cover_url = '';
    const fileInput = document.querySelector('#book-cover-input') as HTMLInputElement;
    if (fileInput) fileInput.value = '';
}

function handleSamplePagesChange(e: Event) {
    const target = e.target as HTMLInputElement;
    if (target.files) {
        form.sample_pages_upload = Array.from(target.files);
    }
}

function handleSamplePdfChange(e: Event) {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.sample_pdf_upload = target.files[0];
    }
}

function submitForm() {
    if (form.primary_author_id && (!form.authors || form.authors.length === 0)) {
        form.authors = [Number(form.primary_author_id)];
    }
    form.post('/admin/books', {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Create New Book — Admin" />

    <AdminLayout>
        <template #header>Create New Book</template>

        <div class="max-w-5xl mx-auto space-y-6 font-sans">
            <!-- Header Banner -->
            <div class="flex items-center justify-between p-6 rounded-2xl shadow-sm border transition-colors bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white">
                <div>
                    <h1 class="text-2xl font-black font-heading tracking-tight">Create New Book</h1>
                    <p class="text-xs mt-1 text-slate-500 dark:text-zinc-400">
                        Add a new book entry to the BookVerse catalog with pricing, metadata, and sample preview.
                    </p>
                </div>
                <Link href="/admin/books">
                    <Button variant="secondary" size="sm" class="text-xs font-bold">
                        ← Back to Catalog
                    </Button>
                </Link>
            </div>

            <!-- Create Form Card -->
            <form
                @submit.prevent="submitForm"
                class="border rounded-2xl p-8 space-y-8 shadow-sm transition-colors bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-slate-100"
            >
                <!-- 1. General Book Details -->
                <div class="space-y-4">
                    <h3 class="text-sm font-bold font-heading uppercase tracking-wider border-b pb-2 text-slate-900 dark:text-white border-slate-100 dark:border-zinc-800">
                        1. General Details
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Input v-model="form.title" label="Book Title *" placeholder="e.g. Deyal" :error="form.errors.title" />
                        <Input v-model="form.slug" label="URL Slug (Optional)" placeholder="e.g. deyal" :error="form.errors.slug" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Input v-model="form.sku" label="SKU Code *" placeholder="e.g. BK-DEYAL-001" :error="form.errors.sku" />
                        <Input v-model="form.isbn_13" label="ISBN-13 (Optional)" placeholder="e.g. 9789849501234" :error="form.errors.isbn_13" />
                    </div>

                    <Textarea v-model="form.summary" label="Short Summary" placeholder="Brief 1-2 sentence overview..." :rows="2" :error="form.errors.summary" />
                    <Textarea v-model="form.description" label="Full Description" placeholder="Detailed synopsis..." :rows="4" :error="form.errors.description" />
                </div>

                <!-- 2. Relationships & Taxonomy -->
                <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-zinc-800">
                    <h3 class="text-sm font-bold font-heading uppercase tracking-wider border-b pb-2 text-slate-900 dark:text-white border-slate-100 dark:border-zinc-800">
                        2. Classification & Authors
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <Select v-model="form.category_id" label="Category *" :error="form.errors.category_id">
                            <option value="">Select Category</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </Select>

                        <Select v-model="form.primary_author_id" label="Primary Author *" :error="form.errors.primary_author_id">
                            <option value="">Select Primary Author</option>
                            <option v-for="a in authors" :key="a.id" :value="a.id">{{ a.name }}</option>
                        </Select>

                        <Select v-model="form.publisher_id" label="Publisher" :error="form.errors.publisher_id">
                            <option value="">Select Publisher</option>
                            <option v-for="p in publishers" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </Select>
                    </div>
                </div>

                <!-- 3. Pricing & Inventory -->
                <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-zinc-800">
                    <h3 class="text-sm font-bold font-heading uppercase tracking-wider border-b pb-2 text-slate-900 dark:text-white border-slate-100 dark:border-zinc-800">
                        3. Pricing & Inventory
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <Input v-model="form.price" type="number" step="0.01" label="Regular Price (৳) *" placeholder="500.00" :error="form.errors.price" />
                        <Input v-model="form.discount_price" type="number" step="0.01" label="Discount Price (৳)" placeholder="400.00" :error="form.errors.discount_price" />
                        <Input v-model="form.stock_quantity" type="number" label="Stock Quantity *" placeholder="10" :error="form.errors.stock_quantity" />

                        <Select v-model="form.format" label="Book Format *">
                            <option v-for="f in formats" :key="f.value" :value="f.value">{{ f.label }}</option>
                        </Select>
                    </div>
                </div>

                <!-- 4. Media & Visibility Toggles -->
                <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-zinc-800">
                    <h3 class="text-sm font-bold font-heading uppercase tracking-wider border-b pb-2 text-slate-900 dark:text-white border-slate-100 dark:border-zinc-800">
                        4. Cover Image & Visibility Settings
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                        <label class="block text-xs font-bold mb-1.5 text-slate-800 dark:text-slate-200">Upload Cover Image File</label>
                            <div class="flex items-center gap-3">
                                <div v-if="coverPreview && !hasPreviewError" class="relative group shrink-0">
                                    <img
                                        :src="coverPreview"
                                        alt="Cover Preview"
                                        class="w-14 h-20 object-cover rounded-xl border border-slate-200 dark:border-zinc-700 shadow-md"
                                        @error="hasPreviewError = true"
                                    />
                                    <button
                                        type="button"
                                        @click="clearCover"
                                        class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-md transition-all opacity-0 group-hover:opacity-100 focus:opacity-100"
                                        title="Remove Cover"
                                    >
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                                <input
                                    id="book-cover-input"
                                    type="file"
                                    @change="handleCoverChange"
                                    accept="image/*"
                                    class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer"
                                />
                            </div>
                            <p v-if="form.errors.cover" class="text-xs text-rose-500 font-bold mt-1.5">{{ form.errors.cover }}</p>
                        </div>

                        <Input v-model="form.cover_url" label="Or Cover Image URL" placeholder="https://images.unsplash.com/..." :error="form.errors.cover_url" />
                    </div>

                    <div class="flex flex-wrap items-center gap-6 pt-2">
                        <Checkbox v-model:checked="form.is_active" label="Active & Available" />
                        <Checkbox v-model:checked="form.is_featured" label="Featured Book" />
                        <Checkbox v-model:checked="form.is_bestseller" label="Bestseller Badge" />
                    </div>
                </div>

                <!-- 5. Book Sample Preview (একটু পড়ে দেখুন) -->
                <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-zinc-800">
                    <h3 class="text-sm font-bold font-heading uppercase tracking-wider border-b pb-2 text-slate-900 dark:text-white border-slate-100 dark:border-zinc-800 flex items-center gap-2">
                        <span class="text-rose-600">📖 5. Book Sample Preview</span>
                        <span class="text-xs normal-case text-slate-400 font-normal">(একটু পড়ে দেখুন настройка)</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Select v-model="form.preview_type" label="Sample Preview Type" :error="form.errors.preview_type">
                            <option v-for="t in preview_types" :key="t.value" :value="t.value">{{ t.label }}</option>
                        </Select>

                        <Input
                            v-model="form.max_preview_pages"
                            type="number"
                            min="1"
                            max="50"
                            label="Max Sample Pages Limit"
                            placeholder="15"
                            :error="form.errors.max_preview_pages"
                        />
                    </div>

                    <div v-if="form.preview_type === 'images'" class="p-4 rounded-xl bg-slate-50 dark:bg-zinc-800/60 border border-slate-200 dark:border-zinc-700 space-y-2">
                        <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">Upload Sample Page Images (Multiple)</label>
                        <input
                            type="file"
                            multiple
                            accept="image/*"
                            @change="handleSamplePagesChange"
                            class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100 cursor-pointer"
                        />
                        <p class="text-[11px] text-slate-400">Select images of table of contents, preface, or first chapter pages.</p>
                    </div>

                    <div v-if="form.preview_type === 'pdf'" class="p-4 rounded-xl bg-slate-50 dark:bg-zinc-800/60 border border-slate-200 dark:border-zinc-700 space-y-2">
                        <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">Upload Sample PDF File (Max 20MB)</label>
                        <input
                            type="file"
                            accept="application/pdf"
                            @change="handleSamplePdfChange"
                            class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100 cursor-pointer"
                        />
                        <p class="text-[11px] text-slate-400">Will be streamed securely via 10-minute temporary signed URLs.</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 dark:border-zinc-800">
                    <Link href="/admin/books">
                        <Button variant="secondary" size="md">Cancel</Button>
                    </Link>
                    <Button variant="brand" size="md" type="submit" :disabled="form.processing">
                        Create Book
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>