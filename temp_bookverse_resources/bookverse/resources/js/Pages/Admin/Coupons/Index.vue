<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermission } from '@/Composables/usePermission';

const { can } = usePermission();
import Input from '@/Components/UI/Input.vue';
import ConfirmDeleteModal from '@/Components/UI/ConfirmDeleteModal.vue';
import { useAdminTheme } from '@/Composables/useAdminTheme';

const { isDark } = useAdminTheme();

interface CouponItem {
    id: number;
    code: string;
    name: string | null;
    type: string;
    type_label: string;
    value: number;
    min_order_amount: number;
    max_discount: number | null;
    max_uses: number | null;
    used_count: number;
    user_limit: number;
    computed_status: string;
    computed_status_label: string;
    is_active: boolean;
    starts_at: string | null;
    expires_at: string | null;
    total_discount_given: number;
    targets_count: number;
}

const props = defineProps<{
    coupons: {
        data: CouponItem[];
        links: any[];
    };
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters.search || '');
const showDeleteModal = ref(false);
const couponToDelete = ref<CouponItem | null>(null);

function handleSearch() {
    router.get('/admin/coupons', { search: search.value || undefined }, { preserveState: true, replace: true });
}

function promptDelete(coupon: CouponItem) {
    couponToDelete.value = coupon;
    showDeleteModal.value = true;
}

function confirmDelete() {
    if (couponToDelete.value) {
        router.delete(`/admin/coupons/${couponToDelete.value.id}`, {
            preserveScroll: true,
            onFinish: () => {
                showDeleteModal.value = false;
                couponToDelete.value = null;
            },
        });
    }
}

function statusBadgeClass(status: string) {
    if (status === 'active') return isDark.value ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-emerald-50 text-emerald-700 border-emerald-200';
    if (status === 'expired') return isDark.value ? 'bg-amber-500/20 text-amber-400 border-amber-500/30' : 'bg-amber-50 text-amber-700 border-amber-200';
    return isDark.value ? 'bg-slate-700 text-slate-300 border-slate-600' : 'bg-slate-100 text-slate-600 border-slate-200';
}
</script>

<template>
    <Head title="Coupon Management — BookVerse Admin" />

    <AdminLayout>
        <template #header>Coupon Management</template>

        <div class="space-y-6 font-sans">
            <!-- Header Banner Card (Matching Reference Screenshot) -->
            <div class="p-6 sm:p-8 rounded-3xl border transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                 :class="isDark ? 'bg-zinc-900 border-zinc-800' : 'bg-white border-slate-200/80 shadow-xs'">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold font-heading tracking-tight" :class="isDark ? 'text-white' : 'text-slate-900'">
                        🎟️ Coupons Directory
                    </h1>
                    <p class="text-xs sm:text-sm font-medium mt-1.5" :class="isDark ? 'text-slate-400' : 'text-slate-500'">
                        Manage promo codes, discount limits, free shipping offers & catalog targets.
                    </p>
                </div>
                <Link
                    v-if="can('create-coupons')"
                    href="/admin/coupons/create"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-full text-xs sm:text-sm font-bold transition-all shadow-sm shrink-0 gap-2"
                    :class="isDark ? 'bg-zinc-800 hover:bg-zinc-700 text-white' : 'bg-slate-900 hover:bg-slate-800 text-white'"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Create New Coupon
                </Link>
            </div>

            <!-- Table Container Panel (Matching Authors/Index.vue Screenshot) -->
            <div class="border rounded-3xl overflow-hidden shadow-xs transition-colors p-6 space-y-5" :class="isDark ? 'bg-zinc-900 border-zinc-800' : 'bg-white border-slate-200'">
                <!-- Table Header Bar with Search & Total Count -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b pb-4" :class="isDark ? 'border-zinc-800' : 'border-slate-100'">
                    <div class="w-full sm:w-80">
                        <Input
                            v-model="search"
                            placeholder="Search coupons by code or title..."
                            @input="handleSearch"
                        />
                    </div>

                    <span class="text-xs font-semibold" :class="isDark ? 'text-slate-400' : 'text-slate-500'">
                        Total Coupons: {{ coupons.data.length }}
                    </span>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b uppercase tracking-wider font-semibold text-[10px]" :class="isDark ? 'border-zinc-800 text-slate-400 bg-zinc-950/60' : 'border-slate-200 text-slate-500 bg-slate-50'">
                                <th class="py-3.5 px-4 font-bold">Code & Campaign</th>
                                <th class="py-3.5 px-4 font-bold">Type & Value</th>
                                <th class="py-3.5 px-4 font-bold">Scope Target</th>
                                <th class="py-3.5 px-4 font-bold">Usage</th>
                                <th class="py-3.5 px-4 font-bold">Status</th>
                                <th class="py-3.5 px-4 font-bold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" :class="isDark ? 'divide-zinc-800' : 'divide-slate-100'">
                            <tr v-for="coupon in coupons.data" :key="coupon.id" class="transition-colors" :class="isDark ? 'hover:bg-zinc-800/40' : 'hover:bg-slate-50/80'">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold font-mono text-sm text-sky-600 dark:text-sky-400 uppercase tracking-wider">
                                        {{ coupon.code }}
                                    </div>
                                    <div v-if="coupon.name" class="text-[11px] font-medium" :class="isDark ? 'text-slate-400' : 'text-slate-500'">
                                        {{ coupon.name }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 space-y-0.5">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-400 border border-sky-200/80 dark:border-sky-800/80">
                                        {{ coupon.type_label }}
                                    </span>
                                    <div class="font-bold text-slate-800 dark:text-slate-200 font-mono text-xs mt-0.5">
                                        <span v-if="coupon.type === 'flat'">৳{{ coupon.value }} OFF</span>
                                        <span v-else-if="coupon.type === 'percent'">{{ coupon.value }}% OFF</span>
                                        <span v-else class="text-emerald-600 dark:text-emerald-400 font-bold">FREE SHIPPING</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="text-xs font-semibold" :class="isDark ? 'text-slate-300' : 'text-slate-700'">
                                        {{ coupon.targets_count > 0 ? `${coupon.targets_count} Targets Scoped` : 'Global Entire Store' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 space-y-1">
                                    <div class="font-mono font-bold" :class="isDark ? 'text-slate-300' : 'text-slate-700'">
                                        {{ coupon.used_count }} / {{ coupon.max_uses ?? '∞' }}
                                    </div>
                                    <div class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                                        Saved: ৳{{ coupon.total_discount_given }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-full border text-[10px] font-bold uppercase tracking-wider" :class="statusBadgeClass(coupon.computed_status)">
                                        {{ coupon.computed_status_label }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <!-- Edit Coupon (Amber Icon) -->
                                        <Link
                                            v-if="can('edit-coupons')"
                                            :href="`/admin/coupons/${coupon.id}/edit`"
                                            class="p-2 rounded-xl border transition-all duration-200 shadow-xs bg-amber-50 hover:bg-amber-500 text-amber-600 hover:text-white border-amber-200/80 dark:bg-amber-500/10 dark:hover:bg-amber-500 dark:text-amber-400 dark:hover:text-white dark:border-amber-500/20"
                                            title="Edit Coupon"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>

                                        <!-- Delete Coupon (Rose Icon) -->
                                        <button
                                            v-if="can('delete-coupons')"
                                            @click="promptDelete(coupon)"
                                            class="p-2 rounded-xl border transition-all duration-200 shadow-xs bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border-rose-200/80 dark:bg-rose-500/10 dark:hover:bg-rose-600 dark:text-rose-400 dark:hover:text-white dark:border-rose-500/20 cursor-pointer"
                                            title="Delete Coupon"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="coupons.data.length === 0">
                                <td colspan="6" class="p-8 text-center text-xs" :class="isDark ? 'text-slate-500' : 'text-slate-400'">
                                    No coupons found matching your search criteria. Click "Create New Coupon" to get started.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Custom Delete Confirmation Modal (Existing Official UI Component) -->
        <ConfirmDeleteModal
            :show="showDeleteModal"
            title="Delete Coupon"
            :item-name="couponToDelete?.code"
            message="Are you sure you want to delete this coupon? This action cannot be undone and will permanently remove the promo record."
            @confirm="confirmDelete"
            @close="showDeleteModal = false"
        />
    </AdminLayout>
</template>
