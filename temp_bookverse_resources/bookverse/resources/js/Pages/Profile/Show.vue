<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Input from '@/Components/UI/Input.vue';
import Textarea from '@/Components/UI/Textarea.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';
import Avatar from '@/Components/UI/Avatar.vue';
import Modal from '@/Components/UI/Modal.vue';
import Toast from '@/Components/UI/Toast.vue';
import PasswordStrengthMeter from '@/Components/Auth/PasswordStrengthMeter.vue';
import AddressCard, { UserAddressItem } from '@/Components/Address/AddressCard.vue';
import AddressFormModal from '@/Components/Address/AddressFormModal.vue';
import SessionCard, { UserSessionItem } from '@/Components/Session/SessionCard.vue';

const props = defineProps<{
    addresses?: UserAddressItem[];
    sessions?: UserSessionItem[];
    mustVerifyEmail?: boolean;
    status?: string;
    divisions?: any[];
    districts?: any[];
    upazilas?: any[];
}>();

const page = usePage();
const user = computed(() => page.props.auth.user as any);

const activeTab = ref<'info' | 'password' | 'addresses' | 'sessions'>('info');
const showToast = ref(false);
const toastMessage = ref('Profile updated successfully');

// Personal Info Form
const infoForm = useForm({
    name: user.value?.name || '',
    email: user.value?.email || '',
    phone: user.value?.phone || '',
    bio: user.value?.bio || '',
});

function updateInfo() {
    infoForm.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            toastMessage.value = 'Personal information updated successfully!';
            showToast.value = true;
        },
    });
}

// Password Form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function updatePassword() {
    passwordForm.put(route('profile.password'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            toastMessage.value = 'Security password updated successfully!';
            showToast.value = true;
        },
    });
}

// Avatar File Upload
const avatarInput = ref<HTMLInputElement | null>(null);
const avatarForm = useForm({
    avatar: null as File | null,
});

function triggerAvatarUpload() {
    avatarInput.value?.click();
}

function handleAvatarChange(e: Event) {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        avatarForm.avatar = target.files[0];
        avatarForm.post(route('profile.avatar'), {
            preserveScroll: true,
            onSuccess: () => {
                toastMessage.value = 'Profile avatar updated!';
                showToast.value = true;
            },
        });
    }
}

// Address Management (Phase 1D)
const showAddressModal = ref(false);
const selectedAddress = ref<UserAddressItem | null>(null);

function openAddAddressModal() {
    selectedAddress.value = null;
    showAddressModal.value = true;
}

function openEditAddressModal(item: UserAddressItem) {
    selectedAddress.value = item;
    showAddressModal.value = true;
}

function handleSetDefaultAddress(item: UserAddressItem) {
    router.patch(route('addresses.default', item.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            toastMessage.value = 'Default address updated!';
            showToast.value = true;
        },
    });
}

function handleDeleteAddress(item: UserAddressItem) {
    if (confirm(`Are you sure you want to delete address "${item.label}"?`)) {
        router.delete(route('addresses.destroy', item.id), {
            preserveScroll: true,
            onSuccess: () => {
                toastMessage.value = 'Address removed successfully!';
                showToast.value = true;
            },
        });
    }
}

// Session Management (Phase 1E)
const showLogoutModal = ref(false);
const logoutOtherForm = useForm({
    password: '',
});

function handleRevokeSession(sessionId: string) {
    if (confirm('Are you sure you want to revoke this browser session?')) {
        router.delete(route('sessions.destroy', sessionId), {
            preserveScroll: true,
            onSuccess: () => {
                toastMessage.value = 'Browser session revoked!';
                showToast.value = true;
            },
        });
    }
}

function logoutOtherDevices() {
    logoutOtherForm.post(route('sessions.other-devices'), {
        preserveScroll: true,
        onSuccess: () => {
            showLogoutModal.value = false;
            logoutOtherForm.reset();
            toastMessage.value = 'Logged out from all other devices!';
            showToast.value = true;
        },
    });
}
</script>

<template>
    <MainLayout>
        <Head title="My Profile — BookVerse" />

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
            <!-- User Header Profile Card -->
            <div class="p-6 lg:p-8 rounded-3xl bg-white border border-slate-200/90 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-sm">
                <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left">
                    <div class="relative group cursor-pointer" @click="triggerAvatarUpload">
                        <Avatar
                            :src="user.avatar_url"
                            :name="user.name"
                            size="xl"
                            status="online"
                        />
                        <div class="absolute inset-0 bg-slate-900/60 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition text-[10px] font-bold text-white">
                            Change
                        </div>
                        <input
                            ref="avatarInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="handleAvatarChange"
                        />
                    </div>

                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                            <h1 class="text-2xl font-bold font-heading text-slate-900">{{ user.name }}</h1>
                            <Badge variant="brand" size="sm" dot>Active Reader</Badge>
                        </div>
                        <p class="text-xs text-slate-500 font-mono">{{ user.email }}</p>
                        <p v-if="user.bio" class="text-xs text-slate-600 italic font-serif max-w-md pt-1">{{ user.bio }}</p>
                    </div>
                </div>

                <Button variant="secondary" size="sm" @click="triggerAvatarUpload" :loading="avatarForm.processing">
                    Upload Avatar
                </Button>
            </div>

            <!-- Tab Navigation Bar -->
            <div class="border-b border-slate-200 flex gap-2 sm:gap-6 overflow-x-auto text-xs font-semibold">
                <button
                    @click="activeTab = 'info'"
                    class="pb-3 border-b-2 transition select-none flex items-center gap-2 whitespace-nowrap"
                    :class="activeTab === 'info' ? 'border-sky-600 text-sky-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Personal Information
                </button>

                <button
                    @click="activeTab = 'password'"
                    class="pb-3 border-b-2 transition select-none flex items-center gap-2 whitespace-nowrap"
                    :class="activeTab === 'password' ? 'border-sky-600 text-sky-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Password & Security
                </button>

                <button
                    @click="activeTab = 'addresses'"
                    class="pb-3 border-b-2 transition select-none flex items-center gap-2 whitespace-nowrap"
                    :class="activeTab === 'addresses' ? 'border-sky-600 text-sky-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    </svg>
                    Saved Addresses ({{ addresses?.length || 0 }}/5)
                </button>

                <button
                    @click="activeTab = 'sessions'"
                    class="pb-3 border-b-2 transition select-none flex items-center gap-2 whitespace-nowrap"
                    :class="activeTab === 'sessions' ? 'border-sky-600 text-sky-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Active Sessions ({{ sessions?.length || 1 }})
                </button>
            </div>

            <!-- Tab Content Panels -->
            <div class="p-6 lg:p-8 rounded-3xl bg-white border border-slate-200/90 shadow-sm">
                <!-- Tab 1: Personal Info -->
                <div v-if="activeTab === 'info'" class="space-y-6 max-w-2xl">
                    <div>
                        <h3 class="text-lg font-bold font-heading text-slate-900">Personal Information</h3>
                        <p class="text-xs text-slate-500">Update your account name, contact email, phone number, and reader bio.</p>
                    </div>

                    <form @submit.prevent="updateInfo" class="space-y-4">
                        <Input
                            v-model="infoForm.name"
                            label="Full Name"
                            placeholder="Your full name"
                            :error="infoForm.errors.name"
                            required
                        />

                        <Input
                            v-model="infoForm.email"
                            label="Email Address"
                            type="email"
                            placeholder="name@example.com"
                            :error="infoForm.errors.email"
                            required
                        />

                        <Input
                            v-model="infoForm.phone"
                            label="Phone Number (Optional)"
                            placeholder="+880 1700-000000"
                            :error="infoForm.errors.phone"
                        />

                        <Textarea
                            v-model="infoForm.bio"
                            label="Short Bio / Literary Interests"
                            placeholder="Tell us what genres or authors you love reading..."
                            :rows="4"
                            :error="infoForm.errors.bio"
                        />

                        <Button type="submit" variant="brand" :loading="infoForm.processing">
                            Save Changes
                        </Button>
                    </form>
                </div>

                <!-- Tab 2: Password & Security -->
                <div v-else-if="activeTab === 'password'" class="space-y-6 max-w-2xl">
                    <div>
                        <h3 class="text-lg font-bold font-heading text-slate-900">Update Password</h3>
                        <p class="text-xs text-slate-500">Ensure your account is using a long, random password to stay secure.</p>
                    </div>

                    <form @submit.prevent="updatePassword" class="space-y-4">
                        <Input
                            v-model="passwordForm.current_password"
                            label="Current Password"
                            type="password"
                            placeholder="Enter current password"
                            :error="passwordForm.errors.current_password"
                            required
                        />

                        <div class="space-y-1">
                            <Input
                                v-model="passwordForm.password"
                                label="New Password"
                                type="password"
                                placeholder="Enter new password"
                                :error="passwordForm.errors.password"
                                required
                            />
                            <PasswordStrengthMeter :password="passwordForm.password" />
                        </div>

                        <Input
                            v-model="passwordForm.password_confirmation"
                            label="Confirm New Password"
                            type="password"
                            placeholder="Re-enter new password"
                            :error="passwordForm.errors.password_confirmation"
                            required
                        />

                        <Button type="submit" variant="primary" :loading="passwordForm.processing">
                            Update Password
                        </Button>
                    </form>
                </div>

                <!-- Tab 3: Saved Addresses (Phase 1D) -->
                <div v-else-if="activeTab === 'addresses'" class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-lg font-bold font-heading text-slate-900">Saved Delivery Addresses</h3>
                            <p class="text-xs text-slate-500">Manage up to 5 shipping addresses for your physical book orders.</p>
                        </div>

                        <Button
                            variant="brand"
                            size="sm"
                            :disabled="(addresses?.length || 0) >= 5"
                            @click="openAddAddressModal"
                        >
                            + Add New Address ({{ addresses?.length || 0 }}/5)
                        </Button>
                    </div>

                    <div v-if="addresses && addresses.length > 0" class="grid md:grid-cols-2 gap-4">
                        <AddressCard
                            v-for="addr in addresses"
                            :key="addr.id"
                            :address="addr"
                            @edit="openEditAddressModal"
                            @delete="handleDeleteAddress"
                            @set-default="handleSetDefaultAddress"
                        />
                    </div>

                    <div v-else class="text-center py-12 space-y-3 bg-slate-50 rounded-2xl border border-slate-200">
                        <p class="text-sm font-semibold text-slate-700">No saved addresses yet.</p>
                        <p class="text-xs text-slate-500">Add a delivery address for easy book checkout.</p>
                        <Button variant="brand" size="sm" @click="openAddAddressModal">
                            Add Delivery Address
                        </Button>
                    </div>
                </div>

                <!-- Tab 4: Active Sessions (Phase 1E) -->
                <div v-else-if="activeTab === 'sessions'" class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-lg font-bold font-heading text-slate-900">Browser Sessions</h3>
                            <p class="text-xs text-slate-500">Manage and revoke active login sessions across your devices.</p>
                        </div>

                        <Button
                            variant="destructive"
                            size="sm"
                            @click="showLogoutModal = true"
                        >
                            Logout Other Browser Devices
                        </Button>
                    </div>

                    <div class="space-y-3">
                        <SessionCard
                            v-for="sess in sessions"
                            :key="sess.id"
                            :session="sess"
                            @revoke="handleRevokeSession"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Logout Other Devices Modal -->
        <Modal
            :show="showLogoutModal"
            title="Log Out Other Browser Sessions"
            maxWidth="md"
            @close="showLogoutModal = false"
        >
            <form @submit.prevent="logoutOtherDevices" class="space-y-4">
                <p class="text-xs text-slate-600">
                    Please enter your password to confirm you would like to log out of your other browser sessions across all devices.
                </p>

                <Input
                    v-model="logoutOtherForm.password"
                    label="Current Password"
                    type="password"
                    placeholder="Enter password to confirm"
                    :error="logoutOtherForm.errors.password"
                    required
                />

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <Button variant="secondary" size="sm" type="button" @click="showLogoutModal = false">Cancel</Button>
                    <Button variant="destructive" size="sm" type="submit" :loading="logoutOtherForm.processing">
                        Confirm & Log Out Other Devices
                    </Button>
                </div>
            </form>
        </Modal>

        <!-- Address Add/Edit Modal -->
        <AddressFormModal
            :show="showAddressModal"
            :address="selectedAddress"
            :divisions="divisions || []"
            :districts="districts || []"
            :upazilas="upazilas || []"
            @close="showAddressModal = false"
            @saved="toastMessage = 'Saved delivery address!'; showToast = true"
        />

        <Toast
            :show="showToast"
            type="success"
            title="Update Successful"
            :message="toastMessage"
            @close="showToast = false"
        />
    </MainLayout>
</template>
