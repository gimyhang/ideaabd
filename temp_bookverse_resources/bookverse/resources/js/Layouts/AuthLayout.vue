<script setup lang="ts">
import { onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

onMounted(() => {
    if (typeof document !== 'undefined') {
        document.documentElement.classList.remove('dark');
    }
});
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden font-sans antialiased text-slate-900 selection:bg-sky-600 selection:text-white">
        <!-- Subtle Ambient Soft Background Glows -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-sky-200/40 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-200/40 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Branding Logo -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center space-y-3 z-10">
            <Link href="/" class="inline-flex items-center gap-3 group">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-sky-600/20 ring-1 ring-white group-hover:scale-105 transition transform">
                    <span class="text-2xl font-bold text-white tracking-wider">B</span>
                </div>
            </Link>

            <h2 class="text-2xl font-extrabold font-heading text-slate-900 tracking-tight">
                <slot name="title">BookVerse Portal</slot>
            </h2>
            <p class="text-xs text-slate-500 font-serif italic">
                <slot name="subtitle">Discover Bangladesh's Next-Gen Book & Magazine Ecosystem</slot>
            </p>
        </div>

        <!-- Centered Auth Card -->
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md z-10">
            <div class="bg-white border border-slate-200/90 py-8 px-6 shadow-xl shadow-slate-200/60 rounded-3xl sm:px-10 space-y-6">
                <slot />
            </div>

            <!-- Footer links -->
            <div class="mt-6 text-center text-xs text-slate-500">
                <Link href="/" class="hover:text-slate-700 transition font-medium">← Back to BookVerse Home</Link>
            </div>
        </div>
    </div>
</template>
