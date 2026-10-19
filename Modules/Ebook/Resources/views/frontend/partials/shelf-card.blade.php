@php
    $eb = $ebook;
    $url = route('ebook.show', $eb->slug ?: $eb->id);
    $regular = (float) ($eb->price ?: 0);
    $offer = ($eb->discount_price > 0 && $eb->discount_price < $regular) ? (float) $eb->discount_price : null;
    $free = !empty($eb->is_free) || $regular <= 0 || ($offer !== null && $offer <= 0);
    $now = $offer ?? $regular;
    $off = ($offer && $regular > 0) ? (int) round((($regular - $offer) / $regular) * 100) : 0;
    $by = optional($eb->author)->name ?: ($eb->author_name ?: 'আইডিয়া প্রকাশন');
    $rating = max(0, min(5, (float) ($eb->reviews_avg_rating ?? 5)));
    $owned = !empty($userLibraryIds) && in_array($eb->id, $userLibraryIds);
@endphp
<article class="eb-card">
    <a href="{{ $url }}" class="eb-card__cover" aria-label="{{ $eb->title }}">
        @if($eb->cover_url)
            <img src="{{ $eb->cover_url }}" alt="" width="300" height="450" loading="lazy" decoding="async" onerror="this.remove()">
        @endif
        <span class="eb-card__fallback">{{ mb_substr($eb->title, 0, 1) }}</span>
        @if($owned)<span class="eb-flag eb-flag--own">Owned</span>
        @elseif($free)<span class="eb-flag eb-flag--free">Free</span>
        @elseif($off)<span class="eb-flag eb-flag--off">-{{ $off }}%</span>@endif
        @if($eb->format_badge)<span class="eb-fmt">{{ $eb->format_badge }}</span>@endif
    </a>
    <div class="eb-card__body">
        <a href="{{ $url }}" class="eb-card__title" title="{{ $eb->title }}">{{ $eb->title }}</a>
        <span class="eb-card__by">{{ $by }}</span>
        <div class="dz-card__price">
            @if($free)
                <span class="dz-card__now">Free</span>
            @else
                <span class="dz-card__now">৳@bn(round($now))</span>
                @if($offer)<s class="dz-card__old">৳@bn(round($regular))</s>@endif
            @endif
        </div>
        <div class="dz-card__stars" role="img" aria-label="{{ number_format($rating, 1) }} / 5">
            @for($i = 1; $i <= 5; $i++)<i class="fa-solid fa-star {{ $i <= round($rating) ? 'on' : '' }}"></i>@endfor
        </div>
    </div>
</article>
