import { ref } from 'vue';

const isCartDrawerOpen = ref(false);

export function useCartDrawer() {
    function openCartDrawer() {
        isCartDrawerOpen.value = true;
    }

    function closeCartDrawer() {
        isCartDrawerOpen.value = false;
    }

    function toggleCartDrawer() {
        isCartDrawerOpen.value = !isCartDrawerOpen.value;
    }

    return {
        isCartDrawerOpen,
        openCartDrawer,
        closeCartDrawer,
        toggleCartDrawer,
    };
}
