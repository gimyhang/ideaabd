@extends('layouts.app')

@section('title', 'বই কেনাকাটা')

@section('content')
    <section class="px-6 py-10 mx-auto max-w-7xl">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900">📚 বই কেনাকাটা</h1>
            <p class="mt-2 text-slate-600">হাজারো বই থেকে আপনার পছন্দের বই খুঁজে নিন</p>
        </div>

        <!-- Search & Filter -->
        <div class="mb-8 rounded-3xl bg-white/90 p-6 shadow-lg ring-1 ring-slate-200">
            <form class="flex flex-col md:flex-row gap-4">
                <input type="text" name="search" placeholder="বইয়ের নাম বা লেখক খুঁজুন..." class="flex-1 rounded-full border border-slate-200 bg-slate-50 py-3 px-4 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                <button type="submit" class="rounded-full bg-gradient-to-r from-indigo-600 to-sky-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition hover:brightness-110">খুঁজুন</button>
            </form>
        </div>

        <!-- Demo Books Grid -->
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            <div class="group rounded-3xl bg-white/90 overflow-hidden shadow-lg hover:shadow-2xl ring-1 ring-slate-200 transition">
                <div class="aspect-square bg-gradient-to-br from-indigo-100 to-sky-100 flex items-center justify-center text-6xl">📘</div>
                <div class="p-4">
                    <h3 class="text-sm font-bold text-slate-900">পদ্মা নদীর মাঝি</h3>
                    <p class="text-xs text-slate-600 mt-1">মানিক বন্দ্যোপাধ্যায়</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-lg font-bold text-indigo-600">৳350</span>
                        <button class="px-3 py-1.5 rounded-full bg-indigo-600 text-white text-xs font-semibold">🛒</button>
                    </div>
                </div>
            </div>

            <div class="group rounded-3xl bg-white/90 overflow-hidden shadow-lg hover:shadow-2xl ring-1 ring-slate-200 transition">
                <div class="aspect-square bg-gradient-to-br from-indigo-100 to-sky-100 flex items-center justify-center text-6xl">📕</div>
                <div class="p-4">
                    <h3 class="text-sm font-bold text-slate-900">চাঁদের পাহাড়</h3>
                    <p class="text-xs text-slate-600 mt-1">সৈয়দ মুস্তাফা সিরাজ</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-lg font-bold text-indigo-600">৳320</span>
                        <button class="px-3 py-1.5 rounded-full bg-indigo-600 text-white text-xs font-semibold">🛒</button>
                    </div>
                </div>
            </div>

            <div class="group rounded-3xl bg-white/90 overflow-hidden shadow-lg hover:shadow-2xl ring-1 ring-slate-200 transition">
                <div class="aspect-square bg-gradient-to-br from-indigo-100 to-sky-100 flex items-center justify-center text-6xl">📗</div>
                <div class="p-4">
                    <h3 class="text-sm font-bold text-slate-900">গীতাঞ্জলি</h3>
                    <p class="text-xs text-slate-600 mt-1">রবীন্দ্রনাথ ঠাকুর</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-lg font-bold text-indigo-600">৳300</span>
                        <button class="px-3 py-1.5 rounded-full bg-indigo-600 text-white text-xs font-semibold">🛒</button>
                    </div>
                </div>
            </div>

            <div class="group rounded-3xl bg-white/90 overflow-hidden shadow-lg hover:shadow-2xl ring-1 ring-slate-200 transition">
                <div class="aspect-square bg-gradient-to-br from-indigo-100 to-sky-100 flex items-center justify-center text-6xl">📙</div>
                <div class="p-4">
                    <h3 class="text-sm font-bold text-slate-900">কর্মফল</h3>
                    <p class="text-xs text-slate-600 mt-1">শরৎচন্দ্র চট্টোপাধ্যায়</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-lg font-bold text-indigo-600">৳400</span>
                        <button class="px-3 py-1.5 rounded-full bg-indigo-600 text-white text-xs font-semibold">🛒</button>
                    </div>
                </div>
            </div>

            <div class="group rounded-3xl bg-white/90 overflow-hidden shadow-lg hover:shadow-2xl ring-1 ring-slate-200 transition">
                <div class="aspect-square bg-gradient-to-br from-indigo-100 to-sky-100 flex items-center justify-center text-6xl">📔</div>
                <div class="p-4">
                    <h3 class="text-sm font-bold text-slate-900">আমার বাঙালি</h3>
                    <p class="text-xs text-slate-600 mt-1">হুমায়ুন আজাদ</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-lg font-bold text-indigo-600">৳350</span>
                        <button class="px-3 py-1.5 rounded-full bg-indigo-600 text-white text-xs font-semibold">🛒</button>
                    </div>
                </div>
            </div>

            <div class="group rounded-3xl bg-white/90 overflow-hidden shadow-lg hover:shadow-2xl ring-1 ring-slate-200 transition">
                <div class="aspect-square bg-gradient-to-br from-indigo-100 to-sky-100 flex items-center justify-center text-6xl">📕</div>
                <div class="p-4">
                    <h3 class="text-sm font-bold text-slate-900">অগ্নি পরীক্ষা</h3>
                    <p class="text-xs text-slate-600 mt-1">বঙ্কিমচন্দ্র চট্টোপাধ্যায়</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-lg font-bold text-indigo-600">৳450</span>
                        <button class="px-3 py-1.5 rounded-full bg-indigo-600 text-white text-xs font-semibold">🛒</button>
                    </div>
                </div>
            </div>

            <div class="group rounded-3xl bg-white/90 overflow-hidden shadow-lg hover:shadow-2xl ring-1 ring-slate-200 transition">
                <div class="aspect-square bg-gradient-to-br from-indigo-100 to-sky-100 flex items-center justify-center text-6xl">📗</div>
                <div class="p-4">
                    <h3 class="text-sm font-bold text-slate-900">বঙ্গভাষা</h3>
                    <p class="text-xs text-slate-600 mt-1">রাজা রামমোহন রায়</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-lg font-bold text-indigo-600">৳280</span>
                        <button class="px-3 py-1.5 rounded-full bg-indigo-600 text-white text-xs font-semibold">🛒</button>
                    </div>
                </div>
            </div>

            <div class="group rounded-3xl bg-white/90 overflow-hidden shadow-lg hover:shadow-2xl ring-1 ring-slate-200 transition">
                <div class="aspect-square bg-gradient-to-br from-indigo-100 to-sky-100 flex items-center justify-center text-6xl">📘</div>
                <div class="p-4">
                    <h3 class="text-sm font-bold text-slate-900">আমার আছে দুঃখ</h3>
                    <p class="text-xs text-slate-600 mt-1">বিষ্ণু দে</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-lg font-bold text-indigo-600">৳260</span>
                        <button class="px-3 py-1.5 rounded-full bg-indigo-600 text-white text-xs font-semibold">🛒</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
