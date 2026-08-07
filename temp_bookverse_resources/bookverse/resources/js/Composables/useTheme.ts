import { ref, watch } from 'vue';

const STORAGE_KEY = 'bookverse_theme';

// Read saved preference or default to light mode
const savedTheme = typeof localStorage !== 'undefined' ? localStorage.getItem(STORAGE_KEY) : null;
const isDark = ref<boolean>(savedTheme === 'dark');

function applyTheme(dark: boolean) {
    if (typeof document !== 'undefined') {
        if (dark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}

// Initial application
applyTheme(isDark.value);

watch(isDark, (val) => {
    if (typeof localStorage !== 'undefined') {
        localStorage.setItem(STORAGE_KEY, val ? 'dark' : 'light');
    }
    applyTheme(val);
});

export function useTheme() {
    function toggleTheme() {
        isDark.value = !isDark.value;
    }

    function setTheme(dark: boolean) {
        isDark.value = dark;
    }

    return { isDark, toggleTheme, setTheme };
}
