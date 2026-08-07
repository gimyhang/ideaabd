<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermission } from '@/Composables/usePermission';

const { can } = usePermission();
import Input from '@/Components/UI/Input.vue';
import Textarea from '@/Components/UI/Textarea.vue';
import Checkbox from '@/Components/UI/Checkbox.vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';
import ConfirmDeleteModal from '@/Components/UI/ConfirmDeleteModal.vue';
import CategoryTree, { CategoryNode } from '@/Components/Catalog/CategoryTree.vue';

interface FlatCategoryOption {
    id: number;
    name: string;
    parent_id?: number | null;
}

const props = defineProps<{
    categories: CategoryNode[];
    flatCategories: FlatCategoryOption[];
}>();

const showModal = ref(false);
const editingCategory = ref<CategoryNode | null>(null);

const form = useForm({
    name: '',
    slug: '',
    description: '',
    parent_id: '' as string | number,
    is_active: true,
    sort_order: 0,
    image_url: '',
    image: null as File | null,
});

function openCreateModal(parentCat?: CategoryNode) {
    editingCategory.value = null;
    form.reset();
    form.clearErrors();
    form.is_active = true;
    form.sort_order = 0;
    form.image_url = '';
    form.image = null;
    if (parentCat) {
        form.parent_id = parentCat.id;
    } else {
        form.parent_id = '';
    }
    showModal.value = true;
}

function handleImageSelect(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        form.image = file;
        form.image_url = window.URL.createObjectURL(file);
    }
}

function clearCategoryImage() {
    form.image = null;
    form.image_url = '';
    const fileInput = document.querySelector('#category-image-input') as HTMLInputElement;
    if (fileInput) fileInput.value = '';
}

function openEditModal(cat: CategoryNode) {
    editingCategory.value = cat;
    form.clearErrors();
    form.name = cat.name;
    form.slug = cat.slug;
    form.description = cat.description || '';
    form.parent_id = cat.parent_id || '';
    form.is_active = cat.is_active;
    form.sort_order = cat.sort_order;
    form.image_url = (cat as any).image_url || '';
    form.image = null;
    showModal.value = true;
}

function submitCategory() {
    if (editingCategory.value) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(route('categories.update', editingCategory.value.id), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                showModal.value = false;
            },
        });
    } else {
        form.post(route('categories.store'), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
}

const showDeleteModal = ref(false);
const categoryToDelete = ref<CategoryNode | null>(null);

function handleDeleteCategory(cat: CategoryNode) {
    categoryToDelete.value = cat;
    showDeleteModal.value = true;
}

function confirmDeleteCategory() {
    if (categoryToDelete.value) {
        router.delete(route('categories.destroy', categoryToDelete.value.id), {
            preserveScroll: true,
            onFinish: () => {
                showDeleteModal.value = false;
                categoryToDelete.value = null;
            },
        });
    }
}
</script>

<template>
    <AdminLayout>
        <Head title="Category Management — Admin Portal" />

        <div class="space-y-6 font-sans">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-2xl shadow-sm border transition-colors bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white">
                <div>
                    <h1 class="text-2xl font-black font-heading tracking-tight">Book Category Taxonomy</h1>
                    <p class="text-xs mt-1 text-slate-500 dark:text-zinc-400">
                        Manage root and nested sub-categories for BookVerse library catalog.
                    </p>
                </div>

                <Button v-if="can('create-categories')" variant="brand" size="md" class="font-bold shadow-lg shadow-sky-600/20" @click="openCreateModal()">
                    <span>Create Root Category</span>
                </Button>
            </div>

            <!-- Category Hierarchy Tree Panel -->
            <div class="p-6 rounded-2xl border shadow-sm space-y-6 transition-colors bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-slate-100">
                <div class="border-b border-slate-100 dark:border-zinc-800 pb-4">
                    <h2 class="text-base font-bold font-heading text-slate-900 dark:text-white">Category Structure Tree</h2>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">Hierarchical list of parent and child categories.</p>
                </div>

                <div v-if="categories && categories.length > 0">
                    <CategoryTree
                        :nodes="categories"
                        @edit="openEditModal"
                        @delete="handleDeleteCategory"
                        @add-sub="(parent) => openCreateModal(parent)"
                    />
                </div>

                <div v-else class="text-center py-12 space-y-3 rounded-2xl border bg-slate-50 dark:bg-zinc-950/50 border-slate-200 dark:border-zinc-800">
                    <p class="text-sm font-semibold text-slate-700 dark:text-zinc-300">No categories found.</p>
                    <p class="text-xs text-slate-500 dark:text-zinc-400">Create root categories to populate initial taxonomy.</p>
                    <Button v-if="can('create-categories')" variant="brand" size="sm" @click="openCreateModal()">
                        Create First Category
                    </Button>
                </div>
            </div>
        </div>

        <!-- Add/Edit Category Modal -->
        <Modal
            :show="showModal"
            :title="editingCategory ? 'Edit Category' : 'Create New Category'"
            maxWidth="lg"
            @close="showModal = false"
        >
            <form @submit.prevent="submitCategory" class="space-y-4">
                <Input
                    v-model="form.name"
                    label="Category Name *"
                    placeholder="e.g. কথাসাহিত্য or Fiction"
                    :error="form.errors.name"
                    required
                />

                <Input
                    v-model="form.slug"
                    label="Custom Slug (Optional)"
                    placeholder="e.g. fiction"
                    :error="form.errors.slug"
                />

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">Category Logo / Cover Image</label>
                    <input
                        id="category-image-input"
                        type="file"
                        accept="image/*"
                        @change="handleImageSelect"
                        class="w-full text-xs text-slate-500 dark:text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 dark:file:bg-sky-950/60 dark:file:text-sky-400 hover:file:bg-sky-100 cursor-pointer"
                    />
                    <div v-if="form.image_url" class="mt-2 flex items-center gap-3">
                        <div class="relative group">
                            <img :src="form.image_url" class="h-16 w-16 object-cover rounded-xl border border-slate-200 dark:border-zinc-800 shadow-sm" alt="Category Image" />
                            <button
                                type="button"
                                @click="clearCategoryImage"
                                class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-md transition-all opacity-0 group-hover:opacity-100 focus:opacity-100"
                                title="Remove Image"
                            >
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-zinc-400 font-mono">Image Selected</span>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">Parent Category (Optional)</label>
                    <select
                        v-model="form.parent_id"
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs font-semibold bg-white dark:bg-zinc-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-zinc-800 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none"
                    >
                        <option value="">None (Root Level Category)</option>
                        <option
                            v-for="flat in flatCategories"
                            :key="flat.id"
                            :value="flat.id"
                            :disabled="editingCategory?.id === flat.id"
                        >
                            {{ flat.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.parent_id" class="text-[11px] font-bold text-rose-500">
                        {{ form.errors.parent_id }}
                    </p>
                </div>

                <Textarea
                    v-model="form.description"
                    label="Short Description"
                    placeholder="Brief summary of books contained within this genre..."
                    :rows="3"
                    :error="form.errors.description"
                />

                <Input
                    v-model.number="form.sort_order"
                    label="Sort Order Index"
                    type="number"
                    placeholder="0"
                    :error="form.errors.sort_order"
                />

                <Checkbox
                    v-model="form.is_active"
                    label="Active Category"
                    description="When disabled, this category and its books will be hidden from public catalog navigation."
                />

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-zinc-800">
                    <Button variant="secondary" size="sm" type="button" @click="showModal = false">Cancel</Button>
                    <Button variant="brand" size="sm" type="submit" :loading="form.processing">
                        {{ editingCategory ? 'Update Category' : 'Save Category' }}
                    </Button>
                </div>
            </form>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <ConfirmDeleteModal
            :show="showDeleteModal"
            title="Delete Category"
            :item-name="categoryToDelete?.name"
            message="Are you sure you want to delete this category and all its nested sub-categories? This action cannot be undone."
            @confirm="confirmDeleteCategory"
            @close="showDeleteModal = false"
        />
    </AdminLayout>
</template>
