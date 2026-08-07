<template>
    <MainLayout>
        <Head title="Secure Checkout — BookVerse" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans">
            <!-- Header Banner & Multi-Step Indicator -->
            <div class="mb-8 border-b border-slate-200 pb-6 space-y-4">
                <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400">
                    <a href="/" class="hover:text-slate-700">Home</a>
                    <span>/</span>
                    <a :href="route('cart.index')" class="hover:text-slate-700">Cart</a>
                    <span>/</span>
                    <span class="text-sky-600">Checkout</span>
                </nav>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold font-heading text-slate-900">
                            Order Checkout
                        </h1>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Complete shipping details, apply promo coupon & choose payment method
                        </p>
                    </div>

                    <!-- Tailwind Multi-Step Indicator -->
                    <div class="flex items-center gap-1 sm:gap-2 text-[11px] font-bold">
                        <div class="flex items-center gap-1 text-sky-600">
                            <span class="w-5 h-5 rounded-full bg-sky-600 text-white flex items-center justify-center text-[10px]">1</span>
                            <span>তথ্য</span>
                        </div>
                        <span class="text-slate-300">→</span>
                        <div class="flex items-center gap-1 text-sky-600">
                            <span class="w-5 h-5 rounded-full bg-sky-600 text-white flex items-center justify-center text-[10px]">2</span>
                            <span>ঠিকানা</span>
                        </div>
                        <span class="text-slate-300">→</span>
                        <div class="flex items-center gap-1 text-sky-600">
                            <span class="w-5 h-5 rounded-full bg-sky-600 text-white flex items-center justify-center text-[10px]">3</span>
                            <span>কুপন</span>
                        </div>
                        <span class="text-slate-300">→</span>
                        <div class="flex items-center gap-1 text-sky-600">
                            <span class="w-5 h-5 rounded-full bg-sky-600 text-white flex items-center justify-center text-[10px]">4</span>
                            <span>পেমেন্ট</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Checkout Form Grid -->
            <form @submit.prevent="submitCheckout" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left Form Section (7 Columns) -->
                <div class="lg:col-span-7 space-y-6">

                    <!-- Step 1: Contact Information -->
                    <div class="bg-white p-6 border border-slate-200 rounded-2xl shadow-2xs space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-sky-600 text-white font-bold text-xs flex items-center justify-center">1</span>
                                <h3 class="font-bold text-sm text-slate-900 font-heading">Contact Information</h3>
                            </div>
                            <span v-if="savedAddress" class="text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                                ✨ Auto-Filled from Saved Address
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Full Name <span class="text-rose-500">*</span></label>
                                <Input v-model="form.shipping_name" placeholder="e.g. Tanvir Ahmed" required />
                                <p v-if="form.errors.shipping_name" class="text-xs text-rose-600 mt-1">{{ form.errors.shipping_name }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Mobile Phone <span class="text-rose-500">*</span></label>
                                <Input v-model="form.shipping_phone" placeholder="01712345678" required />
                                <p v-if="form.errors.shipping_phone" class="text-xs text-rose-600 mt-1">{{ form.errors.shipping_phone }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email Address (Optional)</label>
                            <Input v-model="form.shipping_email" type="email" placeholder="tanvir@example.com" />
                            <p v-if="form.errors.shipping_email" class="text-xs text-rose-600 mt-1">{{ form.errors.shipping_email }}</p>
                        </div>
                    </div>

                    <!-- Step 2: Shipping Address Dropdowns -->
                    <div class="bg-white p-6 border border-slate-200 rounded-2xl shadow-2xs space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                            <span class="w-6 h-6 rounded-full bg-sky-600 text-white font-bold text-xs flex items-center justify-center">2</span>
                            <h3 class="font-bold text-sm text-slate-900 font-heading">ডেলিভারি ঠিকানা (বাংলাদেশ)</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- Division Select -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">বিভাগ <span class="text-rose-500">*</span></label>
                                <select v-model="form.division_id" class="w-full h-10 px-3 text-xs font-medium text-slate-800 rounded-xl border-slate-200 focus:border-sky-500 focus:ring-sky-500 bg-slate-50 cursor-pointer" required>
                                    <option value="" disabled>বিভাগ নির্বাচন করুন</option>
                                    <option v-for="div in divisions" :key="div.id" :value="div.id">
                                        {{ div.bn_name || div.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.division_id" class="text-xs text-rose-600 mt-1">{{ form.errors.division_id }}</p>
                            </div>

                            <!-- District Select -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">জেলা <span class="text-rose-500">*</span></label>
                                <select v-model="form.district_id" :disabled="!form.division_id" class="w-full h-10 px-3 text-xs font-medium text-slate-800 rounded-xl border-slate-200 focus:border-sky-500 focus:ring-sky-500 bg-slate-50 disabled:opacity-50 cursor-pointer" required>
                                    <option value="" disabled>জেলা নির্বাচন করুন</option>
                                    <option v-for="dist in filteredDistricts" :key="dist.id" :value="dist.id">
                                        {{ dist.bn_name || dist.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.district_id" class="text-xs text-rose-600 mt-1">{{ form.errors.district_id }}</p>
                            </div>

                            <!-- Upazila Select -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">থানা / উপজেলা <span class="text-rose-500">*</span></label>
                                <select v-model="form.upazila_id" :disabled="!form.district_id" class="w-full h-10 px-3 text-xs font-medium text-slate-800 rounded-xl border-slate-200 focus:border-sky-500 focus:ring-sky-500 bg-slate-50 disabled:opacity-50 cursor-pointer" required>
                                    <option value="" disabled>উপজেলা নির্বাচন করুন</option>
                                    <option v-for="upz in filteredUpazilas" :key="upz.id" :value="upz.id">
                                        {{ upz.bn_name || upz.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.upazila_id" class="text-xs text-rose-600 mt-1">{{ form.errors.upazila_id }}</p>
                            </div>
                        </div>

                        <!-- Street Address -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">বিস্তারিত ঠিকানা / রোড নং / এলাকা <span class="text-rose-500">*</span></label>
                            <textarea
                                v-model="form.shipping_street"
                                rows="2"
                                class="w-full text-xs font-sans rounded-xl border-slate-200 focus:border-sky-500 focus:ring-sky-500 p-3 bg-slate-50 text-slate-800"
                                placeholder="বাসা #১২, রোড #৪, ধানমন্ডি, ঢাকা"
                                required
                            ></textarea>
                            <p v-if="form.errors.shipping_street" class="text-xs text-rose-600 mt-1">{{ form.errors.shipping_street }}</p>
                        </div>
                    </div>

                    <!-- Step 3: Dynamic Payment Method Selection -->
                    <div class="bg-white p-6 border border-slate-200 rounded-2xl shadow-2xs space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                            <span class="w-6 h-6 rounded-full bg-sky-600 text-white font-bold text-xs flex items-center justify-center">3</span>
                            <h3 class="font-bold text-sm text-slate-900 font-heading">Payment Method</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- COD Option -->
                            <label
                                v-if="paymentSettings?.enable_cod !== false"
                                :class="['border rounded-xl p-3.5 flex items-start gap-3 cursor-pointer transition', form.payment_method === 'cod' ? 'border-sky-600 bg-sky-50/40 ring-1 ring-sky-600' : 'border-slate-200 hover:border-slate-300']"
                            >
                                <input type="radio" v-model="form.payment_method" value="cod" class="mt-0.5 text-sky-600 focus:ring-sky-500" />
                                <div>
                                    <span class="block font-bold text-xs text-slate-900 flex items-center gap-1.5">
                                        <span>💵 Cash On Delivery</span>
                                    </span>
                                    <span class="block text-[11px] text-slate-500 mt-0.5">পণ্য হাতে পেয়ে নগদ মূল্য পরিশোধ করুন</span>
                                </div>
                            </label>

                            <!-- bKash Mobile Banking -->
                            <label
                                v-if="paymentSettings?.enable_bkash"
                                :class="['border rounded-xl p-3.5 flex items-start gap-3 cursor-pointer transition', form.payment_method === 'bkash' ? 'border-sky-600 bg-sky-50/40 ring-1 ring-sky-600' : 'border-slate-200 hover:border-slate-300']"
                            >
                                <input type="radio" v-model="form.payment_method" value="bkash" class="mt-0.5 text-sky-600 focus:ring-sky-500" />
                                <div>
                                    <span class="block font-bold text-xs text-slate-900 flex items-center gap-1.5">
                                        <span>📱 bKash (বিকাশ)</span>
                                    </span>
                                    <span class="block text-[11px] text-slate-500 mt-0.5">Instant mobile gateway payment</span>
                                </div>
                            </label>

                            <!-- Nagad Mobile Banking -->
                            <label
                                v-if="paymentSettings?.enable_nagad"
                                :class="['border rounded-xl p-3.5 flex items-start gap-3 cursor-pointer transition', form.payment_method === 'nagad' ? 'border-sky-600 bg-sky-50/40 ring-1 ring-sky-600' : 'border-slate-200 hover:border-slate-300']"
                            >
                                <input type="radio" v-model="form.payment_method" value="nagad" class="mt-0.5 text-sky-600 focus:ring-sky-500" />
                                <div>
                                    <span class="block font-bold text-xs text-slate-900 flex items-center gap-1.5">
                                        <span>📱 Nagad (নগদ)</span>
                                    </span>
                                    <span class="block text-[11px] text-slate-500 mt-0.5">Fast mobile gateway payment</span>
                                </div>
                            </label>

                            <!-- Bank Wire Transfer -->
                            <label
                                v-if="paymentSettings?.enable_bank"
                                :class="['border rounded-xl p-3.5 flex items-start gap-3 cursor-pointer transition', form.payment_method === 'bank' ? 'border-sky-600 bg-sky-50/40 ring-1 ring-sky-600' : 'border-slate-200 hover:border-slate-300']"
                            >
                                <input type="radio" v-model="form.payment_method" value="bank" class="mt-0.5 text-sky-600 focus:ring-sky-500" />
                                <div>
                                    <span class="block font-bold text-xs text-slate-900 flex items-center gap-1.5">
                                        <span>🏦 Direct Bank Transfer</span>
                                    </span>
                                    <span class="block text-[11px] text-slate-500 mt-0.5">Bank deposit / EFT / Wire transfer</span>
                                </div>
                            </label>

                            <!-- Stripe Gateway -->
                            <label
                                v-if="paymentSettings?.enable_stripe"
                                :class="['border rounded-xl p-3.5 flex items-start gap-3 cursor-pointer transition', form.payment_method === 'stripe' || form.payment_method === 'sslcommerz' ? 'border-sky-600 bg-sky-50/40 ring-1 ring-sky-600' : 'border-slate-200 hover:border-slate-300']"
                            >
                                <input type="radio" v-model="form.payment_method" value="stripe" class="mt-0.5 text-sky-600 focus:ring-sky-500" />
                                <div>
                                    <span class="block font-bold text-xs text-slate-900 flex items-center gap-1.5">
                                        <span>💳 Credit / Debit Card</span>
                                    </span>
                                    <span class="block text-[11px] text-slate-500 mt-0.5">Visa, Mastercard & Global Cards</span>
                                </div>
                            </label>
                        </div>

                        <!-- Bank Details Panel (when Bank option is selected) -->
                        <div v-if="form.payment_method === 'bank' && paymentSettings?.enable_bank" class="p-4 bg-purple-50/60 border border-purple-200 rounded-xl space-y-3 mt-3">
                            <h4 class="font-bold text-xs text-purple-900 flex items-center gap-2">
                                🏦 Bank Account Information:
                            </h4>
                            <div class="text-xs space-y-1 text-purple-900 font-mono">
                                <p><span class="font-bold">Bank Name:</span> {{ paymentSettings.bank_name || 'BookVerse Commercial Bank' }}</p>
                                <p><span class="font-bold">Account Name:</span> {{ paymentSettings.bank_account_name || 'BookVerse E-Commerce Ltd' }}</p>
                                <p><span class="font-bold">Account Number:</span> {{ paymentSettings.bank_account_number || 'N/A' }}</p>
                                <p v-if="paymentSettings.bank_routing_number"><span class="font-bold">Routing Number:</span> {{ paymentSettings.bank_routing_number }}</p>
                            </div>
                            <div class="pt-2">
                                <label class="block text-xs font-bold text-purple-900 mb-1">Transaction Ref / Slip ID (Optional)</label>
                                <Input v-model="form.notes" placeholder="Enter bank deposit slip / Txn reference number" class="bg-white" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Summary Column (5 Columns) -->
                <div class="lg:col-span-5">
                    <div class="bg-white p-6 border border-slate-200 rounded-2xl shadow-2xs space-y-5 sticky top-6">
                        <h3 class="font-bold text-sm text-slate-900 font-heading border-b border-slate-100 pb-3 flex items-center justify-between">
                            <span>Order Summary ({{ cart.items.length }} Items)</span>
                            <span v-if="totalYouSaved > 0" class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">
                                ৳{{ totalYouSaved }} সাশ্রয়
                            </span>
                        </h3>

                        <!-- Items Thumbnail List -->
                        <div class="max-h-56 overflow-y-auto divide-y divide-slate-100 pr-1">
                            <div v-for="item in cart.items" :key="item.id" class="py-2.5 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <img :src="item.cover_url" :alt="item.title" class="w-10 h-14 object-cover rounded-lg border border-slate-200 shrink-0 bg-slate-50" />
                                    <div class="min-w-0">
                                        <h4 class="text-xs font-bold text-slate-800 truncate" :title="item.title">{{ item.title }}</h4>
                                        <p class="text-[11px] text-slate-500">Qty: {{ item.quantity }} × ৳{{ item.unit_price }}</p>
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-slate-900 shrink-0">৳{{ item.subtotal }}</span>
                            </div>
                        </div>

                        <!-- Step 4: Inline Coupon Input Component -->
                        <div class="pt-2 border-t border-slate-100">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">🎟️ কুপন বা প্রোমো কোড</label>
                            <CouponInput
                                :subtotal="cart.subtotal"
                                :initial-coupon-code="form.coupon_code"
                                @applied="handleCouponApplied"
                                @removed="handleCouponRemoved"
                            />
                        </div>

                        <!-- Cost Breakdown -->
                        <div class="border-t border-slate-200 pt-3 space-y-2 text-xs">
                            <div class="flex justify-between text-slate-600">
                                <span>Subtotal</span>
                                <span class="font-medium text-slate-900">৳{{ cart.subtotal }}</span>
                            </div>
                            
                            <!-- Coupon Discount Line -->
                            <div v-if="couponDiscountAmount > 0" class="flex justify-between text-emerald-600 font-medium">
                                <span>Coupon Discount</span>
                                <span class="font-bold">-৳{{ couponDiscountAmount }}</span>
                            </div>

                            <div class="flex justify-between text-slate-600">
                                <span>Shipping Fee</span>
                                <span v-if="effectiveShippingFee === 0" class="font-bold text-emerald-600">FREE</span>
                                <span v-else class="font-medium text-slate-900">৳{{ effectiveShippingFee }}</span>
                            </div>

                            <div class="flex justify-between text-slate-900 font-bold text-sm border-t border-slate-200 pt-3">
                                <span>Total Payable</span>
                                <span class="text-sky-600 font-mono text-base">৳{{ payableGrandTotal }}</span>
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <Button
                            type="submit"
                            variant="brand"
                            size="lg"
                            class="w-full justify-center text-sm font-bold shadow-md cursor-pointer"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing">Processing Order...</span>
                            <span v-else>Confirm & Place Order (৳{{ payableGrandTotal }})</span>
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import CouponInput from '@/Components/Commerce/CouponInput.vue';

export interface LocationItem {
    id: number;
    division_id?: number;
    district_id?: number;
    name: string;
    bn_name: string;
}

export interface CartItem {
    id: number;
    book_id: number;
    title: string;
    slug: string;
    unit_price: number;
    quantity: number;
    subtotal: number;
    format: string;
    cover_url: string;
}

export interface CartSummary {
    items: CartItem[];
    subtotal: number;
    currency: string;
}

const props = defineProps<{
    cart: CartSummary;
    divisions: LocationItem[];
    districts: LocationItem[];
    upazilas: LocationItem[];
    shippingRates: {
        inside_dhaka: number;
        outside_dhaka: number;
        express_delivery: number;
    };
    freeShippingThreshold: number;
    savedAddress?: {
        shipping_name?: string;
        shipping_phone?: string;
        shipping_email?: string;
        division_id?: number | string;
        district_id?: number | string;
        upazila_id?: number | string;
        shipping_street?: string;
        shipping_postal_code?: string;
    } | null;
    paymentSettings?: {
        enable_cod?: boolean;
        enable_bkash?: boolean;
        enable_nagad?: boolean;
        enable_bank?: boolean;
        bank_name?: string;
        bank_account_name?: string;
        bank_account_number?: string;
        bank_routing_number?: string;
        enable_stripe?: boolean;
        stripe_key?: string;
    };
}>();

const page = usePage();
const authUser = computed(() => page.props.auth?.user);

// Random idempotency key generator for duplicate submission safety
const idempotencyKey = ref(`IDEM-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`);

// Initial payment method selection logic
const defaultPaymentMethod = computed(() => {
    if (props.paymentSettings?.enable_cod !== false) return 'cod';
    if (props.paymentSettings?.enable_bkash) return 'bkash';
    if (props.paymentSettings?.enable_nagad) return 'nagad';
    if (props.paymentSettings?.enable_bank) return 'bank';
    if (props.paymentSettings?.enable_stripe) return 'stripe';
    return 'cod';
});

const form = useForm({
    shipping_name: props.savedAddress?.shipping_name || authUser.value?.name || '',
    shipping_phone: props.savedAddress?.shipping_phone || authUser.value?.phone || '',
    shipping_email: props.savedAddress?.shipping_email || authUser.value?.email || '',
    division_id: props.savedAddress?.division_id ? Number(props.savedAddress.division_id) : '',
    district_id: props.savedAddress?.district_id ? Number(props.savedAddress.district_id) : '',
    upazila_id: props.savedAddress?.upazila_id ? Number(props.savedAddress.upazila_id) : '',
    shipping_street: props.savedAddress?.shipping_street || '',
    shipping_postal_code: props.savedAddress?.shipping_postal_code || '',
    payment_method: defaultPaymentMethod.value,
    coupon_code: '',
    idempotency_key: idempotencyKey.value,
    notes: '',
});

// Reactive Coupon Calculation Result
const couponCalculation = ref<any>(null);

function handleCouponApplied(calc: any) {
    couponCalculation.value = calc;
    form.coupon_code = calc.coupon?.code || '';
}

function handleCouponRemoved() {
    couponCalculation.value = null;
    form.coupon_code = '';
}

// Cascading Filtered Districts & Upazilas
const filteredDistricts = computed(() => {
    if (!form.division_id) return [];
    return props.districts.filter(d => d.division_id === Number(form.division_id));
});

const filteredUpazilas = computed(() => {
    if (!form.district_id) return [];
    const list = props.upazilas.filter(u => u.district_id === Number(form.district_id));
    if (list.length > 0) return list;

    const selectedDist = props.districts.find(d => d.id === Number(form.district_id));
    if (!selectedDist) return [];

    const name = selectedDist.name;
    const bnName = selectedDist.bn_name;
    return [
        { id: selectedDist.id * 100 + 1, district_id: selectedDist.id, name: `${name} Sadar`, bn_name: `${bnName} সদর` },
        { id: selectedDist.id * 100 + 2, district_id: selectedDist.id, name: `${name} Pourashava`, bn_name: `${bnName} পৌরসভা` },
        { id: selectedDist.id * 100 + 3, district_id: selectedDist.id, name: `${name} Central`, bn_name: `${bnName} কেন্দ্রীয় থানা` },
        { id: selectedDist.id * 100 + 4, district_id: selectedDist.id, name: `${name} North`, bn_name: `${bnName} উত্তর থানা` },
    ];
});

watch(() => form.division_id, (newVal, oldVal) => {
    if (oldVal !== undefined && oldVal !== '' && oldVal !== newVal) {
        form.district_id = '';
        form.upazila_id = '';
    }
});

watch(() => form.district_id, (newVal, oldVal) => {
    if (oldVal !== undefined && oldVal !== '' && oldVal !== newVal) {
        form.upazila_id = '';
    }
});

// Base Shipping Fee calculation
const baseShippingFee = computed(() => {
    if (props.cart.subtotal >= props.freeShippingThreshold) {
        return 0;
    }
    if (!form.division_id) {
        return props.shippingRates.inside_dhaka;
    }
    const selectedDivision = props.divisions.find(d => d.id === Number(form.division_id));
    if (selectedDivision && (selectedDivision.id === 1 || selectedDivision.name.toLowerCase().includes('dhaka'))) {
        return props.shippingRates.inside_dhaka;
    }
    return props.shippingRates.outside_dhaka;
});

// Final Effective Shipping Fee
const effectiveShippingFee = computed(() => {
    if (couponCalculation.value && couponCalculation.value.is_free_shipping) {
        return 0;
    }
    return baseShippingFee.value;
});

// Coupon Discount
const couponDiscountAmount = computed(() => {
    if (couponCalculation.value && couponCalculation.value.is_valid) {
        return couponCalculation.value.coupon_discount;
    }
    return 0;
});

// Total Savings
const totalYouSaved = computed(() => {
    let saved = couponDiscountAmount.value;
    if (couponCalculation.value && couponCalculation.value.is_free_shipping) {
        saved += baseShippingFee.value;
    }
    return saved;
});

// Final Payable Grand Total
const payableGrandTotal = computed(() => {
    const sub = Math.max(0, props.cart.subtotal - couponDiscountAmount.value);
    return sub + effectiveShippingFee.value;
});

function submitCheckout() {
    form.post(route('checkout.store'), {
        preserveScroll: true,
        onError: () => {
            idempotencyKey.value = `IDEM-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`;
            form.idempotency_key = idempotencyKey.value;
        },
    });
}
</script>