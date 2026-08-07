<script setup lang="ts">
import { ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Icon from '@/Components/UI/Icon.vue';

export interface MenuItem {
    label: string;
    href?: string;
    icon?: string;
    badge?: string;
    items?: MenuItem[];
    permission?: string | null;
}

const props = withDefaults(
    defineProps<{
        title?: string;
        items: MenuItem[];
    }>(),
    { title: 'BookVerse Admin' }
);

const page = usePage();
const isCollapsed = ref(false);
const openParents = ref<Record<string, boolean>>({});

const getIconColorClass = (iconName?: string): string => {
    switch (iconName) {
        case 'dashboard': return 'text-sky-500';
        case 'books': return 'text-indigo-500';
        case 'orders': return 'text-purple-500';
        case 'coupons': return 'text-amber-500';
        case 'submissions': return 'text-emerald-500';
        case 'users': return 'text-cyan-500';
        case 'settings': return 'text-slate-400';
        case 'reports': return 'text-pink-500';
        case 'homepage': return 'text-amber-500';
        case 'shipping': return 'text-teal-500';
        case 'payments': return 'text-emerald-500';
        case 'seo': return 'text-sky-500';
        case 'notifications': return 'text-rose-500';
        default: return 'text-sky-500';
    }
};

const isActive = (href?: string): boolean => {
    if (!href) return false;
    if (href === '/admin') return page.url === '/admin' || page.url === '/admin/';
    if (href.includes('?')) {
        const [path, query] = href.split('?');
        if (page.url === href) return true;
        if (page.url.startsWith(path) && page.url.includes(query)) return true;
        return false;
    }
    return page.url.startsWith(href);
};

const isParentActive = (subItems?: MenuItem[]): boolean => {
    if (!subItems) return false;
    return subItems.some(sub => isActive(sub.href));
};

watch(
    () => page.url,
    () => {
        for (const item of props.items) {
            if (item.items && isParentActive(item.items)) {
                openParents.value[item.label] = true;
            } else if (openParents.value[item.label] === undefined) {
                openParents.value[item.label] = false;
            }
        }
    },
    { immediate: true }
);

const toggleParent = (label: string) => {
    if (isCollapsed.value) {
        isCollapsed.value = false;
        openParents.value[label] = true;
    } else {
        openParents.value[label] = !openParents.value[label];
    }
};
</script>

<template>
    <aside
        class="transition-all duration-300 min-h-screen flex flex-col justify-between border-r border-slate-200/80 dark:border-zinc-900 bg-white dark:bg-zinc-950/90 text-slate-800 dark:text-slate-200 sticky top-0 z-30 select-none shadow-xs"
        :class="isCollapsed ? 'w-20' : 'w-64'"
    >
        <div class="p-4 space-y-6">
            <!-- Header Logo -->
            <div class="flex px-1" :class="isCollapsed ? 'flex-col items-center gap-3' : 'items-center justify-between'">
                <Link href="/admin" v-if="!isCollapsed" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-400 to-indigo-600 flex items-center justify-center font-black text-white text-base shadow-md shadow-sky-500/20">
                        B
                    </div>
                    <div>
                        <span class="font-black text-base block leading-tight tracking-tight text-slate-900 dark:text-white">
                            BookVerse
                        </span>
                        <span class="text-[10px] font-black text-sky-600 dark:text-sky-400 tracking-wider uppercase">Admin Portal</span>
                    </div>
                </Link>

                <div v-else class="mx-auto">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-400 to-indigo-600 flex items-center justify-center font-black text-white text-base shadow-md shadow-sky-500/20">
                        B
                    </div>
                </div>

                <button
                    @click="isCollapsed = !isCollapsed"
                    class="p-1.5 rounded-xl transition-all hover:bg-slate-100 dark:hover:bg-zinc-900 text-slate-400 hover:text-slate-600 dark:text-zinc-500 dark:hover:text-zinc-300"
                >
                    <Icon :name="isCollapsed ? 'panelLeftOpen' : 'panelLeftClose'" class="w-5 h-5" />
                </button>
            </div>

            <!-- Navigation Tree -->
            <nav class="space-y-1">
                <div v-for="group in props.items" :key="group.label" class="space-y-0.5">
                    
                    <!-- Single Direct Menu Item (e.g. Dashboard) -->
                    <Link
                        v-if="!group.items"
                        :href="group.href || '#'"
                        class="flex items-center rounded-2xl text-sm font-bold transition-all duration-200 border"
                        :class="[
                            isCollapsed ? 'justify-center px-2 py-3' : 'gap-3.5 px-4 py-3',
                            isActive(group.href)
                                ? 'bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-400 border-sky-200/80 dark:border-sky-500/20 font-black shadow-xs'
                                : 'text-slate-700 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-zinc-900/60 border-transparent'
                        ]"
                    >
                        <Icon :name="group.icon || 'dashboard'" class="w-5 h-5 flex-shrink-0" :class="getIconColorClass(group.icon)" />
                        <span v-if="!isCollapsed" class="truncate flex-1 font-bold text-[13.5px]">{{ group.label }}</span>
                    </Link>

                    <!-- Parent Menu Item with Dropdown Submenu -->
                    <template v-else>
                        <button
                            type="button"
                            @click="toggleParent(group.label)"
                            class="w-full flex items-center rounded-2xl text-sm font-bold transition-all duration-200 border"
                            :class="[
                                isCollapsed ? 'justify-center px-2 py-3' : 'justify-between px-4 py-3',
                                isParentActive(group.items)
                                    ? 'bg-slate-100/80 dark:bg-zinc-900/90 text-slate-900 dark:text-sky-400 border-slate-200/80 dark:border-zinc-800'
                                    : 'text-slate-700 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-zinc-900/50 border-transparent'
                            ]"
                        >
                            <div class="flex items-center truncate" :class="isCollapsed ? 'justify-center' : 'gap-3.5'">
                                <Icon :name="group.icon || 'dashboard'" class="w-5 h-5 flex-shrink-0" :class="getIconColorClass(group.icon)" />
                                <span v-if="!isCollapsed" class="truncate font-bold text-[13.5px] tracking-tight">{{ group.label }}</span>
                            </div>

                            <Icon
                                name="chevron"
                                v-if="!isCollapsed"
                                class="w-4 h-4 transition-transform duration-200"
                                :class="openParents[group.label] ? 'rotate-180 text-sky-600 dark:text-sky-500' : 'text-slate-400 dark:text-zinc-500'"
                            />
                        </button>

                        <!-- Indented Submenu Items -->
                        <div
                            v-show="!isCollapsed && openParents[group.label]"
                            class="border-l-2 ml-5 pl-3.5 space-y-1 my-1 transition-all duration-200 border-slate-200 dark:border-zinc-800"
                        >
                            <Link
                                v-for="sub in group.items"
                                :key="sub.href"
                                :href="sub.href || '#'"
                                class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-[13px] font-bold transition-all duration-200 border"
                                :class="[
                                    isActive(sub.href)
                                        ? 'bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-400 border-sky-200/80 dark:border-sky-500/20 font-black'
                                        : 'text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-100 hover:bg-slate-50 dark:hover:bg-zinc-900/60 border-transparent'
                                ]"
                            >
                                <Icon :name="sub.icon || 'dashboard'" class="w-4 h-4 flex-shrink-0" :class="isActive(sub.href) ? 'text-sky-600 dark:text-sky-500' : getIconColorClass(sub.icon)" />

                                <span v-if="!isCollapsed" class="truncate flex-1 font-bold text-[13px]">{{ sub.label }}</span>
                            </Link>
                        </div>
                    </template>
                </div>
            </nav>
        </div>

        <!-- Footer Link -->
        <div class="p-4 border-t border-slate-200/80 dark:border-zinc-800">
            <Link
                href="/"
                target="_blank"
                class="flex items-center rounded-2xl text-xs font-semibold transition-all duration-200 text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-zinc-900"
                :class="isCollapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3.5 py-2.5'"
            >
                <svg class="w-4 h-4 text-sky-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span v-if="!isCollapsed" class="truncate font-bold">Live Store</span>
            </Link>
        </div>
    </aside>
</template>
