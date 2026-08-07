<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Avatar from '@/Components/UI/Avatar.vue';
import Badge from '@/Components/UI/Badge.vue';
import ReadingProgressBar from '@/Components/Magazine/ReadingProgressBar.vue';
import LikeButton from '@/Components/Magazine/LikeButton.vue';
import BookmarkButton from '@/Components/Magazine/BookmarkButton.vue';
import PublicCommentThread from '@/Components/Magazine/PublicCommentThread.vue';

interface WriterProfile {
    id: number;
    pen_name: string;
    slug: string;
    bio?: string;
    avatar_url: string;
    user?: {
        name: string;
    };
}

interface Article {
    id: number;
    title: string;
    slug: string;
    excerpt?: string;
    content?: string;
    cover_url?: string;
    cover_image_alt?: string;
    word_count: number;
    read_time_minutes: number;
    likes_count?: number;
    comments_count?: number;
    bookmarks_count?: number;
    meta_title?: string;
    meta_description?: string;
    published_at?: string;
    created_at: string;
    writer_profile?: WriterProfile;
    article_comments?: any[];
}

const props = defineProps<{
    article: Article;
    hasLiked?: boolean;
    hasBookmarked?: boolean;
    relatedArticles?: Article[];
    jsonLdSchema?: any;
}>();

function formatDate(dateStr?: string | null) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
}

function shareOnFacebook() {
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}`, '_blank');
}

function shareOnTwitter() {
    window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(window.location.href)}&text=${encodeURIComponent(props.article.title)}`, '_blank');
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href);
}
</script>

<template>
    <Head :title="article.meta_title || article.title">
        <meta name="description" :content="article.meta_description || article.excerpt || ''" />
        <meta property="og:title" :content="article.title" />
        <meta property="og:description" :content="article.excerpt || ''" />
        <meta v-if="article.cover_url" property="og:image" :content="article.cover_url" />
        <component is="script" type="application/ld+json" v-if="jsonLdSchema">
            {{ JSON.stringify(jsonLdSchema) }}
        </component>
    </Head>

    <MainLayout>
        <!-- Reading Progress Bar -->
        <ReadingProgressBar />

        <article class="font-sans">

            <!-- Article Cover Banner (full bleed) -->
            <div v-if="article.cover_url" class="relative w-full h-64 sm:h-96 bg-slate-900 overflow-hidden">
                <img
                    :src="article.cover_url"
                    :alt="article.cover_image_alt || article.title"
                    class="absolute inset-0 w-full h-full object-cover opacity-70"
                />
                <!-- Gradient overlay at bottom -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>

                <!-- Title on image (on mobile, title floats over cover) -->
                <div class="absolute bottom-0 left-0 right-0 p-6 sm:p-10 max-w-3xl mx-auto">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-sky-500/30 text-sky-200 border border-sky-400/30 text-[10px] font-black tracking-widest uppercase backdrop-blur-sm">
                            📖 Literature & E-Magazine
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-black text-white leading-tight tracking-tight font-heading drop-shadow-lg line-clamp-3">
                        {{ article.title }}
                    </h1>
                </div>
            </div>

            <!-- Article Content Container -->
            <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 sm:py-12 space-y-8">

                <!-- Title (if no cover) -->
                <div v-if="!article.cover_url" class="space-y-4 text-center">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-sky-100 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800 text-[10px] font-black tracking-widest uppercase">
                        📖 Literature & E-Magazine
                    </span>
                    <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white leading-tight tracking-tight font-heading">
                        {{ article.title }}
                    </h1>
                </div>

                <!-- Excerpt (if cover present, show below) -->
                <p v-if="article.excerpt && article.cover_url" class="text-base sm:text-lg text-slate-600 dark:text-zinc-300 leading-relaxed border-l-4 border-sky-400 pl-4 italic">
                    {{ article.excerpt }}
                </p>
                <p v-else-if="article.excerpt" class="text-base sm:text-lg text-slate-600 dark:text-zinc-300 leading-relaxed text-center max-w-2xl mx-auto">
                    {{ article.excerpt }}
                </p>

                <!-- Author + Meta Bar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-2xl bg-slate-50 dark:bg-zinc-900/60 border border-slate-100 dark:border-zinc-800">
                    <Link
                        v-if="article.writer_profile"
                        :href="`/writers/${article.writer_profile.slug}`"
                        class="flex items-center gap-3 group"
                    >
                        <Avatar :name="article.writer_profile.pen_name" size="md" class="ring-2 ring-sky-400/30 group-hover:ring-sky-400 transition-all" />
                        <div>
                            <p class="font-extrabold text-sm text-slate-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition">
                                {{ article.writer_profile.pen_name }}
                            </p>
                            <p class="text-[11px] text-slate-500 dark:text-zinc-500">Author at BookVerse</p>
                        </div>
                    </Link>

                    <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-zinc-400 flex-wrap">
                        <span class="font-mono">{{ formatDate(article.published_at || article.created_at) }}</span>
                        <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-zinc-700"></span>
                        <span class="font-bold text-sky-600 dark:text-sky-400">⏱ {{ article.read_time_minutes }} min read</span>
                        <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-zinc-700"></span>
                        <span class="font-mono">{{ article.word_count }} words</span>
                    </div>
                </div>

                <!-- Share Buttons (sticky on mobile could be added later) -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[11px] font-black tracking-wider uppercase text-slate-400 mr-1">Share:</span>
                    <button @click="shareOnFacebook" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#1877F2] text-white text-xs font-bold hover:opacity-90 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.007 1.792-4.669 4.532-4.669 1.312 0 2.686.235 2.686.235v2.953h-1.514c-1.491 0-1.956.927-1.956 1.874v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>
                        Facebook
                    </button>
                    <button @click="shareOnTwitter" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-black text-white text-xs font-bold hover:opacity-90 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        X / Twitter
                    </button>
                    <button @click="copyLink" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-200 dark:hover:bg-zinc-700 transition cursor-pointer border border-slate-200 dark:border-zinc-700">
                        🔗 Copy Link
                    </button>
                </div>

                <!-- Horizontal Divider -->
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-px bg-slate-200 dark:bg-zinc-800"></div>
                    <span class="text-slate-300 dark:text-zinc-700 text-lg">✦</span>
                    <div class="flex-1 h-px bg-slate-200 dark:bg-zinc-800"></div>
                </div>

                <!-- Article Body Content -->
                <div
                    class="prose dark:prose-invert prose-sky max-w-none text-slate-800 dark:text-slate-200
                           prose-headings:font-black prose-headings:font-heading
                           prose-h1:text-2xl sm:prose-h1:text-3xl
                           prose-h2:text-xl sm:prose-h2:text-2xl
                           prose-p:leading-relaxed prose-p:text-base
                           prose-a:text-sky-600 prose-a:no-underline hover:prose-a:underline
                           prose-blockquote:border-sky-400 prose-blockquote:bg-sky-50 dark:prose-blockquote:bg-sky-950/20 prose-blockquote:rounded-r-2xl prose-blockquote:py-1 prose-blockquote:not-italic
                           prose-img:rounded-2xl prose-img:shadow-md
                           prose-code:bg-slate-100 dark:prose-code:bg-zinc-900 prose-code:rounded prose-code:px-1"
                    v-html="article.content"
                ></div>

                <!-- Interactive Reader Actions (Like & Bookmark) -->
                <div class="flex items-center justify-between gap-4 py-6 border-t border-b border-slate-200 dark:border-zinc-800 my-8">
                    <LikeButton
                        :submissionId="article.id"
                        :initialLiked="hasLiked"
                        :initialCount="article.likes_count || 0"
                    />

                    <BookmarkButton
                        :submissionId="article.id"
                        :initialBookmarked="hasBookmarked"
                    />
                </div>

                <!-- Author Bio Footer -->
                <div v-if="article.writer_profile" class="mt-8 p-6 sm:p-8 rounded-3xl bg-gradient-to-br from-slate-50 to-indigo-50/30 dark:from-zinc-900 dark:to-indigo-950/20 border border-slate-200 dark:border-zinc-800 space-y-4">
                    <p class="text-[11px] font-black tracking-widest uppercase text-slate-400 dark:text-zinc-600">About the Author</p>
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
                        <Avatar :name="article.writer_profile.pen_name" size="xl" class="ring-4 ring-sky-400/20 shrink-0" />
                        <div class="space-y-2 text-center sm:text-left">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                <h3 class="font-extrabold text-lg text-slate-900 dark:text-white font-heading">
                                    {{ article.writer_profile.pen_name }}
                                </h3>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-sky-100 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800 font-bold self-center">
                                    BookVerse Author
                                </span>
                            </div>
                            <p class="text-sm text-slate-600 dark:text-zinc-400 leading-relaxed">
                                {{ article.writer_profile.bio || 'Published author at BookVerse E-Magazine.' }}
                            </p>
                            <Link
                                :href="`/writers/${article.writer_profile.slug}`"
                                class="inline-flex items-center gap-1 text-xs font-extrabold text-sky-600 dark:text-sky-400 hover:underline mt-1"
                            >
                                View Author Profile →
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Public Discussion Thread -->
                <div class="mt-12 pt-8 border-t border-slate-200 dark:border-zinc-800">
                    <PublicCommentThread
                        :submissionId="article.id"
                        :comments="article.article_comments"
                        :authorUserId="article.writer_profile?.id"
                    />
                </div>

                <!-- Divider -->
                <div v-if="relatedArticles && relatedArticles.length > 0" class="flex items-center gap-3">
                    <div class="flex-1 h-px bg-slate-200 dark:bg-zinc-800"></div>
                    <span class="text-slate-300 dark:text-zinc-700 text-lg">✦</span>
                    <div class="flex-1 h-px bg-slate-200 dark:bg-zinc-800"></div>
                </div>

                <!-- Related Articles -->
                <div v-if="relatedArticles && relatedArticles.length > 0" class="space-y-5">
                    <h3 class="font-black text-lg text-slate-900 dark:text-white font-heading">
                        More by {{ article.writer_profile?.pen_name }}
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <Link
                            v-for="rel in relatedArticles"
                            :key="rel.id"
                            :href="`/articles/${rel.slug}`"
                            class="group flex gap-4 p-4 rounded-2xl bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 hover:shadow-md hover:border-sky-300 dark:hover:border-sky-800 transition-all duration-300"
                        >
                            <!-- Thumb -->
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden bg-slate-100 dark:bg-zinc-800 shrink-0">
                                <img v-if="rel.cover_url" :src="rel.cover_url" :alt="rel.title" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" />
                                <div v-else class="w-full h-full flex items-center justify-center text-2xl opacity-30">📝</div>
                            </div>
                            <!-- Info -->
                            <div class="flex flex-col justify-between flex-1 min-w-0">
                                <h4 class="font-bold text-sm text-slate-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition line-clamp-2 leading-snug">
                                    {{ rel.title }}
                                </h4>
                                <div class="flex items-center justify-between text-[10px] text-slate-400 font-mono mt-1">
                                    <span>⏱ {{ rel.read_time_minutes }} min</span>
                                    <span class="font-bold text-sky-600 dark:text-sky-400 group-hover:translate-x-0.5 transition-transform">Read →</span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="pt-4 flex justify-center">
                    <Link
                        href="/magazine"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 text-xs font-extrabold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-zinc-900 hover:border-sky-300 dark:hover:border-sky-800 transition-all shadow-sm"
                    >
                        ← Back to Magazine
                    </Link>
                </div>
            </div>
        </article>
    </MainLayout>
</template>
