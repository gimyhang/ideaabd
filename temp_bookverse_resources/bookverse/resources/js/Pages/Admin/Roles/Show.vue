<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import { useAdminTheme } from '@/Composables/useAdminTheme';

const { isDark } = useAdminTheme();

interface RoleItem {
    id: number;
    name: string;
    label: string;
    description: string;
    badge: string;
    permissions: string[];
}

type PermissionGroupMap = Record<string, Record<string, string>>;

const props = defineProps<{
    role: RoleItem;
    permissionGroups: PermissionGroupMap;
}>();

function hasPermission(permKey: string): boolean {
    return props.role.permissions.includes(permKey);
}
</script>

<template>
    <Head :title="`Role Details: ${role.name} — Admin Portal`" />

    <AdminLayout>
        <template #header>Role Details</template>

        <div class="max-w-4xl mx-auto space-y-6 font-sans">

            <!-- Breadcrumbs -->
            <div class="flex items-center justify-between">
                <nav class="flex items-center gap-2 text-xs text-slate-400">
                    <Link href="/admin/roles" class="hover:text-sky-500 transition-colors">Roles Management</Link>
                    <span>/</span>
                    <span class="font-bold text-slate-200">{{ role.name }}</span>
                </nav>

                <div class="flex items-center gap-2">
                    <Link :href="`/admin/roles/${role.name}/edit`">
                        <Button variant="brand" size="sm" class="gap-1 font-bold">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            Edit Permissions
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Role Details Summary Card -->
            <div
                class="p-6 sm:p-8 rounded-3xl border space-y-6 transition-colors shadow-sm"
                :class="isDark ? 'bg-zinc-900/90 border-zinc-800' : 'bg-white border-slate-200'"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold font-heading" :class="isDark ? 'text-white' : 'text-slate-900'">
                                {{ role.label }}
                            </h1>
                            <Badge :variant="role.badge as any" size="sm" class="font-bold uppercase">
                                {{ role.name }}
                            </Badge>
                        </div>
                        <p class="text-xs mt-1" :class="isDark ? 'text-slate-400' : 'text-slate-600'">
                            {{ role.description }}
                        </p>
                    </div>

                    <div class="text-right">
                        <div class="text-2xl font-black font-mono text-emerald-500">{{ role.permissions.length }}</div>
                        <div class="text-[11px] text-slate-400 uppercase font-semibold">Granted Permissions</div>
                    </div>
                </div>

                <!-- Granted Permissions Grouped List -->
                <div class="space-y-4 pt-4 border-t" :class="isDark ? 'border-zinc-800' : 'border-slate-100'">
                    <h3 class="text-sm font-bold" :class="isDark ? 'text-white' : 'text-slate-800'">
                        Domain Permissions Breakdown
                    </h3>

                    <div class="space-y-4">
                        <div
                            v-for="(permsMap, groupName) in permissionGroups"
                            :key="groupName"
                            class="p-5 rounded-2xl border space-y-3"
                            :class="isDark ? 'bg-zinc-950/60 border-zinc-800' : 'bg-slate-50/60 border-slate-200/80'"
                        >
                            <h4 class="text-xs font-bold uppercase tracking-wider text-sky-500">
                                {{ groupName }}
                            </h4>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                                <div
                                    v-for="(label, permKey) in permsMap"
                                    :key="permKey"
                                    class="p-2.5 rounded-xl border text-xs flex items-center justify-between"
                                    :class="hasPermission(permKey)
                                        ? (isDark ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400 font-medium' : 'bg-emerald-50 border-emerald-200 text-emerald-800 font-medium')
                                        : (isDark ? 'bg-zinc-900/40 border-zinc-800 text-slate-600 opacity-50' : 'bg-white border-slate-200 text-slate-400 opacity-50')"
                                >
                                    <span class="truncate">{{ label }}</span>
                                    <span v-if="hasPermission(permKey)" class="text-emerald-500 font-bold text-xs ml-2">✓</span>
                                    <span v-else class="text-slate-400 text-xs ml-2">—</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
