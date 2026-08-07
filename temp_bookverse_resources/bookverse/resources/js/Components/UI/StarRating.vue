<script setup lang="ts">
const props = defineProps<{
    rating: number;
    size?: 'sm' | 'md' | 'lg';
}>();

const sizes = {
    sm: 'w-3 h-3',
    md: 'w-4 h-4',
    lg: 'w-6 h-6',
};

const sizeClass = props.size ? sizes[props.size] : sizes['md'];

function starType(index: number): 'full' | 'half' | 'empty' {
    const r = props.rating;
    if (r >= index) return 'full';
    if (r >= index - 0.5) return 'half';
    return 'empty';
}
</script>

<template>
    <div class="flex items-center gap-0.5">
        <svg
            v-for="i in 5"
            :key="i"
            :class="sizeClass"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
        >
            <!-- Full star -->
            <template v-if="starType(i) === 'full'">
                <polygon
                    points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"
                    fill="#F59E0B"
                    stroke="#F59E0B"
                    stroke-width="1.5"
                    stroke-linejoin="round"
                />
            </template>
            <!-- Half star -->
            <template v-else-if="starType(i) === 'half'">
                <defs>
                    <linearGradient :id="`half-${i}`">
                        <stop offset="50%" stop-color="#F59E0B" />
                        <stop offset="50%" stop-color="#D1D5DB" />
                    </linearGradient>
                </defs>
                <polygon
                    points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"
                    :fill="`url(#half-${i})`"
                    stroke="#F59E0B"
                    stroke-width="1.5"
                    stroke-linejoin="round"
                />
            </template>
            <!-- Empty star -->
            <template v-else>
                <polygon
                    points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"
                    fill="#D1D5DB"
                    stroke="#D1D5DB"
                    stroke-width="1.5"
                    stroke-linejoin="round"
                />
            </template>
        </svg>
    </div>
</template>
