<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermission } from '@/Composables/usePermission';

const { can } = usePermission();
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';

interface RequestItem {
    id: number;
    book_title: string;
    quantity: number;
    unit_price: number;
    subtotal: number;
}

interface ReturnDetails {
    id: number;
    order_number: string;
    order_id: number;
    customer_name: string;
    customer_email: string;
    return_type: string;
    return_type_label: string;
    reason: string;
    evidence_urls: string[];
    status: string;
    status_label: string;
    refund_status: string;
    refund_status_label: string;
    refund_amount: number;
    restock_inventory: boolean;
    admin_note: string | null;
    created_at: string;
    resolved_at: string | null;
    items: RequestItem[];
}

const props = defineProps<{
    returnRequest: ReturnDetails;
}>();

const form = useForm({
    status: props.returnRequest.status,
    refund_status: props.returnRequest.refund_status,
    restock_inventory: props.returnRequest.restock_inventory,
    admin_note: props.returnRequest.admin_note || '',
});

function resolveRequest(status: 'approved' | 'rejected') {
    form.status = status;
    if (status === 'approved') {
        form.refund_status = 'completed';
    } else {
        form.refund_status = 'failed';
    }

    form.patch(`/admin/returns/${props.returnRequest.id}`, {
        preserveScroll: true,
    });
}

function getBadgeVariant(status: string) {
    switch (status) {
        case 'approved':
        case 'completed':
            return 'success';
        case 'rejected':
        case 'failed':
            return 'error';
        default:
            return 'warning';
    }
}
</script>

<template>
    <AdminLayout>
        <Head :title="`Return Request #${returnRequest.id} — Admin Portal`" />

        <template #header>Return Request #{{ returnRequest.id }}</template>

        <div class="p-6 space-y-6 font-sans">
            <!-- Navigation Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-zinc-950 rounded-3xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <Link href="/admin/returns" class="p-2 rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 transition text-xs font-bold">
                            ← Back to Returns
                        </Link>
                        <h1 class="text-xl font-bold font-heading text-slate-900 dark:text-white">
                            Return Request #{{ returnRequest.id }}
                        </h1>
                        <Badge :variant="getBadgeVariant(returnRequest.status)">
                            {{ returnRequest.status_label }}
                        </Badge>
                    </div>
                    <p class="text-xs text-slate-400">
                        Order {{ returnRequest.order_number }} • Customer: {{ returnRequest.customer_name }} ({{ returnRequest.customer_email }})
                    </p>
                </div>
            </div>

            <!-- Content Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left 2-Column: Request Details, Items & Evidence -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Requested Items for Return -->
                    <div class="bg-white dark:bg-zinc-950 rounded-3xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-4">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Itemized Return Breakdown
                        </h2>
                        <div class="divide-y divide-slate-100 dark:divide-zinc-800">
                            <div v-for="item in returnRequest.items" :key="item.id" class="py-3 flex items-center justify-between text-xs">
                                <div>
                                    <h4 class="font-bold text-slate-900 dark:text-white">{{ item.book_title }}</h4>
                                    <p class="text-[11px] text-slate-400">Qty: {{ item.quantity }} × ৳{{ item.unit_price.toFixed(2) }}</p>
                                </div>
                                <span class="font-bold font-mono text-slate-900 dark:text-white text-sm">৳{{ item.subtotal.toFixed(2) }}</span>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-zinc-900 flex justify-between items-center text-xs font-bold border border-slate-200 dark:border-zinc-800">
                            <span class="text-slate-600 dark:text-slate-400">Total Calculated Refund:</span>
                            <span class="text-base font-mono text-sky-600 dark:text-sky-400">৳{{ returnRequest.refund_amount.toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Customer Reason Card -->
                    <div class="bg-white dark:bg-zinc-950 rounded-3xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-3">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Customer Reason
                        </h2>
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-zinc-900 text-xs text-slate-700 dark:text-slate-300 leading-relaxed font-sans border border-slate-200 dark:border-zinc-800">
                            {{ returnRequest.reason }}
                        </div>
                    </div>

                    <!-- Photo Evidence Gallery -->
                    <div class="bg-white dark:bg-zinc-950 rounded-3xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-3">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Attached Photo Evidence
                            <span v-if="returnRequest.evidence_urls && returnRequest.evidence_urls.length > 0" class="text-sky-500">({{ returnRequest.evidence_urls.length }})</span>
                        </h2>
                        <div v-if="returnRequest.evidence_urls && returnRequest.evidence_urls.length > 0" class="flex flex-wrap gap-4">
                            <a
                                v-for="(url, idx) in returnRequest.evidence_urls"
                                :key="idx"
                                :href="url"
                                target="_blank"
                                class="group relative rounded-xl overflow-hidden border border-slate-200 dark:border-zinc-800"
                            >
                                <img
                                    :src="url"
                                    class="w-24 h-24 object-cover group-hover:scale-105 transition"
                                    @error="(e: Event) => { (e.target as HTMLImageElement).style.display = 'none' }"
                                />
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition flex items-end justify-center pb-1">
                                    <span class="text-[10px] text-white font-bold opacity-0 group-hover:opacity-100">View</span>
                                </div>
                            </a>
                        </div>
                        <p v-else class="text-xs text-slate-400 italic">No evidence photos attached.</p>
                    </div>
                </div>

                <!-- Right Column: Admin Resolution Form -->
                <div class="space-y-6">
                    <div v-if="can('approve-returns')" class="bg-white dark:bg-zinc-950 rounded-3xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs space-y-5">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                            Resolve Return Request
                        </h2>

                        <div class="space-y-4 text-xs">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Return Type</label>
                                <span class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-900 dark:text-white font-bold block">
                                    {{ returnRequest.return_type_label }}
                                </span>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Inventory Restock Option</label>
                                <label class="flex items-center gap-2 cursor-pointer p-3 rounded-xl border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-900">
                                    <input type="checkbox" v-model="form.restock_inventory" class="rounded text-sky-600 focus:ring-sky-500 border-slate-300" />
                                    <span class="font-bold text-slate-800 dark:text-slate-200">Restock item quantities back to inventory</span>
                                </label>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Admin Resolution Note</label>
                                <textarea
                                    v-model="form.admin_note"
                                    rows="3"
                                    placeholder="Add an internal note or explanation for the customer..."
                                    class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-slate-900 dark:text-white outline-none"
                                ></textarea>
                            </div>

                            <div v-if="returnRequest.status === 'pending'" class="flex gap-3 pt-2">
                                <Button
                                    type="button"
                                    variant="brand"
                                    size="sm"
                                    :loading="form.processing"
                                    @click="resolveRequest('approved')"
                                    class="flex-1 rounded-xl font-bold cursor-pointer"
                                >
                                    ✓ Approve & Refund
                                </Button>
                                <Button
                                    type="button"
                                    variant="destructive"
                                    size="sm"
                                    :loading="form.processing"
                                    @click="resolveRequest('rejected')"
                                    class="flex-1 rounded-xl font-bold cursor-pointer"
                                >
                                    ✕ Reject Request
                                </Button>
                            </div>

                            <div v-else class="p-3 rounded-xl bg-slate-100 dark:bg-zinc-900 text-slate-600 dark:text-slate-400 text-center font-bold">
                                Request has been resolved as {{ returnRequest.status_label }}.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
