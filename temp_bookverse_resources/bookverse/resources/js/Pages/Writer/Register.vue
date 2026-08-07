<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Input from '@/Components/UI/Input.vue';
import Textarea from '@/Components/UI/Textarea.vue';

const avatarPreview = ref<string | null>(null);
const coverPreview = ref<string | null>(null);

const form = useForm({
    pen_name: '',
    bio: '',
    portfolio_url: '',
    avatar: null as File | null,
    cover_photo: null as File | null,
    social_links: {
        facebook: '',
        twitter: '',
        linkedin: '',
        instagram: '',
    },
});

function handleAvatarChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.avatar = file;
        avatarPreview.value = URL.createObjectURL(file);
    }
}

function handleCoverChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.cover_photo = file;
        coverPreview.value = URL.createObjectURL(file);
    }
}

function submit() {
    form.post('/writer/register', {
        forceFormData: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <MainLayout>
        <Head title="Apply as a Writer — BookVerse E-Magazine" />

        <div class="bg-slate-50/50 min-h-screen py-10">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 font-sans">



                <!-- Registration Form Card -->
                <form @submit.prevent="submit" class="bg-white dark:bg-zinc-950 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-zinc-800 shadow-xs space-y-8">

                    <!-- Cover Photo & Avatar Upload (Overlap Layout) -->
                    <div class="space-y-4">
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Author Profile Visuals</h3>

                        <div class="relative">
                            <!-- Cover Photo Box -->
                            <div class="relative h-40 sm:h-48 rounded-2xl bg-slate-100 dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 flex items-center justify-center overflow-hidden group shadow-inner">
                                <img v-if="coverPreview" :src="coverPreview" class="w-full h-full object-cover" />
                                <div v-else class="text-center p-4">
                                    <span class="text-2xl block mb-1">🖼️</span>
                                    <span class="text-xs font-extrabold text-slate-500">Upload Cover Banner</span>
                                    <span class="block text-[9px] text-slate-400 mt-0.5">Recommended 1200x400 JPG/PNG, max 4MB</span>
                                </div>
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition duration-300 flex items-center justify-center text-white text-xs font-bold gap-1 cursor-pointer">
                                    📷 Change Cover
                                </div>
                                <input type="file" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" @change="handleCoverChange" />
                            </div>

                            <!-- Avatar Box (Overlapping) -->
                            <div class="flex items-end gap-4 -mt-10 px-6 relative z-10">
                                <div class="relative w-20 h-20 rounded-full bg-slate-100 dark:bg-zinc-900 border-4 border-white dark:border-zinc-950 shadow-md flex items-center justify-center overflow-hidden shrink-0 group">
                                    <img v-if="avatarPreview" :src="avatarPreview" class="w-full h-full object-cover" />
                                    <span v-else class="text-2xl">📸</span>
                                    <div class="absolute inset-0 bg-black/45 opacity-0 group-hover:opacity-100 transition duration-300 flex items-center justify-center text-white text-[10px] font-bold cursor-pointer">
                                        Change
                                    </div>
                                    <input type="file" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" @change="handleAvatarChange" />
                                </div>

                                <div class="pb-1 bg-white/90 dark:bg-zinc-950/90 p-1.5 rounded-xl backdrop-blur-xs">
                                    <label class="block text-xs font-extrabold text-slate-800 dark:text-white">Author Profile Avatar</label>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Upload a professional headshot or avatar icon (max 2MB)</p>
                                    <p v-if="form.errors.avatar" class="text-xs text-rose-500 mt-1 font-semibold">{{ form.errors.avatar }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-slate-100 dark:border-zinc-800" />

                    <!-- Basic Author Info -->
                    <div class="space-y-4">
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Author Credentials</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Pen Name / Author Alias <span class="text-rose-500">*</span>
                                </label>
                                <Input v-model="form.pen_name" placeholder="e.g. Humayun Ahmed, Shafiq R." required />
                                <p v-if="form.errors.pen_name" class="text-xs text-rose-500 mt-1 font-semibold">{{ form.errors.pen_name }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Portfolio / Personal Website URL
                                </label>
                                <Input v-model="form.portfolio_url" type="url" placeholder="https://myportfolio.com" />
                                <p v-if="form.errors.portfolio_url" class="text-xs text-rose-500 mt-1 font-semibold">{{ form.errors.portfolio_url }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                Author Bio / Literary Statement <span class="text-rose-500">*</span>
                            </label>
                            <Textarea
                                v-model="form.bio"
                                :rows="4"
                                placeholder="Write a brief intro about yourself, your favorite genres, previous publications, or literary interest..."
                                required
                            />
                            <div class="flex justify-between items-center text-[10px] text-slate-400 mt-1.5 font-semibold">
                                <span>Maximum 2000 characters</span>
                                <span class="font-mono">{{ form.bio.length }}/2000</span>
                            </div>
                            <p v-if="form.errors.bio" class="text-xs text-rose-500 mt-1 font-semibold">{{ form.errors.bio }}</p>
                        </div>
                    </div>

                    <hr class="border-slate-100 dark:border-zinc-800" />

                    <!-- Social Links -->
                    <div class="space-y-4">
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Social Presence (Optional)</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <Input v-model="form.social_links.facebook" placeholder="Facebook profile link" label="Facebook" />
                            <Input v-model="form.social_links.twitter" placeholder="Twitter / X profile link" label="Twitter / X" />
                            <Input v-model="form.social_links.linkedin" placeholder="LinkedIn profile link" label="LinkedIn" />
                            <Input v-model="form.social_links.instagram" placeholder="Instagram handle link" label="Instagram" />
                        </div>
                    </div>

                    <!-- Submission CTA -->
                    <div class="pt-6 border-t border-slate-100 dark:border-zinc-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <p class="text-[11px] text-slate-400 max-w-md font-medium">
                            By submitting this application, you agree to BookVerse author code of ethics and publishing guidelines.
                        </p>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-3 w-full sm:w-auto bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 disabled:opacity-50 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-sky-500/25 transition duration-300 transform active:scale-98 cursor-pointer flex items-center justify-center gap-1.5"
                        >
                            <span>{{ form.processing ? 'Submitting Application...' : 'Submit Writer Application →' }}</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </MainLayout>
</template>
