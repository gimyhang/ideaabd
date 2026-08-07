<script setup lang="ts">
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';

export interface UserAddressItem {
    id: number;
    label: string;
    recipient_name: string;
    phone: string;
    address_line_1: string;
    address_line_2?: string;
    city: string;
    district: string;
    zip_code: string;
    is_default: boolean;
}

const props = defineProps<{
    address: UserAddressItem;
}>();

const emit = defineEmits<{
    (e: 'edit', address: UserAddressItem): void;
    (e: 'delete', address: UserAddressItem): void;
    (e: 'set-default', address: UserAddressItem): void;
}>();
</script>

<template>
    <div
        class="p-5 rounded-2xl bg-white border transition duration-200 space-y-3 relative shadow-sm"
        :class="address.is_default ? 'border-sky-500 ring-1 ring-sky-500/20 shadow-sky-500/5' : 'border-slate-200 hover:border-slate-300'"
    >
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-slate-900 font-heading">{{ address.label }}</span>
                <Badge v-if="address.is_default" variant="brand" size="sm" dot>Default Address</Badge>
            </div>

            <div class="flex items-center gap-2">
                <button
                    @click="emit('edit', address)"
                    class="text-xs font-semibold text-slate-600 hover:text-slate-900 transition px-2 py-1 rounded-md hover:bg-slate-100"
                >
                    Edit
                </button>
                <button
                    @click="emit('delete', address)"
                    class="text-xs font-semibold text-rose-600 hover:text-rose-700 transition px-2 py-1 rounded-md hover:bg-slate-100"
                >
                    Delete
                </button>
            </div>
        </div>

        <div class="text-xs text-slate-700 space-y-1">
            <p class="font-semibold text-slate-900">{{ address.recipient_name }} <span class="text-slate-500 font-normal">({{ address.phone }})</span></p>
            <p>{{ address.address_line_1 }} <span v-if="address.address_line_2">, {{ address.address_line_2 }}</span></p>
            <p class="text-slate-500">{{ address.city }}, {{ address.district }} — {{ address.zip_code }}</p>
        </div>

        <div v-if="!address.is_default" class="pt-2 border-t border-slate-100 flex justify-end">
            <Button variant="ghost" size="sm" @click="emit('set-default', address)">
                Set as Default
            </Button>
        </div>
    </div>
</template>
