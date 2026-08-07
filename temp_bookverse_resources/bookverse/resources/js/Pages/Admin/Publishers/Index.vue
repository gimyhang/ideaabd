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

export interface PublisherItem {
    id: number;
    name: string;
    slug: string;
    description?: string;
    logo_url?: string;
    website?: string;
    phone?: string;
    address?: string;
    is_featured: boolean;
}

interface PaginationMeta {
    data: PublisherItem[];
    current_page: number;
    last_page: number;
    total: number;
    links: PaginationLink[];
}

const props = defineProps<{
    publishers: PaginationMeta;
    filters: {
        search?: string;
    };
}>();

const searchQuery = ref(props.filters.search || '');
const showModal = ref(false);
const editingPublisher = ref<PublisherItem | null>(null);

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
    description: '',
    logo: null as File | string | null,
    logo_url: '',
    website: '',
    phone: '',
    address: '',
    is_featured: false,
});

function handleSearch() {
    router.get(route('publishers.index'), { search: searchQuery.value || undefined }, { preserveState: true, replace: true });
}

function openCreateModal() {
    editingPublisher.value = null;
    form.reset();
    form.clearErrors();
    form.logo = null;
    form.logo_url = '';
    showModal.value = true;
}

function handleLogoSelect(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        form.logo = file;
        form.logo_url = window.URL.createObjectURL(file);
    }
}

function clearPublisherLogo() {
    form.logo = null;
    form.logo_url = '';
    const fileInput = document.querySelector('#publisher-logo-input') as HTMLInputElement;
    if (fileInput) fileInput.value = '';
}

function openEditModal(pub: PublisherItem) {
    editingPublisher.value = pub;
    form.clearErrors();
    form.name = pub.name;
    form.slug = pub.slug;
    form.description = pub.description || '';
    form.logo = null;
    form.logo_url = (pub as any).logo_url || (pub as any).logo || '';
    form.website = pub.website || '';
    form.phone = pub.phone || '';
    form.address = pub.address || '';
    form.is_featured = pub.is_featured;
    showModal.value = true;
}

function submitPublisher() {
    if (editingPublisher.value) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(route('publishers.update', editingPublisher.value.id), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                showModal.value = false;
            },
        });
    } else {
        form.post(route('publishers.store'), {
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
const publisherToDelete = ref<PublisherItem | null>(null);

function promptDeletePublisher(pub: PublisherItem) {
    publisherToDelete.value = pub;
    showDeleteModal.value = true;
}

function confirmDeletePublisher() {
    if (publisherToDelete.value) {
        router.delete(route('publishers.destroy', publisherToDelete.value.id), {
            preserveScroll: true,
            onFinish: () => {
                showDeleteModal.value = false;
                publisherToDelete.value = null;
            },
        });
    }
}
</script>

<template>
    <AdminLayout>
        <Head title="Publisher Management — Admin Portal" />

        <div class="space-y-6 font-sans">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-2xl shadow-sm border transition-colors bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white">
                <div>
                    <h1 class="text-2xl font-black font-heading tracking-tight">Publishers Directory</h1>
                    <p class="text-xs mt-1 text-slate-500 dark:text-zinc-400">
                        Manage publishing houses and press profiles for BookVerse catalog.
                    </p>
                </div>

                <Button v-if="can('create-publishers')" variant="brand" size="md" class="font-bold shadow-lg shadow-sky-600/20" @click="openCreateModal()">
                    <span>Add New Publisher</span>
                </Button>
            </div>

            <!-- Table Container Panel -->
            <div class="border rounded-2xl overflow-hidden shadow-sm transition-colors bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 space-y-4 p-5">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-slate-100 dark:border-zinc-800 pb-4">
                    <div class="w-full sm:w-80">
                        <Input
                            v-model="searchQuery"
                            placeholder="Search publishers by name..."
                        />
                    </div>

                    <span class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Total Publishers: {{ publishers.total }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b uppercase tracking-wider font-semibold text-[10px] bg-slate-50 dark:bg-zinc-950/60 text-slate-500 dark:text-zinc-400 border-slate-200 dark:border-zinc-800">
                                <th class="py-3.5 px-4">Publisher</th>
                                <th class="py-3.5 px-4">Website / Contact</th>
                                <th class="py-3.5 px-4">Address</th>
                                <th class="py-3.5 px-4">Featured</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800 text-slate-700 dark:text-slate-200">
                            <tr v-for="pub in publishers.data" :key="pub.id" class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/40 transition">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <Avatar :src="pub.logo_url" :name="pub.name" size="md" />
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-white font-heading text-sm">{{ pub.name }}</p>
                                            <p v-if="pub.description" class="text-[11px] text-slate-500 dark:text-zinc-400 line-clamp-1 max-w-xs font-serif">{{ pub.description }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-600 dark:text-zinc-300">
                                    <a v-if="pub.website" :href="pub.website" target="_blank" class="text-sky-600 dark:text-sky-400 hover:underline block font-semibold">{{ pub.website }}</a>
                                    <span v-if="pub.phone" class="text-slate-500 dark:text-zinc-400 text-[11px] block">{{ pub.phone }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 dark:text-zinc-300">{{ pub.address || '—' }}</td>
                                <td class="py-3.5 px-4">
                                    <Badge v-if="pub.is_featured" variant="brand" size="sm" dot>Featured</Badge>
                                    <Badge v-else variant="default" size="sm">Standard</Badge>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <!-- Edit Publisher -->
                                        <button
                                            v-if="can('edit-publishers')"
                                            @click="openEditModal(pub)"
                                            class="p-2 rounded-xl border transition-all duration-200 shadow-sm bg-amber-50 hover:bg-amber-500 text-amber-600 hover:text-white border-amber-200/80 dark:bg-amber-500/10 dark:hover:bg-amber-500 dark:text-amber-400 dark:hover:text-white dark:border-amber-500/20"
                                            title="Edit Publisher"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <!-- Delete Publisher -->
                                        <button
                                            v-if="can('delete-publishers')"
                                            @click="promptDeletePublisher(pub)"
                                            class="p-2 rounded-xl border transition-all duration-200 shadow-sm bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border-rose-200/80 dark:bg-rose-500/10 dark:hover:bg-rose-600 dark:text-rose-400 dark:hover:text-white dark:border-rose-500/20"
                                            title="Delete Publisher"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="publishers.data.length === 0">
                                <td colspan="5" class="py-8 text-center text-slate-400 dark:text-zinc-500">
                                    No publishers found. Click "+ Add New Publisher" to create one.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination -->
                <div class="border-t border-slate-100 dark:border-zinc-800 pt-3">
                    <Pagination :links="publishers.links" />
                </div>
            </div>
        </div>

        <!-- Add/Edit Publisher Modal -->
        <Modal
            :show="showModal"
            :title="editingPublisher ? 'Edit Publisher Profile' : 'Add New Publisher'"
            maxWidth="lg"
            @close="showModal = false"
        >
            <form @submit.prevent="submitPublisher" class="space-y-4">
                <Input
                    v-model="form.name"
                    label="Publisher / Press Name *"
                    placeholder="e.g. প্রথমা প্রকাশন"
                    :error="form.errors.name"
                    required
                />

                <Input
                    v-model="form.slug"
                    label="Custom Slug (Optional)"
                    placeholder="e.g. prothoma-prokashon"
                    :error="form.errors.slug"
                />

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">Publisher Logo</label>
                    <input
                        id="publisher-logo-input"
                        type="file"
                        accept="image/*"
                        @change="handleLogoSelect"
                        class="w-full text-xs text-slate-500 dark:text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 dark:file:bg-sky-950/60 dark:file:text-sky-400 hover:file:bg-sky-100 cursor-pointer"
                    />
                    <div v-if="form.logo_url" class="mt-2 flex items-center gap-3">
                        <div class="relative group">
                            <img :src="form.logo_url" class="h-16 w-16 object-cover rounded-xl border border-slate-200 dark:border-zinc-800 shadow-sm" alt="Publisher Logo" />
                            <button
                                type="button"
                                @click="clearPublisherLogo"
                                class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-md transition-all opacity-0 group-hover:opacity-100 focus:opacity-100"
                                title="Remove Logo"
                            >
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-zinc-400 font-mono">Logo Selected</span>
                    </div>
                </div>

                <Textarea
                    v-model="form.description"
                    label="Description / Press Profile"
                    placeholder="Details about publishing focus and history..."
                    :rows="3"
                    :error="form.errors.description"
                />

                <div class="grid grid-cols-2 gap-4">
                    <Input
                        v-model="form.website"
                        label="Official Website URL"
                        placeholder="https://example.com"
                        :error="form.errors.website"
                    />

                    <Input
                        v-model="form.phone"
                        label="Phone Number"
                        placeholder="+880 1700-000000"
                        :error="form.errors.phone"
                    />
                </div>

                <Input
                    v-model="form.address"
                    label="Office Address"
                    placeholder="e.g. Banglabazar, Dhaka"
                    :error="form.errors.address"
                />

                <Checkbox
                    v-model="form.is_featured"
                    label="Featured Publisher"
                    description="Highlight this publishing house in catalog filters and sponsor banners."
                />

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-zinc-800">
                    <Button variant="secondary" size="sm" type="button" @click="showModal = false">Cancel</Button>
                    <Button variant="brand" size="sm" type="submit" :loading="form.processing">
                        {{ editingPublisher ? 'Update Publisher' : 'Save Publisher' }}
                    </Button>
                </div>
            </form>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <ConfirmDeleteModal
            :show="showDeleteModal"
            title="Delete Publisher"
            :item-name="publisherToDelete?.name"
            message="Are you sure you want to delete this publisher? This action cannot be undone."
            @confirm="confirmDeletePublisher"
            @close="showDeleteModal = false"
        />
    </AdminLayout>
</template>
