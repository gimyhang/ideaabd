<script setup lang="ts">
import { onMounted } from 'vue';
import Navbar from '@/Components/Layout/Navbar.vue';
import Footer from '@/Components/Layout/Footer.vue';
import MobileBottomNav from '@/Components/Layout/MobileBottomNav.vue';

onMounted(() => {
    if (typeof document !== 'undefined') {
        document.documentElement.classList.remove('dark');
    }
});
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-900 flex flex-col font-sans antialiased selection:bg-sky-600 selection:text-white">
        <!-- Main Top Navigation -->
        <Navbar />

        <!-- Main Content View -->
        <main class="flex-1 pb-16 md:pb-0">
            <slot />
        </main>

        <!-- Footer -->
        <Footer />

        <!-- Mobile Sticky Bottom Navigation -->
        <MobileBottomNav />
    </div>
</template>
