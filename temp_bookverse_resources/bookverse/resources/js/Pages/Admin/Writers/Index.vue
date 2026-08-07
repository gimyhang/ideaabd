<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePermission } from '@/Composables/usePermission';

const { can } = usePermission();
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';
import Textarea from '@/Components/UI/Textarea.vue';
import Input from '@/Components/UI/Input.vue';

interface WriterApplicationItem {
    id: number;
    user_id: number;
    user_name: string;
    user_email: string;
    pen_name: string;
    slug: string;
    bio: string | null;
    avatar_url: string;
    cover_photo_url: string | null;
    portfolio_url: string | null;
    social_links: any;
    status: 'pending' | 'approved' | 'rejected' | 'suspended';
    status_label: string;
    status_badge: 'warning' | 'success' | 'error' | 'default';
    verification_badge: boolean;
    rejection_reason: string | null;
    total_submissions: number;
    total_published: number;
    created_at: string;
}

interface PaginatedApplications {
    data: WriterApplicationItem[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

const props = defineProps<{
    applications: PaginatedApplications;
    counts: {
        all: number;
        pending: number;
        approved: number;
        rejected: number;
        suspended: number;
        verified: number;
    };
    filters: {
        status: string | null;
        search: string | null;
    };
}>();

const activeTab = ref(props.filters.status || 'all');
const searchQuery = ref(props.filters.search || '');
let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;

watch(() => props.filters.search, (newSearch) => {
    if (newSearch !== searchQuery.value) {
        searchQuery.value = newSearch || '';
    }
});

watch(searchQuery, (newVal) => {
    if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        router.get('/admin/writers', {
            status: activeTab.value === 'all' ? '' : activeTab.value,
            search: newVal,
        }, { preserveState: true, replace: true, preserveScroll: true });
    }, 300);
});

// Custom Vue Modal States
const showApproveModal = ref(false);
const showRejectModal = ref(false);
const showSuspendModal = ref(false);
const selectedWriter = ref<WriterApplicationItem | null>(null);

const rejectForm = useForm({
    rejection_reason: '',
});

function handleTabChange(statusKey: string) {
    activeTab.value = statusKey;
    router.get('/admin/writers', {
        status: statusKey === 'all' ? '' : statusKey,
        search: searchQuery.value,
    }, { preserveState: true, replace: true, preserveScroll: true });
}

function handleSearch() {
    router.get('/admin/writers', {
        status: activeTab.value === 'all' ? '' : activeTab.value,
        search: searchQuery.value,
    }, { preserveState: true, replace: true, preserveScroll: true });
}

function openApproveModal(writer: WriterApplicationItem) {
    selectedWriter.value = writer;
    showApproveModal.value = true;
}

function confirmApprove() {
    if (!selectedWriter.value) return;
    router.patch(`/admin/writers/${selectedWriter.value.id}/approve`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            showApproveModal.value = false;
            selectedWriter.value = null;
        },
    });
}

function openRejectModal(writer: WriterApplicationItem) {
    selectedWriter.value = writer;
    rejectForm.rejection_reason = '';
    showRejectModal.value = true;
}

function submitRejection() {
    if (!selectedWriter.value) return;

    rejectForm.patch(`/admin/writers/${selectedWriter.value.id}/reject`, {
        preserveScroll: true,
        onSuccess: () => {
            showRejectModal.value = false;
            selectedWriter.value = null;
        },
    });
}

function toggleVerifyBadge(writer: WriterApplicationItem) {
    router.patch(`/admin/writers/${writer.id}/toggle-verify`, {}, { preserveScroll: true });
}

function openSuspendModal(writer: WriterApplicationItem) {
    selectedWriter.value = writer;
    showSuspendModal.value = true;
}

function confirmSuspend() {
    if (!selectedWriter.value) return;
    router.patch(`/admin/writers/${selectedWriter.value.id}/suspend`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            showSuspendModal.value = false;
            selectedWriter.value = null;
        },
    });
}

function handleAvatarError(e: Event, name: string) {
    const target = e.target as HTMLImageElement;
    const initial = name ? name.trim().charAt(0) : 'B';
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120"><rect width="120" height="120" fill="#0284c7"/><text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#ffffff" font-size="52" font-family="sans-serif" font-weight="bold">${initial}</text></svg>`;
    target.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svg)));
}
</script>

<template>
    <AdminLayout>
        <Head title="Writer Applications & Authors — Admin Portal" />

        <div class="space-y-6 font-sans">

            <!-- Page Title Header -->
            <div class="bg-white dark:bg-zinc-950 p-6 rounded-3xl border border-slate-200 dark:border-zinc-800 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900 dark:text-white font-heading">
                        Writer Applications & Authors Management
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Review customer writer applications, approve published authors, grant verified badges, and moderate portal access.
                    </p>
                </div>

                <!-- Stats Badges Bar -->
                <div class="flex items-center gap-2 flex-wrap text-xs font-bold">
                    <span class="px-3 py-1.5 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                        ⏳ {{ counts.pending }} Pending Approval
                    </span>
                    <span class="px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        ✓ {{ counts.approved }} Approved Authors
                    </span>
                    <span class="px-3 py-1.5 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20">
                        ⭐ {{ counts.verified }} Verified Badges
                    </span>
                </div>
            </div>

            <!-- Filter Tabs Bar & Search -->
            <div class="bg-white dark:bg-zinc-950 p-4 rounded-3xl border border-slate-200 dark:border-zinc-800 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <!-- Tabs -->
                    <div class="flex items-center gap-2 overflow-x-auto w-full sm:w-auto text-xs font-bold">
                        <button
                            @click="handleTabChange('all')"
                            :class="[
                                'px-4 py-2 rounded-xl transition cursor-pointer',
                                activeTab === 'all'
                                    ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900'
                                    : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-zinc-900'
                            ]"
                        >
                            All Applications ({{ counts.all }})
                        </button>

                        <button
                            @click="handleTabChange('pending')"
                            :class="[
                                'px-4 py-2 rounded-xl transition flex items-center gap-1.5 cursor-pointer',
                                activeTab === 'pending'
                                    ? 'bg-amber-500 text-slate-950'
                                    : 'text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/30'
                            ]"
                        >
                            <span>Pending Review</span>
                            <span class="px-1.5 py-0.5 rounded-full bg-amber-600 text-white text-[10px]">{{ counts.pending }}</span>
                        </button>

                        <button
                            @click="handleTabChange('approved')"
                            :class="[
                                'px-4 py-2 rounded-xl transition cursor-pointer',
                                activeTab === 'approved'
                                    ? 'bg-emerald-600 text-white'
                                    : 'text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/30'
                            ]"
                        >
                            Approved Authors ({{ counts.approved }})
                        </button>

                        <button
                            @click="handleTabChange('rejected')"
                            :class="[
                                'px-4 py-2 rounded-xl transition cursor-pointer',
                                activeTab === 'rejected'
                                    ? 'bg-rose-600 text-white'
                                    : 'text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30'
                            ]"
                        >
                            Rejected ({{ counts.rejected }})
                        </button>

                        <button
                            @click="handleTabChange('suspended')"
                            :class="[
                                'px-4 py-2 rounded-xl transition cursor-pointer',
                                activeTab === 'suspended'
                                    ? 'bg-slate-700 text-white'
                                    : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-zinc-900'
                            ]"
                        >
                            Suspended ({{ counts.suspended }})
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="w-full sm:w-64">
                        <Input
                            v-model="searchQuery"
                            placeholder="Search by pen name or email..."
                            @keyup.enter="handleSearch"
                        />
                    </div>
                </div>
            </div>

            <!-- Applications Table Card -->
            <div class="bg-white dark:bg-zinc-950 rounded-3xl border border-slate-200 dark:border-zinc-800 overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-zinc-900 text-slate-500 uppercase tracking-wider font-bold text-[10px] border-b border-slate-200 dark:border-zinc-800">
                            <tr>
                                <th class="p-4">Author Applicant</th>
                                <th class="p-4">Bio & Portfolio</th>
                                <th class="p-4">Status</th>
                                <th class="p-4">Verified Badge</th>
                                <th class="p-4">Submitted</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-zinc-900">
                            <tr
                                v-for="writer in applications.data"
                                :key="writer.id"
                                class="hover:bg-slate-50/50 dark:hover:bg-zinc-900/50 transition"
                            >
                                <!-- Applicant Info -->
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <img
                                            :src="writer.avatar_url"
                                            :alt="writer.pen_name"
                                            @error="handleAvatarError($event, writer.pen_name)"
                                            class="w-10 h-10 rounded-full object-cover border border-slate-200 dark:border-zinc-800 shrink-0"
                                        />
                                        <div>
                                            <span class="font-bold text-slate-900 dark:text-white block text-sm">
                                                {{ writer.pen_name }}
                                            </span>
                                            <span class="text-[11px] text-slate-500 block">
                                                User: {{ writer.user_name }} ({{ writer.user_email }})
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Bio & Portfolio -->
                                <td class="p-4 max-w-xs">
                                    <p class="line-clamp-2 text-slate-600 dark:text-slate-400">
                                        {{ writer.bio || 'No bio provided' }}
                                    </p>
                                    <a
                                        v-if="writer.portfolio_url"
                                        :href="writer.portfolio_url"
                                        target="_blank"
                                        class="text-sky-600 hover:underline text-[11px] font-semibold mt-1 inline-block"
                                    >
                                        🔗 Portfolio Link ↗
                                    </a>
                                </td>

                                <!-- Status Badge -->
                                <td class="p-4">
                                    <Badge :variant="writer.status_badge">
                                        {{ writer.status_label }}
                                    </Badge>
                                    <p v-if="writer.rejection_reason" class="text-[10px] text-rose-500 mt-1 max-w-xs italic line-clamp-1">
                                        Reason: {{ writer.rejection_reason }}
                                    </p>
                                </td>

                                <!-- Verified Badge Toggle -->
                                <td class="p-4">
                                    <button
                                        v-if="can('approve-writers')"
                                        @click="toggleVerifyBadge(writer)"
                                        :class="[
                                            'px-2.5 py-1 rounded-full text-[11px] font-bold border transition cursor-pointer flex items-center gap-1',
                                            writer.verification_badge
                                                ? 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-400/30'
                                                : 'bg-slate-100 dark:bg-zinc-900 text-slate-400 border-slate-200 dark:border-zinc-800 hover:text-sky-600'
                                        ]"
                                    >
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                        </svg>
                                        <span>{{ writer.verification_badge ? '✓ Verified' : '+ Add Badge' }}</span>
                                    </button>
                                </td>

                                <!-- Submitted Date -->
                                <td class="p-4 text-slate-500 text-[11px] whitespace-nowrap">
                                    {{ writer.created_at }}
                                </td>

                                <!-- Actions Bar -->
                                <td class="p-4 text-right whitespace-nowrap space-x-2">
                                    <!-- Approve Button -->
                                    <button
                                        v-if="writer.status !== 'approved' && can('approve-writers')"
                                        @click="openApproveModal(writer)"
                                        class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-sm transition cursor-pointer"
                                    >
                                        Approve
                                    </button>

                                    <!-- Reject Button -->
                                    <button
                                        v-if="writer.status === 'pending' && can('approve-writers')"
                                        @click="openRejectModal(writer)"
                                        class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-[11px] shadow-sm transition cursor-pointer"
                                    >
                                        Reject
                                    </button>

                                    <!-- Suspend / Re-activate Button -->
                                    <button
                                        v-if="(writer.status === 'approved' || writer.status === 'suspended') && can('suspend-writers')"
                                        @click="openSuspendModal(writer)"
                                        :class="[
                                            'px-3 py-1.5 rounded-xl font-bold text-[11px] transition cursor-pointer',
                                            writer.status === 'suspended'
                                                ? 'bg-amber-600 hover:bg-amber-700 text-white'
                                                : 'bg-slate-200 dark:bg-zinc-800 hover:bg-rose-100 text-slate-700 dark:text-slate-300'
                                        ]"
                                    >
                                        {{ writer.status === 'suspended' ? 'Re-activate' : 'Suspend' }}
                                    </button>
                                </td>
                            </tr>

                            <!-- Empty Applications State -->
                            <tr v-if="applications.data.length === 0">
                                <td colspan="6" class="p-12 text-center text-slate-500 text-xs">
                                    No writer applications found matching current status filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Custom Vue Approve Confirmation Modal -->
            <Modal
                :show="showApproveModal"
                title="Approve Writer Application"
                maxWidth="md"
                @close="showApproveModal = false"
            >
                <div class="space-y-4">
                    <div class="flex items-center gap-3 p-3 bg-emerald-50 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200 dark:border-emerald-900">
                        <img
                            v-if="selectedWriter"
                            :src="selectedWriter.avatar_url"
                            :alt="selectedWriter.pen_name"
                            @error="handleAvatarError($event, selectedWriter.pen_name)"
                            class="w-12 h-12 rounded-full object-cover border border-emerald-300 dark:border-emerald-800 shrink-0"
                        />
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white text-sm">
                                {{ selectedWriter?.pen_name }}
                            </h4>
                            <p class="text-xs text-slate-500">
                                User: {{ selectedWriter?.user_name }} ({{ selectedWriter?.user_email }})
                            </p>
                        </div>
                    </div>

                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Are you sure you want to approve this writer application? The applicant will receive writer workspace permissions and will be able to submit articles to BookVerse E-Magazine.
                    </p>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-zinc-800">
                        <Button variant="secondary" size="sm" type="button" @click="showApproveModal = false">Cancel</Button>
                        <Button variant="brand" size="sm" type="button" @click="confirmApprove">
                            ✓ Confirm Approval
                        </Button>
                    </div>
                </div>
            </Modal>

            <!-- Custom Vue Rejection Reason Modal -->
            <Modal
                :show="showRejectModal"
                title="Reject Writer Application"
                maxWidth="md"
                @close="showRejectModal = false"
            >
                <form @submit.prevent="submitRejection" class="space-y-4">
                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        Rejecting application for <strong class="text-slate-900 dark:text-white">{{ selectedWriter?.pen_name }}</strong>. Please provide a clear rejection reason for the applicant.
                    </p>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Rejection Reason / Feedback <span class="text-rose-500">*</span>
                        </label>
                        <Textarea
                            v-model="rejectForm.rejection_reason"
                            :rows="3"
                            placeholder="e.g. Portfolio links provided were incomplete or bio does not meet literary criteria."
                            required
                        />
                        <p v-if="rejectForm.errors.rejection_reason" class="text-xs text-rose-500 mt-1">{{ rejectForm.errors.rejection_reason }}</p>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-zinc-800">
                        <Button variant="secondary" size="sm" type="button" @click="showRejectModal = false">Cancel</Button>
                        <Button variant="destructive" size="sm" type="submit" :loading="rejectForm.processing">
                            Confirm Rejection
                        </Button>
                    </div>
                </form>
            </Modal>

            <!-- Custom Vue Suspend / Re-activate Modal -->
            <Modal
                :show="showSuspendModal"
                title="Writer Account Status Action"
                maxWidth="md"
                @close="showSuspendModal = false"
            >
                <div class="space-y-4">
                    <div class="flex items-center gap-3 p-3 bg-slate-100 dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800">
                        <img
                            v-if="selectedWriter"
                            :src="selectedWriter.avatar_url"
                            :alt="selectedWriter.pen_name"
                            @error="handleAvatarError($event, selectedWriter.pen_name)"
                            class="w-12 h-12 rounded-full object-cover border border-slate-300 shrink-0"
                        />
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-white text-sm">
                                {{ selectedWriter?.pen_name }}
                            </h4>
                            <p class="text-xs text-slate-500">
                                Current Status: <span class="font-bold uppercase">{{ selectedWriter?.status }}</span>
                            </p>
                        </div>
                    </div>

                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Are you sure you want to {{ selectedWriter?.status === 'suspended' ? 're-activate author access' : 'suspend author access' }} for <strong class="text-slate-900 dark:text-white">{{ selectedWriter?.pen_name }}</strong>?
                    </p>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-200 dark:border-zinc-800">
                        <Button variant="secondary" size="sm" type="button" @click="showSuspendModal = false">Cancel</Button>
                        <Button
                            :variant="selectedWriter?.status === 'suspended' ? 'brand' : 'destructive'"
                            size="sm"
                            type="button"
                            @click="confirmSuspend"
                        >
                            {{ selectedWriter?.status === 'suspended' ? 'Confirm Re-activation' : 'Confirm Suspension' }}
                        </Button>
                    </div>
                </div>
            </Modal>

        </div>
    </AdminLayout>
</template>
