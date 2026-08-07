<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Badge from '@/Components/UI/Badge.vue';
import Modal from '@/Components/UI/Modal.vue';

interface WriterData {
    pen_name: string;
    slug: string;
    bio: string | null;
    avatar_url: string;
    cover_photo_url: string | null;
    portfolio_url: string | null;
    status: string;
    status_label: string;
    status_badge: 'warning' | 'success' | 'error' | 'default';
    verification_badge: boolean;
    social_links?: Record<string, string | null>;
    stats: {
        total_submissions: number;
        total_published: number;
        total_drafts: number;
        total_views: number;
        total_likes: number;
    };
    recent_articles: Array<{
        id: number;
        title: string;
        status: string;
        views: number;
        likes: number;
        updated_at: string;
    }>;
}

const props = defineProps<{
    writer: WriterData;
}>();

const showDetailsModal = ref(false);

function handleAvatarError(e: Event, name: string) {
    const target = e.target as HTMLImageElement;
    const initial = name ? name.trim().charAt(0) : 'B';
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120"><rect width="120" height="120" fill="#0284c7"/><text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#ffffff" font-size="52" font-family="sans-serif" font-weight="bold">${initial}</text></svg>`;
    target.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svg)));
}
</script>

<template>
    <MainLayout>
        <Head title="Writer Workspace Dashboard — BookVerse E-Magazine" />

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans space-y-8">

            <!-- Author Header Profile Banner -->
            <div class="relative rounded-3xl overflow-hidden bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 shadow-md">
                <!-- Cover Photo Background -->
                <div class="h-36 sm:h-48 bg-gradient-to-r from-sky-800 via-indigo-900 to-slate-900 relative">
                    <img v-if="writer.cover_photo_url" :src="writer.cover_photo_url" class="w-full h-full object-cover opacity-80" />
                    <div class="absolute inset-0 bg-black/20"></div>
                </div>

                <!-- Avatar & Author Meta Header -->
                <div class="p-6 sm:p-8 pt-0 flex flex-col sm:flex-row items-center sm:items-end justify-between gap-6 -mt-12 sm:-mt-16 relative z-10 text-center sm:text-left">
                    <div class="flex flex-col sm:flex-row items-center sm:items-end gap-4 w-full sm:w-auto">
                        <img
                            :src="writer.avatar_url"
                            :alt="writer.pen_name"
                            @error="handleAvatarError($event, writer.pen_name)"
                            class="w-24 h-24 sm:w-28 sm:h-28 rounded-full border-4 border-white dark:border-zinc-950 shadow-xl object-cover bg-white shrink-0 mx-auto sm:mx-0"
                        />
                        <div class="space-y-1 pb-1 flex-1">
                            <div class="flex flex-col sm:flex-row items-center gap-2 flex-wrap justify-center sm:justify-start">
                                <h1 class="text-xl sm:text-2xl font-extrabold font-heading text-slate-900 dark:text-white">
                                    {{ writer.pen_name }}
                                </h1>
                                <!-- Admin Controlled Blue Checkmark Verified Writer Badge -->
                                <span v-if="writer.verification_badge" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-400/30 text-[10px] sm:text-[11px] font-bold">
                                    <svg class="w-3.5 h-3.5 text-sky-500 fill-current" viewBox="0 0 20 20">
                                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                    </svg>
                                    Verified Author
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xl line-clamp-2 sm:line-clamp-none">
                                {{ writer.bio || 'BookVerse Published Writer & Literary Contributor' }}
                            </p>
                        </div>
                    </div>

                    <!-- Quick Actions Bar -->
                    <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto pt-2 sm:pt-0 shrink-0">
                        <button
                            type="button"
                            @click="showDetailsModal = true"
                            class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-800 hover:bg-slate-50 dark:hover:bg-zinc-900 text-slate-700 dark:text-slate-300 font-bold text-xs text-center transition cursor-pointer flex items-center justify-center gap-1.5"
                        >
                            📄 Details / বিবরণ
                        </button>
                        <Link
                            href="/writer/articles/create"
                            class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-md shadow-sky-600/20 text-center transition"
                        >
                            + Write New Article
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Dashboard Navigation Sub-bar -->
            <div class="flex items-center gap-2 border-b border-slate-200 dark:border-zinc-800 pb-3 text-xs font-bold text-slate-600 dark:text-slate-400 overflow-x-auto">
                <span class="px-3 py-1.5 rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20">
                    📊 Overview & Stats
                </span>
                <Link href="/writer/articles?status=draft" class="px-3 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-900 cursor-pointer">
                    📝 My Drafts ({{ writer.stats.total_drafts }})
                </Link>
                <Link href="/writer/articles?status=published" class="px-3 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-900 cursor-pointer">
                    📚 Published Articles ({{ writer.stats.total_published }})
                </Link>
                <span class="px-3 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-zinc-900 cursor-pointer">
                    ⚙️ Profile Settings
                </span>
            </div>

            <!-- KPI Analytics Cards Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <!-- Total Submissions -->
                <div class="bg-white dark:bg-zinc-950 p-5 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-2xs space-y-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Articles</span>
                    <p class="text-2xl font-extrabold text-slate-900 dark:text-white font-heading">
                        {{ writer.stats.total_submissions }}
                    </p>
                    <span class="text-[10px] text-slate-400">Drafts + Submissions</span>
                </div>

                <!-- Published Articles -->
                <div class="bg-white dark:bg-zinc-950 p-5 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-2xs space-y-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Published</span>
                    <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 font-heading">
                        {{ writer.stats.total_published }}
                    </p>
                    <span class="text-[10px] text-slate-400">Live in E-Magazine</span>
                </div>

                <!-- Total Views -->
                <div class="bg-white dark:bg-zinc-950 p-5 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-2xs space-y-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Reads / Views</span>
                    <p class="text-2xl font-extrabold text-sky-600 dark:text-sky-400 font-heading">
                        {{ writer.stats.total_views.toLocaleString() }}
                    </p>
                    <span class="text-[10px] text-slate-400">Article impressions</span>
                </div>

                <!-- Total Likes -->
                <div class="bg-white dark:bg-zinc-950 p-5 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-2xs space-y-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Reader Appreciation</span>
                    <p class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 font-heading">
                        ❤️ {{ writer.stats.total_likes.toLocaleString() }}
                    </p>
                    <span class="text-[10px] text-slate-400">Total article likes</span>
                </div>
            </div>

            <!-- Submissions & Draft Workspace Table -->
            <div class="bg-white dark:bg-zinc-950 rounded-3xl border border-slate-200 dark:border-zinc-800 p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-base text-slate-900 dark:text-white font-heading">Recent Articles & Submissions</h3>
                        <p class="text-xs text-slate-400">Manage your drafted stories, pending editorial revisions, and live magazine articles</p>
                    </div>
                </div>

                <div v-if="writer.stats.total_submissions === 0" class="text-center py-12 space-y-3 bg-slate-50 dark:bg-zinc-900/50 rounded-2xl border border-dashed border-slate-200 dark:border-zinc-800">
                    <span class="text-4xl block">✍️</span>
                    <h4 class="font-bold text-sm text-slate-800 dark:text-slate-200">No Articles Written Yet</h4>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        Ready to share your voice with BookVerse readers? Create your first article draft using our rich editor.
                    </p>
                    <Link
                        href="/writer/articles/create"
                        class="inline-block px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs transition"
                    >
                        Start Writing Now →
                    </Link>
                </div>
            </div>

        </div>

        <!-- Author Details Modal -->
        <Modal :show="showDetailsModal" title="Author Profile Details" @close="showDetailsModal = false">
            <div class="p-6 space-y-6 text-slate-800 dark:text-slate-200">
                <!-- Profile Card Header -->
                <div class="flex items-center gap-4">
                    <img
                        :src="writer.avatar_url"
                        :alt="writer.pen_name"
                        @error="handleAvatarError($event, writer.pen_name)"
                        class="w-16 h-16 rounded-full object-cover border-2 border-slate-100 dark:border-zinc-800 shadow-md bg-slate-50"
                    />
                    <div>
                        <h3 class="font-extrabold text-lg text-slate-900 dark:text-white">{{ writer.pen_name }}</h3>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span v-if="writer.verification_badge" class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-400/30 text-[9px] font-black">
                                ✓ Verified Author
                            </span>
                            <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-400/30 text-[9px] font-black">
                                Status: Active
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Author Bio Section -->
                <div class="space-y-2">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-400">Author Bio / আত্মপরিচয়</h4>
                    <div class="bg-slate-50 dark:bg-zinc-900/50 p-4 rounded-2xl border border-slate-100 dark:border-zinc-800/80 text-xs text-slate-700 dark:text-slate-300 leading-relaxed font-serif whitespace-pre-line max-h-40 overflow-y-auto">
                        {{ writer.bio || 'No bio provided.' }}
                    </div>
                </div>

                <!-- Portfolio & Website -->
                <div class="space-y-2" v-if="writer.portfolio_url">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-400">Portfolio / Website</h4>
                    <a
                        :href="writer.portfolio_url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1 text-xs text-sky-600 hover:text-sky-750 font-extrabold transition break-all"
                    >
                        🌐 {{ writer.portfolio_url }}
                    </a>
                </div>

                <!-- Social Presence Section -->
                <div class="space-y-2">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-400">Social Connections</h4>
                    <div class="grid grid-cols-2 gap-3 bg-slate-50 dark:bg-zinc-900/50 p-4 rounded-2xl border border-slate-100 dark:border-zinc-800/80">
                        <div v-for="(link, platform) in writer.social_links" :key="platform" class="flex flex-col gap-0.5">
                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ platform }}</span>
                            <a
                                v-if="link"
                                :href="link"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline truncate font-semibold"
                            >
                                View Link →
                            </a>
                            <span v-else class="text-xs text-slate-400 italic">Not connected</span>
                        </div>
                    </div>
                </div>

                <!-- SEO Metadata info -->
                <div class="bg-slate-50 dark:bg-zinc-900/50 p-4 rounded-2xl border border-slate-100 dark:border-zinc-800/80 space-y-2">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-400">Search Engine Preview (SEO)</h4>
                    <div class="space-y-1">
                        <span class="text-xs font-extrabold text-blue-600 dark:text-sky-400 hover:underline cursor-pointer block">
                            {{ writer.pen_name }} — Author at BookVerse
                        </span>
                        <span class="text-[10px] text-emerald-700 dark:text-emerald-500 block font-mono">
                            https://bookverse.com/writers/{{ writer.slug }}
                        </span>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed line-clamp-2">
                            {{ writer.bio ? writer.bio.substring(0, 150) + '...' : 'BookVerse Published Writer & Literary Contributor' }}
                        </p>
                    </div>
                </div>
            </div>
        </Modal>
    </MainLayout>
</template>
