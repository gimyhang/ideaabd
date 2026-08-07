<script setup lang="ts">
import Badge from '@/Components/UI/Badge.vue';
import { usePermission } from '@/Composables/usePermission';

const { can } = usePermission();

export interface CategoryNode {
    id: number;
    name: string;
    slug: string;
    description?: string;
    icon?: string;
    image_url?: string;
    parent_id?: number | null;
    is_active: boolean;
    sort_order: number;
    children_recursive?: CategoryNode[];
}

const props = defineProps<{
    nodes: CategoryNode[];
    level?: number;
}>();

const emit = defineEmits<{
    (e: 'edit', category: CategoryNode): void;
    (e: 'delete', category: CategoryNode): void;
    (e: 'add-sub', parentCategory: CategoryNode): void;
}>();
</script>

<template>
    <ul class="space-y-2.5 font-sans" :class="{ 'pl-6 border-l-2 border-slate-200 dark:border-zinc-800 mt-2.5': (level || 0) > 0 }">
        <li
            v-for="node in nodes"
            :key="node.id"
            class="space-y-2.5 group"
        >
            <div class="p-3.5 rounded-xl border shadow-sm transition flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-white dark:bg-zinc-900/90 border-slate-200/90 dark:border-zinc-800 text-slate-900 dark:text-slate-100">
                <div class="flex items-center gap-3">
                    <!-- Image or initial avatar -->
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs overflow-hidden border border-slate-200 dark:border-zinc-700 shrink-0 bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-200">
                        <img
                            v-if="node.image_url && !node.image_url.includes('ui-avatars.com')"
                            :src="node.image_url"
                            :alt="node.name"
                            class="w-full h-full object-cover"
                        />
                        <span v-else>{{ node.name.charAt(0) }}</span>
                    </div>

                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold font-heading text-slate-900 dark:text-white">{{ node.name }}</span>
                            <span class="text-[10px] font-mono text-slate-400 dark:text-zinc-500">/{{ node.slug }}</span>
                            <Badge v-if="node.is_active" variant="success" size="sm" dot>Active</Badge>
                            <Badge v-else variant="default" size="sm">Inactive</Badge>
                        </div>
                        <p v-if="node.description" class="text-[11px] text-slate-500 dark:text-zinc-400 font-serif line-clamp-1">
                            {{ node.description }}
                        </p>
                    </div>
                </div>

                <div class="inline-flex items-center gap-1.5 self-end sm:self-auto">
                    <!-- Add Sub-category -->
                    <button
                        v-if="can('create-categories')"
                        type="button"
                        @click="emit('add-sub', node)"
                        class="px-2.5 py-1.5 rounded-xl text-xs font-bold transition-all duration-200 border shadow-sm flex items-center gap-1 bg-sky-50 hover:bg-sky-600 text-sky-600 hover:text-white border-sky-200/80 dark:bg-sky-500/10 dark:hover:bg-sky-600 dark:text-sky-400 dark:hover:text-white dark:border-sky-500/20"
                        title="Add Sub-Category"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Add Sub</span>
                    </button>

                    <!-- Edit Category -->
                    <button
                        v-if="can('edit-categories')"
                        type="button"
                        @click="emit('edit', node)"
                        class="p-2 rounded-xl border transition-all duration-200 shadow-sm bg-amber-50 hover:bg-amber-500 text-amber-600 hover:text-white border-amber-200/80 dark:bg-amber-500/10 dark:hover:bg-amber-500 dark:text-amber-400 dark:hover:text-white dark:border-amber-500/20"
                        title="Edit Category"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>

                    <!-- Delete Category -->
                    <button
                        v-if="can('delete-categories')"
                        type="button"
                        @click="emit('delete', node)"
                        class="p-2 rounded-xl border transition-all duration-200 shadow-sm bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border-rose-200/80 dark:bg-rose-500/10 dark:hover:bg-rose-600 dark:text-rose-400 dark:hover:text-white dark:border-rose-500/20"
                        title="Delete Category"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Recursive Child Sub-Tree -->
            <CategoryTree
                v-if="node.children_recursive && node.children_recursive.length > 0"
                :nodes="node.children_recursive"
                :level="(level || 0) + 1"
                @edit="(cat) => emit('edit', cat)"
                @delete="(cat) => emit('delete', cat)"
                @add-sub="(cat) => emit('add-sub', cat)"
            />
        </li>
    </ul>
</template>
