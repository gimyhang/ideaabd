<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Button from '@/Components/UI/Button.vue';
</script>

<template>
    <Head title="500 - Internal Server Error — BookVerse" />

    <div class="min-h-screen bg-slate-950 flex flex-col items-center justify-center p-4 text-center text-slate-100 font-sans">
        <div class="space-y-6 max-w-md">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-rose-500/10 border border-rose-500/20 text-rose-400 font-extrabold text-3xl font-heading">
                500
            </div>

            <div class="space-y-2">
                <h1 class="text-3xl font-extrabold font-heading text-white">Something Went Wrong</h1>
                <p class="text-xs text-slate-400 font-serif italic">
                    Our engineering team has been notified. Please try refreshing or return home.
                </p>
            </div>

            <Link href="/">
                <Button variant="primary" size="md">Return to BookVerse Home</Button>
            </Link>
        </div>
    </div>
</template>
