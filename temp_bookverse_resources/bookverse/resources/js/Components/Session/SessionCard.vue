<script setup lang="ts">
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';

export interface UserSessionItem {
    id: string;
    agent: {
        is_desktop: boolean;
        platform: string;
        browser: string;
    };
    ip_address: string;
    is_current_device: boolean;
    last_active: string;
}

const props = defineProps<{
    session: UserSessionItem;
}>();

const emit = defineEmits<{
    (e: 'revoke', sessionId: string): void;
}>();
</script>

<template>
    <div
        class="p-5 rounded-2xl bg-white border transition duration-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm"
        :class="session.is_current_device ? 'border-sky-500 ring-1 ring-sky-500/20 shadow-sky-500/5' : 'border-slate-200 hover:border-slate-300'"
    >
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600">
                <!-- Desktop Icon -->
                <svg v-if="session.agent.is_desktop" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <!-- Mobile Icon -->
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
            </div>

            <div class="space-y-0.5">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-900 font-heading">
                        {{ session.agent.browser }} on {{ session.agent.platform }}
                    </span>
                    <Badge v-if="session.is_current_device" variant="brand" size="sm" dot>This Device</Badge>
                </div>
                <p class="text-xs text-slate-500 font-mono">
                    {{ session.ip_address }} — <span class="text-slate-700 font-sans">Last active {{ session.last_active }}</span>
                </p>
            </div>
        </div>

        <div v-if="!session.is_current_device">
            <Button
                variant="ghost"
                size="sm"
                class="text-rose-600 hover:text-rose-700 hover:bg-rose-50"
                @click="emit('revoke', session.id)"
            >
                Revoke Session
            </Button>
        </div>
    </div>
</template>
