@extends('layouts.app')

@section('title', 'বই কেনাকাটা')

@section('content')
    <section class="container py-6">
        <div class="mb-6 text-center md:text-left hidden">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-900">📚 বই কেনাকাটা</h1>
            <p class="mt-1 text-sm md:text-base text-slate-600">হাজারো বই থেকে আপনার পছন্দের বই খুঁজে নিন</p>
        </div>

        @if(!isset($isSearchMode) || !$isSearchMode)
        <!-- Hero Carousel & Quick Formats (Visible only on Home) -->
        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <!-- Main Slider -->
            <div class="flex-1 rounded-2xl overflow-hidden bg-slate-900 relative shadow-sm h-[200px] md:h-[300px]">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-900 to-indigo-800 opacity-90"></div>
                <div class="absolute inset-0 flex flex-col justify-center px-8 md:px-12 text-white">
                    <span class="bg-blue-500 text-white text-xs font-bold px-2.5 py-1 rounded w-max mb-3 uppercase tracking-wider">Big Discount</span>
                    <h2 class="text-2xl md:text-4xl font-black mb-2 leading-tight">বইমেলা<br>উপলক্ষে বিশেষ ছাড়!</h2>
                    <p class="text-blue-100 text-sm md:text-base mb-5 max-w-sm">বেস্টসেলার বইগুলোতে পাচ্ছেন সর্বোচ্চ ৪০% পর্যন্ত নিশ্চিত ছাড়। আজই আপনার পছন্দের বইটি সংগ্রহ করুন।</p>
                    <a href="#" class="bg-white text-indigo-900 font-bold px-5 py-2.5 rounded-lg w-max hover:bg-indigo-50 transition-colors shadow-sm">শপিং শুরু করুন</a>
                </div>
                <!-- Dummy image decoration -->
                <div class="absolute right-0 bottom-0 top-0 w-1/3 opacity-20 pointer-events-none" style="background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(255,255,255,0.1) 10px, rgba(255,255,255,0.1) 20px);"></div>
            </div>

            <!-- Shop by Format Banners -->
            <div class="w-full md:w-[280px] flex flex-row md:flex-col gap-4">
                <a href="{{ route('book.index', ['format' => 'paperback']) }}" class="flex-1 md:h-1/2 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-5 text-white shadow-sm relative overflow-hidden group">
                    <div class="relative z-10">
                        <h3 class="text-lg font-black mb-1">পেপারব্যাক</h3>
                        <p class="text-emerald-100 text-xs">নিয়মিত পড়ার জন্য</p>
                    </div>
                    <i class="fa-solid fa-book absolute -right-2 -bottom-2 text-6xl opacity-20 group-hover:scale-110 transition-transform"></i>
                </a>
                <a href="{{ route('book.index', ['format' => 'ebook']) }}" class="flex-1 md:h-1/2 rounded-2xl bg-gradient-to-br from-purple-500 to-fuchsia-600 p-5 text-white shadow-sm relative overflow-hidden group">
                    <div class="relative z-10">
                        <h3 class="text-lg font-black mb-1">ইবুক (PDF)</h3>
                        <p class="text-purple-100 text-xs">স্মার্টফোনে পড়ার জন্য</p>
                    </div>
                    <i class="fa-solid fa-mobile-screen absolute -right-2 -bottom-2 text-6xl opacity-20 group-hover:scale-110 transition-transform"></i>
                </a>
            </div>
        </div>
        @endif

        <div class="flex flex-row gap-4 lg:gap-5 items-start">
            <!-- Sidebar Filters -->
            <aside class="w-[240px] shrink-0">
                <form action="{{ route('book.index') }}" method="GET" id="filter-form" class="sticky top-24 flex flex-col gap-4">
                    
                    <div class="flex items-center justify-between px-1 mb-1">
                        <h2 class="font-bold text-slate-800 text-base">ফিল্টার করুন</h2>
                        <a href="{{ route('book.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-2 py-1 rounded transition-colors">Reset All</a>
                    </div>

                    <!-- Top Seller Book in Sidebar -->
                    @if(isset($topSeller) && $topSeller)
                    <div class="bg-white rounded-xl p-3 shadow-sm border border-amber-200 bg-gradient-to-b from-amber-50 to-white relative overflow-hidden mb-3">
                        <div class="absolute top-0 right-0 bg-amber-500 text-white text-[9px] font-black px-2 py-1 rounded-bl-lg tracking-wider uppercase shadow-sm z-10">Bestseller</div>
                        <h3 class="font-bold text-slate-800 text-[13px] mb-3 flex items-center gap-1.5 text-amber-700">
                            <i class="fa-solid fa-crown text-amber-500"></i> টপ সেল বুক
                        </h3>
                        <a href="{{ route('book.show', $topSeller->slug) }}" class="flex flex-col gap-3 group items-center text-center">
                            <div class="w-[120px] aspect-[7/10] bg-slate-100 rounded-md shadow-sm overflow-hidden mx-auto border border-amber-100">
                                @if($topSeller->cover_image)
                                    <img src="{{ str_starts_with($topSeller->cover_image, 'http') ? $topSeller->cover_image : asset('storage/' . $topSeller->cover_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-3xl bg-slate-100 text-slate-300">📘</div>
                                @endif
                            </div>
                            <div class="flex flex-col w-full">
                                <h4 class="text-[13px] font-bold text-slate-800 group-hover:text-indigo-600 line-clamp-2 leading-snug">{{ $topSeller->title }}</h4>
                                <p class="text-[11px] text-slate-500 line-clamp-1 mt-1">{{ $topSeller->authors->isNotEmpty() ? $topSeller->authors->pluck('name')->join(', ') : ($topSeller->author_name ?: 'অজানা লেখক') }}</p>
                                <div class="flex items-center justify-center gap-1.5 text-[13px] font-black text-slate-900 mt-1.5">
                                    @if($topSeller->discount_price && $topSeller->discount_price < $topSeller->price)
                                        <span class="text-[11px] text-rose-500 line-through font-medium">৳{{ round($topSeller->price) }}</span>
                                    @endif
                                    ৳{{ round($topSeller->discount_price ?? $topSeller->price) }}
                                </div>
                            </div>
                        </a>
                    </div>
                    @endif

                    <!-- Search -->
                    <div class="bg-white rounded p-3 shadow-sm border border-slate-200">
                        <div class="flex gap-1.5">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="নাম বা লেখক..." class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 px-2.5 text-[12px] focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-200" />
                            <button type="submit" class="rounded-lg bg-indigo-600 px-2.5 py-1.5 text-[12px] font-semibold text-white shadow hover:bg-indigo-700 transition-colors">খুঁজুন</button>
                        </div>
                    </div>

                    <!-- In Stock -->
                    <div class="bg-white rounded p-3 shadow-sm border border-slate-200 flex items-center justify-between cursor-pointer" onclick="document.getElementById('in_stock').click()">
                        <label for="in_stock" class="text-[13px] font-medium text-slate-700 cursor-pointer">In Stock Only</label>
                        <input type="checkbox" id="in_stock" name="in_stock" value="1" {{ request('in_stock') ? 'checked' : '' }} onchange="this.form.submit()" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 w-4 h-4 cursor-pointer">
                    </div>

                    <!-- Categories -->
                    <div class="bg-white rounded p-3 shadow-sm border border-slate-200">
                        <h3 class="font-bold text-slate-800 text-[13px] mb-2 uppercase tracking-wider">Shop by Categories</h3>
                        <div class="flex flex-col gap-1.5 max-h-56 overflow-y-auto pr-1 custom-scrollbar">
                            @foreach($categories as $category)
                            <label class="flex items-start gap-2 text-[12px] text-slate-600 cursor-pointer group">
                                <input type="radio" name="category" value="{{ $category->slug }}" onchange="this.form.submit()" {{ request('category') == $category->slug ? 'checked' : '' }} class="mt-0.5 text-indigo-600 border-slate-300 focus:ring-indigo-200 w-3 h-3 shrink-0"> 
                                <span class="group-hover:text-indigo-600 flex-1 leading-snug">{{ $category->name }}</span>
                                <span class="text-[9px] text-slate-400 bg-slate-50 px-1 py-0.5 rounded leading-none shrink-0">{{ $category->books_count }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Authors -->
                    @if(isset($sidebarAuthors) && $sidebarAuthors->isNotEmpty())
                    <div class="bg-white rounded p-3 shadow-sm border border-slate-200">
                        <h3 class="font-bold text-slate-800 text-[13px] mb-2 uppercase tracking-wider">By Authors</h3>
                        <div class="flex flex-col gap-1.5 max-h-56 overflow-y-auto pr-1 custom-scrollbar">
                            @foreach($sidebarAuthors as $author)
                            <label class="flex items-start gap-2 text-[12px] text-slate-600 cursor-pointer group">
                                <input type="radio" name="author" value="{{ $author->slug }}" onchange="this.form.submit()" {{ request('author') == $author->slug ? 'checked' : '' }} class="mt-0.5 text-indigo-600 border-slate-300 focus:ring-indigo-200 w-3 h-3 shrink-0"> 
                                <span class="group-hover:text-indigo-600 flex-1 leading-snug">{{ $author->name }}</span>
                                <span class="text-[9px] text-slate-400 bg-slate-50 px-1 py-0.5 rounded leading-none shrink-0">{{ $author->books_count }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Publishers -->
                    @if(isset($sidebarPublishers) && $sidebarPublishers->isNotEmpty())
                    <div class="bg-white rounded p-3 shadow-sm border border-slate-200">
                        <h3 class="font-bold text-slate-800 text-[13px] mb-2 uppercase tracking-wider">By Publishers</h3>
                        <div class="flex flex-col gap-1.5 max-h-56 overflow-y-auto pr-1 custom-scrollbar">
                            @foreach($sidebarPublishers as $publisher)
                            <label class="flex items-start gap-2 text-[12px] text-slate-600 cursor-pointer group">
                                <input type="radio" name="publisher" value="{{ $publisher->slug }}" onchange="this.form.submit()" {{ request('publisher') == $publisher->slug ? 'checked' : '' }} class="mt-0.5 text-indigo-600 border-slate-300 focus:ring-indigo-200 w-3 h-3 shrink-0"> 
                                <span class="group-hover:text-indigo-600 flex-1 leading-snug">{{ $publisher->name }}</span>
                                <span class="text-[9px] text-slate-400 bg-slate-50 px-1 py-0.5 rounded leading-none shrink-0">{{ $publisher->books_count }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Ratings -->
                    <div class="bg-white rounded p-3 shadow-sm border border-slate-200">
                        <h3 class="font-bold text-slate-800 text-[13px] mb-2 uppercase tracking-wider">Ratings</h3>
                        <div class="flex flex-col gap-1.5">
                            @php $currentRating = request('rating', ''); @endphp
                            @foreach([5, 4, 3, 2, 1] as $star)
                            <label class="flex items-center gap-2 text-[12px] text-slate-600 cursor-pointer group">
                                <input type="radio" name="rating" value="{{ $star }}" onchange="this.form.submit()" {{ $currentRating == $star ? 'checked' : '' }} class="text-indigo-600 border-slate-300 focus:ring-indigo-200 w-3 h-3 shrink-0"> 
                                <div class="flex text-amber-400 gap-0.5">
                                    @for($i=1; $i<=5; $i++)
                                        @if($i <= $star)
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                        @else
                                            <svg class="w-3 h-3 text-slate-200" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                        @endif
                                    @endfor
                                </div>
                                <span class="group-hover:text-indigo-600">& Up</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </form>
            </aside>

            <!-- Main Content Grid -->
            <div class="flex-1 flex flex-col gap-4 min-w-0 w-full">
                @if($isSearchMode || request()->anyFilled(['category', 'author', 'publisher', 'in_stock', 'rating', 'sort']))
                    <!-- Search Results -->
                    <div class="bg-white rounded p-4 md:p-5 shadow-sm border border-slate-200 w-full overflow-hidden">
                        <div class="mb-5 flex flex-col md:flex-row md:items-center justify-between border-b border-slate-100 pb-3 gap-3">
                            <h2 class="text-base md:text-lg font-bold text-slate-800">অনুসন্ধানের ফলাফল ({{ $books->total() ?? 0 }})</h2>
                            
                            <!-- Top Sort Bar -->
                            <div class="flex items-center gap-2">
                                <label for="sort" class="text-sm text-slate-600 whitespace-nowrap">Sort By:</label>
                                @php $currentSort = request('sort', ''); @endphp
                                <select name="sort" id="sort" form="filter-form" onchange="document.getElementById('filter-form').submit()" class="text-sm border-slate-200 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 py-1.5 pl-3 pr-8">
                                    <option value="latest" {{ $currentSort == 'latest' || $currentSort == '' ? 'selected' : '' }}>New Released</option>
                                    <option value="bestselling" {{ $currentSort == 'bestselling' ? 'selected' : '' }}>Best Seller</option>
                                    <option value="price_low" {{ $currentSort == 'price_low' ? 'selected' : '' }}>Price - Low to High</option>
                                    <option value="price_high" {{ $currentSort == 'price_high' ? 'selected' : '' }}>Price - High to Low</option>
                                    <option value="discount_high" {{ $currentSort == 'discount_high' ? 'selected' : '' }}>Top Discount</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 md:gap-3 w-full">
                            @forelse($books as $book)
                                @include('book::frontend.partials.book-card', ['book' => $book])
                            @empty
                            <div class="col-span-full py-12 md:py-16 text-center text-slate-500 bg-slate-50 rounded-xl border border-slate-200 border-dashed">
                                <div class="text-4xl md:text-5xl mb-3 opacity-80">😕</div>
                                <p class="text-base md:text-lg font-medium text-slate-700">কোনো বই পাওয়া যায়নি!</p>
                                <p class="text-xs md:text-sm text-slate-500 mt-1">আপনি যা খুঁজছেন তা আমাদের কালেকশনে নেই।</p>
                                
                                <div class="mt-4 flex flex-wrap justify-center gap-3">
                                    <a href="{{ route('book.index') }}" class="bg-white border border-slate-300 text-slate-700 font-medium px-4 py-2 rounded-lg hover:bg-slate-50 transition-colors text-sm shadow-sm">
                                        <i class="fa-solid fa-arrow-left me-1"></i> সব বই দেখুন
                                    </a>
                                    <!-- Request Form Button -->
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#bookRequestModal" class="bg-indigo-600 border border-indigo-600 text-white font-medium px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors text-sm shadow-sm">
                                        <i class="fa-solid fa-code-pull-request me-1"></i> বইটি রিকোয়েস্ট করুন
                                    </button>
                                </div>
                            </div>
                            @endforelse
                        </div>
                        
                        <!-- Pagination -->
                        @if(isset($books) && $books->hasPages())
                        <div class="mt-6 border-t border-slate-100 pt-5">
                            {{ $books->appends(request()->query())->links() }}
                        </div>
                        @endif
                    </div>
                @else
                    <!-- Sections Layout -->
                    @if(isset($flashSales) && $flashSales->isNotEmpty())
                    <div class="bg-white rounded-xl p-4 md:p-5 shadow-sm ring-1 ring-slate-100 border-2 border-indigo-100 relative overflow-hidden">
                        <div class="absolute -right-10 -top-10 w-32 h-32 bg-indigo-50 rounded-full opacity-50 pointer-events-none"></div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 border-b border-indigo-50 pb-3 gap-3">
                            <h2 class="text-lg md:text-xl font-bold text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-bolt text-amber-500"></i>
                                ফ্ল্যাশ সেলস
                            </h2>
                            <div class="flex items-center gap-2 bg-indigo-50 px-3 py-1.5 rounded-lg border border-indigo-100">
                                <span class="text-xs font-semibold text-slate-600">অফার শেষ হতে বাকি:</span>
                                <div class="flex items-center gap-1 text-indigo-700 font-bold" id="flash-countdown">
                                    <span class="bg-white px-1.5 py-0.5 rounded shadow-sm text-sm" id="cd-h">03</span>:
                                    <span class="bg-white px-1.5 py-0.5 rounded shadow-sm text-sm" id="cd-m">45</span>:
                                    <span class="bg-white px-1.5 py-0.5 rounded shadow-sm text-sm" id="cd-s">12</span>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 md:gap-3 w-full">
                            @foreach($flashSales as $book)
                                @include('book::frontend.partials.book-card', ['book' => $book])
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(isset($recentlySold) && $recentlySold->isNotEmpty())
                    <div class="bg-white rounded-xl p-4 md:p-5 shadow-sm ring-1 ring-slate-100">
                        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-2">
                            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                <span class="w-1.5 h-5 bg-rose-500 rounded-full inline-block"></span>
                                Recently sold book products
                            </h2>
                            <a href="{{ route('book.index', ['sort' => 'bestselling']) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded hover:bg-indigo-100 transition-colors">সবগুলো দেখুন</a>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 md:gap-3 w-full">
                            @foreach($recentlySold as $book)
                                @include('book::frontend.partials.book-card', ['book' => $book])
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(isset($bestSellerEbooks) && $bestSellerEbooks->isNotEmpty())
                    <div class="bg-white rounded-xl p-4 md:p-5 shadow-sm ring-1 ring-slate-100">
                        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-2">
                            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                <span class="w-1.5 h-5 bg-amber-500 rounded-full inline-block"></span>
                                Weekly Best Seller Ebook
                            </h2>
                            <a href="{{ route('book.index', ['format' => 'ebook']) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded hover:bg-indigo-100 transition-colors">সবগুলো দেখুন</a>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 md:gap-3 w-full">
                            @foreach($bestSellerEbooks as $book)
                                @include('book::frontend.partials.book-card', ['book' => $book])
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(isset($categoryBooks) && !empty($categoryBooks))
                        @php
                            $colors = ['bg-indigo-500', 'bg-emerald-500', 'bg-blue-500', 'bg-purple-500', 'bg-pink-500', 'bg-orange-500', 'bg-teal-500'];
                            $bgColors = ['bg-indigo-50/40', 'bg-emerald-50/40', 'bg-blue-50/40', 'bg-purple-50/40', 'bg-pink-50/40', 'bg-orange-50/40', 'bg-teal-50/40'];
                            $colorIndex = 0;
                        @endphp
                        @foreach($categoryBooks as $sectionName => $booksList)
                        <div class="{{ $bgColors[$colorIndex % count($bgColors)] }} rounded-xl p-4 md:p-5 shadow-sm ring-1 ring-slate-100">
                            <div class="flex items-center justify-between mb-4 border-b border-slate-200/50 pb-2">
                                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <span class="w-1.5 h-5 {{ $colors[$colorIndex % count($colors)] }} rounded-full inline-block"></span>
                                    {{ $sectionName }}
                                </h2>
                                <a href="{{ route('book.index', ['category' => Str::slug($sectionName)]) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded hover:bg-indigo-100 transition-colors">সবগুলো দেখুন</a>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 md:gap-3 w-full">
                                @foreach($booksList as $book)
                                    @include('book::frontend.partials.book-card', ['book' => $book])
                                @endforeach
                            </div>
                        </div>
                        @php $colorIndex++; @endphp
                        @endforeach
                    @endif

                    <!-- Popular Authors & Publishers -->
                    @if(isset($sidebarAuthors) && $sidebarAuthors->isNotEmpty())
                    <div class="bg-white rounded-xl p-4 md:p-5 shadow-sm ring-1 ring-slate-100 mt-2">
                        <h2 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">জনপ্রিয় লেখক</h2>
                        <div class="flex overflow-x-auto gap-4 pb-2 custom-scrollbar snap-x">
                            @foreach($sidebarAuthors as $author)
                            <a href="{{ route('book.index', ['author' => $author->slug]) }}" class="flex flex-col items-center gap-2 min-w-[80px] snap-start group">
                                <div class="w-16 h-16 rounded-full bg-slate-100 overflow-hidden ring-2 ring-transparent group-hover:ring-indigo-500 transition-all">
                                    @if(isset($author->photo) && $author->photo)
                                        <img src="{{ asset('storage/' . $author->photo) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-400 bg-slate-200 text-xl"><i class="fa-solid fa-user"></i></div>
                                    @endif
                                </div>
                                <span class="text-xs font-medium text-slate-700 group-hover:text-indigo-600 text-center line-clamp-1">{{ $author->name }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Recently Viewed -->
                    @if(isset($recentlyViewedBooks) && $recentlyViewedBooks->isNotEmpty())
                    <div class="bg-slate-50 rounded-xl p-4 md:p-5 shadow-sm ring-1 ring-slate-200 mt-2">
                        <div class="flex items-center justify-between mb-4 border-b border-slate-200 pb-2">
                            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-clock-rotate-left text-indigo-500"></i>
                                আপনি সম্প্রতি দেখেছেন
                            </h2>
                        </div>
                        <div class="flex overflow-x-auto gap-3 pb-2 custom-scrollbar snap-x">
                            @foreach($recentlyViewedBooks as $book)
                            <div class="min-w-[140px] max-w-[150px] snap-start">
                                @include('book::frontend.partials.book-card', ['book' => $book])
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Newsletter Subscription -->
                    <div class="bg-indigo-600 rounded-xl p-6 md:p-8 mt-4 text-center relative overflow-hidden shadow-md">
                        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 20px 20px;"></div>
                        <div class="relative z-10 max-w-lg mx-auto">
                            <i class="fa-regular fa-envelope-open mb-3 text-4xl text-indigo-200"></i>
                            <h2 class="text-xl md:text-2xl font-bold text-white mb-2">আমাদের নিউজলেটারে সাবস্ক্রাইব করুন</h2>
                            <p class="text-indigo-100 text-sm mb-5">নতুন বইয়ের আপডেট, রিভিউ এবং এক্সক্লুসিভ অফার পেতে ইমেইল দিয়ে যুক্ত থাকুন!</p>
                            <form action="#" method="POST" class="flex flex-col sm:flex-row gap-2" onsubmit="event.preventDefault(); alert('ধন্যবাদ! আপনি সফলভাবে সাবস্ক্রাইব করেছেন।');">
                                <input type="email" placeholder="আপনার ইমেইল অ্যাড্রেস লিখুন..." required class="flex-1 rounded-lg border-0 py-2.5 px-4 focus:ring-2 focus:ring-white bg-white/10 text-white placeholder-indigo-200">
                                <button type="submit" class="bg-white text-indigo-600 font-bold px-6 py-2.5 rounded-lg hover:bg-indigo-50 transition-colors shadow-sm">সাবস্ক্রাইব</button>
                            </form>
                        </div>
                    </div>
                    
                    @if((!isset($recentlySold) || $recentlySold->isEmpty()) && (!isset($bestSellerEbooks) || $bestSellerEbooks->isEmpty()) && empty($categoryBooks) && (!isset($flashSales) || $flashSales->isEmpty()))
                    <div class="py-16 text-center text-slate-500 bg-white rounded-xl ring-1 ring-slate-100 shadow-sm">
                        <div class="text-4xl mb-4">😕</div>
                        <p class="text-base">বর্তমানে প্রদর্শনের জন্য কোনো বই নেই।</p>
                        <p class="text-xs text-slate-400 mt-2">অ্যাডমিন প্যানেল থেকে এই ক্যাটাগরিগুলোতে বই যোগ করুন।</p>
                    </div>
                    @endif
                @endif
            </div>
        </div>
    </section>

    @if(!isset($isSearchMode) || !$isSearchMode)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dummy Countdown Timer (3 hours from load)
            let endTime = new Date().getTime() + (3 * 60 * 60 * 1000) + (45 * 60 * 1000) + (12 * 1000);
            
            const timer = setInterval(function() {
                let now = new Date().getTime();
                let distance = endTime - now;
                
                if (distance < 0) {
                    clearInterval(timer);
                    return;
                }
                
                let h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                let m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                let s = Math.floor((distance % (1000 * 60)) / 1000);
                
                const format = val => val < 10 ? '0' + val : val;
                
                let elH = document.getElementById('cd-h');
                let elM = document.getElementById('cd-m');
                let elS = document.getElementById('cd-s');
                
                if (elH) elH.innerText = format(h);
                if (elM) elM.innerText = format(m);
                if (elS) elS.innerText = format(s);
            }, 1000);
        });
    </script>
    @endif
    <!-- Book Request Modal -->
    <div class="modal fade" id="bookRequestModal" tabindex="-1" aria-labelledby="bookRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-xl border-0 shadow-lg">
                <div class="modal-header bg-indigo-50 border-b border-indigo-100 rounded-t-xl pb-3">
                    <h5 class="modal-title font-bold text-slate-800 text-lg flex items-center gap-2" id="bookRequestModalLabel">
                        <i class="fa-solid fa-code-pull-request text-indigo-600"></i> বইটি রিকোয়েস্ট করুন
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('book-requests.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4 md:p-5">
                        <p class="text-sm text-slate-500 mb-4">বইটি বর্তমানে আমাদের কালেকশনে নেই। নিচের ফর্মটি পূরণ করুন, আমরা দ্রুত বইটি সংগ্রহ করে আপনাকে জানাবো।</p>
                        
                        <div class="mb-3">
                            <label class="form-label text-sm font-semibold text-slate-700">বইয়ের নাম <span class="text-rose-500">*</span></label>
                            <input type="text" name="book_title" value="{{ request('search') }}" class="form-control rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-200" required placeholder="যেমন: প্যারাডক্সিক্যাল সাজিদ">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-sm font-semibold text-slate-700">লেখকের নাম</label>
                            <input type="text" name="author_name" class="form-control rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-200" placeholder="লেখকের নাম (যদি জানা থাকে)">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label text-sm font-semibold text-slate-700">আপনার নাম</label>
                                <input type="text" name="customer_name" class="form-control rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-200" placeholder="আপনার নাম">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-sm font-semibold text-slate-700">মোবাইল নম্বর</label>
                                <input type="text" name="customer_phone" class="form-control rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-200" placeholder="01XXXXXXXXX">
                            </div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label text-sm font-semibold text-slate-700">অতিরিক্ত তথ্য (ঐচ্ছিক)</label>
                            <textarea name="additional_info" class="form-control rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-200" rows="2" placeholder="বইটির প্রকাশনী বা অন্য কোনো তথ্য..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-t border-slate-100 bg-slate-50 rounded-b-xl">
                        <button type="button" class="btn btn-light text-slate-600 font-medium px-4 py-2" data-bs-dismiss="modal">বাতিল করুন</button>
                        <button type="submit" class="btn btn-primary bg-indigo-600 border-0 font-medium px-5 py-2 shadow-sm hover:bg-indigo-700">
                            <i class="fa-solid fa-paper-plane me-1"></i> রিকোয়েস্ট পাঠান
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
