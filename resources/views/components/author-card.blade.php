@php
    $photoUrl = $author->avatar_url;
    $initials = $author->initials ?? 'লে';
    $bgColor = $author->avatar_bg_color ?? 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)';
    $booksCount = $author->books_count ?? 0;
    $authorBooks = $author->relationLoaded('books') ? $author->books : collect();
    $penName = $author->pen_name;
    $authorUrl = route('authors.show', $author->slug ?: $author->id);
@endphp

<div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden author-dir-card bg-white position-relative transition-all hover-lift d-flex flex-column justify-content-between">
    {{-- Card Header & Body --}}
    <div class="card-body p-3.5 d-flex flex-column justify-content-between h-100">
        <div>
            {{-- Top Row: 1:1 Avatar + Details --}}
            <div class="d-flex align-items-center gap-3 mb-3">
                {{-- 1:1 Circular Avatar Box --}}
                <a href="{{ $authorUrl }}" class="text-decoration-none flex-shrink-0 position-relative">
                    <div class="rounded-circle overflow-hidden shadow-xs position-relative border border-2 border-white" 
                         style="width: 64px; height: 64px; min-width: 64px; min-height: 64px; aspect-ratio: 1 / 1; background: {{ $bgColor }};">
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="{{ $author->name }}" class="w-100 h-100 object-fit-cover position-absolute top-0 start-0"
                                 onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                            <div class="w-100 h-100 d-none d-flex align-items-center justify-content-center text-white fs-4 fw-bold position-absolute top-0 start-0" style="background: {{ $bgColor }};">
                                {{ $initials }}
                            </div>
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fs-4 fw-bold position-absolute top-0 start-0">
                                {{ $initials }}
                            </div>
                        @endif
                    </div>

                    @if($author->is_verified)
                        <span class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-1 d-flex align-items-center justify-content-center border border-2 border-white shadow-xs" 
                              style="width: 20px; height: 20px; font-size: 9px;" title="যাচাইকৃত লেখক">
                            <i class="fas fa-check"></i>
                        </span>
                    @endif
                </a>

                {{-- Author Identity --}}
                <div class="min-w-0 flex-grow-1">
                    <h6 class="fw-bold text-dark mb-1 text-truncate fs-6" title="{{ $author->name }}">
                        <a href="{{ $authorUrl }}" class="text-decoration-none text-dark hover-primary">
                            {{ $author->name }}
                        </a>
                    </h6>

                    @if($penName)
                        <div class="text-muted small text-truncate mb-1" style="font-size: 0.76rem;">
                            <i class="fa-solid fa-feather-pointed text-warning me-1"></i>{{ $penName }}
                        </div>
                    @endif

                    <div class="d-flex flex-wrap align-items-center gap-1.5">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 rounded-pill px-2.5 py-0.5" style="font-size: 0.72rem;">
                            <i class="fas fa-book-open me-1"></i>@bn($booksCount)টি বই
                        </span>

                        @if($author->is_verified)
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 rounded-pill px-2 py-0.5" style="font-size: 0.70rem;">
                                <i class="fas fa-circle-check me-0.5"></i>যাচাইকৃত
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Truncated Bio Snippet (Uniform Height) --}}
            <div class="mb-3">
                <p class="text-muted small mb-0 line-clamp-2" style="font-size: 0.82rem; line-height: 1.5; min-height: 2.45rem;">
                    {{ $author->bio ? Str::limit(strip_tags($author->bio), 80) : 'আইডিয়া প্রকাশন প্ল্যাটফর্মের সম্মানিত লেখক।' }}
                </p>
            </div>

            {{-- Mini Book Preview Box (Fixed Baseline) --}}
            <div class="p-2.5 bg-light rounded-3 mb-3 border border-light-subtle" style="min-height: 58px;">
                @if($authorBooks->isNotEmpty())
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        @foreach($authorBooks->take(3) as $bk)
                            <a href="{{ route('book.show', $bk->slug ?: $bk->id) }}" class="text-decoration-none" title="{{ $bk->title }}">
                                <div class="rounded overflow-hidden shadow-2xs bg-white border border-light" style="width: 32px; height: 42px; flex-shrink: 0;">
                                    @if(!empty($bk->cover_image))
                                        <img src="{{ asset('storage/' . ltrim($bk->cover_image, '/')) }}" alt="{{ $bk->title }}" class="w-100 h-100 object-fit-cover">
                                    @else
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-muted" style="font-size: 9px;">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                        <div class="min-w-0 ms-1">
                            <small class="text-dark fw-semibold d-block text-truncate" style="font-size: 0.75rem;">{{ $authorBooks->first()->title ?? '' }}</small>
                            <small class="text-primary fw-bold" style="font-size: 0.72rem;">৳@bn($authorBooks->first()->price ?? 0)</small>
                        </div>
                    </div>
                @else
                    <div class="d-flex align-items-center justify-content-between h-100 text-muted" style="font-size: 0.75rem;">
                        <span><i class="fa-solid fa-feather-pointed me-1 text-secondary"></i>আইডিয়াপত্র ও সাহিত্য সাধনা</span>
                        <span class="text-primary">প্রোফাইল →</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Footer Action Button --}}
        <div>
            <a href="{{ $authorUrl }}" class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-semibold d-flex align-items-center justify-content-center gap-1.5 py-1.5" style="font-size: 0.82rem;">
                <span>সকল বই ও সাহিত্য সম্ভার</span>
                <i class="fas fa-arrow-right small"></i>
            </a>
        </div>
    </div>
</div>

