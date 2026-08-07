<script setup lang="ts">
import Icon from '@/Components/UI/Icon.vue';

const props = defineProps<{
    type: 'sales' | 'books' | 'writers' | 'users';
    startDate: string;
    endDate: string;
}>();

function triggerDownload() {
    const url = `/admin/reports/export?type=${props.type}&start_date=${props.startDate}&end_date=${props.endDate}`;
    window.location.href = url;
}
</script>

<template>
    <button
        type="button"
        @click="triggerDownload"
        class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-extrabold text-xs rounded-2xl shadow-md shadow-emerald-500/20 hover:shadow-lg hover:shadow-emerald-500/30 active:scale-95 transition-all duration-200 cursor-pointer shrink-0"
    >
        <Icon name="download" class="w-4 h-4 text-white" />
        <span>Export CSV Report</span>
    </button>
</template>
