<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Avatar from '@/Components/UI/Avatar.vue';
import Pagination from '@/Components/UI/Pagination.vue';

interface WriterProfile {
    id: number;
    pen_name: string;
    slug: string;
    avatar_url: string;
}

interface Article {
    id: number;
    title: string;
    slug: string;
    excerpt?: string;
    cover_url?: string;
    word_count: number;
    read_time_minutes: number;
    published_at?: string;
    created_at: string;
    writer_profile?: WriterProfile;
}

interface PaginatedArticles {
    data: Article[];
    links: any[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    articles: PaginatedArticles;
    featuredArticles?: Article[];
}>();

function formatDate(dateStr?: string | null) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>

<template>
    <Head title="BookVerse E-Magazine — Literature, Stories & Essays">
        <meta name="description" content="Explore published articles, essays, and stories by independent authors on BookVerse E-Magazine." />
    </Head>

    <MainLayout>
        <!-- Hero Banner -->
        <div class="relative bg-gradient-to-br from-indigo-950 via-slate-900 to-sky-950 overflow-hidden">
            <!-- Decorative elements -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-0 right-0 w-80 h-80 bg-sky-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-indigo-600/15 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2"></div>
            </div>
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 relative z-10">
                <div class="text-center space-y-4 max-w-2xl mx-auto">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-sky-500/20 text-sky-300 border border-sky-500/30 text-[11px] font-black tracking-widest uppercase">
                        ✨ বুকভার্স ই-ম্যাগাজিন
                    </span>
                    <h1 class="text-3xl sm:text-5xl font-black text-white leading-tight tracking-tight font-heading">
                        চিন্তাশীল গল্প ও সাহিত্য
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-md mx-auto">
                        স্বাধীন ও প্রথিতযশা লেখকদের অনন্য গল্প, প্রবন্ধ ও সাহিত্য সাময়িকী।
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-3 pt-1">
                        <span class="text-xs text-slate-300 font-semibold bg-white/10 px-3.5 py-1.5 rounded-xl border border-white/10">
                            📚 {{ articles.total }} টি প্রকাশিত সাহিত্যকর্ম
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10 font-sans">

            <!-- Featured Article (first one, large card) -->
            <div v-if="articles.data.length > 0" class="space-y-3">
                <div class="flex items-center gap-2">
                    <span class="w-1 h-5 rounded-full bg-gradient-to-b from-sky-400 to-indigo-500"></span>
                    <h2 class="text-xs font-black tracking-widest uppercase text-slate-500 dark:text-zinc-500">Featured Story</h2>
                </div>

                <Link
                    :href="`/articles/${articles.data[0].slug}`"
                    class="group block rounded-3xl overflow-hidden bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 shadow-sm hover:shadow-xl transition-all duration-300"
                >
                    <div class="flex flex-col sm:flex-row">
                        <!-- Cover Image -->
                        <div class="sm:w-2/5 h-56 sm:h-auto bg-gradient-to-br from-sky-100 to-indigo-100 dark:from-zinc-800 dark:to-zinc-900 overflow-hidden shrink-0">
                            <img
                                v-if="articles.data[0].cover_url"
                                :src="articles.data[0].cover_url"
                                :alt="articles.data[0].title"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-5xl opacity-30">📖</div>
                        </div>
                        <!-- Content -->
                        <div class="flex-1 p-6 sm:p-8 flex flex-col justify-between gap-4">
                            <div class="space-y-3">
                                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-zinc-400">
                                    <Avatar v-if="articles.data[0].writer_profile" :name="articles.data[0].writer_profile.pen_name" size="xs" />
                                    <span class="font-bold text-slate-700 dark:text-slate-300">
                                        {{ articles.data[0].writer_profile?.pen_name || 'Anonymous' }}
                                    </span>
                                    <span>·</span>
                                    <span>{{ formatDate(articles.data[0].published_at || articles.data[0].created_at) }}</span>
                                </div>
                                <h3 class="font-black text-xl sm:text-2xl text-slate-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition leading-snug font-heading">
                                    {{ articles.data[0].title }}
                                </h3>
                                <p v-if="articles.data[0].excerpt" class="text-sm text-slate-600 dark:text-zinc-400 line-clamp-3 leading-relaxed">
                                    {{ articles.data[0].excerpt }}
                                </p>
                            </div>
                            <div class="flex items-center justify-between text-xs text-slate-400 pt-2 border-t border-slate-100 dark:border-zinc-900">
                                <span class="font-mono">⏱ {{ articles.data[0].read_time_minutes }} min read</span>
                                <span class="font-extrabold text-sky-600 dark:text-sky-400 group-hover:translate-x-1 transition-transform">Read Full Story →</span>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- All Articles Grid -->
            <div class="space-y-4" v-if="articles.data.length > 1">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-1 h-5 rounded-full bg-gradient-to-b from-emerald-400 to-teal-500"></span>
                        <h2 class="text-xs font-black tracking-widest uppercase text-slate-500 dark:text-zinc-500">All Articles</h2>
                    </div>
                    <span class="text-xs text-slate-400 font-mono">{{ articles.total }} total</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <Link
                        v-for="(art, i) in articles.data.slice(1)"
                        :key="art.id"
                        :href="`/articles/${art.slug}`"
                        class="group flex flex-col bg-white dark:bg-zinc-950 rounded-2xl border border-slate-200 dark:border-zinc-800 overflow-hidden hover:shadow-lg hover:border-sky-300 dark:hover:border-sky-800 transition-all duration-300"
                    >
                        <!-- Cover -->
                        <div class="h-44 bg-gradient-to-br from-slate-100 to-indigo-50 dark:from-zinc-800 dark:to-zinc-900 overflow-hidden">
                            <img
                                v-if="art.cover_url"
                                :src="art.cover_url"
                                :alt="art.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-4xl opacity-20">📝</div>
                        </div>

                        <!-- Content -->
                        <div class="p-4 flex flex-col flex-1 gap-3">
                            <div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-zinc-500">
                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ art.writer_profile?.pen_name || 'Anonymous' }}</span>
                                <span>·</span>
                                <span>{{ formatDate(art.published_at || art.created_at) }}</span>
                            </div>

                            <h3 class="font-extrabold text-sm text-slate-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition line-clamp-2 leading-snug font-heading flex-1">
                                {{ art.title }}
                            </h3>

                            <p v-if="art.excerpt" class="text-xs text-slate-500 dark:text-zinc-400 line-clamp-2 leading-relaxed">
                                {{ art.excerpt }}
                            </p>

                            <div class="flex items-center justify-between text-[11px] text-slate-400 border-t border-slate-100 dark:border-zinc-900 pt-2.5 mt-auto">
                                <span class="font-mono">⏱ {{ art.read_time_minutes }} min</span>
                                <span class="font-bold text-sky-600 dark:text-sky-400 group-hover:translate-x-0.5 transition-transform">Read →</span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="articles.data.length === 0" class="text-center py-20 rounded-3xl bg-slate-50/80 dark:bg-zinc-900/60 border border-dashed border-slate-200 dark:border-zinc-800 space-y-3">
                <span class="text-5xl">📖</span>
                <h3 class="font-black text-lg text-slate-800 dark:text-white">No articles published yet</h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400 font-medium">Stay tuned — our authors are writing their first stories!</p>
            </div>

            <!-- Pagination -->
            <div v-if="articles.links.length > 3" class="pt-2 flex justify-center">
                <Pagination :links="articles.links" />
            </div>
        </div>
    </MainLayout>
</template>
