<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Input from '@/Components/UI/Input.vue';
import Badge from '@/Components/UI/Badge.vue';

interface WriterItem {
    id: number;
    pen_name: string;
    slug: string;
    bio: string | null;
    avatar_url: string;
    cover_photo_url: string | null;
    verification_badge: boolean;
    total_published: number;
    total_views: number;
    total_likes: number;
}

interface PaginatedWriters {
    data: WriterItem[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

const props = defineProps<{
    writers: PaginatedWriters;
    filters: { search: string | null };
    userWriterState: {
        has_profile: boolean;
        is_approved: boolean;
        status: string | null;
    };
}>();

const searchQuery = ref(props.filters.search || '');

function handleSearch() {
    router.get('/writers', { search: searchQuery.value }, { preserveState: true, replace: true });
}
</script>

<template>
    <MainLayout>
        <Head title="লেখকবৃন্দ ও সাহিত্যিক ডিরেক্টরি — বুকভার্স" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 font-sans space-y-8">

            <!-- Hero Banner Header (Compact Sizing) -->
            <div class="bg-gradient-to-r from-sky-900 via-indigo-900 to-slate-900 rounded-3xl p-6 sm:p-7 text-white flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden shadow-xl">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-sky-500/10 rounded-full blur-3xl"></div>
                
                <div class="relative z-10 space-y-2 max-w-xl">
                    <span class="inline-block px-2.5 py-0.5 bg-sky-500/20 text-sky-300 text-[10px] font-bold rounded-full border border-sky-400/30">
                        ✨ বুকভার্স লেখক ক্লাব
                    </span>
                    <h1 class="text-xl sm:text-2xl font-black font-heading tracking-tight">
                        আমাদের প্রকাশিত লেখক ও সাহিত্যিকবৃন্দ
                    </h1>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        প্রথিতযশা ও উদীয়মান গল্পকার, প্রাবন্ধিক ও কবিদের সাহিত্যকর্ম আবিষ্কার করুন। আপনিও কি একজন লেখক? বুকভার্স প্ল্যাটফর্মে যোগ দিন।
                    </p>
                </div>

                <!-- Dynamic CTA Button based on User Writer Status -->
                <div class="relative z-10 shrink-0 w-full sm:w-auto">
                    <Link
                        v-if="userWriterState.is_approved"
                        href="/writer/dashboard"
                        class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-sky-500 hover:bg-sky-400 text-slate-950 font-extrabold text-xs shadow-lg shadow-sky-500/20 transition"
                    >
                        ✍️ রাইটার ড্যাশবোর্ড →
                    </Link>

                    <Link
                        v-else-if="userWriterState.has_profile"
                        href="/writer/application-status"
                        class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold text-xs shadow-lg shadow-amber-400/20 transition"
                    >
                        ⏳ আবেদনের বর্তমান স্ট্যাটাস →
                    </Link>

                    <Link
                        v-else
                        href="/writer/register"
                        class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-extrabold text-xs shadow-lg shadow-sky-600/30 transition"
                    >
                        ✍️ লেখক হিসেবে আবেদন করুন →
                    </Link>
                </div>
            </div>

            <!-- Search & Filter Bar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white dark:bg-zinc-950 p-4 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-2xs">
                <div class="relative w-full sm:w-80">
                    <Input
                        v-model="searchQuery"
                        placeholder="লেখকের নাম, ছদ্মনাম বা পরিচয় দিয়ে খুঁজুন..."
                        @keyup.enter="handleSearch"
                    />
                </div>
                <div class="text-xs font-semibold text-slate-500 dark:text-zinc-400">
                    মোট {{ writers.data.length }} জন প্রকাশিত লেখক দেখানো হচ্ছে
                </div>
            </div>

            <!-- Writers Cards Grid -->
            <div v-if="writers.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="writer in writers.data"
                    :key="writer.id"
                    class="bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-3xl overflow-hidden shadow-sm hover:shadow-md transition flex flex-col justify-between"
                >
                    <div>
                        <!-- Cover / Top Gradient Header -->
                        <div class="h-24 bg-gradient-to-r from-sky-600 to-indigo-700 relative">
                            <img
                                v-if="writer.cover_photo_url"
                                :src="writer.cover_photo_url"
                                :alt="writer.pen_name"
                                class="w-full h-full object-cover"
                            />
                        </div>

                        <!-- Avatar & Info -->
                        <div class="px-6 relative pb-4">
                            <div class="flex justify-between items-end -mt-10 mb-3">
                                <img
                                    :src="writer.avatar_url"
                                    :alt="writer.pen_name"
                                    class="w-20 h-20 rounded-2xl border-4 border-white dark:border-zinc-950 object-cover shadow-md bg-white"
                                />
                                <Badge v-if="writer.verification_badge" variant="brand" size="sm" class="mb-1">
                                    ✓ ভেরিফায়েড লেখক
                                </Badge>
                            </div>

                            <Link :href="`/writers/${writer.slug}`" class="group">
                                <h3 class="text-lg font-bold font-heading text-slate-900 dark:text-white group-hover:text-sky-600 transition flex items-center gap-1.5">
                                    {{ writer.pen_name }}
                                </h3>
                            </Link>

                            <p class="text-xs text-slate-500 dark:text-zinc-400 line-clamp-2 mt-2 leading-relaxed">
                                {{ writer.bio || 'বুকভার্স ই-ম্যাগাজিনের সম্মানিত নিবন্ধিত লেখক।' }}
                            </p>
                        </div>
                    </div>

                    <!-- Writer Stats Footprint -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-zinc-900/50 border-t border-slate-100 dark:border-zinc-900 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-4 text-slate-600 dark:text-zinc-400 font-medium">
                            <div>
                                <span class="font-extrabold text-slate-900 dark:text-white">{{ writer.total_published }}</span>
                                <span class="text-[10px] text-slate-400 block">লেখা</span>
                            </div>
                            <div class="h-4 w-px bg-slate-200 dark:bg-zinc-800"></div>
                            <div>
                                <span class="font-extrabold text-slate-900 dark:text-white">{{ writer.total_views }}</span>
                                <span class="text-[10px] text-slate-400 block">পাঠ</span>
                            </div>
                            <div class="h-4 w-px bg-slate-200 dark:bg-zinc-800"></div>
                            <div>
                                <span class="font-extrabold text-slate-900 dark:text-white">{{ writer.total_likes }}</span>
                                <span class="text-[10px] text-slate-400 block">লাইক</span>
                            </div>
                        </div>

                        <Link
                            :href="`/writers/${writer.slug}`"
                            class="text-xs font-bold text-sky-600 dark:text-sky-400 hover:underline"
                        >
                            পোর্টফোলিও →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty State when No Writers Found -->
            <div v-else class="bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-3xl p-12 text-center space-y-4">
                <div class="text-4xl">🖊️</div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white font-heading">কোনো লেখক পাওয়া যায়নি</h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400 max-w-md mx-auto leading-relaxed">
                    বুকভার্স প্রকাশনা প্ল্যাটফর্মে যুক্ত হয়ে প্রথম লেখক হিসেবে নিজের গল্প ও সাহিত্যকর্ম প্রকাশ করুন!
                </p>
                <div class="pt-2">
                    <Link
                        href="/writer/register"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-md shadow-sky-600/20 transition"
                    >
                        ✍️ লেখক হিসেবে আবেদন করুন
                    </Link>
                </div>
            </div>

        </div>
    </MainLayout>
</template>
