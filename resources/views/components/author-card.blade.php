<div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden hover-lift p-3 p-md-4" style="transition: all 0.25s ease; background: #ffffff;">
    <div class="d-flex align-items-start gap-3">
        <!-- Avatar -->
        @php
            $photoUrl = $author->avatar_url;
            $initials = $author->initials ?? 'লে';
            $bgColor = $author->avatar_bg_color ?? 'linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%)';
        @endphp
        <a href="{{ route('authors.show', $author->slug ?: $author->id) }}" class="text-decoration-none flex-shrink-0">
            <div class="rounded-circle overflow-hidden shadow-sm position-relative border border-2 border-light" 
                 style="width: 72px; height: 72px; background: {{ $bgColor }};">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $author->name }}" class="w-100 h-100 object-fit-cover"
                         onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                    <div class="w-100 h-100 d-none d-flex align-items-center justify-content-center text-white fs-4 fw-bold" style="background: {{ $bgColor }};">
                        {{ $initials }}
                    </div>
                @else
                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fs-4 fw-bold">
                        {{ $initials }}
                    </div>
                @endif
            </div>
        </a>

        <!-- Details -->
        <div class="flex-grow-1 min-w-0">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <h6 class="fw-bold text-dark mb-0 text-truncate" title="{{ $author->name }}">
                    <a href="{{ route('authors.show', $author->slug ?: $author->id) }}" class="text-decoration-none text-dark hover-primary">
                        {{ $author->name }}
                    </a>
                </h6>
                @if(!empty($author->is_verified))
                    <span class="badge bg-primary-subtle text-primary rounded-circle p-1" title="যাচাইকৃত লেখক">
                        <i class="fa-solid fa-check fs-6" style="font-size: 0.7rem;"></i>
                    </span>
                @endif
            </div>

            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-light text-muted border small" style="font-size: 0.75rem;">
                    <i class="fa-solid fa-book-open text-primary me-1"></i>
                    @bn($author->books_count ?? 0)টি বই
                </span>
            </div>

            <p class="text-muted small line-clamp-2 mb-3" style="font-size: 0.82rem; line-height: 1.5;">
                {{ $author->bio ? Str::limit(strip_tags($author->bio), 80) : 'আইডিয়া প্ল্যাটফর্মের সম্মানিত লেখক।' }}
            </p>

            <a href="{{ route('authors.show', $author->slug ?: $author->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold w-100" style="font-size: 0.8rem;">
                সকল বই ও প্রোফাইল →
            </a>
        </div>
    </div>
</div>
