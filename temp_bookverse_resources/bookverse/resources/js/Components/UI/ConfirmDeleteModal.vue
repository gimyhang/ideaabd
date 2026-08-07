<script setup lang="ts">
import Modal from '@/Components/UI/Modal.vue';
import Button from '@/Components/UI/Button.vue';
import { useAdminTheme } from '@/Composables/useAdminTheme';

const { isDark } = useAdminTheme();

const props = withDefaults(
    defineProps<{
        show: boolean;
        title?: string;
        itemName?: string;
        message?: string;
        loading?: boolean;
    }>(),
    {
        title: 'Delete Confirmation',
        itemName: '',
        message: 'Are you sure you want to delete this item? This action cannot be undone and will permanently remove the record.',
        loading: false,
    }
);

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'confirm'): void;
}>();
</script>

<template>
    <Modal :show="show" maxWidth="md" @close="emit('close')">
        <div class="p-6 text-center space-y-5 font-sans">

            <!-- Trash Warning Icon Badge -->
            <div class="mx-auto w-14 h-14 rounded-2xl bg-rose-500/15 text-rose-500 flex items-center justify-center border border-rose-500/20 shadow-lg shadow-rose-500/10">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>

            <!-- Title & Message -->
            <div class="space-y-2">
                <h3 class="text-lg font-bold font-heading" :class="isDark ? 'text-white' : 'text-slate-900'">
                    {{ title }}
                </h3>

                <!-- Target Item Badge Highlight -->
                <div v-if="itemName" class="py-1">
                    <span
                        class="px-3 py-1 rounded-lg text-xs font-mono font-bold border"
                        :class="isDark ? 'bg-rose-500/20 text-rose-300 border-rose-500/30' : 'bg-rose-50 text-rose-700 border-rose-200'"
                    >
                        {{ itemName }}
                    </span>
                </div>

                <p class="text-xs leading-relaxed max-w-sm mx-auto" :class="isDark ? 'text-slate-400' : 'text-slate-600'">
                    {{ message }}
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-center gap-3 pt-2">
                <Button
                    variant="secondary"
                    size="md"
                    type="button"
                    :disabled="loading"
                    @click="emit('close')"
                    class="px-5 text-xs font-semibold"
                >
                    Cancel
                </Button>
                <Button
                    variant="destructive"
                    size="md"
                    type="button"
                    :loading="loading"
                    @click="emit('confirm')"
                    class="px-5 text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white shadow-md shadow-rose-600/20"
                >
                    Yes, Delete
                </Button>
            </div>

        </div>
    </Modal>
</template>
