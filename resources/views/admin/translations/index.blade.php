@extends('layouts.admin')

@section('title', 'Translations')
@section('heading', 'Translations')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Translations</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-translations.css') }}?v={{ @filemtime(public_path('css/admin-translations.css')) }}">
@endpush

@section('content')
@php
    $ready = $stats['total'] ? (int) floor(($stats['total'] - $stats['missing']) * 100 / $stats['total']) : 100;
    $langs = ['bn' => 'Bangla', 'en' => 'English', 'ar' => 'Arabic'];
@endphp
<div class="tr" id="trApp"
     data-index="{{ route('admin.translations.index') }}"
     data-store="{{ route('admin.translations.store') }}"
     data-update="{{ route('admin.translations.update', ['translation' => '__ID__']) }}"
     data-sync="{{ route('admin.translations.sync') }}"
     data-fill="{{ route('admin.translations.fill') }}"
     data-suggest="{{ route('admin.translations.auto-translate') }}"
     data-can-edit="{{ $canEdit ? 1 : 0 }}">

    {{-- Overview --}}
    <section class="tr-hero">
        <div class="tr-ring" style="--p: {{ $ready }}" data-ring>
            <svg viewBox="0 0 120 120" aria-hidden="true">
                <circle class="tr-ring-track" cx="60" cy="60" r="52"/>
                <circle class="tr-ring-value" cx="60" cy="60" r="52" pathLength="100" stroke-dasharray="{{ $ready }} 100"/>
            </svg>
            <div class="tr-ring-text"><strong data-stat="ready">{{ $ready }}</strong><small>% ready</small></div>
        </div>
        <div class="tr-hero-main">
            <h2>Site language text</h2>
            <p>Text typed here replaces the language files on the site. Leave a box empty to keep the file text (shown in grey).</p>
            <div class="tr-kpis">
                <div class="tr-kpi"><i class="fa-solid fa-key"></i><strong data-stat="total">{{ number_format($stats['total']) }}</strong><span>Keys</span></div>
                <div class="tr-kpi"><i class="fa-solid fa-layer-group"></i><strong data-stat="groups">{{ $stats['groups'] }}</strong><span>Groups</span></div>
                <div class="tr-kpi is-blue"><i class="fa-solid fa-pen-nib"></i><strong data-stat="custom">{{ number_format($stats['custom']) }}</strong><span>Custom</span></div>
                <div class="tr-kpi is-amber"><i class="fa-solid fa-circle-exclamation"></i><strong data-stat="missing">{{ number_format($stats['missing']) }}</strong><span>Missing</span></div>
            </div>
        </div>
        <div class="tr-langs">
            @foreach($langs as $loc => $name)
                <div class="tr-lang-bar" data-lang-bar="{{ $loc }}">
                    <div class="tr-lang-bar-top">
                        <span><b>{{ strtoupper($loc) }}</b> {{ $name }}{{ $loc === 'ar' ? ' · optional' : '' }}</span>
                        <span><strong data-pct>{{ $stats['langs'][$loc]['percent'] }}</strong>% · <span data-count>{{ $stats['langs'][$loc]['count'] }}</span></span>
                    </div>
                    <div class="tr-meter"><i style="width: {{ $stats['langs'][$loc]['percent'] }}%"></i></div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Toolbar --}}
    <div class="tr-toolbar">
        <label class="tr-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" id="trSearch" value="{{ $search }}" placeholder="Search key or text…" autocomplete="off">
            <kbd>/</kbd>
        </label>
        <div class="tr-seg" role="tablist" id="trStatus">
            @foreach(['all' => 'All', 'missing' => 'Missing', 'custom' => 'Custom', 'done' => 'Done'] as $val => $label)
                <button type="button" role="tab" data-status="{{ $val }}" class="{{ $status === $val ? 'is-on' : '' }}" aria-selected="{{ $status === $val ? 'true' : 'false' }}">{{ $label }}</button>
            @endforeach
        </div>
        <div class="tr-actions">
            @if($canEdit)
                <button type="button" class="tr-btn" data-action="sync" title="List every key from the language files"><i class="fa-solid fa-arrows-rotate"></i><span>Sync</span></button>
                <button type="button" class="tr-btn" data-action="fill" title="Fill empty English from saved text and the glossary"><i class="fa-solid fa-wand-magic-sparkles"></i><span>Fill</span></button>
                <a class="tr-btn" href="{{ route('admin.translations.export') }}" title="Download CSV"><i class="fa-solid fa-file-arrow-down"></i><span>Export</span></a>
                <button type="button" class="tr-btn is-primary" data-bs-toggle="modal" data-bs-target="#trAddModal"><i class="fa-solid fa-plus"></i><span>Add</span></button>
            @endif
        </div>
    </div>

    {{-- Groups --}}
    <div class="tr-groups" id="trGroups">
        <button type="button" data-group="" class="tr-chip {{ $group === '' ? 'is-on' : '' }}">
            <span>All</span><b>{{ $stats['total'] }}</b>
        </button>
        @foreach($groups as $g)
            <button type="button" data-group="{{ $g['name'] }}" class="tr-chip {{ $group === $g['name'] ? 'is-on' : '' }}" style="--p: {{ $g['percent'] }}%">
                <span>{{ $g['name'] }}</span><b>{{ $g['total'] }}</b><i class="tr-chip-meter"></i>
            </button>
        @endforeach
    </div>

    <div class="tr-results" id="trResults" aria-live="polite">
        @include('admin.translations._rows')
    </div>

    <p class="tr-note"><i class="fa-solid fa-circle-info"></i>
        Use a key in a page as <code>@{{ __('group.key') }}</code>. Words like <code>:attribute</code> are filled in by the site — keep them in every language.
        Visitors who pick a language other than Bangla or English get Google Translate.</p>
</div>

@if($canEdit)
{{-- Add key --}}
<div class="modal fade" id="trAddModal" tabindex="-1" aria-labelledby="trAddTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content tr-modal" id="trAddForm" novalidate>
            <div class="tr-modal-head">
                <span class="tr-modal-icon"><i class="fa-solid fa-plus"></i></span>
                <div><h5 id="trAddTitle">Add key</h5><p>A new text you can use in pages.</p></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="tr-modal-body">
                <div class="tr-field-row">
                    <label class="tr-field">
                        <span>Group</span>
                        <input name="group" list="trGroupList" required maxlength="50" placeholder="site" value="{{ $group ?: 'site' }}" autocomplete="off">
                        <datalist id="trGroupList">
                            @foreach($groups->pluck('name')->merge($fileGroups)->unique()->sort() as $g)<option value="{{ $g }}">@endforeach
                        </datalist>
                    </label>
                    <label class="tr-field">
                        <span>Key</span>
                        <input name="key" required maxlength="100" placeholder="buy_now" class="font-monospace" autocomplete="off">
                    </label>
                </div>
                <div class="tr-code">Code: <code id="trAddCode">__('site.…')</code></div>
                <label class="tr-field"><span>Bangla</span><textarea name="text_bn" rows="2" maxlength="2000" placeholder="এখনই কিনুন"></textarea></label>
                <label class="tr-field">
                    <span>English <button type="button" class="tr-suggest is-text" data-add-suggest><i class="fa-solid fa-wand-magic-sparkles"></i> Suggest</button></span>
                    <textarea name="text_en" rows="2" maxlength="2000" placeholder="Buy Now"></textarea>
                </label>
                <label class="tr-field"><span>Arabic <em>optional</em></span><textarea name="text_ar" rows="2" maxlength="2000" dir="rtl" placeholder="اشتري الآن"></textarea></label>
                <div class="tr-form-error" id="trAddError" hidden></div>
            </div>
            <div class="tr-modal-foot">
                <button type="button" class="tr-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="tr-btn is-primary"><i class="fa-solid fa-check"></i><span>Save</span></button>
            </div>
        </form>
    </div>
</div>
@endif

<div class="tr-toast" id="trToast" role="status" hidden></div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin-translations.js') }}?v={{ @filemtime(public_path('js/admin-translations.js')) }}" defer></script>
@endpush
