<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Button from '@/Components/UI/Button.vue';

interface Preferences {
    email_article_approved: boolean;
    email_article_rejected: boolean;
    email_new_article_from_followed: boolean;
    email_comment_reply: boolean;
    email_new_follower: boolean;
    inapp_article_approved: boolean;
    inapp_article_rejected: boolean;
    inapp_new_article_from_followed: boolean;
    inapp_comment_reply: boolean;
    inapp_new_follower: boolean;
}

const props = defineProps<{
    preferences: Preferences;
}>();

const form = useForm({
    email_article_approved: props.preferences.email_article_approved,
    email_article_rejected: props.preferences.email_article_rejected,
    email_new_article_from_followed: props.preferences.email_new_article_from_followed,
    email_comment_reply: props.preferences.email_comment_reply,
    email_new_follower: props.preferences.email_new_follower,
    inapp_article_approved: props.preferences.inapp_article_approved,
    inapp_article_rejected: props.preferences.inapp_article_rejected,
    inapp_new_article_from_followed: props.preferences.inapp_new_article_from_followed,
    inapp_comment_reply: props.preferences.inapp_comment_reply,
    inapp_new_follower: props.preferences.inapp_new_follower,
});

function submit() {
    form.put('/profile/notifications', {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Notification Preferences — BookVerse">
        <meta name="description" content="Customize your email and in-app notification channels on BookVerse." />
    </Head>

    <MainLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8 font-sans">
            <!-- Header Title Banner -->
            <div class="border-b border-slate-200 dark:border-zinc-800 pb-5">
                <h1 class="text-2xl sm:text-3xl font-black font-heading text-slate-900 dark:text-white flex items-center gap-2">
                    ⚙️ Notification Preferences
                </h1>
                <p class="text-xs text-slate-500 dark:text-zinc-400 mt-1">
                    Control which email and in-app notifications you receive across different channels.
                </p>
            </div>

            <!-- Preferences Form Table Card -->
            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white dark:bg-zinc-950 rounded-3xl border border-slate-200 dark:border-zinc-800 p-6 sm:p-8 space-y-6 shadow-xs">
                    <div class="grid grid-cols-12 font-bold text-xs text-slate-400 uppercase tracking-wider pb-3 border-b border-slate-100 dark:border-zinc-900">
                        <span class="col-span-6">Notification Type</span>
                        <span class="col-span-3 text-center">📧 Email Channel</span>
                        <span class="col-span-3 text-center">🔔 In-App Bell</span>
                    </div>

                    <!-- Row 1: Article Approved -->
                    <div class="grid grid-cols-12 items-center py-2 text-xs text-slate-700 dark:text-slate-300">
                        <div class="col-span-6 space-y-0.5">
                            <span class="font-extrabold text-slate-900 dark:text-white text-sm">🎉 Article Published Live</span>
                            <p class="text-[11px] text-slate-500">Alerts when your submitted article is approved and published.</p>
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.email_article_approved" class="w-4 h-4 rounded text-sky-600 border-slate-300 focus:ring-sky-500" />
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.inapp_article_approved" class="w-4 h-4 rounded text-sky-600 border-slate-300 focus:ring-sky-500" />
                        </div>
                    </div>

                    <!-- Row 2: Article Rejected -->
                    <div class="grid grid-cols-12 items-center py-2 text-xs text-slate-700 dark:text-slate-300 border-t border-slate-100 dark:border-zinc-900">
                        <div class="col-span-6 space-y-0.5">
                            <span class="font-extrabold text-slate-900 dark:text-white text-sm">📝 Article Revisions Requested</span>
                            <p class="text-[11px] text-slate-500">Alerts when editors request revisions on your submission.</p>
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.email_article_rejected" class="w-4 h-4 rounded text-sky-600 border-slate-300 focus:ring-sky-500" />
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.inapp_article_rejected" class="w-4 h-4 rounded text-sky-600 border-slate-300 focus:ring-sky-500" />
                        </div>
                    </div>

                    <!-- Row 3: Followed Author Published -->
                    <div class="grid grid-cols-12 items-center py-2 text-xs text-slate-700 dark:text-slate-300 border-t border-slate-100 dark:border-zinc-900">
                        <div class="col-span-6 space-y-0.5">
                            <span class="font-extrabold text-slate-900 dark:text-white text-sm">✨ Followed Author Published</span>
                            <p class="text-[11px] text-slate-500">Alerts when an author you follow releases a new article.</p>
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.email_new_article_from_followed" class="w-4 h-4 rounded text-sky-600 border-slate-300 focus:ring-sky-500" />
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.inapp_new_article_from_followed" class="w-4 h-4 rounded text-sky-600 border-slate-300 focus:ring-sky-500" />
                        </div>
                    </div>

                    <!-- Row 4: Comment Reply -->
                    <div class="grid grid-cols-12 items-center py-2 text-xs text-slate-700 dark:text-slate-300 border-t border-slate-100 dark:border-zinc-900">
                        <div class="col-span-6 space-y-0.5">
                            <span class="font-extrabold text-slate-900 dark:text-white text-sm">💬 Comment Reply</span>
                            <p class="text-[11px] text-slate-500">Alerts when another reader replies to your comment.</p>
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.email_comment_reply" class="w-4 h-4 rounded text-sky-600 border-slate-300 focus:ring-sky-500" />
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.inapp_comment_reply" class="w-4 h-4 rounded text-sky-600 border-slate-300 focus:ring-sky-500" />
                        </div>
                    </div>

                    <!-- Row 5: New Follower -->
                    <div class="grid grid-cols-12 items-center py-2 text-xs text-slate-700 dark:text-slate-300 border-t border-slate-100 dark:border-zinc-900">
                        <div class="col-span-6 space-y-0.5">
                            <span class="font-extrabold text-slate-900 dark:text-white text-sm">👥 New Follower</span>
                            <p class="text-[11px] text-slate-500">Alerts when a reader starts following your author profile.</p>
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.email_new_follower" class="w-4 h-4 rounded text-sky-600 border-slate-300 focus:ring-sky-500" />
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.inapp_new_follower" class="w-4 h-4 rounded text-sky-600 border-slate-300 focus:ring-sky-500" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <Button variant="brand" size="md" type="submit" :loading="form.processing">
                        Save Preferences
                    </Button>
                </div>
            </form>
        </div>
    </MainLayout>
</template>
