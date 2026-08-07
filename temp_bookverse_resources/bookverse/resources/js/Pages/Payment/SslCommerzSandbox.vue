<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    tran_id: string;
    orderNumber: string;
    amount: number;
    currency: string;
    merchantName: string;
}>();

const activeTab = ref<'cards' | 'mobile'>('cards');

const form = useForm({
    tran_id: props.tran_id,
    orderNumber: props.orderNumber,
    val_id: 'SSLVAL' + Math.floor(1000000000 + Math.random() * 9000000000),
    status: 'VALID',
});

function handleSuccess() {
    form.status = 'VALID';
    form.post('/payment/sslcommerz/success');
}

function handleFailed() {
    form.status = 'FAILED';
    form.post('/payment/sslcommerz/fail');
}
</script>

<template>
    <Head title="SSLCommerz Hosted Gateway Sandbox" />

    <div class="min-h-screen bg-slate-900 flex items-center justify-center p-4 font-sans text-slate-800">
        <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl overflow-hidden border border-teal-100">
            <!-- SSLCommerz Teal Header -->
            <div class="bg-[#00A79D] text-white p-6 text-center space-y-2 relative">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md text-2xl font-black shadow-inner">
                    💳
                </div>
                <h2 class="text-xl font-extrabold font-heading tracking-tight">SSLCommerz Secured Payment</h2>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-xs font-bold uppercase tracking-wider">
                    ⚡ Sandbox Simulation
                </div>
            </div>

            <!-- Merchant & Order Summary -->
            <div class="bg-teal-50/60 p-4 border-b border-teal-100 flex items-center justify-between text-xs font-medium">
                <div>
                    <span class="text-slate-500 block">Merchant Name</span>
                    <span class="font-bold text-slate-900">{{ merchantName }}</span>
                </div>
                <div class="text-right">
                    <span class="text-slate-500 block">Total Amount</span>
                    <span class="font-bold text-[#00A79D] font-mono text-base">৳{{ amount }}</span>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="flex border-b border-slate-200 text-xs font-bold bg-slate-50">
                <button
                    @click="activeTab = 'cards'"
                    class="flex-1 py-3 border-b-2 text-center transition cursor-pointer"
                    :class="activeTab === 'cards' ? 'border-[#00A79D] text-[#00A79D] bg-white' : 'border-transparent text-slate-500'"
                >
                    Credit / Debit Card
                </button>
                <button
                    @click="activeTab = 'mobile'"
                    class="flex-1 py-3 border-b-2 text-center transition cursor-pointer"
                    :class="activeTab === 'mobile' ? 'border-[#00A79D] text-[#00A79D] bg-white' : 'border-transparent text-slate-500'"
                >
                    Mobile Banking
                </button>
            </div>

            <!-- Content Area -->
            <div class="p-6 space-y-5">
                <div v-if="activeTab === 'cards'" class="space-y-4">
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 text-xs space-y-2">
                        <div class="font-bold text-slate-700">Test Visa / Mastercard</div>
                        <div class="font-mono text-slate-600">Card: 4000 0000 0000 0002</div>
                        <div class="font-mono text-slate-500 text-[11px]">Exp: 12/28 | CVV: 123</div>
                    </div>
                </div>

                <div v-else class="p-4 rounded-xl border border-slate-200 bg-slate-50 text-xs space-y-2 text-center">
                    <div class="font-bold text-slate-700">bKash / Nagad / Rocket / Upay</div>
                    <p class="text-[11px] text-slate-500">Select any test mobile wallet option to test payment.</p>
                </div>

                <!-- Simulation Buttons -->
                <div class="space-y-2 pt-2">
                    <button
                        type="button"
                        @click="handleSuccess"
                        :disabled="form.processing"
                        class="w-full py-3.5 rounded-xl bg-[#00A79D] hover:bg-[#008f87] text-white font-bold text-sm shadow-lg shadow-[#00A79D]/30 transition cursor-pointer"
                    >
                        Simulate Successful Payment ৳{{ amount }}
                    </button>
                    <button
                        type="button"
                        @click="handleFailed"
                        :disabled="form.processing"
                        class="w-full py-2.5 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 border border-rose-200 transition cursor-pointer"
                    >
                        Simulate Payment Failure
                    </button>
                </div>
            </div>

            <div class="p-3 bg-slate-50 border-t border-slate-100 text-center text-[10px] text-slate-400 font-mono">
                SSLCommerz Sandbox Engine
            </div>
        </div>
    </div>
</template>
