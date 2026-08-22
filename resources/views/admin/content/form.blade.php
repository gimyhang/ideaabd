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
    $heading = $editing ? "Edit {$spec['label']}" : "New {$spec['label']}";
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
    <li class="breadcrumb-item active" aria-current="page">{{ $editing ? 'Edit' : 'Create' }}</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        @if ($spec['key'] !== 'books')
            <button type="submit" form="contentMainForm" class="btn btn-success btn-sm rounded-pill px-3.5 fw-bold shadow-xs">
                <i class="fas fa-circle-check me-1"></i> {{ $editing ? 'Save Changes' : 'Publish & Save' }}
            </button>
        @endif
        @if ($editing)
            @if ($spec['key'] === 'webzines')
                <a href="{{ route('webzine.read', $record->slug ?: $record->id) }}" target="_blank" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-xs">
                    <i class="fas fa-book-open me-1"></i> Reader View
                </a>
                <a href="{{ route('webzine.show', $record->slug ?: $record->id) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs">
                    <i class="fas fa-arrow-up-right-from-square me-1"></i> View on Site
                </a>
            @elseif ($spec['key'] === 'ebooks')
                <a href="{{ route('ebook.read', $record->slug ?: $record->id) }}" target="_blank" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-xs">
                    <i class="fas fa-book-open me-1"></i> Reader View
                </a>
                <a href="{{ route('ebook.show', $record->slug ?: $record->id) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs">
                    <i class="fas fa-arrow-up-right-from-square me-1"></i> View on Site
                </a>
            @elseif ($spec['key'] === 'books')
                <a href="{{ route('book.show', $record->slug ?: $record->id) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs">
                    <i class="fas fa-arrow-up-right-from-square me-1"></i> View on Site
                </a>
            @elseif ($spec['key'] === 'blog')
                <a href="{{ route('blog.show', $record->slug ?: $record->id) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs">
                    <i class="fas fa-arrow-up-right-from-square me-1"></i> View on Site
                </a>
            @endif
        @endif
        <a href="{{ route($spec['listRoute']) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
@endsection

@section('content')

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="row g-4" id="contentMainForm">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

@if ($spec['key'] === 'books')
    @include('admin.content.books_form')
@else
    <div class="col-12 col-lg-8">
        <div class="adm-card p-3 p-md-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h2 class="h6 fw-bold mb-0 text-dark">
                    <i class="fas fa-{{ $spec['icon'] }} me-1.5 text-primary"></i> {{ $spec['label'] }} Details & Information
                </h2>
                <span class="badge bg-light text-muted border small">Fields marked * are required</span>
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
                                    <span class="p-1.5 bg-primary-subtle text-primary rounded-circle small"><i class="fas fa-book-bookmark"></i></span> ১. প্রাথমিক তথ্য ও লেখক/অবদানকারী (Basic Information & Contributors)
                                </div>
                            </div>
                        @elseif ($name === 'published_at')
                            <div class="col-12 mt-3 mb-1">
                                <div class="d-flex align-items-center gap-2 pb-1.5 border-bottom text-dark fw-bold" style="font-size: 0.95rem;">
                                    <span class="p-1.5 bg-warning-subtle text-warning rounded-circle small"><i class="fas fa-calendar-check"></i></span> ৩. প্রকাশনা, অর্ডার টাইপ ও স্টক (Publication, Order & Stock)
                                </div>
                            </div>
                        @elseif ($name === 'book_size')
                            <div class="col-12 mt-3 mb-1">
                                <div class="d-flex align-items-center gap-2 pb-1.5 border-bottom text-dark fw-bold" style="font-size: 0.95rem;">
                                    <span class="p-1.5 bg-secondary-subtle text-secondary rounded-circle small"><i class="fas fa-ruler-combined"></i></span> ৪. বইয়ের মাপ ও শারীরিক বিবরণ (Physical Specifications)
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
                                    <span class="p-1.5 bg-purple-subtle text-purple rounded-circle small" style="background-color: #f3e8ff; color: #7e22ce;"><i class="fas fa-align-left"></i></span> ৬. বইয়ের সংক্ষেপ (Product Summary — সর্বোচ্চ ১০০০ শব্দ)
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

                        {{-- ══ AUTHOR ROLE GROUP (প্রধান লেখক নির্বাচন — ড্রপডাউন) ══ --}}
                        @elseif ($field['type'] === 'author_role_group')
                            @php
                                $curRole       = old('author_role',  $editing ? ($record->author_role  ?? 'author') : 'author');
                                $curAuthorId   = old('author_link_id', $editing ? ($record->author_link_id ?? '') : '');
                                $curAuthorName = old('author_name',  $editing ? ($record->author_name  ?? '') : '');
                                $authorOptions = $lookups['authors'] ?? [];
                            @endphp

                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                                    <label class="form-label small fw-bold text-dark mb-0">
                                        <i class="fas fa-pen-nib text-primary me-1"></i> প্রধান লেখক নির্বাচন (Author Selection) <span class="text-danger">*</span>
                                    </label>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2.5 rounded-pill fw-semibold" 
                                            data-bs-toggle="modal" data-bs-target="#quickAddAuthorModal" style="font-size: 11.5px;">
                                        <i class="fas fa-plus-circle me-1"></i>+ Add New Author
                                    </button>
                                </div>

                                {{-- Direct Clean Author Dropdown --}}
                                <div class="mb-2">
                                    <select name="author_link_id" id="f-author_link_id"
                                            class="form-select @error('author_link_id') is-invalid @enderror"
                                            onchange="onAuthorDirectoryChange(this)">
                                        <option value="">— Select Author from Directory (Total: {{ count($authorOptions) }}) —</option>
                                        @foreach ($authorOptions as $aId => $aName)
                                            <option value="{{ $aId }}" @selected((string)$curAuthorId === (string)$aId || (!$curAuthorId && $curAuthorName === $aName))>
                                                {{ $aName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text mt-1 text-muted" style="font-size: 11px;">
                                        <i class="fas fa-info-circle text-primary me-1"></i>তালিকা থেকে লেখক নির্বাচন করুন। লেখক তালিকায় না থাকলে <strong>“+ Add New Author”</strong> ক্লিক করুন।
                                    </div>
                                    @error('author_link_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                {{-- Unlisted Author Name Fallback Input --}}
                                <div class="mt-2 pt-2 border-top">
                                    <label for="f-author_name" class="form-label small fw-semibold text-dark mb-1" style="font-size: 11.5px;">
                                        <i class="fas fa-keyboard text-muted me-1"></i> অথবা কাস্টম লেখক নাম (যদি তালিকায় না থাকে):
                                    </label>
                                    <input type="text" name="author_name" id="f-author_name"
                                           value="{{ $curAuthorName }}"
                                           placeholder="Enter unlisted author name..."
                                           class="form-control form-control-sm @error('author_name') is-invalid @enderror"
                                           oninput="updateLiveMockupCard()">
                                    @error('author_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <input type="hidden" name="author_role" value="author">
                            </div>            @error('author_role')<div class="invalid-feedback d-block mt-1">{{ $message }}</div>@enderror
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
                                    <i class="fas fa-plus-circle me-1"></i>+ Add Category
                                </button>
                            </div>
                            
                            <select id="f-category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" onchange="updateLiveMockupCard()">
                                <option value="">— Select Category —</option>
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
                                            <i class="fas fa-folder-tree me-1 text-primary"></i>Or write new Sub-Category name:
                                        </label>
                                    </div>
                                    <input type="text" id="f-sub_category_name" name="sub_category_name" 
                                           class="form-control form-control-sm" placeholder="e.g. Historical Fiction / Poetry / Science">
                                    <div class="form-text" style="font-size: 11px;">Select a parent category above and enter a sub-category here to auto-create it.</div>
                                </div>
                            @else
                                <div class="mt-2 p-2 bg-light rounded border">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label for="f-new_blog_category_name" class="form-label small fw-semibold text-dark mb-0" style="font-size: 11.5px;">
                                            <i class="fas fa-feather-pointed me-1 text-primary"></i>Or enter new Blog Category name:
                                        </label>
                                    </div>
                                    <input type="text" id="f-new_blog_category_name" name="new_blog_category_name" 
                                           class="form-control form-control-sm" placeholder="e.g. Poetry / Essays / Short Stories / Translation">
                                    <div class="form-text" style="font-size: 11px;">If not in the list, write here and it will be created automatically.</div>
                                </div>
                            @endif
                            @error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                        {{-- ══ BLOG AUTHOR & CONTRIBUTORS SYSTEM ══ --}}
                        @elseif ($name === 'author_id')
                            @php
                                $currentOwnerName = old('owner_name', $editing ? ($record->owner_name ?? '') : '');
                                $selectedAuthorKey = old('author_id');

                                if ($selectedAuthorKey === null && $editing && $record) {
                                    if (!empty($record->owner_name) && Schema::hasTable('authors')) {
                                        $matchedDir = DB::table('authors')->where('name', $record->owner_name)->whereNull('deleted_at')->first();
                                        if ($matchedDir) {
                                            $selectedAuthorKey = 'author_' . $matchedDir->id;
                                        }
                                    }
                                    if (empty($selectedAuthorKey) && !empty($record->author_id)) {
                                        $selectedAuthorKey = 'user_' . $record->author_id;
                                    }
                                }

                                $existingAuthorUser = $editing && $record && $record->author ? $record->author : (!empty($record->author_id) ? \App\Models\User::find($record->author_id) : null);
                                $displayAuthorName = $currentOwnerName ?: ($existingAuthorUser ? $existingAuthorUser->name : ($record->author_name ?? 'Editorial Department'));
                                $authorOptions = $lookups['authors'] ?? [];
                            @endphp

                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                    <div>
                                        <label class="form-label small fw-bold text-dark mb-0">
                                            <i class="fas fa-pen-fancy text-primary me-1"></i> Primary Blog Author
                                        </label>
                                        <div class="text-muted mt-0.5" style="font-size: 11.5px;">
                                            Current Author: <strong class="text-primary" id="currentAuthorBadgeText">{{ $displayAuthorName }}</strong>
                                            @if($existingAuthorUser)
                                                <span class="badge bg-primary-subtle text-primary border ms-1">{{ $existingAuthorUser->role === 'author' ? 'Registered Author' : $existingAuthorUser->role }}</span>
                                            @elseif($currentOwnerName)
                                                <span class="badge bg-secondary-subtle text-secondary border ms-1">Custom Contributor</span>
                                            @endif
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2.5 rounded-pill fw-semibold"
                                            data-bs-toggle="modal" data-bs-target="#quickAddAuthorModal" style="font-size: 11.5px;">
                                        <i class="fas fa-plus-circle me-1"></i>+ Add New Author
                                    </button>
                                </div>

                                {{-- Author Dropdown (Users & Directory Authors) --}}
                                <div class="mb-2">
                                    <label for="f-author_id" class="form-label small fw-semibold text-dark mb-1" style="font-size: 12px;">
                                        Select Author from Directory:
                                    </label>
                                    <select id="f-author_id" name="author_id" class="form-select @error('author_id') is-invalid @enderror" onchange="onBlogAuthorDropdownChange(this)">
                                        <option value="">— Select Author (Total: {{ count($authorOptions) }}) —</option>
                                        @foreach ($authorOptions as $aId => $aName)
                                            <option value="{{ $aId }}" @selected((string) $selectedAuthorKey === (string) $aId)>
                                                {{ $aName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('author_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                {{-- Custom Author Display Name --}}
                                <div>
                                    <label for="f-owner_name" class="form-label small fw-semibold text-dark mb-1" style="font-size: 12px;">
                                        <i class="fas fa-signature text-secondary me-1"></i> Display Author Name (Byline credit on website):
                                    </label>
                                    <input type="text" id="f-owner_name" name="owner_name" 
                                           value="{{ $currentOwnerName }}"
                                           class="form-control form-control-sm" 
                                           placeholder="e.g., Kazi Nazrul Islam / Rabindranath Tagore / Humayun Ahmed"
                                           oninput="updateLiveMockupCard()">
                                    <div class="form-text text-muted mt-1" style="font-size: 11px;">
                                        <i class="fas fa-info-circle text-primary me-1"></i> This name will appear on the blog as the primary author credit. If left empty, selected account name is used.
                                    </div>
                                </div>
                            </div>

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
                                    <i class="fas fa-plus-circle me-1"></i>+ Add Publisher
                                </button>
                            </div>

                            <select id="f-publisher_id" name="publisher_id" class="form-select @error('publisher_id') is-invalid @enderror">
                                <option value="">— Select Publisher —</option>
                                @foreach ($pubOptions as $pId => $pName)
                                    <option value="{{ $pId }}" @selected((string) $current === (string) $pId)>
                                        {{ $pName }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="mt-2 p-2 bg-light rounded border">
                                <input type="text" id="f-new_publisher_name" name="new_publisher_name" 
                                       class="form-control form-control-sm" placeholder="Or enter new publisher name...">
                                <div class="form-text" style="font-size: 11px;">If not in the list, write the name here and it will be created automatically.</div>
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
                                <details class="border rounded-3 bg-white shadow-xs p-3.5 mb-2 overflow-hidden" open>
                                    <summary class="fw-bold text-dark cursor-pointer d-flex align-items-center justify-content-between pb-2" style="font-size: 0.95rem; user-select: none;">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="p-1.5 bg-success-subtle text-success rounded-circle small"><i class="fas fa-calculator"></i></span>
                                            <span>২. বাঁধাই, সংস্করণ, মূল্য নির্ধারণ ও ক্রয়-বিক্রয় কমিশন হিসাব (Binding & Pricing Dropdown)</span>
                                        </div>
                                        <span class="badge bg-success-subtle text-success small rounded-pill px-2.5 py-1">
                                            <i class="fas fa-chevron-down me-1"></i> ড্রপডাউন টগল
                                        </span>
                                    </summary>

                                    <div class="pt-3 border-top">
                                        {{-- 1. Binding / Format Switcher --}}
                                        <div class="mb-3 pb-2.5 border-bottom">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                                <label class="form-label fw-bold text-dark mb-0 small">
                                                    <i class="fas fa-layer-group text-primary me-1.5"></i> Cover Binding & Edition Selection <span class="text-danger">*</span>
                                                </label>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 small">
                                                    <i class="fas fa-calculator me-1"></i>Automatic Margin & Profit Calculation
                                                </span>
                                            </div>

                                            <div class="btn-group w-100 flex-wrap" role="group" id="coverTypeToggleGroup">
                                                <input type="radio" class="btn-check" name="cover_type" id="cover_hardcover" value="hardcover" 
                                                       @checked($curCoverType === 'hardcover') onchange="onCoverTypeChange()">
                                                <label class="btn btn-outline-primary py-2 fw-semibold" for="cover_hardcover">
                                                    <i class="fas fa-gem me-1.5 text-warning"></i> Hardcover (Primary)
                                                </label>

                                                <input type="radio" class="btn-check" name="cover_type" id="cover_paperback" value="paperback" 
                                                       @checked($curCoverType === 'paperback') onchange="onCoverTypeChange()">
                                                <label class="btn btn-outline-primary py-2 fw-semibold" for="cover_paperback">
                                                    <i class="fas fa-book-open me-1.5 text-info"></i> Paperback
                                                </label>

                                                <input type="radio" class="btn-check" name="cover_type" id="cover_both" value="both" 
                                                       @checked($curCoverType === 'both') onchange="onCoverTypeChange()">
                                                <label class="btn btn-outline-primary py-2 fw-semibold" for="cover_both">
                                                    <i class="fas fa-layer-group me-1.5 text-success"></i> Both Editions (Hardcover & Paperback)
                                                </label>
                                            </div>
                                        </div>

                                        {{-- 2. Pricing, Discount & Purchase Cost Cards --}}
                                        <div class="row g-3">
                                            {{-- Hardcover Card --}}
                                            <div class="col-12 col-md-6" id="panelHardcoverCard">
                                                <div class="card h-100 border rounded-3 bg-light overflow-hidden">
                                                    <div class="card-header bg-primary text-white py-2 px-3 d-flex align-items-center justify-content-between">
                                                        <span class="fw-bold small"><i class="fas fa-gem me-1.5 text-warning"></i> Hardcover Edition</span>
                                                        <span class="badge bg-white text-primary small px-2 py-0.5 rounded-pill" id="badgeHardcoverStatus">Primary Price</span>
                                                    </div>
                                                    <div class="card-body p-3">
                                                        {{-- Regular Price (MRP) --}}
                                                        <div class="mb-3">
                                                            <label for="f-hardcover_price" class="form-label small fw-bold text-dark mb-1">
                                                                Printed Price / Regular MRP <span class="text-danger" id="reqStarHardcover">*</span>
                                                            </label>
                                                            <div class="input-group">
                                                                <span class="input-group-text bg-white fw-bold text-primary">৳</span>
                                                                <input type="number" step="0.01" min="0" id="f-hardcover_price" name="hardcover_price" 
                                                                       value="{{ $valHardcoverPrice }}" class="form-control fw-bold" placeholder="0.00" 
                                                                       oninput="onHardcoverPriceChange()">
                                                            </div>
                                                            <div class="form-text text-muted" style="font-size: 11px;">Maximum retail price printed on the book.</div>
                                                        </div>

                                                        {{-- Selling Discount Section --}}
                                                        <div class="p-2.5 bg-white rounded-3 border mb-3">
                                                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                                <span class="small fw-bold text-success"><i class="fas fa-tags me-1"></i>Customer Sales Discount:</span>
                                                                <span class="text-muted small" style="font-size: 10.5px;">2-Way Auto Sync</span>
                                                            </div>
                                                            <div class="row g-2">
                                                                <div class="col-6">
                                                                    <label for="f-hardcover_discount_percent" class="form-label small text-muted mb-1" style="font-size: 11px;">Discount (%)</label>
                                                                    <div class="input-group input-group-sm">
                                                                        <input type="number" step="0.5" min="0" max="100" id="f-hardcover_discount_percent" 
                                                                               class="form-control" placeholder="e.g. 25" oninput="onHardcoverDiscountPercentChange()">
                                                                        <span class="input-group-text bg-light fw-bold">%</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <label for="f-hardcover_discount_price" class="form-label small text-muted mb-1" style="font-size: 11px;">Offer Price (৳)</label>
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
                                                                <span class="small fw-bold text-dark"><i class="fas fa-coins me-1 text-warning"></i>Publisher Purchase / Cost:</span>
                                                                <span class="badge bg-warning-subtle text-warning-emphasis small" style="font-size: 10px;">Purchase Commission</span>
                                                            </div>
                                                            <div class="row g-2">
                                                                <div class="col-6">
                                                                    <label for="f-hardcover_cost_discount_percent" class="form-label small text-muted mb-1" style="font-size: 11px;">Purchase Comm. (%)</label>
                                                                    <div class="input-group input-group-sm">
                                                                        <input type="number" step="0.5" min="0" max="100" id="f-hardcover_cost_discount_percent" 
                                                                               class="form-control" placeholder="e.g. 40" oninput="onHardcoverCostDiscountPercentChange()">
                                                                        <span class="input-group-text bg-light fw-bold">%</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <label for="f-hardcover_cost_price_display" class="form-label small text-muted mb-1" style="font-size: 11px;">Cost Price (৳)</label>
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
                                                        <span class="fw-bold small"><i class="fas fa-book-open me-1.5 text-info"></i> Paperback Edition</span>
                                                        <span class="badge bg-white text-secondary small px-2 py-0.5 rounded-pill" id="badgePaperbackStatus">Optional Edition</span>
                                                    </div>
                                                    <div class="card-body p-3">
                                                        {{-- Regular Price (MRP) --}}
                                                        <div class="mb-3">
                                                            <label for="f-price" class="form-label small fw-bold text-dark mb-1">
                                                                Printed Price / Regular MRP <span class="text-danger" id="reqStarPaperback" style="display:none;">*</span>
                                                            </label>
                                                            <div class="input-group">
                                                                <span class="input-group-text bg-white fw-bold text-primary">৳</span>
                                                                <input type="number" step="0.01" min="0" id="f-price" name="price" 
                                                                       value="{{ $valPaperbackPrice }}" class="form-control fw-bold" placeholder="0.00" 
                                                                       oninput="onRegularPriceChange()">
                                                            </div>
                                                            <div class="form-text text-muted" style="font-size: 11px;">Maximum retail price for paperback edition.</div>
                                                        </div>

                                                        {{-- Selling Discount Section --}}
                                                        <div class="p-2.5 bg-white rounded-3 border mb-3">
                                                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                                <span class="small fw-bold text-success"><i class="fas fa-tags me-1"></i>Customer Sales Discount:</span>
                                                                <span class="text-muted small" style="font-size: 10.5px;">2-Way Auto Sync</span>
                                                            </div>
                                                            <div class="row g-2">
                                                                <div class="col-6">
                                                                    <label for="f-discount_percent" class="form-label small text-muted mb-1" style="font-size: 11px;">Discount (%)</label>
                                                                    <div class="input-group input-group-sm">
                                                                        <input type="number" step="0.5" min="0" max="100" id="f-discount_percent" 
                                                                               class="form-control" placeholder="e.g. 25" oninput="onDiscountPercentChange()">
                                                                        <span class="input-group-text bg-light fw-bold">%</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <label for="f-discount_price" class="form-label small text-muted mb-1" style="font-size: 11px;">Offer Price (৳)</label>
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
                                                                <span class="small fw-bold text-dark"><i class="fas fa-coins me-1 text-warning"></i>Publisher Purchase / Cost:</span>
                                                                <span class="badge bg-warning-subtle text-warning-emphasis small" style="font-size: 10px;">Purchase Commission</span>
                                                            </div>
                                                            <div class="row g-2">
                                                                <div class="col-6">
                                                                    <label for="f-cost_discount_percent" class="form-label small text-muted mb-1" style="font-size: 11px;">Purchase Comm. (%)</label>
                                                                    <div class="input-group input-group-sm">
                                                                        <input type="number" step="0.5" min="0" max="100" id="f-cost_discount_percent" 
                                                                               class="form-control" placeholder="e.g. 40" oninput="onPaperbackCostDiscountPercentChange()">
                                                                        <span class="input-group-text bg-light fw-bold">%</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <label for="f-cost_price" class="form-label small text-muted mb-1" style="font-size: 11px;">Cost Price (৳)</label>
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
                                </details>
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
                                        <i class="fas fa-percent me-1 text-primary"></i>Discount Percentage (%):
                                    </label>
                                    <span class="small text-muted" style="font-size: 11px;">Auto Calculate</span>
                                </div>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.5" min="0" max="100" id="f-discount_percent" 
                                           class="form-control" placeholder="e.g. 25" oninput="onDiscountPercentChange()">
                                    <span class="input-group-text bg-white fw-bold">%</span>
                                </div>
                            </div>

                        @elseif ($name === 'discount_price')
                            <label for="f-discount_price" class="form-label small fw-semibold">
                                {{ $field['label'] }} (Sale / Discounted Price)
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

                        {{-- ══ PRE-ORDER FIELDS ══ --}}
                        @elseif ($name === 'pre_order_release_date')
                            <div class="p-2.5 bg-warning-subtle rounded-3 border border-warning">
                                <label for="f-pre_order_release_date" class="form-label small fw-bold text-dark mb-1">
                                    <i class="fas fa-truck-fast text-warning-emphasis me-1"></i> {{ $field['label'] }}
                                </label>
                                <input type="date" id="f-pre_order_release_date" name="pre_order_release_date" value="{{ $current }}"
                                       class="form-control form-control-sm @error('pre_order_release_date') is-invalid @enderror">
                                <div class="form-text text-muted" style="font-size: 11px;">Specify the estimated shipping / release date for pre-orders.</div>
                                @error('pre_order_release_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                        @elseif ($name === 'pre_order_note')
                            <div class="p-2.5 bg-warning-subtle rounded-3 border border-warning">
                                <label for="f-pre_order_note" class="form-label small fw-bold text-dark mb-1">
                                    <i class="fas fa-gift text-warning-emphasis me-1"></i> {{ $field['label'] }}
                                </label>
                                <textarea id="f-pre_order_note" name="pre_order_note" rows="2"
                                          placeholder="{{ $field['placeholder'] ?? 'Special gifts, autograph note, or pre-order bonuses...' }}"
                                          class="form-control form-control-sm @error('pre_order_note') is-invalid @enderror">{{ $current }}</textarea>
                                <div class="form-text text-muted" style="font-size: 11px;">Promotional message or gift note for pre-order buyers.</div>
                                @error('pre_order_note')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                        {{-- ══ SUMMARY WITH 1000 WORDS LIMIT & PROGRESS ══ --}}
                        @elseif ($name === 'summary')
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label for="f-summary" class="form-label small fw-semibold text-dark mb-0">
                                    <i class="fas fa-file-lines text-primary me-1"></i> Product Summary (বইয়ের সংক্ষেপ — ১০০০ শব্দ)
                                </label>
                                <div class="word-counter-badge safe" id="summaryWordBadge">
                                    <i class="fas fa-font me-1"></i> Words: <span id="summaryWordCount">0</span> / 1000
                                </div>
                            </div>
                            <textarea id="f-summary" name="summary" rows="5"
                                      class="form-control @error('summary') is-invalid @enderror"
                                      placeholder="বইয়ের সংক্ষেপ, মূল পটভূমি বা আকর্ষণীয় সারসংক্ষেপ লিখুন (সর্বোচ্চ ১০০০ শব্দ)..."
                                      oninput="updateGenericWordCount(this, 1000, 'summaryWordCount', 'summaryWordBadge', 'summaryProgressBar', 'summaryWarning')">{{ $current }}</textarea>
                            <div class="word-counter-progress">
                                <div class="word-counter-progress__bar" id="summaryProgressBar"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <div class="form-text text-muted mb-0" style="font-size: 11.5px;">Short book teaser or attractive excerpt (up to 400 words).</div>
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
                                    <i class="fas fa-font me-1"></i> Words: <span id="authorBioWordCount">0</span> / 300
                                </div>
                            </div>
                            <textarea id="f-author_bio" name="author_bio" rows="4"
                                      class="form-control @error('author_bio') is-invalid @enderror"
                                      placeholder="Author biography, literary background and key achievements (max 300 words)..."
                                      oninput="updateGenericWordCount(this, 300, 'authorBioWordCount', 'authorBioWordBadge', 'authorBioProgressBar', 'authorBioWarning')">{{ $current }}</textarea>
                            <div class="word-counter-progress">
                                <div class="word-counter-progress__bar" id="authorBioProgressBar"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <div class="form-text text-muted mb-0" style="font-size: 11.5px;">
                                    Optional custom author bio (up to 300 words).
                                </div>
                                <div id="authorBioWarning" class="text-danger small fw-bold d-none"></div>
                            </div>
                            @error('author_bio')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                        {{-- ══ DESCRIPTION / FLAP (400 WORDS LIMIT) ══ --}}
                        @elseif ($name === 'description')
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label for="f-description" class="form-label small fw-semibold text-dark mb-0">
                                    <i class="fas fa-align-left text-primary me-1"></i> {{ $field['label'] }}
                                </label>
                                <div class="word-counter-badge safe" id="descriptionWordBadge">
                                    <i class="fas fa-font me-1"></i> Words: <span id="descriptionWordCount">0</span> / 400
                                </div>
                            </div>
                            <textarea id="f-description" name="description" rows="7"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Complete book flap, table of contents or overview (max 400 words)..."
                                      oninput="updateGenericWordCount(this, 400, 'descriptionWordCount', 'descriptionWordBadge', 'descriptionProgressBar', 'descriptionWarning')">{{ $current }}</textarea>
                            <div class="word-counter-progress">
                                <div class="word-counter-progress__bar" id="descriptionProgressBar"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <div class="form-text text-muted mb-0" style="font-size: 11.5px;">Full book flap and detailed description (max 400 words).</div>
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
                                            <i class="fas fa-plus-circle me-1"></i>+ Add New Category
                                        </button>
                                    @elseif (($field['lookup'] ?? '') === 'categories' || in_array($spec['key'], ['books', 'ebooks'], true))
                                        <button type="button" class="btn btn-link text-primary p-0 text-decoration-none small fw-semibold"
                                                data-bs-toggle="modal" data-bs-target="#quickAddCategoryModal">
                                            <i class="fas fa-plus-circle me-1"></i>+ Add New Category
                                        </button>
                                    @endif
                                @elseif ($name === 'publisher_id' && ($field['lookup'] ?? '') === 'publishers')
                                    <button type="button" class="btn btn-link text-primary p-0 text-decoration-none small fw-semibold"
                                            data-bs-toggle="modal" data-bs-target="#quickAddPublisherModal">
                                        <i class="fas fa-plus-circle me-1"></i>+ Add New Publisher
                                    </button>
                                @endif
                            </div>

                            @switch($field['type'])
                                @case('textarea')
                                    <textarea id="f-{{ $name }}" name="{{ $name }}" rows="3"
                                              class="form-control @error($name) is-invalid @enderror">{{ $current }}</textarea>
                                    @break

                                @case('editor')
                                    @php
                                        $editorHtml = $current;
                                        if (!empty($editorHtml)) {
                                            if (!str_contains($editorHtml, '<p>') && !str_contains($editorHtml, '<br>') && !str_contains($editorHtml, '<div>') && !str_contains($editorHtml, '<blockquote')) {
                                                $rawStanzas = preg_split('/\r\n\r\n|\n\n+|\r\r+/', (string) $editorHtml);
                                                $formattedStanzas = [];
                                                foreach ($rawStanzas as $st) {
                                                    $st = trim($st);
                                                    if ($st !== '') {
                                                        $formattedStanzas[] = '<p style="margin-bottom: 1.35rem; line-height: 1.95;">' . nl2br(e($st)) . '</p>';
                                                    }
                                                }
                                                $editorHtml = implode('', $formattedStanzas);
                                            }
                                        }
                                    @endphp

                                    <div class="rich-editor-wrapper border rounded-3 overflow-hidden shadow-xs mb-2">
                                        <!-- Formatting Toolbar -->
                                        <div class="rich-editor-toolbar bg-light p-2 border-bottom d-flex flex-wrap gap-1 align-items-center">
                                            <!-- Heading Format Selector -->
                                            <select class="form-select form-select-sm" style="width: auto; min-width: 135px;" onchange="formatDoc('formatBlock', this.value, 'f-{{ $name }}')">
                                                <option value="p">Paragraph (P)</option>
                                                <option value="h1">Heading 1 (H1)</option>
                                                <option value="h2">Heading 2 (H2)</option>
                                                <option value="h3">Heading 3 (H3)</option>
                                                <option value="h4">Section (H4)</option>
                                                <option value="blockquote">Quote (Blockquote)</option>
                                            </select>

                                             <!-- Font Size Selector -->
                                            <select class="form-select form-select-sm" style="width: auto; min-width: 105px;" onchange="formatDoc('fontSize', this.value, 'f-{{ $name }}')">
                                                <option value="3">Font Size</option>
                                                <option value="1">Tiny (12px)</option>
                                                <option value="2">Small (14px)</option>
                                                <option value="3">Regular (16px)</option>
                                                <option value="4">Medium (18px)</option>
                                                <option value="5">Large (22px)</option>
                                                <option value="6">Extra Large (28px)</option>
                                            </select>

                                            <!-- Line Spacing Selector & Direct Increment/Decrement Controls -->
                                            <select class="form-select form-select-sm" style="width: auto; min-width: 110px;" onchange="changeLineSpacing('f-{{ $name }}', this.value)" title="Line Spacing">
                                                <option value="">Line Spacing</option>
                                                <option value="1.2">Compact (1.2)</option>
                                                <option value="1.35">Tight (1.35)</option>
                                                <option value="1.5">Standard (1.5)</option>
                                                <option value="1.65">Readable (1.65)</option>
                                                <option value="1.85">Relaxed (1.85)</option>
                                                <option value="2.1">Loose (2.1)</option>
                                            </select>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-primary fw-bold" onclick="adjustLineSpacing('f-{{ $name }}', -0.15)" title="Tighter Line Spacing">
                                                <i class="fas fa-arrows-alt-v me-1"></i>Tighten (-)
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-dark" onclick="adjustLineSpacing('f-{{ $name }}', 0.15)" title="Looser Line Spacing">
                                                <i class="fas fa-arrows-alt-v me-1"></i>Loosen (+)
                                            </button>

                                            <!-- Paragraph Spacing Selector -->
                                            <select class="form-select form-select-sm" style="width: auto; min-width: 105px;" onchange="changeParagraphSpacing('f-{{ $name }}', this.value)" title="Paragraph Gap">
                                                <option value="">Para Gap</option>
                                                <option value="0.25rem">Minimal (0.25rem)</option>
                                                <option value="0.5rem">Small (0.5rem)</option>
                                                <option value="0.75rem">Standard (0.75rem)</option>
                                                <option value="1.1rem">Medium (1.1rem)</option>
                                                <option value="1.5rem">Large (1.5rem)</option>
                                            </select>

                                            <div class="vr mx-1"></div>

                                            <!-- Style buttons -->
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 fw-bold" onclick="formatDoc('bold', null, 'f-{{ $name }}')" title="Bold (Ctrl+B)">
                                                <i class="fas fa-bold"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 fst-italic" onclick="formatDoc('italic', null, 'f-{{ $name }}')" title="Italic (Ctrl+I)">
                                                <i class="fas fa-italic"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 text-decoration-underline" onclick="formatDoc('underline', null, 'f-{{ $name }}')" title="Underline (Ctrl+U)">
                                                <i class="fas fa-underline"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 text-decoration-line-through" onclick="formatDoc('strikeThrough', null, 'f-{{ $name }}')" title="Strikethrough">
                                                <i class="fas fa-strikethrough"></i>
                                            </button>

                                            <div class="vr mx-1"></div>

                                            <!-- Alignment -->
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="formatDoc('justifyLeft', null, 'f-{{ $name }}')" title="Align Left">
                                                <i class="fas fa-align-left"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="formatDoc('justifyCenter', null, 'f-{{ $name }}')" title="Align Center">
                                                <i class="fas fa-align-center"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="formatDoc('justifyRight', null, 'f-{{ $name }}')" title="Align Right">
                                                <i class="fas fa-align-right"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="formatDoc('justifyFull', null, 'f-{{ $name }}')" title="Justify">
                                                <i class="fas fa-align-justify"></i>
                                            </button>

                                            <div class="vr mx-1"></div>

                                            <!-- Lists & Divider -->
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="formatDoc('insertUnorderedList', null, 'f-{{ $name }}')" title="Bullet List">
                                                <i class="fas fa-list-ul"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="formatDoc('insertOrderedList', null, 'f-{{ $name }}')" title="Numbered List">
                                                <i class="fas fa-list-ol"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="formatDoc('insertHorizontalRule', null, 'f-{{ $name }}')" title="Divider Line">
                                                <i class="fas fa-minus"></i>
                                            </button>

                                            <div class="vr mx-1"></div>

                                            <!-- Link & Media -->
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-primary" onclick="insertLinkPrompt('f-{{ $name }}')" title="Insert Link">
                                                <i class="fas fa-link"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-muted" onclick="formatDoc('unlink', null, 'f-{{ $name }}')" title="Remove Link">
                                                <i class="fas fa-link-slash"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-success" onclick="insertImagePrompt('f-{{ $name }}')" title="Insert Image">
                                                <i class="fas fa-image"></i>
                                            </button>

                                            <div class="vr mx-1"></div>

                                            <!-- Literary Poetry & Prose Enhancers -->
                                            <button type="button" class="btn btn-sm btn-outline-primary border py-1 px-2.5 fw-semibold" onclick="formatPoetryMode('f-{{ $name }}')" title="Preserve Poetry Stanzas">
                                                <i class="fas fa-feather-alt text-primary me-1"></i> Poetry Mode
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary border py-1 px-2.5 fw-semibold" onclick="formatProseMode('f-{{ $name }}')" title="Prose Mode">
                                                <i class="fas fa-align-left me-1"></i> Prose Mode
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-info border py-1 px-2.5 fw-semibold" onclick="formatFixLineBreaks('f-{{ $name }}')" title="Auto Repair Line & Para Spacing">
                                                <i class="fas fa-wand-magic-sparkles me-1"></i> Fix Spacing
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning border py-1 px-2.5 fw-semibold text-dark" id="spellBtn-{{ $name }}" onclick="toggleSpellChecker('{{ $name }}')" title="Spell Checker">
                                                <i class="fas fa-spell-check text-warning me-1"></i> <span id="spellBtnText-{{ $name }}">Spell Check</span>
                                            </button>
                                            @if($spec['key'] === 'blog')
                                                <button type="button" class="btn btn-sm btn-outline-success border py-1 px-2.5 fw-semibold" onclick="openBlogLivePreviewModal('f-{{ $name }}')" title="Live Article Reader Preview">
                                                    <i class="fas fa-eye me-1"></i> Reader Preview
                                                </button>
                                            @endif

                                            <div class="vr mx-1"></div>

                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="formatDoc('undo', null, 'f-{{ $name }}')" title="Undo (Ctrl+Z)">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="formatDoc('redo', null, 'f-{{ $name }}')" title="Redo (Ctrl+Y)">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-danger" onclick="formatDoc('removeFormat', null, 'f-{{ $name }}')" title="Clear Formatting">
                                                <i class="fas fa-eraser"></i>
                                            </button>
                                        </div>

                                        <!-- Contenteditable Live Area -->
                                        <div id="editable-{{ $name }}" contenteditable="true" 
                                             class="p-3.5 bg-white text-dark rich-editor-content" 
                                             style="min-height: 350px; max-height: 650px; overflow-y: auto; outline: none; font-size: 16.5px; line-height: 1.55; font-family: 'Hind Siliguri', 'Kalpurush', 'SolaimanLipi', sans-serif;"
                                             oninput="onEditorInputWithSpellCheck('{{ $name }}')">{!! $editorHtml !!}</div>

                                        <!-- Hidden/Synced real textarea for form submission -->
                                        <textarea id="f-{{ $name }}" name="{{ $name }}" class="d-none @error($name) is-invalid @enderror">{!! $editorHtml !!}</textarea>
                                    </div>

                                    <!-- Spell Checker Results Notification Box -->
                                    <div id="spell-results-{{ $name }}" class="mt-2.5 d-none"></div>

                                    <div class="d-flex flex-wrap align-items-center justify-content-between text-muted mt-1" style="font-size: 11.5px;">
                                        <div><i class="fas fa-circle-info text-primary me-1"></i> Use <strong>“Poetry Mode”</strong> for verse formatting or <strong>“Prose Mode”</strong> & <strong>“Fix Spacing”</strong> for clean stanzas.</div>
                                        <div id="editorWordStats-{{ $name }}" class="fw-semibold text-dark"></div>
                                    </div>
                                    @break

                                @case('select')
                                    @php
                                        $options = $field['options'] ?? ($lookups[$field['lookup'] ?? ''] ?? []);
                                    @endphp
                                    <select id="f-{{ $name }}" name="{{ $name }}"
                                            class="form-select @error($name) is-invalid @enderror"
                                            @if($name === 'cover_type') onchange="onCoverTypeChange()" @endif>
                                        <option value="">— Select Option —</option>
                                        @foreach ($options as $optValue => $optLabel)
                                            <option value="{{ $optValue }}" @selected((string) $current === (string) $optValue)>
                                                {{ $optLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($name === 'stock_status')
                                        <div class="form-text" style="font-size: 11.5px;">Current product availability for customers.</div>
                                    @elseif ($name === 'cover_type')
                                        <div class="form-text" style="font-size: 11.5px;">Select hardcover, paperback, or both editions.</div>
                                    @endif
                                    @break

                                @case('file')
                                    @php
                                        $isCover = in_array($name, ['cover_image', 'image', 'banner'], true);
                                        $isPdf   = in_array($name, ['sample_pdf_path', 'file_path', 'epub_file_path', 'sample_file_path'], true);
                                        $isAvatar = in_array($name, ['avatar', 'author_image', 'logo'], true);

                                        $guideText = '';
                                        if ($isCover) {
                                            $guideText = 'Dimensions: 600 × 900 px (2:3), JPG/PNG/WebP, max 4MB';
                                        } elseif ($isAvatar) {
                                            $guideText = 'Dimensions: 400 × 400 px (1:1 square), max 4MB';
                                        } elseif ($isPdf) {
                                            $guideText = 'PDF / EPUB format, max 20MB';
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
                                            Upload {{ $field['label'] }}
                                        </div>
                                        <div class="text-muted small mb-1" style="font-size: 0.8rem;">
                                            Click to browse or drag and drop file here
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
                                                <span class="badge bg-success mb-1"><i class="fas fa-check-circle me-1"></i> New file ready</span>
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
                                                    Current file stored
                                                </div>
                                                <a href="{{ $fileUrl }}" target="_blank" rel="noopener"
                                                   class="btn btn-sm btn-outline-primary py-0.5 px-2 rounded-pill fw-semibold text-decoration-none" style="font-size: 11px;">
                                                    <i class="fas fa-arrow-up-right-from-square me-1"></i> View / Open File
                                                </a>
                                            </div>
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                       id="rm-{{ $name }}" name="remove_{{ $name }}" value="1">
                                                <label class="form-check-label small text-danger fw-semibold" for="rm-{{ $name }}">Remove</label>
                                            </div>
                                        </div>
                                    @endif
                                    @break

                                @case('number')
                                    <input type="number" step="{{ $field['step'] ?? '1' }}" min="0"
                                           id="f-{{ $name }}" name="{{ $name }}" value="{{ $current }}"
                                           class="form-control @error($name) is-invalid @enderror">
                                    @if ($name === 'page_count')
                                        <div class="form-text" style="font-size: 11px;">Total number of pages (e.g. 256).</div>
                                    @elseif ($name === 'preview_pages')
                                        <div class="form-text" style="font-size: 11px;">Number of pages readable in sample preview.</div>
                                    @endif
                                    @break

                                @case('date')
                                    <input type="date" id="f-{{ $name }}" name="{{ $name }}"
                                           value="{{ $current instanceof \DateTimeInterface ? $current->format('Y-m-d') : $current }}"
                                           class="form-control @error($name) is-invalid @enderror">
                                    @if ($name === 'published_at')
                                        <div class="form-text" style="font-size: 11px;">Original publication / release date.</div>
                                    @endif
                                    @break

                                @default
                                    <input type="text" id="f-{{ $name }}" name="{{ $name }}" value="{{ $current }}"
                                           class="form-control @error($name) is-invalid @enderror"
                                           oninput="updateLiveMockupCard()">
                                    @if ($name === 'subtitle')
                                        <div class="form-text" style="font-size: 11.5px;">Subtitle or edition tagline (optional).</div>
                                    @elseif ($name === 'edition')
                                        <div class="form-text" style="font-size: 11.5px;">e.g., 1st Edition (2024), 2nd Print, etc.</div>
                                    @elseif ($name === 'language')
                                        <div class="form-text" style="font-size: 11.5px;">e.g., Bengali, English, Arabic, etc.</div>
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
                                    <i class="fas fa-list-ol text-primary me-2"></i>Table of Contents & Page Indexer
                                </h5>
                                <p class="text-muted small mb-0">
                                    Add article titles, authors, and page numbers. Readers can jump directly to any page from the interactive Table of Contents.
                                </p>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-xs" onclick="addWebzineTocRow()">
                                <i class="fas fa-plus-circle me-1"></i>+ Add TOC Entry / Article
                            </button>
                        </div>

                        <div class="table-responsive rounded-3 border bg-white shadow-xs">
                            <table class="table table-hover align-middle mb-0" id="webzineTocTable">
                                <thead class="table-light small fw-bold text-secondary">
                                    <tr>
                                        <th style="width: 45px;" class="text-center">#</th>
                                        <th>Article / Chapter Title <span class="text-danger">*</span></th>
                                        <th style="width: 220px;">Author</th>
                                        <th style="width: 140px;">Page # <span class="text-danger">*</span></th>
                                        <th style="width: 60px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="webzineTocBody">
                                    @forelse($existingArticles as $idx => $art)
                                        <tr class="webzine-toc-row">
                                            <td class="text-center fw-bold text-muted row-number">{{ $idx + 1 }}</td>
                                            <td>
                                                <input type="hidden" name="toc_articles[{{ $idx }}][id]" value="{{ $art->id }}">
                                                <input type="hidden" name="toc_articles[{{ $idx }}][order]" class="input-order" value="{{ $art->order ?: ($idx + 1) }}">
                                                <input type="text" name="toc_articles[{{ $idx }}][title]" class="form-control form-control-sm" value="{{ $art->title }}" placeholder="e.g. Editorial / Feature Story..." required>
                                            </td>
                                            <td>
                                                <select name="toc_articles[{{ $idx }}][author_id]" class="form-select form-select-sm">
                                                    <option value="">— Select Author (Optional) —</option>
                                                    @foreach($authorList as $aId => $aName)
                                                        <option value="{{ $aId }}" @selected((string)$art->author_id === (string)$aId)>{{ $aName }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light text-muted">Page</span>
                                                    <input type="number" name="toc_articles[{{ $idx }}][page_number]" class="form-control form-control-sm text-center fw-bold" value="{{ $art->page_number ?: ($idx + 1) }}" min="1" placeholder="1" required>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" onclick="removeWebzineTocRow(this)" title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="webzine-toc-row">
                                            <td class="text-center fw-bold text-muted row-number">1</td>
                                            <td>
                                                <input type="hidden" name="toc_articles[0][order]" class="input-order" value="1">
                                                <input type="text" name="toc_articles[0][title]" class="form-control form-control-sm" placeholder="e.g. Editorial / Opening Piece..." required>
                                            </td>
                                            <td>
                                                <select name="toc_articles[0][author_id]" class="form-select form-select-sm">
                                                    <option value="">— Select Author (Optional) —</option>
                                                    @foreach($authorList as $aId => $aName)
                                                        <option value="{{ $aId }}">{{ $aName }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light text-muted">Page</span>
                                                    <input type="number" name="toc_articles[0][page_number]" class="form-control form-control-sm text-center fw-bold" value="1" min="1" placeholder="1" required>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" onclick="removeWebzineTocRow(this)" title="Delete">
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
                                <i class="fas fa-plus me-1"></i> Add Another Entry
                            </button>
                            <span class="small text-muted"><i class="fas fa-info-circle me-1 text-primary"></i>Page number links directly to the page in the digital reader.</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Sidebar (Live Mockup, Moderation, Submit) -->
    <div class="col-12 col-lg-4">
        <div style="position: sticky; top: 20px; z-index: 1020;">
        
        {{-- Live Book / Ebook Card Mockup --}}
        @if ($isBookOrEbook)
            <div class="adm-card p-3 mb-3">
                <h6 class="fw-bold mb-2 text-dark d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-eye me-1.5 text-primary"></i> Live Card Preview</span>
                    <span class="badge bg-success-subtle text-success small rounded-pill">Real-Time</span>
                </h6>
                <div class="p-3 bg-light rounded-3 border text-center">
                    <div class="position-relative mx-auto mb-2" style="width: 125px; height: 185px;">
                        <img id="mockupCoverImg" 
                             src="{{ ($editing && !empty($record->cover_image)) ? (str_starts_with($record->cover_image, 'http') ? $record->cover_image : asset('storage/' . ltrim($record->cover_image, '/'))) : 'https://placehold.co/300x450/e2e8f0/475569?text=Cover+Image' }}" 
                             alt="Book Cover" class="rounded shadow-sm border w-100 h-100" style="object-fit: cover; aspect-ratio: 2/3; image-rendering: -webkit-optimize-contrast;">
                        <span id="mockupDiscountBadge" class="badge bg-danger position-absolute top-0 start-0 m-1 shadow-xs d-none" style="font-size: 0.72rem;">
                            -0%
                        </span>
                        <span id="mockupFormatBadge" class="badge bg-dark position-absolute bottom-0 start-0 m-1 shadow-xs opacity-90" style="font-size: 0.68rem;">
                            Hardcover
                        </span>
                    </div>
                    <div id="mockupTitle" class="fw-bold text-dark text-truncate mb-0.5" style="font-size: 0.92rem;">
                        {{ $editing ? ($record->title ?? 'Book Title') : 'Book Title' }}
                    </div>
                    <div id="mockupAuthor" class="small text-muted mb-1.5 text-truncate" style="font-size: 0.78rem;">
                        {{ $editing ? ($record->author_name ?? 'Author Name') : 'Author Name' }}
                    </div>
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <span id="mockupFinalPrice" class="fw-bold text-primary fs-6">৳0</span>
                        <span id="mockupOriginalPrice" class="text-muted text-decoration-line-through small d-none">৳0</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Live Blog Post Mockup --}}
        @if ($spec['key'] === 'blog')
            <div class="adm-card p-3 mb-3">
                <h6 class="fw-bold mb-2 text-dark d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-feather-pointed me-1.5 text-primary"></i> Article & Card Preview</span>
                    <span class="badge bg-success-subtle text-success small rounded-pill">Real-Time</span>
                </h6>
                <div class="p-3 bg-light rounded-3 border text-start">
                    <div class="position-relative mx-auto mb-2 rounded-3 overflow-hidden" style="max-height: 140px; aspect-ratio: 16/9; background: #e2e8f0;">
                        <img id="mockupCoverImg" 
                             src="{{ ($editing && !empty($record->featured_image)) ? (str_starts_with($record->featured_image, 'http') ? $record->featured_image : asset('storage/' . ltrim($record->featured_image, '/'))) : asset('images/og-banner.jpg') }}" 
                             alt="Blog Cover" class="img-fluid w-100 h-100 object-fit-cover">
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span id="mockupCategoryBadge" class="badge bg-primary text-white" style="font-size: 0.72rem;">
                            {{ $editing && $record->category ? $record->category->name : 'Literature' }}
                        </span>
                        <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">
                            {{ $editing && $record->published_at ? $record->published_at->format('d M Y') : date('d M Y') }}
                        </span>
                    </div>
                    <div id="mockupTitle" class="fw-bold text-dark text-truncate mb-1" style="font-size: 0.95rem;">
                        {{ $editing ? ($record->title ?? 'Post Title') : 'Post Title' }}
                    </div>
                    <div id="mockupSubtitle" class="small text-secondary mb-1 text-truncate" style="font-size: 0.8rem;">
                        {{ $editing ? ($record->subtitle ?? '') : '' }}
                    </div>
                    <div id="mockupAuthor" class="small text-muted d-flex align-items-center gap-1" style="font-size: 0.78rem;">
                        <i class="fas fa-pen-nib text-success"></i>
                        <span>{{ $editing && $record ? ($record->author_name ?? ($record->author->name ?? 'Author Name')) : 'Author Name' }}</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Live Webzine Card & Reader Mockup --}}
        @if ($spec['key'] === 'webzines')
            <div class="adm-card p-3 mb-3">
                <h6 class="fw-bold mb-2 text-dark d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-newspaper me-1.5 text-info"></i> Webzine Card & Reader</span>
                    <span class="badge bg-info-subtle text-info small rounded-pill">Live Status</span>
                </h6>
                <div class="p-3 bg-light rounded-3 border text-center">
                    <div class="position-relative mx-auto mb-2" style="max-width: 140px;">
                        <img id="mockupCoverImg" 
                             src="{{ ($editing && !empty($record->cover_image)) ? (str_starts_with($record->cover_image, 'http') ? $record->cover_image : asset('storage/' . ltrim($record->cover_image, '/'))) : 'https://placehold.co/300x450/e2e8f0/475569?text=Webzine+Cover' }}" 
                             alt="Webzine Cover" class="img-fluid rounded shadow-sm border" style="aspect-ratio: 2/3; object-fit: cover; width: 100%;">
                        <span id="mockupIssueBadge" class="badge bg-primary position-absolute top-0 start-0 m-1 shadow-xs">
                            {{ $editing ? ($record->issue_number ?? 'Issue') : 'Issue 1' }}
                        </span>
                    </div>
                    <div id="mockupTitle" class="fw-bold text-dark text-truncate mb-0.5" style="font-size: 0.95rem;">
                        {{ $editing ? ($record->title ?? 'Webzine Title') : 'Webzine Title' }}
                    </div>
                    <div id="mockupPublisher" class="small text-muted mb-2 text-truncate" style="font-size: 0.8rem;">
                        {{ $editing && $record->publisher ? $record->publisher->name : 'Idea Prakashan' }}
                    </div>

                    @if ($editing)
                        <div class="d-grid gap-1.5 mt-2">
                            <a href="{{ route('webzine.read', $record->slug ?: $record->id) }}" target="_blank" class="btn btn-sm btn-info text-white rounded-pill fw-bold">
                                <i class="fas fa-book-open me-1"></i> Open in Digital Reader
                            </a>
                            <a href="{{ route('webzine.show', $record->slug ?: $record->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill">
                                <i class="fas fa-eye me-1"></i> Public Page Preview
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Posting on behalf of someone --}}
        <div class="adm-card p-3 mb-3">
            <h2 class="h6 fw-bold mb-1"><i class="fas fa-user-pen me-1 text-muted"></i> On Behalf of (Contributor Credit)</h2>
            <p class="text-muted small mb-3" style="font-size: 11.5px;">
                Credit this entry to an offline contributor or another registered user account.
            </p>

            <div class="mb-2.5">
                <label for="f-submitted_by" class="form-label small fw-semibold mb-1">Registered User</label>
                <select id="f-submitted_by" name="submitted_by" class="form-select form-select-sm @error('submitted_by') is-invalid @enderror">
                    <option value="">— Myself (Admin) —</option>
                    @foreach (($creditees ?? []) as $userId => $userLabel)
                        <option value="{{ $userId }}" @selected((string) $val('submitted_by') === (string) $userId)>
                            {{ $userLabel }}
                        </option>
                    @endforeach
                </select>
                @error('submitted_by')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @if ($spec['key'] !== 'blog')
            <div class="mb-2.5">
                <label for="f-owner_name" class="form-label small fw-semibold mb-1">Offline Contributor Name</label>
                <input type="text" id="f-owner_name" name="owner_name" value="{{ $val('owner_name') }}"
                       placeholder="e.g., Md. Anisur Rahman"
                       class="form-control form-control-sm @error('owner_name') is-invalid @enderror">
                @error('owner_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @endif

            <div>
                <label for="f-owner_phone" class="form-label small fw-semibold mb-1">Contact Phone</label>
                <input type="text" id="f-owner_phone" name="owner_phone" value="{{ $val('owner_phone') }}"
                       placeholder="01XXXXXXXXX"
                       class="form-control form-control-sm @error('owner_phone') is-invalid @enderror">
                @error('owner_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Moderation & Slug --}}
        <div class="adm-card p-3 mb-3">
            <h2 class="h6 fw-bold mb-2.5"><i class="fas fa-circle-check me-1 text-muted"></i> Moderation & URL</h2>

            <div class="mb-3">
                <label for="f-mod_status" class="form-label small fw-semibold mb-1">Status</label>
                <select id="f-mod_status" name="mod_status" class="form-select form-select-sm">
                    @foreach (['approved' => 'Approved (Live on site)', 'pending' => 'Pending (Under Review)', 'rejected' => 'Rejected'] as $value => $text)
                        <option value="{{ $value }}" @selected($val('mod_status', 'approved') === $value)>{{ $text }}</option>
                    @endforeach
                </select>
            </div>

            @if ($editing && $record->rejection_reason)
                <div class="alert alert-warning small mt-2 mb-2 p-2">
                    <strong>Rejection Reason:</strong> {{ $record->rejection_reason }}
                </div>
            @endif

            <div>
                <label for="f-slug" class="form-label small fw-semibold mb-1">Custom Slug (URL)</label>
                <input type="text" id="f-slug" name="slug" value="{{ $val('slug') }}"
                       placeholder="Leave blank to auto-generate"
                       class="form-control form-control-sm @error('slug') is-invalid @enderror">
                <div class="form-text" style="font-size: 11px;">SEO-friendly URL identifier.</div>
                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- Legal & Publishing Compliance Declaration for Books --}}
        @if ($spec['key'] === 'books')
            <div class="adm-card p-3 mb-3 border-start border-4 border-success shadow-xs">
                <div class="d-flex align-items-center gap-2 mb-2 text-dark fw-bold" style="font-size: 0.88rem;">
                    <i class="fas fa-scale-balanced text-success"></i>
                    <span>আইন ও প্রকাশনা নীতিমালা সম্মতি</span>
                </div>

                <div class="p-2.5 bg-light rounded-3 border mb-2.5 small text-secondary" style="font-size: 11px; line-height: 1.55; max-height: 180px; overflow-y: auto;">
                    <p class="mb-1.5"><strong>১. সাধারণ বিধি ও নৈতিকতা:</strong> বাংলাদেশে বই প্রকাশ ও মুদ্রণের ক্ষেত্রে প্রেস ও প্রকাশনা, কপিরাইট, দণ্ডবিধি, অশ্লীল প্রকাশনা এবং ডিজিটাল মাধ্যমে প্রকাশিত কনটেন্টসংক্রান্ত প্রচলিত আইন ও বিধি মানা আবশ্যক। প্রকাশনা ও মুদ্রণ প্রতিষ্ঠানের প্রয়োজনীয় নিবন্ধন/অনুমোদন থাকতে হবে এবং বইয়ের বিষয়বস্তু রাষ্ট্রীয় নিরাপত্তা, জনশৃঙ্খলা, ধর্মীয় অনুভূতি, নৈতিকতা ও শালীনতার পরিপন্থী হওয়া যাবে না।</p>
                    <p class="mb-1.5"><strong>২. দণ্ডবিধি ও প্রকাশনা আইন:</strong> দণ্ডবিধি, ১৮৬০-এর ২৯২, ২৯৩ ও ৫০৫ ধারায় অশ্লীল প্রকাশনা, অপ্রাপ্তবয়স্কদের কাছে অশ্লীল উপাদান সরবরাহ এবং জনশৃঙ্খলা বিনষ্টকারী বক্তব্যের বিষয়ে বিধান রয়েছে। মুদ্রণ ও প্রকাশনা আইন, ১৯৭৩-এর সংশ্লিষ্ট বিধান অনুযায়ী প্রেস পরিচালনা ও প্রকাশনার ক্ষেত্রে প্রয়োজনীয় অনুমোদন এবং সরকারি নির্দেশনা অনুসরণ করতে হবে।</p>
                    <p class="mb-1.5"><strong>৩. কপিরাইট ও মেধাস্বত্ব:</strong> কপিরাইট আইন, ২০০০ অনুযায়ী অন্যের লেখা, ছবি, ডিজাইন বা মেধাস্বত্ব অনুমতি ছাড়া ব্যবহার বা প্রকাশ করা যাবে না। প্রযোজ্য ক্ষেত্রে কপিরাইট নিবন্ধন, ISBN গ্রহণ এবং প্রকাশিত বইয়ের বাধ্যতামূলক কপি জাতীয় গ্রন্থাগারে জমা দেওয়ার বিধানও অনুসরণ করতে হবে। ডিজিটাল মাধ্যমে প্রকাশের ক্ষেত্রে সংশ্লিষ্ট সাইবার ও প্রচলিত আইনও প্রযোজ্য।</p>
                    <p class="mb-1.5"><strong>৪. দায়বদ্ধতা ও বিতরণব্যবস্থা:</strong> বইয়ের তথ্য, বক্তব্য ও উপাদান যথাসম্ভব নির্ভুল, দায়িত্বশীল ও আইনসম্মত হতে হবে। প্রকাশনা বাজারজাতকরণে পরিবেশক/বিক্রেতার সঙ্গে প্রয়োজনীয় চুক্তি ও স্বচ্ছ বিতরণব্যবস্থা নিশ্চিত করা উচিত।</p>
                    <p class="mb-0"><strong>৫. পর্যালোচনা ও প্রত্যাহার নীতি:</strong> আইডিয়া প্রকাশন / প্ল্যাটফর্মে কোনো বইয়ের বিষয়বস্তু নিয়ে অভিযোগ বা সংশয় দেখা দিলে, বইটি সাময়িকভাবে প্রদর্শন থেকে সরিয়ে নির্ধারিত পর্যালোচনা টিমের মাধ্যমে মূল্যায়ন করা হতে পারে। পর্যালোচনার ভিত্তিতে বইটি স্থায়ীভাবে অপসারণ অথবা পুনরায় প্রদর্শনের সিদ্ধান্ত নেওয়া হবে।</p>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="adminComplianceCheck" name="compliance_agreed" value="1" checked required>
                    <label class="form-check-label small text-dark fw-bold" for="adminComplianceCheck" style="font-size: 11.5px; line-height: 1.45;">
                        উপরোক্ত সকল শর্ত ও প্রযোজ্য আইন-বিধি মেনে বই প্রকাশের বিষয়ে আমি সম্মত।
                    </label>
                </div>
            </div>
        @endif

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary rounded-pill py-2.5 fw-bold shadow-xs">
                <i class="fas fa-floppy-disk me-1.5"></i> {{ $editing ? 'Save Changes' : 'Publish & Save' }}
            </button>
            <a href="{{ route($spec['listRoute']) }}" class="btn btn-outline-secondary rounded-pill py-2">Cancel</a>
        </div>
        </div>
    </div>
@endif
</form>

{{-- ========================================================================= --}}
{{-- MODAL 1: QUICK ADD CATEGORY                                               --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="quickAddCategoryModal" tabindex="-1" aria-labelledby="quickAddCatLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0" id="quickAddCatLabel">
                    <i class="fas fa-folder-plus me-1.5"></i> Create New Category
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickCategoryForm" onsubmit="handleQuickCategorySubmit(event)">
                <div class="modal-body p-3">
                    <div id="quickCatAlert"></div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" id="quick_cat_name" name="name" class="form-control form-control-sm" 
                               placeholder="e.g. Translated Fiction / Science / Poetry" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Parent Category (Optional)</label>
                        <select id="quick_cat_parent_id" name="parent_id" class="form-select form-select-sm">
                            <option value="">— Primary Category (No Parent) —</option>
                            @foreach ($lookups['categories'] ?? [] as $cId => $cName)
                                <option value="{{ $cId }}">{{ $cName }}</option>
                            @endforeach
                        </select>
                        <div class="form-text" style="font-size: 11px;">Select a parent category to create a sub-category under it.</div>
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Description (Optional)</label>
                        <textarea id="quick_cat_description" name="description" rows="2" class="form-control form-control-sm" placeholder="Short description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="quickCatBtn" class="btn btn-sm btn-primary">
                        <i class="fas fa-check-circle me-1"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 1.5: QUICK ADD BLOG CATEGORY                                        --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="quickAddBlogCategoryModal" tabindex="-1" aria-labelledby="quickAddBlogCatLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0" id="quickAddBlogCatLabel">
                    <i class="fas fa-shapes me-1.5"></i> Create New Blog Category
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickBlogCategoryForm" onsubmit="handleQuickBlogCategorySubmit(event)">
                <div class="modal-body p-3">
                    <div id="quickBlogCatAlert"></div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Blog Category Name <span class="text-danger">*</span></label>
                        <input type="text" id="quick_blog_cat_name" name="name" class="form-control form-control-sm" 
                               placeholder="e.g. Poetry / Short Stories / Essays / History" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Icon Class (FontAwesome - Optional)</label>
                        <input type="text" id="quick_blog_cat_icon" name="icon" class="form-control form-control-sm" 
                               placeholder="e.g. feather-pointed / book-open-reader / pen-nib" value="feather-pointed">
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Description (Optional)</label>
                        <textarea id="quick_blog_cat_description" name="description" rows="2" class="form-control form-control-sm" placeholder="Short description or intro..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="quickBlogCatBtn" class="btn btn-sm btn-primary">
                        <i class="fas fa-check-circle me-1"></i> Save Blog Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 2: QUICK ADD PUBLISHER                                              --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="quickAddPublisherModal" tabindex="-1" aria-labelledby="quickAddPubLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0" id="quickAddPubLabel">
                    <i class="fas fa-building me-1.5"></i> Add New Publisher
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickPublisherForm" onsubmit="handleQuickPublisherSubmit(event)">
                <div class="modal-body p-3">
                    <div id="quickPubAlert"></div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Publisher Name <span class="text-danger">*</span></label>
                        <input type="text" id="quick_pub_name" name="name" class="form-control form-control-sm" 
                               placeholder="e.g. Somoy Prokashon / Batighar / Anupam" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Phone Number</label>
                        <input type="text" id="quick_pub_phone" name="phone" class="form-control form-control-sm" placeholder="01XXXXXXXXX">
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Address</label>
                        <input type="text" id="quick_pub_address" name="address" class="form-control form-control-sm" placeholder="Banglabazar, Dhaka">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="quickPubBtn" class="btn btn-sm btn-primary">
                        <i class="fas fa-check-circle me-1"></i> Save Publisher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 3: QUICK ADD AUTHOR                                                 --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="quickAddAuthorModal" tabindex="-1" aria-labelledby="quickAddAuthLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0" id="quickAddAuthLabel">
                    <i class="fas fa-pen-nib me-1.5"></i> Add New Author
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickAuthorForm" onsubmit="handleQuickAuthorSubmit(event)">
                <div class="modal-body p-3">
                    <div id="quickAuthAlert"></div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Author Full Name <span class="text-danger">*</span></label>
                        <input type="text" id="quick_auth_name" name="name" class="form-control form-control-sm" 
                               placeholder="e.g. Humayun Ahmed / Muhammad Zafar Iqbal" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Phone Number</label>
                        <input type="text" id="quick_auth_phone" name="phone" class="form-control form-control-sm" placeholder="01XXXXXXXXX">
                    </div>
                    <div>
                        <label class="form-label small fw-semibold">Biography (Bio)</label>
                        <textarea id="quick_auth_bio" name="bio" rows="2" class="form-control form-control-sm" placeholder="Author short biography..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="quickAuthBtn" class="btn btn-sm btn-primary">
                        <i class="fas fa-check-circle me-1"></i> Save Author
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 4: BLOG LITERARY READER LIVE PREVIEW                                --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="blogLivePreviewModal" tabindex="-1" aria-labelledby="blogLivePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-dark text-white py-3 px-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary text-white p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="blogLivePreviewModalLabel">Article Reader Live Preview</h6>
                        <small class="text-white-50">How readers will see this content on the public site</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="adjustPreviewFontSize(-1)" title="Decrease font size">A-</button>
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="adjustPreviewFontSize(1)" title="Increase font size">A+</button>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-4 p-md-5" style="background: #faf8f5; font-family: 'Hind Siliguri', 'Kalpurush', sans-serif;">
                <article class="mx-auto" style="max-width: 820px; background: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 35px rgba(0,0,0,0.06); border: 1px solid #ebd9c8;">
                    
                    {{-- Header Meta --}}
                    <div class="text-center pb-3 mb-4 border-bottom border-warning border-opacity-50">
                        <span id="prevBlogCategory" class="badge bg-primary bg-opacity-10 text-primary px-3 py-1.5 rounded-pill mb-2.5 fw-semibold" style="font-size: 13px;">Literature & Culture</span>
                        <h1 id="prevBlogTitle" class="fw-bold text-dark mb-2" style="font-size: 2.2rem; line-height: 1.35; font-family: 'Hind Siliguri', serif; color: #1e293b;">Loading title...</h1>
                        <p id="prevBlogSubtitle" class="text-muted fst-italic fs-6 mb-3 d-none"></p>

                        <div class="d-flex flex-wrap align-items-center justify-content-center gap-3 text-muted small mt-2 pt-2 border-top">
                            <span><i class="fas fa-pen-nib text-primary me-1"></i> <strong id="prevBlogAuthor" class="text-dark">Author</strong></span>
                            <span>•</span>
                            <span><i class="far fa-calendar-alt me-1"></i> {{ now()->format('d M, Y') }}</span>
                            <span>•</span>
                            <span><i class="far fa-clock me-1"></i> 3 min read</span>
                        </div>
                    </div>

                    {{-- Featured Photocard / Cover Preview --}}
                    <div id="prevBlogCoverWrapper" class="text-center mb-4 d-none">
                        <img id="prevBlogCoverImg" src="" alt="Cover Preview" class="img-fluid rounded-3 shadow-xs border" style="max-height: 380px; width: auto; object-fit: cover;">
                    </div>

                    {{-- Excerpt Callout --}}
                    <div id="prevBlogExcerptWrapper" class="p-3 mb-4 rounded-3 border-start border-4 border-primary bg-light d-none" style="font-style: italic; color: #475569; font-size: 1.05rem; line-height: 1.7;">
                        <span id="prevBlogExcerpt"></span>
                    </div>

                    {{-- Content Body --}}
                    <div id="prevBlogContentBody" class="fs-5 text-dark" style="line-height: 2.0; word-break: break-word; color: #2d3748;">
                        Loading content...
                    </div>

                    {{-- Sign-off ornament --}}
                    <div class="text-center my-4 text-muted" style="letter-spacing: 4px; font-size: 1.1rem;">
                        ❖ ─── ✦ ─── ❖
                    </div>
                </article>
            </div>

            <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-between">
                <span class="text-muted small"><i class="fas fa-check-circle text-success me-1"></i> Formats and stanzas verified live</span>
                <button type="button" class="btn btn-sm btn-dark px-4 rounded-pill" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Pricing Engine Interactive Calculations (Rokomari-style)
function onMainPriceChange() {
    const mrp = parseFloat(document.getElementById('f-price')?.value) || 0;
    const purchDiscPct = parseFloat(document.getElementById('f-purchase_discount_percent')?.value) || 0;
    
    if (mrp > 0 && purchDiscPct > 0) {
        const cost = mrp - (mrp * (purchDiscPct / 100));
        const costInput = document.getElementById('f-cost_price');
        if (costInput) costInput.value = cost.toFixed(2);
    }
    
    calculateLiveSummaryPricing();
    updateLiveMockupCard();
}

function onPurchaseDiscountPercentChange() {
    const mrp = parseFloat(document.getElementById('f-price')?.value) || 0;
    const purchDiscPct = parseFloat(document.getElementById('f-purchase_discount_percent')?.value) || 0;
    if (mrp > 0) {
        const cost = mrp - (mrp * (purchDiscPct / 100));
        const costInput = document.getElementById('f-cost_price');
        if (costInput) costInput.value = cost.toFixed(2);
    }
    calculateLiveSummaryPricing();
}

function onCostPriceChange() {
    const mrp = parseFloat(document.getElementById('f-price')?.value) || 0;
    const cost = parseFloat(document.getElementById('f-cost_price')?.value) || 0;
    if (mrp > 0 && cost > 0 && cost <= mrp) {
        const pct = ((mrp - cost) / mrp) * 100;
        const purchInput = document.getElementById('f-purchase_discount_percent');
        if (purchInput) purchInput.value = pct.toFixed(1);
    }
    calculateLiveSummaryPricing();
}

function onSoldPercentChange() {
    calculateLiveSummaryPricing();
    updateLiveMockupCard();
}

function calculateLiveSummaryPricing() {
    const mrp = parseFloat(document.getElementById('f-price')?.value) || 0;
    const soldPct = parseFloat(document.getElementById('f-sold_percent')?.value) || 0;
    const cost = parseFloat(document.getElementById('f-cost_price')?.value) || 0;
    
    const offerPrice = soldPct > 0 ? (mrp - (mrp * (soldPct / 100))) : mrp;
    const offerSpan = document.getElementById('liveCalculatedOfferPrice');
    if (offerSpan) offerSpan.textContent = '৳' + offerPrice.toFixed(2);
    
    const profitSpan = document.getElementById('liveCalculatedProfit');
    if (profitSpan) {
        const profit = offerPrice - cost;
        const profitPct = cost > 0 ? ((profit / cost) * 100).toFixed(1) : 0;
        profitSpan.textContent = '৳' + profit.toFixed(2) + ' (' + profitPct + '%)';
    }
}

function toggleLookInsideFormat(val) {
    const pdfPanel = document.getElementById('lookInsidePdfPanel');
    const imgPanel = document.getElementById('lookInsideImagesPanel');
    if (val === 'images') {
        if (pdfPanel) pdfPanel.classList.add('d-none');
        if (imgPanel) imgPanel.classList.remove('d-none');
    } else {
        if (pdfPanel) pdfPanel.classList.remove('d-none');
        if (imgPanel) imgPanel.classList.add('d-none');
    }
}

function previewAdminMultiImages(input) {
    const container = document.getElementById('multiImagesPreviewContainer');
    if (!container) return;
    container.innerHTML = '';
    if (input.files && input.files.length > 0) {
        Array.from(input.files).forEach((file, idx) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const thumb = document.createElement('div');
                thumb.className = 'position-relative border rounded p-1 bg-white shadow-xs';
                thumb.style.width = '60px';
                thumb.style.height = '85px';
                thumb.innerHTML = `<img src="${e.target.result}" class="w-100 h-100 object-fit-cover rounded"><span class="badge bg-dark position-absolute bottom-0 start-0 m-0.5" style="font-size: 8px;">#${idx+1}</span>`;
                container.appendChild(thumb);
            }
            reader.readAsDataURL(file);
        });
    }
}

function toggleAdminPreOrderFields(val) {
    const box = document.getElementById('adminPreOrderContainer');
    if (box) {
        if (val === 'pre_order') {
            box.classList.remove('d-none');
        } else {
            box.classList.add('d-none');
        }
    }
}

function syncCategorySelects(val) {
    const mainSel = document.getElementById('f-category_id');
    const sideSel = document.getElementById('f-category_id_sidebar');
    if (mainSel && mainSel.value !== val) mainSel.value = val;
    if (sideSel && sideSel.value !== val) sideSel.value = val;
    updateLiveMockupCard();
}
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
    if (initHcPrice > 0 && initHcDisc > 0 && initHcDisc < initPrice) {
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

// Dynamic visual requirement indicators for book cover formats (Hardcover, Paperback, Both)
function updateCoverTypeRequirement() {
    const selectedRadio = document.querySelector('input[name="cover_type"]:checked');
    const val = selectedRadio ? selectedRadio.value : (document.getElementById('f-cover_type')?.value || 'paperback');

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
        if (badgeHc) { badgeHc.className = 'badge bg-white text-primary small px-2 py-0.5 rounded-pill'; badgeHc.textContent = 'Primary Edition'; }
        if (badgePb) { badgePb.className = 'badge bg-white text-secondary small px-2 py-0.5 rounded-pill'; badgePb.textContent = 'Optional Edition'; }
        if (starHc) starHc.style.display = 'inline';
        if (starPb) starPb.style.display = 'none';
        if (inputHc) inputHc.removeAttribute('required');
        if (inputPb) inputPb.removeAttribute('required');
    } else if (val === 'paperback') {
        if (cardHc) { cardHc.style.opacity = '0.78'; }
        if (cardPb) { cardPb.style.opacity = '1'; }
        if (headerPb) { headerPb.className = 'card-header bg-primary text-white py-2 px-3 d-flex align-items-center justify-content-between'; }
        if (badgeHc) { badgeHc.className = 'badge bg-white text-secondary small px-2 py-0.5 rounded-pill'; badgeHc.textContent = 'Optional Edition'; }
        if (badgePb) { badgePb.className = 'badge bg-white text-primary small px-2 py-0.5 rounded-pill'; badgePb.textContent = 'Primary Edition'; }
        if (starHc) starHc.style.display = 'none';
        if (starPb) starPb.style.display = 'inline';
        if (inputHc) inputHc.removeAttribute('required');
        if (inputPb) inputPb.removeAttribute('required');
    } else if (val === 'both') {
        if (cardHc) { cardHc.style.opacity = '1'; }
        if (cardPb) { cardPb.style.opacity = '1'; }
        if (headerPb) { headerPb.className = 'card-header bg-primary text-white py-2 px-3 d-flex align-items-center justify-content-between'; }
        if (badgeHc) { badgeHc.className = 'badge bg-white text-primary small px-2 py-0.5 rounded-pill'; badgeHc.textContent = 'Hardcover Required'; }
        if (badgePb) { badgePb.className = 'badge bg-white text-primary small px-2 py-0.5 rounded-pill'; badgePb.textContent = 'Paperback Required'; }
        if (starHc) starHc.style.display = 'inline';
        if (starPb) starPb.style.display = 'inline';
        if (inputHc) inputHc.removeAttribute('required');
        if (inputPb) inputPb.removeAttribute('required');
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
            badgeEl.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle p-1.5 w-100 d-flex align-items-center justify-content-between"><span><i class="fas fa-chart-line me-1"></i>Est. Profit: <strong>৳${profit.toFixed(2)}</strong></span> <span class="badge bg-success text-white">${margin}% Net Margin</span></span>`;
        } else {
            badgeEl.innerHTML = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle p-1.5 w-100"><i class="fas fa-triangle-exclamation me-1"></i>Warning: Cost exceeds selling price! Loss ৳${Math.abs(profit).toFixed(2)}</span>`;
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
            badgeEl.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle p-1.5 w-100 d-flex align-items-center justify-content-between"><span><i class="fas fa-chart-line me-1"></i>Est. Profit: <strong>৳${profit.toFixed(2)}</strong></span> <span class="badge bg-success text-white">${margin}% Net Margin</span></span>`;
        } else {
            badgeEl.innerHTML = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle p-1.5 w-100"><i class="fas fa-triangle-exclamation me-1"></i>Warning: Cost exceeds selling price! Loss ৳${Math.abs(profit).toFixed(2)}</span>`;
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
            badgeEl.innerHTML = `<i class="fas fa-tags me-1"></i> ${percent}% discount on paperback! Customer saves ৳${savings.toFixed(2)}`;
        } else if (discount === price) {
            badgeEl.className = 'mt-1 small fw-semibold text-muted';
            badgeEl.innerHTML = `No discount applied.`;
        } else {
            badgeEl.className = 'mt-1 small fw-semibold text-danger';
            badgeEl.innerHTML = `<i class="fas fa-triangle-exclamation me-1"></i> Warning: Discounted price exceeds original price!`;
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
            badgeEl.innerHTML = `<i class="fas fa-tags me-1"></i> ${percent}% discount on hardcover! Customer saves ৳${savings.toFixed(2)}`;
        } else if (discount === price) {
            badgeEl.className = 'mt-1 small fw-semibold text-muted';
            badgeEl.innerHTML = `No discount applied.`;
        } else {
            badgeEl.className = 'mt-1 small fw-semibold text-danger';
            badgeEl.innerHTML = `<i class="fas fa-triangle-exclamation me-1"></i> Hardcover discount price exceeds original price!`;
        }
    } else {
        badgeEl.innerHTML = '';
    }

    updateLiveMockupCard();
}

// ══════════════════════════════════════════════════════════════════════════════
// DYNAMIC MULTI-CONTRIBUTOR REPEATER MANAGERS (AUTHOR, TRANSLATOR, EDITOR, REWRITER)
// ══════════════════════════════════════════════════════════════════════════════
function addAuthorField() {
    const container = document.getElementById('authorsRepeaterContainer');
    if (!container) return;
    const authorLookups = @json($lookups['authors'] ?? []);
    let optionsHtml = '<option value="">— Directory —</option>';
    for (const [aId, aName] of Object.entries(authorLookups)) {
        optionsHtml += `<option value="${aId}">${aName}</option>`;
    }
    const div = document.createElement('div');
    div.className = 'input-group input-group-sm author-field-row';
    div.innerHTML = `
        <select name="author_ids[]" class="form-select form-select-sm" style="max-width: 140px;" onchange="onAuthorSelectRowChange(this)">
            ${optionsHtml}
        </select>
        <input type="text" name="author_names[]" class="form-control form-control-sm author-name-input" 
               placeholder="লেখকের নাম লিখুন..." oninput="updateLiveMockupCard()">
        <button type="button" class="btn btn-outline-danger" onclick="removeRepeaterRow(this); updateLiveMockupCard();">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

function onAuthorSelectRowChange(select) {
    const row = select.closest('.author-field-row');
    if (!row) return;
    const input = row.querySelector('.author-name-input');
    if (input && select.selectedIndex > 0) {
        input.value = select.options[select.selectedIndex].text.trim();
        updateLiveMockupCard();
    }
}

function addTranslatorField() {
    const container = document.getElementById('translatorsRepeaterContainer');
    if (!container) return;
    const div = document.createElement('div');
    div.className = 'input-group input-group-sm translator-field-row';
    div.innerHTML = `
        <input type="text" name="translator_names[]" class="form-control form-control-sm" placeholder="অনুবাদকের নাম...">
        <button type="button" class="btn btn-outline-danger" onclick="removeRepeaterRow(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

function addEditorField() {
    const container = document.getElementById('editorsRepeaterContainer');
    if (!container) return;
    const div = document.createElement('div');
    div.className = 'input-group input-group-sm editor-field-row';
    div.innerHTML = `
        <input type="text" name="editor_names[]" class="form-control form-control-sm" placeholder="সম্পাদকের নাম...">
        <button type="button" class="btn btn-outline-danger" onclick="removeRepeaterRow(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

function addRewriterField() {
    const container = document.getElementById('rewritersRepeaterContainer');
    if (!container) return;
    const div = document.createElement('div');
    div.className = 'input-group input-group-sm rewriter-field-row';
    div.innerHTML = `
        <input type="text" name="rewriter_names[]" class="form-control form-control-sm" placeholder="পুনর্লিখনকারী / রূপান্তরকারীর নাম...">
        <button type="button" class="btn btn-outline-danger" onclick="removeRepeaterRow(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

function removeRepeaterRow(btn) {
    const row = btn.closest('.input-group');
    if (row) {
        row.remove();
    }
}

// Sync Book Height & Width cm to combined size
function syncBookSizeCombined() {
    const h = document.getElementById('f-book_height_cm')?.value?.trim();
    const w = document.getElementById('f-book_width_cm')?.value?.trim();
    const hiddenSize = document.getElementById('f-book_size');
    if (!hiddenSize) return;
    if (h && w) {
        hiddenSize.value = `${h} cm × ${w} cm`;
    } else if (h) {
        hiddenSize.value = `${h} cm`;
    } else if (w) {
        hiddenSize.value = `${w} cm`;
    } else {
        hiddenSize.value = '';
    }
}

// ══════════════════════════════════════════════════════════════════════════════
// INDEPENDENT DUAL PRICING ENGINE (PAPERBACK & HARDCOVER)
// ══════════════════════════════════════════════════════════════════════════════
function onCoverTypeDropdownChange(binding) {
    const pbPanel = document.getElementById('paperbackPricingPanel');
    const hcPanel = document.getElementById('hardcoverPricingPanel');
    const badge = document.getElementById('pricingBindingBadge');

    if (binding === 'hardcover') {
        if (pbPanel) pbPanel.classList.add('d-none');
        if (hcPanel) hcPanel.classList.remove('d-none');
        if (badge) badge.textContent = 'Hardcover Mode';
    } else if (binding === 'both') {
        if (pbPanel) pbPanel.classList.remove('d-none');
        if (hcPanel) hcPanel.classList.remove('d-none');
        if (badge) badge.textContent = 'Dual Mode (Hard & Paperback)';
    } else {
        if (pbPanel) pbPanel.classList.remove('d-none');
        if (hcPanel) hcPanel.classList.add('d-none');
        if (badge) badge.textContent = 'Paperback Mode';
    }
    updateLiveMockupCard();
}

// 1. PAPERBACK PRICING HANDLERS
function onPaperbackPriceChange() {
    const price = parseFloat(document.getElementById('f-price')?.value) || 0;
    const pDiscPct = parseFloat(document.getElementById('f-purchase_discount_percent')?.value) || 0;
    const costInput = document.getElementById('f-cost_price');

    if (price > 0 && pDiscPct > 0 && pDiscPct <= 100) {
        const costVal = Math.round(price * (1 - pDiscPct / 100) * 100) / 100;
        if (costInput) costInput.value = costVal;
    }
    updatePaperbackCalculations();
    updateLiveMockupCard();
}

function onPaperbackPurchaseDiscountChange() {
    const price = parseFloat(document.getElementById('f-price')?.value) || 0;
    const pDiscPct = parseFloat(document.getElementById('f-purchase_discount_percent')?.value) || 0;
    const costInput = document.getElementById('f-cost_price');

    if (price > 0 && pDiscPct >= 0 && pDiscPct <= 100) {
        const costVal = Math.round(price * (1 - pDiscPct / 100) * 100) / 100;
        if (costInput) costInput.value = costVal;
    }
    updatePaperbackCalculations();
}

function onPaperbackCostChange() {
    const price = parseFloat(document.getElementById('f-price')?.value) || 0;
    const cost = parseFloat(document.getElementById('f-cost_price')?.value) || 0;
    const pDiscInput = document.getElementById('f-purchase_discount_percent');

    if (price > 0 && cost > 0 && cost < price) {
        const pct = Math.round(((price - cost) / price) * 100);
        if (pDiscInput) pDiscInput.value = pct;
    }
    updatePaperbackCalculations();
}

function onPaperbackSoldPercentChange() {
    updatePaperbackCalculations();
    updateLiveMockupCard();
}

function updatePaperbackCalculations() {
    const price = parseFloat(document.getElementById('f-price')?.value) || 0;
    const soldPct = parseFloat(document.getElementById('f-sold_percent')?.value) || 0;
    const cost = parseFloat(document.getElementById('f-cost_price')?.value) || 0;

    const offerEl = document.getElementById('liveCalculatedOfferPrice');
    const profitEl = document.getElementById('liveCalculatedProfit');
    const discHidden = document.getElementById('f-discount_price');

    let offerPrice = price;
    if (price > 0 && soldPct > 0 && soldPct <= 100) {
        offerPrice = Math.round(price * (1 - soldPct / 100) * 100) / 100;
    }
    if (discHidden) {
        discHidden.value = (offerPrice < price) ? offerPrice : '';
    }

    if (offerEl) {
        offerEl.textContent = '৳' + offerPrice.toFixed(2);
    }

    if (profitEl) {
        if (offerPrice > 0 && cost > 0) {
            const profit = offerPrice - cost;
            const margin = Math.round((profit / offerPrice) * 1000) / 10;
            if (profit >= 0) {
                profitEl.className = 'text-success fw-bold';
                profitEl.textContent = `৳${profit.toFixed(2)} (${margin}%)`;
            } else {
                profitEl.className = 'text-danger fw-bold';
                profitEl.textContent = `Loss ৳${Math.abs(profit).toFixed(2)} (${margin}%)`;
            }
        } else {
            profitEl.textContent = '৳0.00 (0%)';
        }
    }
}

// 2. HARDCOVER PRICING HANDLERS (SEPARATE & INDEPENDENT)
function onHardcoverPriceChange() {
    const hardPrice = parseFloat(document.getElementById('f-hardcover_price')?.value) || 0;
    const pDiscPct = parseFloat(document.getElementById('f-hardcover_purchase_discount_percent')?.value) || 0;
    const costInput = document.getElementById('f-hardcover_cost_price');

    if (hardPrice > 0 && pDiscPct > 0 && pDiscPct <= 100) {
        const costVal = Math.round(hardPrice * (1 - pDiscPct / 100) * 100) / 100;
        if (costInput) costInput.value = costVal;
    }
    updateHardcoverCalculations();
    updateLiveMockupCard();
}

function onHardcoverPurchaseDiscountChange() {
    const hardPrice = parseFloat(document.getElementById('f-hardcover_price')?.value) || 0;
    const pDiscPct = parseFloat(document.getElementById('f-hardcover_purchase_discount_percent')?.value) || 0;
    const costInput = document.getElementById('f-hardcover_cost_price');

    if (hardPrice > 0 && pDiscPct >= 0 && pDiscPct <= 100) {
        const costVal = Math.round(hardPrice * (1 - pDiscPct / 100) * 100) / 100;
        if (costInput) costInput.value = costVal;
    }
    updateHardcoverCalculations();
}

function onHardcoverCostChange() {
    const hardPrice = parseFloat(document.getElementById('f-hardcover_price')?.value) || 0;
    const cost = parseFloat(document.getElementById('f-hardcover_cost_price')?.value) || 0;
    const pDiscInput = document.getElementById('f-hardcover_purchase_discount_percent');

    if (hardPrice > 0 && cost > 0 && cost < hardPrice) {
        const pct = Math.round(((hardPrice - cost) / hardPrice) * 100);
        if (pDiscInput) pDiscInput.value = pct;
    }
    updateHardcoverCalculations();
}

function onHardcoverSoldPercentChange() {
    updateHardcoverCalculations();
    updateLiveMockupCard();
}

function updateHardcoverCalculations() {
    const hardPrice = parseFloat(document.getElementById('f-hardcover_price')?.value) || 0;
    const soldPct = parseFloat(document.getElementById('f-hardcover_sold_percent')?.value) || 0;
    const cost = parseFloat(document.getElementById('f-hardcover_cost_price')?.value) || 0;

    const offerEl = document.getElementById('liveHardcoverOfferPrice');
    const profitEl = document.getElementById('liveHardcoverProfit');
    const discHidden = document.getElementById('f-hardcover_discount_price');

    let offerPrice = hardPrice;
    if (hardPrice > 0 && soldPct > 0 && soldPct <= 100) {
        offerPrice = Math.round(hardPrice * (1 - soldPct / 100) * 100) / 100;
    }
    if (discHidden) {
        discHidden.value = (offerPrice < hardPrice) ? offerPrice : '';
    }

    if (offerEl) {
        offerEl.textContent = '৳' + offerPrice.toFixed(2);
    }

    if (profitEl) {
        if (offerPrice > 0 && cost > 0) {
            const profit = offerPrice - cost;
            const margin = Math.round((profit / offerPrice) * 1000) / 10;
            if (profit >= 0) {
                profitEl.className = 'text-success fw-bold';
                profitEl.textContent = `৳${profit.toFixed(2)} (${margin}%)`;
            } else {
                profitEl.className = 'text-danger fw-bold';
                profitEl.textContent = `Loss ৳${Math.abs(profit).toFixed(2)} (${margin}%)`;
            }
        } else {
            profitEl.textContent = '৳0.00 (0%)';
        }
    }
}

// Aliases for legacy calls
function onMainPriceChange() { onPaperbackPriceChange(); }
function onPurchaseDiscountPercentChange() { onPaperbackPurchaseDiscountChange(); }
function onCostPriceChange() { onPaperbackCostChange(); }
function onSoldPercentChange() { onPaperbackSoldPercentChange(); }
function updateRokomariCalculations() { updatePaperbackCalculations(); }

// Toggle format for look inside
function toggleLookInsideFormat(type) {
    const pdfPanel = document.getElementById('lookInsidePdfPanel');
    const imagesPanel = document.getElementById('lookInsideImagesPanel');
    if (pdfPanel && imagesPanel) {
        if (type === 'images') {
            pdfPanel.classList.add('d-none');
            imagesPanel.classList.remove('d-none');
        } else {
            pdfPanel.classList.remove('d-none');
            imagesPanel.classList.add('d-none');
        }
    }
}

function toggleAdminPreOrderFields(stockStatus) {
    const container = document.getElementById('adminPreOrderContainer');
    if (!container) return;
    if (stockStatus === 'pre_order') {
        container.classList.remove('d-none');
    } else {
        container.classList.add('d-none');
    }
}

// Preview Multi Images
function previewAdminMultiImages(input) {
    const container = document.getElementById('multiImagesPreviewContainer');
    const summary = document.getElementById('multiImagesSummaryReport');
    const countText = document.getElementById('multiImagesCountText');
    if (!container || !input.files) return;
    container.innerHTML = '';

    const count = input.files.length;
    if (count > 0) {
        if (summary) summary.classList.remove('d-none');
        if (countText) countText.textContent = count;
    } else {
        if (summary) summary.classList.add('d-none');
    }

    Array.from(input.files).forEach((file, idx) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const badge = document.createElement('div');
                badge.className = 'position-relative border rounded-3 p-1 text-center bg-white shadow-xs';
                badge.style.width = '74px';
                badge.innerHTML = `
                    <div class="position-relative rounded overflow-hidden" style="height: 64px;">
                        <img src="${e.target.result}" class="w-100 h-100 object-fit-cover rounded">
                        <span class="badge bg-dark position-absolute top-0 start-0 m-0.5" style="font-size: 8px;">#${idx + 1}</span>
                    </div>
                    <div class="text-dark fw-semibold text-truncate mt-1" style="font-size: 9.5px;" title="${file.name}">Page ${idx + 1}</div>
                    <div class="text-muted" style="font-size: 8.5px;">${(file.size/1024).toFixed(0)} KB</div>
                `;
                container.appendChild(badge);
            };
            reader.readAsDataURL(file);
        }
    });
}

function clearAdminMultiImages() {
    const input = document.getElementById('f-look_inside_images');
    if (input) input.value = '';
    const container = document.getElementById('multiImagesPreviewContainer');
    if (container) container.innerHTML = '';
    const summary = document.getElementById('multiImagesSummaryReport');
    if (summary) summary.classList.add('d-none');
}

function previewAdminCoverInput(input) {
    previewAdminFileInput(input, 'preview-container-cover_image');
}

function previewAdminPdfInput(input) {
    previewAdminFileInput(input, 'preview-container-sample_pdf_path');
}

function clearAdminFileInput(inputId, containerId, mockupImgId) {
    const input = document.getElementById(inputId);
    if (input) input.value = '';
    const container = document.getElementById(containerId);
    if (container) container.classList.add('d-none');
    if (mockupImgId) {
        const mockup = document.getElementById(mockupImgId);
        if (mockup) mockup.src = 'https://placehold.co/300x450/e2e8f0/475569?text=Cover+Image';
    }
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
    mockTitle.textContent = titleVal || 'Title';

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
    const ownerNameInput = document.getElementById('f-owner_name');
    const authorIdSelect = document.getElementById('f-author_id');
    const dirRadio = document.getElementById('author-mode-directory');

    if (ownerNameInput && ownerNameInput.value.trim()) {
        authorVal = ownerNameInput.value.trim();
    } else if (dirRadio && dirRadio.checked && authorSelect && authorSelect.selectedIndex > 0) {
        authorVal = authorSelect.options[authorSelect.selectedIndex].text.replace(/\[.*?\]/, '').trim();
    } else if (authorIdSelect && authorIdSelect.selectedIndex > 0) {
        authorVal = authorIdSelect.options[authorIdSelect.selectedIndex].text.replace(/\[.*?\]/, '').trim();
    } else if (authorCustom && authorCustom.value.trim()) {
        authorVal = authorCustom.value.trim();
    } else {
        const firstAuthorInput = document.querySelector('input[name="author_names[]"]');
        if (firstAuthorInput && firstAuthorInput.value.trim()) {
            authorVal = firstAuthorInput.value.trim();
        }
    }
    if (mockAuthor) {
        mockAuthor.innerHTML = '<i class="fas fa-pen-nib text-success me-1"></i><span>' + (authorVal || 'Author Name') + '</span>';
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
        let formatLabel = 'Paperback';

        if (coverType === 'hardcover') {
            formatLabel = 'Hardcover';
            if (hcPrice > 0) {
                displayOrig = hcPrice;
                displayPrice = (hcDisc > 0 && hcDisc < hcPrice) ? hcDisc : hcPrice;
            } else if (pbPrice > 0) {
                displayOrig = pbPrice;
                displayPrice = (pbDisc > 0 && pbDisc < pbPrice) ? pbDisc : pbPrice;
            }
        } else if (coverType === 'both') {
            formatLabel = 'Both Editions';
            if (hcPrice > 0) {
                displayOrig = hcPrice;
                displayPrice = (hcDisc > 0 && hcDisc < hcPrice) ? hcDisc : hcPrice;
            } else if (pbPrice > 0) {
                displayOrig = pbPrice;
                displayPrice = (pbDisc > 0 && pbDisc < pbPrice) ? pbDisc : pbPrice;
            }
        } else {
            formatLabel = 'Paperback';
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
            mockFinal.textContent = '৳' + displayPrice.toFixed(2);
            if (mockOriginal) {
                mockOriginal.textContent = '৳' + displayOrig.toFixed(2);
                mockOriginal.classList.remove('d-none');
            }
            const percent = Math.round(((displayOrig - displayPrice) / displayOrig) * 100);
            if (mockBadge) {
                mockBadge.textContent = '-' + percent + '%';
                mockBadge.classList.remove('d-none');
            }
        } else if (displayPrice > 0) {
            mockFinal.textContent = '৳' + displayPrice.toFixed(2);
            if (mockOriginal) mockOriginal.classList.add('d-none');
            if (mockBadge) mockBadge.classList.add('d-none');
        } else {
            mockFinal.textContent = '৳0';
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

    if (countEl) countEl.textContent = count;

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
            warningEl.innerHTML = `<i class="fas fa-triangle-exclamation me-1"></i> Word limit exceeded! (${count - maxWords} words extra)`;
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

function previewAdminFileInput(input, containerId) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const name = input.name;
    const container = document.getElementById(containerId);

    const img = document.getElementById('preview-img-' + name);
    const fname = document.getElementById('preview-filename-' + name);
    const fsize = document.getElementById('preview-filesize-' + name);
    const mockupImg = document.getElementById('mockupCoverImg');

    const formattedSize = file.size >= 1048576 
        ? (file.size / (1024 * 1024)).toFixed(2) + ' MB' 
        : (file.size / 1024).toFixed(1) + ' KB';

    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            if (img) img.src = e.target.result;
            if (mockupImg && (name === 'cover_image' || name === 'image')) {
                mockupImg.src = e.target.result;
            }
            if (fname) fname.textContent = file.name;
            if (fsize) fsize.textContent = formattedSize + ' • ' + (file.type.split('/')[1] || 'IMAGE').toUpperCase();
            if (container) container.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    } else {
        if (img) img.src = '';
        if (fname) fname.textContent = file.name;
        if (fsize) fsize.textContent = formattedSize + ' • ' + (file.type ? file.type.toUpperCase() : 'PDF Document');
        if (container) container.classList.remove('d-none');
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

// Line Height & Spacing Control Functions
function changeLineSpacing(targetTextareaId, value) {
    if (!value) return;
    const fieldName = targetTextareaId.replace('f-', '');
    const editorDiv = document.getElementById('editable-' + fieldName);
    if (!editorDiv) return;

    editorDiv.style.lineHeight = value;
    const elements = editorDiv.querySelectorAll('p, div, blockquote, .poetry-verse');
    if (elements.length > 0) {
        elements.forEach(el => {
            el.style.lineHeight = value;
        });
    }
    syncEditorToTextarea(fieldName);
}

function adjustLineSpacing(targetTextareaId, delta) {
    const fieldName = targetTextareaId.replace('f-', '');
    const editorDiv = document.getElementById('editable-' + fieldName);
    if (!editorDiv) return;

    let curHeight = 1.55;
    const firstP = editorDiv.querySelector('p, div');
    if (firstP && firstP.style.lineHeight) {
        const pHeight = parseFloat(firstP.style.lineHeight);
        if (!isNaN(pHeight) && pHeight > 0) curHeight = pHeight;
    } else if (editorDiv.style.lineHeight) {
        const divHeight = parseFloat(editorDiv.style.lineHeight);
        if (!isNaN(divHeight) && divHeight > 0) curHeight = divHeight;
    }

    let newHeight = Math.max(1.15, Math.min(2.5, +(curHeight + delta).toFixed(2)));
    changeLineSpacing(targetTextareaId, newHeight.toString());
}

function changeParagraphSpacing(targetTextareaId, value) {
    if (!value) return;
    const fieldName = targetTextareaId.replace('f-', '');
    const editorDiv = document.getElementById('editable-' + fieldName);
    if (!editorDiv) return;

    const elements = editorDiv.querySelectorAll('p, div, blockquote, .poetry-verse');
    if (elements.length > 0) {
        elements.forEach(el => {
            el.style.marginBottom = value;
        });
    }
    syncEditorToTextarea(fieldName);
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
        alert('Please select or paste poetry text into the editor.');
        return;
    }

    const stanzas = selectedText.trim().split(/\r\n\r\n|\n\n+/);
    const formattedHtml = stanzas.map(stanza => {
        const lines = stanza.split(/\r\n|\n|\r/).map(line => {
            const temp = document.createElement('div');
            temp.textContent = line.trim();
            return temp.innerHTML;
        }).join('<br>');
        return `<p class="poetry-verse" style="line-height: 1.45; margin-bottom: 0.85rem; text-align: left; font-family: inherit;">${lines}</p>`;
    }).join('');

    if (sel && sel.rangeCount > 0 && sel.toString()) {
        document.execCommand('insertHTML', false, formattedHtml);
    } else {
        editorDiv.innerHTML = formattedHtml;
    }
    syncEditorToTextarea(fieldName);
    updateEditorStats(fieldName);
}

function formatProseMode(targetTextareaId) {
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
        alert('Please select prose/essay text to format.');
        return;
    }

    const paragraphs = selectedText.trim().split(/\r\n\r\n|\n\n+/);
    const formattedHtml = paragraphs.map(p => {
        const temp = document.createElement('div');
        temp.textContent = p.trim().replace(/\s+/g, ' ');
        return `<p style="line-height: 1.6; margin-bottom: 0.85rem; text-align: justify;">${temp.innerHTML}</p>`;
    }).join('');

    if (sel && sel.rangeCount > 0 && sel.toString()) {
        document.execCommand('insertHTML', false, formattedHtml);
    } else {
        editorDiv.innerHTML = formattedHtml;
    }
    syncEditorToTextarea(fieldName);
    updateEditorStats(fieldName);
}

function formatFixLineBreaks(targetTextareaId) {
    const fieldName = targetTextareaId.replace('f-', '');
    const editorDiv = document.getElementById('editable-' + fieldName);
    if (!editorDiv) return;

    let text = editorDiv.innerText || editorDiv.textContent;
    if (!text || !text.trim()) {
        alert('No text found in editor.');
        return;
    }

    const stanzas = text.trim().split(/\r\n\r\n|\n\n+/);
    const formattedHtml = stanzas.map(stanza => {
        const lines = stanza.split(/\r\n|\n|\r/).map(line => {
            const temp = document.createElement('div');
            temp.textContent = line.trim();
            return temp.innerHTML;
        }).join('<br>');
        return `<p style="line-height: 1.45; margin-bottom: 0.85rem;">${lines}</p>`;
    }).join('');

    editorDiv.innerHTML = formattedHtml;
    syncEditorToTextarea(fieldName);
    updateEditorStats(fieldName);
    alert('Paragraph and line spacing fixed successfully!');
}

function updateEditorStats(fieldName) {
    const editorDiv = document.getElementById('editable-' + fieldName);
    const statsBox = document.getElementById('editorWordStats-' + fieldName);
    if (!editorDiv || !statsBox) return;

    const text = (editorDiv.innerText || editorDiv.textContent || '').trim();
    const words = text ? text.split(/\s+/).length : 0;
    const chars = text.length;
    statsBox.innerHTML = `<i class="fas fa-file-alt text-primary me-1"></i>Words: ${words} | Chars: ${chars}`;
}

function openBlogLivePreviewModal(targetTextareaId) {
    const fieldName = targetTextareaId.replace('f-', '');
    const editorDiv = document.getElementById('editable-' + fieldName);
    if (!editorDiv) return;

    // Pull form values
    const titleVal = document.getElementById('f-title')?.value || document.getElementById('f-name')?.value || 'Untitled';
    const subVal = document.getElementById('f-subtitle')?.value || '';
    const excerptVal = document.getElementById('f-excerpt')?.value || '';
    
    // Author
    const customAuthor = document.getElementById('f-owner_name')?.value?.trim();
    const authorSelect = document.getElementById('f-author_id');
    const selectedAuthorText = authorSelect && authorSelect.selectedIndex > 0 ? authorSelect.options[authorSelect.selectedIndex].text.replace(/\[.*?\]/, '').trim() : '';
    const authorName = customAuthor || selectedAuthorText || 'Editorial Desk';

    // Category
    const catSelect = document.getElementById('f-category_id');
    const catName = catSelect && catSelect.selectedIndex > 0 ? catSelect.options[catSelect.selectedIndex].text.replace(/—\s*/g, '').trim() : 'Literature & Culture';

    // Cover Image
    const coverPreviewImg = document.getElementById('preview-img-image') || document.getElementById('preview-img-cover_image') || document.getElementById('mockupCoverImg');
    const coverSrc = coverPreviewImg ? coverPreviewImg.src : '';

    // Assign to modal
    document.getElementById('prevBlogTitle').textContent = titleVal;
    
    const subEl = document.getElementById('prevBlogSubtitle');
    if (subVal) {
        subEl.textContent = subVal;
        subEl.classList.remove('d-none');
    } else {
        subEl.classList.add('d-none');
    }

    document.getElementById('prevBlogAuthor').textContent = authorName;
    document.getElementById('prevBlogCategory').textContent = catName;

    const coverWrap = document.getElementById('prevBlogCoverWrapper');
    const modalCoverImg = document.getElementById('prevBlogCoverImg');
    if (coverSrc && coverSrc.length > 20 && !coverSrc.includes('placeholder')) {
        modalCoverImg.src = coverSrc;
        coverWrap.classList.remove('d-none');
    } else {
        coverWrap.classList.add('d-none');
    }

    const excerptWrap = document.getElementById('prevBlogExcerptWrapper');
    const excerptEl = document.getElementById('prevBlogExcerpt');
    if (excerptVal) {
        excerptEl.textContent = excerptVal;
        excerptWrap.classList.remove('d-none');
    } else {
        excerptWrap.classList.add('d-none');
    }

    document.getElementById('prevBlogContentBody').innerHTML = editorDiv.innerHTML;

    // Show modal
    const modalEl = document.getElementById('blogLivePreviewModal');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
}

let previewCurrentFontSize = 1.15;
function adjustPreviewFontSize(delta) {
    previewCurrentFontSize = Math.max(0.85, Math.min(1.8, previewCurrentFontSize + delta * 0.1));
    const contentEl = document.getElementById('prevBlogContentBody');
    if (contentEl) {
        contentEl.style.fontSize = previewCurrentFontSize + 'rem';
    }
}

function onBlogAuthorDropdownChange(select) {
    if (!select) return;
    const badgeText = document.getElementById('currentAuthorBadgeText');
    const ownerInput = document.getElementById('f-owner_name');

    if (select.selectedIndex > 0) {
        const fullText = select.options[select.selectedIndex].text;
        const cleanName = fullText.replace(/\[.*?\]/, '').trim();
        if (badgeText) badgeText.textContent = cleanName;
        if (ownerInput) {
            ownerInput.value = cleanName;
        }
    }
    updateLiveMockupCard();
}

function insertLinkPrompt(targetTextareaId) {
    const url = prompt("Enter Link URL:", "https://");
    if (url && url !== "https://") {
        formatDoc('createLink', url, targetTextareaId);
    }
}

function insertImagePrompt(targetTextareaId) {
    const url = prompt("Enter Direct Image URL:", "https://");
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
        form.addEventListener('submit', function(e) {
            const titleInput = document.getElementById('f-title') || document.getElementById('f-name');
            if (titleInput && !titleInput.value.trim()) {
                e.preventDefault();
                titleInput.focus();
                titleInput.classList.add('is-invalid');
                alert('অনুগ্রহ করে শিরোনাম / নাম (Title) পূরণ করুন।');
                return false;
            }

            const catSelect = document.getElementById('f-category_id');
            if (catSelect && !catSelect.value) {
                e.preventDefault();
                catSelect.focus();
                catSelect.classList.add('is-invalid');
                alert('অনুগ্রহ করে ক্যাটাগরি (Category) নির্বাচন করুন।');
                return false;
            }

            document.querySelectorAll('.rich-editor-content').forEach(function(editor) {
                const name = editor.id.replace('editable-', '');
                const textarea = document.getElementById('f-' + name);
                if (textarea) {
                    textarea.value = editor.innerHTML;
                }
            });
            if (typeof syncBookSizeCombined === 'function') {
                syncBookSizeCombined();
            }
            if (typeof syncActiveCostPrice === 'function') {
                syncActiveCostPrice();
            }

            // Provide visual feedback
            const submitBtns = document.querySelectorAll('button[type="submit"][form="contentMainForm"], #contentMainForm button[type="submit"]');
            submitBtns.forEach(function(btn) {
                btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1.5" role="status" aria-hidden="true"></span> সংরক্ষন করা হচ্ছে...`;
            });
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
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
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
            // Append to all category dropdowns and select
            const catSelects = [
                document.getElementById('f-category_id'),
                document.getElementById('f-category_id_sidebar')
            ];
            catSelects.forEach(sel => {
                if (sel) {
                    const opt = new Option(data.item.display_name, data.item.id, true, true);
                    sel.add(opt);
                    sel.value = data.item.id;
                }
            });
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

            updateLiveMockupCard();
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">${data.message || 'An error occurred'}</div>`;
        }
    })
    .catch(err => {
        alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">Server error. Please try again.</div>`;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Save Category';
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
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
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
            alert('Blog Category created and selected successfully!');
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">${data.message || 'An error occurred'}</div>`;
        }
    })
    .catch(err => {
        alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">Server error. Please try again.</div>`;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Save Blog Category';
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
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
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

            alert('Publisher added and selected successfully!');
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">${data.message || 'An error occurred'}</div>`;
        }
    })
    .catch(err => {
        alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">Server error.</div>`;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Save Publisher';
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
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
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
            alert('Author added and selected successfully!');
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">${data.message || 'An error occurred'}</div>`;
        }
    })
    .catch(err => {
        alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">Server error.</div>`;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Save Author';
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

    let authorOptionsHtml = '<option value="">— Select Author (Optional) —</option>';
    for (const [aId, aName] of Object.entries(authorOptionsJson)) {
        authorOptionsHtml += `<option value="${aId}">${aName}</option>`;
    }

    const tr = document.createElement('tr');
    tr.className = 'webzine-toc-row';
    tr.innerHTML = `
        <td class="text-center fw-bold text-muted row-number">${newIdx + 1}</td>
        <td>
            <input type="hidden" name="toc_articles[${newIdx}][order]" class="input-order" value="${nextOrder}">
            <input type="text" name="toc_articles[${newIdx}][title]" class="form-control form-control-sm" placeholder="e.g. New Article / Story..." required>
        </td>
        <td>
            <select name="toc_articles[${newIdx}][author_id]" class="form-select form-select-sm">
                ${authorOptionsHtml}
            </select>
        </td>
        <td>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light text-muted">Page</span>
                <input type="number" name="toc_articles[${newIdx}][page_number]" class="form-control form-control-sm text-center fw-bold" value="${nextOrder}" min="1" placeholder="1" required>
            </div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" onclick="removeWebzineTocRow(this)" title="Delete">
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

// ══════════════════════════════════════════════════════════════════════════════
// AUTO-FOCUS & SMOOTH SCROLL TO UNFILLED / INVALID REQUIRED FIELDS
// ══════════════════════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function () {
    // 1. Auto focus on page load if validation errors exist
    const firstError = document.querySelector('.is-invalid, .invalid-feedback:not(:empty)');
    if (firstError) {
        let target = firstError;
        if (firstError.classList.contains('invalid-feedback')) {
            target = firstError.previousElementSibling || firstError.closest('.input-group')?.querySelector('input, select, textarea') || firstError.closest('.col-12, .col-md-6')?.querySelector('input, select, textarea') || firstError;
        }
        if (target && typeof target.focus === 'function') {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => {
                target.focus({ preventScroll: true });
                highlightField(target);
            }, 300);
        }
    }

    // 2. Intercept submit and automatically jump/focus cursor to the exact unfilled required field
    const mainForm = document.getElementById('contentMainForm');
    if (mainForm) {
        mainForm.addEventListener('submit', function (e) {
            const isBookForm = {{ ($spec['key'] ?? '') === 'books' ? 'true' : 'false' }};
            if (isBookForm) {
                // Check Title
                const titleInput = document.getElementById('f-title');
                if (titleInput && (!titleInput.value || titleInput.value.trim() === '')) {
                    e.preventDefault();
                    titleInput.classList.add('is-invalid');
                    focusAndHighlight(titleInput, 'অনুগ্রহ করে বইয়ের নাম (Title) লিখুন।');
                    return false;
                }

                // Check Author Name
                const authorInputs = mainForm.querySelectorAll('input[name="author_names[]"]');
                let hasAuthor = false;
                authorInputs.forEach(inp => {
                    if (inp.value && inp.value.trim() !== '') hasAuthor = true;
                });
                const authorSelects = mainForm.querySelectorAll('select[name="author_ids[]"]');
                authorSelects.forEach(sel => {
                    if (sel.value && sel.value !== '') hasAuthor = true;
                });

                if (!hasAuthor && authorInputs.length > 0) {
                    e.preventDefault();
                    const firstAuthInput = authorInputs[0];
                    firstAuthInput.classList.add('is-invalid');
                    focusAndHighlight(firstAuthInput, 'অনুগ্রহ করে কমপক্ষে একজন লেখকের নাম (Author Name) লিখুন।');
                    return false;
                }

                // Check Category
                const catSelect = document.getElementById('f-category_id');
                if (catSelect && (!catSelect.value || catSelect.value === '')) {
                    e.preventDefault();
                    catSelect.classList.add('is-invalid');
                    focusAndHighlight(catSelect, 'অনুগ্রহ করে মূল ক্যাটাগরি (Category) নির্বাচন করুন।');
                    return false;
                }

                // Check Price (at least one price)
                const priceInput = document.getElementById('f-price');
                const hcPriceInput = document.getElementById('f-hardcover_price');
                const hasPrice = (priceInput && priceInput.value && parseFloat(priceInput.value) >= 0) ||
                                 (hcPriceInput && hcPriceInput.value && parseFloat(hcPriceInput.value) >= 0);
                if (!hasPrice) {
                    e.preventDefault();
                    const targetPrice = priceInput || hcPriceInput;
                    if (targetPrice) {
                        targetPrice.classList.add('is-invalid');
                        focusAndHighlight(targetPrice, 'অনুগ্রহ করে নিয়মিত বিক্রয় মূল্য (Price) উল্লেখ করুন।');
                        return false;
                    }
                }
            }
        });
    }
});

function focusAndHighlight(element, message = null) {
    if (!element) return;
    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(() => {
        try {
            element.focus({ preventScroll: true });
        } catch (err) {}
        highlightField(element);
        if (message) {
            showValidationToast(message);
        }
    }, 200);
}

function highlightField(el) {
    if (!el) return;
    el.classList.add('pulse-focus-highlight');
    setTimeout(() => el.classList.remove('pulse-focus-highlight'), 3000);
}

function showValidationToast(msg) {
    let toast = document.getElementById('formValidationToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'formValidationToast';
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:99999;background:#dc2626;color:#fff;padding:12px 20px;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.25);font-weight:600;font-size:13.5px;display:flex;align-items:center;gap:10px;transition:all 0.3s ease;';
        document.body.appendChild(toast);
    }
    toast.innerHTML = `<i class="fas fa-circle-exclamation fs-5"></i> <span>${msg}</span>`;
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
    }, 4500);
}
</script>

<style>
@keyframes pulseGlowRed {
    0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7); border-color: #dc2626; }
    50% { box-shadow: 0 0 0 8px rgba(220, 38, 38, 0.25); border-color: #dc2626; }
    100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); border-color: #dc2626; }
}
.pulse-focus-highlight {
    animation: pulseGlowRed 1.2s ease-in-out 2 !important;
    border-color: #dc2626 !important;
}
</style>
<script src="{{ asset('js/spellchecker.js') }}"></script>
@endpush

@endsection
