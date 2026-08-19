{{--
    Generic create/edit form for every admin-managed content type.

    Driven by App\Support\ContentTypes with modern bookshop enhancements:
    - Quick AJAX Category, Publisher, Author creators without page reload
    - Real-time Discount & Price Calculator (savings in Taka & %)
    - Real-time Live Book Card Mockup in sidebar
    - Live file previews for Covers & PDFs

    @param array                                     $spec       content type definition
    @param \Illuminate\Database\Eloquent\Model|null  $record     null when creating
    @param array<string, array<int, string>>         $lookups    select options keyed by table
    @param array<int, string>                        $creditees  users an entry can be credited to
--}}
@extends('layouts.admin')

@php
    $editing = $record !== null;
    $heading = $editing ? "{$spec['label']} সম্পাদনা" : "নতুন {$spec['label']}";
    $action  = $editing
        ? route('admin.content.update', ['type' => $spec['key'], 'id' => $record->getKey()])
        : route('admin.content.store', ['type' => $spec['key']]);

    /** Current value for a field: old input first, then the record, then blank. */
    $val = fn (string $name, $fallback = null) => old($name, $editing ? ($record->{$name} ?? $fallback) : $fallback);

    $isBookOrEbook = in_array($spec['key'], ['books', 'ebooks'], true);
@endphp

@section('title', $heading)
@section('heading', $heading)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route($spec['listRoute']) }}" class="text-decoration-none">{{ $spec['label'] }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $editing ? 'সম্পাদনা' : 'নতুন' }}</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        @if ($editing)
            @if ($spec['key'] === 'webzines')
                <a href="{{ route('webzine.read', $record->slug ?: $record->id) }}" target="_blank" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-xs">
                    <i class="fas fa-book-open me-1"></i> সরাসরি পড়ুন (Reader)
                </a>
                <a href="{{ route('webzine.show', $record->slug ?: $record->id) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs">
                    <i class="fas fa-arrow-up-right-from-square me-1"></i> সাইটে দেখুন
                </a>
            @elseif ($spec['key'] === 'ebooks')
                <a href="{{ route('ebook.read', $record->slug ?: $record->id) }}" target="_blank" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-xs">
                    <i class="fas fa-book-open me-1"></i> সরাসরি পড়ুন (Reader)
                </a>
                <a href="{{ route('ebook.show', $record->slug ?: $record->id) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs">
                    <i class="fas fa-arrow-up-right-from-square me-1"></i> সাইটে দেখুন
                </a>
            @elseif ($spec['key'] === 'books')
                <a href="{{ route('book.show', $record->slug ?: $record->id) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs">
                    <i class="fas fa-arrow-up-right-from-square me-1"></i> সাইটে দেখুন
                </a>
            @elseif ($spec['key'] === 'blog')
                <a href="{{ route('blog.show', $record->slug ?: $record->id) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs">
                    <i class="fas fa-arrow-up-right-from-square me-1"></i> সাইটে দেখুন
                </a>
            @endif
        @endif
        <a href="{{ route($spec['listRoute']) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> তালিকায় ফিরুন
        </a>
    </div>
@endsection

@section('content')

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="row g-4" id="contentMainForm">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="col-12 col-lg-8">
        <div class="adm-card p-3 p-md-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h2 class="h6 fw-bold mb-0 text-dark">
                    <i class="fas fa-{{ $spec['icon'] }} me-1.5 text-primary"></i> {{ $spec['label'] }} সংক্রান্ত বিবরণ ও তথ্য
                </h2>
                <span class="badge bg-light text-muted border small">প্রয়োজনীয় ফিল্ডগুলো পূরণ করুন</span>
            </div>

            <div class="row g-3">
                @foreach ($spec['fields'] as $name => $field)
                    @php $current = $val($name); @endphp

                    @if ($spec['key'] === 'books')
                        @if (in_array($name, ['hardcover_price', 'hardcover_discount_price', 'price', 'discount_price', 'cost_price'], true))
                            @continue
                        @endif

                        @if ($name === 'title')
                            <div class="col-12 mt-1 mb-1">
                                <div class="d-flex align-items-center gap-2 pb-1.5 border-bottom text-dark fw-bold" style="font-size: 0.95rem;">
                                    <span class="p-1.5 bg-primary-subtle text-primary rounded-circle small"><i class="fas fa-book-bookmark"></i></span> ১. বইয়ের মূল তথ্য ও অবদানকারী (লেখক, অনুবাদক ও সম্পাদক)
                                </div>
                            </div>
                        @elseif ($name === 'cover_type')
                            <div class="col-12 mt-3 mb-1">
                                <div class="d-flex align-items-center gap-2 pb-1.5 border-bottom text-dark fw-bold" style="font-size: 0.95rem;">
                                    <span class="p-1.5 bg-success-subtle text-success rounded-circle small"><i class="fas fa-calculator"></i></span> ২. বাঁধাই, সংস্করণ, মূল্য নির্ধারণ ও ক্রয়-বিক্রয় কমিশন হিসাব
                                </div>
                            </div>
                        @elseif ($name === 'published_at')
                            <div class="col-12 mt-3 mb-1">
                                <div class="d-flex align-items-center gap-2 pb-1.5 border-bottom text-dark fw-bold" style="font-size: 0.95rem;">
                                    <span class="p-1.5 bg-warning-subtle text-warning rounded-circle small"><i class="fas fa-calendar-check"></i></span> ৩. প্রকাশনা, রিলিজ ও ইনভেন্টরি
                                </div>
                            </div>
                        @elseif ($name === 'book_size')
                            <div class="col-12 mt-3 mb-1">
                                <div class="d-flex align-items-center gap-2 pb-1.5 border-bottom text-dark fw-bold" style="font-size: 0.95rem;">
                                    <span class="p-1.5 bg-secondary-subtle text-secondary rounded-circle small"><i class="fas fa-ruler-combined"></i></span> ৪. বইয়ের শারীরিক বিবরণ ও পরিমাপ
                                </div>
                            </div>
                        @elseif ($name === 'cover_image')
                            <div class="col-12 mt-3 mb-1">
                                <div class="d-flex align-items-center gap-2 pb-1.5 border-bottom text-dark fw-bold" style="font-size: 0.95rem;">
                                    <span class="p-1.5 bg-info-subtle text-info rounded-circle small"><i class="fas fa-images"></i></span> ৫. কভার, লেখকের ছবি ও নমুনা ফাইল (স্ট্যান্ডার্ড সাইজ)
                                </div>
                            </div>
                        @elseif ($name === 'summary')
                            <div class="col-12 mt-3 mb-1">
                                <div class="d-flex align-items-center gap-2 pb-1.5 border-bottom text-dark fw-bold" style="font-size: 0.95rem;">
                                    <span class="p-1.5 bg-purple-subtle text-purple rounded-circle small" style="background-color: #f3e8ff; color: #7e22ce;"><i class="fas fa-align-left"></i></span> ৬. সারসংক্ষেপ, বিস্তারিত ফ্ল্যাপ ও লেখক পরিচিতি
                                </div>
                            </div>
                        @endif
                    @endif

                    <div class="col-md-{{ ($name === 'cover_type' && $spec['key'] === 'books') ? 12 : ($field['col'] ?? 12) }}">

                        {{-- ══ CHECKBOX ══════════════════════════════════════════ --}}
                        @if ($field['type'] === 'checkbox')
                            <div class="form-check mt-md-4">
                                <input type="hidden" name="{{ $name }}" value="0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="f-{{ $name }}" name="{{ $name }}" value="1"
                                       @checked(old($name, $editing ? (bool) $record->{$name} : true))>
                                <label class="form-check-label fw-semibold" for="f-{{ $name }}">{{ $field['label'] }}</label>
                            </div>

                        {{-- ══ AUTHOR ROLE GROUP (লেখক / অনুবাদক / সম্পাদক) ══ --}}
                        @elseif ($field['type'] === 'author_role_group')
                            @php
                                $curRole       = old('author_role',  $editing ? ($record->author_role  ?? 'author') : 'author');
                                $curAuthorId   = old('author_link_id', $editing ? ($record->author_link_id ?? '') : '');
                                $curAuthorName = old('author_name',  $editing ? ($record->author_name  ?? '') : '');
                                $authorOptions = $lookups['authors'] ?? [];
                                $hasMatchingAuthor = $curAuthorId || ($curAuthorName && in_array($curAuthorName, $authorOptions, true));
                                $curMode       = old('author_input_mode', ($hasMatchingAuthor || !empty($authorOptions) ? 'directory' : 'custom'));
                            @endphp

                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                                    <label class="form-label small fw-bold text-dark mb-0">
                                        <i class="fas fa-pen-nib text-primary me-1"></i> প্রধান লেখক নির্বাচন (Author Selection) <span class="text-danger">*</span>
                                    </label>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill fw-semibold" 
                                            data-bs-toggle="modal" data-bs-target="#quickAddAuthorModal" style="font-size: 11.5px;">
                                        <i class="fas fa-plus-circle me-1"></i>+ নতুন লেখক তৈরি করুন
                                    </button>
                                </div>

                                {{-- ইনপুট মোড স্যুইচ --}}
                                <div class="btn-group btn-group-sm mb-2 w-100" role="group" id="author-mode-tabs">
                                    <input type="radio" class="btn-check" name="author_input_mode"
                                           id="author-mode-directory" value="directory"
                                           @checked($curMode === 'directory') onchange="toggleAuthorMode('directory')">
                                    <label class="btn btn-outline-primary fw-semibold" for="author-mode-directory">
                                        <i class="fas fa-address-book me-1"></i> ডিরেক্টরি থেকে বাছাই (ড্রপডাউন)
                                    </label>

                                    <input type="radio" class="btn-check" name="author_input_mode"
                                           id="author-mode-custom" value="custom"
                                           @checked($curMode === 'custom') onchange="toggleAuthorMode('custom')">
                                    <label class="btn btn-outline-secondary fw-semibold" for="author-mode-custom">
                                        <i class="fas fa-keyboard me-1"></i> নতুন লেখকের নাম লিখুন
                                    </label>
                                </div>

                                {{-- Directory Mode: লেখক ড্রপডাউন --}}
                                <div id="author-directory-panel" style="{{ $curMode === 'directory' ? '' : 'display:none' }}">
                                    <select name="author_link_id" id="f-author_link_id"
                                            class="form-select @error('author_link_id') is-invalid @enderror"
                                            onchange="onAuthorDirectoryChange(this)">
                                        <option value="">— লেখক নির্বাচন করুন (মোট: {{ count($authorOptions) }} জন) —</option>
                                        @foreach ($authorOptions as $aId => $aName)
                                            <option value="{{ $aId }}" @selected((string)$curAuthorId === (string)$aId || (!$curAuthorId && $curAuthorName === $aName))>
                                                {{ $aName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text mt-1 text-muted" style="font-size: 11.5px;">
                                        <i class="fas fa-info-circle text-primary me-1"></i>লেখকের প্রোফাইল ও বইয়ের তালিকার সাথে সরাসরি সংযুক্ত থাকবে। তালিকায় লেখক না থাকলে উপরে <strong>“নতুন লেখক তৈরি করুন”</strong> বাটনে চাপুন।
                                    </div>
                                    @error('author_link_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                {{-- Custom Mode: ফ্রি-টেক্সট --}}
                                <div id="author-custom-panel" style="{{ $curMode === 'custom' ? '' : 'display:none' }}">
                                    <input type="text" name="author_name" id="f-author_name"
                                           value="{{ $curAuthorName }}"
                                           placeholder="লেখকের পুরো নাম লিখুন (যেমন: হুমায়ূন আহমেদ / আনিসুল হক)"
                                           class="form-control @error('author_name') is-invalid @enderror"
                                           oninput="updateLiveMockupCard()">
                                    <div class="form-text mt-1 text-muted" style="font-size: 11.5px;">
                                        <i class="fas fa-circle-check text-success me-1"></i>নামটি সেভ করার সাথে সাথে লেখক ডিরেক্টরিতে স্বয়ংক্রিয়ভাবে প্রোফাইল তৈরি হয়ে যাবে।
                                    </div>
                                    @error('author_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <input type="hidden" name="author_role" value="author">
                                @error('author_role')<div class="invalid-feedback d-block mt-1">{{ $message }}</div>@enderror
                            </div>

                        {{-- ══ CATEGORY SELECT WITH DYNAMIC QUICK CREATION ═══════════════ --}}
                        @elseif ($name === 'category_id')
                            @php
                                $isBlog = ($spec['key'] === 'blog');
                                $catLookupKey = $field['lookup'] ?? ($isBlog ? 'blog_categories' : 'categories');
                                $catOptions = $lookups[$catLookupKey] ?? ($lookups['categories'] ?? []);
                                $targetModalId = $isBlog ? 'quickAddBlogCategoryModal' : 'quickAddCategoryModal';
                            @endphp
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label for="f-category_id" class="form-label small fw-semibold mb-0">
                                    <i class="fas fa-shapes text-primary me-1"></i> {{ $field['label'] }}
                                </label>
                                <button type="button" class="btn btn-link text-primary p-0 text-decoration-none small fw-semibold"
                                        data-bs-toggle="modal" data-bs-target="#{{ $targetModalId }}">
                                    <i class="fas fa-plus-circle me-1"></i>+ নতুন ক্যাটাগরি তৈরি
                                </button>
                            </div>
                            
                            <select id="f-category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" onchange="updateLiveMockupCard()">
                                <option value="">— ক্যাটাগরি নির্বাচন করুন —</option>
                                @foreach ($catOptions as $catId => $catLabel)
                                    <option value="{{ $catId }}" @selected((string) $current === (string) $catId)>
                                        {{ $catLabel }}
                                    </option>
                                @endforeach
                            </select>

                            @if (!$isBlog)
                                <div class="mt-2 p-2 bg-light rounded border">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label for="f-sub_category_name" class="form-label small fw-semibold text-dark mb-0" style="font-size: 11.5px;">
                                            <i class="fas fa-folder-tree me-1 text-primary"></i>অথবা নতুন সাব-ক্যাটাগরি লিখুন:
                                        </label>
                                    </div>
                                    <input type="text" id="f-sub_category_name" name="sub_category_name" 
                                           class="form-control form-control-sm" placeholder="উদা: ঐতিহাসিক উপন্যাস / অনুবাদ সাহিত্য">
                                    <div class="form-text" style="font-size: 11px;">উপরের মেইন ক্যাটাগরি নির্বাচন করে এখানে সাব-ক্যাটাগরির নাম লিখলে স্বয়ংক্রিয়ভাবে তৈরি হবে।</div>
                                </div>
                            @else
                                <div class="mt-2 p-2 bg-light rounded border">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label for="f-new_blog_category_name" class="form-label small fw-semibold text-dark mb-0" style="font-size: 11.5px;">
                                            <i class="fas fa-feather-pointed me-1 text-primary"></i>অথবা সরাসরি নতুন ক্যাটাগরির নাম লিখুন:
                                        </label>
                                    </div>
                                    <input type="text" id="f-new_blog_category_name" name="new_blog_category_name" 
                                           class="form-control form-control-sm" placeholder="উদা: কবিতা / গল্প / প্রবন্ধ / রম্যরচনা / অনুবাদ">
                                    <div class="form-text" style="font-size: 11px;">তালিকায় না থাকলে এখানে লিখলে পোস্ট সেভ করার সময় ক্যাটাগরি স্বয়ংক্রিয়ভাবে তৈরি হয়ে যাবে।</div>
                                </div>
                            @endif
                            @error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                        {{-- ══ BLOG AUTHOR SELECT WITH QUICK CREATION ═════════════ --}}
                        @elseif ($name === 'author_id')
                            @php
                                $authorOptions = $lookups['authors'] ?? ($lookups['users'] ?? []);
                            @endphp
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label for="f-author_id" class="form-label small fw-semibold mb-0">
                                    <i class="fas fa-pen-nib text-primary me-1"></i> {{ $field['label'] }}
                                </label>
                                <button type="button" class="btn btn-link text-primary p-0 text-decoration-none small fw-semibold"
                                        data-bs-toggle="modal" data-bs-target="#quickAddAuthorModal">
                                    <i class="fas fa-plus-circle me-1"></i>+ নতুন লেখক যুক্ত করুন
                                </button>
                            </div>
                            
                            <select id="f-author_id" name="author_id" class="form-select @error('author_id') is-invalid @enderror" onchange="updateLiveMockupCard()">
                                <option value="">— লেখক নির্বাচন করুন —</option>
                                @foreach ($authorOptions as $aId => $aName)
                                    <option value="{{ $aId }}" @selected((string) $current === (string) $aId)>
                                        {{ $aName }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text" style="font-size: 11px;">তালিকায় লেখক না থাকলে পাশের বাটনে ক্লিক করে সাথে সাথে যুক্ত করতে পারেন।</div>
                            @error('author_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                        {{-- ══ PUBLISHER SELECT WITH QUICK CREATION ══════════════ --}}
                        @elseif ($name === 'publisher_id')
                            @php
                                $pubOptions = $lookups['publishers'] ?? [];
                            @endphp
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label for="f-publisher_id" class="form-label small fw-semibold mb-0">
                                    {{ $field['label'] }}
                                </label>
                                <button type="button" class="btn btn-link text-primary p-0 text-decoration-none small fw-semibold"
                                        data-bs-toggle="modal" data-bs-target="#quickAddPublisherModal">
                                    <i class="fas fa-plus-circle me-1"></i>নতুন প্রকাশনী তৈরি
                                </button>
                            </div>

                            <select id="f-publisher_id" name="publisher_id" class="form-select @error('publisher_id') is-invalid @enderror">
                                <option value="">— প্রকাশক / প্রকাশনী নির্বাচন করুন —</option>
                                @foreach ($pubOptions as $pId => $pName)
                                    <option value="{{ $pId }}" @selected((string) $current === (string) $pId)>
                                        {{ $pName }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="mt-2 p-2 bg-light rounded border">
                                <input type="text" id="f-new_publisher_name" name="new_publisher_name" 
                                       class="form-control form-control-sm" placeholder="অথবা নতুন প্রকাশনীর নাম লিখুন...">
                                <div class="form-text" style="font-size: 11px;">তালিকায় না থাকলে এখানে নাম লিখলে স্বয়ংক্রিয়ভাবে প্রকাশনী যুক্ত হবে।</div>
                            </div>
                            @error('publisher_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        {{-- ══ SECTION 2: BINDING, PRICING, SALES DISCOUNT & PURCHASE COMMISSION ENGINE (BOOKS) ══ --}}
                        @elseif ($name === 'cover_type' && $spec['key'] === 'books')
                            @php
                                $curCoverType = old('cover_type', $editing ? ($record->cover_type ?? '') : '');
                                if (empty($curCoverType)) {
                                    if ($editing && !empty($record->hardcover_price) && empty($record->price)) {
                                        $curCoverType = 'hardcover';
                                    } elseif ($editing && !empty($record->price) && empty($record->hardcover_price)) {
                                        $curCoverType = 'paperback';
                                    } elseif ($editing && !empty($record->price) && !empty($record->hardcover_price)) {
                                        $curCoverType = 'both';
                                    } else {
                                        $curCoverType = 'paperback';
                                    }
                                }
                                $valHardcoverPrice = old('hardcover_price', $editing ? $record->hardcover_price : '');
                                $valHardcoverDiscount = old('hardcover_discount_price', $editing ? $record->hardcover_discount_price : '');
                                $valPaperbackPrice = old('price', $editing ? $record->price : '');
                                $valPaperbackDiscount = old('discount_price', $editing ? $record->discount_price : '');
                                $valCostPrice = old('cost_price', $editing ? $record->cost_price : '');
                            @endphp

                            <div class="col-12">
                                <div class="p-3 bg-white rounded-3 border shadow-xs">
                                    {{-- 1. Binding / Format Switcher --}}
                                    <div class="mb-3 pb-2.5 border-bottom">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                            <label class="form-label fw-bold text-dark mb-0 small">
                                                <i class="fas fa-layer-group text-primary me-1.5"></i> মূল বাঁধাই ও সংস্করণ নির্বাচন (Cover Binding & Edition) <span class="text-danger">*</span>
                                            </label>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 small">
                                                <i class="fas fa-calculator me-1"></i>স্বয়ংক্রিয় কমিশন ও লাভ হিসাব
                                            </span>
                                        </div>

                                        <div class="btn-group w-100 flex-wrap" role="group" id="coverTypeToggleGroup">
                                            <input type="radio" class="btn-check" name="cover_type" id="cover_hardcover" value="hardcover" 
                                                   @checked($curCoverType === 'hardcover') onchange="onCoverTypeChange()">
                                            <label class="btn btn-outline-primary py-2 fw-semibold" for="cover_hardcover">
                                                <i class="fas fa-gem me-1.5 text-warning"></i> হার্ডকভার (Hardcover — প্রধান)
                                            </label>

                                            <input type="radio" class="btn-check" name="cover_type" id="cover_paperback" value="paperback" 
                                                   @checked($curCoverType === 'paperback') onchange="onCoverTypeChange()">
                                            <label class="btn btn-outline-primary py-2 fw-semibold" for="cover_paperback">
                                                <i class="fas fa-book-open me-1.5 text-info"></i> পেপারব্যাক (Paperback)
                                            </label>

                                            <input type="radio" class="btn-check" name="cover_type" id="cover_both" value="both" 
                                                   @checked($curCoverType === 'both') onchange="onCoverTypeChange()">
                                            <label class="btn btn-outline-primary py-2 fw-semibold" for="cover_both">
                                                <i class="fas fa-layer-group me-1.5 text-success"></i> উভয় সংস্করণ (হার্ডকভার ও পেপারব্যাক)
                                            </label>
                                        </div>
                                    </div>

                                    {{-- 2. Pricing, Discount & Purchase Cost Cards --}}
                                    <div class="row g-3">
                                        {{-- Hardcover Card --}}
                                        <div class="col-12 col-md-6" id="panelHardcoverCard">
                                            <div class="card h-100 border rounded-3 bg-light overflow-hidden">
                                                <div class="card-header bg-primary text-white py-2 px-3 d-flex align-items-center justify-content-between">
                                                    <span class="fw-bold small"><i class="fas fa-gem me-1.5 text-warning"></i> হার্ডকভার সংস্করণ (Hardcover)</span>
                                                    <span class="badge bg-white text-primary small px-2 py-0.5 rounded-pill" id="badgeHardcoverStatus">প্রধান মূল্য</span>
                                                </div>
                                                <div class="card-body p-3">
                                                    {{-- Regular Price (MRP) --}}
                                                    <div class="mb-3">
                                                        <label for="f-hardcover_price" class="form-label small fw-bold text-dark mb-1">
                                                            গায়ের মূল্য / নিয়মিত বিক্রয়মূল্য (MRP) <span class="text-danger" id="reqStarHardcover">*</span>
                                                        </label>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-white fw-bold text-primary">৳</span>
                                                            <input type="number" step="0.01" min="0" id="f-hardcover_price" name="hardcover_price" 
                                                                   value="{{ $valHardcoverPrice }}" class="form-control fw-bold" placeholder="0.00" 
                                                                   oninput="onHardcoverPriceChange()">
                                                        </div>
                                                        <div class="form-text text-muted" style="font-size: 11px;">বইয়ের গায়ে মুদ্রিত সর্বোচ্চ খুচরা মূল্য।</div>
                                                    </div>

                                                    {{-- Selling Discount Section --}}
                                                    <div class="p-2.5 bg-white rounded-3 border mb-3">
                                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                            <span class="small fw-bold text-success"><i class="fas fa-tags me-1"></i>গ্রাহক বিক্রয় ছাড় (Sales Discount):</span>
                                                            <span class="text-muted small" style="font-size: 10.5px;">২-ওয়ে অটো হিসাব</span>
                                                        </div>
                                                        <div class="row g-2">
                                                            <div class="col-6">
                                                                <label for="f-hardcover_discount_percent" class="form-label small text-muted mb-1" style="font-size: 11px;">ছাড়ের হার (%)</label>
                                                                <div class="input-group input-group-sm">
                                                                    <input type="number" step="0.5" min="0" max="100" id="f-hardcover_discount_percent" 
                                                                           class="form-control" placeholder="যেমন: ২৫" oninput="onHardcoverDiscountPercentChange()">
                                                                    <span class="input-group-text bg-light fw-bold">%</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <label for="f-hardcover_discount_price" class="form-label small text-muted mb-1" style="font-size: 11px;">ছাড়ের পর বিক্রয়মূল্য (৳)</label>
                                                                <div class="input-group input-group-sm">
                                                                    <span class="input-group-text bg-light fw-bold">৳</span>
                                                                    <input type="number" step="0.01" min="0" id="f-hardcover_discount_price" name="hardcover_discount_price" 
                                                                           value="{{ $valHardcoverDiscount }}" class="form-control fw-semibold" placeholder="0.00" 
                                                                           oninput="onHardcoverDiscountPriceChange()">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="liveHardcoverDiscountBadge" class="mt-1 small fw-semibold"></div>
                                                    </div>

                                                    {{-- Purchase & Cost Section --}}
                                                    <div class="p-2.5 bg-white rounded-3 border border-warning-subtle mb-1">
                                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                            <span class="small fw-bold text-dark"><i class="fas fa-coins me-1 text-warning"></i>ক্রয় হিসাব / প্রকাশনী খরচ:</span>
                                                            <span class="badge bg-warning-subtle text-warning-emphasis small" style="font-size: 10px;">ক্রয়মূল্য কমিশন</span>
                                                        </div>
                                                        <div class="row g-2">
                                                            <div class="col-6">
                                                                <label for="f-hardcover_cost_discount_percent" class="form-label small text-muted mb-1" style="font-size: 11px;">ক্রয় কমিশন ছাড় (%)</label>
                                                                <div class="input-group input-group-sm">
                                                                    <input type="number" step="0.5" min="0" max="100" id="f-hardcover_cost_discount_percent" 
                                                                           class="form-control" placeholder="যেমন: ৪০" oninput="onHardcoverCostDiscountPercentChange()">
                                                                    <span class="input-group-text bg-light fw-bold">%</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <label for="f-hardcover_cost_price_display" class="form-label small text-muted mb-1" style="font-size: 11px;">ক্রয়মূল্য / খরচ (৳)</label>
                                                                <div class="input-group input-group-sm">
                                                                    <span class="input-group-text bg-light fw-bold">৳</span>
                                                                    <input type="number" step="0.01" min="0" id="f-hardcover_cost_price_display" 
                                                                           value="{{ $curCoverType !== 'paperback' ? $valCostPrice : '' }}" class="form-control fw-semibold" placeholder="0.00" 
                                                                           oninput="onHardcoverCostPriceChange()">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="liveHardcoverProfitBadge" class="mt-1.5 small"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Paperback Card --}}
                                        <div class="col-12 col-md-6" id="panelPaperbackCard">
                                            <div class="card h-100 border rounded-3 bg-light overflow-hidden">
                                                <div class="card-header bg-secondary text-white py-2 px-3 d-flex align-items-center justify-content-between" id="headerPaperback">
                                                    <span class="fw-bold small"><i class="fas fa-book-open me-1.5 text-info"></i> পেপারব্যাক সংস্করণ (Paperback)</span>
                                                    <span class="badge bg-white text-secondary small px-2 py-0.5 rounded-pill" id="badgePaperbackStatus">ঐচ্ছিক সংস্করণ</span>
                                                </div>
                                                <div class="card-body p-3">
                                                    {{-- Regular Price (MRP) --}}
                                                    <div class="mb-3">
                                                        <label for="f-price" class="form-label small fw-bold text-dark mb-1">
                                                            গায়ের মূল্য / নিয়মিত বিক্রয়মূল্য (MRP) <span class="text-danger" id="reqStarPaperback" style="display:none;">*</span>
                                                        </label>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-white fw-bold text-primary">৳</span>
                                                            <input type="number" step="0.01" min="0" id="f-price" name="price" 
                                                                   value="{{ $valPaperbackPrice }}" class="form-control fw-bold" placeholder="0.00" 
                                                                   oninput="onRegularPriceChange()">
                                                        </div>
                                                        <div class="form-text text-muted" style="font-size: 11px;">পেপারব্যাক সংস্করণের খুচরা বিক্রয়মূল্য।</div>
                                                    </div>

                                                    {{-- Selling Discount Section --}}
                                                    <div class="p-2.5 bg-white rounded-3 border mb-3">
                                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                            <span class="small fw-bold text-success"><i class="fas fa-tags me-1"></i>গ্রাহক বিক্রয় ছাড় (Sales Discount):</span>
                                                            <span class="text-muted small" style="font-size: 10.5px;">২-ওয়ে অটো হিসাব</span>
                                                        </div>
                                                        <div class="row g-2">
                                                            <div class="col-6">
                                                                <label for="f-discount_percent" class="form-label small text-muted mb-1" style="font-size: 11px;">ছাড়ের হার (%)</label>
                                                                <div class="input-group input-group-sm">
                                                                    <input type="number" step="0.5" min="0" max="100" id="f-discount_percent" 
                                                                           class="form-control" placeholder="যেমন: ২৫" oninput="onDiscountPercentChange()">
                                                                    <span class="input-group-text bg-light fw-bold">%</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <label for="f-discount_price" class="form-label small text-muted mb-1" style="font-size: 11px;">ছাড়ের পর বিক্রয়মূল্য (৳)</label>
                                                                <div class="input-group input-group-sm">
                                                                    <span class="input-group-text bg-light fw-bold">৳</span>
                                                                    <input type="number" step="0.01" min="0" id="f-discount_price" name="discount_price" 
                                                                           value="{{ $valPaperbackDiscount }}" class="form-control fw-semibold" placeholder="0.00" 
                                                                           oninput="onDiscountPriceChange()">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="liveDiscountBadge" class="mt-1 small fw-semibold"></div>
                                                    </div>

                                                    {{-- Purchase & Cost Section --}}
                                                    <div class="p-2.5 bg-white rounded-3 border border-warning-subtle mb-1">
                                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                            <span class="small fw-bold text-dark"><i class="fas fa-coins me-1 text-warning"></i>ক্রয় হিসাব / প্রকাশনী খরচ:</span>
                                                            <span class="badge bg-warning-subtle text-warning-emphasis small" style="font-size: 10px;">ক্রয়মূল্য কমিশন</span>
                                                        </div>
                                                        <div class="row g-2">
                                                            <div class="col-6">
                                                                <label for="f-cost_discount_percent" class="form-label small text-muted mb-1" style="font-size: 11px;">ক্রয় কমিশন ছাড় (%)</label>
                                                                <div class="input-group input-group-sm">
                                                                    <input type="number" step="0.5" min="0" max="100" id="f-cost_discount_percent" 
                                                                           class="form-control" placeholder="যেমন: ৪০" oninput="onPaperbackCostDiscountPercentChange()">
                                                                    <span class="input-group-text bg-light fw-bold">%</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <label for="f-cost_price" class="form-label small text-muted mb-1" style="font-size: 11px;">ক্রয়মূল্য / খরচ (৳)</label>
                                                                <div class="input-group input-group-sm">
                                                                    <span class="input-group-text bg-light fw-bold">৳</span>
                                                                    <input type="number" step="0.01" min="0" id="f-cost_price" name="cost_price" 
                                                                           value="{{ $valCostPrice }}" class="form-control fw-semibold" placeholder="0.00" 
                                                                           oninput="onPaperbackCostPriceChange()">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div id="livePaperbackProfitBadge" class="mt-1.5 small"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        {{-- ══ PRICING & DISCOUNT FIELDS (EBOOKS / OTHER CONTENT TYPES) ══ --}}
                        @elseif ($name === 'price')
                            <label for="f-price" class="form-label small fw-semibold">
                                {{ $field['label'] }} <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">৳</span>
                                <input type="number" step="{{ $field['step'] ?? '1' }}" min="0"
                                       id="f-price" name="price" value="{{ $current }}"
                                       class="form-control @error('price') is-invalid @enderror"
                                       placeholder="0.00" oninput="onRegularPriceChange()">
                            </div>
                            @error('price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                            <div class="mt-2.5 p-2 bg-light rounded border">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label for="f-discount_percent" class="form-label small fw-semibold text-dark mb-0" style="font-size: 11.5px;">
                                        <i class="fas fa-percent me-1 text-primary"></i>ছাড়ের শতকরা হার (%):
                                    </label>
                                    <span class="small text-muted" style="font-size: 11px;">স্বয়ংক্রিয় হিসাব</span>
                                </div>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.5" min="0" max="100" id="f-discount_percent" 
                                           class="form-control" placeholder="যেমন: ২৫" oninput="onDiscountPercentChange()">
                                    <span class="input-group-text bg-white fw-bold">%</span>
                                </div>
                            </div>

                        @elseif ($name === 'discount_price')
                            <label for="f-discount_price" class="form-label small fw-semibold">
                                {{ $field['label'] }} (ছাড়ের পর বিক্রয়মূল্য)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">৳</span>
                                <input type="number" step="{{ $field['step'] ?? '1' }}" min="0"
                                       id="f-discount_price" name="discount_price" value="{{ $current }}"
                                       class="form-control @error('discount_price') is-invalid @enderror"
                                       placeholder="0.00" oninput="onDiscountPriceChange()">
                            </div>
                            <div id="liveDiscountBadge" class="mt-1 small fw-semibold"></div>
                            @error('discount_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                        {{-- ══ PRE-ORDER FIELDS (সম্ভাব্য তারিখ ও বিশেষ বার্তা) ══ --}}
                        @elseif ($name === 'pre_order_release_date')
                            <div class="p-2.5 bg-warning-subtle rounded-3 border border-warning">
                                <label for="f-pre_order_release_date" class="form-label small fw-bold text-dark mb-1">
                                    <i class="fas fa-truck-fast text-warning-emphasis me-1"></i> {{ $field['label'] }}
                                </label>
                                <input type="date" id="f-pre_order_release_date" name="pre_order_release_date" value="{{ $current }}"
                                       class="form-control form-control-sm @error('pre_order_release_date') is-invalid @enderror">
                                <div class="form-text text-muted" style="font-size: 11px;">প্রি-অর্ডার চালু থাকলে সম্ভাব্য কোন তারিখে ডেলিভারি শুরু হবে তা উল্লেখ করুন।</div>
                                @error('pre_order_release_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                        @elseif ($name === 'pre_order_note')
                            <div class="p-2.5 bg-warning-subtle rounded-3 border border-warning">
                                <label for="f-pre_order_note" class="form-label small fw-bold text-dark mb-1">
                                    <i class="fas fa-gift text-warning-emphasis me-1"></i> {{ $field['label'] }}
                                </label>
                                <textarea id="f-pre_order_note" name="pre_order_note" rows="2"
                                          placeholder="{{ $field['placeholder'] ?? 'প্রি-অর্ডার বিশেষ নোট বা উপহার সংক্রান্ত বার্তা...' }}"
                                          class="form-control form-control-sm @error('pre_order_note') is-invalid @enderror">{{ $current }}</textarea>
                                <div class="form-text text-muted" style="font-size: 11px;">গ্রাহকদের আকর্ষণ করতে প্রি-অর্ডার সংক্রান্ত অফার বা বার্তা লিখুন।</div>
                                @error('pre_order_note')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                        @elseif ($name === 'summary')
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label for="f-summary" class="form-label small fw-semibold text-dark mb-0">
                                    <i class="fas fa-feather-pointed text-primary me-1"></i> {{ $field['label'] }}
                                </label>
                                <div class="word-counter-badge safe" id="summaryWordBadge">
                                    <i class="fas fa-font me-1"></i> শব্দ: <span id="summaryWordCount">০</span> / ৪০০
                                </div>
                            </div>
                            <textarea id="f-summary" name="summary" rows="3"
                                      class="form-control @error('summary') is-invalid @enderror"
                                      placeholder="বইয়ের মূল আকর্ষণ বা সংক্ষেপ — যা পণ্যের পেজের শুরুতে সুন্দর হাইলাইট বক্সে প্রদর্শিত হবে (সর্বোচ্চ ৪০০ শব্দ)..."
                                      oninput="updateGenericWordCount(this, 400, 'summaryWordCount', 'summaryWordBadge', 'summaryProgressBar', 'summaryWarning')">{{ $current }}</textarea>
                            <div class="word-counter-progress">
                                <div class="word-counter-progress__bar" id="summaryProgressBar"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <div class="form-text text-muted mb-0" style="font-size: 11.5px;">বইয়ের সংক্ষিপ্ত সারসংক্ষেপ বা আকর্ষণীয় উদ্ধৃতি লিখুন (সর্বোচ্চ ৪০০ শব্দ)।</div>
                                <div id="summaryWarning" class="text-danger small fw-bold d-none"></div>
                            </div>
                            @error('summary')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                        {{-- ══ AUTHOR BIO WITH 300 WORDS LIMIT & PROGRESS ══ --}}
                        @elseif ($name === 'author_bio')
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label for="f-author_bio" class="form-label small fw-semibold text-dark mb-0">
                                    <i class="fas fa-address-card text-primary me-1"></i> {{ $field['label'] }}
                                </label>
                                <div class="word-counter-badge safe" id="authorBioWordBadge">
                                    <i class="fas fa-font me-1"></i> শব্দ: <span id="authorBioWordCount">০</span> / ৩০০
                                </div>
                            </div>
                            <textarea id="f-author_bio" name="author_bio" rows="4"
                                      class="form-control @error('author_bio') is-invalid @enderror"
                                      placeholder="কাস্টম লেখকের পরিচিতি, কর্মজীবন বা সংক্ষিপ্ত জীবনী লিখুন (সর্বোচ্চ ৩০০ শব্দ)..."
                                      oninput="updateGenericWordCount(this, 300, 'authorBioWordCount', 'authorBioWordBadge', 'authorBioProgressBar', 'authorBioWarning')">{{ $current }}</textarea>
                            <div class="word-counter-progress">
                                <div class="word-counter-progress__bar" id="authorBioProgressBar"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <div class="form-text text-muted mb-0" style="font-size: 11.5px;">
                                    ডিরেক্টরি ছাড়া সরাসরি কাস্টম লেখকের বায়ো/পরিচিতি দিতে চাইলে এখানে লিখুন (সর্বোচ্চ ৩০০ শব্দ)।
                                </div>
                                <div id="authorBioWarning" class="text-danger small fw-bold d-none"></div>
                            </div>
                            @error('author_bio')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                        {{-- ══ DESCRIPTION / FLAP (বিস্তারিত বিবরণ - ৪০০ শব্দ) ══ --}}
                        @elseif ($name === 'description')
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label for="f-description" class="form-label small fw-semibold text-dark mb-0">
                                    <i class="fas fa-align-left text-primary me-1"></i> {{ $field['label'] }}
                                </label>
                                <div class="word-counter-badge safe" id="descriptionWordBadge">
                                    <i class="fas fa-font me-1"></i> শব্দ: <span id="descriptionWordCount">০</span> / ৪০০
                                </div>
                            </div>
                            <textarea id="f-description" name="description" rows="7"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="বইয়ের বিস্তারিত ফ্ল্যাপ ও সূচিপত্র/পরিচিতি লিখুন (সর্বোচ্চ ৪০০ শব্দ)..."
                                      oninput="updateGenericWordCount(this, 400, 'descriptionWordCount', 'descriptionWordBadge', 'descriptionProgressBar', 'descriptionWarning')">{{ $current }}</textarea>
                            <div class="word-counter-progress">
                                <div class="word-counter-progress__bar" id="descriptionProgressBar"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <div class="form-text text-muted mb-0" style="font-size: 11.5px;">বইয়ের পূর্ণাঙ্গ ফ্ল্যাপ ও বিবরণ লিখুন (সর্বোচ্চ ৪০০ শব্দ)।</div>
                                <div id="descriptionWarning" class="text-danger small fw-bold d-none"></div>
                            </div>
                            @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                        {{-- ══ সব সাধারণ ফিল্ড ══════════════════════════════ --}}
                        @else
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label for="f-{{ $name }}" class="form-label small fw-semibold mb-0">
                                    {{ $field['label'] }}
                                    @if (str_contains($field['rules'] ?? '', 'required'))
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                @if ($name === 'category_id')
                                    @if (($field['lookup'] ?? '') === 'blog_categories' || $spec['key'] === 'blog')
                                        <button type="button" class="btn btn-link text-primary p-0 text-decoration-none small fw-semibold"
                                                data-bs-toggle="modal" data-bs-target="#quickAddBlogCategoryModal">
                                            <i class="fas fa-plus-circle me-1"></i>নতুন ক্যাটাগরি তৈরি করুন
                                        </button>
                                    @elseif (($field['lookup'] ?? '') === 'categories' || in_array($spec['key'], ['books', 'ebooks'], true))
                                        <button type="button" class="btn btn-link text-primary p-0 text-decoration-none small fw-semibold"
                                                data-bs-toggle="modal" data-bs-target="#quickAddCategoryModal">
                                            <i class="fas fa-plus-circle me-1"></i>নতুন ক্যাটাগরি তৈরি করুন
                                        </button>
                                    @endif
                                @elseif ($name === 'publisher_id' && ($field['lookup'] ?? '') === 'publishers')
                                    <button type="button" class="btn btn-link text-primary p-0 text-decoration-none small fw-semibold"
                                            data-bs-toggle="modal" data-bs-target="#quickAddPublisherModal">
                                        <i class="fas fa-plus-circle me-1"></i>নতুন প্রকাশনী তৈরি করুন
                                    </button>
                                @endif
                            </div>

                            @switch($field['type'])
                                @case('textarea')
                                    <textarea id="f-{{ $name }}" name="{{ $name }}" rows="3"
                                              class="form-control @error($name) is-invalid @enderror">{{ $current }}</textarea>
                                    @break

                                @case('editor')
                                    <div class="rich-editor-wrapper border rounded-3 overflow-hidden shadow-xs mb-2">
                                        <!-- Formatting Toolbar -->
                                        <div class="rich-editor-toolbar bg-light p-2 border-bottom d-flex flex-wrap gap-1 align-items-center">
                                            <!-- Heading Format Selector -->
                                            <select class="form-select form-select-sm" style="width: auto; min-width: 140px;" onchange="formatDoc('formatBlock', this.value, 'f-{{ $name }}')">
                                                <option value="p">স্বাভাবিক প্যারাগ্রাফ (P)</option>
                                                <option value="h1">বড় শিরোনাম (H1)</option>
                                                <option value="h2">উপ-শিরোনাম (H2)</option>
                                                <option value="h3">ছোট শিরোনাম (H3)</option>
                                                <option value="h4">সেকশন হেডিং (H4)</option>
                                                <option value="blockquote">উদ্ধৃতি (Blockquote)</option>
                                            </select>

                                            <!-- Font Size Selector -->
                                            <select class="form-select form-select-sm" style="width: auto; min-width: 110px;" onchange="formatDoc('fontSize', this.value, 'f-{{ $name }}')">
                                                <option value="3">ফন্ট সাইজ</option>
                                                <option value="1">খুব ছোট (Small)</option>
                                                <option value="2">ছোট (13px)</option>
                                                <option value="3">স্বাভাবিক (15px)</option>
                                                <option value="4">মাঝারি (18px)</option>
                                                <option value="5">বড় (24px)</option>
                                                <option value="6">খুব বড় (32px)</option>
                                            </select>

                                            <div class="vr mx-1"></div>

                                            <!-- Style buttons -->
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 fw-bold" onclick="formatDoc('bold', null, 'f-{{ $name }}')" title="বোল্ড (Ctrl+B)">
                                                <i class="fas fa-bold"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 fst-italic" onclick="formatDoc('italic', null, 'f-{{ $name }}')" title="ইটালিক (Ctrl+I)">
                                                <i class="fas fa-italic"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 text-decoration-underline" onclick="formatDoc('underline', null, 'f-{{ $name }}')" title="আন্ডারলাইন (Ctrl+U)">
                                                <i class="fas fa-underline"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 text-decoration-line-through" onclick="formatDoc('strikeThrough', null, 'f-{{ $name }}')" title="স্ট্রাইকথ্রু">
                                                <i class="fas fa-strikethrough"></i>
                                            </button>

                                            <div class="vr mx-1"></div>

                                            <!-- Alignment -->
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="formatDoc('justifyLeft', null, 'f-{{ $name }}')" title="বাম সারিবদ্ধ">
                                                <i class="fas fa-align-left"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="formatDoc('justifyCenter', null, 'f-{{ $name }}')" title="মাঝে সারিবদ্ধ">
                                                <i class="fas fa-align-center"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="formatDoc('justifyRight', null, 'f-{{ $name }}')" title="ডান সারিবদ্ধ">
                                                <i class="fas fa-align-right"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="formatDoc('justifyFull', null, 'f-{{ $name }}')" title="জাস্টিফাই">
                                                <i class="fas fa-align-justify"></i>
                                            </button>

                                            <div class="vr mx-1"></div>

                                            <!-- Lists & Divider -->
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="formatDoc('insertUnorderedList', null, 'f-{{ $name }}')" title="বুলেট পয়েন্ট">
                                                <i class="fas fa-list-ul"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="formatDoc('insertOrderedList', null, 'f-{{ $name }}')" title="নম্বর লিস্ট">
                                                <i class="fas fa-list-ol"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="formatDoc('insertHorizontalRule', null, 'f-{{ $name }}')" title="বিভাজক রেখা">
                                                <i class="fas fa-minus"></i>
                                            </button>

                                            <div class="vr mx-1"></div>

                                            <!-- Link & Media -->
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-primary" onclick="insertLinkPrompt('f-{{ $name }}')" title="লিংক যুক্ত করুন">
                                                <i class="fas fa-link"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-muted" onclick="formatDoc('unlink', null, 'f-{{ $name }}')" title="লিংক মুছুন">
                                                <i class="fas fa-link-slash"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-success" onclick="insertImagePrompt('f-{{ $name }}')" title="ছবি যুক্ত করুন">
                                                <i class="fas fa-image"></i>
                                            </button>

                                            <div class="vr mx-1"></div>

                                            <!-- Actions & Poetry & Spell Check Helper -->
                                            <button type="button" class="btn btn-sm btn-outline-warning border py-1 px-2.5 fw-semibold text-dark" id="spellBtn-{{ $name }}" onclick="toggleSpellChecker('{{ $name }}')" title="প্রমিত বাংলা একাডেমি ও ইংরেজি বানান পরীক্ষা (Spell Check)">
                                                <i class="fas fa-spell-check text-warning me-1"></i> <span id="spellBtnText-{{ $name }}">বানান পরীক্ষা</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary border py-1 px-2.5 fw-semibold" onclick="formatPoetryMode('f-{{ $name }}')" title="কবিতার লাইনবিন্যাস সাজান (Preserve Poetry Verses)">
                                                <i class="fas fa-feather-alt text-primary me-1"></i> কবিতার লাইন সাজান
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="formatDoc('undo', null, 'f-{{ $name }}')" title="আনডু (Ctrl+Z)">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="formatDoc('redo', null, 'f-{{ $name }}')" title="রিডু (Ctrl+Y)">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-danger" onclick="formatDoc('removeFormat', null, 'f-{{ $name }}')" title="ফরম্যাটিং মুছুন">
                                                <i class="fas fa-eraser"></i>
                                            </button>
                                        </div>

                                        <!-- Contenteditable Live Area -->
                                        <div id="editable-{{ $name }}" contenteditable="true" 
                                             class="p-3 bg-white text-dark rich-editor-content" 
                                             style="min-height: 280px; max-height: 550px; overflow-y: auto; outline: none; font-size: 15.5px; line-height: 1.85;"
                                             oninput="onEditorInputWithSpellCheck('{{ $name }}')">{!! $current !!}</div>

                                        <!-- Hidden/Synced real textarea for form submission -->
                                        <textarea id="f-{{ $name }}" name="{{ $name }}" class="d-none @error($name) is-invalid @enderror">{{ $current }}</textarea>
                                    </div>

                                    <!-- Spell Checker Results Notification Box -->
                                    <div id="spell-results-{{ $name }}" class="mt-2.5 d-none"></div>

                                    <div class="form-text" style="font-size: 11.5px;">উপরে দেওয়া টুলবার ব্যবহার করে লেখা বোল্ড, ইটালিক, বড়-ছোট, কবিতার লাইন ও <strong>প্রমিত বানান পরীক্ষা</strong> করতে পারবেন।</div>
                                    @break

                                @case('select')
                                    @php
                                        $options = $field['options'] ?? ($lookups[$field['lookup'] ?? ''] ?? []);
                                    @endphp
                                    <select id="f-{{ $name }}" name="{{ $name }}"
                                            class="form-select @error($name) is-invalid @enderror"
                                            @if($name === 'cover_type') onchange="onCoverTypeChange()" @endif>
                                        <option value="">— নির্বাচন করুন —</option>
                                        @foreach ($options as $optValue => $optLabel)
                                            <option value="{{ $optValue }}" @selected((string) $current === (string) $optValue)>
                                                {{ $optLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($name === 'stock_status')
                                        <div class="form-text" style="font-size: 11.5px;">গ্রাহকদের জন্য বর্তমান প্রাপ্তিসাধ্যতা (Availability)।</div>
                                    @elseif ($name === 'cover_type')
                                        <div class="form-text" style="font-size: 11.5px;">হার্ডকভার, পেপারব্যাক বা উভয় সংস্করণ নির্বাচন করুন।</div>
                                    @endif
                                    @break

                                @case('file')
                                    @php
                                        $isCover = in_array($name, ['cover_image', 'image', 'banner'], true);
                                        $isPdf   = in_array($name, ['sample_pdf_path', 'file_path', 'epub_file_path', 'sample_file_path'], true);
                                        $isAvatar = in_array($name, ['avatar', 'author_image', 'logo'], true);

                                        $guideText = '';
                                        if ($isCover) {
                                            $guideText = 'মাপ: ৬০০ × ৯০০ px (২:৩), JPG/PNG/WebP, সর্বোচ্চ ৪MB';
                                        } elseif ($isAvatar) {
                                            $guideText = 'মাপ: ৪০০ × ৪০০ px (১:১ স্কয়ার), সর্বোচ্চ ৪MB';
                                        } elseif ($isPdf) {
                                            $guideText = 'PDF / EPUB ফরম্যাট, সর্বোচ্চ ২০MB';
                                        }
                                    @endphp

                                    <!-- Modern Interactive Drag & Drop Zone -->
                                    <div class="adm-dropzone position-relative mb-2" id="dropzone-{{ $name }}"
                                         ondragover="handleDropzoneDragOver(event, this)"
                                         ondragleave="handleDropzoneDragLeave(event, this)"
                                         ondrop="handleDropzoneDrop(event, this, 'f-{{ $name }}')">
                                        
                                        <input type="file" id="f-{{ $name }}" name="{{ $name }}"
                                               accept="{{ $field['accept'] ?? '' }}"
                                               class="adm-dropzone__file-input"
                                               onchange="previewAdminFileInput(this, 'preview-container-{{ $name }}')">
                                        
                                        <div class="adm-dropzone__icon">
                                            @if ($isCover)
                                                <i class="fas fa-image"></i>
                                            @elseif ($isAvatar)
                                                <i class="fas fa-camera"></i>
                                            @elseif ($isPdf)
                                                <i class="fas fa-file-pdf text-danger"></i>
                                            @else
                                                <i class="fas fa-cloud-arrow-up"></i>
                                            @endif
                                        </div>

                                        <div class="fw-bold text-dark mb-0.5" style="font-size: 0.9rem;">
                                            {{ $field['label'] }} আপলোড করুন
                                        </div>
                                        <div class="text-muted small mb-1" style="font-size: 0.8rem;">
                                            ফাইল নির্বাচন করতে ক্লিক করুন অথবা এখানে টেনে আনুন
                                        </div>

                                        @if ($guideText)
                                            <span class="badge bg-light text-primary border small fw-normal py-1 px-2">
                                                <i class="fas fa-circle-info me-1"></i> {{ $guideText }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Dynamic Live Instant Preview Box -->
                                    <div id="preview-container-{{ $name }}" class="d-none mb-2 p-2.5 bg-light rounded-3 border border-success border-opacity-50">
                                        <div class="d-flex align-items-center gap-3">
                                            @if ($isCover || $isAvatar)
                                                <img id="preview-img-{{ $name }}" src="" alt="Preview" class="rounded border shadow-xs {{ $isAvatar ? 'rounded-circle' : '' }}" style="height: 65px; width: {{ $isAvatar ? '65px' : '50px' }}; object-fit: cover;">
                                            @else
                                                <div class="rounded-3 bg-danger-subtle text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 55px; font-size: 1.5rem;">
                                                    <i class="fas fa-file-pdf"></i>
                                                </div>
                                            @endif
                                            <div class="overflow-hidden">
                                                <span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i> নতুন ফাইল প্রস্তুত</span>
                                                <div id="preview-filename-{{ $name }}" class="small fw-bold text-dark text-truncate" style="max-width: 250px;"></div>
                                                <div id="preview-filesize-{{ $name }}" class="small text-muted"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Existing Uploaded File View/Remove Widget -->
                                    @if ($editing && $record->{$name})
                                        @php
                                            $rawVal = (string)$record->{$name};
                                            $fileUrl = str_starts_with($rawVal, 'http') 
                                                ? $rawVal 
                                                : (str_starts_with($rawVal, 'storage/') ? asset($rawVal) : asset('storage/' . ltrim($rawVal, '/')));
                                        @endphp
                                        <div class="adm-asset-card">
                                            @if (($field['accept'] ?? '') === 'image/*' || $isCover || $isAvatar)
                                                <img src="{{ $fileUrl }}" alt="" class="adm-asset-card__thumb {{ $isAvatar ? 'rounded-circle' : '' }}">
                                            @else
                                                <div class="adm-asset-card__icon text-danger">
                                                    <i class="fas fa-file-pdf"></i>
                                                </div>
                                            @endif
                                            <div class="overflow-hidden me-auto">
                                                <div class="small fw-bold text-dark mb-0.5 text-truncate" style="max-width: 220px;">
                                                    বর্তমান ফাইল সংরক্ষিত আছে
                                                </div>
                                                <a href="{{ $fileUrl }}" target="_blank" rel="noopener"
                                                   class="btn btn-sm btn-outline-primary py-0.5 px-2 rounded-pill fw-semibold text-decoration-none" style="font-size: 11px;">
                                                    <i class="fas fa-arrow-up-right-from-square me-1"></i> ফাইল ওপেন / ভিউ
                                                </a>
                                            </div>
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                       id="rm-{{ $name }}" name="remove_{{ $name }}" value="1">
                                                <label class="form-check-label small text-danger fw-semibold" for="rm-{{ $name }}">মুছুন</label>
                                            </div>
                                        </div>
                                    @endif
                                    @break

                                @case('number')
                                    <input type="number" step="{{ $field['step'] ?? '1' }}" min="0"
                                           id="f-{{ $name }}" name="{{ $name }}" value="{{ $current }}"
                                           class="form-control @error($name) is-invalid @enderror">
                                    @if ($name === 'page_count')
                                        <div class="form-text" style="font-size: 11px;">বইটির সর্বমোট পৃষ্ঠা সংখ্যা (যেমন: ২৫৬)।</div>
                                    @elseif ($name === 'preview_pages')
                                        <div class="form-text" style="font-size: 11px;">নমুনায় কত পৃষ্ঠা পড়তে পারবে।</div>
                                    @endif
                                    @break

                                @case('date')
                                    <input type="date" id="f-{{ $name }}" name="{{ $name }}"
                                           value="{{ $current instanceof \DateTimeInterface ? $current->format('Y-m-d') : $current }}"
                                           class="form-control @error($name) is-invalid @enderror">
                                    @if ($name === 'published_at')
                                        <div class="form-text" style="font-size: 11px;">বইটির মূল প্রকাশের তারিখ।</div>
                                    @endif
                                    @break

                                @default
                                    <input type="text" id="f-{{ $name }}" name="{{ $name }}" value="{{ $current }}"
                                           class="form-control @error($name) is-invalid @enderror"
                                           oninput="updateLiveMockupCard()">
                                    @if ($name === 'subtitle')
                                        <div class="form-text" style="font-size: 11.5px;">বইয়ের উপশিরোনাম বা বিশেষ সংস্করণ ট্যাগ (ঐচ্ছিক)।</div>
                                    @elseif ($name === 'edition')
                                        <div class="form-text" style="font-size: 11.5px;">যেমন: ১ম সংস্করণ (২০২৪), ২য় মুদ্রণ ইত্যাদি।</div>
                                    @elseif ($name === 'language')
                                        <div class="form-text" style="font-size: 11.5px;">যেমন: বাংলা, ইংরেজি, আরবি ইত্যাদি।</div>
                                    @endif
                            @endswitch

                            @error($name)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        @endif

                    </div>
                @endforeach

                {{-- ══ WEBZINE TABLE OF CONTENTS & ARTICLE INDEXER ════════════════════════ --}}
                @if ($spec['key'] === 'webzines')
                    @php
                        $existingArticles = $editing && $record ? $record->articles()->orderBy('order')->orderBy('page_number')->get() : collect();
                        $authorList = $lookups['authors'] ?? [];
                    @endphp
                    <div class="col-12 mt-4 pt-3 border-top">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div>
                                <h5 class="fw-bold mb-1 text-dark">
                                    <i class="fas fa-list-ol text-primary me-2"></i>সূচিপত্র ও পৃষ্ঠা ইনডেক্সার (Table of Contents & Page Indexer)
                                </h5>
                                <p class="text-muted small mb-0">
                                    প্রতিটি লেখার শিরোনাম, লেখক এবং বইয়ের পৃষ্ঠা নম্বর (Page #) লিখে দিন। পাঠক সূচিপত্রে ক্লিক করলেই স্বয়ংক্রিয়ভাবে সেই নির্দিষ্ট পেজে চলে যাবে।
                                </p>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-xs" onclick="addWebzineTocRow()">
                                <i class="fas fa-plus-circle me-1"></i> নতুন সূচি / লেখা যোগ করুন
                            </button>
                        </div>

                        <div class="table-responsive rounded-3 border bg-white shadow-xs">
                            <table class="table table-hover align-middle mb-0" id="webzineTocTable">
                                <thead class="table-light small fw-bold text-secondary">
                                    <tr>
                                        <th style="width: 45px;" class="text-center">#</th>
                                        <th>লেখার শিরোনাম / অধ্যায় <span class="text-danger">*</span></th>
                                        <th style="width: 220px;">লেখক (Author)</th>
                                        <th style="width: 140px;">পৃষ্ঠা নম্বর (Page #) <span class="text-danger">*</span></th>
                                        <th style="width: 60px;" class="text-center">অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody id="webzineTocBody">
                                    @forelse($existingArticles as $idx => $art)
                                        <tr class="webzine-toc-row">
                                            <td class="text-center fw-bold text-muted row-number">{{ $idx + 1 }}</td>
                                            <td>
                                                <input type="hidden" name="toc_articles[{{ $idx }}][id]" value="{{ $art->id }}">
                                                <input type="hidden" name="toc_articles[{{ $idx }}][order]" class="input-order" value="{{ $art->order ?: ($idx + 1) }}">
                                                <input type="text" name="toc_articles[{{ $idx }}][title]" class="form-control form-control-sm" value="{{ $art->title }}" placeholder="যেমন: সম্পাদকীয় / ভালোবাসার গল্প..." required>
                                            </td>
                                            <td>
                                                <select name="toc_articles[{{ $idx }}][author_id]" class="form-select form-select-sm">
                                                    <option value="">— লেখক নির্বাচন করুন (ঐচ্ছিক) —</option>
                                                    @foreach($authorList as $aId => $aName)
                                                        <option value="{{ $aId }}" @selected((string)$art->author_id === (string)$aId)>{{ $aName }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light text-muted">পৃষ্ঠা</span>
                                                    <input type="number" name="toc_articles[{{ $idx }}][page_number]" class="form-control form-control-sm text-center fw-bold" value="{{ $art->page_number ?: ($idx + 1) }}" min="1" placeholder="1" required>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" onclick="removeWebzineTocRow(this)" title="মুছুন">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="webzine-toc-row">
                                            <td class="text-center fw-bold text-muted row-number">১</td>
                                            <td>
                                                <input type="hidden" name="toc_articles[0][order]" class="input-order" value="1">
                                                <input type="text" name="toc_articles[0][title]" class="form-control form-control-sm" placeholder="যেমন: সম্পাদকীয় / প্রথম রচনা..." required>
                                            </td>
                                            <td>
                                                <select name="toc_articles[0][author_id]" class="form-select form-select-sm">
                                                    <option value="">— লেখক নির্বাচন করুন (ঐচ্ছিক) —</option>
                                                    @foreach($authorList as $aId => $aName)
                                                        <option value="{{ $aId }}">{{ $aName }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light text-muted">পৃষ্ঠা</span>
                                                    <input type="number" name="toc_articles[0][page_number]" class="form-control form-control-sm text-center fw-bold" value="1" min="1" placeholder="1" required>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" onclick="removeWebzineTocRow(this)" title="মুছুন">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="addWebzineTocRow()">
                                <i class="fas fa-plus me-1"></i> আরো একটি সূচি যোগ করুন
                            </button>
                            <span class="small text-muted"><i class="fas fa-info-circle me-1 text-primary"></i>পৃষ্ঠা নম্বর দিলে অনলাইন রিডারে স্বয়ংক্রিয়ভাবে সেই পেজে জাম্প করবে।</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Sidebar (Live Mockup, Moderation, Submit) -->
    <div class="col-12 col-lg-4">
        
        {{-- Live Book / Ebook Card Mockup --}}
        @if ($isBookOrEbook)
            <div class="adm-card p-3 mb-3">
                <h6 class="fw-bold mb-2 text-dark d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-eye me-1.5 text-primary"></i> লাইভ প্রিভিউ (Live Card)</span>
                    <span class="badge bg-success-subtle text-success small rounded-pill">রিয়েল-টাইম</span>
                </h6>
                <div class="p-3 bg-light rounded-3 border text-center">
                    <div class="position-relative mx-auto mb-2" style="width: 125px; height: 185px;">
                        <img id="mockupCoverImg" 
                             src="{{ ($editing && !empty($record->cover_image)) ? (str_starts_with($record->cover_image, 'http') ? $record->cover_image : asset('storage/' . ltrim($record->cover_image, '/'))) : 'https://placehold.co/300x450/e2e8f0/475569?text=Cover+Image' }}" 
                             alt="Book Cover" class="rounded shadow-sm border w-100 h-100" style="object-fit: cover; aspect-ratio: 2/3; image-rendering: -webkit-optimize-contrast;">
                        <span id="mockupDiscountBadge" class="badge bg-danger position-absolute top-0 start-0 m-1 shadow-xs d-none" style="font-size: 0.72rem;">
                            -০%
                        </span>
                        <span id="mockupFormatBadge" class="badge bg-dark position-absolute bottom-0 start-0 m-1 shadow-xs opacity-90" style="font-size: 0.68rem;">
                            হার্ডকভার
                        </span>
                    </div>
                    <div id="mockupTitle" class="fw-bold text-dark text-truncate mb-0.5" style="font-size: 0.92rem;">
                        {{ $editing ? ($record->title ?? 'বইয়ের শিরোনাম') : 'বইয়ের শিরোনাম' }}
                    </div>
                    <div id="mockupAuthor" class="small text-muted mb-1.5 text-truncate" style="font-size: 0.78rem;">
                        {{ $editing ? ($record->author_name ?? 'লেখকের নাম') : 'লেখকের নাম' }}
                    </div>
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <span id="mockupFinalPrice" class="fw-bold text-primary fs-6">৳০</span>
                        <span id="mockupOriginalPrice" class="text-muted text-decoration-line-through small d-none">৳০</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Live Blog Post Mockup --}}
        @if ($spec['key'] === 'blog')
            <div class="adm-card p-3 mb-3">
                <h6 class="fw-bold mb-2 text-dark d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-feather-pointed me-1.5 text-primary"></i> সাহিত্যপত্র ও পোস্ট প্রিভিউ</span>
                    <span class="badge bg-success-subtle text-success small rounded-pill">রিয়েল-টাইম</span>
                </h6>
                <div class="p-3 bg-light rounded-3 border text-start">
                    <div class="position-relative mx-auto mb-2 rounded-3 overflow-hidden" style="max-height: 140px; aspect-ratio: 16/9; background: #e2e8f0;">
                        <img id="mockupCoverImg" 
                             src="{{ ($editing && !empty($record->featured_image)) ? (str_starts_with($record->featured_image, 'http') ? $record->featured_image : asset('storage/' . ltrim($record->featured_image, '/'))) : asset('images/og-banner.jpg') }}" 
                             alt="Blog Cover" class="img-fluid w-100 h-100 object-fit-cover">
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span id="mockupCategoryBadge" class="badge bg-primary text-white" style="font-size: 0.72rem;">
                            {{ $editing && $record->category ? $record->category->name : 'সাহিত্য সাময়িকী' }}
                        </span>
                        <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">
                            {{ $editing && $record->published_at ? $record->published_at->format('d M Y') : date('d M Y') }}
                        </span>
                    </div>
                    <div id="mockupTitle" class="fw-bold text-dark text-truncate mb-1" style="font-size: 0.95rem;">
                        {{ $editing ? ($record->title ?? 'পোস্টের শিরোনাম') : 'পোস্টের শিরোনাম' }}
                    </div>
                    <div id="mockupSubtitle" class="small text-secondary mb-1 text-truncate" style="font-size: 0.8rem;">
                        {{ $editing ? ($record->subtitle ?? '') : '' }}
                    </div>
                    <div id="mockupAuthor" class="small text-muted d-flex align-items-center gap-1" style="font-size: 0.78rem;">
                        <i class="fas fa-pen-nib text-success"></i>
                        <span>{{ $editing && $record->author ? $record->author->name : 'লেখকের নাম' }}</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Live Webzine Card & Reader Mockup --}}
        @if ($spec['key'] === 'webzines')
            <div class="adm-card p-3 mb-3">
                <h6 class="fw-bold mb-2 text-dark d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-newspaper me-1.5 text-info"></i> ওয়েবজিন লাইভ কার্ড ও রিডার</span>
                    <span class="badge bg-info-subtle text-info small rounded-pill">লাইভ স্ট্যাটাস</span>
                </h6>
                <div class="p-3 bg-light rounded-3 border text-center">
                    <div class="position-relative mx-auto mb-2" style="max-width: 140px;">
                        <img id="mockupCoverImg" 
                             src="{{ ($editing && !empty($record->cover_image)) ? (str_starts_with($record->cover_image, 'http') ? $record->cover_image : asset('storage/' . ltrim($record->cover_image, '/'))) : 'https://placehold.co/300x450/e2e8f0/475569?text=Webzine+Cover' }}" 
                             alt="Webzine Cover" class="img-fluid rounded shadow-sm border" style="aspect-ratio: 2/3; object-fit: cover; width: 100%;">
                        <span id="mockupIssueBadge" class="badge bg-primary position-absolute top-0 start-0 m-1 shadow-xs">
                            {{ $editing ? ($record->issue_number ?? 'সংখ্যা') : '১ম সংখ্যা' }}
                        </span>
                    </div>
                    <div id="mockupTitle" class="fw-bold text-dark text-truncate mb-0.5" style="font-size: 0.95rem;">
                        {{ $editing ? ($record->title ?? 'ওয়েবজিনের শিরোনাম') : 'ওয়েবজিনের শিরোনাম' }}
                    </div>
                    <div id="mockupPublisher" class="small text-muted mb-2 text-truncate" style="font-size: 0.8rem;">
                        {{ $editing && $record->publisher ? $record->publisher->name : 'আইডিয়া প্রকাশন' }}
                    </div>

                    @if ($editing)
                        <div class="d-grid gap-1.5 mt-2">
                            <a href="{{ route('webzine.read', $record->slug ?: $record->id) }}" target="_blank" class="btn btn-sm btn-info text-white rounded-pill fw-bold">
                                <i class="fas fa-book-open me-1"></i> সরাসরি রিডারে পড়ুন
                            </a>
                            <a href="{{ route('webzine.show', $record->slug ?: $record->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill">
                                <i class="fas fa-eye me-1"></i> পাবলিক পেজ প্রিভিউ
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Posting on behalf of someone --}}
        <div class="adm-card p-3 mb-3">
            <h2 class="h6 fw-bold mb-1"><i class="fas fa-user-pen me-1 text-muted"></i> কার পক্ষে (কন্ট্রিবিউটর ক্রেডিট)</h2>
            <p class="text-muted small mb-3" style="font-size: 11.5px;">
                যিনি অনলাইনে রেজিস্ট্রেশন করতে পারেন না, তাঁর নাম এখানে লিখলে এন্ট্রিটি তাঁর নামে সংরক্ষিত থাকবে।
            </p>

            <div class="mb-2.5">
                <label for="f-submitted_by" class="form-label small fw-semibold mb-1">রেজিস্টার্ড ব্যবহারকারী</label>
                <select id="f-submitted_by" name="submitted_by" class="form-select form-select-sm @error('submitted_by') is-invalid @enderror">
                    <option value="">— আমি নিজে (অ্যাডমিন) —</option>
                    @foreach (($creditees ?? []) as $userId => $userLabel)
                        <option value="{{ $userId }}" @selected((string) $val('submitted_by') === (string) $userId)>
                            {{ $userLabel }}
                        </option>
                    @endforeach
                </select>
                @error('submitted_by')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-2.5">
                <label for="f-owner_name" class="form-label small fw-semibold mb-1">অফলাইন ব্যক্তির নাম</label>
                <input type="text" id="f-owner_name" name="owner_name" value="{{ $val('owner_name') }}"
                       placeholder="যেমন: মোঃ আনিসুর রহমান"
                       class="form-control form-control-sm @error('owner_name') is-invalid @enderror">
                @error('owner_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div>
                <label for="f-owner_phone" class="form-label small fw-semibold mb-1">যোগাযোগের ফোন নম্বর</label>
                <input type="text" id="f-owner_phone" name="owner_phone" value="{{ $val('owner_phone') }}"
                       placeholder="01XXXXXXXXX"
                       class="form-control form-control-sm @error('owner_phone') is-invalid @enderror">
                @error('owner_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Moderation & Slug --}}
        <div class="adm-card p-3 mb-3">
            <h2 class="h6 fw-bold mb-2.5"><i class="fas fa-circle-check me-1 text-muted"></i> অনুমোদন ও ইউআরএল</h2>

            <div class="mb-3">
                <label for="f-mod_status" class="form-label small fw-semibold mb-1">স্ট্যাটাস</label>
                <select id="f-mod_status" name="mod_status" class="form-select form-select-sm">
                    @foreach (['approved' => 'অনুমোদিত (সরাসরি লাইভ)', 'pending' => 'অপেক্ষমাণ (রিভিউ)', 'rejected' => 'বাতিল'] as $value => $text)
                        <option value="{{ $value }}" @selected($val('mod_status', 'approved') === $value)>{{ $text }}</option>
                    @endforeach
                </select>
            </div>

            @if ($editing && $record->rejection_reason)
                <div class="alert alert-warning small mt-2 mb-2 p-2">
                    <strong>বাতিলের কারণ:</strong> {{ $record->rejection_reason }}
                </div>
            @endif

            <div>
                <label for="f-slug" class="form-label small fw-semibold mb-1">কাস্টম Slug (URL)</label>
                <input type="text" id="f-slug" name="slug" value="{{ $val('slug') }}"
                       placeholder="খালি রাখলে নাম থেকে তৈরি হবে"
                       class="form-control form-control-sm @error('slug') is-invalid @enderror">
                <div class="form-text" style="font-size: 11px;">ইংরেজি বা বাংলায় এসইও-বান্ধব ইউআরএল।</div>
                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary rounded-pill py-2.5 fw-bold shadow-xs">
                <i class="fas fa-floppy-disk me-1.5"></i> {{ $editing ? 'হালনাগাদ সম্পন্ন করুন' : 'সংরক্ষণ ও প্রকাশ করুন' }}
            </button>
            <a href="{{ route($spec['listRoute']) }}" class="btn btn-outline-secondary rounded-pill py-2">বাতিল করুন</a>
        </div>
    </div>
</form>

{{-- ========================================================================= --}}
{{-- MODAL 1: QUICK ADD CATEGORY (ক্যাটাগরি কুইক ক্রিয়েটর)                      --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="quickAddCategoryModal" tabindex="-1" aria-labelledby="quickAddCatLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0" id="quickAddCatLabel">
                    <i class="fas fa-folder-plus me-1.5"></i> নতুন ক্যাটাগরি তৈরি করুন
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickCategoryForm" onsubmit="handleQuickCategorySubmit(event)">
                <div class="modal-body p-3">
                    <div id="quickCatAlert"></div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">ক্যাটাগরির নাম <span class="text-danger">*</span></label>
                        <input type="text" id="quick_cat_name" name="name" class="form-control form-control-sm" 
                               placeholder="উদা: অনুবাদ সাহিত্য / রম্যরচনা / বিজ্ঞান কল্পকাহিনী" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">মূল ক্যাটাগরি (Parent Category - ঐচ্ছিক)</label>
                        <select id="quick_cat_parent_id" name="parent_id" class="form-select form-select-sm">
                            <option value="">— এটিই মূল ক্যাটাগরি (No Parent) —</option>
                            @foreach ($lookups['categories'] ?? [] as $cId => $cName)
                                <option value="{{ $cId }}">{{ $cName }}</option>
                            @endforeach
                        </select>
                        <div class="form-text" style="font-size: 11px;">কোনো ক্যাটাগরির অধীনে সাব-ক্যাটাগরি বানাতে চাইলে মূল ক্যাটাগরি সিলেক্ট করুন।</div>
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">বিবরণ (ঐচ্ছিক)</label>
                        <textarea id="quick_cat_description" name="description" rows="2" class="form-control form-control-sm" placeholder="সংক্ষিপ্ত বিবরণ..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" id="quickCatBtn" class="btn btn-sm btn-primary">
                        <i class="fas fa-check-circle me-1"></i> ক্যাটাগরি সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 1.5: QUICK ADD BLOG CATEGORY (ব্লগ ক্যাটাগরি কুইক ক্রিয়েটর)          --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="quickAddBlogCategoryModal" tabindex="-1" aria-labelledby="quickAddBlogCatLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0" id="quickAddBlogCatLabel">
                    <i class="fas fa-shapes me-1.5"></i> নতুন ব্লগ ক্যাটাগরি তৈরি করুন
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickBlogCategoryForm" onsubmit="handleQuickBlogCategorySubmit(event)">
                <div class="modal-body p-3">
                    <div id="quickBlogCatAlert"></div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">ব্লগ ক্যাটাগরির নাম <span class="text-danger">*</span></label>
                        <input type="text" id="quick_blog_cat_name" name="name" class="form-control form-control-sm" 
                               placeholder="উদা: কবিতা / গল্প / প্রবন্ধ / ইতিহাস" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">আইকন ক্লাস (FontAwesome - ঐচ্ছিক)</label>
                        <input type="text" id="quick_blog_cat_icon" name="icon" class="form-control form-control-sm" 
                               placeholder="উদা: feather-pointed / book-open-reader / pen-nib" value="feather-pointed">
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">বিবরণ (ঐচ্ছিক)</label>
                        <textarea id="quick_blog_cat_description" name="description" rows="2" class="form-control form-control-sm" placeholder="সংক্ষিপ্ত বিবরণ বা ভূমিকা..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" id="quickBlogCatBtn" class="btn btn-sm btn-primary">
                        <i class="fas fa-check-circle me-1"></i> ব্লগ ক্যাটাগরি সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 2: QUICK ADD PUBLISHER (প্রকাশনী কুইক ক্রিয়েটর)                    --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="quickAddPublisherModal" tabindex="-1" aria-labelledby="quickAddPubLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0" id="quickAddPubLabel">
                    <i class="fas fa-building me-1.5"></i> নতুন প্রকাশনী যুক্ত করুন
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickPublisherForm" onsubmit="handleQuickPublisherSubmit(event)">
                <div class="modal-body p-3">
                    <div id="quickPubAlert"></div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">প্রকাশনীর নাম <span class="text-danger">*</span></label>
                        <input type="text" id="quick_pub_name" name="name" class="form-control form-control-sm" 
                               placeholder="উদা: সময় প্রকাশন / বাতিঘর / ইত্যাদি" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">মোবাইল / ফোন নম্বর</label>
                        <input type="text" id="quick_pub_phone" name="phone" class="form-control form-control-sm" placeholder="01XXXXXXXXX">
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">ঠিকানা</label>
                        <input type="text" id="quick_pub_address" name="address" class="form-control form-control-sm" placeholder="বাংলাবাজার, ঢাকা">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" id="quickPubBtn" class="btn btn-sm btn-primary">
                        <i class="fas fa-check-circle me-1"></i> প্রকাশনী সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 3: QUICK ADD AUTHOR (লেখক কুইক ক্রিয়েটর)                          --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="quickAddAuthorModal" tabindex="-1" aria-labelledby="quickAddAuthLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0" id="quickAddAuthLabel">
                    <i class="fas fa-pen-nib me-1.5"></i> নতুন লেখক যুক্ত করুন
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickAuthorForm" onsubmit="handleQuickAuthorSubmit(event)">
                <div class="modal-body p-3">
                    <div id="quickAuthAlert"></div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">লেখকের পূর্ণ নাম <span class="text-danger">*</span></label>
                        <input type="text" id="quick_auth_name" name="name" class="form-control form-control-sm" 
                               placeholder="উদা: হুমায়ূন আহমেদ / জাফর ইকবাল" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">ফোন নম্বর</label>
                        <input type="text" id="quick_auth_phone" name="phone" class="form-control form-control-sm" placeholder="01XXXXXXXXX">
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">সংক্ষিপ্ত পরিচিতি (Bio)</label>
                        <textarea id="quick_auth_bio" name="bio" rows="2" class="form-control form-control-sm" placeholder="লেখকের সংক্ষিপ্ত জীবনী..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" id="quickAuthBtn" class="btn btn-sm btn-primary">
                        <i class="fas fa-check-circle me-1"></i> লেখক সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Toggle Author Input Mode
function toggleAuthorMode(mode) {
    const dirPanel  = document.getElementById('author-directory-panel');
    const custPanel = document.getElementById('author-custom-panel');
    const isDir = (mode === 'directory');
    if (dirPanel) dirPanel.style.display  = isDir ? '' : 'none';
    if (custPanel) custPanel.style.display = isDir ? 'none' : '';

    const dirRadio = document.getElementById('author-mode-directory');
    const custRadio = document.getElementById('author-mode-custom');
    if (dirRadio && isDir) dirRadio.checked = true;
    if (custRadio && !isDir) custRadio.checked = true;

    updateLiveMockupCard();
}

function onAuthorDirectoryChange(select) {
    const authorCustom = document.getElementById('f-author_name');
    if (select && select.selectedIndex > 0) {
        const text = select.options[select.selectedIndex].text.trim();
        if (authorCustom && !authorCustom.value) {
            authorCustom.value = text;
        }
    }
    updateLiveMockupCard();
}

(function () {
    // Initial sync of paperback discount percentage on load
    const initPrice = parseFloat(document.getElementById('f-price')?.value) || 0;
    const initDisc = parseFloat(document.getElementById('f-discount_price')?.value) || 0;
    if (initPrice > 0 && initDisc > 0 && initDisc < initPrice) {
        const initPct = Math.round(((initPrice - initDisc) / initPrice) * 100);
        const pctInput = document.getElementById('f-discount_percent');
        if (pctInput) pctInput.value = initPct;
    }

    // Initial sync of hardcover discount percentage on load
    const initHcPrice = parseFloat(document.getElementById('f-hardcover_price')?.value) || 0;
    const initHcDisc = parseFloat(document.getElementById('f-hardcover_discount_price')?.value) || 0;
    if (initHcPrice > 0 && initHcDisc > 0 && initHcDisc < initHcPrice) {
        const initHcPct = Math.round(((initHcPrice - initHcDisc) / initHcPrice) * 100);
        const hcPctInput = document.getElementById('f-hardcover_discount_percent');
        if (hcPctInput) hcPctInput.value = initHcPct;
    }

    // Attach listeners for live mockup card updates
    const titleInput = document.getElementById('f-title');
    const authorSelect = document.getElementById('f-author_link_id');
    const authorCustom = document.getElementById('f-author_name');
    
    if (titleInput) titleInput.addEventListener('input', updateLiveMockupCard);
    if (authorSelect) authorSelect.addEventListener('change', updateLiveMockupCard);
    if (authorCustom) authorCustom.addEventListener('input', updateLiveMockupCard);

    calculateLiveDiscount();
    calculateLiveHardcoverDiscount();
    updateCoverTypeRequirement();
    updateLiveMockupCard();
    updateSummaryWordCount();
    updateDescriptionWordCount();
    updateAuthorBioWordCount();
})();

// Dynamic requirement indicators for book cover formats (Hardcover, Paperback, Both)
function updateCoverTypeRequirement() {
    const selectedRadio = document.querySelector('input[name="cover_type"]:checked');
    const val = selectedRadio ? selectedRadio.value : (document.getElementById('f-cover_type')?.value || 'hardcover');

    const cardHc = document.getElementById('panelHardcoverCard');
    const cardPb = document.getElementById('panelPaperbackCard');
    const headerPb = document.getElementById('headerPaperback');
    const badgeHc = document.getElementById('badgeHardcoverStatus');
    const badgePb = document.getElementById('badgePaperbackStatus');
    const starHc = document.getElementById('reqStarHardcover');
    const starPb = document.getElementById('reqStarPaperback');
    const inputHc = document.getElementById('f-hardcover_price');
    const inputPb = document.getElementById('f-price');

    if (val === 'hardcover') {
        if (cardHc) { cardHc.style.opacity = '1'; }
        if (cardPb) { cardPb.style.opacity = '0.78'; }
        if (headerPb) { headerPb.className = 'card-header bg-secondary text-white py-2 px-3 d-flex align-items-center justify-content-between'; }
        if (badgeHc) { badgeHc.className = 'badge bg-white text-primary small px-2 py-0.5 rounded-pill'; badgeHc.textContent = 'প্রধান সংস্করণ'; }
        if (badgePb) { badgePb.className = 'badge bg-white text-secondary small px-2 py-0.5 rounded-pill'; badgePb.textContent = 'ঐচ্ছিক সংস্করণ'; }
        if (starHc) starHc.style.display = 'inline';
        if (starPb) starPb.style.display = 'none';
        if (inputHc) inputHc.setAttribute('required', 'required');
        if (inputPb) inputPb.removeAttribute('required');
    } else if (val === 'paperback') {
        if (cardHc) { cardHc.style.opacity = '0.78'; }
        if (cardPb) { cardPb.style.opacity = '1'; }
        if (headerPb) { headerPb.className = 'card-header bg-primary text-white py-2 px-3 d-flex align-items-center justify-content-between'; }
        if (badgeHc) { badgeHc.className = 'badge bg-white text-secondary small px-2 py-0.5 rounded-pill'; badgeHc.textContent = 'ঐচ্ছিক সংস্করণ'; }
        if (badgePb) { badgePb.className = 'badge bg-white text-primary small px-2 py-0.5 rounded-pill'; badgePb.textContent = 'প্রধান সংস্করণ'; }
        if (starHc) starHc.style.display = 'none';
        if (starPb) starPb.style.display = 'inline';
        if (inputHc) inputHc.removeAttribute('required');
        if (inputPb) inputPb.setAttribute('required', 'required');
    } else if (val === 'both') {
        if (cardHc) { cardHc.style.opacity = '1'; }
        if (cardPb) { cardPb.style.opacity = '1'; }
        if (headerPb) { headerPb.className = 'card-header bg-primary text-white py-2 px-3 d-flex align-items-center justify-content-between'; }
        if (badgeHc) { badgeHc.className = 'badge bg-white text-primary small px-2 py-0.5 rounded-pill'; badgeHc.textContent = 'হার্ডকভার আবশ্যক'; }
        if (badgePb) { badgePb.className = 'badge bg-white text-primary small px-2 py-0.5 rounded-pill'; badgePb.textContent = 'পেপারব্যাক আবশ্যক'; }
        if (starHc) starHc.style.display = 'inline';
        if (starPb) starPb.style.display = 'inline';
        if (inputHc) inputHc.setAttribute('required', 'required');
        if (inputPb) inputPb.setAttribute('required', 'required');
    }
}

function onCoverTypeChange() {
    updateCoverTypeRequirement();
    updateLiveMockupCard();
    syncActiveCostPrice();
}

function syncActiveCostPrice() {
    const selectedRadio = document.querySelector('input[name="cover_type"]:checked');
    const val = selectedRadio ? selectedRadio.value : 'hardcover';
    const hcCostInput = document.getElementById('f-hardcover_cost_price_display');
    const pbCostInput = document.getElementById('f-cost_price');

    if (val === 'hardcover' && hcCostInput && pbCostInput && hcCostInput.value) {
        pbCostInput.value = hcCostInput.value;
    }
}

// Hardcover Two-way interactive price, discount, cost & profit calculations
function onHardcoverPriceChange() {
    const price = parseFloat(document.getElementById('f-hardcover_price')?.value) || 0;
    const discPct = parseFloat(document.getElementById('f-hardcover_discount_percent')?.value) || 0;
    const discInput = document.getElementById('f-hardcover_discount_price');
    const costPct = parseFloat(document.getElementById('f-hardcover_cost_discount_percent')?.value) || 0;
    const costInput = document.getElementById('f-hardcover_cost_price_display');

    if (price > 0 && discPct > 0 && discPct <= 100) {
        const discounted = Math.round(price * (1 - discPct / 100) * 100) / 100;
        if (discInput) discInput.value = discounted;
    } else if (discInput && discInput.value) {
        const disc = parseFloat(discInput.value) || 0;
        if (price > 0 && disc < price) {
            const calculatedPct = Math.round(((price - disc) / price) * 100);
            const pctInput = document.getElementById('f-hardcover_discount_percent');
            if (pctInput) pctInput.value = calculatedPct;
        }
    }

    if (price > 0 && costPct > 0 && costPct <= 100) {
        const costVal = Math.round(price * (1 - costPct / 100) * 100) / 100;
        if (costInput) costInput.value = costVal;
        syncActiveCostPrice();
    }

    calculateLiveHardcoverDiscount();
    calculateLiveHardcoverProfit();
}

function onHardcoverDiscountPercentChange() {
    const price = parseFloat(document.getElementById('f-hardcover_price')?.value) || 0;
    const pct = parseFloat(document.getElementById('f-hardcover_discount_percent')?.value) || 0;
    const discInput = document.getElementById('f-hardcover_discount_price');

    if (price > 0 && pct >= 0 && pct <= 100) {
        const discounted = Math.round(price * (1 - pct / 100) * 100) / 100;
        if (discInput) discInput.value = discounted;
    } else if (pct === 0) {
        if (discInput) discInput.value = price;
    }
    calculateLiveHardcoverDiscount();
    calculateLiveHardcoverProfit();
}

function onHardcoverDiscountPriceChange() {
    const price = parseFloat(document.getElementById('f-hardcover_price')?.value) || 0;
    const disc = parseFloat(document.getElementById('f-hardcover_discount_price')?.value) || 0;
    const pctInput = document.getElementById('f-hardcover_discount_percent');

    if (price > 0 && disc > 0 && disc < price) {
        const calculatedPct = Math.round(((price - disc) / price) * 100);
        if (pctInput) pctInput.value = calculatedPct;
    } else if (disc === 0 || disc >= price) {
        if (pctInput) pctInput.value = 0;
    }
    calculateLiveHardcoverDiscount();
    calculateLiveHardcoverProfit();
}

function onHardcoverCostDiscountPercentChange() {
    const price = parseFloat(document.getElementById('f-hardcover_price')?.value) || 0;
    const pct = parseFloat(document.getElementById('f-hardcover_cost_discount_percent')?.value) || 0;
    const costInput = document.getElementById('f-hardcover_cost_price_display');

    if (price > 0 && pct >= 0 && pct <= 100) {
        const costVal = Math.round(price * (1 - pct / 100) * 100) / 100;
        if (costInput) costInput.value = costVal;
        syncActiveCostPrice();
    }
    calculateLiveHardcoverProfit();
}

function onHardcoverCostPriceChange() {
    const price = parseFloat(document.getElementById('f-hardcover_price')?.value) || 0;
    const cost = parseFloat(document.getElementById('f-hardcover_cost_price_display')?.value) || 0;
    const pctInput = document.getElementById('f-hardcover_cost_discount_percent');

    if (price > 0 && cost > 0 && cost < price) {
        const calculatedPct = Math.round(((price - cost) / price) * 100);
        if (pctInput) pctInput.value = calculatedPct;
    } else if (cost === 0 || cost >= price) {
        if (pctInput) pctInput.value = 0;
    }
    syncActiveCostPrice();
    calculateLiveHardcoverProfit();
}

function calculateLiveHardcoverProfit() {
    const price = parseFloat(document.getElementById('f-hardcover_price')?.value) || 0;
    const disc = parseFloat(document.getElementById('f-hardcover_discount_price')?.value) || 0;
    const cost = parseFloat(document.getElementById('f-hardcover_cost_price_display')?.value) || 0;
    const badgeEl = document.getElementById('liveHardcoverProfitBadge');

    if (!badgeEl) return;

    const sellPrice = (disc > 0 && disc <= price) ? disc : price;

    if (sellPrice > 0 && cost > 0) {
        const profit = sellPrice - cost;
        const margin = Math.round((profit / sellPrice) * 1000) / 10;
        if (profit >= 0) {
            badgeEl.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle p-1.5 w-100 d-flex align-items-center justify-content-between"><span><i class="fas fa-chart-line me-1"></i>আনুমানিক লাভ: <strong>৳${profit.toLocaleString('bn-BD', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></span> <span class="badge bg-success text-white">${margin}% নিট মার্জিন</span></span>`;
        } else {
            badgeEl.innerHTML = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle p-1.5 w-100"><i class="fas fa-triangle-exclamation me-1"></i>সতর্কবার্তা: ক্রয়মূল্য বিক্রয়মূল্যের চেয়ে বেশি! ক্ষতি ৳${Math.abs(profit).toFixed(2)}</span>`;
        }
    } else {
        badgeEl.innerHTML = '';
    }
}

// Paperback Two-way interactive price, discount, cost & profit calculations
function onRegularPriceChange() {
    const price = parseFloat(document.getElementById('f-price')?.value) || 0;
    const discPct = parseFloat(document.getElementById('f-discount_percent')?.value) || 0;
    const discInput = document.getElementById('f-discount_price');
    const costPct = parseFloat(document.getElementById('f-cost_discount_percent')?.value) || 0;
    const costInput = document.getElementById('f-cost_price');

    if (price > 0 && discPct > 0 && discPct <= 100) {
        const discounted = Math.round(price * (1 - discPct / 100) * 100) / 100;
        if (discInput) discInput.value = discounted;
    } else if (discInput && discInput.value) {
        const disc = parseFloat(discInput.value) || 0;
        if (price > 0 && disc < price) {
            const calculatedPct = Math.round(((price - disc) / price) * 100);
            const pctInput = document.getElementById('f-discount_percent');
            if (pctInput) pctInput.value = calculatedPct;
        }
    }

    if (price > 0 && costPct > 0 && costPct <= 100) {
        const costVal = Math.round(price * (1 - costPct / 100) * 100) / 100;
        if (costInput) costInput.value = costVal;
    }

    calculateLiveDiscount();
    calculateLivePaperbackProfit();
}

function onDiscountPercentChange() {
    const price = parseFloat(document.getElementById('f-price')?.value) || 0;
    const pct = parseFloat(document.getElementById('f-discount_percent')?.value) || 0;
    const discInput = document.getElementById('f-discount_price');

    if (price > 0 && pct >= 0 && pct <= 100) {
        const discounted = Math.round(price * (1 - pct / 100) * 100) / 100;
        if (discInput) discInput.value = discounted;
    } else if (pct === 0) {
        if (discInput) discInput.value = price;
    }
    calculateLiveDiscount();
    calculateLivePaperbackProfit();
}

function onDiscountPriceChange() {
    const price = parseFloat(document.getElementById('f-price')?.value) || 0;
    const disc = parseFloat(document.getElementById('f-discount_price')?.value) || 0;
    const pctInput = document.getElementById('f-discount_percent');

    if (price > 0 && disc > 0 && disc < price) {
        const calculatedPct = Math.round(((price - disc) / price) * 100);
        if (pctInput) pctInput.value = calculatedPct;
    } else if (disc === 0 || disc >= price) {
        if (pctInput) pctInput.value = 0;
    }
    calculateLiveDiscount();
    calculateLivePaperbackProfit();
}

function onPaperbackCostDiscountPercentChange() {
    const price = parseFloat(document.getElementById('f-price')?.value) || 0;
    const pct = parseFloat(document.getElementById('f-cost_discount_percent')?.value) || 0;
    const costInput = document.getElementById('f-cost_price');

    if (price > 0 && pct >= 0 && pct <= 100) {
        const costVal = Math.round(price * (1 - pct / 100) * 100) / 100;
        if (costInput) costInput.value = costVal;
    }
    calculateLivePaperbackProfit();
}

function onPaperbackCostPriceChange() {
    const price = parseFloat(document.getElementById('f-price')?.value) || 0;
    const cost = parseFloat(document.getElementById('f-cost_price')?.value) || 0;
    const pctInput = document.getElementById('f-cost_discount_percent');

    if (price > 0 && cost > 0 && cost < price) {
        const calculatedPct = Math.round(((price - cost) / price) * 100);
        if (pctInput) pctInput.value = calculatedPct;
    } else if (cost === 0 || cost >= price) {
        if (pctInput) pctInput.value = 0;
    }
    calculateLivePaperbackProfit();
}

function calculateLivePaperbackProfit() {
    const price = parseFloat(document.getElementById('f-price')?.value) || 0;
    const disc = parseFloat(document.getElementById('f-discount_price')?.value) || 0;
    const cost = parseFloat(document.getElementById('f-cost_price')?.value) || 0;
    const badgeEl = document.getElementById('livePaperbackProfitBadge');

    if (!badgeEl) return;

    const sellPrice = (disc > 0 && disc <= price) ? disc : price;

    if (sellPrice > 0 && cost > 0) {
        const profit = sellPrice - cost;
        const margin = Math.round((profit / sellPrice) * 1000) / 10;
        if (profit >= 0) {
            badgeEl.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle p-1.5 w-100 d-flex align-items-center justify-content-between"><span><i class="fas fa-chart-line me-1"></i>আনুমানিক লাভ: <strong>৳${profit.toLocaleString('bn-BD', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></span> <span class="badge bg-success text-white">${margin}% নিট মার্জিন</span></span>`;
        } else {
            badgeEl.innerHTML = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle p-1.5 w-100"><i class="fas fa-triangle-exclamation me-1"></i>সতর্কবার্তা: ক্রয়মূল্য বিক্রয়মূল্যের চেয়ে বেশি! ক্ষতি ৳${Math.abs(profit).toFixed(2)}</span>`;
        }
    } else {
        badgeEl.innerHTML = '';
    }
}

// Real-time discount calculation
function calculateLiveDiscount() {
    const priceEl = document.getElementById('f-price');
    const discEl = document.getElementById('f-discount_price');
    const badgeEl = document.getElementById('liveDiscountBadge');

    const price = priceEl ? parseFloat(priceEl.value) || 0 : 0;
    const discount = discEl ? parseFloat(discEl.value) || 0 : 0;

    if (!badgeEl) return;

    if (price > 0 && discount > 0) {
        if (discount < price) {
            const savings = price - discount;
            const percent = Math.round((savings / price) * 100);
            badgeEl.className = 'mt-1 small fw-semibold text-success';
            badgeEl.innerHTML = `<i class="fas fa-tags me-1"></i> পেপারব্যাকে ${percent}% ছাড়! গ্রাহক বাঁচাবে ৳${savings.toLocaleString('bn-BD')}`;
        } else if (discount === price) {
            badgeEl.className = 'mt-1 small fw-semibold text-muted';
            badgeEl.innerHTML = `কোনো ছাড় প্রযোজ্য নয়।`;
        } else {
            badgeEl.className = 'mt-1 small fw-semibold text-danger';
            badgeEl.innerHTML = `<i class="fas fa-triangle-exclamation me-1"></i> সতর্কবার্তা: ছাড়ের মূল্য মূল দামের চেয়ে বেশি!`;
        }
    } else {
        badgeEl.innerHTML = '';
    }

    updateLiveMockupCard();
}

function calculateLiveHardcoverDiscount() {
    const priceEl = document.getElementById('f-hardcover_price');
    const discEl = document.getElementById('f-hardcover_discount_price');
    const badgeEl = document.getElementById('liveHardcoverDiscountBadge');

    const price = priceEl ? parseFloat(priceEl.value) || 0 : 0;
    const discount = discEl ? parseFloat(discEl.value) || 0 : 0;

    if (!badgeEl) return;

    if (price > 0 && discount > 0) {
        if (discount < price) {
            const savings = price - discount;
            const percent = Math.round((savings / price) * 100);
            badgeEl.className = 'mt-1 small fw-semibold text-success';
            badgeEl.innerHTML = `<i class="fas fa-tags me-1"></i> হার্ডকভারে ${percent}% ছাড়! গ্রাহক বাঁচাবে ৳${savings.toLocaleString('bn-BD')}`;
        } else if (discount === price) {
            badgeEl.className = 'mt-1 small fw-semibold text-muted';
            badgeEl.innerHTML = `কোনো ছাড় প্রযোজ্য নয়।`;
        } else {
            badgeEl.className = 'mt-1 small fw-semibold text-danger';
            badgeEl.innerHTML = `<i class="fas fa-triangle-exclamation me-1"></i> হার্ডকভার ছাড়ের মূল্য মূল দামের চেয়ে বেশি!`;
        }
    } else {
        badgeEl.innerHTML = '';
    }

    updateLiveMockupCard();
}

// Live Mockup Card Update
function updateLiveMockupCard() {
    const titleEl = document.getElementById('f-title');
    const authorSelect = document.getElementById('f-author_link_id');
    const authorCustom = document.getElementById('f-author_name');
    const priceEl = document.getElementById('f-price');
    const discEl = document.getElementById('f-discount_price');

    const mockTitle = document.getElementById('mockupTitle');
    const mockAuthor = document.getElementById('mockupAuthor');
    const mockFinal = document.getElementById('mockupFinalPrice');
    const mockOriginal = document.getElementById('mockupOriginalPrice');
    const mockBadge = document.getElementById('mockupDiscountBadge');

    const mockSubtitle = document.getElementById('mockupSubtitle');
    const mockCatBadge = document.getElementById('mockupCategoryBadge');

    if (!mockTitle) return;

    // Title
    const titleVal = titleEl ? titleEl.value.trim() : '';
    mockTitle.textContent = titleVal || 'শিরোনাম';

    // Subtitle
    const subEl = document.getElementById('f-subtitle');
    if (mockSubtitle) {
        mockSubtitle.textContent = subEl ? subEl.value.trim() : '';
    }

    // Category
    const catSelect = document.getElementById('f-category_id');
    if (mockCatBadge && catSelect && catSelect.selectedIndex > 0) {
        mockCatBadge.textContent = catSelect.options[catSelect.selectedIndex].text;
    }

    // Author
    let authorVal = '';
    const authorIdSelect = document.getElementById('f-author_id');
    const dirRadio = document.getElementById('author-mode-directory');
    if (dirRadio && dirRadio.checked && authorSelect && authorSelect.selectedIndex > 0) {
        authorVal = authorSelect.options[authorSelect.selectedIndex].text;
    } else if (authorIdSelect && authorIdSelect.selectedIndex > 0) {
        authorVal = authorIdSelect.options[authorIdSelect.selectedIndex].text;
    } else if (authorCustom) {
        authorVal = authorCustom.value.trim();
    }
    if (mockAuthor) {
        mockAuthor.textContent = authorVal || 'লেখকের নাম';
    }

    // Pricing & Format Badge calculation (prioritize selected cover type)
    if (mockFinal) {
        const hcPriceEl = document.getElementById('f-hardcover_price');
        const hcDiscEl = document.getElementById('f-hardcover_discount_price');
        const hcPrice = hcPriceEl ? parseFloat(hcPriceEl.value) || 0 : 0;
        const hcDisc = hcDiscEl ? parseFloat(hcDiscEl.value) || 0 : 0;

        const pbPrice = priceEl ? parseFloat(priceEl.value) || 0 : 0;
        const pbDisc = discEl ? parseFloat(discEl.value) || 0 : 0;

        const selectedCoverRadio = document.querySelector('input[name="cover_type"]:checked');
        const coverType = selectedCoverRadio ? selectedCoverRadio.value : 'paperback';
        const mockFmtBadge = document.getElementById('mockupFormatBadge');

        let displayPrice = 0;
        let displayOrig = 0;
        let formatLabel = 'পেপারব্যাক';

        if (coverType === 'hardcover') {
            formatLabel = 'হার্ডকভার';
            if (hcPrice > 0) {
                displayOrig = hcPrice;
                displayPrice = (hcDisc > 0 && hcDisc < hcPrice) ? hcDisc : hcPrice;
            } else if (pbPrice > 0) {
                displayOrig = pbPrice;
                displayPrice = (pbDisc > 0 && pbDisc < pbPrice) ? pbDisc : pbPrice;
            }
        } else if (coverType === 'both') {
            formatLabel = 'উভয় সংস্করণ';
            if (hcPrice > 0) {
                displayOrig = hcPrice;
                displayPrice = (hcDisc > 0 && hcDisc < hcPrice) ? hcDisc : hcPrice;
            } else if (pbPrice > 0) {
                displayOrig = pbPrice;
                displayPrice = (pbDisc > 0 && pbDisc < pbPrice) ? pbDisc : pbPrice;
            }
        } else {
            // paperback
            formatLabel = 'পেপারব্যাক';
            if (pbPrice > 0) {
                displayOrig = pbPrice;
                displayPrice = (pbDisc > 0 && pbDisc < pbPrice) ? pbDisc : pbPrice;
            } else if (hcPrice > 0) {
                displayOrig = hcPrice;
                displayPrice = (hcDisc > 0 && hcDisc < hcPrice) ? hcDisc : hcPrice;
            }
        }

        if (mockFmtBadge) {
            mockFmtBadge.textContent = formatLabel;
        }

        if (displayPrice > 0 && displayOrig > displayPrice) {
            mockFinal.textContent = '৳' + displayPrice.toLocaleString('bn-BD');
            if (mockOriginal) {
                mockOriginal.textContent = '৳' + displayOrig.toLocaleString('bn-BD');
                mockOriginal.classList.remove('d-none');
            }
            const percent = Math.round(((displayOrig - displayPrice) / displayOrig) * 100);
            if (mockBadge) {
                mockBadge.textContent = '-' + percent + '%';
                mockBadge.classList.remove('d-none');
            }
        } else if (displayPrice > 0) {
            mockFinal.textContent = '৳' + displayPrice.toLocaleString('bn-BD');
            if (mockOriginal) mockOriginal.classList.add('d-none');
            if (mockBadge) mockBadge.classList.add('d-none');
        } else {
            mockFinal.textContent = '৳০';
            if (mockOriginal) mockOriginal.classList.add('d-none');
            if (mockBadge) mockBadge.classList.add('d-none');
        }
    }
}

// Drag and drop events for modern dropzone
function handleDropzoneDragOver(e, dropzoneEl) {
    e.preventDefault();
    e.stopPropagation();
    dropzoneEl.classList.add('dragover');
}

function handleDropzoneDragLeave(e, dropzoneEl) {
    e.preventDefault();
    e.stopPropagation();
    dropzoneEl.classList.remove('dragover');
}

function handleDropzoneDrop(e, dropzoneEl, inputId) {
    e.preventDefault();
    e.stopPropagation();
    dropzoneEl.classList.remove('dragover');
    
    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        const input = document.getElementById(inputId);
        if (input) {
            input.files = e.dataTransfer.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
}

// Generic Word Counter for summary (400), description (400), author_bio (300)
function updateGenericWordCount(textarea, maxWords, countId, badgeId, barId, warningId) {
    if (!textarea) return;

    const text = textarea.value.trim();
    const words = text ? text.split(/\s+/).filter(w => w.length > 0) : [];
    const count = words.length;

    const countEl = document.getElementById(countId);
    const badgeEl = document.getElementById(badgeId);
    const progressEl = document.getElementById(barId);
    const warningEl = document.getElementById(warningId);

    if (countEl) countEl.textContent = count.toLocaleString('bn-BD');

    const pct = Math.min(100, Math.round((count / maxWords) * 100));
    if (progressEl) {
        progressEl.style.width = pct + '%';
        if (count > maxWords) {
            progressEl.style.backgroundColor = '#dc2626';
        } else if (count > (maxWords * 0.85)) {
            progressEl.style.backgroundColor = '#d97706';
        } else {
            progressEl.style.backgroundColor = '#10b981';
        }
    }

    if (badgeEl) {
        if (count > maxWords) {
            badgeEl.className = 'word-counter-badge danger';
        } else if (count > (maxWords * 0.85)) {
            badgeEl.className = 'word-counter-badge warn';
        } else {
            badgeEl.className = 'word-counter-badge safe';
        }
    }

    if (warningEl) {
        if (count > maxWords) {
            warningEl.classList.remove('d-none');
            warningEl.innerHTML = `<i class="fas fa-triangle-exclamation me-1"></i> শব্দসীমা অতিক্রম হয়েছে! (${count - maxWords} শব্দ বেশি)`;
        } else {
            warningEl.classList.add('d-none');
            warningEl.innerHTML = '';
        }
    }
}

function updateAuthorBioWordCount(textarea) {
    updateGenericWordCount(textarea || document.getElementById('f-author_bio'), 300, 'authorBioWordCount', 'authorBioWordBadge', 'authorBioProgressBar', 'authorBioWarning');
}

function updateSummaryWordCount(textarea) {
    updateGenericWordCount(textarea || document.getElementById('f-summary'), 400, 'summaryWordCount', 'summaryWordBadge', 'summaryProgressBar', 'summaryWarning');
}

function updateDescriptionWordCount(textarea) {
    updateGenericWordCount(textarea || document.getElementById('f-description'), 400, 'descriptionWordCount', 'descriptionWordBadge', 'descriptionProgressBar', 'descriptionWarning');
}

// Preview file input
function previewAdminFileInput(input, containerId) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const container = document.getElementById(containerId);
    if (!container) return;

    const name = input.name;
    const img = document.getElementById('preview-img-' + name);
    const fname = document.getElementById('preview-filename-' + name);
    const fsize = document.getElementById('preview-filesize-' + name);
    const mockupImg = document.getElementById('mockupCoverImg');

    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (img) img.src = e.target.result;
            if (mockupImg && (name === 'cover_image' || name === 'image')) mockupImg.src = e.target.result;
            if (fname) fname.textContent = file.name;
            if (fsize) fsize.textContent = (file.size / 1024).toFixed(1) + ' KB | ' + file.type;
            container.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    } else {
        if (img) img.src = '';
        if (fname) fname.textContent = file.name;
        if (fsize) fsize.textContent = (file.size / 1024).toFixed(1) + ' KB | ' + (file.type || 'PDF Document');
        container.classList.remove('d-none');
    }
}

// WYSIWYG Rich Text Formatting Engine
function formatDoc(cmd, value, targetTextareaId) {
    const fieldName = targetTextareaId.replace('f-', '');
    const editorDiv = document.getElementById('editable-' + fieldName);
    if (editorDiv) {
        editorDiv.focus();
        document.execCommand(cmd, false, value);
        syncEditorToTextarea(fieldName);
    }
}

function formatPoetryMode(targetTextareaId) {
    const fieldName = targetTextareaId.replace('f-', '');
    const editorDiv = document.getElementById('editable-' + fieldName);
    if (!editorDiv) return;

    editorDiv.focus();
    const sel = window.getSelection();
    let selectedText = sel ? sel.toString() : '';
    
    if (!selectedText) {
        selectedText = editorDiv.innerText || editorDiv.textContent;
    }

    if (!selectedText || !selectedText.trim()) {
        alert('অনুগ্রহ করে কবিতার লাইনগুলো সিলেক্ট করুন বা বক্সে পেস্ট করুন।');
        return;
    }

    // Format into poetry verses / stanzas with linebreaks
    const stanzas = selectedText.trim().split(/\r\n\r\n|\n\n+/);
    const formattedHtml = stanzas.map(stanza => {
        const lines = stanza.split(/\r\n|\n|\r/).map(line => {
            const temp = document.createElement('div');
            temp.textContent = line.trim();
            return temp.innerHTML;
        }).join('<br>');
        return `<p class="poetry-verse" style="line-height: 2.1; margin-bottom: 1.5rem; font-family: inherit;">${lines}</p>`;
    }).join('');

    if (sel && sel.rangeCount > 0 && sel.toString()) {
        document.execCommand('insertHTML', false, formattedHtml);
    } else {
        editorDiv.innerHTML = formattedHtml;
    }
    syncEditorToTextarea(fieldName);
}

function insertLinkPrompt(targetTextareaId) {
    const url = prompt("লিংক ইউআরএল (URL) লিখুন:", "https://");
    if (url && url !== "https://") {
        formatDoc('createLink', url, targetTextareaId);
    }
}

function insertImagePrompt(targetTextareaId) {
    const url = prompt("ছবির সরাসরি লিংক (Image URL) দিন:", "https://");
    if (url && url !== "https://") {
        formatDoc('insertImage', url, targetTextareaId);
    }
}

function syncEditorToTextarea(fieldName) {
    const editorDiv = document.getElementById('editable-' + fieldName);
    const textarea = document.getElementById('f-' + fieldName);
    if (editorDiv && textarea) {
        textarea.value = editorDiv.innerHTML;
    }
}

// Global submit sync and poetry-preserving paste for all rich editor fields
document.addEventListener('DOMContentLoaded', function() {
    // Smart paste to preserve poem lines without turning into continuous prose
    document.querySelectorAll('.rich-editor-content').forEach(function(editor) {
        editor.addEventListener('paste', function(e) {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text/plain');
            if (!text) return;
            
            const blocks = text.split(/\r\n\r\n|\n\n+/);
            const formattedHtml = blocks.map(block => {
                const lines = block.split(/\r\n|\n|\r/).map(line => {
                    const temp = document.createElement('div');
                    temp.textContent = line;
                    return temp.innerHTML;
                }).join('<br>');
                return `<p style="line-height: 2.0; margin-bottom: 1.25rem;">${lines}</p>`;
            }).join('');

            document.execCommand('insertHTML', false, formattedHtml);
            const name = editor.id.replace('editable-', '');
            syncEditorToTextarea(name);
        });
    });

    const form = document.getElementById('contentMainForm');
    if (form) {
        form.addEventListener('submit', function() {
            document.querySelectorAll('.rich-editor-content').forEach(function(editor) {
                const name = editor.id.replace('editable-', '');
                const textarea = document.getElementById('f-' + name);
                if (textarea) {
                    textarea.value = editor.innerHTML;
                }
            });
            syncActiveCostPrice();
        });
    }

    // Initialize cover format requirement, discount calculations & profit margins
    updateCoverTypeRequirement();
    
    // Auto-calculate initial percentage badges if prices already exist (e.g. edit mode)
    const hcPriceInit = parseFloat(document.getElementById('f-hardcover_price')?.value) || 0;
    const hcDiscInit = parseFloat(document.getElementById('f-hardcover_discount_price')?.value) || 0;
    const hcCostInit = parseFloat(document.getElementById('f-hardcover_cost_price_display')?.value) || 0;
    if (hcPriceInit > 0 && hcDiscInit > 0 && hcDiscInit < hcPriceInit) {
        const hcPctEl = document.getElementById('f-hardcover_discount_percent');
        if (hcPctEl && !hcPctEl.value) hcPctEl.value = Math.round(((hcPriceInit - hcDiscInit) / hcPriceInit) * 100);
    }
    if (hcPriceInit > 0 && hcCostInit > 0 && hcCostInit < hcPriceInit) {
        const hcCostPctEl = document.getElementById('f-hardcover_cost_discount_percent');
        if (hcCostPctEl && !hcCostPctEl.value) hcCostPctEl.value = Math.round(((hcPriceInit - hcCostInit) / hcPriceInit) * 100);
    }

    const pbPriceInit = parseFloat(document.getElementById('f-price')?.value) || 0;
    const pbDiscInit = parseFloat(document.getElementById('f-discount_price')?.value) || 0;
    const pbCostInit = parseFloat(document.getElementById('f-cost_price')?.value) || 0;
    if (pbPriceInit > 0 && pbDiscInit > 0 && pbDiscInit < pbPriceInit) {
        const pbPctEl = document.getElementById('f-discount_percent');
        if (pbPctEl && !pbPctEl.value) pbPctEl.value = Math.round(((pbPriceInit - pbDiscInit) / pbPriceInit) * 100);
    }
    if (pbPriceInit > 0 && pbCostInit > 0 && pbCostInit < pbPriceInit) {
        const pbCostPctEl = document.getElementById('f-cost_discount_percent');
        if (pbCostPctEl && !pbCostPctEl.value) pbCostPctEl.value = Math.round(((pbPriceInit - pbCostInit) / pbPriceInit) * 100);
    }

    calculateLiveDiscount();
    calculateLiveHardcoverDiscount();
    calculateLiveHardcoverProfit();
    calculateLivePaperbackProfit();
    updateLiveMockupCard();
});

// AJAX Quick Category Creator
function handleQuickCategorySubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('quickCatBtn');
    const alertBox = document.getElementById('quickCatAlert');
    const nameInput = document.getElementById('quick_cat_name');
    const parentSelect = document.getElementById('quick_cat_parent_id');
    const descInput = document.getElementById('quick_cat_description');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> সংরক্ষণ হচ্ছে...';
    alertBox.innerHTML = '';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch("{{ route('admin.quick.category') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            name: nameInput.value,
            parent_id: parentSelect.value || null,
            description: descInput.value || null
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.item) {
            // Append to main category dropdown and select
            const mainCatSelect = document.getElementById('f-category_id');
            if (mainCatSelect) {
                const opt = new Option(data.item.display_name, data.item.id, true, true);
                mainCatSelect.add(opt);
                mainCatSelect.value = data.item.id;
            }
            // Append to modal parent select
            if (parentSelect) {
                const optModal = new Option(data.item.name, data.item.id);
                parentSelect.add(optModal);
            }

            // Close modal
            const modalEl = document.getElementById('quickAddCategoryModal');
            bootstrap.Modal.getInstance(modalEl)?.hide();
            nameInput.value = '';
            descInput.value = '';

            alert('ক্যাটাগরি সফলভাবে তৈরি ও নির্বাচিত হয়েছে!');
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">${data.message || 'ত্রুটি হয়েছে'}</div>`;
        }
    })
    .catch(err => {
        alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">সার্ভার এরর হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।</div>`;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> ক্যাটাগরি সংরক্ষণ করুন';
    });
}

// AJAX Quick Blog Category Creator
function handleQuickBlogCategorySubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('quickBlogCatBtn');
    const alertBox = document.getElementById('quickBlogCatAlert');
    const nameInput = document.getElementById('quick_blog_cat_name');
    const iconInput = document.getElementById('quick_blog_cat_icon');
    const descInput = document.getElementById('quick_blog_cat_description');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> সংরক্ষণ হচ্ছে...';
    alertBox.innerHTML = '';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch("{{ route('admin.quick.blog-category') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            name: nameInput.value,
            icon: iconInput.value || 'feather-pointed',
            description: descInput.value || null
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.item) {
            // Append to main category dropdown and select
            const mainCatSelect = document.getElementById('f-category_id');
            if (mainCatSelect) {
                const opt = new Option(data.item.display_name, data.item.id, true, true);
                mainCatSelect.add(opt);
                mainCatSelect.value = data.item.id;
            }

            // Close modal
            const modalEl = document.getElementById('quickAddBlogCategoryModal');
            bootstrap.Modal.getInstance(modalEl)?.hide();
            nameInput.value = '';
            descInput.value = '';

            updateLiveMockupCard();
            alert('ব্লগ ক্যাটাগরি সফলভাবে তৈরি ও নির্বাচিত হয়েছে!');
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">${data.message || 'ত্রুটি হয়েছে'}</div>`;
        }
    })
    .catch(err => {
        alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">সার্ভার এরর হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।</div>`;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> ব্লগ ক্যাটাগরি সংরক্ষণ করুন';
    });
}

// AJAX Quick Publisher Creator
function handleQuickPublisherSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('quickPubBtn');
    const alertBox = document.getElementById('quickPubAlert');
    const nameInput = document.getElementById('quick_pub_name');
    const phoneInput = document.getElementById('quick_pub_phone');
    const addressInput = document.getElementById('quick_pub_address');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> সংরক্ষণ হচ্ছে...';
    alertBox.innerHTML = '';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch("{{ route('admin.quick.publisher') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            name: nameInput.value,
            phone: phoneInput.value || null,
            address: addressInput.value || null
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.item) {
            const mainPubSelect = document.getElementById('f-publisher_id');
            if (mainPubSelect) {
                const opt = new Option(data.item.name, data.item.id, true, true);
                mainPubSelect.add(opt);
                mainPubSelect.value = data.item.id;
            }

            const modalEl = document.getElementById('quickAddPublisherModal');
            bootstrap.Modal.getInstance(modalEl)?.hide();
            nameInput.value = '';
            phoneInput.value = '';
            addressInput.value = '';

            alert('প্রকাশনী সফলভাবে যুক্ত ও নির্বাচিত হয়েছে!');
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">${data.message || 'ত্রুটি হয়েছে'}</div>`;
        }
    })
    .catch(err => {
        alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">সার্ভার এরর হয়েছে।</div>`;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> প্রকাশনী সংরক্ষণ করুন';
    });
}

// AJAX Quick Author Creator
function handleQuickAuthorSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('quickAuthBtn');
    const alertBox = document.getElementById('quickAuthAlert');
    const nameInput = document.getElementById('quick_auth_name');
    const phoneInput = document.getElementById('quick_auth_phone');
    const bioInput = document.getElementById('quick_auth_bio');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> সংরক্ষণ হচ্ছে...';
    alertBox.innerHTML = '';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch("{{ route('admin.quick.author') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            name: nameInput.value,
            phone: phoneInput.value || null,
            bio: bioInput.value || null
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.item) {
            const blogAuthSelect = document.getElementById('f-author_id');
            if (blogAuthSelect) {
                const opt = new Option(data.item.name, data.item.id, true, true);
                blogAuthSelect.add(opt);
                blogAuthSelect.value = data.item.id;
            }

            const mainAuthSelect = document.getElementById('f-author_link_id');
            if (mainAuthSelect) {
                const opt = new Option(data.item.name, data.item.id, true, true);
                mainAuthSelect.add(opt);
                mainAuthSelect.value = data.item.id;
            }

            // Also switch to directory mode
            const dirRadio = document.getElementById('author-mode-directory');
            if (dirRadio) {
                dirRadio.checked = true;
                dirRadio.dispatchEvent(new Event('change'));
            }

            const modalEl = document.getElementById('quickAddAuthorModal');
            bootstrap.Modal.getInstance(modalEl)?.hide();
            nameInput.value = '';
            phoneInput.value = '';
            bioInput.value = '';

            updateLiveMockupCard();
            alert('লেখক সফলভাবে যুক্ত ও নির্বাচিত হয়েছে!');
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">${data.message || 'ত্রুটি হয়েছে'}</div>`;
        }
    })
    .catch(err => {
        alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">সার্ভার এরর হয়েছে।</div>`;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> লেখক সংরক্ষণ করুন';
    });
}

// Dynamic Webzine Table of Contents Row Management
const authorOptionsJson = @json($lookups['authors'] ?? []);

function addWebzineTocRow() {
    const tbody = document.getElementById('webzineTocBody');
    if (!tbody) return;

    const rows = tbody.querySelectorAll('.webzine-toc-row');
    const newIdx = rows.length;
    const nextOrder = newIdx + 1;

    let authorOptionsHtml = '<option value="">— লেখক নির্বাচন করুন (ঐচ্ছিক) —</option>';
    for (const [aId, aName] of Object.entries(authorOptionsJson)) {
        authorOptionsHtml += `<option value="${aId}">${aName}</option>`;
    }

    const tr = document.createElement('tr');
    tr.className = 'webzine-toc-row';
    tr.innerHTML = `
        <td class="text-center fw-bold text-muted row-number">${newIdx + 1}</td>
        <td>
            <input type="hidden" name="toc_articles[${newIdx}][order]" class="input-order" value="${nextOrder}">
            <input type="text" name="toc_articles[${newIdx}][title]" class="form-control form-control-sm" placeholder="যেমন: নতুন প্রবন্ধ / গল্প..." required>
        </td>
        <td>
            <select name="toc_articles[${newIdx}][author_id]" class="form-select form-select-sm">
                ${authorOptionsHtml}
            </select>
        </td>
        <td>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light text-muted">পৃষ্ঠা</span>
                <input type="number" name="toc_articles[${newIdx}][page_number]" class="form-control form-control-sm text-center fw-bold" value="${nextOrder}" min="1" placeholder="1" required>
            </div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" onclick="removeWebzineTocRow(this)" title="মুছুন">
                <i class="fas fa-trash-alt"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    renumberWebzineTocRows();
}

function removeWebzineTocRow(btn) {
    const tbody = document.getElementById('webzineTocBody');
    const row = btn.closest('.webzine-toc-row');
    if (row && tbody) {
        if (tbody.querySelectorAll('.webzine-toc-row').length > 1) {
            row.remove();
            renumberWebzineTocRows();
        } else {
            // Just clear inputs
            row.querySelector('input[type="text"]').value = '';
            row.querySelector('select').value = '';
        }
    }
}

function renumberWebzineTocRows() {
    const tbody = document.getElementById('webzineTocBody');
    if (!tbody) return;
    const rows = tbody.querySelectorAll('.webzine-toc-row');
    rows.forEach((r, idx) => {
        const numCell = r.querySelector('.row-number');
        if (numCell) numCell.textContent = idx + 1;
        const orderInput = r.querySelector('.input-order');
        if (orderInput) orderInput.value = idx + 1;
    });
}
</script>
<script src="{{ asset('js/spellchecker.js') }}"></script>
@endpush

@endsection
