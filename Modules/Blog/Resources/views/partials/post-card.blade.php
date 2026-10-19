{{-- Uniform blog card: 16:10 image · category · title · author · date --}}
<article class="bl-card">
    <a href="{{ route('blog.show', $post->slug) }}" class="bl-card__media" aria-label="{{ $post->title }}">
        @if(!empty($imageUrl))
            <img src="{{ $imageUrl }}" alt="" width="480" height="300" loading="lazy" decoding="async"
                 onerror="this.remove()">
        @endif
        @if($post->category)<span class="bl-card__tag">{{ $post->category->name }}</span>@endif
    </a>
    <div class="bl-card__body">
        <a href="{{ route('blog.show', $post->slug) }}" class="bl-card__title">{{ $post->title }}</a>
        <div class="bl-card__meta">
            <span class="bl-card__av">{{ mb_substr($post->author_name ?: 'স', 0, 1) }}</span>
            <span class="bl-card__by">{{ $post->author_name ?: 'সম্পাদকীয়' }}</span>
            <span class="bl-card__dot">·</span>
            <time datetime="{{ optional($post->published_at)->toDateString() }}">{{ optional($post->published_at)->format('d M') }}</time>
            @if($post->view_count)<span class="bl-card__views"><i class="fa-regular fa-eye"></i> {{ number_format($post->view_count) }}</span>@endif
        </div>
    </div>
</article>
