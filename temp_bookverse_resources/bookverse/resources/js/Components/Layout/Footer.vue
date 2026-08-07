<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="bg-white text-slate-700 text-xs font-sans border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
            <!-- Col 1: Brand & Bio -->
            <div class="space-y-4">
                <Link href="/" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-600 to-indigo-600 flex items-center justify-center text-white font-extrabold text-xl shadow-md shadow-sky-600/15 group-hover:scale-105 transition transform">
                        B
                    </div>
                    <div>
                        <span class="text-xl font-black text-slate-900 tracking-tight font-heading">BookVerse</span>
                        <span class="block text-[10px] text-sky-600 font-bold uppercase tracking-wider">বুকভার্স বাংলাদেশ</span>
                    </div>
                </Link>
                <p class="text-slate-500 leading-relaxed text-xs">
                    বাংলাদেশের নির্ভরযোগ্য অনলাইন বইয়ের প্ল্যাটফর্ম ও সাহিত্য ই-ম্যাগাজিন। ঘরে বসেই আপনার পছন্দের বই অর্ডার করুন এবং নিয়মিত পড়ুন সাহিত্য পত্রিকার বিশেষ গল্প ও ফিচার।
                </p>
            </div>

            <!-- Col 2: Quick Links (দ্রুত লিংকসমূহ) -->
            <div class="space-y-3">
                <h4 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider font-heading border-l-2 border-sky-500 pl-2">
                    প্রয়োজনীয় লিংক
                </h4>
                <ul class="space-y-2.5 text-slate-600 font-medium">
                    <li><Link href="/catalog" class="hover:text-sky-600 transition flex items-center gap-1.5"><span>•</span><span>বইয়ের ক্যাটালগ</span></Link></li>
                    <li><Link href="/magazine" class="hover:text-sky-600 transition flex items-center gap-1.5"><span>•</span><span>ই-ম্যাগাজিন</span></Link></li>
                    <li><Link href="/writers" class="hover:text-sky-600 transition flex items-center gap-1.5"><span>•</span><span>লেখকবৃন্দ</span></Link></li>
                    <li><Link href="/catalog?is_featured=1" class="hover:text-sky-600 transition flex items-center gap-1.5"><span>•</span><span>বিশেষ ফিচার্ড বই</span></Link></li>
                </ul>
            </div>

            <!-- Col 3: Customer Care (গ্রাহক সেবা) -->
            <div class="space-y-3">
                <h4 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider font-heading border-l-2 border-indigo-500 pl-2">
                    গ্রাহক সহায়তা
                </h4>
                <ul class="space-y-2.5 text-slate-600 font-medium">
                    <li><Link href="/orders" class="hover:text-sky-600 transition flex items-center gap-1.5"><span>•</span><span>অর্ডার ট্র্যাকিং</span></Link></li>
                    <li><Link href="/profile" class="hover:text-sky-600 transition flex items-center gap-1.5"><span>•</span><span>আমার প্রোফাইল</span></Link></li>
                    <li><Link href="/wishlist" class="hover:text-sky-600 transition flex items-center gap-1.5"><span>•</span><span>পছন্দের তালিকা</span></Link></li>
                    <li><Link href="/contact" class="hover:text-sky-600 transition flex items-center gap-1.5"><span>•</span><span>যোগাযোগ ও হেল্পলাইন</span></Link></li>
                </ul>
            </div>

            <!-- Col 4: Contact Info (যোগাযোগ) -->
            <div class="space-y-3">
                <h4 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider font-heading border-l-2 border-emerald-500 pl-2">
                    যোগাযোগ
                </h4>
                <div class="space-y-2 text-slate-600 text-xs">
                    <p class="flex items-center gap-2">
                        <span>📍</span>
                        <span>ঢাকা, বাংলাদেশ</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <span>✉️</span>
                        <span class="font-mono text-slate-800">support@bookverse.com</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <span>📞</span>
                        <span class="font-mono text-slate-800">+880 1700-000000</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Copyright Strip -->
        <div class="border-t border-slate-100 py-6 text-center text-slate-500 text-[11px] bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p>© {{ new Date().getFullYear() }} BookVerse (বুকভার্স)। সর্বস্বত্ব সংরক্ষিত।</p>
                <div class="flex items-center gap-4 text-slate-500 text-[11px]">
                    <span class="hover:text-slate-800 cursor-pointer">গোপনীয়তা নীতি</span>
                    <span>•</span>
                    <span class="hover:text-slate-800 cursor-pointer">ব্যবহারের শর্তাবলী</span>
                </div>
            </div>
        </div>
    </footer>
</template>
