import { ref, watch } from 'vue';

const STORAGE_KEY = 'admin_theme';

const savedTheme = typeof localStorage !== 'undefined' ? localStorage.getItem(STORAGE_KEY) : null;
const isDark = ref<boolean>(savedTheme === 'dark');

function applyTheme(dark: boolean) {
    if (typeof document !== 'undefined') {
        const isAdmin = typeof window !== 'undefined' && window.location.pathname.startsWith('/admin');
        if (dark && isAdmin) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}

watch(isDark, (val) => {
    if (typeof localStorage !== 'undefined') {
        localStorage.setItem(STORAGE_KEY, val ? 'dark' : 'light');
    }
    applyTheme(val);
}, { immediate: true });

export function useAdminTheme() {
    function toggle() {
        isDark.value = !isDark.value;
        applyTheme(isDark.value);
    }

    function enableAdminTheme() {
        if (typeof document !== 'undefined') {
            if (isDark.value) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    }

    function disableAdminTheme() {
        if (typeof document !== 'undefined') {
            document.documentElement.classList.remove('dark');
        }
    }

    return { isDark, toggle, enableAdminTheme, disableAdminTheme };
}
