<script setup lang="ts">
import { computed } from 'vue';
import {
    LayoutDashboard,
    BookOpen,
    Tags,
    Feather,
    Building2,
    ShoppingBag,
    Undo2,
    MessageSquare,
    Ticket,
    FileText,
    Users,
    ShieldCheck,
    Settings,
    ChevronDown,
    PanelLeftClose,
    PanelLeftOpen,
    Menu,
    TrendingUp,
    Calendar,
    Download
} from 'lucide-vue-next';

const props = withDefaults(
    defineProps<{
        name: string;
        size?: string | number;
    }>(),
    { size: 20 }
);

const iconMap: Record<string, any> = {
    dashboard: LayoutDashboard,
    books: BookOpen,
    categories: Tags,
    authors: Feather,
    publishers: Building2,
    orders: ShoppingBag,
    returns: Undo2,
    reviews: MessageSquare,
    coupons: Ticket,
    submissions: FileText,
    comments: MessageSquare,
    users: Users,
    writers: Feather,
    roles: ShieldCheck,
    settings: Settings,
    chevron: ChevronDown,
    panelLeftClose: PanelLeftClose,
    panelLeftOpen: PanelLeftOpen,
    menu: Menu,
    reports: TrendingUp,
    calendar: Calendar,
    download: Download,
};

const currentIcon = computed(() => iconMap[props.name] || LayoutDashboard);
</script>

<template>
    <component :is="currentIcon" :size="size" class="inline-block flex-shrink-0" />
</template>
