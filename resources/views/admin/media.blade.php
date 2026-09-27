@extends('layouts.admin')

@section('title', 'মিডিয়া ও ডিজিটাল অ্যাসেট স্টুডিও — Media & Asset Studio')
@section('heading', 'মিডিয়া ও ডিজিটাল অ্যাসেট স্টুডিও (Media & Asset Studio)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">মিডিয়া ও অ্যাসেট স্টুডিও</li>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-media.css') }}">
@endpush

@section('actions')
    <div class="d-flex align-items-center gap-2 flex-wrap">
        {{-- 1-Click Convert All Existing to WebP --}}
        <button type="button" class="btn btn-webp-gradient btn-sm shadow-xs" id="btnConvertAllWebp" onclick="openConvertWebpEngineModal()" title="বিদ্যমান সকল PNG ও JPG ফাইলকে WebP তে রূপান্তর করুন">
            <i class="fa-solid fa-bolt-lightning"></i>
            <span>সকল ফাইল WebP-তে রূপান্তর</span>
        </button>

        {{-- Create Folder Modal Trigger --}}
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#createFolderModal">
            <i class="fa-solid fa-folder-plus text-warning"></i>
            <span>নতুন ফোল্ডার</span>
        </button>

        {{-- 1-Click Auto Optimizer Engine --}}
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5" id="btnOptimizeAll" onclick="runMediaOptimization(this)">
            <i class="fa-solid fa-bolt text-success"></i>
            <span>অটো-অপ্টিমাইজেশন</span>
        </button>

        {{-- Upload Modal Trigger --}}
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 shadow-sm fw-bold d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#uploadMediaModal">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <span>মিডিয়া আপলোড</span>
        </button>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-3.5 pb-5">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-xs border-0 border-start border-4 border-success bg-white py-3 px-4" role="alert">
            <i class="fa-solid fa-circle-check text-success fs-4 me-3"></i>
            <div class="fw-semibold text-dark">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-xs border-0 border-start border-4 border-danger bg-white py-3 px-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation text-danger fs-4 me-3"></i>
            <div class="fw-semibold text-dark">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Dynamic Live Alert Container -->
    <div id="mediaLiveAlert"></div>

    <!-- Storage Statistics 4-Card Grid -->
    <div class="row g-3">
        {{-- Card 1: Total Assets --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="media-kpi-card border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-bold text-uppercase font-monospace">মোট অ্যাসেট ফাইল</span>
                    <div class="media-kpi-icon bg-primary-subtle text-primary">
                        <i class="fa-solid fa-images"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold mb-1 font-monospace">{{ number_format($totalCount) }} <span class="fs-6 fw-normal text-muted">টি ফাইল</span></h3>
                <div class="d-flex align-items-center justify-content-between text-muted small">
                    <span>ফিল্টারকৃত: <strong class="text-primary font-monospace">{{ number_format($filteredCount) }}</strong></span>
                    <span>ডিস্ক মেমোরি</span>
                </div>
            </div>
        </div>

        {{-- Card 2: Total Storage --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="media-kpi-card border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-bold text-uppercase font-monospace">মোট স্টোরেজ সাইজ</span>
                    <div class="media-kpi-icon bg-info-subtle text-info">
                        <i class="fa-solid fa-hard-drive"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold font-monospace mb-1">{{ $totalFormatted }}</h3>
                <div class="d-flex align-items-center justify-content-between text-muted small">
                    <span>পাবলিক ও স্টোরেজ ডিস্ক</span>
                    <span class="badge bg-light text-dark border font-monospace">SSD Cloud</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Modern WebP Adoption --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="media-kpi-card border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-bold text-uppercase font-monospace">Next-Gen ফরম্যাট</span>
                    <div class="media-kpi-icon bg-success-subtle text-success">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-1">
                    <h3 class="text-success fs-4 fw-bold font-monospace mb-0">{{ $webpPercent }}%</h3>
                    <span class="badge bg-success-subtle text-success font-monospace">{{ $webpCount }} WebP</span>
                </div>
                <div class="d-flex align-items-center justify-content-between text-muted small">
                    <span>হাই-স্পিড কম্প্রেশন</span>
                    <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-2 py-0.5 fw-bold" onclick="openConvertWebpEngineModal()">
                        <i class="fa-solid fa-bolt me-1"></i> রূপান্তর
                    </button>
                </div>
            </div>
        </div>

        {{-- Card 4: Engine Status --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="media-kpi-card border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-bold text-uppercase font-monospace">ইমেজ স্টুডিও ইঞ্জিন</span>
                    <div class="media-kpi-icon bg-warning-subtle text-warning-emphasis">
                        <i class="fa-solid fa-palette"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-5 fw-bold mb-1 d-flex align-items-center gap-1.5">
                    <span class="badge bg-success text-white rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-circle-check me-1"></i> HTML5 Canvas + GD
                    </span>
                </h3>
                <div class="text-muted small">রিসাইজ, রোটেট, ওয়াটারমার্ক ও ফিল্টার সক্রিয়</div>
            </div>
        </div>
    </div>

    <!-- Folder Navigation Horizontal Bar -->
    <div class="card bg-white rounded-4 shadow-sm border-0 p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="small text-muted fw-bold text-uppercase font-monospace">
                <i class="fa-solid fa-folder-tree text-primary me-1"></i> ফোল্ডার ডিরেক্টরি ক্যাটাগরি
            </span>
            <span class="small text-muted font-monospace">মোট {{ count($folderDefs) }} টি ডিরেক্টরি</span>
        </div>
        <div class="media-folder-bar">
            {{-- All Folders --}}
            <a href="{{ route('admin.media.index', array_merge(request()->query(), ['folder' => 'all'])) }}" class="media-folder-pill {{ $folderFilter === 'all' ? 'active' : '' }}">
                <i class="fa-solid fa-layer-group text-primary"></i>
                <span>সকল মিডিয়া</span>
                <span class="media-folder-count">{{ $totalCount }}</span>
            </a>

            @foreach($folderDefs as $k => $fInfo)
                @php
                    $fStat = $folderStats[$k] ?? ['count' => 0, 'formatted' => '0 B'];
                @endphp
                <a href="{{ route('admin.media.index', array_merge(request()->query(), ['folder' => $k])) }}" class="media-folder-pill {{ $folderFilter === $k ? 'active' : '' }}" title="{{ $fInfo['label'] }} ({{ $fStat['formatted'] }})">
                    <i class="{{ $fInfo['icon'] }}"></i>
                    <span>{{ $fInfo['label'] }}</span>
                    <span class="media-folder-count">{{ $fStat['count'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Advanced Filter, Search, Sorter & View Switcher Bar -->
    <div class="card bg-white rounded-4 shadow-sm border-0 p-3">
        <form action="{{ route('admin.media.index') }}" method="GET" id="mediaFilterForm">
            <input type="hidden" name="folder" value="{{ $folderFilter }}">
            <input type="hidden" name="view" id="mediaViewModeInput" value="{{ $viewMode }}">

            <div class="row g-2 align-items-center">
                {{-- Live Search Input --}}
                <div class="col-12 col-md-4 col-xl-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-primary">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="search" name="search" id="mediaLiveSearch" class="form-control border-start-0 ps-0 fw-semibold" 
                               placeholder="ফাইলের নাম বা ফোল্ডার খুঁজুন..." 
                               value="{{ $search }}" oninput="filterMediaLive(this.value)">
                    </div>
                </div>

                {{-- Format Filter --}}
                <div class="col-6 col-md-2 col-xl-2">
                    <select name="format" class="form-select form-select-sm rounded-3 fw-semibold" onchange="document.getElementById('mediaFilterForm').submit()">
                        <option value="all" {{ $formatFilter === 'all' ? 'selected' : '' }}>সব ফরম্যাট (All)</option>
                        <option value="webp" {{ $formatFilter === 'webp' ? 'selected' : '' }}>WebP</option>
                        <option value="png" {{ $formatFilter === 'png' ? 'selected' : '' }}>PNG</option>
                        <option value="jpg" {{ $formatFilter === 'jpg' ? 'selected' : '' }}>JPG / JPEG</option>
                        <option value="svg" {{ $formatFilter === 'svg' ? 'selected' : '' }}>SVG Vector</option>
                        <option value="gif" {{ $formatFilter === 'gif' ? 'selected' : '' }}>GIF Animation</option>
                    </select>
                </div>

                {{-- Dimension Filter --}}
                <div class="col-6 col-md-2 col-xl-2">
                    <select name="dim" class="form-select form-select-sm rounded-3 fw-semibold" onchange="document.getElementById('mediaFilterForm').submit()">
                        <option value="all" {{ $dimensionFilter === 'all' ? 'selected' : '' }}>সব রেজোলিউশন</option>
                        <option value="portrait" {{ $dimensionFilter === 'portrait' ? 'selected' : '' }}>বই / পোর্ট্রেট (2:3)</option>
                        <option value="banner" {{ $dimensionFilter === 'banner' ? 'selected' : '' }}>ব্যানার (≥1200px)</option>
                        <option value="square" {{ $dimensionFilter === 'square' ? 'selected' : '' }}>বর্গাকার (1:1)</option>
                        <option value="thumb" {{ $dimensionFilter === 'thumb' ? 'selected' : '' }}>থাম্বনেইল (≤400px)</option>
                    </select>
                </div>

                {{-- Sorter --}}
                <div class="col-6 col-md-2 col-xl-2">
                    <select name="sort" class="form-select form-select-sm rounded-3 fw-semibold" onchange="document.getElementById('mediaFilterForm').submit()">
                        <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>সর্বশেষ আপলোড</option>
                        <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>পুরোনো আগে</option>
                        <option value="size_desc" {{ $sort === 'size_desc' ? 'selected' : '' }}>বড় সাইজ (MB)</option>
                        <option value="size_asc" {{ $sort === 'size_asc' ? 'selected' : '' }}>ছোট সাইজ (KB)</option>
                        <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>নাম (A - Z)</option>
                        <option value="name_desc" {{ $sort === 'name_desc' ? 'selected' : '' }}>নাম (Z - A)</option>
                    </select>
                </div>

                {{-- View Switcher Buttons & Select All --}}
                <div class="col-6 col-md-2 col-xl-2 d-flex align-items-center justify-content-end gap-2">
                    <div class="form-check d-flex align-items-center gap-1.5 mb-0" title="সকল ছবি সিলেক্ট করুন">
                        <input class="form-check-input" type="checkbox" id="selectAllMaster" onchange="toggleSelectAll(this)">
                        <label class="form-check-label small fw-bold text-dark cursor-pointer" for="selectAllMaster">সবগুলো</label>
                    </div>

                    <div class="btn-group btn-group-sm rounded-pill overflow-hidden border">
                        <button type="button" class="btn {{ $viewMode === 'grid' ? 'btn-primary' : 'btn-light' }} px-2.5" onclick="document.getElementById('mediaViewModeInput').value='grid'; document.getElementById('mediaFilterForm').submit();" title="গ্রিড ভিউ">
                            <i class="fa-solid fa-table-cells-large"></i>
                        </button>
                        <button type="button" class="btn {{ $viewMode === 'list' ? 'btn-primary' : 'btn-light' }} px-2.5" onclick="document.getElementById('mediaViewModeInput').value='list'; document.getElementById('mediaFilterForm').submit();" title="লিস্ট ভিউ">
                            <i class="fa-solid fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Media Library Content Area -->
    @if($viewMode === 'grid')
        {{-- ================================================================= --}}
        {{-- GRID VIEW                                                         --}}
        {{-- ================================================================= --}}
        <div class="row g-3 {{ $folderFilter === 'books' ? 'is-book-folder' : '' }}" id="mediaGridContainer">
            @forelse($paginatedItems as $index => $item)
                <div class="col-6 col-sm-4 col-md-3 col-xl-2 media-item-card" 
                     data-filename="{{ strtolower($item['filename']) }}" 
                     data-ext="{{ strtolower($item['ext']) }}" 
                     data-folder="{{ strtolower($item['folder']) }}"
                     data-index="{{ $index }}"
                     data-url="{{ $item['url'] }}"
                     data-path="{{ $item['path'] }}"
                     data-title="{{ $item['item_title'] ?? $item['filename'] }}"
                     data-size="{{ $item['size'] }}"
                     data-date="{{ $item['updated_at']->format('d M, Y h:i A') }}"
                     data-res="{{ $item['width'] ? $item['width'].'x'.$item['height'] : 'N/A' }}"
                     data-folderlabel="{{ $item['folder_label'] }}">
                    <div class="card media-card position-relative">
                        {{-- Selection Checkbox --}}
                        <div class="media-select-cb-wrapper">
                            <input type="checkbox" class="form-check-input media-select-cb" 
                                   data-path="{{ $item['path'] }}" 
                                   data-url="{{ $item['url'] }}" 
                                   data-filename="{{ $item['filename'] }}">
                        </div>

                        {{-- Action Buttons on Hover Overlay --}}
                        <div class="media-thumb-overlay">
                            <button type="button" class="btn-thumb-action" title="ফুলস্ক্রিন প্রিভিউ" onclick="openLightboxByIndex({{ $index }})">
                                <i class="fa-solid fa-expand"></i>
                            </button>
                            <button type="button" class="btn-thumb-action" title="কাস্টমাইজ ও অপ্টিমাইজ স্টুডিও" onclick="openStudioModal('{{ $item['url'] }}', '{{ addslashes($item['path']) }}', '{{ addslashes($item['item_title'] ?? $item['filename']) }}', {{ $item['width'] ?? 0 }}, {{ $item['height'] ?? 0 }})">
                                <i class="fa-solid fa-palette"></i>
                            </button>
                            <a href="{{ $item['url'] }}" download="{{ $item['filename'] }}" class="btn-thumb-action" title="কম্পিউটারে ডাউনলোড">
                                <i class="fa-solid fa-download"></i>
                            </a>
                        </div>

                        {{-- Thumbnail Container --}}
                        <div class="media-thumb-container" onclick="openLightboxByIndex({{ $index }})">
                            <img src="{{ $item['url'] }}" alt="{{ $item['filename'] }}" class="media-thumb-img" loading="lazy">
                            
                            {{-- Dimension & Format Meta Badges on Thumb --}}
                            <div class="media-badge-meta">
                                <span class="media-badge-tag {{ $item['is_webp'] ? 'badge-webp-glow' : '' }}">{{ strtoupper($item['ext']) }}</span>
                                @if($item['width'] && $item['height'])
                                    <span class="media-badge-tag">{{ $item['aspect_ratio'] }} ({{ $item['width'] }}×{{ $item['height'] }})</span>
                                @endif
                            </div>
                        </div>

                        {{-- Details --}}
                        <div class="media-details">
                            <div>
                                @if(!empty($item['item_title']))
                                    <div class="media-item-title" title="{{ $item['item_title'] }}">{{ $item['item_title'] }}</div>
                                    <div class="media-item-subtitle">{{ $item['item_subtitle'] ?? $item['filename'] }}</div>
                                @else
                                    <div class="media-filename" title="{{ $item['filename'] }}">{{ $item['filename'] }}</div>
                                @endif
                                <div class="media-meta-row mt-1">
                                    <span class="badge bg-light text-dark border px-1.5 py-0.5 rounded-pill font-monospace" style="font-size: 10px;">
                                        <i class="{{ $item['folder_icon'] }} me-0.5"></i> {{ $item['folder_label'] }}
                                    </span>
                                    <span class="fw-bold text-dark">{{ $item['size'] }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Footer Action Buttons --}}
                        <div class="media-card-footer">
                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0.5 fw-semibold" onclick="openStudioModal('{{ $item['url'] }}', '{{ addslashes($item['path']) }}', '{{ addslashes($item['item_title'] ?? $item['filename']) }}', {{ $item['width'] ?? 0 }}, {{ $item['height'] ?? 0 }})" title="স্টুডিওতে কাস্টমাইজ করুন">
                                <i class="fa-solid fa-palette me-0.5"></i> স্টুডিও
                            </button>
                            <div class="d-flex align-items-center gap-1">
                                <a href="{{ $item['url'] }}" download="{{ $item['filename'] }}" class="btn btn-xs btn-outline-success border-0 p-1" title="কম্পিউটারে ডাউনলোড">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                                {{-- Quick Snippet Copy Dropdown --}}
                                <div class="dropdown d-inline-block">
                                    <button type="button" class="btn btn-xs btn-outline-secondary border-0 p-1" data-bs-toggle="dropdown" title="কোড ও লিংক কপি">
                                        <i class="fa-regular fa-copy"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end snippet-dropdown-menu">
                                        <div class="snippet-dropdown-item" onclick="copySnippet('url', '{{ $item['url'] }}', '{{ addslashes($item['item_title'] ?? $item['filename']) }}')">
                                            <i class="fa-solid fa-link text-primary"></i> Direct Image URL
                                        </div>
                                        <div class="snippet-dropdown-item" onclick="copySnippet('html', '{{ $item['url'] }}', '{{ addslashes($item['item_title'] ?? $item['filename']) }}')">
                                            <i class="fa-brands fa-html5 text-danger"></i> HTML &lt;img&gt; Tag
                                        </div>
                                        <div class="snippet-dropdown-item" onclick="copySnippet('markdown', '{{ $item['url'] }}', '{{ addslashes($item['item_title'] ?? $item['filename']) }}')">
                                            <i class="fa-brands fa-markdown text-info"></i> Markdown Snippet
                                        </div>
                                        <div class="snippet-dropdown-item" onclick="copySnippet('blade', '{{ $item['url'] }}', '{{ addslashes($item['item_title'] ?? $item['filename']) }}')">
                                            <i class="fa-brands fa-laravel text-warning"></i> Laravel Blade Snippet
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-xs btn-outline-secondary border-0 p-1" onclick="openReplaceModal('{{ addslashes($item['path']) }}', '{{ addslashes($item['filename']) }}')" title="ছবি রিপ্লেস / আপডেট করুন">
                                    <i class="fa-solid fa-arrow-right-arrow-left"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-secondary border-0 p-1" onclick="openRenameModal('{{ addslashes($item['path']) }}', '{{ addslashes($item['filename']) }}')" title="ফাইলের নাম পরিবর্তন">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('admin.media.destroy') }}" method="POST"
                                      data-confirm="আপনি কি নিশ্চিত এই মিডিয়া ফাইলটি মুছে ফেলতে চান?"
                                      data-confirm-title="মিডিয়া ফাইল অপসারণ"
                                      data-confirm-icon="warning"
                                      data-confirm-btn="<i class='fa-solid fa-trash-can me-1'></i> মুছে ফেলুন"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="path" value="{{ $item['path'] }}">
                                    <button type="submit" class="btn btn-xs btn-outline-danger border-0 p-1" title="মুছে ফেলুন">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12" id="emptyMediaNotice">
                    <div class="card bg-white rounded-4 p-5 text-center text-muted border-0 shadow-sm">
                        <i class="fa-solid fa-images fs-1 text-secondary mb-3 opacity-50"></i>
                        <h5 class="text-dark fw-bold">কোনো মিডিয়া ফাইল পাওয়া যায়নি</h5>
                        <p class="small mb-3">উপরে <strong>মিডিয়া আপলোড</strong> বাটনে ক্লিক করে ছবি যোগ করুন।</p>
                    </div>
                </div>
            @endforelse
        </div>
    @else
        {{-- ================================================================= --}}
        {{-- LIST / TABLE VIEW                                                 --}}
        {{-- ================================================================= --}}
        <div class="card bg-white rounded-4 shadow-sm border-0 p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="media-table mb-0" id="mediaTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" class="form-check-input" onchange="toggleSelectAll(this)">
                            </th>
                            <th style="width: 60px;">প্রিভিউ</th>
                            <th>ফাইলের নাম ও বিবরণ</th>
                            <th>ফোল্ডার</th>
                            <th>রেজোলিউশন</th>
                            <th>ফরম্যাট</th>
                            <th>সাইজ</th>
                            <th>তারিখ</th>
                            <th class="text-end">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginatedItems as $index => $item)
                            <tr class="media-item-row" 
                                data-filename="{{ strtolower($item['filename']) }}" 
                                data-ext="{{ strtolower($item['ext']) }}" 
                                data-folder="{{ strtolower($item['folder']) }}"
                                data-index="{{ $index }}"
                                data-url="{{ $item['url'] }}"
                                data-path="{{ $item['path'] }}"
                                data-title="{{ $item['item_title'] ?? $item['filename'] }}"
                                data-size="{{ $item['size'] }}"
                                data-date="{{ $item['updated_at']->format('d M, Y h:i A') }}"
                                data-res="{{ $item['width'] ? $item['width'].'x'.$item['height'] : 'N/A' }}"
                                data-folderlabel="{{ $item['folder_label'] }}">
                                <td>
                                    <input type="checkbox" class="form-check-input media-select-cb" 
                                           data-path="{{ $item['path'] }}" 
                                           data-url="{{ $item['url'] }}" 
                                           data-filename="{{ $item['filename'] }}">
                                </td>
                                <td>
                                    <img src="{{ $item['url'] }}" alt="{{ $item['filename'] }}" class="media-table-thumb" 
                                         onclick="openLightboxByIndex({{ $index }})" loading="lazy">
                                </td>
                                <td>
                                    @if(!empty($item['item_title']))
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 280px;" title="{{ $item['item_title'] }}">{{ $item['item_title'] }}</div>
                                        <small class="text-muted font-monospace fs-xs">{{ $item['item_subtitle'] ?? $item['filename'] }}</small>
                                    @else
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 280px;" title="{{ $item['filename'] }}">{{ $item['filename'] }}</div>
                                        <small class="text-muted font-monospace fs-xs">{{ $item['mime'] }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill font-monospace px-2.5 py-1">
                                        <i class="{{ $item['folder_icon'] }} text-primary me-1"></i> {{ $item['folder_label'] }}
                                    </span>
                                </td>
                                <td>
                                    @if($item['width'] && $item['height'])
                                        <span class="badge bg-dark-subtle text-dark rounded-pill font-monospace px-2 py-0.5" style="font-size: 11px;">
                                            {{ $item['width'] }} × {{ $item['height'] }} ({{ $item['aspect_ratio'] }})
                                        </span>
                                    @else
                                        <span class="text-muted font-monospace small">Vector / Auto</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $item['is_webp'] ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary' }} font-monospace text-uppercase px-2 py-0.5">
                                        {{ $item['ext'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold font-monospace text-dark">{{ $item['size'] }}</span>
                                </td>
                                <td>
                                    <span class="text-muted small font-monospace">{{ $item['updated_at']->format('d M, Y') }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1.5">
                                        <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold" onclick="openStudioModal('{{ $item['url'] }}', '{{ addslashes($item['path']) }}', '{{ addslashes($item['item_title'] ?? $item['filename']) }}', {{ $item['width'] ?? 0 }}, {{ $item['height'] ?? 0 }})">
                                            <i class="fa-solid fa-palette me-1"></i> কাস্টমাইজ
                                        </button>
                                        <a href="{{ $item['url'] }}" download="{{ $item['filename'] }}" class="btn btn-xs btn-outline-success rounded-pill px-2 py-1" title="ডাউনলোড">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1" onclick="copySnippet('url', '{{ $item['url'] }}', '{{ addslashes($item['item_title'] ?? $item['filename']) }}')" title="URL কপি করুন">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary border-0 p-1" onclick="openReplaceModal('{{ addslashes($item['path']) }}', '{{ addslashes($item['filename']) }}')" title="ছবি রিপ্লেস / আপডেট">
                                            <i class="fa-solid fa-arrow-right-arrow-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary border-0 p-1" onclick="openRenameModal('{{ addslashes($item['path']) }}', '{{ addslashes($item['filename']) }}')" title="নাম পরিবর্তন">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form action="{{ route('admin.media.destroy') }}" method="POST"
                                              data-confirm="আপনি কি নিশ্চিত এই মিডিয়া ফাইলটি মুছে ফেলতে চান?"
                                              data-confirm-title="মিডিয়া ফাইল অপসারণ"
                                              data-confirm-icon="warning"
                                              data-confirm-btn="<i class='fa-solid fa-trash-can me-1'></i> মুছে ফেলুন"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="path" value="{{ $item['path'] }}">
                                            <button type="submit" class="btn btn-xs btn-outline-danger border-0 p-1" title="মুছে ফেলুন">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-images fs-1 text-secondary opacity-50 mb-2"></i>
                                    <div>কোনো মিডিয়া ফাইল পাওয়া যায়নি।</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Pagination & Per-Page Controls Bar --}}
    @if($filteredCount > 0)
        <div class="card bg-white rounded-4 shadow-sm border-0 p-3 mt-2">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="small text-muted font-monospace">
                    মোট <strong>{{ number_format($filteredCount) }}</strong> টি ফাইলের মধ্যে 
                    <strong>{{ count($paginatedItems) }}</strong> টি প্রদর্শিত (পৃষ্ঠা {{ $currentPage }} / {{ $totalPages }})
                </div>

                <div class="d-flex align-items-center gap-3">
                    {{-- Per-Page Selector --}}
                    <div class="d-flex align-items-center gap-1.5">
                        <span class="small text-muted fw-semibold">প্রতি পৃষ্ঠায়:</span>
                        <select class="form-select form-select-sm rounded-pill font-monospace" style="width: 80px;" onchange="const u = new URL(window.location.href); u.searchParams.set('per_page', this.value); u.searchParams.set('page', '1'); window.location.href = u.toString();">
                            <option value="24" {{ $perPage == '24' ? 'selected' : '' }}>24</option>
                            <option value="48" {{ $perPage == '48' ? 'selected' : '' }}>48</option>
                            <option value="96" {{ $perPage == '96' ? 'selected' : '' }}>96</option>
                            <option value="all" {{ $perPage == 'all' ? 'selected' : '' }}>All</option>
                        </select>
                    </div>

                    {{-- Page Navigation Links --}}
                    @if($totalPages > 1 && $perPage !== 'all')
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                @if($currentPage > 1)
                                    <li class="page-item">
                                        <a class="page-link rounded-pill me-1 px-2.5" href="{{ route('admin.media.index', array_merge(request()->query(), ['page' => $currentPage - 1])) }}">
                                            <i class="fa-solid fa-chevron-left"></i>
                                        </a>
                                    </li>
                                @endif

                                @for($p = max(1, $currentPage - 2); $p <= min($totalPages, $currentPage + 2); $p++)
                                    <li class="page-item {{ $p === $currentPage ? 'active' : '' }}">
                                        <a class="page-link rounded-circle mx-0.5 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;" href="{{ route('admin.media.index', array_merge(request()->query(), ['page' => $p])) }}">
                                            {{ $p }}
                                        </a>
                                    </li>
                                @endfor

                                @if($currentPage < $totalPages)
                                    <li class="page-item">
                                        <a class="page-link rounded-pill ms-1 px-2.5" href="{{ route('admin.media.index', array_merge(request()->query(), ['page' => $currentPage + 1])) }}">
                                            <i class="fa-solid fa-chevron-right"></i>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- No Search Results Placeholder -->
    <div id="noSearchMatchNotice" class="d-none col-12 mt-3">
        <div class="card bg-white rounded-4 p-5 text-center text-muted border-0 shadow-sm">
            <i class="fa-solid fa-magnifying-glass fs-1 text-secondary mb-3 opacity-50"></i>
            <h5 class="text-dark fw-bold">কোনো ফলাফল মেলেনি</h5>
            <p class="small mb-0">ভিন্ন কোনো ফাইলের নাম বা এক্সটেনশন দিয়ে অনুসন্ধান করুন।</p>
        </div>
    </div>

</div>

{{-- ========================================================================= --}}
{{-- FLOATING BATCH ACTIONS TOOLBAR                                             --}}
{{-- ========================================================================= --}}
<div id="mediaFloatingBar" class="media-floating-bar">
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary text-white font-monospace px-3 py-1.5 rounded-pill" id="selectedMediaCountBadge">0 টি নির্বাচিত</span>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <button type="button" class="btn-floating-action btn-floating-warning" onclick="executeBulkConvertToWebp()" title="নির্বাচিত ফাইলসমূহকে WebP ফরম্যাটে রূপান্তর করুন">
            <i class="fa-solid fa-bolt-lightning text-warning"></i> WebP রূপান্তর
        </button>
        <button type="button" class="btn-floating-action" onclick="copySelectedUrls()">
            <i class="fa-solid fa-copy"></i> URL কপি
        </button>
        <button type="button" class="btn-floating-action" onclick="openBulkMoveModal()">
            <i class="fa-solid fa-folder-tree"></i> ফোল্ডারে সরান
        </button>
        <button type="button" class="btn-floating-action" onclick="executeBulkDownloadZip()">
            <i class="fa-solid fa-file-zipper"></i> ZIP ডাউনলোড
        </button>
        <button type="button" class="btn-floating-action btn-floating-danger" onclick="executeBulkDelete()">
            <i class="fa-solid fa-trash-can"></i> মুছে ফেলুন
        </button>
        <button type="button" class="btn-floating-action" onclick="clearAllSelections()" title="নির্বাচন বাতিল">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 1: ✨ কাস্টমাইজ ও অপ্টিমাইজ স্টুডিও (IMAGE CUSTOMIZER STUDIO)        --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="mediaStudioModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4 bg-dark text-white rounded-top-4">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="studioModalTitle">
                    <i class="fa-solid fa-wand-magic-sparkles text-warning"></i>
                    <span>কাস্টমাইজ ও অপ্টিমাইজ স্টুডিও</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="row g-4">
                    {{-- Left Side: Live Canvas Workspace --}}
                    <div class="col-12 col-lg-7">
                        <div class="studio-canvas-container mb-2">
                            <canvas id="studioCanvas" class="studio-canvas"></canvas>
                        </div>
                        <div class="d-flex align-items-center justify-content-between text-muted small px-1 font-monospace">
                            <span id="studioOrigMetaDisplay">আসল রেজোলিউশন: -</span>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5" onclick="downloadStudioCanvasLocally()">
                                    <i class="fa-solid fa-download me-1"></i> লোকাল ডাউনলোড
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Right Side: Customizer Controls Panel --}}
                    <div class="col-12 col-lg-5" style="max-height: 560px; overflow-y: auto; scrollbar-width: thin;">
                        
                        {{-- 1. Aspect Ratio & Presets --}}
                        <div class="studio-control-box">
                            <div class="studio-control-title">
                                <i class="fa-solid fa-crop text-primary"></i> আসপেক্ট রেশিও ও সাইজ প্রিসেট
                            </div>
                            <div class="d-flex flex-wrap gap-1.5 mb-2.5">
                                <span class="studio-preset-chip active" data-preset="original" onclick="setStudioPreset('original')">আসল সাইজ</span>
                                <span class="studio-preset-chip" data-preset="book-cover-std" onclick="setStudioPreset('book-cover-std')">📚 বই কাভার (800×1200)</span>
                                <span class="studio-preset-chip" data-preset="book-cover-compact" onclick="setStudioPreset('book-cover-compact')">📖 কমপ্যাক্ট (600×900)</span>
                                <span class="studio-preset-chip" data-preset="1:1" onclick="setStudioPreset('1:1')">1:1 বর্গাকার</span>
                                <span class="studio-preset-chip" data-preset="16:9" onclick="setStudioPreset('16:9')">16:9 ব্যানার</span>
                                <span class="studio-preset-chip" data-preset="4:3" onclick="setStudioPreset('4:3')">4:3 স্ট্যান্ডার্ড</span>
                                <span class="studio-preset-chip" data-preset="1200x630" onclick="setStudioPreset('1200x630')">🔗 সোশ্যাল ব্যানার (1200×630)</span>
                                <span class="studio-preset-chip" data-preset="1080x1080" onclick="setStudioPreset('1080x1080')">📱 স্কয়ার পোস্ট (1080×1080)</span>
                                <span class="studio-preset-chip" data-preset="1920x1080" onclick="setStudioPreset('1920x1080')">1920×1080 HD</span>
                            </div>
                            <div class="row g-2 align-items-center">
                                <div class="col-5">
                                    <label class="form-label fs-xs text-muted mb-0.5">প্রস্থ (Width px)</label>
                                    <input type="number" id="studioWidthInput" class="form-control form-control-sm font-monospace fw-bold" placeholder="Width">
                                </div>
                                <div class="col-2 text-center pt-3">
                                    <i class="fa-solid fa-xmark text-muted"></i>
                                </div>
                                <div class="col-5">
                                    <label class="form-label fs-xs text-muted mb-0.5">উচ্চতা (Height px)</label>
                                    <input type="number" id="studioHeightInput" class="form-control form-control-sm font-monospace fw-bold" placeholder="Height">
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="studioLockRatio" checked>
                                        <label class="form-check-label small fw-semibold text-dark" for="studioLockRatio">আসপেক্ট রেশিও লক রাখুন (Lock Ratio)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Transform: Rotate & Flip --}}
                        <div class="studio-control-box">
                            <div class="studio-control-title">
                                <i class="fa-solid fa-arrows-rotate text-info"></i> রোটেট ও ট্রান্সফর্ম
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="studioRotate(-90)">
                                    <i class="fa-solid fa-rotate-left me-1"></i> -90°
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="studioRotate(90)">
                                    <i class="fa-solid fa-rotate-right me-1"></i> +90°
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="studioFlip('h')">
                                    <i class="fa-solid fa-arrows-left-right me-1"></i> ফ্লিপ (H)
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="studioFlip('v')">
                                    <i class="fa-solid fa-arrows-up-down me-1"></i> ফ্লিপ (V)
                                </button>
                            </div>
                        </div>

                        {{-- 3. Filters & Tone Adjustment --}}
                        <div class="studio-control-box">
                            <div class="studio-control-title">
                                <i class="fa-solid fa-sliders text-success"></i> ফিল্টার ও কালার অ্যাডজাস্টমেন্ট
                            </div>
                            <div class="d-flex flex-wrap gap-1.5 mb-3">
                                <button type="button" class="btn btn-xs btn-outline-dark rounded-pill px-2.5" onclick="setStudioQuickFilter('none')">স্বাভাবিক</button>
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5" onclick="setStudioQuickFilter('crisp')">উজ্জ্বল ও ক্রিস্প</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5" onclick="setStudioQuickFilter('bw')">সাদা-কালো (B&W)</button>
                                <button type="button" class="btn btn-xs btn-outline-warning rounded-pill px-2.5" onclick="setStudioQuickFilter('vintage')">ভিন্টেজ (Sepia)</button>
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small text-muted mb-0.5">
                                    <span>উজ্জ্বলতা (Brightness)</span>
                                    <span id="studioBrightnessVal" class="font-monospace fw-bold text-dark">100%</span>
                                </div>
                                <input type="range" class="form-range" id="studioBrightness" min="30" max="180" value="100">
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small text-muted mb-0.5">
                                    <span>কনট্রাস্ট (Contrast)</span>
                                    <span id="studioContrastVal" class="font-monospace fw-bold text-dark">100%</span>
                                </div>
                                <input type="range" class="form-range" id="studioContrast" min="30" max="180" value="100">
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small text-muted mb-0.5">
                                    <span>কালার স্যাচুরেশন (Saturation)</span>
                                    <span id="studioSaturationVal" class="font-monospace fw-bold text-dark">100%</span>
                                </div>
                                <input type="range" class="form-range" id="studioSaturation" min="0" max="200" value="100">
                            </div>
                        </div>

                        {{-- 4. Branding & Watermark Overlay --}}
                        <div class="studio-control-box">
                            <div class="studio-control-title">
                                <i class="fa-solid fa-stamp text-danger"></i> ওয়াটারমার্ক ও ব্র্যান্ডিং স্ট্যাম্প
                            </div>
                            <div class="mb-2">
                                <input type="text" id="studioWatermarkTextInput" class="form-control form-control-sm" placeholder="e.g. © IDEA Prokashon / ideaabd.com">
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="d-flex justify-content-between small text-muted mb-0.5">
                                        <span>স্বচ্ছতা (Opacity)</span>
                                        <span id="studioWatermarkOpacityVal" class="font-monospace fw-bold text-dark">60%</span>
                                    </div>
                                    <input type="range" class="form-range" id="studioWatermarkOpacity" min="10" max="100" value="60">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fs-xs text-muted mb-0.5">পজিশন</label>
                                    <select id="studioWatermarkPos" class="form-select form-select-sm" onchange="studioState.watermarkPosition = this.value; renderStudioCanvas();">
                                        <option value="bottom-right" selected>নিচে ডানে (Bottom Right)</option>
                                        <option value="bottom-left">নিচে বামে (Bottom Left)</option>
                                        <option value="center">কেন্দ্রে (Center)</option>
                                        <option value="top-right">উপরে ডানে (Top Right)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- 5. Format & Compression --}}
                        <div class="studio-control-box">
                            <div class="studio-control-title">
                                <i class="fa-solid fa-file-export text-purple"></i> এক্সপোর্ট ফরম্যাট ও কম্প্রেশন কোয়ালিটি
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label fs-xs text-muted mb-0.5">টার্গেট ফরম্যাট</label>
                                    <select id="studioFormatSelect" class="form-select form-select-sm fw-bold">
                                        <option value="webp" selected>⚡ WebP (সুপার ফাস্ট)</option>
                                        <option value="png">PNG (স্বচ্ছ ব্যাকগ্রাউন্ড)</option>
                                        <option value="jpg">JPG / JPEG (স্ট্যান্ডার্ড)</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fs-xs text-muted mb-0.5">টার্গেট ফোল্ডার</label>
                                    <select id="studioFolderSelect" class="form-select form-select-sm">
                                        @foreach($folderDefs as $fk => $finfo)
                                            <option value="{{ $fk }}" {{ ($folderFilter !== 'all' && $folderFilter === $fk) || ($folderFilter === 'all' && $fk === 'uploads') ? 'selected' : '' }}>{{ $finfo['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between small text-muted mb-0.5">
                                    <span>কোয়ালিটি কম্প্রেশন</span>
                                    <span id="studioQualityVal" class="font-monospace fw-bold text-success">85% (Optimal)</span>
                                </div>
                                <input type="range" class="form-range" id="studioQuality" min="30" max="100" value="85">
                            </div>
                        </div>

                        {{-- 6. File Name --}}
                        <div class="studio-control-box">
                            <label class="form-label small fw-bold text-dark mb-1">ফাইলের নাম (File Name)</label>
                            <input type="text" id="studioFilenameInput" class="form-control form-control-sm font-monospace fw-semibold" placeholder="e.g. customized_book_cover">
                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer border-top py-3 px-4 bg-white d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3.5 fw-bold" id="btnStudioOverwrite" onclick="saveCustomizedImage('overwrite')">
                        <i class="fa-solid fa-arrows-rotate me-1"></i> মূল ফাইল ওভাররাইট করুন
                    </button>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btnStudioSaveCopy" onclick="saveCustomizedImage('new_copy')">
                        <i class="fa-solid fa-floppy-disk me-1"></i> নতুন কপি হিসেবে সংরক্ষণ
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 2: DRAG & DROP MULTI-FILE UPLOADER MODAL                            --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="uploadMediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom py-3 px-4 bg-light rounded-top-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-up text-primary"></i>
                    <span>মিডিয়া ও অ্যাসেট আপলোড ইঞ্জিন</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                
                {{-- Drop Zone --}}
                <div class="media-dropzone mb-3" id="mediaUploadZone" onclick="document.getElementById('multiFileInput').click()">
                    <i class="fa-solid fa-cloud-arrow-up media-dropzone-icon"></i>
                    <h6 class="fw-bold text-dark mb-1">ছবি বা অ্যাসেট ড্র্যাগ করে এখানে ছাড়ুন অথবা ক্লিক করুন</h6>
                    <p class="text-muted small mb-2">সমর্থিত ফরম্যাট: JPG, PNG, WEBP, SVG, GIF, AVIF, ICO | সর্বোচ্চ প্রতি ফাইল: 10MB</p>
                    <span class="badge bg-primary text-white rounded-pill px-3 py-1.5 font-monospace">
                        <i class="fa-solid fa-folder-open me-1"></i> ফাইল ব্রাউজ করুন
                    </span>
                    <input type="file" id="multiFileInput" class="d-none" multiple accept="image/*">
                </div>

                {{-- Upload Options --}}
                <div class="row g-3 p-3 bg-light rounded-3 border mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-dark">টার্গেট ফোল্ডার</label>
                        <select id="uploadTargetFolder" class="form-select form-select-sm rounded-3 fw-semibold">
                            @foreach($folderDefs as $fk => $finfo)
                                <option value="{{ $fk }}" {{ ($folderFilter !== 'all' && $folderFilter === $fk) || ($folderFilter === 'all' && $fk === 'uploads') ? 'selected' : '' }}>
                                    {{ $finfo['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-dark">ম্যাক্সিমাম রেজোলিউশন লিমিট</label>
                        <select id="uploadMaxDim" class="form-select form-select-sm rounded-3 fw-semibold">
                            <option value="1920" selected>1920px (Full HD অটো স্কেল)</option>
                            <option value="1200">1200px (স্ট্যান্ডার্ড ওয়েব)</option>
                            <option value="800">800px (কমপ্যাক্ট)</option>
                            <option value="0">আসল সাইজ রাখুন (No Resize)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="uploadAutoWebp" checked>
                            <label class="form-check-label small fw-semibold text-dark" for="uploadAutoWebp">
                                আপলোডের সময় স্বয়ংক্রিয়ভাবে WebP অপ্টিমাইজ করুন (Faster Page Speed)
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Preview List for Pending Files --}}
                <div id="uploadPreviewWrap" class="d-none mb-3">
                    <label class="form-label small fw-bold text-dark mb-1.5">নির্বাচিত ফাইলসমূহ:</label>
                    <div id="uploadThumbPreviewList" style="max-height: 180px; overflow-y: auto; scrollbar-width: thin;"></div>
                </div>

                {{-- Progress Bar --}}
                <div id="uploadProgressWrap" class="d-none mb-2">
                    <div class="d-flex justify-content-between small text-muted mb-1 font-monospace">
                        <span>আপলোড ও অপ্টিমাইজেশন চলছে...</span>
                        <span id="uploadPercentLabel">প্রসেসিং</span>
                    </div>
                    <div class="media-upload-progress">
                        <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" id="uploadProgressBar" style="width: 0%;"></div>
                    </div>
                </div>

            </div>
            <div class="modal-footer border-top py-2.5 px-4 bg-light rounded-bottom-4 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btnSubmitUpload" onclick="submitMultiUploadAjax()">
                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> আপলোড ও অপ্টিমাইজ শুরু করুন
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 3: CREATE NEW FOLDER MODAL                                          --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="createFolderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-2.5 px-3 bg-light rounded-top-4">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-folder-plus text-warning"></i>
                    <span>নতুন ফোল্ডার তৈরি</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-2.5">
                    <label class="form-label small fw-bold text-dark">ফোল্ডারের নাম (English Slug)</label>
                    <input type="text" id="newFolderNameInput" class="form-control form-control-sm font-monospace" placeholder="e.g. spring_campaign">
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-bold text-dark">লোকেশন</label>
                    <select id="newFolderLocSelect" class="form-select form-select-sm">
                        <option value="storage" selected>Storage Public (storage/app/public)</option>
                        <option value="public">Root Images (public/images)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-top py-2 px-3 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                <button type="button" class="btn btn-xs btn-warning text-dark fw-bold rounded-pill px-3" id="btnConfirmCreateFolder" onclick="submitCreateFolder()">
                    <i class="fa-solid fa-check me-1"></i> ফোল্ডার তৈরি
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 4: RENAME FILE MODAL                                                --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="renameFileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-2.5 px-3 bg-light rounded-top-4">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-primary"></i>
                    <span>ফাইলের নাম পরিবর্তন</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <input type="hidden" id="renamePathInput">
                <label class="form-label small fw-bold text-dark">নতুন ফাইলের নাম</label>
                <input type="text" id="renameNewNameInput" class="form-control form-control-sm font-monospace fw-semibold" placeholder="new-file-name">
                <small class="text-muted fs-xs mt-1 d-block">এক্সটেনশন স্বয়ংক্রিয়ভাবে সংরক্ষিত থাকবে।</small>
            </div>
            <div class="modal-footer border-top py-2 px-3 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                <button type="button" class="btn btn-xs btn-primary fw-bold rounded-pill px-3" id="btnConfirmRename" onclick="submitRenameFile()">
                    <i class="fa-solid fa-check me-1"></i> নাম সংরক্ষণ
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 5: BULK MOVE FOLDER MODAL                                           --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="bulkMoveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-2.5 px-3 bg-light rounded-top-4">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-folder-tree text-primary"></i>
                    <span>ফোল্ডারে স্থানান্তর</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <label class="form-label small fw-bold text-dark">টার্গেট ফোল্ডার নির্বাচন করুন</label>
                <select id="bulkMoveTargetFolder" class="form-select form-select-sm fw-semibold">
                    @foreach($folderDefs as $fk => $finfo)
                        <option value="{{ $fk }}">{{ $finfo['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer border-top py-2 px-3 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                <button type="button" class="btn btn-xs btn-primary fw-bold rounded-pill px-3" id="btnConfirmBulkMove" onclick="executeBulkMove()">
                    <i class="fa-solid fa-arrow-right-arrow-left me-1"></i> স্থানান্তর করুন
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 6: REPLACE MEDIA ASSET MODAL                                         --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="replaceMediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom py-3 px-4 bg-light rounded-top-4">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-arrow-right-arrow-left text-primary"></i>
                    <span>মিডিয়া ফাইল রিপ্লেস / আপডেট</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="replaceTargetPathInput">
                <div class="alert alert-primary py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-info fs-5 text-primary"></i>
                    <div>ফাইলের নাম ও বর্তমান লিঙ্ক অক্ষুণ্ণ রেখে নতুন ছবি দিয়ে রিপ্লেস হবে এবং স্বয়ংক্রিয়ভাবে অপ্টিমাইজ হবে।</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark mb-1">টার্গেট ফাইল:</label>
                    <div id="replaceTargetFilenameDisplay" class="font-monospace fw-bold text-primary small p-2.5 bg-light rounded border"></div>
                </div>
                <div>
                    <label class="form-label small fw-bold text-dark mb-1">নতুন ছবি নির্বাচন করুন:</label>
                    <input type="file" id="replaceFileInput" class="form-control form-control-sm" accept="image/*">
                </div>
            </div>
            <div class="modal-footer border-top py-2.5 px-4 bg-light rounded-bottom-4 d-flex justify-content-between">
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                <button type="button" class="btn btn-xs btn-primary fw-bold rounded-pill px-4 shadow-sm" id="btnConfirmReplace" onclick="submitReplaceFile()">
                    <i class="fa-solid fa-check me-1"></i> রিপ্লেস ও সেভ করুন
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 7: FULLSCREEN LIGHTBOX PREVIEW MODAL (CAROUSEL & STUDIO INTEGRATED)   --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden bg-dark">
            <div class="modal-header border-bottom border-secondary py-2.5 px-4 bg-dark text-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2 text-truncate me-2">
                    <i class="fa-solid fa-expand text-primary"></i>
                    <h6 class="modal-title fw-bold text-white small text-truncate mb-0" id="lightboxTitle">Image Preview</h6>
                </div>
                <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                    <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-2.5" onclick="lightboxZoom(0.2)" title="জুম ইন">
                        <i class="fa-solid fa-magnifying-glass-plus"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-2.5" onclick="lightboxZoom(-0.2)" title="জুম আউট">
                        <i class="fa-solid fa-magnifying-glass-minus"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-2.5" onclick="lightboxResetZoom()" title="রিসেট">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-0 position-relative d-flex align-items-center justify-content-center bg-black overflow-hidden" style="min-height: 500px; max-height: 75vh;">
                <!-- Nav Prev Arrow -->
                <button type="button" class="lightbox-nav-btn lightbox-nav-prev" onclick="lightboxNavigate(-1)" title="পূর্ববর্তী ছবি (Left Arrow)">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="lightbox-img-wrapper d-flex align-items-center justify-content-center w-100 h-100 p-2" id="lightboxImgWrapper">
                    <img id="lightboxImage" src="" alt="Full Preview" class="img-fluid rounded shadow transition-all" style="max-height: 70vh; max-width: 88vw; object-fit: contain; transition: transform 0.2s ease;">
                </div>
                <!-- Nav Next Arrow -->
                <button type="button" class="lightbox-nav-btn lightbox-nav-next" onclick="lightboxNavigate(1)" title="পরবর্তী ছবি (Right Arrow)">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
            <div class="modal-footer border-top border-secondary py-2.5 px-4 bg-dark d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="small text-light font-monospace" id="lightboxMeta"></div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-xs btn-outline-warning rounded-pill px-3 fw-semibold" id="lightboxStudioBtn" onclick="openStudioFromLightbox()">
                        <i class="fa-solid fa-palette me-1"></i> স্টুডিওতে খুলুন
                    </button>
                    <a href="" id="lightboxDownloadBtn" download class="btn btn-xs btn-outline-success rounded-pill px-3" title="ডাউনলোড">
                        <i class="fa-solid fa-download me-1"></i> ডাউনলোড
                    </a>
                    <button type="button" class="btn btn-xs btn-outline-light rounded-pill px-3" id="lightboxCopyBtn" onclick="copySnippet('url', document.getElementById('lightboxImage').src, document.getElementById('lightboxTitle').textContent)">
                        <i class="fa-regular fa-copy me-1"></i> URL কপি
                    </button>
                    <a href="" target="_blank" class="btn btn-xs btn-primary rounded-pill px-3.5 fw-bold" id="lightboxOpenBtn">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> ব্রাউজারে দেখুন
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 8: DYNAMIC WEBP CONVERSION & STORAGE BOOSTER ENGINE                 --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="convertWebpEngineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4 bg-dark text-white rounded-top-4">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="fa-solid fa-bolt-lightning text-warning"></i>
                    <span>Next-Gen WebP রূপান্তর ও মেমোরি বুস্টার ইঞ্জিন</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <!-- Step 1: Configuration Form -->
                <div id="webpEngineConfigView">
                    <div class="alert alert-success d-flex align-items-center gap-3 p-3 rounded-3 mb-3 border-0 bg-success-subtle text-success-emphasis">
                        <i class="fa-solid fa-wand-magic-sparkles fs-3"></i>
                        <div>
                            <strong class="d-block">স্বয়ংক্রিয় ইমেজ কম্প্রেশন ও স্পিড অপ্টিমাইজেশন</strong>
                            <span class="small">PNG ও JPG ফাইলগুলোকে লসলেস/উচ্চ কোয়ালিটির WebP-তে রূপান্তর করে পেজ লোডিং গতি ৭০% দ্রুত এবং স্টোরেজ সাশ্রয় করুন।</span>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">টার্গেট ফোল্ডার নির্বাচন</label>
                            <select id="webpTargetFolderSelect" class="form-select form-select-sm rounded-3 fw-semibold">
                                <option value="all" selected>🌐 সকল ফোল্ডার (পুরো সিস্টেম)</option>
                                @foreach($folderDefs as $fk => $finfo)
                                    <option value="{{ $fk }}">{{ $finfo['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-dark">WebP কোয়ালিটি লেভেল</label>
                            <select id="webpQualitySelect" class="form-select form-select-sm rounded-3 fw-semibold">
                                <option value="85" selected>85% (Optimal — দ্রুত ও ক্রিস্প)</option>
                                <option value="90">90% (Ultra High Quality)</option>
                                <option value="75">75% (Maximum Storage Saving)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch p-2 bg-white rounded-3 border">
                                <input class="form-check-input ms-0 me-2" type="checkbox" id="webpDeleteOriginalCheck" checked>
                                <label class="form-check-label small fw-bold text-dark cursor-pointer" for="webpDeleteOriginalCheck">
                                    মূল PNG / JPG ফাইল মুছে স্টোরেজ ডিস্ক মেমোরি খালি করুন (Recommended)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                        <button type="button" class="btn btn-sm btn-webp-gradient rounded-pill px-4 shadow-sm" onclick="startWebpConversionEngine()">
                            <i class="fa-solid fa-play me-1"></i> রূপান্তর প্রক্রিয়া শুরু করুন
                        </button>
                    </div>
                </div>

                <!-- Step 2: Live Progress Screen -->
                <div id="webpEngineProgressView" class="d-none">
                    <div class="text-center py-3">
                        <div class="spinner-grow text-success mb-2" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1" id="webpProgressTitle">ইমেজ স্ক্যান ও WebP রূপান্তর চলছে...</h5>
                        <p class="text-muted small mb-3" id="webpProgressSubtitle">সার্ভারের ছবিগুলো প্রসেস হচ্ছে, অনুগ্রহ করে অপেক্ষা করুন</p>
                    </div>

                    <!-- Progress Bar -->
                    <div class="media-upload-progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" id="webpEngineProgressBar" style="width: 25%;"></div>
                    </div>

                    <!-- Live Stat Counter Boxes -->
                    <div class="row g-2 mb-3 text-center font-monospace">
                        <div class="col-4">
                            <div class="p-2 bg-white rounded-3 border">
                                <div class="fs-xs text-muted">প্রসেসিং স্ট্যাটাস</div>
                                <div class="fw-bold text-primary small" id="webpStatusBadge">চলছে...</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-white rounded-3 border">
                                <div class="fs-xs text-muted">কনভার্ট সম্পন্ন</div>
                                <div class="fw-bold text-success small" id="webpConvertedCount">0 টি ফাইল</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-white rounded-3 border">
                                <div class="fs-xs text-muted">মেমোরি সাশ্রয়</div>
                                <div class="fw-bold text-dark small" id="webpMemorySaved">0 B</div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Log Terminal Box -->
                    <div class="p-2.5 bg-dark text-success rounded-3 font-monospace small" id="webpEngineLogBox" style="max-height: 150px; overflow-y: auto; font-size: 11px; scrollbar-width: thin;">
                        <div>[Init] WebP রূপান্তর ইঞ্জিন প্রস্তুত...</div>
                        <div>[Scan] সিস্টেম ইমেজ ডিরেক্টরি স্ক্যান করা হচ্ছে...</div>
                    </div>
                </div>

                <!-- Step 3: Finished Success Screen -->
                <div id="webpEngineSuccessView" class="d-none text-center py-4">
                    <div class="mb-3">
                        <i class="fa-solid fa-circle-check text-success fs-1 animate__animated animate__bounceIn" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">WebP রূপান্তর সফলভাবে সম্পন্ন!</h4>
                    <p class="text-muted small mb-4" id="webpSuccessMessage">সকল ছবি আধুনিক WebP ফরম্যাটে রূপান্তর ও অপ্টিমাইজ হয়েছে।</p>

                    <div class="d-inline-flex gap-2">
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="window.location.reload()">
                            <i class="fa-solid fa-rotate me-1"></i> পেজ রিফ্রেশ করুন
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin-media.js') }}"></script>
@endpush
@endsection
