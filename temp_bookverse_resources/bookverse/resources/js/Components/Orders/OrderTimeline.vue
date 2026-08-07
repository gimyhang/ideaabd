<script setup lang="ts">
import { computed } from 'vue';

interface HistoryRecord {
    id: number;
    old_status: string | null;
    new_status: string;
    reason: string;
    changed_by: string;
    created_at: string;
}

const props = defineProps<{
    status: string;
    histories?: HistoryRecord[];
}>();

const isCancelled = computed(() => ['cancelled', 'refunded'].includes(props.status.toLowerCase()));

const steps = [
    { key: 'pending', number: 1, label: 'Order Placed', desc: 'Order received and confirmed' },
    { key: 'processing', number: 2, label: 'Processing & Packing', desc: 'Items reserved and being packaged' },
    { key: 'shipped', number: 3, label: 'Shipped', desc: 'Handed over to courier service' },
    { key: 'delivered', number: 4, label: 'Delivered', desc: 'Successfully delivered to recipient' },
];

function getStepState(stepKey: string, stepNum: number) {
    const s = props.status.toLowerCase();
    const map: Record<string, number> = {
        pending: 1,
        processing: 2,
        shipped: 3,
        delivered: 4,
        completed: 4,
    };
    const currentNum = map[s] || 1;

    if (stepNum < currentNum) return 'completed';
    if (stepNum === currentNum) return 'current';
    return 'upcoming';
}

const cancellationHistory = computed(() => {
    if (!props.histories) return null;
    return props.histories.find(h => h.new_status === 'cancelled');
});
</script>

<template>
    <div class="space-y-6">
        <!-- Cancelled Order Banner -->
        <div v-if="isCancelled" class="p-4 sm:p-5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 space-y-2">
            <div class="flex items-center gap-2 text-rose-700 font-bold text-xs sm:text-sm">
                <span class="text-base sm:text-lg">🚫</span>
                <span>Order Cancelled</span>
            </div>
            <p class="text-xs text-rose-700 leading-relaxed">
                Reason: {{ cancellationHistory?.reason || 'This order was cancelled and reserved inventory stock has been released.' }}
            </p>
            <p v-if="cancellationHistory" class="text-[10px] text-rose-500 font-mono">
                Cancelled on {{ cancellationHistory.created_at }} by {{ cancellationHistory.changed_by }}
            </p>
        </div>

        <!-- Normal Active Step Timeline -->
        <div v-else>
            <!-- Desktop Horizontal Timeline (sm:grid) -->
            <div class="hidden sm:block relative">
                <!-- Connecting Line -->
                <div class="absolute top-1/2 left-8 right-8 -translate-y-1/2 h-1 bg-slate-100 z-0"></div>

                <div class="grid grid-cols-4 gap-4 relative z-10">
                    <div
                        v-for="step in steps"
                        :key="step.key"
                        class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col items-center text-center gap-2 transition"
                        :class="{
                            'border-emerald-500 ring-2 ring-emerald-500/20': getStepState(step.key, step.number) === 'current',
                            'border-emerald-200 bg-emerald-50/30': getStepState(step.key, step.number) === 'completed',
                            'opacity-60': getStepState(step.key, step.number) === 'upcoming',
                        }"
                    >
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shadow-xs transition"
                            :class="{
                                'bg-emerald-600 text-white': getStepState(step.key, step.number) === 'completed',
                                'bg-emerald-600 text-white ring-4 ring-emerald-100 animate-pulse': getStepState(step.key, step.number) === 'current',
                                'bg-slate-100 text-slate-500': getStepState(step.key, step.number) === 'upcoming',
                            }"
                        >
                            <span v-if="getStepState(step.key, step.number) === 'completed'">✓</span>
                            <span v-else>{{ step.number }}</span>
                        </div>

                        <div class="space-y-0.5">
                            <div
                                class="text-xs font-bold font-heading"
                                :class="getStepState(step.key, step.number) !== 'upcoming' ? 'text-slate-900' : 'text-slate-500'"
                            >
                                {{ step.label }}
                            </div>
                            <div class="text-[11px] text-slate-500">
                                {{ step.desc }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Vertical Timeline (block sm:hidden) with Flexbox Alignment -->
            <div class="block sm:hidden relative space-y-6">
                <!-- Vertical Line running behind circles -->
                <div class="absolute left-3.5 top-3 bottom-3 w-0.5 bg-slate-200 z-0"></div>

                <div
                    v-for="step in steps"
                    :key="step.key"
                    class="relative z-10 flex items-start gap-4"
                    :class="{
                        'opacity-60': getStepState(step.key, step.number) === 'upcoming'
                    }"
                >
                    <!-- Left Circle Badge (Flexbox shrink-0 prevents clipping) -->
                    <div
                        class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shadow-xs shrink-0 z-10 transition"
                        :class="{
                            'bg-emerald-600 text-white ring-4 ring-white': getStepState(step.key, step.number) === 'completed',
                            'bg-emerald-600 text-white ring-4 ring-emerald-100 animate-pulse': getStepState(step.key, step.number) === 'current',
                            'bg-slate-200 text-slate-600 ring-4 ring-white': getStepState(step.key, step.number) === 'upcoming',
                        }"
                    >
                        <span v-if="getStepState(step.key, step.number) === 'completed'">✓</span>
                        <span v-else>{{ step.number }}</span>
                    </div>

                    <!-- Right Text Details Container -->
                    <div class="flex-1 space-y-0.5 pt-0.5">
                        <div class="text-xs font-bold text-slate-900 font-heading">
                            {{ step.label }}
                        </div>
                        <div class="text-[11px] text-slate-500 leading-snug">
                            {{ step.desc }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
