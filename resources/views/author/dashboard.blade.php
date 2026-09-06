@extends('author.layout')

@section('title', 'Author Dashboard — IDEA')
@section('heading', 'Welcome, ' . auth()->user()->name)

@section('content')
<div class="d-flex flex-column gap-3.5">

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 0. AUTHOR PROFILE & DYNAMIC PHOTO STUDIO BANNER                            --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    @php
        $dashAvatar = auth()->user()->avatar ?: (auth()->user()->reg_data['avatar'] ?? ($author?->avatar ?? null));
        $dashAvatarUrl = $dashAvatar ? (str_starts_with($dashAvatar, 'http') ? $dashAvatar : asset('storage/' . ltrim($dashAvatar, '/'))) : null;
        $authorPenName = auth()->user()->reg_data['pen_name'] ?? ($author?->name != auth()->user()->name ? $author?->name : null);
        $authorBioText = auth()->user()->reg_data['bio'] ?? ($author?->bio ?? '');
        $userRegData = is_array(auth()->user()->reg_data) ? auth()->user()->reg_data : [];
        $fatherName = $userRegData['father_name'] ?? null;
        $motherName = $userRegData['mother_name'] ?? null;
        $nidOrPassport = $userRegData['nid_or_passport'] ?? null;
        $presentAddress = $userRegData['present_address'] ?? null;
        $payoutMethod = $userRegData['payout_method'] ?? null;
        $payoutNumber = $userRegData['payout_number'] ?? null;
        $hasExtraDetails = $fatherName || $motherName || $nidOrPassport || $presentAddress || $payoutMethod || !empty($authorBioText);
    @endphp
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden text-white position-relative" 
         style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);">
        
        <div class="p-3.5 p-md-4 position-relative z-1">
            <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-3">
                
                {{-- Interactive Avatar with Quick Edit Badge --}}
                <div class="position-relative flex-shrink-0 cursor-pointer" onclick="openPhotoStudioModal()" title="Change Photo">
                    <div class="rounded-circle overflow-hidden shadow-md border border-3 border-white position-relative bg-white" 
                         style="width: 82px; height: 82px; min-width: 82px; min-height: 82px; aspect-ratio: 1 / 1;" id="dashAvatarMainBox">
                        @if($dashAvatarUrl)
                            <img src="{{ $dashAvatarUrl }}" alt="{{ auth()->user()->name }}" class="w-100 h-100 object-fit-cover current-author-avatar-img">
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary fs-2 fw-bold bg-light">
                                {{ mb_substr(auth()->user()->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    
                    {{-- Camera Change Badge --}}
                    <button type="button" class="btn btn-warning btn-sm rounded-circle position-absolute bottom-0 end-0 p-0 d-flex align-items-center justify-content-center shadow-sm border border-2 border-white" 
                            style="width: 28px; height: 28px; transform: translate(2px, 2px);" title="Change Photo">
                        <i class="fas fa-camera text-dark" style="font-size: 11px;"></i>
                    </button>
                </div>

                {{-- Author Details & Quick Profile Actions --}}
                <div class="text-center text-md-start flex-grow-1 min-w-0 w-100">
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 mb-1">
                        <h4 class="fw-bold mb-0 text-white fs-5 fs-md-4" id="dashAuthorNameDisplay">{{ auth()->user()->name }}</h4>
                        @if($authorPenName)
                            <span class="badge bg-light text-dark rounded-pill px-2.5 py-1 small fw-semibold">
                                <i class="fas fa-feather-pointed me-1 text-primary"></i>{{ $authorPenName }}
                            </span>
                        @endif
                        <span class="badge bg-success bg-opacity-75 text-white rounded-pill px-2.5 py-1 small">
                            <i class="fas fa-circle-check me-1"></i>Author
                        </span>
                    </div>

                    {{-- Bio preview snippet --}}
                    <div class="mb-2.5">
                        @if($authorBioText)
                            <p class="text-white-50 small mb-1 text-truncate" style="font-size: 0.82rem; max-width: 600px;" id="dashAuthorBioDisplay">
                                {{ $authorBioText }}
                            </p>
                        @endif
                        @if($hasExtraDetails)
                            <button class="btn btn-link text-info text-decoration-none p-0 small fw-semibold" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#authorExtraDetailsCollapse" 
                                    aria-expanded="false" 
                                    aria-controls="authorExtraDetailsCollapse" 
                                    style="font-size: 11.5px;">
                                <i class="fas fa-circle-info me-1"></i>Profile Info <i class="fas fa-chevron-down ms-0.5 small"></i>
                            </button>
                        @endif
                    </div>

                    {{-- Collapsible Section for Details --}}
                    <div class="collapse mb-3" id="authorExtraDetailsCollapse">
                        <div class="p-3 rounded-3 text-start small border border-white border-opacity-15" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px);">
                            @if($authorBioText)
                                <div class="mb-2">
                                    <strong class="text-info d-block mb-0.5"><i class="fas fa-book-open me-1"></i>Bio:</strong>
                                    <span class="text-light opacity-90" style="line-height: 1.45;">{{ $authorBioText }}</span>
                                </div>
                            @endif
                            <div class="row g-2 text-white-50 pt-2 border-top border-white border-opacity-10" style="font-size: 11.5px;">
                                @if($fatherName)
                                    <div class="col-6 col-md-4">
                                        <span class="text-white fw-semibold">Father:</span> {{ $fatherName }}
                                    </div>
                                @endif
                                @if($motherName)
                                    <div class="col-6 col-md-4">
                                        <span class="text-white fw-semibold">Mother:</span> {{ $motherName }}
                                    </div>
                                @endif
                                @if($nidOrPassport)
                                    <div class="col-6 col-md-4">
                                        <span class="text-white fw-semibold">NID/Passport:</span> {{ $nidOrPassport }}
                                    </div>
                                @endif
                                @if($presentAddress)
                                    <div class="col-12 col-md-6">
                                        <span class="text-white fw-semibold">Address:</span> {{ $presentAddress }}
                                    </div>
                                @endif
                                @if($payoutMethod && $payoutNumber)
                                    <div class="col-12 col-md-6">
                                        <span class="text-white fw-semibold">Payout:</span> {{ strtoupper($payoutMethod) }} ({{ $payoutNumber }})
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Profile Action Buttons --}}
                    <div class="row g-1.5 g-md-2">
                        <div class="col-4">
                            <button type="button" class="btn btn-warning btn-sm w-100 rounded-pill py-1.5 px-1 px-md-3 fw-bold text-dark shadow-xs d-flex align-items-center justify-content-center gap-1 text-truncate" onclick="openPhotoStudioModal()">
                                <i class="fas fa-camera"></i>
                                <span>Photo</span>
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-light btn-sm w-100 rounded-pill py-1.5 px-1 px-md-3 fw-semibold d-flex align-items-center justify-content-center gap-1 text-truncate" onclick="openBioEditModal()">
                                <i class="fas fa-user-pen"></i>
                                <span>Edit Bio</span>
                            </button>
                        </div>
                        <div class="col-4">
                            @if($author && $author->slug)
                                <a href="{{ route('authors.show', $author->slug) }}" target="_blank" class="btn btn-outline-info btn-sm w-100 rounded-pill py-1.5 px-1 px-md-3 fw-semibold text-white d-flex align-items-center justify-content-center gap-1 text-truncate">
                                    <i class="fas fa-arrow-up-right-from-square"></i>
                                    <span>Profile</span>
                                </a>
                            @else
                                <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-info btn-sm w-100 rounded-pill py-1.5 px-1 px-md-3 fw-semibold text-white d-flex align-items-center justify-content-center gap-1 text-truncate">
                                    <i class="fas fa-store"></i>
                                    <span>Store</span>
                                </a>
                            @endif
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 1. PRIMARY CTA QUICK ACTION BAR                                            --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="card border-0 shadow-sm rounded-4 p-2.5 p-md-3 bg-white">
        <div class="row g-2">
            <div class="col-4">
                <a href="{{ route('author.posts.create') }}" class="btn btn-light border bg-warning-subtle border-warning-subtle w-100 py-2 py-md-2.5 rounded-3 fw-bold text-dark d-flex flex-column flex-md-row align-items-center justify-content-center gap-1 gap-md-2 shadow-xs hover-lift text-decoration-none">
                    <i class="fas fa-feather-pointed text-warning-emphasis fs-5"></i>
                    <span style="font-size: 12px;" class="fw-bold text-truncate">+ Post</span>
                </a>
            </div>

            <div class="col-4">
                <a href="{{ route('author.ebooks.create') }}" class="btn btn-light border bg-primary-subtle border-primary-subtle w-100 py-2 py-md-2.5 rounded-3 fw-bold text-primary-emphasis d-flex flex-column flex-md-row align-items-center justify-content-center gap-1 gap-md-2 shadow-xs hover-lift text-decoration-none">
                    <i class="fas fa-cloud-arrow-up text-primary fs-5"></i>
                    <span style="font-size: 12px;" class="fw-bold text-truncate">+ E-Book</span>
                </a>
            </div>

            <div class="col-4">
                <a href="{{ route('author.ebooks.create') }}" class="btn btn-light border bg-success-subtle border-success-subtle w-100 py-2 py-md-2.5 rounded-3 fw-bold text-success-emphasis d-flex flex-column flex-md-row align-items-center justify-content-center gap-1 gap-md-2 shadow-xs hover-lift text-decoration-none">
                    <i class="fas fa-file-arrow-up text-success fs-5"></i>
                    <span style="font-size: 12px;" class="fw-bold text-truncate">+ Manuscript</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 2. DYNAMIC STATUS CARDS                                                    --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="row g-2.5 g-md-3">
        {{-- Card 1: Posts Counter --}}
        <div class="col-6 col-lg-3">
            <div class="author-card p-3 h-100 border-start border-4 border-warning d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-truncate">Posts</span>
                    <span class="p-1.5 px-2 bg-warning-subtle text-warning-emphasis rounded-3"><i class="fas fa-pen-nib small"></i></span>
                </div>
                <div>
                    <h3 class="fw-bold mb-1 text-dark fs-4 font-monospace">{{ $totalPosts }}</h3>
                    <div class="small text-muted d-flex flex-wrap align-items-center gap-1">
                        <span class="badge bg-success-subtle text-success" style="font-size: 10px;">{{ $publishedPosts }} Live</span>
                        @if($pendingPosts > 0)
                            <span class="badge bg-warning-subtle text-warning-emphasis" style="font-size: 10px;">{{ $pendingPosts }} Pending</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: E-Books & Sales Counter --}}
        <div class="col-6 col-lg-3">
            <div class="author-card p-3 h-100 border-start border-4 border-primary d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-truncate">E-Books & Sales</span>
                    <span class="p-1.5 px-2 bg-primary-subtle text-primary rounded-3"><i class="fas fa-book-bookmark small"></i></span>
                </div>
                <div>
                    <h3 class="fw-bold mb-1 text-dark fs-4 font-monospace">{{ $totalEbooks }}</h3>
                    <div class="small text-muted d-flex flex-wrap align-items-center gap-1">
                        <span class="badge bg-primary-subtle text-primary" style="font-size: 10px;">{{ $totalCopiesSold }} Sold</span>
                        <span class="badge bg-success-subtle text-success" style="font-size: 10px;">{{ $publishedEbooks }} Live</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Total Royalty Share (50%) --}}
        <div class="col-6 col-lg-3">
            <div class="author-card p-3 h-100 border-start border-4 border-success d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-truncate">Royalty (50%)</span>
                    <span class="p-1.5 px-2 bg-success-subtle text-success rounded-3"><i class="fas fa-sack-dollar small"></i></span>
                </div>
                <div>
                    <h3 class="fw-bold mb-1 text-success font-monospace fs-4">৳{{ number_format($totalRoyaltyEarned, 2) }}</h3>
                    <div class="small text-muted text-truncate" style="font-size: 11px;">50% E-Book Share</div>
                </div>
            </div>
        </div>

        {{-- Card 4: Reader Tips --}}
        <div class="col-6 col-lg-3">
            <div class="author-card p-3 h-100 border-start border-4 border-danger d-flex flex-column justify-content-between">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-truncate">Reader Tips</span>
                    <span class="p-1.5 px-2 bg-danger bg-opacity-10 text-danger rounded-3"><i class="fas fa-heart small"></i></span>
                </div>
                <div>
                    <h3 class="fw-bold mb-1 text-danger font-monospace fs-4">৳{{ number_format($totalHonorariumEarned, 2) }}</h3>
                    <div class="small text-muted d-flex align-items-center justify-content-between" style="font-size: 11px;">
                        <span>{{ $totalHonorariumCount }} Tips</span>
                        <a href="{{ route('author.honorariums') }}" class="text-danger text-decoration-none fw-bold">View →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 5: Wallet Banner --}}
    <div class="card border-0 shadow-sm rounded-4 p-3 p-md-3.5 text-white" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #4338ca 100%);">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold fs-4 flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <span class="text-white-50 small text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 11px;">Available Balance</span>
                    <h2 class="fw-bold mb-0 text-warning font-monospace fs-3 fs-md-2">৳{{ number_format($availableBalance, 2) }}</h2>
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2 w-100 w-sm-auto justify-content-between justify-content-md-end">
                <a href="{{ route('author.payouts.index') }}" class="btn btn-warning btn-sm rounded-pill px-3 py-2 fw-bold text-dark shadow-sm flex-grow-1 flex-md-grow-0 text-center">
                    <i class="fas fa-hand-holding-dollar me-1"></i> Withdraw
                </a>
                <a href="{{ route('author.honorariums') }}" class="btn btn-outline-light btn-sm rounded-pill px-3 py-2 fw-semibold flex-grow-1 flex-md-grow-0 text-center">
                    <i class="fas fa-receipt me-1"></i> Ledger
                </a>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 3. QUICK DRAFT WIDGET                                                      --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="author-card bg-white overflow-hidden">
        <div class="p-3 d-flex align-items-center justify-content-between cursor-pointer border-bottom bg-light bg-opacity-25"
             data-bs-toggle="collapse" 
             data-bs-target="#quickDraftCollapse" 
             aria-expanded="false" 
             aria-controls="quickDraftCollapse" 
             style="cursor: pointer;">
            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <span class="badge bg-warning bg-opacity-20 text-warning-emphasis rounded-circle p-1.5">
                    <i class="fas fa-pen-to-square"></i>
                </span>
                <span class="small fw-bold">Quick Draft</span>
            </h6>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 small" style="font-size: 10.5px;">Write Draft ▾</span>
                <i class="fas fa-chevron-down text-muted small"></i>
            </div>
        </div>

        <div class="collapse" id="quickDraftCollapse">
            <div class="p-3 p-md-4">
                <form action="{{ route('author.posts.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="action_type" value="draft">

                    <div class="row g-2">
                        <div class="col-12 col-md-8">
                            <input type="text" name="title" class="form-control form-control-sm rounded-3 fw-semibold" placeholder="Post Title..." required>
                        </div>
                        <div class="col-12 col-md-4">
                            <button type="submit" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3 w-100">
                                <i class="fas fa-save me-1"></i> Save Draft
                            </button>
                        </div>
                        <div class="col-12 mt-2">
                            <textarea name="content" rows="3" class="form-control rounded-3 small" placeholder="Write draft thoughts or excerpt..." required></textarea>
                        </div>
                        <div class="col-12 text-end mt-1">
                            <a href="{{ route('author.posts.create') }}" class="small text-primary text-decoration-none fw-semibold">
                                Full Editor →
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 4. RECENT SALES & E-BOOKS GRID                                             --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="row g-3">
        {{-- Left: Recent Sales & Royalty Earnings --}}
        <div class="col-12 col-lg-8">
            <div class="author-card p-3 p-md-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-receipt text-primary me-1.5"></i> Recent Sales
                    </h6>
                    <a href="{{ route('author.royalties') }}" class="small text-primary text-decoration-none fw-semibold">
                        View All <i class="fas fa-arrow-right small"></i>
                    </a>
                </div>

                {{-- Desktop Table View --}}
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small fw-bold text-secondary">
                            <tr>
                                <th>Date</th>
                                <th>E-Book Title</th>
                                <th>Price</th>
                                <th>Royalty (50%)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($recentRoyalties as $royalty)
                                <tr>
                                    <td class="text-muted">{{ $royalty->created_at->format('d M, Y') }}</td>
                                    <td>
                                        <strong class="text-dark">{{ $royalty->ebook?->title ?? 'E-Book' }}</strong>
                                        <small class="d-block text-muted">Order #{{ $royalty->order?->order_number ?? $royalty->order_id }}</small>
                                    </td>
                                    <td class="font-monospace">৳{{ number_format($royalty->sale_price, 2) }}</td>
                                    <td class="fw-bold text-success font-monospace">+৳{{ number_format($royalty->royalty_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5">Earned</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-receipt fs-3 opacity-25 d-block mb-1"></i>
                                        No sales records yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Card View --}}
                <div class="d-flex flex-column gap-2 d-md-none">
                    @forelse($recentRoyalties as $royalty)
                        <div class="p-2.5 rounded-3 border bg-light bg-opacity-40">
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-1.5">
                                <div class="overflow-hidden">
                                    <h6 class="small fw-bold mb-0 text-dark text-truncate">{{ $royalty->ebook?->title ?? 'E-Book' }}</h6>
                                    <small class="text-muted" style="font-size: 10.5px;">Order #{{ $royalty->order?->order_number ?? $royalty->order_id }}</small>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 10px;">Earned</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between pt-1 border-top border-light-subtle">
                                <span class="text-muted" style="font-size: 11px;">
                                    <i class="fas fa-calendar-day me-1"></i>{{ $royalty->created_at->format('d M, Y') }}
                                </span>
                                <div class="text-end">
                                    <small class="text-muted me-1.5 font-monospace">৳{{ number_format($royalty->sale_price, 2) }}</small>
                                    <strong class="text-success font-monospace">+৳{{ number_format($royalty->royalty_amount, 2) }}</strong>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="fas fa-receipt fs-3 opacity-25 d-block mb-1"></i>
                            No sales records yet.
                        </div>
                    @endforelse
                </div>

            </div>
        </div>

        {{-- Right: My E-Books --}}
        <div class="col-12 col-lg-4">
            <div class="author-card p-3 p-md-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-book text-info me-1.5"></i> My E-Books
                    </h6>
                    <a href="{{ route('author.ebooks.index') }}" class="small text-primary text-decoration-none fw-semibold">
                        Manage
                    </a>
                </div>

                <div class="d-flex flex-column gap-2">
                    @forelse($recentEbooks as $eb)
                        <div class="d-flex align-items-center gap-2 p-2 rounded-3 border bg-light bg-opacity-50">
                            <img src="{{ $eb->cover_url ?? 'https://placehold.co/100x140?text=Cover' }}" 
                                 alt="Cover" class="rounded object-fit-cover flex-shrink-0" style="width: 36px; height: 50px;">
                            <div class="overflow-hidden flex-grow-1">
                                <h6 class="small fw-bold mb-0 text-truncate text-dark">{{ $eb->title }}</h6>
                                <div class="font-monospace small text-primary fw-semibold" style="font-size: 11px;">৳{{ number_format($eb->price, 2) }}</div>
                                <div class="mt-0.5">
                                    @if($eb->mod_status === 'approved')
                                        <span class="badge bg-success-subtle text-success" style="font-size: 9.5px;">Live</span>
                                    @elseif($eb->mod_status === 'rejected')
                                        <span class="badge bg-danger-subtle text-danger" style="font-size: 9.5px;">Revision</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis" style="font-size: 9.5px;">Pending</span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('author.ebooks.edit', $eb->id) }}" class="btn btn-xs btn-outline-secondary rounded-pill p-1 px-2" title="Edit">
                                <i class="fas fa-pen small"></i>
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <p class="small mb-2">No e-books uploaded yet.</p>
                            <a href="{{ route('author.ebooks.create') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                                <i class="fas fa-plus me-1"></i> Upload E-Book
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 5. RECENT TIPS & APPRECIATION                                              --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    @if(isset($recentHonorariums) && $recentHonorariums->isNotEmpty())
        <div class="author-card p-3 p-md-4 bg-white border-start border-4 border-danger">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-circle p-1.5">
                        <i class="fas fa-heart"></i>
                    </span>
                    <span class="small fw-bold">Recent Reader Tips</span>
                </h6>
                <a href="{{ route('author.honorariums') }}" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5 fw-semibold" style="font-size: 11px;">
                    All ({{ $totalHonorariumCount }}) →
                </a>
            </div>

            <div class="row g-2.5 g-md-3">
                @foreach($recentHonorariums as $rh)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="p-2.5 p-md-3 rounded-3 border h-100 position-relative" style="background: #fafaf9;">
                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                <span class="fw-bold text-dark text-truncate small" style="max-width: 65%;">
                                    <i class="fas fa-user-circle text-muted me-1"></i>{{ $rh->display_name }}
                                </span>
                                <span class="badge bg-danger text-white font-monospace rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                    +৳{{ number_format($rh->author_amount, 2) }}
                                </span>
                            </div>
                            @if($rh->post)
                                <a href="{{ route('blog.show', $rh->post->slug ?: $rh->post->id) }}" target="_blank" class="small text-muted text-decoration-none d-block text-truncate mb-1.5 hover-primary" title="{{ $rh->post->title }}" style="font-size: 11.5px;">
                                    <i class="fas fa-newspaper me-1 text-primary"></i>{{ $rh->post->title }}
                                </a>
                            @endif
                            @if($rh->message)
                                <div class="small text-secondary bg-white p-2 rounded-2 border fst-italic mb-1.5" style="font-size: 11px; line-height: 1.35;">
                                    "{{ \Illuminate\Support\Str::limit($rh->message, 80) }}"
                                </div>
                            @endif
                            <div class="small text-muted d-flex align-items-center justify-content-between mt-auto" style="font-size: 10.5px;">
                                <span class="badge {{ $rh->method_badge_class }} rounded-pill px-2 py-0.5" style="font-size: 9px;">{{ strtoupper($rh->payment_method) }}</span>
                                <span>{{ $rh->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 6. RECENT POSTS LIST                                                       --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="author-card p-3 p-md-4 bg-white">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
            <div>
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <span class="badge bg-warning bg-opacity-20 text-warning-emphasis rounded-circle p-1.5">
                        <i class="fas fa-feather-pointed"></i>
                    </span>
                    <span class="small fw-bold">My Posts</span>
                </h6>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('author.posts.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5" style="font-size: 11px;">
                    All ({{ $totalPosts }})
                </a>
                <a href="{{ route('author.posts.create') }}" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3 shadow-xs" style="font-size: 11.5px;">
                    <i class="fas fa-plus me-1"></i> New Post
                </a>
            </div>
        </div>

        {{-- Desktop Table --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small fw-bold text-secondary">
                    <tr>
                        <th style="width: 50px;">Cover</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Views</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($recentPosts as $post)
                        <tr>
                            <td>
                                @php
                                    $imgUrl = $post->cover_url ?: ($post->featured_image ? (str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . ltrim($post->featured_image, '/'))) : 'https://placehold.co/80x60?text=Post');
                                @endphp
                                <img src="{{ $imgUrl }}" alt="Post" class="rounded object-fit-cover shadow-xs" style="width: 40px; height: 30px;">
                            </td>
                            <td>
                                <strong class="text-dark">{{ $post->title }}</strong>
                                @if($post->excerpt)
                                    <small class="text-muted d-block text-truncate" style="max-width: 280px;">{{ $post->excerpt }}</small>
                                @endif
                            </td>
                            <td class="text-muted">{{ $post->created_at->format('d M, Y') }}</td>
                            <td>
                                <span class="text-muted font-monospace"><i class="fas fa-eye me-1"></i>{{ $post->view_count ?? 0 }}</span>
                            </td>
                            <td>
                                @if($post->status === 'published')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5">Live</span>
                                @elseif($post->status === 'pending')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5">Pending</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-0.5">Draft</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('blog.show', $post->slug ?: $post->id) }}" target="_blank" class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye me-1"></i> Read
                                    </a>
                                    @if($post->status !== 'published' && $post->mod_status !== 'approved' && $post->status !== 'pending')
                                        <a href="{{ route('author.posts.edit', $post->id) }}" class="btn btn-outline-secondary" title="Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-newspaper fs-3 opacity-25 d-block mb-1"></i>
                                No posts created yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card List --}}
        <div class="d-flex flex-column gap-2 d-md-none">
            @forelse($recentPosts as $post)
                @php
                    $imgUrl = $post->cover_url ?: ($post->featured_image ? (str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . ltrim($post->featured_image, '/'))) : 'https://placehold.co/80x60?text=Post');
                @endphp
                <div class="p-2.5 rounded-3 border bg-light bg-opacity-30">
                    <div class="d-flex align-items-center gap-2 mb-1.5">
                        <img src="{{ $imgUrl }}" alt="Post" class="rounded object-fit-cover shadow-xs flex-shrink-0" style="width: 44px; height: 36px;">
                        <div class="overflow-hidden flex-grow-1">
                            <h6 class="small fw-bold mb-0 text-dark text-truncate">{{ $post->title }}</h6>
                            <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 10.5px;">
                                <span><i class="fas fa-calendar-day me-1"></i>{{ $post->created_at->format('d M, Y') }}</span>
                                <span><i class="fas fa-eye me-1"></i>{{ $post->view_count ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between pt-1 border-top border-light-subtle">
                        <div>
                            @if($post->status === 'published')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 10px;">Live</span>
                            @elseif($post->status === 'pending')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size: 10px;">Pending</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-0.5" style="font-size: 10px;">Draft</span>
                            @endif
                        </div>
                        <div class="d-flex gap-1.5">
                            <a href="{{ route('blog.show', $post->slug ?: $post->id) }}" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0.5" style="font-size: 10.5px;">
                                <i class="fas fa-eye me-0.5"></i> Read
                            </a>
                            @if($post->status !== 'published' && $post->mod_status !== 'approved' && $post->status !== 'pending')
                                <a href="{{ route('author.posts.edit', $post->id) }}" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0.5" style="font-size: 10.5px;">
                                    <i class="fas fa-pen"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted small">
                    <i class="fas fa-newspaper fs-3 opacity-25 d-block mb-1"></i>
                    No posts created yet.
                </div>
            @endforelse
        </div>
    </div>

</div>

{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- 2. FLOATING ACTION BUTTON (FAB) FOR MOBILE (< 992PX)                        --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
<div class="author-fab-wrapper d-lg-none" id="authorFabContainer">
    <div class="author-fab-backdrop" id="authorFabBackdrop" onclick="closeAuthorFab()"></div>

    <div class="author-fab-menu d-flex flex-column align-items-end gap-2" id="authorFabMenu">
        <a href="{{ route('author.posts.create') }}" class="author-fab-action text-decoration-none shadow-sm">
            <span class="author-fab-label">+ Post</span>
            <span class="author-fab-icon bg-warning text-dark"><i class="fas fa-feather-pointed"></i></span>
        </a>
        <a href="{{ route('author.ebooks.create') }}" class="author-fab-action text-decoration-none shadow-sm">
            <span class="author-fab-label">+ E-Book</span>
            <span class="author-fab-icon bg-primary text-white"><i class="fas fa-cloud-arrow-up"></i></span>
        </a>
        <a href="{{ route('author.ebooks.create') }}" class="author-fab-action text-decoration-none shadow-sm">
            <span class="author-fab-label">+ Manuscript</span>
            <span class="author-fab-icon bg-success text-white"><i class="fas fa-file-arrow-up"></i></span>
        </a>
    </div>

    <button type="button" class="btn btn-warning author-fab-main-btn rounded-circle shadow-lg d-flex align-items-center justify-content-center" id="authorFabMainBtn" onclick="toggleAuthorFab()" title="Quick Actions">
        <i class="fas fa-plus fs-4 text-dark" id="authorFabIcon"></i>
    </button>
</div>

{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- 7. DYNAMIC AUTHOR PHOTO STUDIO MODAL                                       --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="authorPhotoStudioModal" tabindex="-1" aria-labelledby="authorPhotoStudioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0 pt-3 px-4 bg-light">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="authorPhotoStudioModalLabel">
                    <i class="fas fa-camera text-primary"></i>
                    <span>Photo Studio</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                {{-- Interactive Crop Canvas Container --}}
                <div class="text-center mb-3">
                    <div class="position-relative mx-auto rounded-4 overflow-hidden border border-2 border-primary shadow-xs bg-light" 
                         style="width: 240px; height: 240px; cursor: grab; touch-action: none;" id="modalCanvasWrapper"
                         ondragover="event.preventDefault(); this.classList.add('border-warning');"
                         ondragleave="this.classList.remove('border-warning');"
                         ondrop="handleModalFileDrop(event)">
                        <canvas id="modalCropCanvas" width="240" height="240" style="display:block; width:240px; height:240px;"></canvas>
                        
                        {{-- SVG Circular Mask Guide --}}
                        <svg class="position-absolute top-0 start-0 w-100 h-100 pe-none" viewBox="0 0 240 240" style="pointer-events: none; z-index: 5;">
                            <defs>
                                <mask id="authorCropCircleMask">
                                    <rect width="240" height="240" fill="white"/>
                                    <circle cx="120" cy="120" r="115" fill="black"/>
                                </mask>
                            </defs>
                            <rect width="240" height="240" fill="rgba(15, 23, 42, 0.50)" mask="url(#authorCropCircleMask)"/>
                            <circle cx="120" cy="120" r="115" fill="none" stroke="rgba(255, 255, 255, 0.85)" stroke-width="2" stroke-dasharray="6,4"/>
                        </svg>

                        {{-- Placeholder --}}
                        <div id="modalCanvasPlaceholder" class="position-absolute top-0 start-0 w-100 h-100 flex-column align-items-center justify-content-center bg-light text-muted p-3 pointer-events-none text-center" style="display: flex; z-index: 6;">
                            <i class="fas fa-cloud-arrow-up text-primary fs-1 mb-2"></i>
                            <span class="fw-bold text-dark small mb-1">Select / Drop Photo</span>
                            <span class="text-muted" style="font-size: 11px;">JPG, PNG, WebP, HEIC</span>
                        </div>
                    </div>
                    <div class="text-muted small mt-1.5" style="font-size: 11.5px;">
                        <i class="fas fa-hand-pointer text-secondary me-1"></i>Drag to reposition
                    </div>
                </div>

                {{-- File Pickers --}}
                <div class="mb-3">
                    <div class="d-flex gap-2">
                        <label class="btn btn-outline-primary btn-sm flex-grow-1 rounded-pill fw-semibold py-1.5" style="cursor: pointer;">
                            <i class="fas fa-images me-1"></i> Select Photo
                            <input type="file" id="modalAuthorAvatarInput" 
                                   accept="image/jpeg,image/png,image/jpg,image/webp,image/heic,image/heif" 
                                   class="d-none"
                                   onclick="this.value=null;"
                                   onchange="loadModalAuthorImage(this)">
                        </label>
                        <label class="btn btn-outline-secondary btn-sm rounded-pill fw-semibold py-1.5 px-3" style="cursor: pointer;" title="Take Photo">
                            <i class="fas fa-camera me-1"></i> Camera
                            <input type="file" accept="image/*" capture="user" class="d-none" onclick="this.value=null;" onchange="loadModalAuthorImage(this)">
                        </label>
                    </div>
                </div>

                {{-- Zoom / Rotate / Reset Controls --}}
                <div id="modalCropControls" class="p-3 bg-light rounded-3 border mb-3 d-none">
                    <div class="d-flex align-items-center justify-content-between mb-1.5" style="font-size: 11.5px;">
                        <span class="text-muted fw-semibold"><i class="fas fa-magnifying-glass-plus text-primary me-1"></i>Zoom:</span>
                        <span class="badge bg-white text-dark border font-monospace" id="modalZoomValBadge">100%</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <button type="button" class="btn btn-sm btn-white border rounded-circle p-1" style="width:28px;height:28px;" onclick="adjustModalZoom(-0.1)" title="Zoom Out"><i class="fa-solid fa-minus" style="font-size:10px;"></i></button>
                        <input type="range" class="form-range flex-grow-1" id="modalZoomSlider" min="0.01" max="5.0" step="0.01" value="1" oninput="onModalZoomChange(this.value)">
                        <button type="button" class="btn btn-sm btn-white border rounded-circle p-1" style="width:28px;height:28px;" onclick="adjustModalZoom(0.1)" title="Zoom In"><i class="fa-solid fa-plus" style="font-size:10px;"></i></button>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap justify-content-center">
                        <button type="button" class="btn btn-white btn-sm border rounded-pill px-3 py-1 text-dark small" onclick="rotateModalImage(90)">
                            <i class="fas fa-rotate-right me-1 text-primary"></i> Rotate 90°
                        </button>
                        <button type="button" class="btn btn-white btn-sm border rounded-pill px-3 py-1 text-dark small" onclick="resetModalCrop()">
                            <i class="fas fa-arrows-to-circle me-1 text-secondary"></i> Reset
                        </button>
                    </div>
                </div>

                {{-- Alert --}}
                <div id="modalPhotoUploadAlert" class="alert d-none small py-2 px-3 rounded-3 mb-0"></div>
            </div>

            <div class="modal-footer border-0 pt-0 px-4 pb-3.5 d-flex justify-content-between">
                <button type="button" class="btn btn-light rounded-pill px-3.5" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="modalSavePhotoBtn" onclick="submitDynamicAuthorPhoto()" disabled>
                    <span class="spinner-border spinner-border-sm d-none me-1" id="modalPhotoSpinner" role="status"></span>
                    <i class="fas fa-save me-1" id="modalPhotoSaveIcon"></i> Save Photo
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- 8. AUTHOR BIO & PROFILE EDIT MODAL                                         --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="authorBioEditModal" tabindex="-1" aria-labelledby="authorBioEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0 pt-3.5 px-4 bg-light">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="authorBioEditModalLabel">
                    <i class="fas fa-user-pen text-primary"></i>
                    <span>Edit Profile</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="authorBioEditForm" onsubmit="submitAuthorBio(event)">
                <div class="modal-body p-4">
                    
                    <ul class="nav nav-pills nav-fill mb-3 bg-light p-1 rounded-3" id="authorEditTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active small py-1.5 fw-semibold" id="tab-basic-tab" data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" role="tab">
                                <i class="fas fa-user me-1"></i> Basic Info
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link small py-1.5 fw-semibold" id="tab-bio-tab" data-bs-toggle="tab" data-bs-target="#tab-bio" type="button" role="tab">
                                <i class="fas fa-book-open me-1"></i> Bio & Genre
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link small py-1.5 fw-semibold" id="tab-contact-tab" data-bs-toggle="tab" data-bs-target="#tab-contact" type="button" role="tab">
                                <i class="fas fa-address-card me-1"></i> Address & Payout
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="authorEditTabsContent">
                        {{-- TAB 1: BASIC INFO --}}
                        <div class="tab-pane fade show active" id="tab-basic" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" id="editAuthorNameInput" class="form-control rounded-3" value="{{ auth()->user()->name }}" required placeholder="Full Name">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Pen Name <span class="text-muted small">(Optional)</span></label>
                                    <input type="text" id="editAuthorPenNameInput" class="form-control rounded-3" value="{{ $authorPenName }}" placeholder="e.g. Literary Pen Name">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Phone Number</label>
                                    <input type="text" id="editAuthorPhoneInput" class="form-control rounded-3" value="{{ auth()->user()->phone ?? ($author?->phone ?? '') }}" placeholder="017XXXXXXXX">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Email <span class="text-muted small">(Login ID)</span></label>
                                    <input type="email" class="form-control rounded-3 bg-light" value="{{ auth()->user()->email }}" readonly disabled>
                                </div>
                            </div>
                        </div>

                        {{-- TAB 2: BIO & GENRE --}}
                        <div class="tab-pane fade" id="tab-bio" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label small fw-bold text-dark mb-0">Author Bio</label>
                                        <span class="text-muted small" id="editBioCounter" style="font-size: 11px;">0 chars</span>
                                    </div>
                                    <textarea id="editAuthorBioInput" rows="5" class="form-control rounded-3 small" placeholder="Write your author biography, published works, or literary background..." oninput="document.getElementById('editBioCounter').textContent = this.value.length + ' chars'">{{ $authorBioText }}</textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Genre / Field</label>
                                    <input type="text" id="editAuthorGenreInput" class="form-control rounded-3" value="{{ $author?->genre ?? ($userRegData['genre'] ?? '') }}" placeholder="e.g. Poetry, Fiction, Research">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Website / Social URL</label>
                                    <input type="url" id="editAuthorWebsiteInput" class="form-control rounded-3" value="{{ $author?->website ?? ($userRegData['website'] ?? '') }}" placeholder="https://...">
                                </div>
                            </div>
                        </div>

                        {{-- TAB 3: CONTACT & PAYOUT --}}
                        <div class="tab-pane fade" id="tab-contact" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Father's Name</label>
                                    <input type="text" id="editAuthorFatherNameInput" class="form-control rounded-3" value="{{ $fatherName }}" placeholder="Father's Name">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Mother's Name</label>
                                    <input type="text" id="editAuthorMotherNameInput" class="form-control rounded-3" value="{{ $motherName }}" placeholder="Mother's Name">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">NID / Passport</label>
                                    <input type="text" id="editAuthorNidInput" class="form-control rounded-3" value="{{ $nidOrPassport }}" placeholder="NID or Passport number">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Present Address</label>
                                    <input type="text" id="editAuthorAddressInput" class="form-control rounded-3" value="{{ $presentAddress }}" placeholder="Address...">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Payout Method</label>
                                    <select id="editAuthorPayoutMethodSelect" class="form-select rounded-3">
                                        <option value="">Select Method</option>
                                        <option value="bkash" {{ strtolower($payoutMethod ?? '') === 'bkash' ? 'selected' : '' }}>bKash</option>
                                        <option value="nagad" {{ strtolower($payoutMethod ?? '') === 'nagad' ? 'selected' : '' }}>Nagad</option>
                                        <option value="rocket" {{ strtolower($payoutMethod ?? '') === 'rocket' ? 'selected' : '' }}>Rocket</option>
                                        <option value="bank" {{ strtolower($payoutMethod ?? '') === 'bank' ? 'selected' : '' }}>Bank Account</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-dark">Payout Number / Account</label>
                                    <input type="text" id="editAuthorPayoutNumberInput" class="form-control rounded-3" value="{{ $payoutNumber }}" placeholder="Account or phone number">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="editBioAlert" class="alert d-none small py-2 px-3 rounded-3 mt-3 mb-0"></div>
                </div>

                <div class="modal-footer border-0 pt-0 px-4 pb-3.5 d-flex justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-3.5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="saveBioSubmitBtn">
                        <span class="spinner-border spinner-border-sm d-none me-1" id="saveBioSpinner"></span>
                        <i class="fas fa-save me-1" id="saveBioIcon"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.12) !important;
}

/* Mobile Floating Action Button & Speed Dial */
.author-fab-wrapper {
    position: fixed;
    bottom: 78px;
    right: 18px;
    z-index: 1040;
}
.author-fab-main-btn {
    width: 54px;
    height: 54px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
    border: 3px solid #ffffff;
    transition: transform 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.author-fab-main-btn:active {
    transform: scale(0.92);
}
.author-fab-wrapper.active .author-fab-main-btn {
    transform: rotate(45deg);
    background-color: #ef4444 !important;
    border-color: #ffffff;
    color: #ffffff !important;
}
.author-fab-wrapper.active .author-fab-main-btn i {
    color: #ffffff !important;
}
.author-fab-menu {
    position: absolute;
    bottom: 66px;
    right: 4px;
    opacity: 0;
    pointer-events: none;
    transform: translateY(15px);
    transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.author-fab-wrapper.active .author-fab-menu {
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0);
}
.author-fab-action {
    display: flex;
    align-items: center;
    gap: 10px;
    white-space: nowrap;
}
.author-fab-label {
    background: rgba(15, 23, 42, 0.90);
    backdrop-filter: blur(6px);
    color: #ffffff;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.author-fab-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #ffffff;
    font-size: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.18);
}
.author-fab-backdrop {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.4);
    backdrop-filter: blur(2px);
    z-index: -1;
}
.author-fab-wrapper.active .author-fab-backdrop {
    display: block;
}
</style>

<script>
// Floating Action Button Toggle
function toggleAuthorFab() {
    const container = document.getElementById('authorFabContainer');
    if (container) {
        container.classList.toggle('active');
    }
}

function closeAuthorFab() {
    const container = document.getElementById('authorFabContainer');
    if (container) {
        container.classList.remove('active');
    }
}

// Interactive Photo Studio Cropper
let modalCanvas = document.getElementById('modalCropCanvas');
let modalCtx = modalCanvas ? modalCanvas.getContext('2d') : null;
let modalCurrentImg = null;
let modalImgX = 120;
let modalImgY = 120;
let modalScale = 1;
let modalRotation = 0;
let modalIsDragging = false;
let modalStartX, modalStartY;
let modalCroppedDataUrl = null;

function openPhotoStudioModal() {
    const modalEl = document.getElementById('authorPhotoStudioModal');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        setTimeout(() => {
            if (!modalCanvas) modalCanvas = document.getElementById('modalCropCanvas');
            if (modalCanvas && !modalCtx) modalCtx = modalCanvas.getContext('2d');
            if (modalCurrentImg) renderModalCanvas();
        }, 200);
    }
}

function openBioEditModal() {
    const modalEl = document.getElementById('authorBioEditModal');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
}

function loadModalAuthorImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Instant preview
        const mainBox = document.getElementById('dashAvatarMainBox');
        if (mainBox) {
            try {
                const objectUrl = URL.createObjectURL(file);
                mainBox.innerHTML = `<img src="${objectUrl}" alt="Preview" class="w-100 h-100 object-fit-cover current-author-avatar-img">`;
            } catch(e) {}
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const dataUri = e.target.result;
            modalCurrentImg = new Image();
            modalCurrentImg.onload = function() {
                modalCanvas = document.getElementById('modalCropCanvas');
                if (modalCanvas) {
                    modalCanvas.width = 240;
                    modalCanvas.height = 240;
                    modalCtx = modalCanvas.getContext('2d');
                }

                const placeholder = document.getElementById('modalCanvasPlaceholder');
                if (placeholder) {
                    placeholder.classList.add('d-none');
                    placeholder.style.display = 'none';
                }
                
                const controls = document.getElementById('modalCropControls');
                if (controls) {
                    controls.classList.remove('d-none');
                    controls.style.display = 'block';
                }
                
                const saveBtn = document.getElementById('modalSavePhotoBtn');
                if (saveBtn) saveBtn.disabled = false;
                
                const canvasW = 240;
                const canvasH = 240;
                const scaleW = canvasW / modalCurrentImg.width;
                const scaleH = canvasH / modalCurrentImg.height;
                modalScale = Math.max(scaleW, scaleH);
                
                const slider = document.getElementById('modalZoomSlider');
                if (slider) {
                    const minScale = Math.min(scaleW, scaleH) * 0.4;
                    const maxScale = Math.max(scaleW, scaleH) * 4.0;
                    slider.min = Math.max(0.001, minScale).toFixed(4);
                    slider.max = Math.max(minScale + 0.1, maxScale).toFixed(4);
                    slider.step = ((parseFloat(slider.max) - parseFloat(slider.min)) / 100).toFixed(4);
                    slider.value = modalScale;
                }
                const badge = document.getElementById('modalZoomValBadge');
                if (badge) badge.textContent = '100%';
                
                modalImgX = canvasW / 2;
                modalImgY = canvasH / 2;
                modalRotation = 0;
                
                renderModalCanvas();
                exportModalCroppedAvatar();
            };
            modalCurrentImg.src = dataUri;
        };
        reader.readAsDataURL(file);
    }
}

function renderModalCanvas() {
    if (!modalCanvas) modalCanvas = document.getElementById('modalCropCanvas');
    if (modalCanvas && !modalCtx) modalCtx = modalCanvas.getContext('2d');
    if (!modalCurrentImg || !modalCtx) return;
    
    modalCtx.clearRect(0, 0, modalCanvas.width, modalCanvas.height);
    modalCtx.fillStyle = '#f8fafc';
    modalCtx.fillRect(0, 0, modalCanvas.width, modalCanvas.height);
    
    modalCtx.save();
    modalCtx.imageSmoothingEnabled = true;
    modalCtx.imageSmoothingQuality = 'high';
    
    modalCtx.translate(modalImgX, modalImgY);
    modalCtx.rotate((modalRotation * Math.PI) / 180);
    modalCtx.scale(modalScale, modalScale);
    
    modalCtx.drawImage(modalCurrentImg, -modalCurrentImg.width / 2, -modalCurrentImg.height / 2);
    modalCtx.restore();
}

function exportModalCroppedAvatar() {
    if (!modalCurrentImg) return;
    const highRes = document.createElement('canvas');
    highRes.width = 500;
    highRes.height = 500;
    const hrCtx = highRes.getContext('2d');
    
    hrCtx.fillStyle = '#ffffff';
    hrCtx.fillRect(0, 0, 500, 500);
    
    const canvasW = modalCanvas ? modalCanvas.width : 240;
    const ratio = 500 / canvasW;
    hrCtx.save();
    hrCtx.imageSmoothingEnabled = true;
    hrCtx.imageSmoothingQuality = 'high';
    
    hrCtx.translate(modalImgX * ratio, modalImgY * ratio);
    hrCtx.rotate((modalRotation * Math.PI) / 180);
    hrCtx.scale(modalScale * ratio, modalScale * ratio);
    hrCtx.drawImage(modalCurrentImg, -modalCurrentImg.width / 2, -modalCurrentImg.height / 2);
    hrCtx.restore();
    
    modalCroppedDataUrl = highRes.toDataURL('image/jpeg', 0.90);
}

function onModalZoomChange(val) {
    modalScale = parseFloat(val);
    const slider = document.getElementById('modalZoomSlider');
    const min = parseFloat(slider.min) || 0.01;
    const max = parseFloat(slider.max) || 5.0;
    const pct = Math.round(((modalScale - min) / (max - min)) * 100);
    const badge = document.getElementById('modalZoomValBadge');
    if (badge) badge.textContent = `${pct}%`;
    renderModalCanvas();
}

function adjustModalZoom(delta) {
    const slider = document.getElementById('modalZoomSlider');
    if (!slider) return;
    const range = parseFloat(slider.max) - parseFloat(slider.min);
    let newVal = parseFloat(slider.value) + (delta * (range / 10));
    newVal = Math.max(parseFloat(slider.min), Math.min(parseFloat(slider.max), newVal));
    slider.value = newVal;
    onModalZoomChange(newVal);
}

function rotateModalImage(deg) {
    modalRotation = (modalRotation + deg) % 360;
    renderModalCanvas();
}

function resetModalCrop() {
    if (!modalCurrentImg) return;
    const canvasW = 240;
    const canvasH = 240;
    modalImgX = canvasW / 2;
    modalImgY = canvasH / 2;
    const scaleW = canvasW / modalCurrentImg.width;
    const scaleH = canvasH / modalCurrentImg.height;
    modalScale = Math.max(scaleW, scaleH);
    modalRotation = 0;
    
    const slider = document.getElementById('modalZoomSlider');
    if (slider) {
        slider.value = modalScale;
    }
    const badge = document.getElementById('modalZoomValBadge');
    if (badge) badge.textContent = '100%';
    renderModalCanvas();
}

// Touch & Mouse Drag Handlers
const modalWrapper = document.getElementById('modalCanvasWrapper');

function getModalPos(e) {
    if (!modalCanvas) modalCanvas = document.getElementById('modalCropCanvas');
    const rect = modalCanvas ? modalCanvas.getBoundingClientRect() : { left: 0, top: 0 };
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
    return {
        x: clientX - rect.left,
        y: clientY - rect.top
    };
}

if (modalWrapper) {
    const startModalDrag = (e) => {
        if (!modalCurrentImg) return;
        modalIsDragging = true;
        modalWrapper.style.cursor = 'grabbing';
        const pos = getModalPos(e);
        modalStartX = pos.x - modalImgX;
        modalStartY = pos.y - modalImgY;
    };

    const onModalDrag = (e) => {
        if (!modalIsDragging || !modalCurrentImg) return;
        if (e.cancelable) e.preventDefault();
        const pos = getModalPos(e);
        modalImgX = pos.x - modalStartX;
        modalImgY = pos.y - modalStartY;
        renderModalCanvas();
    };

    const stopModalDrag = () => {
        if (modalIsDragging) {
            modalIsDragging = false;
            modalWrapper.style.cursor = 'grab';
            exportModalCroppedAvatar();
        }
    };

    modalWrapper.addEventListener('mousedown', startModalDrag);
    window.addEventListener('mousemove', onModalDrag);
    window.addEventListener('mouseup', stopModalDrag);

    modalWrapper.addEventListener('touchstart', startModalDrag, { passive: false });
    window.addEventListener('touchmove', onModalDrag, { passive: false });
    window.addEventListener('touchend', stopModalDrag);
}

// Submit Photo via AJAX
function submitDynamicAuthorPhoto() {
    exportModalCroppedAvatar();
    
    const fileInput = document.getElementById('modalAuthorAvatarInput');
    const formData = new FormData();
    
    if (modalCroppedDataUrl) {
        formData.append('avatar_cropped', modalCroppedDataUrl);
    }
    if (fileInput && fileInput.files && fileInput.files[0]) {
        formData.append('avatar', fileInput.files[0]);
    }
    
    const saveBtn = document.getElementById('modalSavePhotoBtn');
    const spinner = document.getElementById('modalPhotoSpinner');
    const icon = document.getElementById('modalPhotoSaveIcon');
    const alertBox = document.getElementById('modalPhotoUploadAlert');
    
    saveBtn.disabled = true;
    spinner.classList.remove('d-none');
    icon.classList.add('d-none');
    alertBox.className = 'alert d-none';
    
    fetch('{{ route("author.profile.avatar") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        saveBtn.disabled = false;
        spinner.classList.add('d-none');
        icon.classList.remove('d-none');
        
        if (data.success) {
            alertBox.className = 'alert alert-success small py-2 px-3 rounded-3 mb-0';
            alertBox.textContent = data.message || 'Photo updated successfully.';
            alertBox.classList.remove('d-none');
            
            const newUrl = data.avatar_url;
            const mainBox = document.getElementById('dashAvatarMainBox');
            if (mainBox && newUrl) {
                mainBox.innerHTML = `<img src="${newUrl}?v=${Date.now()}" alt="Author Avatar" class="w-100 h-100 object-fit-cover current-author-avatar-img">`;
            }
            document.querySelectorAll('.header-author-avatar-img, .current-author-avatar-img').forEach(img => {
                img.src = `${newUrl}?v=${Date.now()}`;
            });
            
            setTimeout(() => {
                const modalEl = document.getElementById('authorPhotoStudioModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }, 1200);
        } else {
            alertBox.className = 'alert alert-danger small py-2 px-3 rounded-3 mb-0';
            alertBox.textContent = data.message || 'Failed to upload photo.';
            alertBox.classList.remove('d-none');
        }
    })
    .catch(err => {
        saveBtn.disabled = false;
        spinner.classList.add('d-none');
        icon.classList.remove('d-none');
        alertBox.className = 'alert alert-danger small py-2 px-3 rounded-3 mb-0';
        alertBox.textContent = 'Server connection error. Please try again.';
        alertBox.classList.remove('d-none');
    });
}

function handleModalFileDrop(e) {
    e.preventDefault();
    const wrapper = document.getElementById('modalCanvasWrapper');
    if (wrapper) wrapper.classList.remove('border-warning');
    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
        const file = e.dataTransfer.files[0];
        if (!file.type.match('image.*')) {
            alert('Please drop an image file (JPG, PNG, WebP).');
            return;
        }
        const fakeInput = { files: [file] };
        loadModalAuthorImage(fakeInput);
    }
}

// Submit Profile Bio via AJAX
function submitAuthorBio(e) {
    e.preventDefault();
    const name = document.getElementById('editAuthorNameInput')?.value.trim() || '';
    const penName = document.getElementById('editAuthorPenNameInput')?.value.trim() || '';
    const phone = document.getElementById('editAuthorPhoneInput')?.value.trim() || '';
    const bio = document.getElementById('editAuthorBioInput')?.value.trim() || '';
    const genre = document.getElementById('editAuthorGenreInput')?.value.trim() || '';
    const website = document.getElementById('editAuthorWebsiteInput')?.value.trim() || '';
    const fatherName = document.getElementById('editAuthorFatherNameInput')?.value.trim() || '';
    const motherName = document.getElementById('editAuthorMotherNameInput')?.value.trim() || '';
    const nidOrPassport = document.getElementById('editAuthorNidInput')?.value.trim() || '';
    const presentAddress = document.getElementById('editAuthorAddressInput')?.value.trim() || '';
    const payoutMethod = document.getElementById('editAuthorPayoutMethodSelect')?.value.trim() || '';
    const payoutNumber = document.getElementById('editAuthorPayoutNumberInput')?.value.trim() || '';
    
    const saveBtn = document.getElementById('saveBioSubmitBtn');
    const spinner = document.getElementById('saveBioSpinner');
    const icon = document.getElementById('saveBioIcon');
    const alertBox = document.getElementById('editBioAlert');
    
    saveBtn.disabled = true;
    spinner.classList.remove('d-none');
    icon.classList.add('d-none');
    alertBox.className = 'alert d-none';
    
    const payload = {
        name: name,
        pen_name: penName,
        phone: phone,
        bio: bio,
        genre: genre,
        website: website,
        father_name: fatherName,
        mother_name: motherName,
        nid_or_passport: nidOrPassport,
        present_address: presentAddress,
        payout_method: payoutMethod,
        payout_number: payoutNumber
    };
    
    fetch('{{ route("author.profile.update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        saveBtn.disabled = false;
        spinner.classList.add('d-none');
        icon.classList.remove('d-none');
        
        if (data.success) {
            alertBox.className = 'alert alert-success small py-2 px-3 rounded-3 mb-0';
            alertBox.textContent = data.message || 'Profile updated successfully.';
            alertBox.classList.remove('d-none');
            
            const nameDisplay = document.getElementById('dashAuthorNameDisplay');
            if (nameDisplay && name) nameDisplay.textContent = name;
            const bioDisplay = document.getElementById('dashAuthorBioDisplay');
            if (bioDisplay) bioDisplay.textContent = bio || '';
            
            setTimeout(() => {
                const modalEl = document.getElementById('authorBioEditModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                window.location.reload();
            }, 800);
        } else {
            alertBox.className = 'alert alert-danger small py-2 px-3 rounded-3 mb-0';
            alertBox.textContent = data.message || 'Could not update profile.';
            alertBox.classList.remove('d-none');
        }
    })
    .catch(err => {
        saveBtn.disabled = false;
        spinner.classList.add('d-none');
        icon.classList.remove('d-none');
        alertBox.className = 'alert alert-danger small py-2 px-3 rounded-3 mb-0';
        alertBox.textContent = 'Server error. Please try again.';
        alertBox.classList.remove('d-none');
    });
}
</script>
@endsection
