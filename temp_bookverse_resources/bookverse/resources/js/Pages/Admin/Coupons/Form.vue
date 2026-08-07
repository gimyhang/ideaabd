<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAdminTheme } from '@/Composables/useAdminTheme';

const { isDark } = useAdminTheme();

const props = defineProps<{
    coupon?: any;
    books: { id: number; title: string }[];
    categories: { id: number; name: string }[];
    publishers: { id: number; name: string }[];
}>();

const isEditing = Boolean(props.coupon);

const form = useForm({
    code: props.coupon?.code || '',
    name: props.coupon?.name || '',
    type: props.coupon?.type || 'flat',
    value: props.coupon?.value ?? 0,
    min_order_amount: props.coupon?.min_order_amount ?? 0,
    max_discount: props.coupon?.max_discount ?? null,
    max_uses: props.coupon?.max_uses ?? null,
    user_limit: props.coupon?.user_limit ?? 1,
    first_order_only: props.coupon?.first_order_only ?? false,
    min_items: props.coupon?.min_items ?? 0,
    starts_at: props.coupon?.starts_at ? props.coupon.starts_at.slice(0, 16) : '',
    expires_at: props.coupon?.expires_at ? props.coupon.expires_at.slice(0, 16) : '',
    is_active: props.coupon?.is_active ?? true,
    target_type: props.coupon?.target_type || '',
    target_ids: (props.coupon?.target_ids || []).map(Number),
});

const targetSearch = ref('');

// Computed Target Options based on selected Target Type
const currentTargetOptions = computed(() => {
    if (form.target_type === 'book') return props.books.map(b => ({ id: b.id, name: b.title }));
    if (form.target_type === 'category') return props.categories.map(c => ({ id: c.id, name: c.name }));
    if (form.target_type === 'publisher') return props.publishers.map(p => ({ id: p.id, name: p.name }));
    return [];
});

const filteredTargetOptions = computed(() => {
    if (!targetSearch.value.trim()) return currentTargetOptions.value;
    const query = targetSearch.value.toLowerCase();
    return currentTargetOptions.value.filter(item => item.name.toLowerCase().includes(query));
});

function toggleTargetSelection(id: number) {
    const idx = form.target_ids.indexOf(id);
    if (idx === -1) {
        form.target_ids.push(id);
    } else {
        form.target_ids.splice(idx, 1);
    }
}

function selectAllTargets() {
    form.target_ids = currentTargetOptions.value.map(item => item.id);
}

function clearAllTargets() {
    form.target_ids = [];
}

function submit() {
    if (isEditing) {
        form.put(`/admin/coupons/${props.coupon.id}`);
    } else {
        form.post('/admin/coupons');
    }
}
</script>

<template>
    <Head :title="`${isEditing ? 'Edit' : 'Create'} Coupon — BookVerse Admin`" />

    <AdminLayout>
        <template #header>{{ isEditing ? 'Edit Coupon' : 'Create Coupon' }}</template>

        <div class="space-y-6">
            <!-- Header Banner Card (Matching Reference Screenshot) -->
            <div class="p-6 sm:p-8 rounded-3xl border transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                 :class="isDark ? 'bg-zinc-900 border-zinc-800' : 'bg-white border-slate-200/80 shadow-xs'">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold font-heading tracking-tight" :class="isDark ? 'text-white' : 'text-slate-900'">
                        {{ isEditing ? `Edit Coupon` : 'Create New Coupon' }}
                    </h1>
                    <p class="text-xs sm:text-sm font-medium mt-1.5" :class="isDark ? 'text-slate-400' : 'text-slate-500'">
                        Configure discount rules, pricing metadata, target scope, and promo limits.
                    </p>
                </div>
                <Link href="/admin/coupons"
                      class="inline-flex items-center justify-center px-5 py-2.5 rounded-full text-xs sm:text-sm font-bold transition-all shadow-sm shrink-0"
                      :class="isDark ? 'bg-zinc-800 hover:bg-zinc-700 text-white' : 'bg-slate-900 hover:bg-slate-800 text-white'">
                    ← Back to Coupons
                </Link>
            </div>

            <form @submit.prevent="submit" class="p-6 rounded-2xl border space-y-6 transition-colors" :class="isDark ? 'bg-zinc-900 border-zinc-800' : 'bg-white border-slate-200 shadow-xs'">
                
                <!-- Code & Name -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold uppercase tracking-wider" :class="isDark ? 'text-slate-300' : 'text-slate-700'">
                            Coupon Code *
                        </label>
                        <input
                            type="text"
                            v-model="form.code"
                            placeholder="e.g. SUMMER100"
                            class="w-full text-xs font-mono uppercase px-3.5 py-2.5 rounded-xl border transition"
                            :class="isDark ? 'bg-zinc-800 border-zinc-700 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'"
                            required
                        />
                        <p v-if="form.errors.code" class="text-[11px] text-rose-500 font-medium">{{ form.errors.code }}</p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold uppercase tracking-wider" :class="isDark ? 'text-slate-300' : 'text-slate-700'">
                            Campaign Title / Description
                        </label>
                        <input
                            type="text"
                            v-model="form.name"
                            placeholder="e.g. Summer Book Fair Special Offer"
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border transition"
                            :class="isDark ? 'bg-zinc-800 border-zinc-700 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'"
                        />
                    </div>
                </div>

                <!-- Type & Value -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold uppercase tracking-wider" :class="isDark ? 'text-slate-300' : 'text-slate-700'">
                            Discount Type *
                        </label>
                        <select
                            v-model="form.type"
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border transition cursor-pointer"
                            :class="isDark ? 'bg-zinc-800 border-zinc-700 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'"
                        >
                            <option value="flat">Flat Amount Discount (৳)</option>
                            <option value="percent">Percentage Discount (%)</option>
                            <option value="free_shipping">Free Shipping Offer</option>
                        </select>
                    </div>

                    <div v-if="form.type !== 'free_shipping'" class="space-y-1">
                        <label class="text-xs font-bold uppercase tracking-wider" :class="isDark ? 'text-slate-300' : 'text-slate-700'">
                            Discount Value *
                        </label>
                        <input
                            type="number"
                            step="0.01"
                            v-model.number="form.value"
                            placeholder="Value"
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border transition font-mono"
                            :class="isDark ? 'bg-zinc-800 border-zinc-700 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'"
                        />
                    </div>

                    <div v-if="form.type === 'percent'" class="space-y-1">
                        <label class="text-xs font-bold uppercase tracking-wider" :class="isDark ? 'text-slate-300' : 'text-slate-700'">
                            Max Discount Cap (৳)
                        </label>
                        <input
                            type="number"
                            step="0.01"
                            v-model.number="form.max_discount"
                            placeholder="Optional maximum cap"
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border transition font-mono"
                            :class="isDark ? 'bg-zinc-800 border-zinc-700 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'"
                        />
                    </div>
                </div>

                <!-- Limits & Conditions -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold uppercase tracking-wider" :class="isDark ? 'text-slate-300' : 'text-slate-700'">
                            Min Order Subtotal (৳)
                        </label>
                        <input
                            type="number"
                            step="0.01"
                            v-model.number="form.min_order_amount"
                            placeholder="0"
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border transition font-mono"
                            :class="isDark ? 'bg-zinc-800 border-zinc-700 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold uppercase tracking-wider" :class="isDark ? 'text-slate-300' : 'text-slate-700'">
                            Total Max Uses
                        </label>
                        <input
                            type="number"
                            v-model.number="form.max_uses"
                            placeholder="Leave blank for unlimited"
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border transition font-mono"
                            :class="isDark ? 'bg-zinc-800 border-zinc-700 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold uppercase tracking-wider" :class="isDark ? 'text-slate-300' : 'text-slate-700'">
                            Per User Limit *
                        </label>
                        <input
                            type="number"
                            v-model.number="form.user_limit"
                            placeholder="1"
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border transition font-mono"
                            :class="isDark ? 'bg-zinc-800 border-zinc-700 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'"
                            required
                        />
                    </div>
                </div>

                <!-- Modern Scoped Target Picker UI -->
                <div class="p-5 rounded-2xl border space-y-4 transition-colors" :class="isDark ? 'bg-zinc-800/40 border-zinc-700/60' : 'bg-slate-50 border-slate-200'">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-sky-600 dark:text-sky-400 flex items-center gap-2">
                                🎯 Catalog Target Scope
                            </h3>
                            <p class="text-[11px] mt-0.5" :class="isDark ? 'text-slate-400' : 'text-slate-500'">
                                Limit coupon eligibility to specific books, categories or publishers
                            </p>
                        </div>

                        <select
                            v-model="form.target_type"
                            @change="form.target_ids = []"
                            class="text-xs font-bold px-3 py-1.5 rounded-xl border transition cursor-pointer"
                            :class="isDark ? 'bg-zinc-800 border-zinc-700 text-white' : 'bg-white border-slate-200 text-slate-800 shadow-2xs'"
                        >
                            <option value="">Global (Entire Store)</option>
                            <option value="book">Specific Books</option>
                            <option value="category">Specific Categories</option>
                            <option value="publisher">Specific Publishers</option>
                        </select>
                    </div>

                    <!-- Custom Searchable Target Picker -->
                    <div v-if="form.target_type" class="space-y-3 pt-2 border-t" :class="isDark ? 'border-zinc-700' : 'border-slate-200'">
                        <div class="flex items-center justify-between gap-3">
                            <input
                                type="text"
                                v-model="targetSearch"
                                placeholder="Search targets..."
                                class="text-xs px-3 py-1.5 rounded-xl border flex-1 transition"
                                :class="isDark ? 'bg-zinc-800 border-zinc-700 text-white placeholder-slate-500' : 'bg-white border-slate-200 text-slate-900'"
                            />
                            <div class="flex items-center gap-2">
                                <button type="button" @click="selectAllTargets" class="text-[11px] font-semibold text-sky-600 dark:text-sky-400 hover:underline">
                                    Select All
                                </button>
                                <span class="text-slate-300">|</span>
                                <button type="button" @click="clearAllTargets" class="text-[11px] font-semibold text-rose-600 dark:text-rose-400 hover:underline">
                                    Clear
                                </button>
                            </div>
                        </div>

                        <!-- Checkbox List -->
                        <div class="max-h-48 overflow-y-auto rounded-xl border divide-y p-2 space-y-1 transition-colors"
                             :class="isDark ? 'bg-zinc-900 border-zinc-700 divide-zinc-800' : 'bg-white border-slate-200 divide-slate-100 shadow-2xs'">
                            <label
                                v-for="item in filteredTargetOptions"
                                :key="item.id"
                                class="flex items-center justify-between p-2 rounded-lg cursor-pointer transition text-xs"
                                :class="[
                                    form.target_ids.includes(item.id)
                                        ? (isDark ? 'bg-sky-500/15 text-sky-300 font-bold' : 'bg-sky-50 text-sky-900 font-bold')
                                        : (isDark ? 'text-slate-300 hover:bg-zinc-800' : 'text-slate-700 hover:bg-slate-50')
                                ]"
                            >
                                <span>{{ item.name }}</span>
                                <input
                                    type="checkbox"
                                    :value="item.id"
                                    :checked="form.target_ids.includes(item.id)"
                                    @change="toggleTargetSelection(item.id)"
                                    class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer"
                                />
                            </label>
                            <div v-if="filteredTargetOptions.length === 0" class="p-4 text-center text-xs" :class="isDark ? 'text-slate-500' : 'text-slate-400'">
                                No matching targets found.
                            </div>
                        </div>

                        <div class="text-[11px] font-medium text-sky-600 dark:text-sky-400 flex items-center justify-between">
                            <span>Selected: {{ form.target_ids.length }} items</span>
                        </div>
                    </div>
                </div>

                <!-- Dates & Active Status -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold uppercase tracking-wider" :class="isDark ? 'text-slate-300' : 'text-slate-700'">Starts At</label>
                        <input
                            type="datetime-local"
                            v-model="form.starts_at"
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border transition"
                            :class="isDark ? 'bg-zinc-800 border-zinc-700 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold uppercase tracking-wider" :class="isDark ? 'text-slate-300' : 'text-slate-700'">Expires At</label>
                        <input
                            type="datetime-local"
                            v-model="form.expires_at"
                            class="w-full text-xs px-3.5 py-2.5 rounded-xl border transition"
                            :class="isDark ? 'bg-zinc-800 border-zinc-700 text-white' : 'bg-slate-50 border-slate-200 text-slate-900'"
                        />
                    </div>
                </div>

                <div class="flex items-center gap-6 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold" :class="isDark ? 'text-slate-300' : 'text-slate-700'">
                        <input type="checkbox" v-model="form.is_active" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500" />
                        <span>Active Coupon</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold" :class="isDark ? 'text-slate-300' : 'text-slate-700'">
                        <input type="checkbox" v-model="form.first_order_only" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500" />
                        <span>First Order Only Offer</span>
                    </label>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t" :class="isDark ? 'border-zinc-800' : 'border-slate-100'">
                    <Link href="/admin/coupons" class="px-4 py-2.5 rounded-xl border text-xs font-semibold transition" :class="isDark ? 'border-zinc-700 text-slate-300 hover:bg-zinc-800' : 'border-slate-300 text-slate-700 hover:bg-slate-100'">
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold transition shadow-sm"
                    >
                        {{ isEditing ? 'Update Coupon' : 'Create Coupon' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
