<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps<{
    initialCouponCode?: string;
    subtotal: number;
}>();

const emit = defineEmits<{
    (e: 'applied', calculation: any): void;
    (e: 'removed'): void;
}>();

const couponCode = ref(props.initialCouponCode || '');
const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const appliedCalculation = ref<any>(null);

async function applyCoupon() {
    if (!couponCode.value.trim()) {
        errorMessage.value = 'একটি কুপন কোড লিখুন।';
        return;
    }

    loading.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        const response = await axios.post('/coupons/apply', {
            code: couponCode.value.trim(),
        });

        if (response.data.success) {
            appliedCalculation.value = response.data.calculation;
            successMessage.value = response.data.message;
            emit('applied', response.data.calculation);
        }
    } catch (err: any) {
        errorMessage.value = err.response?.data?.message || 'কুপনটি সঠিক নয় বা এর মেয়াদ শেষ হয়ে গেছে।';
    } finally {
        loading.value = false;
    }
}

async function removeCoupon() {
    loading.value = true;
    try {
        await axios.post('/coupons/remove');
        appliedCalculation.value = null;
        couponCode.value = '';
        successMessage.value = '';
        errorMessage.value = '';
        emit('removed');
    } catch (err) {
        console.error('Failed to remove coupon', err);
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="space-y-2">
        <!-- Active Applied Coupon Pill -->
        <div v-if="appliedCalculation" class="flex items-center justify-between p-3 rounded-xl border border-emerald-200 bg-emerald-50 text-xs">
            <div class="flex items-center gap-2">
                <span class="font-bold text-emerald-700 uppercase tracking-wider font-mono">
                    🎟️ {{ appliedCalculation.coupon.code }}
                </span>
                <span v-if="appliedCalculation.is_free_shipping" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-200 text-emerald-800">
                    ফ্রি শিপিং
                </span>
                <span v-else class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-200 text-emerald-800">
                    ৳{{ appliedCalculation.coupon_discount }} ছাড়
                </span>
            </div>
            <button
                type="button"
                @click="removeCoupon"
                :disabled="loading"
                class="text-xs text-rose-600 hover:text-rose-700 font-bold transition"
            >
                মুছুন ✕
            </button>
        </div>

        <!-- Input Box & Apply Button -->
        <div v-else class="flex gap-2">
            <input
                type="text"
                v-model="couponCode"
                placeholder="কুপন কোড (যেমন: SUMMER100)"
                @keyup.enter="applyCoupon"
                class="flex-1 text-xs uppercase font-mono px-3.5 py-2.5 rounded-xl border border-slate-200 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition"
            />
            <button
                type="button"
                @click="applyCoupon"
                :disabled="loading || !couponCode.trim()"
                class="px-4 py-2.5 text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 disabled:opacity-50 rounded-xl transition shadow-xs flex items-center gap-1.5"
            >
                <svg v-if="loading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span>ব্যবহার করুন</span>
            </button>
        </div>

        <!-- Validation Error Message -->
        <p v-if="errorMessage" class="text-[11px] text-rose-600 font-medium flex items-center gap-1">
            <span>⚠️</span> {{ errorMessage }}
        </p>

        <!-- Success Message -->
        <p v-if="successMessage" class="text-[11px] text-emerald-600 font-medium flex items-center gap-1">
            <span>✅</span> {{ successMessage }}
        </p>
    </div>
</template>
