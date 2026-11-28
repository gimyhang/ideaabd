@extends('layouts.admin')

@section('title', 'Inquiries')
@section('heading', 'যোগাযোগ বার্তা')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Inquiries</li>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-inbox.css') }}?v={{ @filemtime(public_path('css/admin-inbox.css')) ?: 1 }}">
@endpush

@section('actions')
    <div class="ib-actions">
        <button type="button" class="ib-btn" data-ib-refresh title="হালনাগাদ (R)"><i class="fa-solid fa-rotate-right"></i><span class="ib-hide-xs">হালনাগাদ</span></button>
        @if($isAdmin)
            <a href="{{ route('admin.communication.export') }}" class="ib-btn ib-btn--green" title="CSV ডাউনলোড"><i class="fa-solid fa-file-arrow-down"></i><span class="ib-hide-xs">CSV</span></a>
        @endif
        <a href="{{ route('admin.sms.index') }}" class="ib-btn ib-btn--primary"><i class="fa-solid fa-paper-plane"></i> বাল্ক SMS / ইমেইল</a>
    </div>
@endsection

@php
    $folders = [
        'inbox'    => ['ইনবক্স', 'fa-inbox'],
        'new'      => ['নতুন', 'fa-envelope'],
        'starred'  => ['তারকা', 'fa-star'],
        'replied'  => ['উত্তর দেওয়া', 'fa-reply'],
        'archived' => ['আর্কাইভ', 'fa-box-archive'],
        'spam'     => ['স্প্যাম', 'fa-shield-virus'],
        'trash'    => ['ট্র্যাশ', 'fa-trash-can'],
    ];
    $kpis = [
        ['new', 'new', 'fa-envelope', 'নতুন (পড়া হয়নি)', 'new', 'দেখুন →'],
        ['week', 'week', 'fa-calendar-week', 'গত ৭ দিনে', 'inbox', 'ইনবক্স →'],
        ['done', 'replied', 'fa-reply', 'উত্তর দেওয়া', 'replied', 'দেখুন →'],
        ['spam', 'spam', 'fa-shield-virus', 'স্প্যাম (আটকানো)', 'spam', 'দেখুন →'],
    ];
@endphp

@section('content')
<div class="ib" id="ib"
     data-list-url="{{ route('admin.communication.list') }}"
     data-poll-url="{{ route('admin.communication.poll') }}"
     data-base-url="{{ url('admin/communication') }}"
     data-bulk-url="{{ route('admin.communication.bulk') }}"
     data-admin="{{ $isAdmin ? '1' : '0' }}">

    <div class="ib-kpis mb-3">
        @foreach($kpis as [$cls, $key, $icon, $label, $folder, $hint])
            <button type="button" class="ib-kpi ib-kpi--{{ $cls }}" data-kpi="{{ $folder }}" aria-label="{{ $label }} — ফোল্ডার খুলুন">
                <i class="fa-solid {{ $icon }}"></i>
                <div><span>{{ $label }}</span><b data-count="{{ $key }}">{{ \App\Support\Bn::digits($counts[$key] ?? 0) }}</b></div>
                <small>{{ $hint }}</small>
            </button>
        @endforeach
    </div>

    <div class="ib-shell">
        {{-- folders --}}
        <nav class="ib-folders" aria-label="ফোল্ডার">
            <div class="ib-folders__title">ফোল্ডার</div>
            @foreach($folders as $fk => [$fl, $fi])
                @if($fk === 'archived')<div class="ib-folders__sep" role="presentation"></div>@endif
                <button type="button" class="ib-folder {{ $fk === 'inbox' ? 'is-on' : '' }}" data-folder="{{ $fk }}" aria-pressed="{{ $fk === 'inbox' ? 'true' : 'false' }}" title="{{ $fl }}">
                    <i class="fa-solid {{ $fi }}"></i><span>{{ $fl }}</span><b data-count="{{ $fk }}">{{ $counts[$fk] ? \App\Support\Bn::digits($counts[$fk]) : '' }}</b>
                </button>
            @endforeach
            <div class="ib-folders__foot">
                <button type="button" class="ib-help-btn" data-ib-help title="কিবোর্ড শর্টকাট (?)"><i class="fa-regular fa-keyboard"></i><span>শর্টকাট</span></button>
            </div>
        </nav>

        {{-- list --}}
        <section class="ib-list" aria-label="বার্তার তালিকা">
            <div class="ib-list__top">
                <label class="ib-check" title="এই পাতার সব বাছাই"><input type="checkbox" id="ibAll" aria-label="সব বাছাই"></label>
                <div class="ib-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" id="ibSearch" placeholder="নাম, নম্বর, ইমেইল, বার্তা…" aria-label="খুঁজুন" maxlength="100" autocomplete="off">
                    <span class="ib-kbd">/</span>
                    <button type="button" class="ib-search__clear" id="ibSearchX" aria-label="খোঁজা মুছুন" hidden><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
            <div class="ib-list__meta"><span id="ibListInfo">—</span><span class="ib-live" title="প্রতি ৩০ সেকেন্ডে নতুন বার্তা দেখা হয়">লাইভ</span></div>
            <div class="ib-bulk" id="ibBulk" hidden>
                <button type="button" class="ib-bulk__x" id="ibBulkX" aria-label="বাছাই বাতিল"><i class="fa-solid fa-xmark"></i></button>
                <span><b id="ibSelN">০</b>টি বাছাই</span>
                <div class="ib-bulk__btns" id="ibBulkBtns"></div>
            </div>
            <div class="ib-items" id="ibItems" role="list">
                @for($i = 0; $i < 5; $i++)<div class="ib-skel" aria-hidden="true"><i></i><div><span></span><span></span><span></span></div></div>@endfor
            </div>
            <div class="ib-pager" id="ibPager" hidden>
                <button type="button" class="ib-btn ib-btn--sm" data-page="prev"><i class="fa-solid fa-chevron-left"></i> আগের</button>
                <span id="ibPageInfo"></span>
                <button type="button" class="ib-btn ib-btn--sm" data-page="next">পরের <i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </section>

        {{-- message --}}
        <section class="ib-read" id="ibRead" aria-label="বার্তা" aria-live="polite"></section>
    </div>

    <dialog class="ib-dialog" id="ibHelp" aria-label="কিবোর্ড শর্টকাট">
        <header><span><i class="fa-regular fa-keyboard me-2"></i>কিবোর্ড শর্টকাট</span><button type="button" class="ib-btn ib-btn--sm ib-btn--icon" data-close aria-label="বন্ধ"><i class="fa-solid fa-xmark"></i></button></header>
        <ul>
            <li><span>পরের / আগের বার্তা</span><span><kbd class="ib-kbd">J</kbd><kbd class="ib-kbd">K</kbd></span></li>
            <li><span>খুঁজুন</span><span><kbd class="ib-kbd">/</kbd></span></li>
            <li><span>উত্তর লিখুন</span><span><kbd class="ib-kbd">R</kbd></span></li>
            <li><span>উত্তর পাঠান</span><span><kbd class="ib-kbd">Ctrl</kbd><kbd class="ib-kbd">Enter</kbd></span></li>
            <li><span>তারকা</span><span><kbd class="ib-kbd">S</kbd></span></li>
            <li><span>আর্কাইভ</span><span><kbd class="ib-kbd">E</kbd></span></li>
            <li><span>পড়া হয়নি করুন</span><span><kbd class="ib-kbd">U</kbd></span></li>
            <li><span>ট্র্যাশ</span><span><kbd class="ib-kbd">#</kbd></span></li>
            <li><span>বন্ধ / ফিরে যান</span><span><kbd class="ib-kbd">Esc</kbd></span></li>
        </ul>
    </dialog>
</div>

<template id="ibEmptyTpl">
    <div class="ib-placeholder">
        <div class="ib-placeholder__ic"><i class="fa-regular fa-envelope-open"></i></div>
        <b>একটি বার্তা বাছাই করুন</b>
        <small>বার্তা খুললে নিজে থেকেই "পড়া হয়েছে" হয়। দ্রুত চলতে <kbd class="ib-kbd">J</kbd> <kbd class="ib-kbd">K</kbd> চাপুন, সব শর্টকাট দেখতে <kbd class="ib-kbd">?</kbd></small>
    </div>
</template>

<template id="ibReadTpl">
    <div class="ib-read__bar">
        <button type="button" class="ib-tool d-lg-none" data-act="back" aria-label="ফিরে যান"><i class="fa-solid fa-arrow-left"></i></button>
        <div class="ib-read__nav">
            <button type="button" class="ib-tool" data-act="prev" title="আগের বার্তা (K)" aria-label="আগের বার্তা"><i class="fa-solid fa-chevron-up"></i></button>
            <button type="button" class="ib-tool" data-act="next" title="পরের বার্তা (J)" aria-label="পরের বার্তা"><i class="fa-solid fa-chevron-down"></i></button>
        </div>
        <div class="ib-read__tools">
            <button type="button" class="ib-tool" data-act="star" title="তারকা (S)"><i class="fa-regular fa-star"></i><span>তারকা</span></button>
            <button type="button" class="ib-tool" data-act="unread" title="পড়া হয়নি করুন (U)"><i class="fa-solid fa-envelope"></i><span>অপঠিত</span></button>
            <button type="button" class="ib-tool ib-tool--good" data-act="done" title="উত্তর দেওয়া হয়েছে চিহ্নিত করুন"><i class="fa-solid fa-check-double"></i><span>সম্পন্ন</span></button>
            <span class="ib-tool-sep" role="presentation"></span>
            <button type="button" class="ib-tool" data-act="archive" title="আর্কাইভ (E)"><i class="fa-solid fa-box-archive"></i><span>আর্কাইভ</span></button>
            <button type="button" class="ib-tool" data-act="spam" title="স্প্যাম"><i class="fa-solid fa-shield-virus"></i><span>স্প্যাম</span></button>
            <button type="button" class="ib-tool ib-tool--good" data-act="inbox" title="ইনবক্সে ফেরত" hidden><i class="fa-solid fa-inbox"></i><span>ইনবক্সে</span></button>
            <button type="button" class="ib-tool ib-tool--good" data-act="restore" title="ফেরত আনুন" hidden><i class="fa-solid fa-rotate-left"></i><span>ফেরত আনুন</span></button>
            <button type="button" class="ib-tool ib-tool--danger" data-act="trash" title="ট্র্যাশ (#)"><i class="fa-solid fa-trash-can"></i><span>ট্র্যাশ</span></button>
        </div>
    </div>
    <div class="ib-read__scroll">
        <header class="ib-head">
            <div class="ib-avatar" data-f="initial"></div>
            <div class="min-w-0 flex-grow-1">
                <div class="ib-head__name"><span data-f="name"></span> <span class="ib-badge ib-badge--member" data-f="member" hidden></span> <span class="ib-badge" data-f="status"></span></div>
                <div class="ib-head__meta"><i class="fa-regular fa-clock me-1"></i><span data-f="date"></span> · <span data-f="ago"></span><span data-f="ipwrap" hidden> · IP <code data-f="ip"></code></span></div>
            </div>
        </header>
        <div class="ib-spam" data-f="spamwarn" hidden><i class="fa-solid fa-triangle-exclamation fs-5"></i><div>সম্ভাব্য স্প্যাম — স্কোর <b data-f="spam"></b>/১০০। বার্তার কোনো লিংকে ক্লিক করবেন না।</div></div>
        <div class="ib-contact">
            <a class="ib-chip" data-f="tel" title="কল করুন"><i class="fa-solid fa-phone"></i><span data-f="phone"></span></a>
            <button type="button" class="ib-chip ib-chip--copy" data-act="copy" title="নম্বর কপি করুন" aria-label="নম্বর কপি করুন"><i class="fa-regular fa-copy" style="background:none;width:auto;height:auto"></i></button>
            <a class="ib-chip ib-chip--wa" data-f="wa" target="_blank" rel="noopener noreferrer" title="WhatsApp-এ খুলুন"><i class="fa-brands fa-whatsapp"></i>WhatsApp</a>
            <a class="ib-chip ib-chip--mail" data-f="mail" hidden title="ইমেইল অ্যাপে খুলুন"><i class="fa-regular fa-envelope"></i><span data-f="email"></span></a>
        </div>
        <h2 class="ib-subject" data-f="subject"></h2>
        <div class="ib-message" data-f="message"></div>

        <div class="ib-box ib-reply">
            <div class="ib-box__head">
                <span><i class="fa-solid fa-reply"></i>উত্তর দিন</span>
                <div class="ib-seg" role="radiogroup" aria-label="মাধ্যম">
                    <input type="radio" class="btn-check" name="ibCh" id="ibChSms" value="sms" checked><label for="ibChSms"><i class="fa-solid fa-comment-sms"></i> SMS</label>
                    <input type="radio" class="btn-check" name="ibCh" id="ibChMail" value="email"><label for="ibChMail"><i class="fa-regular fa-envelope"></i> ইমেইল</label>
                </div>
            </div>
            <div class="ib-reply__in">
                <div class="ib-quick" data-f="quick"></div>
                <input type="text" class="form-control form-control-sm mb-2" data-f="rsubject" maxlength="150" placeholder="ইমেইলের বিষয়" aria-label="ইমেইলের বিষয়" hidden>
                <textarea class="form-control" data-f="rbody" rows="4" maxlength="5000" placeholder="উত্তর লিখুন… (পাঠাতে Ctrl + Enter)" aria-label="উত্তর"></textarea>
            </div>
            <div class="ib-reply__foot">
                <div>
                    <button type="button" class="ib-btn ib-btn--sm" data-act="logcall" title="ফোনে কথা হয়েছে — ইতিহাসে লিখে রাখুন"><i class="fa-solid fa-phone"></i> কল করেছি</button>
                    <button type="button" class="ib-btn ib-btn--sm ib-btn--green" data-act="logwa" title="WhatsApp-এ উত্তর দিয়েছি — ইতিহাসে লিখে রাখুন"><i class="fa-brands fa-whatsapp"></i> WhatsApp-এ দিয়েছি</button>
                </div>
                <div>
                    <span class="ib-meter" data-f="meter"><span class="ib-meter__bar"><i data-f="meterbar"></i></span><span data-f="rcount"></span></span>
                    <button type="button" class="ib-btn ib-btn--primary" data-act="send"><i class="fa-solid fa-paper-plane"></i><span data-f="sendlbl">পাঠান</span></button>
                </div>
            </div>
        </div>

        <div class="ib-box">
            <div class="ib-box__head"><span><i class="fa-solid fa-clock-rotate-left"></i>কাজের ইতিহাস</span><small class="text-muted fw-normal" data-f="tlcount"></small></div>
            <ol class="ib-timeline" data-f="timeline"></ol>
        </div>

        <div class="ib-box">
            <div class="ib-box__head"><span><i class="fa-solid fa-note-sticky"></i>ভেতরের নোট <small class="text-muted fw-normal">(গ্রাহক দেখেন না)</small></span><small class="ib-saved" data-f="notestate"></small></div>
            <textarea class="form-control ib-note" data-f="note" rows="2" maxlength="2000" placeholder="যেমন: অর্ডার #… দেখা হচ্ছে" aria-label="ভেতরের নোট"></textarea>
        </div>

        <div class="ib-box" data-f="otherswrap" hidden>
            <div class="ib-box__head"><span><i class="fa-solid fa-layer-group"></i>এই নম্বরের অন্য বার্তা</span></div>
            <ul class="ib-others" data-f="others"></ul>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script src="{{ asset('js/admin-inbox.js') }}?v={{ @filemtime(public_path('js/admin-inbox.js')) ?: 1 }}" defer></script>
@endpush
