<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermission } from '@/Composables/usePermission';

const { can } = usePermission();
import Avatar from '@/Components/UI/Avatar.vue';
import Badge from '@/Components/UI/Badge.vue';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';
import ConfirmDeleteModal from '@/Components/UI/ConfirmDeleteModal.vue';
import Pagination from '@/Components/UI/Pagination.vue';

interface Role {
    id: number;
    name: string;
    guard_name: string;
}

interface UserItem {
    id: number;
    name: string;
    email: string;
    phone?: string;
    status: 'active' | 'suspended';
    email_verified_at?: string;
    avatar_url: string;
    created_at: string;
    roles: Role[];
    has_writer?: boolean;
    writer_status?: string | null;
}

interface PaginatedUsers {
    data: UserItem[];
    links: any[];
    current_page: number;
    last_page: number;
    total: number;
}

interface RolesCount {
    all: number;
    super_admin: number;
    admin: number;
    customer: number;
    writer: number;
}

const props = defineProps<{
    users: PaginatedUsers;
    filters: {
        search?: string;
        role?: string;
    };
    rolesCount: RolesCount;
}>();

const page = usePage();
const authUserId = page.props.auth?.user?.id;

const search = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || '');

// Debounced filter trigger
let searchTimeout: any = null;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

function selectRoleFilter(roleKey: string) {
    selectedRole.value = roleKey;
    applyFilters();
}

function applyFilters() {
    router.get(
        '/admin/users',
        {
            search: search.value || undefined,
            role: selectedRole.value || undefined,
        },
        { preserveState: true, replace: true }
    );
}

// User Create / Edit Modal State
const showUserModal = ref(false);
const isEditing = ref(false);
const editingUserId = ref<number | null>(null);

const userForm = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    role: 'customer',
    status: 'active',
});

function openCreateUserModal() {
    isEditing.value = false;
    editingUserId.value = null;
    userForm.reset();
    userForm.clearErrors();
    userForm.role = 'customer';
    userForm.status = 'active';
    showUserModal.value = true;
}

function openEditUserModal(user: UserItem) {
    isEditing.value = true;
    editingUserId.value = user.id;
    userForm.clearErrors();
    userForm.name = user.name;
    userForm.email = user.email;
    userForm.phone = user.phone || '';
    userForm.password = '';
    userForm.role = user.roles && user.roles.length > 0 ? user.roles[0].name : 'customer';
    userForm.status = user.status;
    showUserModal.value = true;
}

function submitUserForm() {
    if (isEditing.value && editingUserId.value) {
        userForm.put(`/admin/users/${editingUserId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                showUserModal.value = false;
                userForm.reset();
            },
        });
    } else {
        userForm.post('/admin/users', {
            preserveScroll: true,
            onSuccess: () => {
                showUserModal.value = false;
                userForm.reset();
            },
        });
    }
}

// User Details (Show) Modal State
const showDetailsModal = ref(false);
const selectedUserForShow = ref<UserItem | null>(null);

function openShowUserModal(user: UserItem) {
    selectedUserForShow.value = user;
    showDetailsModal.value = true;
}

// Delete User Modal State
const showDeleteModal = ref(false);
const userToDelete = ref<UserItem | null>(null);

function promptDeleteUser(user: UserItem) {
    userToDelete.value = user;
    showDeleteModal.value = true;
}

function confirmDeleteUser() {
    if (!userToDelete.value) return;

    router.delete(`/admin/users/${userToDelete.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            showDeleteModal.value = false;
            userToDelete.value = null;
        },
    });
}

function roleBadgeVariant(roleName: string) {
    switch (roleName) {
        case 'super-admin':
            return 'error';
        case 'admin':
            return 'brand';
        case 'writer':
            return 'warning';
        default:
            return 'default';
    }
}
</script>

<template>
    <AdminLayout>
        <template #header>User Management</template>

        <div class="space-y-6 font-sans">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-3xl shadow-xs border transition-colors bg-white dark:bg-zinc-950 border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white">
                <div>
                    <h1 class="text-xl font-extrabold font-heading tracking-tight">User Accounts Management</h1>
                    <p class="text-xs mt-0.5 text-slate-500 dark:text-zinc-400">
                        Create new users, update profile details, assign RBAC roles, or remove accounts.
                    </p>
                </div>

                <Button v-if="can('create-users')" variant="brand" size="sm" @click="openCreateUserModal" class="shrink-0">
                    + Add New User
                </Button>
            </div>

            <!-- Role Filter Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                <button
                    @click="selectRoleFilter('')"
                    class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all border cursor-pointer"
                    :class="selectedRole === ''
                        ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 border-slate-900 dark:border-white font-bold'
                        : 'bg-white dark:bg-zinc-950 border-slate-200 dark:border-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-900'"
                >
                    All Users <span class="ml-1 opacity-70">({{ rolesCount.all }})</span>
                </button>

                <button
                    @click="selectRoleFilter('super-admin')"
                    class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all border cursor-pointer"
                    :class="selectedRole === 'super-admin'
                        ? 'bg-sky-600 text-white border-sky-600 shadow-xs font-bold'
                        : 'bg-white dark:bg-zinc-950 border-slate-200 dark:border-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-900'"
                >
                    🛡️ Super Admin <span class="ml-1 opacity-70">({{ rolesCount.super_admin }})</span>
                </button>

                <button
                    @click="selectRoleFilter('admin')"
                    class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all border cursor-pointer"
                    :class="selectedRole === 'admin'
                        ? 'bg-sky-600 text-white border-sky-600 shadow-xs font-bold'
                        : 'bg-white dark:bg-zinc-950 border-slate-200 dark:border-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-900'"
                >
                    ⚡ Admin <span class="ml-1 opacity-70">({{ rolesCount.admin }})</span>
                </button>

                <button
                    @click="selectRoleFilter('writer')"
                    class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all border cursor-pointer"
                    :class="selectedRole === 'writer'
                        ? 'bg-amber-500 text-slate-950 border-amber-500 shadow-xs font-bold'
                        : 'bg-white dark:bg-zinc-950 border-slate-200 dark:border-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-900'"
                >
                    ✍️ Writer <span class="ml-1 opacity-70">({{ rolesCount.writer }})</span>
                </button>

                <button
                    @click="selectRoleFilter('customer')"
                    class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all border cursor-pointer"
                    :class="selectedRole === 'customer'
                        ? 'bg-sky-600 text-white border-sky-600 shadow-xs font-bold'
                        : 'bg-white dark:bg-zinc-950 border-slate-200 dark:border-zinc-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-900'"
                >
                    👤 Customer <span class="ml-1 opacity-70">({{ rolesCount.customer }})</span>
                </button>
            </div>

            <!-- Users Table Card -->
            <div class="border rounded-3xl overflow-hidden shadow-xs transition-colors bg-white dark:bg-zinc-950 border-slate-200 dark:border-zinc-800 space-y-4 p-5">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-slate-100 dark:border-zinc-900 pb-4">
                    <div class="w-full sm:w-80">
                        <Input
                            v-model="search"
                            placeholder="Search by name, email, phone..."
                        />
                    </div>

                    <span class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Total Users: {{ users.total }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b uppercase tracking-wider font-semibold text-[10px] bg-slate-50 dark:bg-zinc-900 text-slate-500 dark:text-zinc-400 border-slate-200 dark:border-zinc-800">
                                <th class="py-3.5 px-5">User</th>
                                <th class="py-3.5 px-5">Assigned Roles</th>
                                <th class="py-3.5 px-5">Contact</th>
                                <th class="py-3.5 px-5">Verified</th>
                                <th class="py-3.5 px-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-900 text-slate-700 dark:text-slate-200">
                            <tr
                                v-for="u in users.data"
                                :key="u.id"
                                class="hover:bg-slate-50/80 dark:hover:bg-zinc-900/50 transition"
                            >
                                <!-- User Column -->
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-3">
                                        <Avatar :name="u.name" size="md" />
                                        <div>
                                            <div class="font-bold text-sm text-slate-900 dark:text-white">
                                                {{ u.name }}
                                            </div>
                                            <div class="text-[11px] text-slate-500 dark:text-zinc-400">
                                                {{ u.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Roles Column -->
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <Badge
                                            v-for="role in u.roles"
                                            :key="role.id"
                                            :variant="roleBadgeVariant(role.name)"
                                            size="sm"
                                            class="font-bold capitalize"
                                        >
                                            {{ role.name }}
                                        </Badge>
                                        <span v-if="!u.roles || u.roles.length === 0" class="text-slate-400 italic text-[11px]">
                                            No role
                                        </span>
                                    </div>
                                </td>

                                <!-- Contact Column -->
                                <td class="py-3.5 px-5">
                                    <span class="font-mono text-[11px] text-slate-600 dark:text-zinc-300">
                                        {{ u.phone || '—' }}
                                    </span>
                                </td>

                                <!-- Email Verified Column -->
                                <td class="py-3.5 px-5">
                                    <span v-if="u.email_verified_at" class="text-emerald-600 dark:text-emerald-400 font-semibold text-[11px] flex items-center gap-1">
                                        ✓ Verified
                                    </span>
                                    <span v-else class="text-amber-600 dark:text-amber-400 font-semibold text-[11px]">
                                        Pending
                                    </span>
                                </td>

                                <!-- Action Buttons: Show, Edit, Delete -->
                                <td class="py-3.5 px-5 text-right whitespace-nowrap space-x-2">
                                    <!-- Show Button -->
                                    <button
                                        v-if="can('view-users')"
                                        @click="openShowUserModal(u)"
                                        class="px-2.5 py-1.5 rounded-xl text-[11px] font-bold bg-sky-50 dark:bg-sky-950/30 text-sky-600 dark:text-sky-400 hover:bg-sky-600 hover:text-white border border-sky-200 dark:border-sky-800 transition cursor-pointer"
                                    >
                                        👁️ Show
                                    </button>

                                    <!-- Edit Button -->
                                    <button
                                        v-if="can('edit-users')"
                                        @click="openEditUserModal(u)"
                                        class="px-2.5 py-1.5 rounded-xl text-[11px] font-bold bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-zinc-700 transition cursor-pointer"
                                    >
                                        ✏️ Edit
                                    </button>

                                    <!-- Delete Button -->
                                    <button
                                        v-if="u.id !== authUserId && can('delete-users')"
                                        @click="promptDeleteUser(u)"
                                        class="px-2.5 py-1.5 rounded-xl text-[11px] font-bold bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 dark:bg-rose-950/30 dark:border-rose-900 dark:text-rose-400 transition cursor-pointer"
                                    >
                                        🗑️ Delete
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="py-8 px-5 text-center text-slate-400 text-xs">
                                    No user accounts found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination footer -->
                <div v-if="users.links.length > 3" class="pt-3 border-t border-slate-100 dark:border-zinc-900">
                    <Pagination :links="users.links" />
                </div>
            </div>

            <!-- Create / Edit User Modal -->
            <Modal
                :show="showUserModal"
                :title="isEditing ? 'Edit User Account' : 'Create New User Account'"
                maxWidth="md"
                @close="showUserModal = false"
            >
                <form @submit.prevent="submitUserForm" class="space-y-4 font-sans">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Full Name <span class="text-rose-500">*</span>
                        </label>
                        <Input
                            v-model="userForm.name"
                            placeholder="e.g. Shafiqul Islam"
                            required
                        />
                        <p v-if="userForm.errors.name" class="text-xs text-rose-500 mt-1">{{ userForm.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Email Address <span class="text-rose-500">*</span>
                        </label>
                        <Input
                            v-model="userForm.email"
                            type="email"
                            placeholder="user@example.com"
                            required
                        />
                        <p v-if="userForm.errors.email" class="text-xs text-rose-500 mt-1">{{ userForm.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Phone Number
                        </label>
                        <Input
                            v-model="userForm.phone"
                            placeholder="+880 1700-000000"
                        />
                        <p v-if="userForm.errors.phone" class="text-xs text-rose-500 mt-1">{{ userForm.errors.phone }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Assigned Role <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="userForm.role"
                            class="w-full text-xs rounded-xl border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-900 text-slate-900 dark:text-white px-3 py-2.5 focus:ring-2 focus:ring-sky-500"
                        >
                            <option value="customer">Customer</option>
                            <option value="writer">Writer</option>
                            <option value="admin">Admin</option>
                            <option value="super-admin">Super Admin</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Password {{ isEditing ? '(Leave blank to keep unchanged)' : '*' }}
                        </label>
                        <Input
                            v-model="userForm.password"
                            type="password"
                            placeholder="••••••••"
                            :required="!isEditing"
                        />
                        <p v-if="userForm.errors.password" class="text-xs text-rose-500 mt-1">{{ userForm.errors.password }}</p>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-zinc-800">
                        <Button variant="secondary" size="sm" type="button" @click="showUserModal = false">Cancel</Button>
                        <Button variant="brand" size="sm" type="submit" :loading="userForm.processing">
                            {{ isEditing ? 'Save Changes' : 'Create User' }}
                        </Button>
                    </div>
                </form>
            </Modal>

            <!-- Show User Details Modal -->
            <Modal
                :show="showDetailsModal"
                title="User Profile Details"
                maxWidth="md"
                @close="showDetailsModal = false"
            >
                <div v-if="selectedUserForShow" class="space-y-4 font-sans text-xs">
                    <!-- Profile Card Banner -->
                    <div class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800">
                        <Avatar :name="selectedUserForShow.name" size="lg" />
                        <div>
                            <h3 class="font-extrabold text-base text-slate-900 dark:text-white">
                                {{ selectedUserForShow.name }}
                            </h3>
                            <p class="text-slate-500 text-xs">{{ selectedUserForShow.email }}</p>
                        </div>
                    </div>

                    <!-- Meta Grid -->
                    <div class="grid grid-cols-2 gap-3 text-slate-700 dark:text-slate-300">
                        <div class="p-3 rounded-xl bg-white dark:bg-zinc-950 border border-slate-100 dark:border-zinc-900">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Phone Number</span>
                            <span class="font-mono font-semibold">{{ selectedUserForShow.phone || 'Not provided' }}</span>
                        </div>

                        <div class="p-3 rounded-xl bg-white dark:bg-zinc-950 border border-slate-100 dark:border-zinc-900">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Email Verified</span>
                            <span class="font-semibold" :class="selectedUserForShow.email_verified_at ? 'text-emerald-600' : 'text-amber-600'">
                                {{ selectedUserForShow.email_verified_at ? '✓ Verified' : 'Pending' }}
                            </span>
                        </div>
                    </div>

                    <!-- Assigned Roles -->
                    <div class="p-3 rounded-xl bg-white dark:bg-zinc-950 border border-slate-100 dark:border-zinc-900 space-y-1">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block">Assigned Platform Roles</span>
                        <div class="flex items-center gap-1.5 flex-wrap pt-1">
                            <Badge
                                v-for="role in selectedUserForShow.roles"
                                :key="role.id"
                                :variant="roleBadgeVariant(role.name)"
                                size="sm"
                                class="font-bold capitalize"
                            >
                                {{ role.name }}
                            </Badge>
                        </div>
                    </div>

                    <div class="flex justify-end pt-3 border-t border-slate-200 dark:border-zinc-800">
                        <Button variant="secondary" size="sm" type="button" @click="showDetailsModal = false">Close</Button>
                    </div>
                </div>
            </Modal>

            <!-- Delete Confirmation Modal -->
            <ConfirmDeleteModal
                :show="showDeleteModal"
                title="Delete User Account"
                :item-name="userToDelete?.name"
                message="Are you sure you want to permanently delete this user account? This action cannot be undone."
                @confirm="confirmDeleteUser"
                @close="showDeleteModal = false"
            />
        </div>
    </AdminLayout>
</template>
