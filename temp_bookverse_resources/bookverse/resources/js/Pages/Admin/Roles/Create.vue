<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Input from '@/Components/UI/Input.vue';
import Textarea from '@/Components/UI/Textarea.vue';
import Button from '@/Components/UI/Button.vue';
import { useAdminTheme } from '@/Composables/useAdminTheme';

const { isDark } = useAdminTheme();

type PermissionGroupMap = Record<string, Record<string, string>>;

const props = defineProps<{
    permissionGroups: PermissionGroupMap;
}>();

const form = useForm({
    name: '',
    description: '',
    permissions: [] as string[],
});

// Toggle single permission checkbox
function togglePermission(permKey: string) {
    const idx = form.permissions.indexOf(permKey);
    if (idx > -1) {
        form.permissions.splice(idx, 1);
    } else {
        form.permissions.push(permKey);
    }
}

// Check if group is fully selected
function isGroupAllSelected(groupName: string): boolean {
    const groupPermKeys = Object.keys(props.permissionGroups[groupName] || {});
    return groupPermKeys.every(k => form.permissions.includes(k));
}

// Toggle select/deselect all for a domain group
function toggleGroupAll(groupName: string) {
    const groupPermKeys = Object.keys(props.permissionGroups[groupName] || {});
    if (isGroupAllSelected(groupName)) {
        form.permissions = form.permissions.filter(k => !groupPermKeys.includes(k));
    } else {
        groupPermKeys.forEach(k => {
            if (!form.permissions.includes(k)) {
                form.permissions.push(k);
            }
        });
    }
}

function submit() {
    form.post('/admin/roles');
}
</script>

<template>
    <Head title="Create New Role — Admin Portal" />

    <AdminLayout>
        <template #header>Create New Role</template>

        <div class="max-w-4xl mx-auto space-y-6 font-sans">

            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs text-slate-400">
                <Link href="/admin/roles" class="hover:text-sky-500 transition-colors">Roles Management</Link>
                <span>/</span>
                <span class="font-bold text-slate-200">Create New Role</span>
            </nav>

            <!-- Card Container -->
            <div
                class="p-6 sm:p-8 rounded-3xl border space-y-6 transition-colors shadow-sm"
                :class="isDark ? 'bg-zinc-900/90 border-zinc-800' : 'bg-white border-slate-200'"
            >
                <form @submit.prevent="submit" class="space-y-6">

                    <!-- Role Name & Description -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <Input
                            v-model="form.name"
                            label="Role Name"
                            placeholder="e.g. moderator, store_manager"
                            required
                        />

                        <Textarea
                            v-model="form.description"
                            label="Role Description"
                            placeholder="Briefly describe what this role can do"
                            :rows="3"
                        />
                    </div>

                    <!-- Permissions Section -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b pb-2" :class="isDark ? 'border-zinc-800' : 'border-slate-100'">
                            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">
                                Permissions
                            </h3>
                            <span class="text-xs font-mono font-bold text-emerald-500">
                                {{ form.permissions.length }} Selected
                            </span>
                        </div>

                        <!-- Permission Groups Accordions / Cards -->
                        <div class="space-y-5">
                            <div
                                v-for="(permsMap, groupName) in permissionGroups"
                                :key="groupName"
                                class="p-5 rounded-2xl border space-y-3 transition-colors"
                                :class="isDark ? 'bg-zinc-950/60 border-zinc-800' : 'bg-slate-50/60 border-slate-200/80'"
                            >
                                <!-- Group Header with Deselect/Select All button -->
                                <div class="flex items-center justify-between border-b pb-2" :class="isDark ? 'border-zinc-800' : 'border-slate-200/60'">
                                    <h4 class="text-xs font-bold uppercase tracking-wider capitalize text-sky-500">
                                        {{ groupName }}
                                    </h4>
                                    <button
                                        type="button"
                                        @click="toggleGroupAll(groupName)"
                                        class="text-xs font-medium text-sky-600 hover:text-sky-500 hover:underline transition-colors"
                                    >
                                        {{ isGroupAllSelected(groupName) ? 'Deselect All' : 'Select All' }}
                                    </button>
                                </div>

                                <!-- Checkbox Grid -->
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 pt-1">
                                    <label
                                        v-for="(label, permKey) in permsMap"
                                        :key="permKey"
                                        class="flex items-center gap-2.5 p-2.5 rounded-xl border text-xs cursor-pointer transition-all select-none"
                                        :class="form.permissions.includes(permKey)
                                            ? (isDark
                                                ? 'bg-sky-500/15 border-sky-500/40 text-white font-medium'
                                                : 'bg-white border-sky-300 text-sky-950 shadow-sm font-medium')
                                            : (isDark
                                                ? 'bg-zinc-900/60 border-zinc-800 text-slate-400 hover:border-zinc-700'
                                                : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-100')"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="permKey"
                                            :checked="form.permissions.includes(permKey)"
                                            @change="togglePermission(permKey)"
                                            class="rounded text-sky-500 focus:ring-sky-500 border-slate-300 dark:border-zinc-700 dark:bg-zinc-800"
                                        />
                                        <span class="truncate capitalize">{{ permKey.replaceAll('-', ' ') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Action Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t" :class="isDark ? 'border-zinc-800' : 'border-slate-100'">
                        <Link href="/admin/roles">
                            <Button
                                variant="secondary"
                                size="md"
                                type="button"
                            >
                                Cancel
                            </Button>
                        </Link>
                        <Button
                            variant="brand"
                            size="md"
                            type="submit"
                            :loading="form.processing"
                            class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6"
                        >
                            Create Role
                        </Button>
                    </div>

                </form>
            </div>

        </div>
    </AdminLayout>
</template>
