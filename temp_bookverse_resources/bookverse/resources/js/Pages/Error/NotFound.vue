<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Button from '@/Components/UI/Button.vue';
</script>

<template>
    <Head title="404 - Page Not Found — BookVerse" />

    <div class="min-h-screen bg-slate-950 flex flex-col items-center justify-center p-4 text-center text-slate-100 font-sans">
        <div class="space-y-6 max-w-md">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-sky-500/10 border border-sky-500/20 text-sky-400 font-extrabold text-3xl font-heading">
                404
            </div>

            <div class="space-y-2">
                <h1 class="text-3xl font-extrabold font-heading text-white">Page Not Found</h1>
                <p class="text-xs text-slate-400 font-serif italic">
                    "The story you are looking for has either been moved or never written."
                </p>
            </div>

            <Link href="/">
                <Button variant="brand" size="md">Return to BookVerse Home</Button>
            </Link>
        </div>
    </div>
</template>
