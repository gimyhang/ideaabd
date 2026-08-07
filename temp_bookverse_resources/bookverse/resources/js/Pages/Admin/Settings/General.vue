

<template>
    <Head title="General Settings — Admin Panel" />

    <AdminLayout>
        <template #header>
            System Settings
        </template>

        <div class="p-6 sm:p-8 space-y-8 font-sans">
            <!-- Header Title Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-sky-500/10 via-indigo-500/10 to-purple-500/10 dark:from-sky-950/30 dark:via-indigo-950/30 dark:to-purple-950/30 p-6 sm:p-7 rounded-3xl border border-sky-200/50 dark:border-sky-900/30">
                <div class="space-y-1">
                    <h1 class="text-xl sm:text-2xl font-black font-heading text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-2xl bg-sky-500 text-white flex items-center justify-center text-lg shadow-md shadow-sky-500/25">🌐</span>
                        General & Branding Settings
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 font-mono">
                        Manage global platform metadata, brand identity, support emails & phone hotlines
                    </p>
                </div>
            </div>

            <!-- Sub-Settings Navigation Tabs -->
            <div class="flex items-center bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-1.5 rounded-2xl shadow-xs gap-1 overflow-x-auto">
                <Link
                    href="/admin/settings/notifications"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-zinc-800/60"
                >
                    🔔 Notification Rules
                </Link>

                <Link
                    href="/admin/settings/general"
                    class="px-4 py-2 rounded-xl text-xs font-black transition-all bg-sky-600 text-white shadow-xs"
                >
                    🌐 General & Branding
                </Link>
            </div>

            <!-- General Settings Form Card -->
            <form @submit.prevent="submit" class="space-y-6 max-w-3xl">
                <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-slate-200/80 dark:border-zinc-800 p-6 sm:p-8 space-y-6 shadow-sm">
                    <div class="border-b border-slate-100 dark:border-zinc-800 pb-4">
                        <h2 class="font-extrabold text-base text-slate-900 dark:text-white font-heading flex items-center gap-2">
                            🌐 Site Identity Configuration
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 font-mono mt-0.5">
                            Key-value settings stored dynamically in system configuration table
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block font-black text-slate-800 dark:text-zinc-200 mb-1 text-xs uppercase tracking-wider font-mono">
                                Site Name <span class="text-rose-500">*</span>
                            </label>
                            <Input v-model="form.site_name" required class="rounded-2xl" />
                        </div>

                        <div>
                            <label class="block font-black text-slate-800 dark:text-zinc-200 mb-1 text-xs uppercase tracking-wider font-mono">
                                Site Tagline / Slogan
                            </label>
                            <Input v-model="form.site_tagline" class="rounded-2xl" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-black text-slate-800 dark:text-zinc-200 mb-1 text-xs uppercase tracking-wider font-mono">
                                    Support Email <span class="text-rose-500">*</span>
                                </label>
                                <Input type="email" v-model="form.support_email" required class="rounded-2xl" />
                            </div>

                            <div>
                                <label class="block font-black text-slate-800 dark:text-zinc-200 mb-1 text-xs uppercase tracking-wider font-mono">
                                    Support Phone
                                </label>
                                <Input v-model="form.support_phone" class="rounded-2xl" />
                            </div>
                        </div>

                        <!-- Brand Logo File Upload -->
                        <div class="pt-2">
                            <label class="block font-black text-slate-800 dark:text-zinc-200 mb-1 text-xs uppercase tracking-wider font-mono">
                                Brand Logo Upload
                            </label>
                            <input
                                id="settings-logo-input"
                                type="file"
                                @change="handleLogoSelect"
                                accept="image/*"
                                class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-2xl file:border-0 file:text-xs file:font-black file:bg-sky-50 dark:file:bg-sky-950/60 file:text-sky-700 dark:file:text-sky-400 hover:file:bg-sky-100 cursor-pointer"
                            />
                            <!-- Selected File Preview -->
                            <div v-if="logoPreview" class="mt-3 flex items-center gap-3">
                                <div class="relative group">
                                    <img :src="logoPreview" alt="Selected Logo" class="h-12 object-contain rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-1 shadow-sm" />
                                    <button
                                        type="button"
                                        @click="clearLogo"
                                        class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-md transition-all opacity-0 group-hover:opacity-100 focus:opacity-100"
                                        title="Remove Selected Logo"
                                    >
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                                <span class="text-xs text-slate-500 dark:text-zinc-400 font-mono">Logo Selected</span>
                            </div>
                            <!-- Current Saved Logo -->  
                            <div v-if="!logoPreview && settings.logo_url" class="mt-3">
                                <p class="text-[10px] text-slate-400 dark:text-zinc-500 uppercase font-bold tracking-wider mb-1">Current Logo</p>
                                <img :src="settings.logo_url" alt="Current Logo" class="h-10 object-contain rounded-xl border border-slate-200 dark:border-zinc-800 bg-white p-1" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-zinc-800">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white text-xs font-extrabold rounded-2xl shadow-md shadow-sky-500/20 active:scale-95 transition cursor-pointer"
                        >
                            Save General Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Input from '@/Components/UI/Input.vue';

interface GeneralSettings {
    site_name: string;
    site_tagline: string;
    support_email: string;
    support_phone: string;
    logo_url?: string;
}

const props = defineProps<{
    settings: GeneralSettings;
}>();

const logoPreview = ref('');

const form = useForm({
    site_name: props.settings.site_name,
    site_tagline: props.settings.site_tagline,
    support_email: props.settings.support_email,
    support_phone: props.settings.support_phone,
    logo: null as File | null,
});

function handleLogoSelect(e: Event) {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        form.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
}

function clearLogo() {
    form.logo = null;
    logoPreview.value = '';
    const fileInput = document.querySelector('#settings-logo-input') as HTMLInputElement;
    if (fileInput) fileInput.value = '';
}

function submit() {
    form.post('/admin/settings/general', {
        preserveScroll: true,
    });
}
</script>