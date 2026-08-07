<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/UI/Button.vue';

interface OrderItem {
    id: number;
    book_title: string;
    quantity: number;
    unit_price: number;
    subtotal: number;
}

interface OrderDetails {
    id: number;
    order_number: string;
    total_amount: number;
    created_at: string;
    items: OrderItem[];
}

const props = defineProps<{
    order: OrderDetails;
}>();

// Item selection state for Partial Return
const selectedItemIds = ref<number[]>(props.order.items.map((i) => i.id));
const itemQuantities = ref<Record<number, number>>(
    Object.fromEntries(props.order.items.map((i) => [i.id, i.quantity]))
);

const form = useForm({
    return_type: 'refund',
    reason: '',
    items: [] as { order_item_id: number; quantity: number }[],
    evidence: [] as File[],
});

const calculatedTotalRefund = computed(() => {
    return props.order.items
        .filter((item) => selectedItemIds.value.includes(item.id))
        .reduce((sum, item) => {
            const qty = itemQuantities.value[item.id] || 1;
            return sum + item.unit_price * qty;
        }, 0);
});

const evidenceFiles = ref<File[]>([]);
const previewUrls = ref<string[]>([]);

function toggleItemSelection(itemId: number) {
    const idx = selectedItemIds.value.indexOf(itemId);
    if (idx > -1) {
        selectedItemIds.value.splice(idx, 1);
    } else {
        selectedItemIds.value.push(itemId);
    }
}

function handleFileChange(event: Event) {
    const input = event.target as HTMLInputElement;
    if (!input.files) return;

    const files = Array.from(input.files);
    for (const file of files) {
        if (evidenceFiles.value.length < 5) {
            evidenceFiles.value.push(file);
            previewUrls.value.push(URL.createObjectURL(file));
        }
    }
    form.evidence = evidenceFiles.value;
    input.value = '';
}

function removePhoto(index: number) {
    evidenceFiles.value.splice(index, 1);
    previewUrls.value.splice(index, 1);
    form.evidence = evidenceFiles.value;
}

function submit() {
    form.items = selectedItemIds.value.map((id) => ({
        order_item_id: id,
        quantity: itemQuantities.value[id] || 1,
    }));

    form.post(route('customer.orders.return.store', props.order.order_number), {
        preserveScroll: true,
    });
}
</script>

<template>
    <MainLayout>
        <Head :title="`Request Return & Refund — ${order.order_number}`" />

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6 font-sans">
            <!-- Header Navigation Banner -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <Link :href="`/orders/${order.order_number}`" class="p-2 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition text-xs font-bold">
                            ← Back to Order
                        </Link>
                        <h1 class="text-xl font-bold font-heading text-slate-900">
                            Request Return & Refund
                        </h1>
                    </div>
                    <p class="text-xs text-slate-500">Order {{ order.order_number }} • Placed on {{ order.created_at }}</p>
                </div>
            </div>

            <!-- Return Form Card -->
            <form @submit.prevent="submit" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-6">
                <!-- Return Type Selector -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Select Return Type <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label
                            class="p-3 rounded-xl border flex items-center gap-2 cursor-pointer transition"
                            :class="form.return_type === 'refund' ? 'border-sky-500 bg-sky-50/50 font-bold text-sky-900' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
                        >
                            <input type="radio" v-model="form.return_type" value="refund" class="text-sky-600" />
                            <span class="text-xs">💰 Money Refund</span>
                        </label>
                        <label
                            class="p-3 rounded-xl border flex items-center gap-2 cursor-pointer transition"
                            :class="form.return_type === 'replacement' ? 'border-sky-500 bg-sky-50/50 font-bold text-sky-900' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
                        >
                            <input type="radio" v-model="form.return_type" value="replacement" class="text-sky-600" />
                            <span class="text-xs">🔄 Replacement</span>
                        </label>
                        <label
                            class="p-3 rounded-xl border flex items-center gap-2 cursor-pointer transition"
                            :class="form.return_type === 'exchange' ? 'border-sky-500 bg-sky-50/50 font-bold text-sky-900' : 'border-slate-200 text-slate-700 hover:bg-slate-50'"
                        >
                            <input type="radio" v-model="form.return_type" value="exchange" class="text-sky-600" />
                            <span class="text-xs">📚 Book Exchange</span>
                        </label>
                    </div>
                </div>

                <!-- Partial Return Item Selection -->
                <div class="space-y-2 border-t border-slate-100 pt-4">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Select Items to Return <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[11px] text-slate-400">Uncheck items you want to keep</span>
                    </div>

                    <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-hidden text-xs">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="p-3.5 flex items-center justify-between gap-4"
                            :class="selectedItemIds.includes(item.id) ? 'bg-white' : 'bg-slate-50 opacity-60'"
                        >
                            <div class="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    :checked="selectedItemIds.includes(item.id)"
                                    @change="toggleItemSelection(item.id)"
                                    class="rounded text-sky-600 focus:ring-sky-500 border-slate-300 cursor-pointer"
                                />
                                <div>
                                    <h4 class="font-bold text-slate-900">{{ item.book_title }}</h4>
                                    <p class="text-[11px] text-slate-400">Unit Price: ৳{{ item.unit_price.toFixed(2) }}</p>
                                </div>
                            </div>

                            <div v-if="selectedItemIds.includes(item.id)" class="flex items-center gap-2">
                                <span class="text-[11px] text-slate-500">Qty:</span>
                                <select
                                    v-model="itemQuantities[item.id]"
                                    class="px-2 py-1 rounded-lg border border-slate-200 text-xs font-bold bg-white"
                                >
                                    <option v-for="q in item.quantity" :key="q" :value="q">{{ q }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <p v-if="form.errors.items" class="text-xs font-bold text-rose-500">{{ form.errors.items }}</p>
                </div>

                <!-- Reason Input -->
                <div class="space-y-2 border-t border-slate-100 pt-4">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Reason for Return & Refund <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        v-model="form.reason"
                        rows="4"
                        placeholder="Please describe why you are requesting a return/refund in detail (damaged product, wrong book, quality issue)..."
                        class="w-full px-4 py-3 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none transition"
                        required
                    ></textarea>
                    <p v-if="form.errors.reason" class="text-xs font-bold text-rose-500">{{ form.errors.reason }}</p>
                </div>

                <!-- Evidence Uploads -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Attach Evidence Photos / Documents (Optional, max 5 files)
                    </label>
                    <input
                        type="file"
                        multiple
                        accept="image/*,.pdf"
                        @change="handleFileChange"
                        class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer"
                    />
                    <p v-if="form.errors.evidence" class="text-xs font-bold text-rose-500">{{ form.errors.evidence }}</p>

                    <!-- Preview Thumbnail Grid with Remove Button -->
                    <div v-if="previewUrls.length > 0" class="flex flex-wrap gap-3 pt-2">
                        <div
                            v-for="(url, idx) in previewUrls"
                            :key="idx"
                            class="relative group w-16 h-16 rounded-xl border border-slate-200 overflow-hidden"
                        >
                            <img :src="url" class="w-full h-full object-cover" />
                            <button
                                type="button"
                                @click="removePhoto(idx)"
                                class="absolute top-1 right-1 w-5 h-5 rounded-full bg-rose-600 text-white font-bold text-[10px] flex items-center justify-center shadow-xs cursor-pointer hover:bg-rose-700 transition"
                                title="Remove photo"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Calculated Refund Box -->
                <div class="p-4 rounded-xl bg-sky-50 border border-sky-100 flex items-center justify-between text-xs">
                    <span class="font-bold text-sky-900">Calculated Refund Amount:</span>
                    <span class="text-base font-bold font-mono text-sky-700">৳{{ calculatedTotalRefund.toFixed(2) }}</span>
                </div>

                <!-- Submit CTAs -->
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                    <Link :href="`/orders/${order.order_number}`">
                        <Button type="button" variant="outline" size="sm" class="rounded-xl cursor-pointer">
                            Cancel
                        </Button>
                    </Link>
                    <Button type="submit" variant="brand" size="sm" :loading="form.processing" :disabled="selectedItemIds.length === 0" class="rounded-xl font-bold cursor-pointer">
                        Submit Return Request
                    </Button>
                </div>
            </form>
        </div>
    </MainLayout>
</template>
