@extends('layouts.app')

@section('title', 'আমাদের সম্পর্কে - IdeaABD')

@section('content')
<div class="min-h-screen bg-classic px-4 py-16 sm:px-6 lg:px-8">
    <div class="mx-auto flex max-w-6xl flex-col gap-10 lg:flex-row">
        <div class="flex-1 rounded-3xl border border-slate-200 bg-white/80 p-8 shadow-xl shadow-slate-200/50 backdrop-blur">
            <p class="mb-4 inline-flex rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700">📚 IdeaABD bookstore</p>
            <h1 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">বাংলাদেশের জন্য আধুনিক বইয়ের অভিজ্ঞতা</h1>
            <p class="mt-6 text-lg leading-8 text-slate-600">IdeaABD একটি স্বপ্নের বুকশপ প্ল্যাটফর্ম যেখানে পাঠক, লেখক ও প্রকাশক একসাথে এক সুন্দর ডিজিটাল অভিজ্ঞতায় যুক্ত হয়।</p>
            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl bg-slate-50 p-5">
                    <h2 class="font-bold text-slate-900">📖 বিশাল বই সংগ্রহ</h2>
                    <p class="mt-2 text-sm text-slate-600">শিক্ষা, শিশু-কিশোর, সাহিত্য, বিজ্ঞান ও ব্যবসার বই একসাথে খুঁজুন।</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-5">
                    <h2 class="font-bold text-slate-900">⚡ দ্রুত অর্ডার</h2>
                    <p class="mt-2 text-sm text-slate-600">সহজ অর্ডার, দ্রুত ডেলিভারি ও নির্ভরযোগ্য সেবা।</p>
                </div>
            </div>
        </div>

        <div class="w-full max-w-md rounded-3xl border border-indigo-100 bg-gradient-to-br from-indigo-600 to-sky-600 p-8 text-white shadow-2xl">
            <h2 class="text-2xl font-bold">কেন বেছে নেবেন?</h2>
            <ul class="mt-6 space-y-4 text-sm leading-7">
                <li>✓ নান্দনিক ও মোবাইল-ফ্রেন্ডলি ডিজাইন</li>
                <li>✓ লেখক, বই ও ক্যাটাগরির সহজ অনুসন্ধান</li>
                <li>✓ বুকপ্রিভিউ ও দ্রুত ভিউ</li>
                <li>✓ বাংলাদেশের পাঠকদের জন্য উপযোগী অভিজ্ঞতা</li>
            </ul>
        </div>
    </div>
</div>
@endsection
