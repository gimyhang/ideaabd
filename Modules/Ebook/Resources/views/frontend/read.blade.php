@extends('layouts.app')

@section('title', $ebook->title . ' - পড়ুন')

@section('content')
    <section class="px-6 py-10 mx-auto max-w-7xl">
        <div class="rounded-3xl bg-white/90 p-8 shadow-lg ring-1 ring-slate-200">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">{{ $ebook->title }}</h1>
            <p class="text-lg text-indigo-600 mb-6">ই-পাব রিডার</p>
            
            <div class="bg-slate-50 rounded-2xl p-8 text-center">
                <div class="text-8xl mb-4">📖</div>
                <p class="text-slate-600 text-lg">ই-বুক রিডার সিস্টেম এখানে চলবে</p>
                <p class="text-slate-500 mt-4">{{ $ebook->description ?? 'ই-বুক সামগ্রী' }}</p>
            </div>
        </div>
    </section>
@endsection
