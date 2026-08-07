<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';

const scrollProgress = ref(0);

function handleScroll() {
    const totalHeight = document.documentElement.scrollHeight - window.innerHeight;
    if (totalHeight > 0) {
        scrollProgress.value = Math.min(100, Math.max(0, (window.scrollY / totalHeight) * 100));
    }
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div class="fixed top-0 left-0 right-0 h-1.5 bg-transparent z-50 pointer-events-none">
        <div
            class="h-full bg-gradient-to-r from-sky-500 via-indigo-500 to-amber-500 transition-all duration-75 ease-out shadow-xs"
            :style="{ width: `${scrollProgress}%` }"
        ></div>
    </div>
</template>
