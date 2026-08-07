<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Badge from '@/Components/UI/Badge.vue';

interface ApplicationData {
    pen_name: string;
    avatar_url: string;
    status: 'pending' | 'approved' | 'rejected' | 'suspended';
    status_label: string;
    status_badge: 'warning' | 'success' | 'error' | 'default';
    rejection_reason: string | null;
    created_at: string;
}

const props = defineProps<{
    application: ApplicationData;
}>();

function handleAvatarError(e: Event, name: string) {
    const target = e.target as HTMLImageElement;
    const initial = name ? name.trim().charAt(0) : 'B';
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120"><rect width="120" height="120" fill="#0284c7"/><text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#ffffff" font-size="52" font-family="sans-serif" font-weight="bold">${initial}</text></svg>`;
    target.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svg)));
}
</script>

<template>
    <MainLayout>
        <Head title="Writer Application Status — BookVerse" />

        <div class="max-w-2xl mx-auto px-4 py-12 sm:py-16 font-sans space-y-6">

            <!-- Card Container -->
            <div class="bg-white dark:bg-zinc-950 rounded-3xl p-6 sm:p-10 border border-slate-200 dark:border-zinc-800 shadow-xl space-y-6 text-center">

                <!-- Avatar & Pen Name -->
                <div class="space-y-3">
                    <img
                        :src="application.avatar_url"
                        :alt="application.pen_name"
                        @error="handleAvatarError($event, application.pen_name)"
                        class="w-24 h-24 rounded-full mx-auto object-cover border-4 border-slate-100 dark:border-zinc-800 shadow-md"
                    />
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold font-heading text-slate-900 dark:text-white">
                            {{ application.pen_name }}
                        </h1>
                        <p class="text-xs text-slate-400 mt-0.5">Application submitted on {{ application.created_at }}</p>
                    </div>

                    <!-- Status Badge -->
                    <div class="pt-1">
                        <Badge :variant="application.status_badge">
                            {{ application.status_label }}
                        </Badge>
                    </div>
                </div>

                <hr class="border-slate-100 dark:border-zinc-800" />

                <!-- Dynamic Message Content based on status -->
                <div v-if="application.status === 'pending'" class="space-y-3 bg-amber-50 dark:bg-amber-950/30 p-5 rounded-2xl border border-amber-200 dark:border-amber-900/50 text-xs">
                    <span class="text-3xl block">⏳</span>
                    <h3 class="font-bold text-amber-900 dark:text-amber-300 text-sm">Application Under Editorial Review</h3>
                    <p class="text-amber-800 dark:text-amber-400 leading-relaxed max-w-md mx-auto">
                        Thank you for applying to become a BookVerse author! Your application is currently under review by our editorial team. You will be able to access your Writer Workspace once approved.
                    </p>
                </div>

                <div v-else-if="application.status === 'rejected'" class="space-y-3 bg-rose-50 dark:bg-rose-950/30 p-5 rounded-2xl border border-rose-200 dark:border-rose-900/50 text-xs">
                    <span class="text-3xl block">❌</span>
                    <h3 class="font-bold text-rose-900 dark:text-rose-300 text-sm">Application Decision Notice</h3>
                    <p class="text-rose-800 dark:text-rose-400 leading-relaxed max-w-md mx-auto">
                        Unfortunately, your writer application could not be approved at this time.
                    </p>
                    <div v-if="application.rejection_reason" class="p-3 bg-white dark:bg-zinc-900 rounded-xl text-left border border-rose-200 dark:border-rose-900">
                        <span class="font-bold block text-slate-700 dark:text-slate-300 text-[11px] uppercase">Reason from Editorial Board:</span>
                        <p class="text-slate-600 dark:text-slate-400 italic mt-1 font-serif">"{{ application.rejection_reason }}"</p>
                    </div>
                </div>

                <div v-else-if="application.status === 'suspended'" class="space-y-3 bg-slate-100 dark:bg-zinc-900 p-5 rounded-2xl border border-slate-200 dark:border-zinc-800 text-xs">
                    <span class="text-3xl block">🚫</span>
                    <h3 class="font-bold text-slate-800 dark:text-slate-200 text-sm">Writer Portal Suspended</h3>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed max-w-md mx-auto">
                        Your writer portal access has been temporarily suspended. Please contact customer support for further details.
                    </p>
                </div>

                <!-- Navigation Action -->
                <div class="pt-2">
                    <Link
                        href="/"
                        class="inline-block px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition"
                    >
                        Return to Home Page
                    </Link>
                </div>

            </div>

        </div>
    </MainLayout>
</template>
