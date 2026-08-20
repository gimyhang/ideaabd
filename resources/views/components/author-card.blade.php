@php
    $photoUrl = $author->avatar_url;
    $initials = $author->initials ?? 'লে';
    $bgColor = $author->avatar_bg_color ?? 'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)';
    $booksCount = $author->books_count ?? 0;
    $authorBooks = $author->relationLoaded('books') ? $author->books : collect();
@endphp

<div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden author-dir-card bg-white position-relative transition-all hover-lift">
    {{-- Card Body --}}
    <div class="card-body p-3 p-md-3.5 d-flex flex-column justify-content-between h-100">
        <div>
            {{-- Top Header: Avatar & Info --}}
            <div class="d-flex align-items-start gap-3 mb-3">
                {{-- Avatar --}}
                <a href="{{ route('authors.show', $author->slug ?: $author->id) }}" class="text-decoration-none flex-shrink-0 position-relative">
                    <div class="rounded-circle overflow-hidden shadow-xs position-relative border border-2 border-white" 
                         style="width: 68px; height: 68px; min-width: 68px; min-height: 68px; aspect-ratio: 1 / 1; background: {{ $bgColor }};">
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
                        <span class="position-absolute bottom-0 end-0 bg-info text-white rounded-circle p-1 d-flex align-items-center justify-content-center border border-2 border-white shadow-xs" 
                              style="width: 20px; height: 20px; font-size: 9px;" title="যাচাইকৃত লেখক">
                            <i class="fas fa-check"></i>
                        </span>
                    @endif
                </a>

                {{-- Name & Badges --}}
                <div class="min-w-0 flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                        <h6 class="fw-bold text-dark mb-0 text-truncate fs-6" title="{{ $author->name }}">
                            <a href="{{ route('authors.show', $author->slug ?: $author->id) }}" class="text-decoration-none text-dark hover-primary">
                                {{ $author->name }}
                            </a>
                        </h6>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-1.5 mb-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                            <i class="fas fa-book-open me-1"></i>@bn($booksCount)টি বই
                        </span>

                        @if($booksCount >= 5)
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size: 0.70rem;">
                                <i class="fas fa-star me-1 text-warning"></i>শীর্ষ লেখক
                            </span>
                        @endif
                    </div>

                    {{-- Bio Snippet --}}
                    <p class="text-muted small mb-0 line-clamp-2" style="font-size: 0.79rem; line-height: 1.45;">
                        {{ $author->bio ? Str::limit(strip_tags($author->bio), 75) : 'আইডিয়া প্রকাশন প্ল্যাটফর্মের সম্মানিত লেখক।' }}
                    </p>
                </div>
            </div>

            {{-- Mini Book Preview Thumbnails --}}
            @if($authorBooks->isNotEmpty())
                <div class="p-2 bg-light rounded-3 mb-3 border border-light-subtle">
                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">প্রকাশিত বইসমূহ:</span>
                        <a href="{{ route('authors.show', $author->slug ?: $author->id) }}" class="text-primary text-decoration-none small" style="font-size: 0.72rem;">সব দেখুন →</a>
                    </div>
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        @foreach($authorBooks->take(3) as $bk)
                            <a href="{{ route('book.show', $bk->slug ?: $bk->id) }}" class="text-decoration-none" title="{{ $bk->title }}">
                                <div class="rounded overflow-hidden shadow-xs bg-white border border-light" style="width: 36px; height: 48px; flex-shrink: 0;">
                                    @if(!empty($bk->cover_image))
                                        <img src="{{ asset('storage/' . ltrim($bk->cover_image, '/')) }}" alt="{{ $bk->title }}" class="w-100 h-100 object-fit-cover">
                                    @else
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-muted" style="font-size: 10px;">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                        <div class="min-w-0 ms-1">
                            <small class="text-dark fw-semibold d-block text-truncate" style="font-size: 0.75rem;">{{ $authorBooks->first()->title ?? '' }}</small>
                            <small class="text-muted" style="font-size: 0.7rem;">৳@bn($authorBooks->first()->price ?? 0)</small>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer Action Button --}}
        <div>
            <a href="{{ route('authors.show', $author->slug ?: $author->id) }}" class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-semibold d-flex align-items-center justify-content-center gap-1.5 py-1.5" style="font-size: 0.82rem;">
                <span>সকল বই ও পূর্ণাঙ্গ প্রোফাইল</span>
                <i class="fas fa-arrow-right small"></i>
            </a>
        </div>
    </div>
</div>
