<script setup lang="ts">
import Sidebar, { type MenuItem } from '@/Components/Layout/Sidebar.vue';

type NavItem = MenuItem;
import Avatar from '@/Components/UI/Avatar.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const writerNavItems: NavItem[] = [
    { label: 'Writer Dashboard', href: '/writer/dashboard' },
    { label: 'My Submissions', href: '/writer/submissions' },
    { label: 'New Article Draft', href: '/writer/editor', badge: 'Tiptap' },
    { label: 'Reading Stats', href: '/writer/stats' },
    { label: 'Writer Profile', href: '/writer/profile' },
];
</script>

<template>
    <div class="min-h-screen flex bg-slate-950 text-slate-100 font-sans antialiased">
        <!-- Writer Sidebar -->
        <Sidebar title="Writer Workspace" :items="writerNavItems" />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Topbar -->
            <header class="h-16 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 px-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold text-indigo-400 uppercase tracking-wider bg-indigo-500/10 border border-indigo-500/20 px-2 py-0.5 rounded-full">
                        Writer & Editor Studio
                    </span>
                    <h2 class="text-sm font-bold text-white font-heading"><slot name="header">My Desk</slot></h2>
                </div>

                <div class="flex items-center gap-4 text-xs font-medium text-slate-300">
                    <span>{{ page.props.auth?.user?.name || 'Writer' }}</span>
                    <Avatar :name="page.props.auth?.user?.name || 'Writer'" size="sm" status="online" />
                </div>
            </header>

            <!-- Scrollable Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
