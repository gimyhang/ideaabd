{{--
    Action row under the cover: look inside, share (full share sheet) and wishlist (account or browser).
    Needs: $book, $coverUrl, $authorNames, $finalPrice. Optional: $shareCount, $inWishlist.
    Behaviour lives in public/js/book-actions.js, look in public/css/book-actions.css.
--}}
@php
    $shareUrl   = route('book.show', $book->slug ?: $book->id);
    $shareCount = (int) ($shareCount ?? 0);
    $inWishlist = (bool) ($inWishlist ?? false);
    $priceLine  = ($finalPrice ?? 0) > 0 ? ' — মূল্য ৳' . round($finalPrice) : '';
    $shareText  = $book->title . ($authorNames ? ' — ' . $authorNames : '') . $priceLine . ' | আইডিয়া প্রকাশন';
@endphp

<div class="ba-actions"
     data-book-id="{{ $book->id }}"
     data-title="{{ $book->title }}"
     data-url="{{ $shareUrl }}"
     data-text="{{ $shareText }}"
     data-cover="{{ $coverUrl }}"
     data-share-endpoint="{{ route('book.share', $book->id) }}"
     data-wishlist-endpoint="{{ route('book.wishlist.toggle', $book->id) }}"
     data-wishlist-sync="{{ route('book.wishlist.sync') }}"
     data-fb-app="{{ config('services.facebook.app_id') }}"
     data-auth="{{ auth()->check() ? '1' : '0' }}"
     data-saved="{{ $inWishlist ? '1' : '0' }}">

    <button type="button" class="ba-btn ba-btn-primary" data-bs-toggle="modal" data-bs-target="#samplePreviewModal">একটু পড়ে দেখুন</button>

    <button type="button" class="ba-btn" data-ba="share" aria-haspopup="dialog">
        শেয়ার<span class="ba-count" data-ba="share-count" @if($shareCount < 1) hidden @endif>{{ $shareCount }}</span>
    </button>

    <button type="button" class="ba-btn" data-ba="wishlist" aria-pressed="{{ $inWishlist ? 'true' : 'false' }}">
        <span data-ba="wishlist-label">{{ $inWishlist ? 'সংরক্ষিত' : 'উইশলিস্ট' }}</span>
    </button>
</div>

<dialog class="ba-sheet" id="baShareSheet" aria-labelledby="baShareTitle">
    <form method="dialog" class="ba-sheet-inner">
        <header class="ba-sheet-head">
            <h2 id="baShareTitle">বইটি শেয়ার করুন</h2>
            <button type="submit" class="ba-close" aria-label="বন্ধ করুন">বন্ধ</button>
        </header>

        <div class="ba-card">
            <img src="{{ $coverUrl }}" alt="" loading="lazy" width="54" height="72">
            <div class="ba-card-text">
                <strong>{{ $book->title }}</strong>
                @if($authorNames)<span>{{ $authorNames }}</span>@endif
                @if(($finalPrice ?? 0) > 0)<span class="ba-card-price">৳ @bn(round($finalPrice))</span>@endif
            </div>
        </div>

        <label class="ba-field">
            <span>আপনার বার্তা (বদলানো যাবে)</span>
            <textarea rows="2" maxlength="240" data-ba="message">{{ $shareText }}</textarea>
        </label>

        <div class="ba-grid" role="list">
            <button type="button" class="ba-chip" data-channel="native" hidden role="listitem">ডিভাইস থেকে</button>
            <button type="button" class="ba-chip" data-channel="whatsapp" role="listitem">WhatsApp</button>
            <button type="button" class="ba-chip" data-channel="facebook" role="listitem">Facebook</button>
            <button type="button" class="ba-chip" data-channel="messenger" hidden role="listitem">Messenger</button>
            <button type="button" class="ba-chip" data-channel="telegram" role="listitem">Telegram</button>
            <button type="button" class="ba-chip" data-channel="x" role="listitem">X</button>
            <button type="button" class="ba-chip" data-channel="linkedin" role="listitem">LinkedIn</button>
            <button type="button" class="ba-chip" data-channel="email" role="listitem">Email</button>
            <button type="button" class="ba-chip" data-channel="sms" role="listitem">SMS</button>
            <button type="button" class="ba-chip" data-channel="qr" role="listitem">QR কোড</button>
        </div>

        <div class="ba-qr" data-ba="qr-box" hidden>
            <div data-ba="qr" aria-label="বইয়ের লিংকের QR কোড"></div>
            <button type="button" class="ba-link" data-ba="qr-download">QR ছবি নামান</button>
        </div>

        <div class="ba-copy">
            <input type="text" readonly value="{{ $shareUrl }}" data-ba="url" aria-label="বইয়ের লিংক">
            <button type="button" class="ba-btn ba-btn-primary" data-channel="copy">লিংক কপি</button>
        </div>

        <p class="ba-status" role="status" aria-live="polite" data-ba="status"></p>
    </form>
</dialog>
