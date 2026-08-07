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
    bio?: string;
    avatar_url: string;
    verification_badge: boolean;
    total_published: number;
}

interface PaginatedWriters {
    data: WriterProfile[];
    links: any[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    writers: PaginatedWriters;
}>();
</script>

<template>
    <Head title="Authors & Writers Directory — BookVerse E-Magazine">
        <meta name="description" content="Discover verified writers and independent literature authors on BookVerse E-Magazine." />
    </Head>

    <MainLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10 font-sans">
            <!-- Header Title Banner -->
            <div class="text-center max-w-2xl mx-auto space-y-3">
                <Badge variant="brand" size="sm" class="font-extrabold uppercase tracking-wider">
                    ✍️ E-Magazine Community
                </Badge>
                <h1 class="text-3xl sm:text-4xl font-black font-heading text-slate-900 dark:text-white">
                    Meet Our Independent Writers
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-zinc-400">
                    Explore profile portfolios and literature collections from verified authors.
                </p>
            </div>

            <!-- Writers Grid -->
            <div v-if="writers.data.length > 0" class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 gap-6">
                <Link
                    v-for="writer in writers.data"
                    :key="writer.id"
                    :href="`/writers/${writer.slug}`"
                    class="group p-6 rounded-3xl bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 hover:shadow-lg transition space-y-4 text-center flex flex-col items-center justify-between"
                >
                    <div class="space-y-3 flex flex-col items-center">
                        <Avatar :name="writer.pen_name" size="xl" class="w-20 h-20 text-xl shadow-xs group-hover:scale-105 transition" />

                        <div>
                            <div class="flex items-center justify-center gap-1.5">
                                <h3 class="font-extrabold text-base text-slate-900 dark:text-white group-hover:text-sky-600 dark:group-hover:text-sky-400 transition font-heading">
                                    {{ writer.pen_name }}
                                </h3>
                                <span v-if="writer.verification_badge" class="text-sky-500 font-bold text-xs" title="Verified Author">✓</span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-zinc-400 line-clamp-2 mt-1">
                                {{ writer.bio || 'Author at BookVerse E-Magazine.' }}
                            </p>
                        </div>
                    </div>

                    <div class="w-full pt-3 border-t border-slate-100 dark:border-zinc-900 text-xs font-bold text-sky-600 dark:text-sky-400 flex items-center justify-center gap-1 group-hover:translate-x-1 transition">
                        View Author Profile →
                    </div>
                </Link>
            </div>

            <div v-else class="text-center py-16 bg-white dark:bg-zinc-950 rounded-3xl border border-slate-200 dark:border-zinc-800 text-slate-400 text-sm">
                No approved writers in directory yet.
            </div>

            <!-- Pagination -->
            <div v-if="writers.links.length > 3" class="pt-4 flex justify-center">
                <Pagination :links="writers.links" />
            </div>
        </div>
    </MainLayout>
</template>
