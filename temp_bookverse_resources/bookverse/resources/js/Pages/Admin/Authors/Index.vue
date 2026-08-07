<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermission } from '@/Composables/usePermission';

const { can } = usePermission();
import Input from '@/Components/UI/Input.vue';
import Textarea from '@/Components/UI/Textarea.vue';
import Checkbox from '@/Components/UI/Checkbox.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';
import Avatar from '@/Components/UI/Avatar.vue';
import Modal from '@/Components/UI/Modal.vue';
import ConfirmDeleteModal from '@/Components/UI/ConfirmDeleteModal.vue';
import Pagination, { type PaginationLink } from '@/Components/UI/Pagination.vue';

export interface AuthorItem {
    id: number;
    name: string;
    slug: string;
    bio?: string;
    photo_url?: string;
    birth_date?: string;
    death_date?: string;
    is_featured: boolean;
}

interface PaginationMeta {
    data: AuthorItem[];
    current_page: number;
    last_page: number;
    total: number;
    links: PaginationLink[];
}

const props = defineProps<{
    authors: PaginationMeta;
    filters: {
        search?: string;
    };
}>();

const searchQuery = ref(props.filters.search || '');
const showModal = ref(false);
const editingAuthor = ref<AuthorItem | null>(null);

// Debounced live search
let searchTimer: ReturnType<typeof setTimeout> | null = null;
watch(searchQuery, () => {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        handleSearch();
    }, 300);
});

const form = useForm({
    name: '',
    slug: '',
    bio: '',
    photo: null as File | string | null,
    image_url: '',
    birth_date: '',
    death_date: '',
    is_featured: false,
});

function handleSearch() {
    router.get(route('authors.index'), { search: searchQuery.value || undefined }, { preserveState: true, replace: true });
}

function openCreateModal() {
    editingAuthor.value = null;
    form.reset();
    form.clearErrors();
    form.photo = null;
    form.image_url = '';
    showModal.value = true;
}

function handleImageSelect(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        form.photo = file;
        form.image_url = window.URL.createObjectURL(file);
    }
}

function clearAuthorImage() {
    form.photo = null;
    form.image_url = '';
    // Reset file input
    const fileInput = document.querySelector('#author-photo-input') as HTMLInputElement;
    if (fileInput) fileInput.value = '';
}

function openEditModal(author: AuthorItem) {
    editingAuthor.value = author;
    form.clearErrors();
    form.name = author.name;
    form.slug = author.slug;
    form.bio = author.bio || '';
    form.photo = null;
    form.image_url = (author as any).image_url || (author as any).photo || '';
    form.birth_date = author.birth_date || '';
    form.death_date = author.death_date || '';
    form.is_featured = author.is_featured;
    showModal.value = true;
}

function submitAuthor() {
    if (editingAuthor.value) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(route('authors.update', editingAuthor.value.id), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                showModal.value = false;
            },
        });
    } else {
        form.post(route('authors.store'), {
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
const authorToDelete = ref<AuthorItem | null>(null);

function promptDeleteAuthor(author: AuthorItem) {
    authorToDelete.value = author;
    showDeleteModal.value = true;
}

function confirmDeleteAuthor() {
    if (authorToDelete.value) {
        router.delete(route('authors.destroy', authorToDelete.value.id), {
            preserveScroll: true,
            onFinish: () => {
                showDeleteModal.value = false;
                authorToDelete.value = null;
            },
        });
    }
}
</script>

<template>
    <AdminLayout>
        <Head title="Author Management — Admin Portal" />

        <div class="space-y-6 font-sans">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-2xl shadow-sm border transition-colors bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white">
                <div>
                    <h1 class="text-2xl font-black font-heading tracking-tight">Authors Directory</h1>
                    <p class="text-xs mt-1 text-slate-500 dark:text-zinc-400">
                        Manage literary authors and biographic profiles for BookVerse catalog.
                    </p>
                </div>

                <Button v-if="can('create-authors')" variant="brand" size="md" class="font-bold shadow-lg shadow-sky-600/20" @click="openCreateModal()">
                    <span>Add New Author</span>
                </Button>
            </div>

            <!-- Table Container Panel -->
            <div class="border rounded-2xl overflow-hidden shadow-sm transition-colors bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 space-y-4 p-5">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-slate-100 dark:border-zinc-800 pb-4">
                    <div class="w-full sm:w-80">
                        <Input
                            v-model="searchQuery"
                            placeholder="Search authors by name..."
                        />
                    </div>

                    <span class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Total Authors: {{ authors.total }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b uppercase tracking-wider font-semibold text-[10px] bg-slate-50 dark:bg-zinc-950/60 text-slate-500 dark:text-zinc-400 border-slate-200 dark:border-zinc-800">
                                <th class="py-3.5 px-4">Author</th>
                                <th class="py-3.5 px-4">Slug</th>
                                <th class="py-3.5 px-4">Lifespan</th>
                                <th class="py-3.5 px-4">Featured</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800 text-slate-700 dark:text-slate-200">
                            <tr v-for="author in authors.data" :key="author.id" class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/40 transition">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <Avatar :src="author.photo_url" :name="author.name" size="md" />
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-white font-heading text-sm">{{ author.name }}</p>
                                            <p v-if="author.bio" class="text-[11px] text-slate-500 dark:text-zinc-400 line-clamp-1 max-w-xs font-serif">{{ author.bio }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-500 dark:text-zinc-400">/{{ author.slug }}</td>
                                <td class="py-3.5 px-4 font-mono text-slate-600 dark:text-zinc-300">
                                    <span v-if="author.birth_date">{{ author.birth_date }}</span>
                                    <span v-if="author.death_date"> — {{ author.death_date }}</span>
                                    <span v-if="!author.birth_date && !author.death_date" class="text-slate-400 dark:text-zinc-500">—</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <Badge v-if="author.is_featured" variant="brand" size="sm" dot>Featured</Badge>
                                    <Badge v-else variant="default" size="sm">Standard</Badge>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <!-- Edit Author -->
                                        <button
                                            v-if="can('edit-authors')"
                                            @click="openEditModal(author)"
                                            class="p-2 rounded-xl border transition-all duration-200 shadow-sm bg-amber-50 hover:bg-amber-500 text-amber-600 hover:text-white border-amber-200/80 dark:bg-amber-500/10 dark:hover:bg-amber-500 dark:text-amber-400 dark:hover:text-white dark:border-amber-500/20"
                                            title="Edit Author"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <!-- Delete Author -->
                                        <button
                                            v-if="can('delete-authors')"
                                            @click="promptDeleteAuthor(author)"
                                            class="p-2 rounded-xl border transition-all duration-200 shadow-sm bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border-rose-200/80 dark:bg-rose-500/10 dark:hover:bg-rose-600 dark:text-rose-400 dark:hover:text-white dark:border-rose-500/20"
                                            title="Delete Author"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="authors.data.length === 0">
                                <td colspan="5" class="py-8 text-center text-slate-400 dark:text-zinc-500">
                                    No authors found. Click "+ Add New Author" to create one.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination -->
                <div class="border-t border-slate-100 dark:border-zinc-800 pt-3">
                    <Pagination :links="authors.links" />
                </div>
            </div>
        </div>

        <!-- Add/Edit Author Modal -->
        <Modal
            :show="showModal"
            :title="editingAuthor ? 'Edit Author Profile' : 'Add New Author'"
            maxWidth="lg"
            @close="showModal = false"
        >
            <form @submit.prevent="submitAuthor" class="space-y-4">
                <Input
                    v-model="form.name"
                    label="Author Full Name *"
                    placeholder="e.g. হুমায়ূন আহমেদ"
                    :error="form.errors.name"
                    required
                />

                <Input
                    v-model="form.slug"
                    label="Custom Slug (Optional)"
                    placeholder="e.g. humayun-ahmed"
                    :error="form.errors.slug"
                />

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">Author Photo / Image</label>
                    <input
                        id="author-photo-input"
                        type="file"
                        accept="image/*"
                        @change="handleImageSelect"
                        class="w-full text-xs text-slate-500 dark:text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 dark:file:bg-sky-950/60 dark:file:text-sky-400 hover:file:bg-sky-100 cursor-pointer"
                    />
                    <div v-if="form.image_url" class="mt-2 flex items-center gap-3">
                        <div class="relative group">
                            <img :src="form.image_url" class="h-16 w-16 object-cover rounded-xl border border-slate-200 dark:border-zinc-800 shadow-sm" alt="Author Photo" />
                            <button
                                type="button"
                                @click="clearAuthorImage"
                                class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-md transition-all opacity-0 group-hover:opacity-100 focus:opacity-100"
                                title="Remove Image"
                            >
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-zinc-400 font-mono">Image Selected</span>
                    </div>
                </div>

                <Textarea
                    v-model="form.bio"
                    label="Biography / Literary Summary"
                    placeholder="Brief details about author's life, career, and famous creations..."
                    :rows="4"
                    :error="form.errors.bio"
                />

                <div class="grid grid-cols-2 gap-4">
                    <Input
                        v-model="form.birth_date"
                        label="Birth Date"
                        type="date"
                        :error="form.errors.birth_date"
                    />

                    <Input
                        v-model="form.death_date"
                        label="Death Date (If applicable)"
                        type="date"
                        :error="form.errors.death_date"
                    />
                </div>

                <Checkbox
                    v-model="form.is_featured"
                    label="Featured Author"
                    description="Highlight this author on home and catalog discovery carousels."
                />

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-zinc-800">
                    <Button variant="secondary" size="sm" type="button" @click="showModal = false">Cancel</Button>
                    <Button variant="brand" size="sm" type="submit" :loading="form.processing">
                        {{ editingAuthor ? 'Update Author' : 'Save Author' }}
                    </Button>
                </div>
            </form>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <ConfirmDeleteModal
            :show="showDeleteModal"
            title="Delete Author"
            :item-name="authorToDelete?.name"
            message="Are you sure you want to delete this author? This action cannot be undone and will permanently remove the profile record."
            @confirm="confirmDeleteAuthor"
            @close="showDeleteModal = false"
        />
    </AdminLayout>
</template>
