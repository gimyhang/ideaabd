<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Badge from '@/Components/UI/Badge.vue';
</script>

<template>
    <Head title="503 - Under Scheduled Maintenance — BookVerse" />

    <div class="min-h-screen bg-slate-950 flex flex-col items-center justify-center p-4 text-center text-slate-100 font-sans">
        <div class="space-y-6 max-w-md">
            <Badge variant="warning" dot>Scheduled Maintenance</Badge>

            <div class="space-y-2">
                <h1 class="text-3xl font-extrabold font-heading text-white">We'll Be Right Back</h1>
                <p class="text-xs text-slate-400 font-serif italic">
                    BookVerse is currently undergoing upgrades to serve you better.
                </p>
            </div>
        </div>
    </div>
</template>
