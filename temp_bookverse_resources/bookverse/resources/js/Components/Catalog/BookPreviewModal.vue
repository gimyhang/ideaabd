<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Button from '@/Components/UI/Button.vue';
import { useCartDrawer } from '@/Composables/useCartDrawer';

export interface BookPreviewData {
    id: number;
    title: string;
    slug: string;
    format: string;
    price: number;
    discount_price?: number;
    price_after_discount: number;
    stock_quantity: number;
    cover_url: string;
    preview_type: 'none' | 'images' | 'pdf';
    max_preview_pages: number;
    sample_pages_urls: string[];
    authors?: Array<{ id: number; name: string }>;
}

const props = defineProps<{
    show: boolean;
    book: BookPreviewData;
    samplePdfSignedUrl?: string | null;
}>();

const emit = defineEmits(['close']);

const { openCartDrawer } = useCartDrawer();
const isAddingToCart = ref(false);

const pagesList = computed(() => {
    if (props.book.sample_pages_urls && props.book.sample_pages_urls.length > 0) {
        return props.book.sample_pages_urls;
    }
    return [props.book.cover_url];
});

const primaryAuthorName = computed(() => {
    return props.book.authors?.[0]?.name || '';
});

const savingsAmount = computed(() => {
    if (props.book.discount_price && props.book.discount_price < props.book.price) {
        return (props.book.price - props.book.price_after_discount).toFixed(0);
    }
    return null;
});

watch(() => props.show, (newVal) => {
    if (newVal) {
        syncUrlHash(true);
    } else {
        syncUrlHash(false);
    }
});

function syncUrlHash(open: boolean) {
    if (typeof window === 'undefined') return;
    if (open) {
        if (window.location.hash !== '#preview') {
            history.pushState(null, '', '#preview');
        }
    } else {
        if (window.location.hash === '#preview') {
            history.pushState(null, '', window.location.pathname + window.location.search);
        }
    }
}

function handleKeyDown(e: KeyboardEvent) {
    if (!props.show) return;
    if (e.key === 'Escape') {
        closeModal();
    }
}

function handlePopState() {
    if (props.show && window.location.hash !== '#preview') {
        closeModal();
    }
}

function closeModal() {
    emit('close');
}

function handleAddToCart() {
    if (props.book.stock_quantity <= 0 || isAddingToCart.value) return;
    isAddingToCart.value = true;

    router.post(route('cart.store'), {
        book_id: props.book.id,
        quantity: 1,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isAddingToCart.value = false;
            closeModal();
            openCartDrawer();
        },
        onError: () => {
            isAddingToCart.value = false;
        },
    });
}

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
    window.addEventListener('popstate', handlePopState);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
    window.removeEventListener('popstate', handlePopState);
});
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/65 backdrop-blur-sm p-4 font-sans select-none"
                role="dialog"
                aria-modal="true"
                aria-label="Rokomari Style Look Inside Preview"
            >
                <div class="relative w-full max-w-5xl h-[88vh] flex flex-col md:flex-row items-start justify-center gap-6">

                    <!-- LEFT: MAIN BOOK PAGES SCROLLING CONTAINER (Rokomari Reader Stage) -->
                    <div class="w-full md:flex-1 h-full bg-slate-950 rounded-xl shadow-2xl overflow-y-auto p-4 sm:p-6 space-y-6 border border-slate-800 relative">

                        <!-- Sticky Reader Header -->
                        <div class="sticky top-0 z-20 flex items-center justify-between bg-slate-900/90 backdrop-blur-md px-4 py-3 rounded-t-xl border-b border-slate-800/80 -mx-4 sm:-mx-6 -mt-4 sm:-mt-6 mb-6">
                            <span class="text-xs font-bold text-slate-200 tracking-wide uppercase flex items-center gap-1.5">
                                📖 <span class="hidden sm:inline">Look Inside Preview</span><span class="sm:hidden">Preview</span>
                            </span>
                            <span class="text-xs font-semibold text-slate-400 font-mono bg-slate-950/60 px-2.5 py-0.5 rounded-full border border-slate-800">
                                {{ pagesList.length }} Page{{ pagesList.length !== 1 ? 's' : '' }} Available
                            </span>
                        </div>

                        <!-- Watermark Overlay -->
                        <div class="fixed inset-0 pointer-events-none z-10 flex items-center justify-center opacity-[0.02] select-none">
                            <span class="text-7xl font-black tracking-widest text-white rotate-[-30deg] uppercase">BookVerse Preview</span>
                        </div>

                        <!-- 1. SAMPLE PDF STREAMING MODE -->
                        <template v-if="book.preview_type === 'pdf' && samplePdfSignedUrl">
                            <div class="w-full h-full min-h-[75vh] overflow-hidden rounded-lg bg-slate-950 relative">
                                <object
                                    :data="`${samplePdfSignedUrl}#toolbar=0&navpanes=0&view=FitH`"
                                    type="application/pdf"
                                    class="w-full h-full border-0 bg-slate-950"
                                    style="margin-top: -38px; height: calc(100% + 38px);"
                                >
                                    <embed :src="`${samplePdfSignedUrl}#toolbar=0&navpanes=0&view=FitH`" type="application/pdf" class="w-full h-full" />
                                </object>
                            </div>
                        </template>

                        <!-- 2. STACKED IMAGE PAGES MODE (Rokomari Image Reader) -->
                        <template v-else>
                            <div
                                v-for="(imgUrl, idx) in pagesList"
                                :key="idx"
                                class="flex flex-col items-center justify-center pb-8 border-b border-slate-800/80 last:border-b-0 last:pb-0"
                            >
                                <div class="relative bg-white p-2 rounded shadow-2xl border border-slate-200 max-w-[95%] sm:max-w-[85%] flex items-center justify-center">
                                    <img
                                        :src="imgUrl"
                                        :alt="`${book.title} - Page ${idx + 1}`"
                                        class="max-w-full h-auto object-contain rounded select-none pointer-events-none"
                                    />
                                    <div class="absolute bottom-4 right-4 bg-black/60 text-white font-mono text-[10px] font-bold px-2.5 py-1 rounded-md backdrop-blur-xs shadow-md">
                                        Page {{ idx + 1 }} / {{ pagesList.length }}
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- RIGHT: FLOATING WHITE SIDEBAR CARD (Rokomari Style) -->
                    <div class="w-full md:w-80 flex flex-col gap-3 shrink-0">

                        <!-- Close Button Above Card -->
                        <button
                            @click="closeModal"
                            class="flex items-center gap-1 text-white font-bold text-sm hover:text-sky-300 transition self-start cursor-pointer px-1 py-0.5"
                        >
                            ✕ Close
                        </button>

                        <!-- White Card -->
                        <div class="bg-white rounded-2xl p-5 shadow-2xl space-y-4 border border-slate-100">

                            <!-- Top Row: Cover Thumbnail + Title & Author -->
                            <div class="flex items-start gap-3">
                                <div class="w-14 h-20 rounded-lg overflow-hidden shadow border border-slate-200 shrink-0">
                                    <img :src="book.cover_url" :alt="book.title" class="w-full h-full object-cover" />
                                </div>
                                <div class="space-y-1 pt-1">
                                    <h3 class="text-sm font-bold font-heading text-slate-900 line-clamp-2 leading-tight">
                                        {{ book.title }}
                                    </h3>
                                    <p v-if="primaryAuthorName" class="text-xs text-slate-500 line-clamp-1">
                                        {{ primaryAuthorName }}
                                    </p>
                                </div>
                            </div>

                            <!-- Price Row -->
                            <div class="space-y-1 pt-2 border-t border-slate-100">
                                <div class="text-xs font-bold text-slate-700">
                                    <span v-if="book.discount_price" class="line-through text-slate-400 font-mono mr-1">TK. {{ book.price }}</span>
                                    <span class="font-mono text-slate-900">Total: TK. {{ book.price_after_discount }}</span>
                                </div>
                                <p v-if="savingsAmount" class="text-[11px] font-bold text-emerald-600">
                                    You Saved TK. {{ savingsAmount }}
                                </p>
                            </div>

                            <!-- CTA Button -->
                            <div class="pt-2">
                                <Button
                                    variant="brand"
                                    size="lg"
                                    class="w-full justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md py-3 rounded-xl"
                                    :disabled="book.stock_quantity <= 0 || isAddingToCart"
                                    @click="handleAddToCart"
                                >
                                    🛒 {{ isAddingToCart ? 'Adding...' : 'Add to Cart' }}
                                </Button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
