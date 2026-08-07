<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';
import TiptapEditor from '@/Components/Writer/TiptapEditor.vue';

const form = useForm({
    title: '',
    excerpt: '',
    content: '',
    content_json: null as any,
    cover_image: null as File | null,
    cover_image_alt: '',
    visibility: 'public',
    meta_title: '',
    meta_description: '',
    canonical_url: '',
    allow_comments: true,
});

const showMetadataDrawer = ref(false);
const showLocalRecoveryPrompt = ref(false);
const recoveredLocalContent = ref<string | null>(null);

const LOCAL_STORAGE_KEY = 'bookverse_writer_draft_create';

onMounted(() => {
    const cached = localStorage.getItem(LOCAL_STORAGE_KEY);
    if (cached) {
        try {
            const parsed = JSON.parse(cached);
            if (parsed.content && parsed.content.length > 20) {
                recoveredLocalContent.value = parsed.content;
                showLocalRecoveryPrompt.value = true;
            }
        } catch (e) {}
    }
});

function restoreLocalDraft() {
    if (recoveredLocalContent.value) {
        form.content = recoveredLocalContent.value;
        showLocalRecoveryPrompt.value = false;
        localStorage.removeItem(LOCAL_STORAGE_KEY);
    }
}

function discardLocalDraft() {
    showLocalRecoveryPrompt.value = false;
    localStorage.removeItem(LOCAL_STORAGE_KEY);
}

function handleEditorContentUpdate(html: string) {
    form.content = html;
    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify({
        title: form.title,
        content: html,
        saved_at: new Date().toISOString(),
    }));
}

function handleEditorJsonUpdate(json: any) {
    form.content_json = json;
}

function handleCoverChange(e: Event) {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.cover_image = target.files[0];
    }
}

function submitCreateForm() {
    form.post('/writer/articles', {
        preserveScroll: true,
        onSuccess: () => {
            localStorage.removeItem(LOCAL_STORAGE_KEY);
        },
    });
}
</script>

<template>
    <Head title="Create New Article | BookVerse E-Magazine" />

    <MainLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5 font-sans">

            <!-- Top Sticky Navbar -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-4 rounded-3xl bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 shadow-xs">
                <div class="flex items-center gap-3">
                    <Link
                        href="/writer/articles"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-700 dark:text-slate-200 transition"
                    >
                        ← Articles
                    </Link>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-sky-500 animate-pulse"></span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">New Draft</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button
                        type="button"
                        @click="showMetadataDrawer = !showMetadataDrawer"
                        class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-zinc-700 transition cursor-pointer"
                    >
                        ⚙️ Article Options
                    </button>

                    <Button variant="brand" size="sm" @click="submitCreateForm" :loading="form.processing" class="flex-1 sm:flex-initial">
                        💾 Save Draft
                    </Button>
                </div>
            </div>

            <!-- Crash Recovery Banner -->
            <div v-if="showLocalRecoveryPrompt" class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2 text-amber-800 dark:text-amber-300 font-semibold">
                    <span class="text-base shrink-0">⚠️</span>
                    <span>Unsaved draft found from a previous session!</span>
                </div>
                <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto">
                    <button @click="restoreLocalDraft" class="flex-1 sm:flex-initial px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs transition cursor-pointer text-center">
                        Restore Draft
                    </button>
                    <button @click="discardLocalDraft" class="px-3 py-1.5 rounded-xl bg-slate-200 dark:bg-zinc-800 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-300 transition cursor-pointer">
                        Discard
                    </button>
                </div>
            </div>

            <!-- Article Editor Card -->
            <div class="bg-white dark:bg-zinc-950 rounded-3xl border border-slate-200 dark:border-zinc-800 shadow-xs overflow-hidden">
                <form @submit.prevent="submitCreateForm" class="divide-y divide-slate-100 dark:divide-zinc-900">

                    <!-- Title Area -->
                    <div class="px-6 pt-8 pb-4 sm:px-10">
                        <input
                            v-model="form.title"
                            type="text"
                            placeholder="Article Title..."
                            required
                            class="w-full text-2xl sm:text-4xl font-black font-heading bg-transparent border-none outline-none focus:outline-none text-slate-900 dark:text-white placeholder-slate-300 dark:placeholder-zinc-700 leading-tight"
                        />
                        <p v-if="form.errors.title" class="text-xs text-rose-500 font-bold mt-2">{{ form.errors.title }}</p>
                    </div>

                    <!-- Excerpt -->
                    <div class="px-6 py-4 sm:px-10">
                        <textarea
                            v-model="form.excerpt"
                            rows="2"
                            placeholder="Short sub-headline or article summary (optional)..."
                            class="w-full text-sm sm:text-base bg-transparent border-none outline-none focus:outline-none text-slate-600 dark:text-slate-400 placeholder-slate-300 dark:placeholder-zinc-700 resize-none leading-relaxed"
                        ></textarea>
                    </div>

                    <!-- Rich Text Editor -->
                    <div class="px-4 sm:px-6 py-4">
                        <TiptapEditor
                            v-model="form.content"
                            placeholder="Write your article body here using formatting tools, headings, lists, quotes, and images..."
                            @update:model-value="handleEditorContentUpdate"
                            @change-json="handleEditorJsonUpdate"
                            @save-revision="submitCreateForm"
                        />
                        <p v-if="form.errors.content" class="text-xs text-rose-500 font-bold mt-2">{{ form.errors.content }}</p>
                    </div>

                    <!-- Bottom Submit Area -->
                    <div class="px-6 py-4 sm:px-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-slate-50 dark:bg-zinc-900/50">
                        <p class="text-xs text-slate-400 dark:text-zinc-500 italic">
                            💡 Your draft is automatically saved to local storage. Click "Save Draft" to store it on the server.
                        </p>
                        <Button variant="brand" size="md" @click="submitCreateForm" :loading="form.processing" class="w-full sm:w-auto">
                            💾 Create & Save Draft
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Article Options & SEO Drawer -->
            <transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="showMetadataDrawer" class="bg-white dark:bg-zinc-950 rounded-3xl border border-slate-200 dark:border-zinc-800 shadow-md overflow-hidden">
                    <!-- Drawer Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-zinc-900 bg-slate-50 dark:bg-zinc-900/50">
                        <div class="flex items-center gap-2">
                            <span class="text-base">⚙️</span>
                            <h3 class="font-extrabold text-sm text-slate-900 dark:text-white">Article Options & SEO Metadata</h3>
                        </div>
                        <button type="button" @click="showMetadataDrawer = false" class="p-1.5 rounded-xl hover:bg-slate-200 dark:hover:bg-zinc-800 text-slate-400 hover:text-slate-700 dark:hover:text-white transition cursor-pointer text-sm font-bold">
                            ✕
                        </button>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Cover Image -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Cover Image</label>
                                <div class="border-2 border-dashed border-slate-200 dark:border-zinc-800 rounded-2xl p-4 text-center hover:border-sky-400 transition cursor-pointer">
                                    <input
                                        type="file"
                                        accept="image/*"
                                        @change="handleCoverChange"
                                        class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-600 hover:file:bg-sky-100 cursor-pointer"
                                    />
                                    <p class="text-[10px] text-slate-400 mt-2">JPG, PNG, WEBP — Max 4MB</p>
                                </div>
                                <div class="space-y-1">
                                    <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300">Cover Alt Text (SEO)</label>
                                    <Input v-model="form.cover_image_alt" placeholder="Descriptive caption for accessibility" />
                                </div>
                            </div>

                            <div class="space-y-4">
                                <!-- Visibility -->
                                <div class="space-y-1">
                                    <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Visibility Level</label>
                                    <select v-model="form.visibility" class="w-full text-xs rounded-xl border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-900 p-2.5 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500">
                                        <option value="public">🌐 Public Access</option>
                                        <option value="members">🔒 Subscribers / Members Only</option>
                                        <option value="private">🔐 Private Draft</option>
                                    </select>
                                </div>

                                <!-- Comments -->
                                <label class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 cursor-pointer hover:border-sky-300 transition">
                                    <input type="checkbox" v-model="form.allow_comments" class="rounded text-sky-600 w-4 h-4" />
                                    <div>
                                        <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 block">Allow Comments</span>
                                        <span class="text-[10px] text-slate-400">Readers can post comments on this article</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- SEO Fields -->
                        <div class="pt-4 border-t border-slate-100 dark:border-zinc-900 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Meta Title (SEO)</label>
                                <Input v-model="form.meta_title" placeholder="Search engine title tag" />
                            </div>
                            <div class="space-y-1">
                                <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Meta Description</label>
                                <textarea
                                    v-model="form.meta_description"
                                    rows="2"
                                    placeholder="Google search snippet description..."
                                    class="w-full text-xs rounded-xl border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-900 p-2.5 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 resize-none"
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>

        </div>
    </MainLayout>
</template>
