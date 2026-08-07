<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SettingField from '@/Components/Admin/SettingField.vue';

interface BookItem {
    id: number;
    title: string;
}

interface GeneralSettings {
    site_name: string;
    site_tagline?: string;
    support_email: string;
    support_phone?: string;
    logo_url?: string;
}

interface SeoSettings {
    meta_title: string;
    meta_description?: string;
    google_analytics_id?: string;
    robots_txt_rule?: string;
}

interface HomepageSettings {
    headline_text: string;
    promo_banner_text?: string;
    subheadline_text?: string;
    search_placeholder?: string;
    search_button_text?: string;
    hero_bg_image_url?: string;
    enable_hero_overlay: boolean;
    hero_overlay_color?: string;
    hero_overlay_opacity?: number | string;
    show_featured_book_card: boolean;
}

interface NotificationSettings {
    notify_order_mail: boolean;
    notify_order_icon: boolean;
    notify_shipping_mail: boolean;
    notify_shipping_icon: boolean;
    notify_writer_mail: boolean;
    notify_writer_icon: boolean;
    notify_review_mail: boolean;
    notify_review_icon: boolean;
    notify_user_mail: boolean;
    notify_user_icon: boolean;
    notify_stock_mail: boolean;
    notify_stock_icon: boolean;
}

interface PaymentSettings {
    enable_cod: boolean;
    enable_bkash: boolean;
    bkash_app_key?: string;
    bkash_app_secret?: string;
    bkash_username?: string;
    bkash_password?: string;
    bkash_mode?: string;
    enable_nagad: boolean;
    nagad_merchant_id?: string;
    nagad_public_key?: string;
    nagad_private_key?: string;
    nagad_mode?: string;
    enable_bank: boolean;
    bank_name?: string;
    bank_account_name?: string;
    bank_account_number?: string;
    bank_routing_number?: string;
    enable_stripe: boolean;
    stripe_key?: string;
    stripe_secret?: string;
    stripe_webhook_secret?: string;
}

interface ShippingSettings {
    inside_dhaka: number;
    outside_dhaka: number;
    express_delivery: number;
    free_shipping_threshold: number;
}

interface SettingsPayload {
    general: GeneralSettings;
    seo: SeoSettings;
    homepage: HomepageSettings;
    payments: PaymentSettings;
    shipping: ShippingSettings;
    notifications?: NotificationSettings;
}

const props = defineProps<{
    settings: SettingsPayload;
    booksList: BookItem[];
}>();

const getInitialTab = () => {
    if (typeof window !== 'undefined') {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab && ['hero', 'shipping', 'payments', 'general', 'seo', 'notifications'].includes(tab)) {
            return tab as any;
        }
    }
    return 'general';
};

const activeTab = ref<'hero' | 'shipping' | 'payments' | 'general' | 'seo' | 'notifications'>(getInitialTab());

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab && ['hero', 'shipping', 'payments', 'general', 'seo', 'notifications'].includes(tab)) {
        activeTab.value = tab as any;
    }
});

function switchTab(t: any) {
    activeTab.value = t;
    if (typeof window !== 'undefined') {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', t);
        window.history.pushState({}, '', url.toString());
    }
}

// 1. General settings form
const generalForm = useForm({
    site_name: props.settings.general.site_name,
    site_tagline: props.settings.general.site_tagline || '',
    support_email: props.settings.general.support_email,
    support_phone: props.settings.general.support_phone || '',
    logo_url: props.settings.general.logo_url || '',
    logo: null as File | null,
});

// 2. SEO settings form
const seoForm = useForm({
    meta_title: props.settings.seo.meta_title,
    meta_description: props.settings.seo.meta_description || '',
    google_analytics_id: props.settings.seo.google_analytics_id || '',
    robots_txt_rule: props.settings.seo.robots_txt_rule || '',
});

// 3. Hero Banner settings form
const heroForm = useForm({
    show_featured_book_card: props.settings.homepage.show_featured_book_card ?? true,
    headline_text: props.settings.homepage.headline_text || 'আপনার পছন্দের বইটি খুঁজুন বুকভার্স-এ',
    promo_banner_text: props.settings.homepage.promo_banner_text || '📚 বাংলাদেশের বৃহত্তম ডিজিটাল বুক স্টোর',
    subheadline_text: props.settings.homepage.subheadline_text || 'হাজার হাজার গল্প, উপন্যাস, কবিতা ও সাহিত্য ম্যাগাজিনের এক সুবিশাল সংগ্রহশালা। আজই পড়ুন ডিজিটাল অথবা অর্ডার করুন প্রিন্টেড কপি।',
    search_placeholder: props.settings.homepage.search_placeholder || 'বইয়ের নাম, লেখক বা বিষয় দিয়ে খুঁজুন...',
    search_button_text: props.settings.homepage.search_button_text || 'খুঁজুন',
    hero_bg_image_url: props.settings.homepage.hero_bg_image_url || '',
    hero_bg_image: null as File | null,
    enable_hero_overlay: props.settings.homepage.enable_hero_overlay ?? true,
    hero_overlay_color: props.settings.homepage.hero_overlay_color || '#0f172a',
    hero_overlay_opacity: props.settings.homepage.hero_overlay_opacity ?? 85,
});

// 4. Payments settings form
const paymentsForm = useForm({
    enable_cod: props.settings.payments.enable_cod ?? true,
    enable_bkash: props.settings.payments.enable_bkash ?? false,
    bkash_app_key: props.settings.payments.bkash_app_key || '',
    bkash_app_secret: props.settings.payments.bkash_app_secret || '',
    bkash_username: props.settings.payments.bkash_username || '',
    bkash_password: props.settings.payments.bkash_password || '',
    bkash_mode: props.settings.payments.bkash_mode || 'sandbox',
    enable_nagad: props.settings.payments.enable_nagad ?? false,
    nagad_merchant_id: props.settings.payments.nagad_merchant_id || '',
    nagad_public_key: props.settings.payments.nagad_public_key || '',
    nagad_private_key: props.settings.payments.nagad_private_key || '',
    nagad_mode: props.settings.payments.nagad_mode || 'sandbox',
    enable_bank: props.settings.payments.enable_bank ?? false,
    bank_name: props.settings.payments.bank_name || '',
    bank_account_name: props.settings.payments.bank_account_name || '',
    bank_account_number: props.settings.payments.bank_account_number || '',
    bank_routing_number: props.settings.payments.bank_routing_number || '',
    enable_stripe: props.settings.payments.enable_stripe ?? false,
    stripe_key: props.settings.payments.stripe_key || '',
    stripe_secret: props.settings.payments.stripe_secret || '',
    stripe_webhook_secret: props.settings.payments.stripe_webhook_secret || '',
});

// 5. Shipping / Delivery settings form
const shippingForm = useForm({
    inside_dhaka: props.settings.shipping?.inside_dhaka ?? 60,
    outside_dhaka: props.settings.shipping?.outside_dhaka ?? 120,
    express_delivery: props.settings.shipping?.express_delivery ?? 150,
    free_shipping_threshold: props.settings.shipping?.free_shipping_threshold ?? 1000,
});

// 6. Notification Rules & Channels settings form
const notificationsForm = useForm({
    notify_order_mail: props.settings.notifications?.notify_order_mail ?? true,
    notify_order_icon: props.settings.notifications?.notify_order_icon ?? true,
    notify_shipping_mail: props.settings.notifications?.notify_shipping_mail ?? true,
    notify_shipping_icon: props.settings.notifications?.notify_shipping_icon ?? true,
    notify_writer_mail: props.settings.notifications?.notify_writer_mail ?? true,
    notify_writer_icon: props.settings.notifications?.notify_writer_icon ?? true,
    notify_review_mail: props.settings.notifications?.notify_review_mail ?? false,
    notify_review_icon: props.settings.notifications?.notify_review_icon ?? true,
    notify_user_mail: props.settings.notifications?.notify_user_mail ?? true,
    notify_user_icon: props.settings.notifications?.notify_user_icon ?? true,
    notify_stock_mail: props.settings.notifications?.notify_stock_mail ?? true,
    notify_stock_icon: props.settings.notifications?.notify_stock_icon ?? true,
});

function submitGeneral() {
    generalForm.post('/admin/settings/general', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            generalForm.logo = null;
        }
    });
}

function submitSeo() {
    seoForm.put('/admin/settings/seo', {
        preserveScroll: true,
    });
}

function submitHero() {
    heroForm.post('/admin/settings/homepage', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            heroForm.hero_bg_image = null;
        }
    });
}

function submitPayments() {
    paymentsForm.put('/admin/settings/payments', {
        preserveScroll: true,
    });
}

function submitShipping() {
    shippingForm.put('/admin/settings/shipping', {
        preserveScroll: true,
    });
}

function submitNotifications() {
    notificationsForm.post('/admin/settings/notifications', {
        preserveScroll: true,
    });
}

function handleLogoChange(file: File) {
    generalForm.logo = file;
    generalForm.logo_url = URL.createObjectURL(file);
}

function handleHeroBgImageChange(file: File) {
    heroForm.hero_bg_image = file;
    heroForm.hero_bg_image_url = URL.createObjectURL(file);
}

const verticalTabs = [
    { id: 'general', label: 'General Settings', icon: '⚙️' },
    { id: 'hero', label: 'Hero Banner Settings', icon: '✨' },
    { id: 'shipping', label: 'Delivery Charges', icon: '🚚' },
    { id: 'payments', label: 'Payment Gateways', icon: '💳' },
    { id: 'seo', label: 'SEO & Analytics', icon: '🔍' },
    { id: 'notifications', label: 'Notification Rules', icon: '🔔' },
];
</script>

<template>
    <Head title="System Settings Control Panel — BookVerse" />

    <AdminLayout>
        <template #header>
            Settings
        </template>

        <div class="p-6 sm:p-8 space-y-6 font-sans w-full">
            <!-- Settings Page Main Title -->
            <div class="pb-3 flex items-center justify-between border-b border-slate-200 dark:border-zinc-800">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white font-heading tracking-tight">
                        Settings
                    </h1>
                    <p class="text-sm sm:text-base text-slate-500 dark:text-zinc-400 font-semibold mt-1">
                        Manage global platform branding, homepage hero text, delivery rates, payment gateways & SEO
                    </p>
                </div>
            </div>

            <!-- Main Split Layout: Left Vertical Tabs + Right Form Area -->
            <div class="flex flex-col md:flex-row gap-6 items-start">
                
                <!-- Left Vertical Navigation Bar -->
                <div class="w-full md:w-64 lg:w-72 shrink-0 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800/80 rounded-2xl p-3 shadow-xs space-y-1">
                    <button
                        v-for="t in verticalTabs"
                        :key="t.id"
                        @click="switchTab(t.id)"
                        class="w-full text-left px-4 py-3.5 rounded-xl text-sm sm:text-base font-extrabold transition-all cursor-pointer flex items-center justify-between group active:scale-[0.98]"
                        :class="activeTab === t.id 
                            ? 'bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800/60 font-black shadow-xs' 
                            : 'text-slate-600 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-zinc-800/50'"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-lg leading-none">{{ t.icon }}</span>
                            <span class="font-sans tracking-wide">{{ t.label }}</span>
                        </div>
                        <span v-if="activeTab === t.id" class="w-2.5 h-2.5 rounded-full bg-sky-500 shadow-xs"></span>
                    </button>
                </div>

                <!-- Right Form Content Card Area -->
                <div class="flex-1 w-full bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800/80 rounded-2xl p-6 sm:p-8 shadow-xs">
                    
                    <!-- 1. GENERAL SETTINGS FORM -->
                    <form v-if="activeTab === 'general'" @submit.prevent="submitGeneral" class="space-y-7">
                        <div class="border-b border-slate-200 dark:border-zinc-800 pb-4">
                            <h3 class="font-black text-xl text-slate-900 dark:text-white font-heading flex items-center gap-2">
                                ⚙️ General Settings
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-zinc-400 font-medium mt-1">
                                Configure platform name, tagline, support contact details & site logo
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <SettingField label="Platform Name" v-model="generalForm.site_name" />
                            <SettingField label="Tagline Description" v-model="generalForm.site_tagline" />
                            <SettingField label="Support Email Address" v-model="generalForm.support_email" />
                            <SettingField label="Contact Hotline" v-model="generalForm.support_phone" />
                            <div class="md:col-span-2">
                                <SettingField label="System Logo Asset" type="file" v-model="generalForm.logo_url" @change-file="handleLogoChange" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-200 dark:border-zinc-800">
                            <button
                                type="submit"
                                :disabled="generalForm.processing"
                                class="px-8 py-3.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white text-sm font-extrabold rounded-2xl shadow-md shadow-sky-500/25 active:scale-95 transition cursor-pointer uppercase tracking-wider"
                            >
                                Save General Settings
                            </button>
                        </div>
                    </form>

                    <!-- 2. HERO BANNER SETTINGS FORM -->
                    <form v-if="activeTab === 'hero'" @submit.prevent="submitHero" class="space-y-8">
                        <div class="border-b border-slate-200 dark:border-zinc-800 pb-4">
                            <h3 class="font-black text-xl text-slate-900 dark:text-white font-heading flex items-center gap-2">
                                ✨ Hero Banner Settings
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-zinc-400 font-medium mt-1">
                                Manage landing hero text, 3D bestseller book card toggle, background banner & color overlay
                            </p>
                        </div>

                        <div class="space-y-8">
                            <!-- Section 1: Single Switch to Show/Hide Hero Book Card -->
                            <div class="bg-slate-50 dark:bg-zinc-950 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 space-y-4">
                                <h4 class="font-black text-base text-slate-900 dark:text-white uppercase tracking-wider font-mono flex items-center gap-2">
                                    <span>📚</span>
                                    <span>Hero Bestseller Book Card</span>
                                </h4>
                                <SettingField
                                    label="Show Hero Bestseller Book Card"
                                    helpText="Active = Displays top bestseller book card on the homepage hero. Off = Hides the book card."
                                    type="switch"
                                    v-model="heroForm.show_featured_book_card"
                                />
                            </div>

                            <!-- Section 2: Banner Text Content -->
                            <div class="bg-slate-50 dark:bg-zinc-950 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 space-y-6">
                                <h4 class="font-black text-base text-slate-900 dark:text-white uppercase tracking-wider font-mono flex items-center gap-2">
                                    <span>📝</span>
                                    <span>Hero Banner Headlines & Text Content</span>
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <SettingField
                                        label="Top Badge Tagline"
                                        helpText="Small pill tag above main headline"
                                        v-model="heroForm.promo_banner_text"
                                    />
                                    <SettingField
                                        label="Main Hero Headline"
                                        helpText="Large title heading"
                                        v-model="heroForm.headline_text"
                                    />
                                    <div class="md:col-span-2">
                                        <SettingField
                                            label="Sub-Headline Description Paragraph"
                                            helpText="Detailed description paragraph underneath hero title"
                                            type="textarea"
                                            v-model="heroForm.subheadline_text"
                                        />
                                    </div>
                                    <SettingField
                                        label="Search Input Placeholder"
                                        helpText="Text inside hero search box"
                                        v-model="heroForm.search_placeholder"
                                    />
                                    <SettingField
                                        label="Search Button Text"
                                        helpText="Hero search action button text"
                                        v-model="heroForm.search_button_text"
                                    />
                                </div>
                            </div>

                            <!-- Section 3: Background Image & Tint Layer -->
                            <div class="bg-slate-50 dark:bg-zinc-950 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 space-y-6">
                                <h4 class="font-black text-base text-slate-900 dark:text-white uppercase tracking-wider font-mono flex items-center gap-2">
                                    <span>🎨</span>
                                    <span>Background Image & Color Overlay Tint</span>
                                </h4>

                                <SettingField
                                    label="Full Hero Background Image"
                                    helpText="Upload a background image that spans across the entire hero section"
                                    type="file"
                                    v-model="heroForm.hero_bg_image_url"
                                    @change-file="handleHeroBgImageChange"
                                />

                                <SettingField
                                    label="Enable Background Dark Overlay Tint Layer"
                                    helpText="Active = applies dark color tint over background image for contrast. Off = background image displays directly."
                                    type="switch"
                                    v-model="heroForm.enable_hero_overlay"
                                />

                                <div v-if="heroForm.enable_hero_overlay" class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-2 border-t border-slate-200 dark:border-zinc-800/80">
                                    <SettingField
                                        label="Overlay Tint Layer Color"
                                        helpText="Click color box to choose any custom tint color"
                                        type="color"
                                        v-model="heroForm.hero_overlay_color"
                                    />
                                    <SettingField
                                        label="Overlay Opacity Percentage (%)"
                                        helpText="Slide to control tint darkness opacity level"
                                        type="range"
                                        :min="10"
                                        :max="95"
                                        :step="5"
                                        v-model="heroForm.hero_overlay_opacity"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-5 border-t border-slate-200 dark:border-zinc-800">
                            <button
                                type="submit"
                                :disabled="heroForm.processing"
                                class="px-8 py-3.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white text-sm font-extrabold rounded-2xl shadow-md shadow-sky-500/25 active:scale-95 transition cursor-pointer uppercase tracking-wider"
                            >
                                Save Hero Banner Settings
                            </button>
                        </div>
                    </form>

                    <!-- 3. SHIPPING / DELIVERY CHARGES FORM -->
                    <form v-if="activeTab === 'shipping'" @submit.prevent="submitShipping" class="space-y-8">
                        <div class="border-b border-slate-200 dark:border-zinc-800 pb-4">
                            <h3 class="font-black text-xl text-slate-900 dark:text-white font-heading flex items-center gap-2">
                                🚚 Delivery Charges
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-zinc-400 font-medium mt-1">
                                Set shipping rates (BDT ৳) for Inside Dhaka, Outside Dhaka, Express Courier & Free Delivery Threshold
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <SettingField
                                label="Inside Dhaka Delivery Charge (৳)"
                                helpText="Shipping fee for Dhaka Division orders (e.g. ৳60)"
                                v-model="shippingForm.inside_dhaka"
                            />

                            <SettingField
                                label="Outside Dhaka Delivery Charge (৳)"
                                helpText="Shipping fee for all other divisions & districts (e.g. ৳120)"
                                v-model="shippingForm.outside_dhaka"
                            />

                            <SettingField
                                label="Express Fast Delivery Charge (৳)"
                                helpText="Same-day / Fast express delivery option rate (e.g. ৳150)"
                                v-model="shippingForm.express_delivery"
                            />

                            <SettingField
                                label="Free Shipping Target Amount (৳)"
                                helpText="Orders exceeding this subtotal get 100% FREE delivery (e.g. ৳1000)"
                                v-model="shippingForm.free_shipping_threshold"
                            />
                        </div>

                        <div class="flex items-center justify-end pt-5 border-t border-slate-200 dark:border-zinc-800">
                            <button
                                type="submit"
                                :disabled="shippingForm.processing"
                                class="px-8 py-3.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white text-sm font-extrabold rounded-2xl shadow-md shadow-sky-500/25 active:scale-95 transition cursor-pointer uppercase tracking-wider"
                            >
                                Save Delivery Charges
                            </button>
                        </div>
                    </form>

                    <!-- 4. PAYMENTS TAB (2-Column Grid Layout) -->
                    <form v-if="activeTab === 'payments'" @submit.prevent="submitPayments" class="space-y-8">
                        <div class="border-b border-slate-200 dark:border-zinc-800 pb-4">
                            <h3 class="font-black text-xl text-slate-900 dark:text-white font-heading flex items-center gap-2">
                                💳 Payment Gateways
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-zinc-400 font-medium mt-1">
                                Select enabled checkout payment methods and configure merchant API keys
                            </p>
                        </div>

                        <!-- 2-Column Grid for Payment Gateways Cards -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                            <!-- 1. Cash On Delivery (COD) -->
                            <div class="bg-slate-50 dark:bg-zinc-950 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="w-10 h-10 rounded-2xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-400 font-bold flex items-center justify-center text-base shadow-xs">💵</span>
                                        <div>
                                            <h4 class="font-bold text-base text-slate-900 dark:text-white">Cash On Delivery (COD)</h4>
                                            <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 font-medium">Hand-to-hand cash payment upon order delivery</p>
                                        </div>
                                    </div>
                                    <SettingField label="" type="switch" v-model="paymentsForm.enable_cod" />
                                </div>
                            </div>

                            <!-- 2. bKash Mobile Banking -->
                            <div class="bg-slate-50 dark:bg-zinc-950 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 space-y-4">
                                <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-800 pb-3">
                                    <div class="flex items-center gap-3">
                                        <span class="w-10 h-10 rounded-2xl bg-pink-100 dark:bg-pink-950/80 text-pink-700 dark:text-pink-400 font-bold flex items-center justify-center text-sm shadow-xs">📱</span>
                                        <div>
                                            <h4 class="font-bold text-base text-slate-900 dark:text-white">bKash Gateway</h4>
                                            <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 font-medium">bKash Online Tokenized PGW API</p>
                                        </div>
                                    </div>
                                    <SettingField label="" type="switch" v-model="paymentsForm.enable_bkash" />
                                </div>

                                <div v-if="paymentsForm.enable_bkash" class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                    <SettingField
                                        label="Environment Mode"
                                        type="select"
                                        :options="[
                                            { value: 'sandbox', label: 'Sandbox / Testing' },
                                            { value: 'live', label: 'Live / Production' }
                                        ]"
                                        v-model="paymentsForm.bkash_mode"
                                    />
                                    <SettingField label="bKash API Username" v-model="paymentsForm.bkash_username" />
                                    <SettingField label="bKash App Key" v-model="paymentsForm.bkash_app_key" />
                                    <SettingField label="bKash App Secret" v-model="paymentsForm.bkash_app_secret" />
                                    <div class="md:col-span-2">
                                        <SettingField label="bKash API Password" v-model="paymentsForm.bkash_password" />
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Nagad Mobile Banking -->
                            <div class="bg-slate-50 dark:bg-zinc-950 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 space-y-4">
                                <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-800 pb-3">
                                    <div class="flex items-center gap-3">
                                        <span class="w-10 h-10 rounded-2xl bg-orange-100 dark:bg-orange-950/80 text-orange-700 dark:text-orange-400 font-bold flex items-center justify-center text-sm shadow-xs">📱</span>
                                        <div>
                                            <h4 class="font-bold text-base text-slate-900 dark:text-white">Nagad Gateway</h4>
                                            <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 font-medium">Nagad Merchant Payment API</p>
                                        </div>
                                    </div>
                                    <SettingField label="" type="switch" v-model="paymentsForm.enable_nagad" />
                                </div>

                                <div v-if="paymentsForm.enable_nagad" class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                    <SettingField
                                        label="Environment Mode"
                                        type="select"
                                        :options="[
                                            { value: 'sandbox', label: 'Sandbox / Testing' },
                                            { value: 'live', label: 'Live / Production' }
                                        ]"
                                        v-model="paymentsForm.nagad_mode"
                                    />
                                    <SettingField label="Nagad Merchant ID" v-model="paymentsForm.nagad_merchant_id" />
                                    <div class="md:col-span-2">
                                        <SettingField label="Nagad Public Key" type="textarea" v-model="paymentsForm.nagad_public_key" />
                                    </div>
                                    <div class="md:col-span-2">
                                        <SettingField label="Nagad Private Key" type="textarea" v-model="paymentsForm.nagad_private_key" />
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Direct Bank Transfer -->
                            <div class="bg-slate-50 dark:bg-zinc-950 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 space-y-4">
                                <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-800 pb-3">
                                    <div class="flex items-center gap-3">
                                        <span class="w-10 h-10 rounded-2xl bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-400 font-bold flex items-center justify-center text-sm shadow-xs">🏦</span>
                                        <div>
                                            <h4 class="font-bold text-base text-slate-900 dark:text-white">Direct Bank Wire / Transfer</h4>
                                            <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 font-medium">Direct bank deposit & EFT transfers</p>
                                        </div>
                                    </div>
                                    <SettingField label="" type="switch" v-model="paymentsForm.enable_bank" />
                                </div>

                                <div v-if="paymentsForm.enable_bank" class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                    <SettingField label="Bank Name" helpText="e.g. DBBL / City Bank" v-model="paymentsForm.bank_name" />
                                    <SettingField label="Account Title" helpText="e.g. BookVerse Ltd" v-model="paymentsForm.bank_account_name" />
                                    <SettingField label="Account Number" v-model="paymentsForm.bank_account_number" />
                                    <SettingField label="Routing Number" v-model="paymentsForm.bank_routing_number" />
                                </div>
                            </div>

                            <!-- 5. Stripe Credit & Debit Cards -->
                            <div class="bg-slate-50 dark:bg-zinc-950 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 space-y-4 lg:col-span-2">
                                <div class="flex items-center justify-between border-b border-slate-200 dark:border-zinc-800 pb-3">
                                    <div class="flex items-center gap-3">
                                        <span class="w-10 h-10 rounded-2xl bg-blue-100 dark:bg-blue-950/80 text-blue-700 dark:text-blue-400 font-bold flex items-center justify-center text-sm shadow-xs">💳</span>
                                        <div>
                                            <h4 class="font-bold text-base text-slate-900 dark:text-white">Stripe Global Cards (Visa / Mastercard)</h4>
                                            <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 font-medium">Accept Credit & Debit Cards globally via Stripe API</p>
                                        </div>
                                    </div>
                                    <SettingField label="" type="switch" v-model="paymentsForm.enable_stripe" />
                                </div>

                                <div v-if="paymentsForm.enable_stripe" class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                                    <SettingField label="Stripe Publishable Key" v-model="paymentsForm.stripe_key" />
                                    <SettingField label="Stripe Secret Key" v-model="paymentsForm.stripe_secret" />
                                    <SettingField label="Stripe Webhook Secret" v-model="paymentsForm.stripe_webhook_secret" />
                                </div>
                            </div>

                        </div>

                        <div class="flex items-center justify-end pt-5 border-t border-slate-200 dark:border-zinc-800">
                            <button
                                type="submit"
                                :disabled="paymentsForm.processing"
                                class="px-8 py-3.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white text-sm font-extrabold rounded-2xl shadow-md shadow-sky-500/25 active:scale-95 transition cursor-pointer uppercase tracking-wider"
                            >
                                Save Gateway Settings
                            </button>
                        </div>
                    </form>

                    <!-- 5. SEO TAB -->
                    <form v-if="activeTab === 'seo'" @submit.prevent="submitSeo" class="space-y-6">
                        <div class="border-b border-slate-200 dark:border-zinc-800 pb-4">
                            <h3 class="font-black text-xl text-slate-900 dark:text-white font-heading flex items-center gap-2">
                                🔍 SEO & Analytics
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-zinc-400 font-medium mt-1">
                                Manage search engine metadata indexing, Google Analytics tokens & crawler rules
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <SettingField label="Default SEO Title" v-model="seoForm.meta_title" />
                            <SettingField label="Google Analytics Measurement ID" v-model="seoForm.google_analytics_id" />
                            <div class="md:col-span-2">
                                <SettingField label="Default Meta Description" type="textarea" v-model="seoForm.meta_description" />
                            </div>
                            <div class="md:col-span-2">
                                <SettingField label="Robots.txt RuleSet" type="textarea" v-model="seoForm.robots_txt_rule" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-200 dark:border-zinc-800">
                            <button
                                type="submit"
                                :disabled="seoForm.processing"
                                class="px-8 py-3.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white text-sm font-extrabold rounded-2xl shadow-md shadow-sky-500/25 active:scale-95 transition cursor-pointer uppercase tracking-wider"
                            >
                                Save SEO Settings
                            </button>
                        </div>
                    </form>

                    <!-- 6. NOTIFICATION RULES SETTINGS FORM -->
                    <form v-if="activeTab === 'notifications'" @submit.prevent="submitNotifications" class="space-y-8">
                        <div class="border-b border-slate-200 dark:border-zinc-800 pb-4">
                            <h3 class="font-black text-xl text-slate-900 dark:text-white font-heading flex items-center gap-2">
                                🔔 Notification Rules & Channels
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-zinc-400 font-medium mt-1">
                                Configure system event triggers and select notification channels (Email Mail vs System In-App Icon)
                            </p>
                        </div>

                        <!-- Event Rules Cards Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- 1. New Order Placed -->
                            <div class="bg-slate-50 dark:bg-zinc-950 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 space-y-4">
                                <div class="flex items-center gap-3 border-b border-slate-200 dark:border-zinc-800 pb-3">
                                    <span class="w-10 h-10 rounded-2xl bg-sky-100 dark:bg-sky-950/80 text-sky-600 dark:text-sky-400 font-bold flex items-center justify-center text-base shadow-xs">📦</span>
                                    <div>
                                        <h4 class="font-bold text-base text-slate-900 dark:text-white">New Order Placed</h4>
                                        <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 font-medium">When a customer submits a new order</p>
                                    </div>
                                </div>
                                <div class="space-y-3 pt-1">
                                    <SettingField label="Send Email Notification (Mail)" type="switch" v-model="notificationsForm.notify_order_mail" />
                                    <SettingField label="System In-App Icon Notification (Icon)" type="switch" v-model="notificationsForm.notify_order_icon" />
                                </div>
                            </div>

                            <!-- 2. Order Shipping & Delivery -->
                            <div class="bg-slate-50 dark:bg-zinc-950 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 space-y-4">
                                <div class="flex items-center gap-3 border-b border-slate-200 dark:border-zinc-800 pb-3">
                                    <span class="w-10 h-10 rounded-2xl bg-indigo-100 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 font-bold flex items-center justify-center text-base shadow-xs">🚚</span>
                                    <div>
                                        <h4 class="font-bold text-base text-slate-900 dark:text-white">Order Status Updates</h4>
                                        <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 font-medium">When an order is shipped or completed</p>
                                    </div>
                                </div>
                                <div class="space-y-3 pt-1">
                                    <SettingField label="Send Email Notification (Mail)" type="switch" v-model="notificationsForm.notify_shipping_mail" />
                                    <SettingField label="System In-App Icon Notification (Icon)" type="switch" v-model="notificationsForm.notify_shipping_icon" />
                                </div>
                            </div>

                            <!-- 3. Writer Application Received -->
                            <div class="bg-slate-50 dark:bg-zinc-950 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 space-y-4">
                                <div class="flex items-center gap-3 border-b border-slate-200 dark:border-zinc-800 pb-3">
                                    <span class="w-10 h-10 rounded-2xl bg-purple-100 dark:bg-purple-950/80 text-purple-600 dark:text-purple-400 font-bold flex items-center justify-center text-base shadow-xs">✍️</span>
                                    <div>
                                        <h4 class="font-bold text-base text-slate-900 dark:text-white">Writer Application</h4>
                                        <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 font-medium">When a new author submits an application</p>
                                    </div>
                                </div>
                                <div class="space-y-3 pt-1">
                                    <SettingField label="Send Email Notification (Mail)" type="switch" v-model="notificationsForm.notify_writer_mail" />
                                    <SettingField label="System In-App Icon Notification (Icon)" type="switch" v-model="notificationsForm.notify_writer_icon" />
                                </div>
                            </div>

                            <!-- 4. New Book Review Submitted -->
                            <div class="bg-slate-50 dark:bg-zinc-950 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 space-y-4">
                                <div class="flex items-center gap-3 border-b border-slate-200 dark:border-zinc-800 pb-3">
                                    <span class="w-10 h-10 rounded-2xl bg-amber-100 dark:bg-amber-950/80 text-amber-600 dark:text-amber-400 font-bold flex items-center justify-center text-base shadow-xs">⭐</span>
                                    <div>
                                        <h4 class="font-bold text-base text-slate-900 dark:text-white">Book Review Submission</h4>
                                        <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 font-medium">When a reader posts a new book review</p>
                                    </div>
                                </div>
                                <div class="space-y-3 pt-1">
                                    <SettingField label="Send Email Notification (Mail)" type="switch" v-model="notificationsForm.notify_review_mail" />
                                    <SettingField label="System In-App Icon Notification (Icon)" type="switch" v-model="notificationsForm.notify_review_icon" />
                                </div>
                            </div>

                            <!-- 5. New User Account Registration -->
                            <div class="bg-slate-50 dark:bg-zinc-950 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 space-y-4">
                                <div class="flex items-center gap-3 border-b border-slate-200 dark:border-zinc-800 pb-3">
                                    <span class="w-10 h-10 rounded-2xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 font-bold flex items-center justify-center text-base shadow-xs">👤</span>
                                    <div>
                                        <h4 class="font-bold text-base text-slate-900 dark:text-white">User Registration</h4>
                                        <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 font-medium">When a new user creates an account</p>
                                    </div>
                                </div>
                                <div class="space-y-3 pt-1">
                                    <SettingField label="Send Email Notification (Mail)" type="switch" v-model="notificationsForm.notify_user_mail" />
                                    <SettingField label="System In-App Icon Notification (Icon)" type="switch" v-model="notificationsForm.notify_user_icon" />
                                </div>
                            </div>

                            <!-- 6. Inventory Low Stock Alert -->
                            <div class="bg-slate-50 dark:bg-zinc-950 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 space-y-4">
                                <div class="flex items-center gap-3 border-b border-slate-200 dark:border-zinc-800 pb-3">
                                    <span class="w-10 h-10 rounded-2xl bg-rose-100 dark:bg-rose-950/80 text-rose-600 dark:text-rose-400 font-bold flex items-center justify-center text-base shadow-xs">⚠️</span>
                                    <div>
                                        <h4 class="font-bold text-base text-slate-900 dark:text-white">Low Stock Alert</h4>
                                        <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 font-medium">When book inventory falls below 5 copies</p>
                                    </div>
                                </div>
                                <div class="space-y-3 pt-1">
                                    <SettingField label="Send Email Notification (Mail)" type="switch" v-model="notificationsForm.notify_stock_mail" />
                                    <SettingField label="System In-App Icon Notification (Icon)" type="switch" v-model="notificationsForm.notify_stock_icon" />
                                </div>
                            </div>

                        </div>

                        <div class="flex items-center justify-end pt-5 border-t border-slate-200 dark:border-zinc-800">
                            <button
                                type="submit"
                                :disabled="notificationsForm.processing"
                                class="px-8 py-3.5 bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700 text-white text-sm font-extrabold rounded-2xl shadow-md shadow-sky-500/25 active:scale-95 transition cursor-pointer uppercase tracking-wider"
                            >
                                Save Notification Rules
                            </button>
                        </div>
                    </form>

                </div>

            </div>
        </div>
    </AdminLayout>
</template>