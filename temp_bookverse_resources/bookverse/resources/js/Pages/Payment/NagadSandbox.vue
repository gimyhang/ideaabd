<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    paymentID: string;
    orderNumber: string;
    amount: number;
    currency: string;
    merchantName: string;
}>();

const phone = ref('01700000000');

const form = useForm({
    paymentID: props.paymentID,
    orderNumber: props.orderNumber,
    issuer_payment_ref: 'NG' + Math.floor(1000000000 + Math.random() * 9000000000),
    status: 'Success',
});

function handlePay() {
    form.post('/payment/nagad/callback');
}

function handleCancel() {
    form.status = 'Failed';
    form.post('/payment/nagad/callback');
}
</script>

<template>
    <Head title="Nagad Payment Gateway Sandbox" />

    <div class="min-h-screen bg-slate-900 flex items-center justify-center p-4 font-sans text-slate-800">
        <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden border border-orange-100">
            <!-- Nagad Orange Brand Header -->
            <div class="bg-[#F7921E] text-white p-6 text-center space-y-2 relative">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md text-2xl font-black shadow-inner">
                    নগদ
                </div>
                <h2 class="text-xl font-extrabold font-heading tracking-tight">Nagad Merchant Payment Gateway</h2>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-xs font-bold uppercase tracking-wider">
                    ⚡ Sandbox Simulation
                </div>
            </div>

            <!-- Merchant & Order Summary -->
            <div class="bg-orange-50/60 p-4 border-b border-orange-100 flex items-center justify-between text-xs font-medium">
                <div>
                    <span class="text-slate-500 block">Merchant</span>
                    <span class="font-bold text-slate-900">{{ merchantName }}</span>
                </div>
                <div class="text-right">
                    <span class="text-slate-500 block">Amount Payable</span>
                    <span class="font-bold text-[#F7921E] font-mono text-sm">৳{{ amount }}</span>
                </div>
            </div>

            <!-- Form Content -->
            <div class="p-6 space-y-5">
                <div class="space-y-4">
                    <label class="block text-xs font-bold text-slate-700">Nagad Account Number</label>
                    <input
                        v-model="phone"
                        type="text"
                        placeholder="01700000000"
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 font-mono text-sm focus:outline-none focus:border-[#F7921E] focus:ring-2 focus:ring-[#F7921E]/20"
                    />
                    <p class="text-[11px] text-slate-500">Test Nagad Account Number (e.g. 01700000000).</p>
                </div>

                <!-- Action CTAs -->
                <div class="space-y-2 pt-2">
                    <button
                        type="button"
                        @click="handlePay"
                        :disabled="form.processing"
                        class="w-full py-3.5 rounded-xl bg-[#F7921E] hover:bg-[#e07f12] text-white font-bold text-sm shadow-lg shadow-[#F7921E]/30 transition cursor-pointer"
                    >
                        Pay Now ৳{{ amount }}
                    </button>
                    <button
                        type="button"
                        @click="handleCancel"
                        :disabled="form.processing"
                        class="w-full py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 transition cursor-pointer"
                    >
                        Cancel Transaction
                    </button>
                </div>
            </div>

            <div class="p-3 bg-slate-50 border-t border-slate-100 text-center text-[10px] text-slate-400 font-mono">
                Nagad Merchant API Simulation
            </div>
        </div>
    </div>
</template>
