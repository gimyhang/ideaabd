<script setup lang="ts">
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import StarRating from '@/Components/UI/StarRating.vue';
import HelpfulVoteButton from '@/Components/Reviews/HelpfulVoteButton.vue';
import ReportReviewModal from '@/Components/Reviews/ReportReviewModal.vue';

interface Review {
    id: number;
    rating: number;
    title: string;
    body: string;
    photo_url: string | null;
    helpful_count: number;
    is_verified_purchase: boolean;
    created_at: string;
    user_name: string;
    is_own: boolean;
    is_voted?: boolean;
    is_reported_by_user?: boolean;
}

const props = defineProps<{
    review: Review;
}>();

const emit = defineEmits<{
    edit: [review: Review];
}>();

const page = usePage();
const expanded = ref(false);
const isLong = props.review.body.length > 280;
const lightboxOpen = ref(false);
const showReportModal = ref(false);

function deleteReview() {
    if (! confirm('Are you sure you want to remove your review?')) return;
    router.delete(`/reviews/${props.review.id}`, { preserveScroll: true });
}

function openReportModal() {
    if (! page.props.auth?.user) {
        router.get('/login');
        return;
    }
    showReportModal.value = true;
}
</script>

<template>
    <div class="bg-white dark:bg-zinc-950 rounded-2xl p-5 border border-slate-100 dark:border-zinc-800 space-y-3 transition-shadow hover:shadow-sm">
        <!-- Header -->
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
                <!-- Avatar -->
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-sky-400 to-indigo-500 flex items-center justify-center text-white font-bold text-sm shrink-0">
                    {{ review.user_name.charAt(0).toUpperCase() }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-900 dark:text-white leading-tight">
                        {{ review.user_name }}
                    </p>
                    <p class="text-[11px] text-slate-400">{{ review.created_at }}</p>
                </div>
            </div>

            <!-- Badges & Actions -->
            <div class="flex items-center gap-2 shrink-0">
                <!-- Verified Purchase -->
                <span
                    v-if="review.is_verified_purchase"
                    class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 px-2 py-0.5 rounded-full"
                >
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Verified Purchase
                </span>

                <!-- Own review actions -->
                <template v-if="review.is_own">
                    <button
                        class="text-xs text-sky-600 dark:text-sky-400 hover:underline font-medium"
                        @click="emit('edit', review)"
                    >Edit</button>
                    <button
                        class="text-xs text-red-500 dark:text-red-400 hover:underline font-medium"
                        @click="deleteReview"
                    >Delete</button>
                </template>
            </div>
        </div>

        <!-- Rating + Title -->
        <div class="flex items-center gap-2">
            <StarRating :rating="review.rating" size="sm" />
            <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ review.title }}</h4>
        </div>

        <!-- Body -->
        <div class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
            <span v-if="!isLong || expanded">{{ review.body }}</span>
            <template v-else>
                <span>{{ review.body.slice(0, 280) }}…</span>
                <button class="ml-1 text-sky-600 dark:text-sky-400 font-medium hover:underline" @click="expanded = true">
                    Read more
                </button>
            </template>
        </div>

        <!-- Photo -->
        <div v-if="review.photo_url">
            <img
                :src="review.photo_url"
                :alt="`Review photo by ${review.user_name}`"
                class="w-24 h-24 object-cover rounded-xl border border-slate-200 dark:border-zinc-700 cursor-pointer hover:opacity-90 transition"
                @click="lightboxOpen = true"
            />

            <!-- Lightbox -->
            <div
                v-if="lightboxOpen"
                class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4"
                @click.self="lightboxOpen = false"
            >
                <img :src="review.photo_url" class="max-w-full max-h-[90vh] rounded-xl object-contain" />
                <button
                    class="absolute top-4 right-4 text-white bg-black/40 rounded-full p-2 hover:bg-black/60 transition"
                    @click="lightboxOpen = false"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Footer Actions (Helpful Button + Report) -->
        <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-zinc-800">
            <!-- Helpful Vote Button -->
            <HelpfulVoteButton
                :review-id="review.id"
                :helpful-count="review.helpful_count"
                :is-voted="review.is_voted"
                :is-own="review.is_own"
            />

            <!-- Report Button -->
            <button
                v-if="!review.is_own"
                type="button"
                class="inline-flex items-center gap-1 text-[11px] font-medium transition"
                :class="review.is_reported_by_user
                    ? 'text-rose-500 cursor-default font-semibold'
                    : 'text-slate-400 hover:text-rose-500'"
                :disabled="review.is_reported_by_user"
                @click="openReportModal"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                </svg>
                <span>{{ review.is_reported_by_user ? 'Reported' : 'Report' }}</span>
            </button>
        </div>

        <!-- Report Modal -->
        <ReportReviewModal
            :show="showReportModal"
            :review-id="review.id"
            :review-title="review.title"
            @close="showReportModal = false"
        />
    </div>
</template>
