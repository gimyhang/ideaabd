<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Avatar from '@/Components/UI/Avatar.vue';
import Badge from '@/Components/UI/Badge.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import FollowButton from '@/Components/Magazine/FollowButton.vue';

interface WriterProfile {
    id: number;
    pen_name: string;
    slug: string;
    bio?: string;
    avatar_url: string;
    cover_photo?: string;
    portfolio_url?: string;
    social_links?: Record<string, string>;
    verification_badge: boolean;
    total_published: number;
    total_views: number;
    followers_count?: number;
    user?: {
        name: string;
    };
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
}

interface PaginatedArticles {
    data: Article[];
    links: any[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    writer: WriterProfile;
    hasFollowed?: boolean;
    articles: PaginatedArticles;
    jsonLdSchema?: any;
}>();
</script>

<template>
    <Head :title="`${writer.pen_name} — Author Profile | BookVerse E-Magazine`">
        <meta name="description" :content="writer.bio || `Articles published by ${writer.pen_name}`" />

        <component is="script" type="application/ld+json" v-if="jsonLdSchema">
            {{ JSON.stringify(jsonLdSchema) }}
        </component>
    </Head>

    <MainLayout>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10 font-sans">
            <!-- Writer Hero Banner Card -->
            <div class="p-8 sm:p-12 rounded-3xl bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 shadow-xs relative overflow-hidden space-y-6">
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left">
                    <Avatar :name="writer.pen_name" size="xl" class="w-24 h-24 sm:w-28 sm:h-28 text-2xl shadow-md" />

                    <div class="space-y-3 flex-1">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center justify-center sm:justify-start gap-2">
                                <h1 class="text-2xl sm:text-3xl font-black font-heading text-slate-900 dark:text-white">
                                    {{ writer.pen_name }}
                                </h1>
                                <Badge v-if="writer.verification_badge" variant="brand" size="sm" class="font-extrabold">
                                    ✓ Verified Author
                                </Badge>
                            </div>

                            <!-- RSS Feed & Follow Controls -->
                            <div class="flex items-center justify-center gap-2">
                                <FollowButton
                                    :writerProfileId="writer.id"
                                    :initialFollowing="hasFollowed"
                                    :initialCount="writer.followers_count || 0"
                                />

                                <a
                                    :href="`/feed/writer/${writer.slug}`"
                                    target="_blank"
                                    class="px-3 py-2 rounded-2xl text-xs font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 hover:bg-amber-500 hover:text-slate-950 border border-amber-200 dark:border-amber-800 transition flex items-center gap-1 cursor-pointer"
                                >
                                    📡 RSS Feed
                                </a>
                            </div>
                        </div>

                        <p class="text-xs sm:text-sm text-slate-600 dark:text-zinc-300 max-w-3xl leading-relaxed">
                            {{ writer.bio || 'Author at BookVerse E-Magazine.' }}
                        </p>

                        <!-- Stats Row -->
                        <div class="flex items-center justify-center sm:justify-start gap-6 pt-2 text-xs font-mono text-slate-500">
                            <div>
                                Articles: <strong class="text-slate-900 dark:text-white font-bold">{{ writer.total_published || articles.total }}</strong>
                            </div>
                            <div>
                                Member Since: <strong class="text-slate-900 dark:text-white font-bold">{{ new Date(writer.id ? Date.now() : Date.now()).getFullYear() }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Published Articles Grid -->
            <div class="space-y-6">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-800 pb-4">
                    <h2 class="text-xl font-black font-heading text-slate-900 dark:text-white">
                        Published Articles ({{ articles.total }})
                    </h2>
                </div>

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

                <div v-else class="text-center py-12 bg-white dark:bg-zinc-950 rounded-3xl border border-slate-200 dark:border-zinc-800 text-slate-400 text-xs">
                    No articles published by this author yet.
                </div>

                <!-- Pagination -->
                <div v-if="articles.links.length > 3" class="pt-4 flex justify-center">
                    <Pagination :links="articles.links" />
                </div>
            </div>
        </div>
    </MainLayout>
</template>
