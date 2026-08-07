<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Avatar from '@/Components/UI/Avatar.vue';
import Badge from '@/Components/UI/Badge.vue';
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
}>();
</script>

<template>
    <Head title="Saved Reading List — BookVerse E-Magazine">
        <meta name="description" content="Access your bookmarked articles and saved reading list on BookVerse E-Magazine." />
    </Head>

    <MainLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8 font-sans">
            <!-- Header Title Banner -->
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-800 pb-5">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black font-heading text-slate-900 dark:text-white flex items-center gap-2">
                        🔖 Saved Reading List ({{ articles.total }})
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                        Articles you've bookmarked to read later.
                    </p>
                </div>
            </div>

            <!-- Bookmarked Articles Grid -->
            <div v-if="articles.data.length > 0" class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <Link
                    v-for="art in articles.data"
                    :key="art.id"
                    :href="`/articles/${art.slug}`"
                    class="group p-5 rounded-3xl bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 hover:shadow-lg transition space-y-4 flex flex-col justify-between"
                >
                    <div class="space-y-3">
                        <div v-if="art.cover_url" class="rounded-2xl overflow-hidden h-40 w-full bg-slate-100 dark:bg-zinc-900">
                            <img :src="art.cover_url" :alt="art.title" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" />
                        </div>

                        <div class="flex items-center gap-2 text-xs text-slate-500">
                            <span v-if="art.writer_profile?.pen_name" class="font-bold text-slate-700 dark:text-slate-300">
                                {{ art.writer_profile.pen_name }}
                            </span>
                            <span>•</span>
                            <span>{{ new Date(art.published_at || art.created_at).toLocaleDateString() }}</span>
                        </div>

                        <h3 class="font-extrabold text-base text-slate-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition line-clamp-2 leading-snug">
                            {{ art.title }}
                        </h3>

                        <p v-if="art.excerpt" class="text-xs text-slate-500 dark:text-zinc-400 line-clamp-2">
                            {{ art.excerpt }}
                        </p>
                    </div>

                    <div class="pt-2 border-t border-slate-100 dark:border-zinc-900 flex items-center justify-between text-xs text-slate-400 font-mono">
                        <span>⏱️ {{ art.read_time_minutes }} min read</span>
                        <span class="text-sky-600 dark:text-sky-400 font-bold group-hover:translate-x-1 transition">Read Article →</span>
                    </div>
                </Link>
            </div>

            <div v-else class="text-center py-16 bg-white dark:bg-zinc-950 rounded-3xl border border-slate-200 dark:border-zinc-800 text-slate-400 text-xs space-y-3">
                <div class="text-3xl">🔖</div>
                <p class="font-semibold text-slate-600 dark:text-zinc-400">Your reading list is empty.</p>
                <p>Browse the E-Magazine catalog and save interesting articles to read later!</p>
                <div class="pt-2">
                    <Link href="/magazine" class="px-4 py-2 rounded-xl bg-sky-600 text-white font-bold text-xs hover:bg-sky-700 transition">
                        Explore E-Magazine →
                    </Link>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="articles.links.length > 3" class="pt-4 flex justify-center">
                <Pagination :links="articles.links" />
            </div>
        </div>
    </MainLayout>
</template>
