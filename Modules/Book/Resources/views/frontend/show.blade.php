@extends('layouts.app')

@section('title', $book->title . ' - বই কেনাকাটা')

@section('content')
<div class="bg-slate-50 py-8">
    <div class="container mx-auto px-4 max-w-[1200px]">
        <!-- Breadcrumb -->
        <nav class="flex text-sm text-slate-500 mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('book.index') }}" class="hover:text-indigo-600">হোম</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('book.index', ['category' => $book->category->slug ?? '']) }}" class="hover:text-indigo-600">{{ $book->category->name ?? 'ক্যাটাগরি' }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="text-slate-400 font-medium line-clamp-1">{{ $book->title }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 overflow-hidden">
            <div class="flex flex-col md:flex-row">
                <!-- Left: Book Cover -->
                <div class="md:w-1/3 p-6 md:p-8 flex flex-col items-center border-b md:border-b-0 md:border-r border-slate-100">
                    <div class="relative w-full max-w-[280px] aspect-[3/4] bg-slate-50 rounded-xl shadow-md overflow-hidden flex items-center justify-center">
                        @php
                            $cover = $book->cover_image;
                            if ($cover) {
                                $coverUrl = str_starts_with($cover, 'http') ? $cover : (str_starts_with($cover, '/storage/') ? asset(ltrim($cover, '/')) : asset('storage/' . $cover));
                            }
                        @endphp
                        @if($cover)
                            <img src="{{ $coverUrl }}" alt="{{ $book->title }}" class="object-cover w-full h-full" />
                        @else
                            <div class="text-9xl">📘</div>
                        @endif
                    </div>
                    @php
                        $samplePdf = $book->sample_pdf_path;
                        if ($samplePdf) {
                            $samplePdfUrl = str_starts_with($samplePdf, 'http') ? $samplePdf : (str_starts_with($samplePdf, '/storage/') ? asset(ltrim($samplePdf, '/')) : asset('storage/' . $samplePdf));
                        }
                    @endphp
                    @if($samplePdf)
                        <a href="{{ $samplePdfUrl }}" target="_blank" class="mt-6 w-full max-w-[280px] py-2.5 border-2 border-indigo-600 text-indigo-600 rounded-lg font-semibold hover:bg-indigo-50 transition-colors flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                            একটু পড়ে দেখুন
                        </a>
                    @endif
                </div>

                <!-- Right: Book Details -->
                <div class="md:w-2/3 p-6 md:p-10 flex flex-col">
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-900 leading-tight">{{ $book->title }}</h1>
                    @if($book->subtitle)
                        <p class="text-lg text-slate-600 mt-1">{{ $book->subtitle }}</p>
                    @endif
                    
                    <div class="mt-4 flex flex-col gap-2 text-sm text-slate-600">
                        <div class="flex items-start">
                            <span class="w-24 text-slate-400">লেখক:</span>
                            @php
                                $authorNames = $book->authors->isNotEmpty() ? $book->authors->pluck('name')->join(', ') : ($book->author_name ?: 'অজানা লেখক');
                            @endphp
                            <span class="font-semibold text-indigo-600">{{ $authorNames }}</span>
                        </div>
                        <div class="flex items-start">
                            <span class="w-24 text-slate-400">প্রকাশনী:</span>
                            <span class="font-medium hover:text-indigo-600 cursor-pointer">{{ $book->publisher->name ?? 'অজানা' }}</span>
                        </div>
                        <div class="flex items-start">
                            <span class="w-24 text-slate-400">ক্যাটাগরি:</span>
                            <span class="font-medium hover:text-indigo-600 cursor-pointer">{{ $book->category->name ?? 'সাধারণ' }}</span>
                        </div>
                    </div>

                    <!-- Rating -->
                    <div class="mt-5 flex items-center gap-2">
                        <div class="flex text-amber-400">
                            @for($i=1; $i<=5; $i++)
                                @if($i <= round($book->reviews_avg_rating ?? 0))
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                @else
                                    <svg class="w-5 h-5 text-slate-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                @endif
                            @endfor
                        </div>
                        <span class="text-sm text-slate-500">{{ $book->reviews_avg_rating ? number_format($book->reviews_avg_rating, 1) : '0.0' }} ({{ $book->reviews_count ?? 0 }} রেটিং)</span>
                    </div>

                    <hr class="my-6 border-slate-100" />

                    <!-- Pricing Box -->
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                        <div>
                            @if($book->discount_price && $book->discount_price < $book->price)
                                <div class="flex items-end gap-3 mb-1">
                                    <span class="text-3xl font-bold text-slate-900">৳ {{ round($book->discount_price) }}</span>
                                    <span class="text-lg text-slate-400 line-through mb-1">৳ {{ round($book->price) }}</span>
                                </div>
                                @php
                                    $discountPercentage = round((($book->price - $book->discount_price) / $book->price) * 100);
                                @endphp
                                <p class="text-sm font-medium text-emerald-600">আপনি সাশ্রয় করছেন ৳ {{ round($book->price - $book->discount_price) }} ({{ $discountPercentage }}%)</p>
                            @else
                                <div class="text-3xl font-bold text-slate-900 mb-1">৳ {{ round($book->price) }}</div>
                            @endif
                            <div class="mt-4 flex items-center gap-2 text-sm text-slate-600">
                                @if($book->stock_quantity > 0)
                                    <span class="flex items-center gap-1 text-emerald-600 font-medium">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        স্টকে আছে ({{ $book->stock_quantity }} কপি)
                                    </span>
                                @else
                                    <span class="text-rose-500 font-medium">স্টক আউট</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                            <!-- Quantity Selector -->
                            <div class="flex items-center border border-slate-200 rounded-lg bg-white h-12 w-full sm:w-32 justify-between px-2 shrink-0 shadow-sm">
                                <button type="button" onclick="const q=document.getElementById('qty'); if(q.value > 1) q.value--" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-100 rounded-md transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg></button>
                                <input type="number" id="qty" name="quantity" value="1" min="1" max="{{ $book->stock_quantity > 0 ? $book->stock_quantity : 1 }}" class="w-10 text-center border-none focus:ring-0 text-slate-800 font-bold p-0 bg-transparent appearance-none">
                                <button type="button" onclick="document.getElementById('qty').value++" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:bg-slate-100 rounded-md transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg></button>
                            </div>
                            
                            <button class="flex-1 md:flex-none px-6 py-3 h-12 rounded-lg bg-indigo-50 text-indigo-600 font-semibold hover:bg-indigo-100 transition-colors flex items-center justify-center gap-2 whitespace-nowrap">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                কার্টে রাখুন
                            </button>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#orderModal" class="flex-1 md:flex-none px-8 py-3 h-12 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition-colors shadow-md shadow-indigo-200 flex items-center justify-center whitespace-nowrap">
                                এখনই কিনুন / গিফট
                            </button>
                        </div>
                    </div>
                    
                    <!-- Additional Actions (Wishlist & Share) -->
                    <div class="mt-6 flex flex-wrap items-center gap-4">
                        <button class="flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-rose-500 transition-colors bg-white px-4 py-2 rounded-lg border border-slate-200 shadow-sm hover:border-rose-200 hover:bg-rose-50">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                            উইশলিস্টে রাখুন
                        </button>
                        <button class="flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-indigo-600 transition-colors bg-white px-4 py-2 rounded-lg border border-slate-200 shadow-sm hover:border-indigo-200 hover:bg-indigo-50">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" /></svg>
                            শেয়ার করুন
                        </button>
                    </div>
                    
                    <div class="mt-6 flex gap-6 text-sm border-t border-slate-100 pt-6">
                        <div class="flex items-center gap-2 text-slate-600">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>১০০% অরিজিনাল বই</span>
                        </div>
                        <div class="flex items-center gap-2 text-slate-600">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>দ্রুত ডেলিভারি</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Book Specifications & Summary -->
        <div class="mt-8 flex flex-col lg:flex-row gap-8">
            <div class="lg:w-2/3">
                <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 p-6 md:p-8">
                    <h2 class="text-xl font-bold text-slate-800 mb-4 border-b border-slate-100 pb-3">বইয়ের সারাংশ</h2>
                    <div class="prose prose-slate max-w-none text-slate-600 text-sm md:text-base">
                        @if($book->description)
                            {!! nl2br(e($book->description)) !!}
                        @else
                            <p class="italic">এই বইয়ের কোনো সারাংশ যুক্ত করা হয়নি।</p>
                        @endif
                    </div>
                </div>

                @if($book->authors->isNotEmpty())
                <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 p-6 md:p-8 mt-6">
                    <h2 class="text-xl font-bold text-slate-800 mb-4 border-b border-slate-100 pb-3">লেখক পরিচিতি</h2>
                    @foreach($book->authors as $author)
                        <div class="flex flex-col sm:flex-row gap-4 mb-4 pb-4 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                            <div class="w-16 h-16 rounded-full bg-indigo-50 shrink-0 overflow-hidden flex items-center justify-center">
                                @if(isset($author->photo))
                                    <img src="{{ asset('storage/'.$author->photo) }}" class="w-full h-full object-cover" alt="{{ $author->name }}">
                                @else
                                    <span class="text-2xl">✍️</span>
                                @endif
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-slate-800">{{ $author->name }}</h3>
                                <p class="text-sm text-slate-600 mt-1 line-clamp-3">{{ $author->bio ?? 'লেখকের সম্পর্কে বিস্তারিত তথ্য দেওয়া নেই।' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
            
            <div class="lg:w-1/3">
                <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 p-6">
                    <h2 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-3">বইয়ের বিবরণ</h2>
                    <ul class="space-y-3 text-sm">
                        <li class="flex py-1 border-b border-slate-50 border-dashed">
                            <span class="w-1/3 text-slate-500">শিরোনাম</span>
                            <span class="w-2/3 font-medium text-slate-800">{{ $book->title }}</span>
                        </li>
                        <li class="flex py-1 border-b border-slate-50 border-dashed">
                            <span class="w-1/3 text-slate-500">লেখক</span>
                            <span class="w-2/3 font-medium text-slate-800">{{ $authorNames }}</span>
                        </li>
                        <li class="flex py-1 border-b border-slate-50 border-dashed">
                            <span class="w-1/3 text-slate-500">প্রকাশনী</span>
                            <span class="w-2/3 font-medium text-slate-800">{{ $book->publisher->name ?? 'অজানা' }}</span>
                        </li>
                        @if($book->isbn)
                        <li class="flex py-1 border-b border-slate-50 border-dashed">
                            <span class="w-1/3 text-slate-500">ISBN</span>
                            <span class="w-2/3 font-medium text-slate-800">{{ $book->isbn }}</span>
                        </li>
                        @endif
                        <li class="flex py-1 border-b border-slate-50 border-dashed">
                            <span class="w-1/3 text-slate-500">ক্যাটাগরি</span>
                            <span class="w-2/3 font-medium text-slate-800">{{ $book->category->name ?? 'সাধারণ' }}</span>
                        </li>
                        @if($book->format)
                        <li class="flex py-1">
                            <span class="w-1/3 text-slate-500">ফরম্যাট</span>
                            <span class="w-2/3 font-medium text-slate-800">{{ ucfirst($book->format) }}</span>
                        </li>
                        @endif
                    </ul>
                </div>
                
                <!-- Delivery Estimator Widget -->
                <div class="bg-indigo-50 rounded-2xl shadow-sm border border-indigo-100 p-6 mt-6">
                    <h2 class="text-md font-bold text-slate-800 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-truck-fast text-indigo-500"></i> ডেলিভারি এস্টিমেট
                    </h2>
                    <div class="space-y-3">
                        <select id="district-selector" class="w-full text-sm border-slate-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 p-2.5">
                            <option value="">আপনার জেলা নির্বাচন করুন</option>
                            <option value="dhaka">ঢাকা সিটি (৳ ৫০)</option>
                            <option value="dhaka_sub">ঢাকার আশেপাশে (৳ ১০০)</option>
                            <option value="outside">ঢাকার বাইরে (৳ ১২০)</option>
                        </select>
                        <div id="delivery-result" class="hidden bg-white p-3 rounded-lg border border-indigo-100 text-sm">
                            <div class="flex justify-between mb-1">
                                <span class="text-slate-500">ডেলিভারি চার্জ:</span>
                                <span class="font-bold text-slate-800" id="delivery-fee"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">আনুমানিক সময়:</span>
                                <span class="font-bold text-indigo-600" id="delivery-time"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Frequently Bought Together -->
        @if(isset($frequentlyBoughtTogether) && $frequentlyBoughtTogether->isNotEmpty())
        <div class="mt-8 bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 p-6 md:p-8">
            <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                <span class="w-1.5 h-6 bg-amber-500 rounded-full inline-block"></span>
                একসাথে কিনুন (Frequently Bought Together)
            </h2>
            
            <div class="flex flex-col md:flex-row items-start md:items-center gap-6 overflow-x-auto pb-4 custom-scrollbar">
                <!-- Main Book -->
                <div class="flex flex-col items-center min-w-[120px] max-w-[120px]">
                    <div class="w-[100px] aspect-[7/10] bg-slate-50 rounded shadow-sm overflow-hidden mb-2 relative">
                        @if($cover)
                            <img src="{{ $coverUrl }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-3xl bg-slate-100 text-slate-300">📘</div>
                        @endif
                        <div class="absolute inset-0 bg-indigo-600/10 border-2 border-indigo-600 rounded"></div>
                    </div>
                    <p class="text-xs font-medium text-center line-clamp-2" title="{{ $book->title }}">{{ $book->title }}</p>
                    <p class="text-sm font-bold text-slate-900 mt-1">৳{{ round($book->discount_price ?? $book->price) }}</p>
                    <input type="checkbox" checked disabled class="mt-2 text-indigo-600 rounded border-slate-300 shadow-sm focus:ring-indigo-500">
                </div>
                
                @foreach($frequentlyBoughtTogether as $fbtBook)
                    <!-- Plus Sign -->
                    <div class="hidden md:flex text-2xl font-bold text-slate-300 items-center h-[142px]">+</div>
                    
                    <!-- FBT Book -->
                    <div class="flex flex-col items-center min-w-[120px] max-w-[120px]">
                        <a href="{{ route('book.show', $fbtBook->slug) }}" class="w-[100px] aspect-[7/10] bg-slate-50 rounded shadow-sm overflow-hidden mb-2 hover:shadow-md transition-shadow">
                            @php
                                $fbtCover = $fbtBook->cover_image;
                                if ($fbtCover) {
                                    $fbtCoverUrl = str_starts_with($fbtCover, 'http') ? $fbtCover : (str_starts_with($fbtCover, '/storage/') ? asset(ltrim($fbtCover, '/')) : asset('storage/' . $fbtCover));
                                }
                            @endphp
                            @if($fbtCover)
                                <img src="{{ $fbtCoverUrl }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-3xl bg-slate-100 text-slate-300">📘</div>
                            @endif
                        </a>
                        <a href="{{ route('book.show', $fbtBook->slug) }}" class="text-xs font-medium text-center line-clamp-2 hover:text-indigo-600 transition-colors" title="{{ $fbtBook->title }}">{{ $fbtBook->title }}</a>
                        <p class="text-sm font-bold text-slate-900 mt-1">৳{{ round($fbtBook->discount_price ?? $fbtBook->price) }}</p>
                        <input type="checkbox" checked class="mt-2 text-indigo-600 rounded border-slate-300 shadow-sm focus:ring-indigo-500 cursor-pointer">
                    </div>
                @endforeach
                
                <!-- Total Box -->
                <div class="ml-auto w-full md:w-auto p-5 bg-slate-50 rounded-xl border border-slate-200 shrink-0 shadow-inner">
                    <p class="text-sm font-medium text-slate-500 mb-1">মোট {{ $frequentlyBoughtTogether->count() + 1 }}টি আইটেম</p>
                    @php
                        $totalPrice = ($book->discount_price ?? $book->price) + $frequentlyBoughtTogether->sum(fn($b) => $b->discount_price ?? $b->price);
                    @endphp
                    <p class="text-3xl font-black text-slate-900 mb-4">৳{{ round($totalPrice) }}</p>
                    <button class="w-full px-6 py-3 rounded-lg bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition-colors shadow-md">
                        একসাথে কার্টে রাখুন
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Customer Reviews -->
        <div class="mt-8 bg-white rounded-2xl shadow-sm ring-1 ring-slate-100 p-6 md:p-8">
            <h2 class="text-2xl font-bold text-slate-800 mb-6 border-b border-slate-100 pb-4">কাস্টমার রিভিউ ও রেটিং</h2>
            
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Rating Summary -->
                <div class="md:w-1/3 flex flex-col items-center justify-center p-6 bg-slate-50 rounded-xl border border-slate-100 shrink-0">
                    <div class="text-6xl font-black text-slate-900 mb-2">{{ $book->reviews_avg_rating ? number_format($book->reviews_avg_rating, 1) : '0.0' }}</div>
                    <div class="flex text-amber-400 mb-2">
                        @for($i=1; $i<=5; $i++)
                            @if($i <= round($book->reviews_avg_rating ?? 0))
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            @else
                                <svg class="w-6 h-6 text-slate-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            @endif
                        @endfor
                    </div>
                    <p class="text-sm font-medium text-slate-500 mb-5">{{ $book->reviews_count ?? 0 }} টি রেটিং</p>
                    <button class="w-full py-2.5 border-2 border-indigo-600 text-indigo-600 font-bold rounded-lg hover:bg-indigo-50 transition-colors">
                        রিভিউ লিখুন
                    </button>
                </div>
                
                <!-- Review List -->
                <div class="md:w-2/3">
                    @if(isset($book->reviews) && $book->reviews->isNotEmpty())
                        <div class="flex flex-col gap-6 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                            @foreach($book->reviews as $review)
                                <div class="border-b border-slate-100 pb-6 last:border-0 last:pb-0">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold uppercase overflow-hidden shadow-sm">
                                            @if($review->user && $review->user->avatar)
                                                <img src="{{ asset('storage/' . $review->user->avatar) }}" class="w-full h-full object-cover">
                                            @else
                                                {{ substr($review->user->name ?? 'U', 0, 1) }}
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 leading-none">{{ $review->user->name ?? 'Anonymous User' }}</p>
                                            <div class="flex items-center gap-2 mt-1.5">
                                                <div class="flex text-amber-400">
                                                    @for($i=1; $i<=5; $i++)
                                                        @if($i <= $review->rating)
                                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                        @else
                                                            <svg class="w-3.5 h-3.5 text-slate-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                                        @endif
                                                    @endfor
                                                </div>
                                                <span class="text-[11px] text-slate-400">{{ $review->created_at ? $review->created_at->diffForHumans() : 'কিছুক্ষণ আগে' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-slate-600 text-sm mt-2 leading-relaxed">{{ $review->comment }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="h-full flex flex-col items-center justify-center text-center py-10 bg-slate-50/50 rounded-xl border border-slate-100 border-dashed">
                            <div class="text-5xl mb-4 text-slate-200">💬</div>
                            <h3 class="text-lg font-bold text-slate-700">কোনো রিভিউ নেই</h3>
                            <p class="text-slate-500 text-sm mt-1">আপনি প্রথম ব্যক্তি হিসেবে এই বইটির রিভিউ দিতে পারেন!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Related Books -->
        @if(isset($relatedBooks) && $relatedBooks->isNotEmpty())
        <div class="mt-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-slate-800">সম্পর্কিত বইসমূহ</h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-5 lg:gap-6">
                @foreach($relatedBooks as $relatedBook)
                    @include('book::frontend.partials.book-card', ['book' => $relatedBook])
                @endforeach
            </div>
        </div>
        @endif

    </div>
    </section>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selector = document.getElementById('district-selector');
            const resultBox = document.getElementById('delivery-result');
            const feeEl = document.getElementById('delivery-fee');
            const timeEl = document.getElementById('delivery-time');

            if (selector) {
                selector.addEventListener('change', function() {
                    if (this.value === 'dhaka') {
                        feeEl.textContent = '৳ ৫০';
                        timeEl.textContent = '১-২ দিন';
                        resultBox.classList.remove('hidden');
                    } else if (this.value === 'dhaka_sub') {
                        feeEl.textContent = '৳ ১০০';
                        timeEl.textContent = '২-৩ দিন';
                        resultBox.classList.remove('hidden');
                    } else if (this.value === 'outside') {
                        feeEl.textContent = '৳ ১২০';
                        timeEl.textContent = '৩-৫ দিন';
                        resultBox.classList.remove('hidden');
                    } else {
                        resultBox.classList.add('hidden');
                    }
                });
            }
        });
    </script>
    @endpush
    <!-- Order Modal -->
    <div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-xl border-0 shadow-lg">
                <div class="modal-header bg-indigo-50 border-b border-indigo-100 rounded-t-xl pb-3">
                    <h5 class="modal-title font-bold text-slate-800 text-lg flex items-center gap-2" id="orderModalLabel">
                        <i class="fa-solid fa-cart-shopping text-indigo-600"></i> অর্ডার করুন
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('orders.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                    <div class="modal-body p-4 md:p-5">
                        <div class="row g-4">
                            <!-- Book Info -->
                            <div class="col-md-5 border-end border-slate-100">
                                <div class="flex gap-3 mb-4">
                                    <div class="w-16 h-24 bg-slate-100 rounded overflow-hidden shrink-0">
                                        @if($book->cover_image)
                                            <img src="{{ str_starts_with($book->cover_image, 'http') ? $book->cover_image : asset('storage/' . $book->cover_image) }}" class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-sm line-clamp-2">{{ $book->title }}</h4>
                                        <p class="text-xs text-slate-500 mt-1">{{ $authorNames }}</p>
                                        <div class="mt-2 text-indigo-600 font-bold">
                                            ৳{{ round($book->discount_price ?? $book->price) }}
                                        </div>
                                    </div>
                                </div>
                                
                                <h6 class="font-bold text-slate-800 text-sm mb-3">আপনার তথ্য</h6>
                                <div class="mb-3">
                                    <input type="text" name="customer_name" class="form-control text-sm rounded-lg" required placeholder="আপনার নাম">
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="customer_phone" class="form-control text-sm rounded-lg" required placeholder="মোবাইল নম্বর">
                                </div>
                                <div class="mb-3">
                                    <select name="district" class="form-select text-sm rounded-lg" required>
                                        <option value="">জেলা নির্বাচন করুন</option>
                                        <option value="dhaka">ঢাকা সিটি</option>
                                        <option value="dhaka_sub">ঢাকার আশেপাশে</option>
                                        <option value="outside">ঢাকার বাইরে</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <textarea name="customer_address" class="form-control text-sm rounded-lg" rows="2" required placeholder="সম্পূর্ণ ঠিকানা"></textarea>
                                </div>
                            </div>
                            
                            <!-- Gift Options -->
                            <div class="col-md-7">
                                <div class="bg-amber-50 rounded-xl p-4 border border-amber-100 mb-4 h-100">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input cursor-pointer" type="checkbox" name="is_gift" value="1" id="isGiftToggle">
                                        <label class="form-check-label font-bold text-amber-800 cursor-pointer flex items-center gap-2" for="isGiftToggle">
                                            <i class="fa-solid fa-gift"></i> উপহার হিসেবে পাঠাতে চান? (+৳২০ র‍্যাপিং চার্জ)
                                        </label>
                                    </div>
                                    
                                    <div id="giftFields" class="d-none mt-3">
                                        <hr class="border-amber-200 mb-3">
                                        <h6 class="text-xs font-bold text-amber-800 mb-2 uppercase tracking-wider">প্রাপকের তথ্য</h6>
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <input type="text" name="gift_recipient_name" class="form-control text-sm border-amber-200 bg-white" placeholder="প্রাপকের নাম">
                                            </div>
                                            <div class="col-6">
                                                <input type="text" name="gift_recipient_phone" class="form-control text-sm border-amber-200 bg-white" placeholder="মোবাইল নম্বর">
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <textarea name="gift_recipient_address" class="form-control text-sm border-amber-200 bg-white" rows="2" placeholder="প্রাপকের সম্পূর্ণ ঠিকানা"></textarea>
                                        </div>
                                        <div class="mb-2">
                                            <textarea name="gift_message" class="form-control text-sm border-amber-200 bg-white" rows="2" placeholder="উপহারের সাথে একটি মেসেজ (যেমন: শুভ জন্মদিন!)"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-t border-slate-100 bg-slate-50 rounded-b-xl">
                        <button type="button" class="btn btn-light text-slate-600 font-medium px-4 py-2 text-sm" data-bs-dismiss="modal">বাতিল করুন</button>
                        <button type="submit" class="btn btn-primary bg-indigo-600 border-0 font-medium px-5 py-2 text-sm shadow-sm hover:bg-indigo-700">
                            <i class="fa-solid fa-check me-1"></i> অর্ডার কনফার্ম করুন
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const giftToggle = document.getElementById('isGiftToggle');
            const giftFields = document.getElementById('giftFields');
            if (giftToggle && giftFields) {
                giftToggle.addEventListener('change', function() {
                    if (this.checked) {
                        giftFields.classList.remove('d-none');
                        // Add required attrs
                        giftFields.querySelectorAll('input, textarea:not([name="gift_message"])').forEach(el => el.required = true);
                    } else {
                        giftFields.classList.add('d-none');
                        // Remove required attrs
                        giftFields.querySelectorAll('input, textarea').forEach(el => el.required = false);
                    }
                });
            }
        });
    </script>
@endsection
