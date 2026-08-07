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

const step = ref<'phone' | 'otp' | 'pin'>('phone');
const phone = ref('01700000000');
const otp = ref('123456');
const pin = ref('12345');

const form = useForm({
    paymentID: props.paymentID,
    orderNumber: props.orderNumber,
    trxID: 'BK' + Math.floor(1000000000 + Math.random() * 9000000000),
    status: 'Completed',
});

function handleNext() {
    if (step.value === 'phone') {
        if (!phone.value || phone.value.length < 11) return;
        step.value = 'otp';
    } else if (step.value === 'otp') {
        step.value = 'pin';
    } else if (step.value === 'pin') {
        form.post('/payment/bkash/callback');
    }
}

function handleCancel() {
    form.status = 'Cancelled';
    form.post('/payment/bkash/callback');
}
</script>

<template>
    <Head title="bKash Payment Gateway Sandbox" />

    <div class="min-h-screen bg-slate-900 flex items-center justify-center p-4 font-sans text-slate-800">
        <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden border border-rose-100">
            <!-- bKash Pink Brand Header -->
            <div class="bg-[#E2136E] text-white p-6 text-center space-y-2 relative">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md text-2xl font-black shadow-inner">
                    bKash
                </div>
                <h2 class="text-xl font-extrabold font-heading tracking-tight">bKash Payment Gateway</h2>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-xs font-bold uppercase tracking-wider">
                    ⚡ Sandbox Simulation
                </div>
            </div>

            <!-- Merchant & Order Summary -->
            <div class="bg-rose-50/60 p-4 border-b border-rose-100 flex items-center justify-between text-xs font-medium">
                <div>
                    <span class="text-slate-500 block">Merchant</span>
                    <span class="font-bold text-slate-900">{{ merchantName }}</span>
                </div>
                <div class="text-right">
                    <span class="text-slate-500 block">Amount Payable</span>
                    <span class="font-bold text-[#E2136E] font-mono text-sm">৳{{ amount }}</span>
                </div>
            </div>

            <!-- Form Step Content -->
            <div class="p-6 space-y-5">
                <div v-if="step === 'phone'" class="space-y-4">
                    <label class="block text-xs font-bold text-slate-700">Your bKash Account Number</label>
                    <input
                        v-model="phone"
                        type="text"
                        placeholder="e.g. 01711XXXXXX"
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 font-mono text-sm focus:outline-none focus:border-[#E2136E] focus:ring-2 focus:ring-[#E2136E]/20"
                    />
                    <p class="text-[11px] text-slate-500">Enter test bKash wallet number (e.g. 01700000000).</p>
                </div>

                <div v-else-if="step === 'otp'" class="space-y-4">
                    <label class="block text-xs font-bold text-slate-700">Enter Verification Code (OTP)</label>
                    <input
                        v-model="otp"
                        type="text"
                        placeholder="123456"
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 font-mono text-center text-lg tracking-widest focus:outline-none focus:border-[#E2136E]"
                    />
                    <p class="text-[11px] text-slate-500">Test OTP Code auto-filled: 123456</p>
                </div>

                <div v-else-if="step === 'pin'" class="space-y-4">
                    <label class="block text-xs font-bold text-slate-700">Enter bKash PIN</label>
                    <input
                        v-model="pin"
                        type="password"
                        placeholder="•••••"
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 font-mono text-center text-lg tracking-widest focus:outline-none focus:border-[#E2136E]"
                    />
                    <p class="text-[11px] text-slate-500">Test PIN auto-filled: 12345</p>
                </div>

                <!-- Action CTAs -->
                <div class="space-y-2 pt-2">
                    <button
                        type="button"
                        @click="handleNext"
                        :disabled="form.processing"
                        class="w-full py-3.5 rounded-xl bg-[#E2136E] hover:bg-[#c90f60] text-white font-bold text-sm shadow-lg shadow-[#E2136E]/30 transition cursor-pointer"
                    >
                        {{ step === 'pin' ? 'Confirm Payment ৳' + amount : 'Proceed' }}
                    </button>
                    <button
                        type="button"
                        @click="handleCancel"
                        :disabled="form.processing"
                        class="w-full py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 transition cursor-pointer"
                    >
                        Cancel Payment
                    </button>
                </div>
            </div>

            <!-- Footer Disclaimer -->
            <div class="p-3 bg-slate-50 border-t border-slate-100 text-center text-[10px] text-slate-400 font-mono">
                bKash Tokenized Direct API Simulation
            </div>
        </div>
    </div>
</template>
