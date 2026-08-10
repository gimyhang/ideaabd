@extends('layouts.app')

@section('title', 'বই ক্যাটালগ - আইডিয়া প্রকাশন')

@section('content')
<div class="min-h-screen bg-classic px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
    <div class="mx-auto max-w-7xl">
        <section class="hero-panel overflow-hidden rounded-[2rem] border border-white/20 p-8 text-white sm:p-10 lg:p-12">
            <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="mb-4 inline-flex rounded-full bg-white/15 px-3 py-1 text-sm font-semibold backdrop-blur">📚 আধুনিক অনলাইন বুকশপ</p>
                    <h1 class="text-3xl font-black tracking-tight sm:text-4xl lg:text-5xl">আপনার পছন্দের বই, এখন এক ক্লিকে</h1>
                    <p class="mt-4 text-lg text-indigo-100">শিক্ষা, সাহিত্য, শিশু-কিশোর, বিজ্ঞান ও ব্যবসার বই একসাথে আবিষ্কার করুন।</p>
                </div>
                <div class="rounded-2xl border border-white/20 bg-white/12 p-4 backdrop-blur">
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-indigo-100">আজকের অফার</p>
                    <p class="mt-2 text-2xl font-bold">ফ্রেশ রিলিজ & বেস্টসেলার</p>
                </div>
            </div>
        </section>

        <div class="mt-8 flex flex-col gap-6 lg:flex-row lg:items-start">
            <aside class="w-full lg:w-72">
                <div class="shop-card p-5">
                    <h3 class="text-lg font-bold text-slate-900">ক্যাটাগরিসমূহ</h3>
                    <ul class="mt-4 space-y-2">
                        @foreach($categories as $cat)
                            <li>
                                <a href="?category={{ $cat->slug }}" class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-indigo-600">
                                    <span>{{ $cat->name }}</span>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500">{{ $cat->books_count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>

            <section class="flex-1">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @forelse($books as $book)
                        <article class="shop-card group p-5 transition hover:-translate-y-1 hover:shadow-[0_22px_55px_rgba(15,23,42,0.12)]">
                            <div class="flex items-center justify-between">
                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">{{$book->category->name ?? 'বই'}}</span>
                                <span class="text-sm font-semibold text-slate-500">{{$book->format ?? 'প্রিন্টেড'}}</span>
                            </div>
                            <h4 class="mt-4 text-xl font-bold text-slate-900">{{ $book->title }}</h4>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ Str::limit($book->description ?? 'বইয়ের বিস্তারিত শীঘ্রই যোগ হবে।', 120) }}</p>
                            <p class="mt-4 text-sm text-slate-500">লেখক: {{ $book->authors->pluck('name')->join(', ') }}</p>
                            <div class="mt-5 flex items-center justify-between">
                                <p class="text-lg font-black text-slate-900">৳ {{ number_format($book->price, 2) }}</p>
                                <a href="/books/{{ $book->slug }}" class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">বিস্তারিত</a>
                            </div>
                        </article>
                    @empty
                        <div class="shop-card col-span-full p-8 text-center text-slate-600">
                            কোনো বই পাওয়া যায়নি। শীঘ্রই আরও নতুন বই যুক্ত হবে।
                        </div>
                    @endforelse
                </div>

                @if(method_exists($books, 'links'))
                    <div class="mt-6">
                        {{ $books->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
