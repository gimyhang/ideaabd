@extends('layouts.app')

@section('title', 'যোগাযোগ - IdeaABD')

@section('content')
<div class="min-h-screen bg-classic px-4 py-16 sm:px-6 lg:px-8">
    <div class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="rounded-3xl border border-slate-200 bg-white/80 p-8 shadow-xl shadow-slate-200/50 backdrop-blur">
            <p class="mb-4 inline-flex rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700">📬 যোগাযোগ করুন</p>
            <h1 class="text-3xl font-black tracking-tight text-slate-900">আপনার প্রশ্ন বা পরামর্শ আমাদের জানান</h1>
            <p class="mt-4 text-lg text-slate-600">যেকোনো অর্ডার, বই, প্রকাশনা বা সহযোগিতার বিষয়ে আমরা সহায়তা করতে প্রস্তুত।</p>

            <div class="mt-8 space-y-4 text-sm text-slate-600">
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="font-semibold text-slate-900">📍 অবস্থান</p>
                    <p class="mt-1">ঢাকা, বাংলাদেশ</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="font-semibold text-slate-900">📧 ইমেইল</p>
                    <p class="mt-1">ideapbd@gmail.com</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="font-semibold text-slate-900">📞 হেল্পলাইন ও ফোন</p>
                    <p class="mt-1"><a href="tel:+8801726976982" class="text-primary text-decoration-none font-semibold">+8801726976982</a></p>
                </div>
                <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4">
                    <p class="font-semibold text-emerald-900"><i class="fa-brands fa-whatsapp text-emerald-600 me-1"></i> হোয়াটসঅ্যাপ (WhatsApp)</p>
                    <p class="mt-1">
                        <a href="https://wa.me/8801726976982" target="_blank" class="text-emerald-700 font-semibold hover:underline">+8801726976982</a>
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-indigo-100 bg-gradient-to-br from-slate-900 to-indigo-800 p-8 text-white shadow-2xl">
            <h2 class="text-2xl font-bold">সাথে থাকুন</h2>
            <p class="mt-4 text-sm leading-7 text-indigo-100">নতুন বই, অফার এবং সাহিত্য কনটেন্টের আপডেট পেতে আমাদের সাথে যুক্ত থাকুন।</p>
            <a href="/" class="mt-8 inline-flex rounded-full bg-white px-5 py-3 font-semibold text-indigo-700">বুকশপে ফিরে যান</a>
        </div>
    </div>
</div>
@endsection
