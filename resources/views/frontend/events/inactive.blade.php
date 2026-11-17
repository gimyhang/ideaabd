@extends('layouts.app')

@php
    $reason = $reason ?? $campaign->closedReason() ?? 'inactive';
    $isScholarship = $campaign->isScholarship();
    // Scholarship pages are English only; other campaigns keep their Bengali wording.
    $t = fn (string $en, string $bn) => $isScholarship ? $en : $bn;

    $headline = match ($reason) {
        'expired' => $t('Applications are closed: the deadline has passed', 'আবেদনের সময়সীমা শেষ হয়েছে'),
        'full'    => $t('Applications are closed: the limit has been reached', 'আবেদনের সর্বোচ্চ সংখ্যা পূর্ণ হয়েছে'),
        default   => $t('Applications are closed', 'আবেদন এখন বন্ধ আছে'),
    };
    $detail = match ($reason) {
        'expired' => $campaign->ends_at
            ? $t('The deadline was ' . $campaign->ends_at->format('d M Y') . '.', 'শেষ তারিখ ছিল ' . $campaign->ends_at->format('d M Y') . '।')
            : $t('The application deadline has passed.', 'আবেদনের শেষ তারিখ পার হয়ে গেছে।'),
        'full'    => $t('All places have been filled, so new applications are not being accepted.', 'নির্ধারিত সংখ্যক আবেদন জমা হয়ে গেছে, তাই নতুন আবেদন নেওয়া হচ্ছে না।'),
        default   => $t('New applications are not being accepted right now.', 'এই কার্যক্রমে বর্তমানে নতুন আবেদন নেওয়া হচ্ছে না।'),
    };
    $nextNote = $t('Please check this page again for the next announcement.', 'নতুন ঘোষণার জন্য এই পেজটি আবার দেখুন বা ওয়েবসাইটের সাথে যুক্ত থাকুন।');
    $shareDesc = trim((string) $campaign->short_description) ?: 'Idea Prokashon scholarship and registration program.';

    // Words the "already applied" box shows, handed to its script as-is.
    $lookupText = [
        'needBoth'   => 'Please enter both your name and mobile number.',
        'searching'  => 'Searching...',
        'button'     => 'Find my application',
        'tooMany'    => 'Too many attempts. Please try again in a minute.',
        'notFound'   => 'No application was found for this name and mobile number. Please check both and try again.',
        'failed'     => 'We could not search right now. Please check your internet and try again.',
        'appNo'      => 'Application No',
        'status'     => 'Status',
        'pdf'        => 'Download PDF',
        'print'      => 'Print',
    ];
@endphp

@section('title', $campaign->title . ' — Registration Closed')
{{-- Sharing a closed campaign still previews the campaign's own banner, never the site's main banner. --}}
@section('og_title', $campaign->title . ' — Registration Closed | Idea Prokashon')
@section('og_description', Str::limit(strip_tags($shareDesc), 180))
@section('og_image', $campaign->shareImageUrl())
@section('og_url', $campaign->shareUrl())

@section('content')
<div class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white" style="border: 1px solid #e2e8f0;">
                @if($campaign->banner_image)
                    <img src="{{ asset('storage/' . $campaign->banner_image) }}" alt="{{ $campaign->title }}" class="w-100 d-block" style="max-height: 220px; object-fit: cover;" loading="lazy">
                @endif

                <div class="p-4 p-md-5 text-center">
                    <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis fw-semibold px-3 py-2 mb-3">{{ $headline }}</span>

                    <h1 class="h4 fw-bold text-dark mb-2">{{ $campaign->title }}</h1>
                    <p class="text-secondary mb-1">{{ $detail }}</p>
                    <p class="text-secondary small mb-0">{{ $nextNote }}</p>
                </div>

                @if($isScholarship)
                    <div class="border-top bg-light p-4">
                        <h2 class="h6 fw-bold text-dark mb-1">Already applied?</h2>
                        <p class="small text-secondary mb-3">Enter your name and mobile number to view or download your application form.</p>

                        <form id="closedLookupForm" action="{{ route('event.lookup', $campaign->slug) }}" method="POST" novalidate>
                            @csrf
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label for="closedLookName" class="form-label small fw-semibold mb-1">Name on application</label>
                                    <input id="closedLookName" name="name" type="text" class="form-control" autocomplete="name" maxlength="120" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="closedLookPhone" class="form-label small fw-semibold mb-1">Mobile number</label>
                                    <input id="closedLookPhone" name="phone" type="tel" class="form-control" inputmode="numeric" autocomplete="tel" maxlength="20" placeholder="01XXXXXXXXX" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 mt-3 fw-semibold" id="closedLookBtn">{{ $lookupText['button'] }}</button>
                        </form>

                        <div id="closedLookResult" class="mt-3" role="status" aria-live="polite" hidden></div>
                    </div>
                @endif

                @if($campaign->contact_phone || $campaign->contact_email)
                    <div class="border-top p-3 text-center small text-secondary">
                        {{ $t('Need help?', 'সাহায্য দরকার?') }}
                        @if($campaign->contact_phone)
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $campaign->contact_phone) }}" class="fw-semibold">{{ $campaign->contact_phone }}</a>
                        @endif
                        @if($campaign->contact_phone && $campaign->contact_email) · @endif
                        @if($campaign->contact_email)
                            <a href="mailto:{{ $campaign->contact_email }}" class="fw-semibold">{{ $campaign->contact_email }}</a>
                        @endif
                    </div>
                @endif

                <div class="border-top p-3 text-center">
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold">{{ $t('Go to homepage', 'হোমপেজে যান') }}</a>
                </div>
            </div>

        </div>
    </div>
</div>

@if($isScholarship)
<script>
(function () {
    const T = @json($lookupText);
    const form = document.getElementById('closedLookupForm');
    if (!form) return;
    const box = document.getElementById('closedLookResult');
    const btn = document.getElementById('closedLookBtn');

    function show(node) { box.replaceChildren(node); box.hidden = false; }
    function note(text, cls) {
        const p = document.createElement('div');
        p.className = 'alert ' + cls + ' mb-0 py-2 small';
        p.textContent = text;   // textContent: server text is never parsed as HTML
        return p;
    }
    function link(label, href, outline) {
        const a = document.createElement('a');
        a.className = 'btn btn-sm rounded-pill px-3 fw-semibold ' + (outline ? 'btn-outline-primary' : 'btn-primary');
        a.textContent = label;
        a.href = href;
        a.target = '_blank';
        a.rel = 'noopener';
        return a;
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!form.name.value.trim() || !form.phone.value.trim()) {
            show(note(T.needBoth, 'alert-warning'));
            return;
        }
        btn.disabled = true;
        btn.textContent = T.searching;
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
                credentials: 'same-origin',
            });
            if (res.status === 429) { show(note(T.tooMany, 'alert-warning')); return; }
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            if (!data.found) { show(note(T.notFound, 'alert-warning')); return; }

            const wrap = document.createElement('div');
            wrap.className = 'p-3 rounded-3 bg-white border';
            const title = document.createElement('div');
            title.className = 'fw-semibold text-dark';
            title.textContent = data.name;
            const meta = document.createElement('div');
            meta.className = 'small text-secondary mb-2';
            meta.textContent = T.appNo + ' ' + data.application_no + ' · ' + T.status + ': ' + data.status;
            const actions = document.createElement('div');
            actions.className = 'd-flex flex-wrap gap-2';
            actions.append(link(T.pdf, data.pdf_url, false), link(T.print, data.print_url, true));
            wrap.append(title, meta, actions);
            show(wrap);
        } catch (err) {
            show(note(T.failed, 'alert-danger'));
        } finally {
            btn.disabled = false;
            btn.textContent = T.button;
        }
    });
})();
</script>
@endif
@endsection
