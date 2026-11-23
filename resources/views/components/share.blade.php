{{--
    Share button + share sheet for posts, e-books, webzines, authors and site pages.
      <x-share type="post" :id="$post->id" :url="..." :title="..." text="..." :image="..." subtitle="..." />
    type: post | ebook | webzine | author | page (id = page key, see App\Support\Share::PAGES)
    The sheet (one per page) is filled from the clicked button; every share is counted (ShareController).
    Behaviour: public/js/share.js, look: public/css/share.css. Text only, no icon fonts.
--}}
@props([
    'type',
    'id',
    'url',
    'title',
    'text' => null,
    'image' => null,
    'subtitle' => null,
    'label' => 'শেয়ার',
    'heading' => 'শেয়ার করুন',
    'variant' => 'button',   // button | bar (a labelled strip with the most used channels)
    'count' => true,
])
@php
    $shareKey   = (string) $id;
    $shareCount = $count ? \App\Support\Share::count($type, $shareKey) : 0;
    $shareText  = trim($text ?: $title . ' | ' . config('brand.name', 'আইডিয়া প্রকাশন'));
    $shareData  = [
        'type' => $type, 'key' => $shareKey, 'url' => $url, 'title' => $title, 'text' => $shareText,
        'image' => $image, 'subtitle' => $subtitle, 'heading' => $heading,
        'endpoint' => \Illuminate\Support\Facades\Route::has('share.track') ? route('share.track', [$type, $shareKey]) : '',
    ];
@endphp

@once
    <link rel="stylesheet" href="{{ asset('css/share.css') }}?v={{ @filemtime(public_path('css/share.css')) }}">
    <script src="{{ asset('js/share.js') }}?v={{ @filemtime(public_path('js/share.js')) }}" defer></script>
@endonce

@if ($variant === 'bar')
    <div {{ $attributes->merge(['class' => 'sh-bar']) }} data-share='@json($shareData)'>
        <div class="sh-bar__text">
            <strong>{{ $heading }}</strong>
            <span class="sh-bar__count" data-share-count @if($shareCount < 1) hidden @endif>{{ \App\Support\Bn::num($shareCount) }} বার শেয়ার হয়েছে</span>
        </div>
        <div class="sh-bar__actions">
            <button type="button" class="sh-pill sh-pill--wa" data-share-channel="whatsapp">WhatsApp</button>
            <button type="button" class="sh-pill sh-pill--fb" data-share-channel="facebook">Facebook</button>
            <button type="button" class="sh-pill" data-share-channel="copy">লিংক কপি</button>
            <button type="button" class="sh-pill sh-pill--more" data-share-open aria-haspopup="dialog">আরও</button>
        </div>
    </div>
@else
    <button type="button" {{ $attributes->merge(['class' => 'sh-btn']) }} data-share='@json($shareData)' data-share-open aria-haspopup="dialog">
        {{ $label }}<span class="sh-count" data-share-count data-short @if($shareCount < 1) hidden @endif>{{ \App\Support\Bn::num($shareCount) }}</span>
    </button>
@endif

@once
<dialog class="sh-sheet" id="shSheet" aria-labelledby="shSheetTitle">
    <form method="dialog" class="sh-sheet__inner">
        <header class="sh-sheet__head">
            <h2 id="shSheetTitle" data-sh="heading">শেয়ার করুন</h2>
            <button type="submit" class="sh-close" aria-label="বন্ধ করুন">বন্ধ</button>
        </header>

        <div class="sh-card">
            <img data-sh="image" alt="" width="64" height="64" hidden>
            <div class="sh-card__text">
                <strong data-sh="title"></strong>
                <span data-sh="subtitle" hidden></span>
                <span class="sh-card__host" data-sh="host"></span>
            </div>
        </div>

        <label class="sh-field">
            <span>আপনার বার্তা (বদলানো যাবে)</span>
            <textarea rows="2" maxlength="280" data-sh="message"></textarea>
        </label>

        <div class="sh-grid" role="list">
            <button type="button" class="sh-chip" data-channel="native" hidden role="listitem">ডিভাইস থেকে</button>
            <button type="button" class="sh-chip" data-channel="whatsapp" role="listitem">WhatsApp</button>
            <button type="button" class="sh-chip" data-channel="facebook" role="listitem">Facebook</button>
            <button type="button" class="sh-chip" data-channel="messenger" hidden role="listitem">Messenger</button>
            <button type="button" class="sh-chip" data-channel="telegram" role="listitem">Telegram</button>
            <button type="button" class="sh-chip" data-channel="x" role="listitem">X</button>
            <button type="button" class="sh-chip" data-channel="linkedin" role="listitem">LinkedIn</button>
            <button type="button" class="sh-chip" data-channel="email" role="listitem">Email</button>
            <button type="button" class="sh-chip" data-channel="sms" role="listitem">SMS</button>
            <button type="button" class="sh-chip" data-channel="qr" role="listitem">QR কোড</button>
        </div>

        <div class="sh-qr" data-sh="qr-box" hidden>
            <div data-sh="qr" aria-label="লিংকের QR কোড"></div>
            <button type="button" class="sh-link" data-sh="qr-download">QR ছবি নামান</button>
        </div>

        <div class="sh-copy">
            <input type="text" readonly data-sh="url" aria-label="লিংক">
            <button type="button" class="sh-btn sh-btn--primary" data-channel="copy">লিংক কপি</button>
        </div>

        <p class="sh-status" role="status" aria-live="polite" data-sh="status"></p>
    </form>
</dialog>
@endonce
