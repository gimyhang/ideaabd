<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/UI/Modal.vue';

const props = defineProps<{
    show: boolean;
    reviewId: number;
    reviewTitle: string;
}>();

const emit = defineEmits<{
    close: [];
}>();

const form = useForm({
    reason: 'spam',
    details: '',
});

const reportReasons = [
    { value: 'spam', label: '🚫 Spam or Commercial Promotion', desc: 'Commercial ads, irrelevant links, or repetitive spam.' },
    { value: 'offensive', label: '🤬 Hate Speech or Offensive Language', desc: 'Profanity, harassment, or hate speech.' },
    { value: 'spoiler', label: '🤐 Unmarked Spoilers', desc: 'Contains major plot spoilers without warning.' },
    { value: 'fake', label: '🤖 Fake or Paid Review', desc: 'Inaccurate, automated, or manipulated content.' },
    { value: 'other', label: '❓ Other Issue', desc: 'Any other violation of community guidelines.' },
];

function submitReport() {
    form.post(`/reviews/${props.reviewId}/report`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
}
</script>

<template>
    <Modal :show="show" max-width="md" @close="emit('close')">
        <div class="p-6 space-y-5 font-sans">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Report Review</h3>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5 truncate max-w-xs">
                        "{{ reviewTitle }}"
                    </p>
                </div>
                <button
                    class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white transition"
                    @click="emit('close')"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form class="space-y-4" @submit.prevent="submitReport">
                <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                    Why are you reporting this review?
                </p>

                <!-- Radio option list -->
                <div class="space-y-2">
                    <label
                        v-for="r in reportReasons"
                        :key="r.value"
                        class="flex items-start gap-3 p-3 rounded-xl border transition-all cursor-pointer"
                        :class="form.reason === r.value
                            ? 'bg-rose-50/50 dark:bg-rose-950/20 border-rose-300 dark:border-rose-800/60 ring-1 ring-rose-500/30'
                            : 'bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 hover:border-slate-300'"
                    >
                        <input
                            v-model="form.reason"
                            type="radio"
                            :value="r.value"
                            class="mt-0.5 text-rose-600 focus:ring-rose-500 dark:bg-zinc-800"
                        />
                        <div>
                            <p class="text-xs font-bold text-slate-900 dark:text-white">{{ r.label }}</p>
                            <p class="text-[11px] text-slate-500 dark:text-zinc-400 leading-tight mt-0.5">{{ r.desc }}</p>
                        </div>
                    </label>
                </div>

                <!-- Additional Details -->
                <div class="space-y-1 pt-1">
                    <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                        Additional Details <span class="font-normal text-slate-400">(optional)</span>
                    </label>
                    <textarea
                        v-model="form.details"
                        rows="3"
                        maxlength="500"
                        placeholder="Provide more context if necessary..."
                        class="w-full text-xs rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-900 p-3 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500/30 transition resize-none"
                    />
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition"
                        @click="emit('close')"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2 bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl transition shadow-xs"
                    >
                        {{ form.processing ? 'Submitting…' : 'Submit Report' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
