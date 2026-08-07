<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/UI/Modal.vue';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';
import Checkbox from '@/Components/UI/Checkbox.vue';
import { UserAddressItem } from './AddressCard.vue';

interface LocationItem {
    id: number;
    division_id?: number;
    district_id?: number;
    name: string;
    bn_name: string;
}

const props = withDefaults(
    defineProps<{
        show: boolean;
        address?: UserAddressItem | null;
        divisions?: LocationItem[];
        districts?: LocationItem[];
        upazilas?: LocationItem[];
    }>(),
    {
        show: false,
        address: null,
        divisions: () => [],
        districts: () => [],
        upazilas: () => [],
    }
);

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved'): void;
}>();

const selectedDivisionId = ref<number | string>('');
const selectedDistrictId = ref<number | string>('');
const selectedUpazilaId = ref<number | string>('');

const form = useForm({
    label: 'Home',
    recipient_name: '',
    phone: '',
    address_line_1: '',
    address_line_2: '',
    city: 'Dhaka',
    district: 'Dhaka',
    zip_code: '1200',
    is_default: false,
});

const filteredDistricts = computed(() => {
    if (!selectedDivisionId.value || !props.districts.length) return [];
    return props.districts.filter(d => d.division_id === Number(selectedDivisionId.value));
});

const filteredUpazilas = computed(() => {
    if (!selectedDistrictId.value || !props.upazilas.length) return [];
    return props.upazilas.filter(u => u.district_id === Number(selectedDistrictId.value));
});

function handleDivisionChange() {
    selectedDistrictId.value = '';
    selectedUpazilaId.value = '';
    form.district = '';
    form.city = '';
}

function handleDistrictChange() {
    selectedUpazilaId.value = '';
    const dist = props.districts.find(d => d.id === Number(selectedDistrictId.value));
    if (dist) {
        form.district = dist.name;
    }
    form.city = '';
}

function handleUpazilaChange() {
    const upz = props.upazilas.find(u => u.id === Number(selectedUpazilaId.value));
    if (upz) {
        form.city = upz.name;
    }
}

watch(
    () => props.address,
    (item) => {
        if (item) {
            form.label = item.label;
            form.recipient_name = item.recipient_name;
            form.phone = item.phone;
            form.address_line_1 = item.address_line_1;
            form.address_line_2 = item.address_line_2 || '';
            form.city = item.city;
            form.district = item.district;
            form.zip_code = item.zip_code;
            form.is_default = item.is_default;

            // Pre-select matching division, district, and upazila if editing
            if (props.districts.length) {
                const matchedDist = props.districts.find(
                    d => d.name.toLowerCase() === item.district.toLowerCase() || d.bn_name === item.district
                );
                if (matchedDist) {
                    selectedDivisionId.value = matchedDist.division_id || '';
                    selectedDistrictId.value = matchedDist.id;

                    if (props.upazilas.length) {
                        const matchedUpz = props.upazilas.find(
                            u => u.district_id === matchedDist.id && (u.name.toLowerCase() === item.city.toLowerCase() || u.bn_name === item.city)
                        );
                        if (matchedUpz) {
                            selectedUpazilaId.value = matchedUpz.id;
                        }
                    }
                }
            }
        } else {
            form.reset();
            selectedDivisionId.value = '';
            selectedDistrictId.value = '';
            selectedUpazilaId.value = '';
        }
    },
    { immediate: true }
);

function submit() {
    if (props.address) {
        form.put(route('addresses.update', props.address.id), {
            preserveScroll: true,
            onSuccess: () => {
                emit('saved');
                emit('close');
            },
        });
    } else {
        form.post(route('addresses.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                emit('saved');
                emit('close');
            },
        });
    }
}
</script>

<template>
    <Modal
        :show="show"
        :title="address ? 'Edit Delivery Address' : 'Add New Delivery Address'"
        maxWidth="lg"
        @close="emit('close')"
    >
        <form @submit.prevent="submit" class="space-y-4">
            <!-- Label & Recipient Name -->
            <div class="grid grid-cols-2 gap-4">
                <Input
                    v-model="form.label"
                    label="Address Label"
                    placeholder="e.g. Home, Office"
                    :error="form.errors.label"
                    required
                />

                <Input
                    v-model="form.recipient_name"
                    label="Recipient Name"
                    placeholder="Full name"
                    :error="form.errors.recipient_name"
                    required
                />
            </div>

            <!-- Phone & Zip -->
            <div class="grid grid-cols-2 gap-4">
                <Input
                    v-model="form.phone"
                    label="Phone Number"
                    placeholder="+880 1700-000000"
                    :error="form.errors.phone"
                    required
                />

                <Input
                    v-model="form.zip_code"
                    label="Zip / Postal Code"
                    placeholder="e.g. 1205"
                    :error="form.errors.zip_code"
                    required
                />
            </div>

            <!-- 1-to-1 Cascading Location Dropdowns -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- Division Select -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">বিভাগ <span class="text-rose-500">*</span></label>
                    <select
                        v-model="selectedDivisionId"
                        class="w-full h-10 px-3 text-xs font-medium text-slate-800 rounded-xl border-slate-200 focus:border-sky-500 focus:ring-sky-500 bg-slate-50 cursor-pointer"
                        required
                        @change="handleDivisionChange"
                    >
                        <option value="" disabled>বিভাগ নির্বাচন করুন</option>
                        <option v-for="div in divisions" :key="div.id" :value="div.id">
                            {{ div.bn_name || div.name }}
                        </option>
                    </select>
                </div>

                <!-- District Select -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">জেলা <span class="text-rose-500">*</span></label>
                    <select
                        v-model="selectedDistrictId"
                        :disabled="!selectedDivisionId"
                        class="w-full h-10 px-3 text-xs font-medium text-slate-800 rounded-xl border-slate-200 focus:border-sky-500 focus:ring-sky-500 bg-slate-50 disabled:opacity-50 cursor-pointer"
                        required
                        @change="handleDistrictChange"
                    >
                        <option value="" disabled>জেলা নির্বাচন করুন</option>
                        <option v-for="dist in filteredDistricts" :key="dist.id" :value="dist.id">
                            {{ dist.bn_name || dist.name }}
                        </option>
                    </select>
                </div>

                <!-- Upazila Select -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">থানা / উপজেলা <span class="text-rose-500">*</span></label>
                    <select
                        v-model="selectedUpazilaId"
                        :disabled="!selectedDistrictId"
                        class="w-full h-10 px-3 text-xs font-medium text-slate-800 rounded-xl border-slate-200 focus:border-sky-500 focus:ring-sky-500 bg-slate-50 disabled:opacity-50 cursor-pointer"
                        required
                        @change="handleUpazilaChange"
                    >
                        <option value="" disabled>উপজেলা নির্বাচন করুন</option>
                        <option v-for="upz in filteredUpazilas" :key="upz.id" :value="upz.id">
                            {{ upz.bn_name || upz.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Street Address -->
            <Input
                v-model="form.address_line_1"
                label="Street Address / House / Road"
                placeholder="e.g. House #12, Road #4, Block B"
                :error="form.errors.address_line_1"
                required
            />

            <!-- Landmark -->
            <Input
                v-model="form.address_line_2"
                label="Apartment / Suite / Landmark (Optional)"
                placeholder="e.g. Apt 4A, Near City Bank"
                :error="form.errors.address_line_2"
            />

            <!-- Default Checkbox -->
            <Checkbox
                v-model="form.is_default"
                label="Set as Default Delivery Address"
            />

            <!-- Submit Buttons -->
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                <Button variant="secondary" size="sm" type="button" @click="emit('close')">Cancel</Button>
                <Button variant="brand" size="sm" type="submit" :loading="form.processing">
                    {{ address ? 'Update Address' : 'Save Address' }}
                </Button>
            </div>
        </form>
    </Modal>
</template>
