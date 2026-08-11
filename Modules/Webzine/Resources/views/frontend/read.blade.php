@extends('layouts.app')

@section('title', $webzine->title . ' - পড়ুন')

@section('content')
    <section class="px-6 py-10 mx-auto max-w-7xl">
        <div class="rounded-3xl bg-white/90 p-8 shadow-lg ring-1 ring-slate-200">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">{{ $webzine->title }}</h1>
            <p class="text-lg text-indigo-600 mb-6">{{ $webzine->issue ?? 'ডিজিটাল সংকলন' }}</p>
            
            <div class="bg-slate-50 rounded-2xl p-8 text-center">
                <div class="text-8xl mb-4">📰</div>
                <p class="text-slate-600 text-lg">ওয়েবজিন পাঠক সিস্টেম এখানে চলবে</p>
                <p class="text-slate-500 mt-4">{{ $webzine->description ?? 'ওয়েবজিন সামগ্রী' }}</p>
            </div>
        </div>
    </section>
@endsection
