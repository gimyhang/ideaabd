@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-gradient-to-r from-brand-600 to-accent py-12">
        <div class="container mx-auto px-4 text-white">
            <h1 class="text-4xl font-bold mb-4">{{ $paper->title }}</h1>
            <div class="space-y-2">
                <p><strong>লেখক:</strong> {{ $paper->author->name ?? 'অজানা' }}</p>
                <p><strong>প্রকাশনী তারিখ:</strong> {{ $paper->published_at->format('d M Y') }}</p>
                @if($paper->doi)
                    <p><strong>DOI:</strong> {{ $paper->doi }}</p>
                @endif
                <p><strong>বিভাগ:</strong> {{ $paper->category ?? 'অন্যান্য' }}</p>
                <p><strong>পড়া হয়েছে:</strong> {{ $paper->view_count }} বার | <strong>ডাউনলোড:</strong> {{ $paper->download_count }} বার</p>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12">
        <div class="max-w-3xl mx-auto">
            @if($paper->abstract)
                <div class="bg-white rounded-lg p-8 mb-8">
                    <h2 class="text-2xl font-bold mb-4">সারমর্ম</h2>
                    <p class="text-slate-700">{{ $paper->abstract }}</p>
                </div>
            @endif

            @if($paper->keywords)
                <div class="bg-white rounded-lg p-8 mb-8">
                    <h3 class="font-bold mb-3">মূল শব্দ</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($paper->keywords as $keyword)
                            <span class="px-3 py-1 bg-brand-100 text-brand-700 rounded-full text-sm">#{{ $keyword }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-lg p-8 mb-8">
                <h2 class="text-2xl font-bold mb-4">বিষয়বস্তু</h2>
                <div class="prose max-w-none text-slate-700">
                    {!! nl2br(e($paper->content)) !!}
                </div>
            </div>

            @if($paper->pdf_file_path)
                <div class="bg-white rounded-lg p-8 mb-8">
                    <a href="{{ route('research.download', $paper->slug) }}" class="inline-block px-6 py-3 bg-brand-600 text-white rounded-lg font-bold hover:bg-brand-700">
                        📥 PDF ডাউনলোড করুন
                    </a>
                </div>
            @endif

            @if($related->count())
                <div class="border-t pt-8">
                    <h2 class="text-2xl font-bold mb-6">সম্পর্কিত গবেষণা</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($related as $relatedPaper)
                            <article class="shop-card">
                                <div class="h-20 bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center">
                                    <span class="text-2xl">📄</span>
                                </div>
                                <div class="p-3">
                                    <h3 class="font-bold text-sm mb-1 line-clamp-2">{{ $relatedPaper->title }}</h3>
                                    <a href="{{ route('research.show', $relatedPaper->slug) }}" class="text-xs text-brand-600 hover:text-brand-700 font-semibold">পড়ুন →</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
