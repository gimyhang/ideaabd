<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/UI/Button.vue';
import ConfirmDeleteModal from '@/Components/UI/ConfirmDeleteModal.vue';
import { usePermission } from '@/Composables/usePermission';

const { can } = usePermission();

interface RoleItem {
    id: number;
    name: string;
    label: string;
    description: string;
    badge: string;
    permissions: string[];
}

const props = defineProps<{
    roles: RoleItem[];
}>();

// Delete Modal State
const showDeleteModal = ref(false);
const deletingRole = ref<RoleItem | null>(null);
const isDeleting = ref(false);

const defaultRoles = ['super-admin', 'admin', 'editor', 'writer', 'customer'];

function isDefaultRole(roleName: string): boolean {
    return defaultRoles.includes(roleName.toLowerCase());
}

function promptDeleteRole(role: RoleItem) {
    if (isDefaultRole(role.name)) {
        alert('Default system roles cannot be deleted.');
        return;
    }

    deletingRole.value = role;
    showDeleteModal.value = true;
}

function confirmDeleteRole() {
    if (!deletingRole.value) return;

    isDeleting.value = true;
    router.delete(`/admin/roles/${deletingRole.value.name}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingRole.value = null;
            isDeleting.value = false;
        },
        onError: () => {
            isDeleting.value = false;
        },
    });
}
</script>

<template>
    <Head title="Roles Management — Admin Portal" />

    <AdminLayout>
        <template #header>Roles Management</template>

        <div class="space-y-6 font-sans">
            <!-- Header Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-2xl shadow-sm border transition-colors bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800 text-slate-900 dark:text-white">
                <div>
                    <h1 class="text-2xl font-black font-heading tracking-tight">Roles Management</h1>
                    <p class="text-xs mt-1 text-slate-500 dark:text-zinc-400">
                        Manage system roles and granular permissions for platform access control.
                    </p>
                </div>

                <Link v-if="can('create-roles')" href="/admin/roles/create">
                    <Button variant="brand" size="md" class="font-bold shadow-lg shadow-sky-600/20">
                        <span>Add New Role</span>
                    </Button>
                </Link>
            </div>

            <!-- Roles Table Card -->
            <div class="border rounded-2xl overflow-hidden shadow-sm transition-colors bg-white dark:bg-zinc-900 border-slate-200 dark:border-zinc-800">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b uppercase tracking-wider font-semibold text-[10px] bg-slate-50 dark:bg-zinc-950/60 text-slate-500 dark:text-zinc-400 border-slate-200 dark:border-zinc-800">
                                <th class="py-3.5 px-6 w-1/5">ROLE NAME</th>
                                <th class="py-3.5 px-6">PERMISSIONS</th>
                                <th class="py-3.5 px-6 text-right w-40">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-800 text-slate-700 dark:text-slate-200">
                            <tr
                                v-for="role in roles"
                                :key="role.id"
                                class="hover:bg-slate-50/80 dark:hover:bg-zinc-800/40 transition"
                            >
                                <!-- Role Name -->
                                <td class="py-4 px-6 font-bold text-sm font-heading text-slate-900 dark:text-white">
                                    <Link :href="`/admin/roles/${role.name}`" class="hover:text-sky-600 dark:hover:text-sky-400 transition-colors">
                                        {{ role.name }}
                                    </Link>
                                </td>

                                <!-- Permissions Pills -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <template v-if="role.permissions.length > 0">
                                            <span
                                                v-for="pKey in role.permissions.slice(0, 5)"
                                                :key="pKey"
                                                class="px-2.5 py-1 rounded-lg text-[11px] font-mono font-medium border bg-emerald-50 text-emerald-700 border-emerald-200/80 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20"
                                            >
                                                {{ pKey }}
                                            </span>

                                            <!-- Remaining Count Badge -->
                                            <span
                                                v-if="role.permissions.length > 5"
                                                class="px-2.5 py-1 rounded-lg text-[11px] font-semibold border bg-slate-100 text-slate-600 border-slate-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700"
                                            >
                                                +{{ role.permissions.length - 5 }} more
                                            </span>
                                        </template>

                                        <span v-else class="text-slate-400 dark:text-zinc-500 italic text-[11px]">
                                            No permissions assigned
                                        </span>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="py-4 px-6 text-right">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <!-- View Show Page -->
                                        <Link
                                            v-if="can('view-roles')"
                                            :href="`/admin/roles/${role.name}`"
                                            class="p-2 rounded-xl border transition-all duration-200 shadow-sm bg-sky-50 hover:bg-sky-600 text-sky-600 hover:text-white border-sky-200/80 dark:bg-sky-500/10 dark:hover:bg-sky-600 dark:text-sky-400 dark:hover:text-white dark:border-sky-500/20"
                                            title="View Details"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>

                                        <!-- Edit Page -->
                                        <Link
                                             v-if="can('edit-roles') && role.name !== 'super-admin'"
                                             :href="`/admin/roles/${role.name}/edit`"
                                             class="p-2 rounded-xl border transition-all duration-200 shadow-sm bg-amber-50 hover:bg-amber-500 text-amber-600 hover:text-white border-amber-200/80 dark:bg-amber-500/10 dark:hover:bg-amber-500 dark:text-amber-400 dark:hover:text-white dark:border-amber-500/20"
                                             title="Edit Role Permissions"
                                         >
                                             <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                 <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                             </svg>
                                         </Link>
                                         <span
                                             v-else-if="role.name === 'super-admin'"
                                             class="p-2 rounded-xl border opacity-40 cursor-not-allowed bg-slate-100 text-slate-400 border-slate-200 dark:bg-zinc-800 dark:text-zinc-500 dark:border-zinc-700"
                                             title="Super Admin permissions are protected and cannot be edited"
                                         >
                                             <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                 <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                             </svg>
                                         </span>

                                        <!-- Delete button -->
                                        <button
                                            v-if="can('delete-roles')"
                                            @click="promptDeleteRole(role)"
                                            :disabled="isDefaultRole(role.name)"
                                            class="p-2 rounded-xl border transition-all duration-200 shadow-sm bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border-rose-200/80 dark:bg-rose-500/10 dark:hover:bg-rose-600 dark:text-rose-400 dark:hover:text-white dark:border-rose-500/20 disabled:opacity-30 disabled:cursor-not-allowed"
                                            :title="isDefaultRole(role.name) ? 'Default system roles cannot be deleted' : 'Delete Role'"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reusable Confirm Delete Modal -->
            <ConfirmDeleteModal
                :show="showDeleteModal"
                title="Delete Role?"
                :item-name="deletingRole?.name"
                message="Are you sure you want to delete this role? Users assigned to this role will lose their granted privileges."
                :loading="isDeleting"
                @close="showDeleteModal = false"
                @confirm="confirmDeleteRole"
            />
        </div>
    </AdminLayout>
</template>
