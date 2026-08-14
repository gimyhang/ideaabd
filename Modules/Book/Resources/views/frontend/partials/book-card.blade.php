<div class="group flex flex-col bg-white overflow-hidden border border-slate-200 hover:shadow-lg transition-all duration-300 h-full relative p-1 w-full rounded-md">
    <!-- Offer Badge -->
    @if($book->discount_price && $book->discount_price < $book->price)
        @php
            $discountPercentage = round((($book->price - $book->discount_price) / $book->price) * 100);
        @endphp
        <div class="absolute -top-1.5 -left-1.5 z-30 drop-shadow-md w-11 h-11 flex items-center justify-center">
            <svg class="absolute inset-0 w-full h-full text-rose-500 drop-shadow-lg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 0l2.8 2.8 4-.6 1.4 3.8 3.8 1.4-.6 4 2.8 2.8-2.8 2.8.6 4-3.8 1.4-1.4 3.8-4 .6L12 24l-2.8-2.8-4 .6-1.4-3.8-3.8-1.4.6-4-2.8-2.8 2.8-2.8-.6-4 3.8-1.4 1.4-3.8 4-.6L12 0z"/>
            </svg>
            <span class="relative z-10 text-[13px] font-black text-white leading-none mt-0.5">{{ $discountPercentage }}%</span>
        </div>
    @endif

    <!-- Book Image -->
    <div class="relative w-full aspect-[7/10] mx-auto flex-shrink-0 bg-slate-50 mb-2 overflow-hidden rounded-sm">
        
        <!-- Wishlist Button -->
        <button type="button" class="absolute top-1.5 right-1.5 z-30 w-7 h-7 rounded-full bg-white/90 hover:bg-white text-slate-400 hover:text-rose-500 shadow-sm flex items-center justify-center transition-all duration-200" title="উইশলিস্টে যোগ করুন">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
        </button>

        @php
            $cover = $book->cover_image;
            if ($cover) {
                $coverUrl = str_starts_with($cover, 'http') ? $cover : (str_starts_with($cover, '/storage/') ? asset(ltrim($cover, '/')) : asset('storage/' . $cover));
            }
        @endphp
        <a href="{{ route('book.show', $book->slug) }}" class="block w-full h-full">
            @if($cover)
                <img src="{{ $coverUrl }}" alt="{{ $book->title }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" />
            @else
                <!-- Demo Image -->
                <img src="https://placehold.co/130x186/f8fafc/64748b?text=No+Cover" alt="Demo Image" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" />
            @endif
        </a>
        
        <!-- Hover Action Overlay -->
        <div class="absolute inset-0 z-20 bg-slate-900/60 flex flex-col items-center justify-center gap-3 opacity-0 invisible group-hover:visible group-hover:opacity-100 transition-all duration-300">
            <!-- Quick View Button (Redirects to details since modal is missing) -->
            <a href="{{ route('book.show', $book->slug) }}" class="quick-view-btn cursor-pointer w-[120px] bg-white text-slate-800 hover:bg-slate-100 transition-colors text-[12px] py-2 rounded-full font-bold shadow-lg flex justify-center items-center gap-1.5 translate-y-4 group-hover:translate-y-0 duration-300 delay-75" title="এক নজরে দেখুন">
                <i class="fa-solid fa-eye text-[11px]"></i>
                কুইক ভিউ
            </a>

            <!-- Add to Cart Button (Redirects to details since script is missing) -->
            <a href="{{ route('book.show', $book->slug) }}" class="add-to-cart-btn cursor-pointer w-[120px] bg-blue-600 text-white hover:bg-blue-700 transition-colors text-[12px] py-2 rounded-full font-bold shadow-lg flex justify-center items-center gap-1.5 translate-y-4 group-hover:translate-y-0 duration-300 delay-100" title="কার্টে যোগ করুন">
                <i class="fa-solid fa-cart-shopping text-[11px]"></i>
                অ্যাড টু কার্ট
            </a>
        </div>
    </div>
    
    <!-- Book Info (Dynamic with fixed spaces) -->
    <div class="flex flex-col flex-grow text-center px-1 pb-1">
        <!-- Title -->
        <div class="mb-0.5 relative flex items-center justify-center min-h-[36px] w-full">
            <!-- Original Title -->
            <a href="{{ route('book.show', $book->slug) }}" class="block w-full">
                <h3 class="text-[13px] font-bold text-slate-800 hover:text-blue-600 transition-colors line-clamp-2 leading-tight" title="{{ $book->title }}">
                    {{ $book->title }}
                </h3>
            </a>
            
            <!-- See More Button removed from here to prevent hiding text -->
        </div>
        
        <!-- Author -->
        <div class="mb-1 overflow-hidden">
            @php
                $authorNames = $book->authors->isNotEmpty() ? $book->authors->pluck('name')->join(', ') : ($book->author_name ?: 'অজানা লেখক');
            @endphp
            <p class="text-[11px] text-slate-500 line-clamp-1" title="{{ $authorNames }}">
                {{ $authorNames }}
            </p>
        </div>
        
        <!-- Price -->
        <div class="mt-auto flex flex-wrap items-center justify-center gap-x-1.5 gap-y-0 mb-0.5">
            @if($book->discount_price && $book->discount_price < $book->price)
                <span class="text-[12px] text-rose-500 line-through font-medium">৳{{ round($book->price) }}</span>
                <span class="text-[14px] font-bold text-slate-900">৳{{ round($book->discount_price) }}</span>
            @else
                <span class="text-[14px] font-bold text-slate-900">৳{{ round($book->price) }}</span>
            @endif
        </div>
        
        <!-- Rating -->
        <div class="flex items-center justify-center gap-0.5 mb-1.5">
            <div class="flex text-amber-400">
                @for($i=1; $i<=5; $i++)
                    @if($i <= round($book->reviews_avg_rating ?? 0))
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" /></svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" /></svg>
                    @endif
                @endfor
            </div>
            <span class="text-[10px] text-slate-400 ml-0.5">({{ $book->reviews_count ?? 0 }})</span>
        </div>
        
        <!-- Permanent Add to Cart (Mobile Friendly) -->
        <a href="{{ route('book.show', $book->slug) }}" class="mt-auto w-full py-1.5 bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white rounded-md text-[12px] font-bold transition-colors flex items-center justify-center gap-1.5 border border-blue-100 hover:border-blue-600">
            <i class="fa-solid fa-cart-shopping text-[10px]"></i>
            কার্টে রাখুন
        </a>
    </div>
</div>
