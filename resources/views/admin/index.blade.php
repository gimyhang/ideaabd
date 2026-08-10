@extends('layouts.app')

@section('title', 'অ্যাডমিন ড্যাশবোর্ড')

@section('content')
    <section class="px-6 py-10 mx-auto max-w-7xl">
        <div class="mb-8 rounded-3xl bg-white/90 p-8 shadow-lg ring-1 ring-slate-200">
            <h1 class="text-3xl font-bold text-slate-900">অ্যাডমিন ড্যাশবোর্ড</h1>
            <p class="mt-3 text-slate-600">স্বাগতম! এখান থেকে আপনি অ্যাডমিন অপারেশন চালাতে পারবেন।</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-3xl bg-slate-50 p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">মোট ব্যবহারকারী</h2>
                <p class="mt-3 text-4xl font-bold text-brand-500">--</p>
            </div>
            <div class="rounded-3xl bg-slate-50 p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">বিভাগ</h2>
                <p class="mt-3 text-4xl font-bold text-brand-500">--</p>
            </div>
            <div class="rounded-3xl bg-slate-50 p-6 shadow-sm ring-1 ring-slate-200">
                <h2 class="text-xl font-semibold text-slate-900">সাম্প্রতিক কার্যকলাপ</h2>
                <p class="mt-3 text-slate-600">ডেটা লোড হয়নি</p>
            </div>
        </div>
    </section>
@endsection
