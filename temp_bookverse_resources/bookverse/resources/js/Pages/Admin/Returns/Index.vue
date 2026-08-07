<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermission } from '@/Composables/usePermission';

const { can } = usePermission();
import Badge from '@/Components/UI/Badge.vue';
import Pagination, { PaginationLink } from '@/Components/UI/Pagination.vue';

interface ReturnItem {
    id: number;
    order_number: string;
    customer_name: string;
    return_type: string;
    return_type_label: string;
    reason: string;
    refund_amount: number;
    status: string;
    status_label: string;
    refund_status: string;
    refund_status_label: string;
    created_at: string;
}

interface PaginationMeta {
    data: ReturnItem[];
    links: PaginationLink[];
}

const props = defineProps<{
    returns: PaginationMeta;
    filters: {
        status: string;
    };
}>();

const selectedStatus = ref(props.filters.status || '');

function filterStatus() {
    router.get(
        route('admin.returns.index'),
        { status: selectedStatus.value || undefined },
        { preserveState: true, replace: true }
    );
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
        <Head title="Return & Refund Requests — Admin Portal" />

        <template #header>Return & Refund Management</template>

        <div class="p-6 space-y-6 font-sans">
            <!-- Header Filter Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-zinc-950 rounded-3xl p-6 border border-slate-200 dark:border-zinc-800 shadow-xs">
                <div class="space-y-1">
                    <h1 class="text-xl font-bold font-heading text-slate-900 dark:text-white">
                        Customer Return & Refund Requests
                    </h1>
                    <p class="text-xs text-slate-400">Review partial returns, evidence photos, and resolve refunds or inventory restocks.</p>
                </div>

                <div class="flex items-center gap-3">
                    <select
                        v-model="selectedStatus"
                        @change="filterStatus"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-50 dark:bg-zinc-900 text-slate-900 dark:text-white border border-slate-200 dark:border-zinc-800 outline-none"
                    >
                        <option value="">All Statuses</option>
                        <option value="pending">Pending Review</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>

            <!-- Returns Table Card -->
            <div class="bg-white dark:bg-zinc-950 rounded-3xl border border-slate-200 dark:border-zinc-800 overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-zinc-900/50 border-b border-slate-200 dark:border-zinc-800 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="p-4">Req #</th>
                                <th class="p-4">Order Number</th>
                                <th class="p-4">Type</th>
                                <th class="p-4">Customer</th>
                                <th class="p-4">Refund Amount</th>
                                <th class="p-4">Request Status</th>
                                <th class="p-4">Refund Status</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800/60">
                            <tr v-for="item in returns.data" :key="item.id" class="hover:bg-slate-50/50 dark:hover:bg-zinc-900/30 transition">
                                <td class="p-4 font-mono font-bold text-slate-900 dark:text-white">#{{ item.id }}</td>
                                <td class="p-4 font-mono font-bold text-sky-600 dark:text-sky-400">{{ item.order_number }}</td>
                                <td class="p-4 font-bold text-slate-700 dark:text-slate-300 capitalize">{{ item.return_type }}</td>
                                <td class="p-4 font-medium text-slate-900 dark:text-white">{{ item.customer_name }}</td>
                                <td class="p-4 font-bold font-mono text-slate-900 dark:text-white">৳{{ item.refund_amount.toFixed(2) }}</td>
                                <td class="p-4">
                                    <Badge :variant="getBadgeVariant(item.status)">
                                        {{ item.status_label }}
                                    </Badge>
                                </td>
                                <td class="p-4">
                                    <Badge :variant="getBadgeVariant(item.refund_status)">
                                        {{ item.refund_status_label }}
                                    </Badge>
                                </td>
                                <td class="p-4 text-right">
                                    <Link
                                        v-if="can('view-returns')"
                                        :href="`/admin/returns/${item.id}`"
                                        class="px-3 py-1.5 rounded-xl bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 hover:bg-sky-100 font-bold transition"
                                    >
                                        Review Request →
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="returns.data.length === 0">
                                <td colspan="8" class="p-8 text-center text-slate-400">
                                    No return requests found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="returns.links && returns.links.length > 3" class="p-4 border-t border-slate-100 dark:border-zinc-800 flex justify-center">
                    <Pagination :links="returns.links" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
