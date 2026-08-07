<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import StarRatingInput from '@/Components/UI/StarRatingInput.vue';

interface ExistingReview {
    id: number;
    rating: number;
    title: string;
    body: string;
    photo_url: string | null;
}

const props = defineProps<{
    bookId: number;
    bookSlug: string;
    existing?: ExistingReview | null;
}>();

const emit = defineEmits<{
    cancel: [];
}>();

const isEdit = !! props.existing;

const form = useForm({
    rating:       props.existing?.rating ?? 0,
    title:        props.existing?.title ?? '',
    body:         props.existing?.body ?? '',
    photo:        null as File | null,
    remove_photo: false,
});

const photoPreview = ref<string | null>(props.existing?.photo_url ?? null);
const currentPhoto = ref<string | null>(props.existing?.photo_url ?? null);

function handlePhotoChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (! file) return;
    form.photo = file;
    photoPreview.value = URL.createObjectURL(file);
    form.remove_photo = false;
}

function removePhoto() {
    form.photo = null;
    photoPreview.value = null;
    if (currentPhoto.value) {
        form.remove_photo = true;
    }
}

function submit() {
    if (isEdit && props.existing) {
        form.put(`/reviews/${props.existing.id}`, {
            preserveScroll: true,
            onSuccess: () => emit('cancel'),
        });
    } else {
        form.post(`/books/${props.bookSlug}/reviews`, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                photoPreview.value = null;
            },
        });
    }
}
</script>

<template>
    <form
        class="bg-white dark:bg-zinc-950 rounded-2xl p-6 border border-slate-200 dark:border-zinc-800 space-y-5"
        @submit.prevent="submit"
    >
        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
            {{ isEdit ? 'Edit Your Review' : 'Write a Review' }}
        </h3>

        <!-- Star Rating Input -->
        <div class="space-y-1">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400">Your Rating *</label>
            <StarRatingInput v-model="form.rating" />
            <p v-if="form.errors.rating" class="text-xs text-red-500">{{ form.errors.rating }}</p>
        </div>

        <!-- Title -->
        <div class="space-y-1">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400">Review Title *</label>
            <input
                v-model="form.title"
                type="text"
                maxlength="120"
                placeholder="Summarize your experience in one line"
                class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 transition"
            />
            <p v-if="form.errors.title" class="text-xs text-red-500">{{ form.errors.title }}</p>
        </div>

        <!-- Body -->
        <div class="space-y-1">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400">
                Your Review * <span class="font-normal text-slate-400">(min 20 characters)</span>
            </label>
            <textarea
                v-model="form.body"
                rows="5"
                placeholder="Share your thoughts about this book — what you liked, what you didn't, who you'd recommend it to..."
                class="w-full rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 transition resize-none"
            />
            <div class="flex justify-between">
                <p v-if="form.errors.body" class="text-xs text-red-500">{{ form.errors.body }}</p>
                <p class="text-xs text-slate-400 ml-auto">{{ form.body.length }}/2000</p>
            </div>
        </div>

        <!-- Photo Upload -->
        <div class="space-y-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-400">
                Photo <span class="font-normal text-slate-400">(optional, max 2MB)</span>
            </label>

            <div v-if="photoPreview" class="relative inline-block">
                <img
                    :src="photoPreview"
                    class="w-24 h-24 object-cover rounded-xl border border-slate-200 dark:border-zinc-700"
                />
                <button
                    type="button"
                    class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600 transition"
                    @click="removePhoto"
                >×</button>
            </div>

            <label
                v-else
                class="flex items-center gap-2 w-fit cursor-pointer bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-600 dark:text-slate-300 text-xs font-medium px-4 py-2 rounded-xl transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Upload Photo
                <input type="file" accept="image/jpg,image/jpeg,image/png,image/webp" class="hidden" @change="handlePhotoChange" />
            </label>
            <p v-if="form.errors.photo" class="text-xs text-red-500">{{ form.errors.photo }}</p>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3 pt-2">
            <button
                type="submit"
                :disabled="form.processing || form.rating === 0"
                class="px-6 py-2.5 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition"
            >
                {{ form.processing ? 'Submitting…' : (isEdit ? 'Save Changes' : 'Publish Review') }}
            </button>
            <button
                v-if="isEdit"
                type="button"
                class="px-5 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition"
                @click="emit('cancel')"
            >
                Cancel
            </button>
        </div>
    </form>
</template>
