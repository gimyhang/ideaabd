<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import WishlistCard, { type WishlistBook } from '@/Components/Wishlist/WishlistCard.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';
import Modal from '@/Components/UI/Modal.vue';
import Input from '@/Components/UI/Input.vue';
import Checkbox from '@/Components/UI/Checkbox.vue';
import Pagination, { type PaginationLink } from '@/Components/UI/Pagination.vue';

interface Wishlist {
    id: number;
    name: string;
    slug: string;
    is_public: boolean;
    share_token?: string;
    is_default: boolean;
    items_count: number;
}

interface PaginatedItems {
    data: WishlistBook[];
    links: PaginationLink[];
    total: number;
}

const props = defineProps<{
    wishlists: Wishlist[];
    activeWishlist: Wishlist;
    items: PaginatedItems;
}>();

const selectedBookIds = ref<number[]>([]);
const showCreateModal = ref(false);
const showShareModal = ref(false);
const copiedLink = ref(false);

const createForm = useForm({
    name: '',
    is_public: false,
    is_default: false,
});

const isAllSelected = computed(() => {
    return props.items?.data.length > 0 && props.items.data.every(b => selectedBookIds.value.includes(b.id));
});

function toggleSelectAll() {
    if (isAllSelected.value) {
        selectedBookIds.value = [];
    } else {
        selectedBookIds.value = props.items?.data.map(b => b.id) || [];
    }
}

function toggleSelectBook(id: number) {
    const idx = selectedBookIds.value.indexOf(id);
    if (idx > -1) {
        selectedBookIds.value.splice(idx, 1);
    } else {
        selectedBookIds.value.push(id);
    }
}

function handleSelectTab(wishlistId: number) {
    selectedBookIds.value = [];
    router.get(route('wishlist.index'), { wishlist_id: wishlistId }, { preserveState: true });
}

function handleMoveSingleToCart(bookId: number) {
    router.post(route('wishlist.move-to-cart', props.activeWishlist.id), {
        book_ids: [bookId],
    }, { preserveScroll: true });
}

function handleMoveSelectedToCart() {
    if (selectedBookIds.value.length === 0) return;
    router.post(route('wishlist.move-to-cart', props.activeWishlist.id), {
        book_ids: selectedBookIds.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            selectedBookIds.value = [];
        },
    });
}

function handleRemoveSingle(bookId: number) {
    router.delete(route('wishlist.items.remove', [props.activeWishlist.id, bookId]), {
        preserveScroll: true,
    });
}

function submitCreateWishlist() {
    createForm.post(route('wishlist.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
    });
}

function handleTogglePublic() {
    router.put(route('wishlist.update', props.activeWishlist.id), {
        name: props.activeWishlist.name,
        is_public: !props.activeWishlist.is_public,
        is_default: props.activeWishlist.is_default,
    }, { preserveScroll: true });
}

function copyShareLink() {
    if (!props.activeWishlist.share_token) return;
    const url = typeof window !== 'undefined' ? `${window.location.origin}/wishlist/shared/${props.activeWishlist.share_token}` : '';
    if (url) {
        navigator.clipboard.writeText(url);
        copiedLink.value = true;
        setTimeout(() => {
            copiedLink.value = false;
        }, 2000);
    }
}

const shareUrl = computed(() => {
    if (typeof window === 'undefined' || !props.activeWishlist?.share_token) return '';
    return `${window.location.origin}/wishlist/shared/${props.activeWishlist.share_token}`;
});
</script>

<template>
    <Head title="My Wishlists — BookVerse" />

    <MainLayout>
        <div class="bg-slate-50/50 min-h-screen py-10">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 font-sans">
                <!-- Header Banner -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-2xl shadow-sm border transition-colors bg-white dark:bg-zinc-900 border-slate-200/80 dark:border-zinc-800 text-slate-900 dark:text-white">
                    <div>
                        <h1 class="text-2xl font-black font-heading tracking-tight bg-gradient-to-r from-slate-900 to-slate-700 dark:from-white dark:to-zinc-300 bg-clip-text text-transparent">My Wishlists</h1>
                        <p class="text-xs mt-1 text-slate-500 dark:text-zinc-400 font-medium">
                            Save favorite books, organize customized reading lists, and share with friends.
                        </p>
                    </div>

                    <button
                        @click="showCreateModal = true"
                        class="px-4 py-2.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-sky-500/20 transition duration-300 transform active:scale-98 cursor-pointer flex items-center justify-center gap-1.5"
                    >
                        <span>+ Create Wishlist</span>
                    </button>
                </div>

                <!-- Wishlist Selection Tabs -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1.5 scrollbar-thin">
                    <button
                        v-for="w in wishlists"
                        :key="w.id"
                        @click="handleSelectTab(w.id)"
                        class="px-4 py-2.5 rounded-xl text-xs font-extrabold whitespace-nowrap transition duration-300 border flex items-center gap-2 cursor-pointer transform active:scale-98"
                        :class="activeWishlist?.id === w.id
                            ? 'bg-gradient-to-r from-sky-500 to-indigo-600 text-white border-sky-500/30 shadow-md shadow-sky-500/15'
                            : 'bg-white dark:bg-zinc-900 border-slate-200/80 dark:border-zinc-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-800 hover:border-slate-300'"
                    >
                        <span>{{ w.name }}</span>
                        <Badge v-if="w.is_default" variant="default" size="sm" class="py-0 px-1.5 text-[9px] uppercase tracking-wider">Default</Badge>
                        <span class="opacity-70 font-mono text-[10px] font-bold">({{ w.items_count }})</span>
                    </button>
                </div>

                <!-- Active Wishlist Toolbar & Items Card -->
                <div class="border border-slate-200/80 rounded-2xl p-6 shadow-xs space-y-6 transition-colors bg-white dark:bg-zinc-900 dark:border-zinc-800 text-slate-900 dark:text-slate-100">
                    <!-- Toolbar Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-zinc-800 pb-4">
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg font-black font-heading text-slate-900 dark:text-white">{{ activeWishlist?.name }}</h2>
                            <Badge :variant="activeWishlist?.is_public ? 'success' : 'default'" size="sm" dot>
                                {{ activeWishlist?.is_public ? 'Public Shared' : 'Private' }}
                            </Badge>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Toggle Public Sharing -->
                            <button
                                type="button"
                                @click="handleTogglePublic"
                                class="px-3.5 py-2 rounded-xl border text-xs font-bold transition duration-300 border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-800 cursor-pointer"
                            >
                                {{ activeWishlist?.is_public ? 'Make Private' : 'Make Public' }}
                            </button>

                            <!-- Share Link Modal Opener -->
                            <button
                                v-if="activeWishlist?.is_public"
                                type="button"
                                @click="showShareModal = true"
                                class="px-3.5 py-2 rounded-xl border text-xs font-bold transition duration-300 border-sky-200 dark:border-sky-500/20 text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-500/10 hover:bg-sky-100 cursor-pointer"
                            >
                                🔗 Share Link
                            </button>
                        </div>
                    </div>

                    <!-- Bulk Selection Controls Bar -->
                    <div v-if="items?.data.length > 0" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl bg-slate-50/50 dark:bg-zinc-950/60 border border-slate-200/60 dark:border-zinc-800">
                        <label class="inline-flex items-center gap-2.5 cursor-pointer select-none text-xs font-extrabold text-slate-700 dark:text-zinc-300">
                            <input
                                type="checkbox"
                                :checked="isAllSelected"
                                @change="toggleSelectAll"
                                class="w-4 h-4 rounded border-slate-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-sky-600 focus:ring-sky-500/20"
                            />
                            <span>Select All ({{ items.total }} item{{ items.total !== 1 ? 's' : '' }})</span>
                        </label>

                        <div class="flex items-center gap-2">
                            <button
                                :disabled="selectedBookIds.length === 0"
                                @click="handleMoveSelectedToCart"
                                class="px-4 py-2 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 disabled:opacity-50 text-white font-extrabold text-xs rounded-xl shadow-md transition duration-300 transform active:scale-98 cursor-pointer flex items-center gap-1.5"
                            >
                                🛒 Move Selected to Cart ({{ selectedBookIds.length }})
                            </button>
                        </div>
                    </div>

                    <!-- Items Grid List -->
                    <div v-if="items?.data.length > 0" class="space-y-3">
                        <WishlistCard
                            v-for="book in items.data"
                            :key="book.id"
                            :book="book"
                            :selected="selectedBookIds.includes(book.id)"
                            @toggle-select="toggleSelectBook"
                            @move-to-cart="handleMoveSingleToCart"
                            @remove="handleRemoveSingle"
                        />
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-16 space-y-4 rounded-2xl border border-dashed border-slate-200 dark:border-zinc-800">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-zinc-800 mx-auto flex items-center justify-center text-3xl">
                            🤍
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">No books in this wishlist</h3>
                            <p class="text-xs text-slate-500 dark:text-zinc-400">Explore the catalog and save books to your reading list.</p>
                        </div>
                        <div class="pt-2">
                            <Link href="/catalog" class="inline-flex items-center justify-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl transition shadow-md shadow-sky-600/10">
                                Browse Catalog
                            </Link>
                        </div>
                    </div>

                    <!-- Pagination Footer -->
                    <div v-if="items?.links.length > 3" class="pt-4 border-t border-slate-100 dark:border-zinc-800">
                        <Pagination :links="items.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Wishlist Modal -->
        <Modal
            :show="showCreateModal"
            title="Create Custom Wishlist"
            maxWidth="md"
            @close="showCreateModal = false"
        >
            <form @submit.prevent="submitCreateWishlist" class="space-y-4 p-1">
                <Input
                    v-model="createForm.name"
                    label="Wishlist Name *"
                    placeholder="e.g. Summer Reading List"
                    :error="createForm.errors.name"
                    required
                />

                <Checkbox
                    v-model:checked="createForm.is_public"
                    label="Public Shareable Link"
                    description="Allow anyone with the link to view this wishlist."
                />

                <Checkbox
                    v-model:checked="createForm.is_default"
                    label="Set as Default Wishlist"
                />

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-zinc-800">
                    <Button variant="secondary" size="sm" type="button" @click="showCreateModal = false">Cancel</Button>
                    <Button variant="brand" size="sm" type="submit" :loading="createForm.processing">
                        Create Wishlist
                    </Button>
                </div>
            </form>
        </Modal>

        <!-- Share Link Modal -->
        <Modal
            :show="showShareModal"
            title="Share Public Wishlist"
            maxWidth="md"
            @close="showShareModal = false"
        >
            <div class="space-y-4 p-1">
                <p class="text-xs text-slate-600 dark:text-zinc-400">
                    Anyone with this link can view the public books in your <span class="font-bold text-slate-900 dark:text-white">{{ activeWishlist?.name }}</span> wishlist.
                </p>

                <div class="flex items-center gap-2">
                    <input
                        type="text"
                        readonly
                        :value="shareUrl"
                        class="flex-1 px-3 py-2.5 rounded-xl text-xs font-mono bg-slate-100 dark:bg-zinc-800 text-slate-800 dark:text-zinc-200 border border-slate-200 dark:border-zinc-700 outline-none"
                    />
                    <Button variant="brand" size="sm" class="font-bold shrink-0" @click="copyShareLink">
                        {{ copiedLink ? '✓ Copied!' : 'Copy Link' }}
                    </Button>
                </div>
            </div>
        </Modal>
    </MainLayout>
</template>
