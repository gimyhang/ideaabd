<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface NotificationSettings {
    email_article_approved: boolean;
    email_article_rejected: boolean;
    email_new_article_from_followed: boolean;
    email_comment_reply: boolean;
    email_new_follower: boolean;
    email_new_order_placed: boolean;
    email_order_delivered: boolean;
    inapp_article_approved: boolean;
    inapp_article_rejected: boolean;
    inapp_new_article_from_followed: boolean;
    inapp_comment_reply: boolean;
    inapp_new_follower: boolean;
    inapp_new_order_placed: boolean;
    inapp_order_delivered: boolean;
}

const props = defineProps<{
    settings: NotificationSettings;
}>();

const form = useForm({
    email_article_approved: props.settings.email_article_approved,
    email_article_rejected: props.settings.email_article_rejected,
    email_new_article_from_followed: props.settings.email_new_article_from_followed,
    email_comment_reply: props.settings.email_comment_reply,
    email_new_follower: props.settings.email_new_follower,
    email_new_order_placed: props.settings.email_new_order_placed,
    email_order_delivered: props.settings.email_order_delivered,
    inapp_article_approved: props.settings.inapp_article_approved,
    inapp_article_rejected: props.settings.inapp_article_rejected,
    inapp_new_article_from_followed: props.settings.inapp_new_article_from_followed,
    inapp_comment_reply: props.settings.inapp_comment_reply,
    inapp_new_follower: props.settings.inapp_new_follower,
    inapp_new_order_placed: props.settings.inapp_new_order_placed,
    inapp_order_delivered: props.settings.inapp_order_delivered,
});

function submit() {
    form.put('/admin/settings/notifications', {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Notification Settings — Admin Panel" />

    <AdminLayout>
        <template #header>
            System Settings
        </template>

        <div class="p-6 sm:p-8 space-y-8 font-sans">
            <!-- Header Title Banner -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-sky-500/10 via-indigo-500/10 to-purple-500/10 dark:from-sky-950/30 dark:via-indigo-950/30 dark:to-purple-950/30 p-6 sm:p-7 rounded-3xl border border-sky-200/50 dark:border-sky-900/30">
                <div class="space-y-1">
                    <h1 class="text-xl sm:text-2xl font-black font-heading text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-2xl bg-sky-500 text-white flex items-center justify-center text-lg shadow-md shadow-sky-500/25">🔔</span>
                        Notification Rules Engine
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 font-mono">
                        Configure automated email triggers & in-app bell notification delivery channels
                    </p>
                </div>
            </div>

            <!-- Sub-Settings Navigation Tabs -->
            <div class="flex items-center bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-1.5 rounded-2xl shadow-xs gap-1 overflow-x-auto">
                <Link
                    href="/admin/settings/notifications"
                    class="px-4 py-2 rounded-xl text-xs font-black transition-all bg-sky-600 text-white shadow-xs"
                >
                    🔔 Notification Rules
                </Link>

                <Link
                    href="/admin/settings/general"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition text-slate-600 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-zinc-800/60"
                >
                    🌐 General & Branding
                </Link>
            </div>

            <!-- Global Notification Rules Table Card -->
            <form @submit.prevent="submit" class="space-y-6 max-w-5xl">
                <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-slate-200/80 dark:border-zinc-800 p-6 sm:p-8 space-y-6 shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800 pb-4">
                        <div>
                            <h2 class="font-extrabold text-base text-slate-900 dark:text-white font-heading">
                                🔔 Global Event Triggers
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-zinc-400 font-mono mt-0.5">
                                Enable or disable email digests & instant bell alerts for platform actions
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-12 font-black text-[11px] text-slate-500 dark:text-zinc-400 uppercase tracking-wider pb-3 border-b border-slate-100 dark:border-zinc-800 font-mono">
                        <span class="col-span-6">System Event Trigger</span>
                        <span class="col-span-3 text-center">📧 Email Channel</span>
                        <span class="col-span-3 text-center">🔔 In-App Bell</span>
                    </div>

                    <!-- Row 1: New Order Placed -->
                    <div class="grid grid-cols-12 items-center py-3 text-xs text-slate-700 dark:text-zinc-300">
                        <div class="col-span-6 space-y-0.5">
                            <span class="font-extrabold text-slate-900 dark:text-white text-sm">🛍️ New Customer Order Placed</span>
                            <p class="text-[11px] text-slate-500 dark:text-zinc-400">Notifies Admin users when a customer places a new order.</p>
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.email_new_order_placed" class="w-4 h-4 rounded text-sky-600 border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500 cursor-pointer" />
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.inapp_new_order_placed" class="w-4 h-4 rounded text-sky-600 border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500 cursor-pointer" />
                        </div>
                    </div>

                    <!-- Row 2: Order Delivered -->
                    <div class="grid grid-cols-12 items-center py-3 text-xs text-slate-700 dark:text-zinc-300 border-t border-slate-100 dark:border-zinc-800/80">
                        <div class="col-span-6 space-y-0.5">
                            <span class="font-extrabold text-slate-900 dark:text-white text-sm">🚚 Order Delivered</span>
                            <p class="text-[11px] text-slate-500 dark:text-zinc-400">Notifies the Customer when their order is marked as delivered.</p>
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.email_order_delivered" class="w-4 h-4 rounded text-sky-600 border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500 cursor-pointer" />
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.inapp_order_delivered" class="w-4 h-4 rounded text-sky-600 border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500 cursor-pointer" />
                        </div>
                    </div>

                    <!-- Row 3: Article Published Live -->
                    <div class="grid grid-cols-12 items-center py-3 text-xs text-slate-700 dark:text-zinc-300 border-t border-slate-100 dark:border-zinc-800/80">
                        <div class="col-span-6 space-y-0.5">
                            <span class="font-extrabold text-slate-900 dark:text-white text-sm">🎉 Article Published Live</span>
                            <p class="text-[11px] text-slate-500 dark:text-zinc-400">Notifies Author when their article is approved and published.</p>
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.email_article_approved" class="w-4 h-4 rounded text-sky-600 border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500 cursor-pointer" />
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.inapp_article_approved" class="w-4 h-4 rounded text-sky-600 border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500 cursor-pointer" />
                        </div>
                    </div>

                    <!-- Row 4: Article Revisions Requested -->
                    <div class="grid grid-cols-12 items-center py-3 text-xs text-slate-700 dark:text-zinc-300 border-t border-slate-100 dark:border-zinc-800/80">
                        <div class="col-span-6 space-y-0.5">
                            <span class="font-extrabold text-slate-900 dark:text-white text-sm">📝 Article Revisions Requested</span>
                            <p class="text-[11px] text-slate-500 dark:text-zinc-400">Notifies Author when editors request article revisions.</p>
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.email_article_rejected" class="w-4 h-4 rounded text-sky-600 border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500 cursor-pointer" />
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.inapp_article_rejected" class="w-4 h-4 rounded text-sky-600 border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500 cursor-pointer" />
                        </div>
                    </div>

                    <!-- Row 5: Followed Author Published -->
                    <div class="grid grid-cols-12 items-center py-3 text-xs text-slate-700 dark:text-zinc-300 border-t border-slate-100 dark:border-zinc-800/80">
                        <div class="col-span-6 space-y-0.5">
                            <span class="font-extrabold text-slate-900 dark:text-white text-sm">✨ Followed Author Published</span>
                            <p class="text-[11px] text-slate-500 dark:text-zinc-400">Notifies Followers when a followed author releases a new article.</p>
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.email_new_article_from_followed" class="w-4 h-4 rounded text-sky-600 border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500 cursor-pointer" />
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.inapp_new_article_from_followed" class="w-4 h-4 rounded text-sky-600 border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500 cursor-pointer" />
                        </div>
                    </div>

                    <!-- Row 6: Comment Reply -->
                    <div class="grid grid-cols-12 items-center py-3 text-xs text-slate-700 dark:text-zinc-300 border-t border-slate-100 dark:border-zinc-800/80">
                        <div class="col-span-6 space-y-0.5">
                            <span class="font-extrabold text-slate-900 dark:text-white text-sm">💬 Comment Reply</span>
                            <p class="text-[11px] text-slate-500 dark:text-zinc-400">Notifies Original Commenter when someone replies to their comment.</p>
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.email_comment_reply" class="w-4 h-4 rounded text-sky-600 border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500 cursor-pointer" />
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.inapp_comment_reply" class="w-4 h-4 rounded text-sky-600 border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500 cursor-pointer" />
                        </div>
                    </div>

                    <!-- Row 7: New Follower -->
                    <div class="grid grid-cols-12 items-center py-3 text-xs text-slate-700 dark:text-zinc-300 border-t border-slate-100 dark:border-zinc-800/80">
                        <div class="col-span-6 space-y-0.5">
                            <span class="font-extrabold text-slate-900 dark:text-white text-sm">👥 New Follower</span>
                            <p class="text-[11px] text-slate-500 dark:text-zinc-400">Notifies Author when a reader starts following their profile.</p>
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.email_new_follower" class="w-4 h-4 rounded text-sky-600 border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500 cursor-pointer" />
                        </div>
                        <div class="col-span-3 flex justify-center">
                            <input type="checkbox" v-model="form.inapp_new_follower" class="w-4 h-4 rounded text-sky-600 border-slate-300 dark:border-zinc-700 dark:bg-zinc-800 focus:ring-sky-500 cursor-pointer" />
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-zinc-800">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white text-xs font-extrabold rounded-2xl shadow-md shadow-sky-500/20 active:scale-95 transition cursor-pointer"
                        >
                            Save Notification Rules
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
