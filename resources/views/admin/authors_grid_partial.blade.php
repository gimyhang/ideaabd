{{--
    Author directory results: cards (grid) and rows (list) for the same page of authors, plus pagination.
    Rendered on page load and by live filtering (AdminController@authors, ?ajax_filter=1).
    Which of the two shows is a class on #adResults (remembered per browser).
--}}
@if ($authors->isEmpty())
    <div class="ad-empty">
        <h5>No authors found</h5>
        <p>{{ request()->hasAny(['search', 'author_type', 'has_books', 'is_verified']) || request('is_active') !== null ? 'Nothing matches these filters.' : 'Add the first author to the directory.' }}</p>
        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-ad-clear>Clear filters</button>
    </div>
@else
    @php
        $adRows = $authors->map(function ($a) {
            $userPending = $a->user && $a->user->reg_status === 'pending';
            return (object) [
                'a' => $a,
                'avatar' => $a->avatar_url,
                'initials' => $a->initials,
                'bg' => $a->avatar_bg_color,
                'sub' => collect([$a->name_en && $a->name_en !== $a->name ? $a->name_en : null, $a->name_bn && $a->name_bn !== $a->name ? $a->name_bn : null])->filter()->first(),
                'pending' => !$a->is_active || $userPending,
                'portal' => (bool) $a->user_id,
                'public' => route('authors.show', $a->slug ?: $a->id),
                'contact' => $a->phone ?: ($a->email ?: ($a->user->phone ?? $a->user->email ?? null)),
            ];
        });
    @endphp

    {{-- grid --}}
    <div class="ad-grid">
        @foreach ($adRows as $r)
            @php $a = $r->a; @endphp
            <article class="ad-card {{ $a->is_active ? '' : 'is-off' }}" id="authorCard-{{ $a->id }}" data-id="{{ $a->id }}">
                <header class="ad-card__top">
                    <label class="ad-check" title="Select">
                        <input type="checkbox" class="form-check-input author-select-checkbox" value="{{ $a->id }}" onchange="updateBulkSelectionState()">
                    </label>
                    <label class="ad-live form-switch" title="Show in the public directory">
                        <input class="form-check-input ad-live-input" type="checkbox" role="switch" data-id="{{ $a->id }}" @checked($a->is_active) onchange="toggleAuthorStatusAjax({{ $a->id }}, this)">
                    </label>
                </header>

                <button type="button" class="ad-avatar" onclick="openAuthorDetailsModal({{ $a->id }})" title="Open profile" style="--ad-bg: {{ $r->bg }}">
                    @if ($r->avatar)
                        <img src="{{ $r->avatar }}" alt="" loading="lazy" onerror="this.remove()">
                    @endif
                    <span>{{ $r->initials }}</span>
                </button>

                <h3 class="ad-name">
                    <button type="button" onclick="openAuthorDetailsModal({{ $a->id }})">{{ $a->name }}</button>
                    @if ($a->is_verified)<span class="ad-tick" title="Verified" aria-label="Verified">✓</span>@endif
                </h3>
                @if ($r->sub)<div class="ad-sub">{{ $r->sub }}</div>@endif

                <div class="ad-tags">
                    @if ($r->pending)<span class="ad-tag ad-tag--warn">Pending</span>@endif
                    @if ($r->portal)<span class="ad-tag ad-tag--blue" title="Has a writer portal login">Portal</span>@endif
                    <a class="ad-tag" href="{{ route('admin.books', ['author_id' => $a->id]) }}" title="This author's books">{{ number_format($a->books_count) }} {{ $a->books_count === 1 ? 'book' : 'books' }}</a>
                </div>

                @if ($a->books->isNotEmpty())
                    <div class="ad-books" aria-label="Best-selling books">
                        @foreach ($a->books as $b)
                            <a href="{{ route('admin.content.edit', ['type' => 'books', 'id' => $b->id]) }}" title="{{ $b->title }}">
                                @if ($b->cover_image)
                                    <img src="{{ str_starts_with($b->cover_image, 'http') ? $b->cover_image : asset('storage/' . ltrim($b->cover_image, '/')) }}" alt="" loading="lazy" onerror="this.replaceWith(Object.assign(document.createElement('span'), {textContent: '{{ mb_substr($b->title, 0, 1) }}'}))">
                                @else
                                    <span>{{ mb_substr($b->title, 0, 1) }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="ad-contact">{{ $r->contact ?: 'No contact' }}</div>

                <footer class="ad-card__foot">
                    <button type="button" class="btn btn-sm btn-primary ad-edit" onclick="openEditAuthorModal({{ $a->id }})">Edit</button>
                    @include('admin.partials.author-menu', ['a' => $a, 'r' => $r])
                </footer>
            </article>
        @endforeach
    </div>

    {{-- list --}}
    <div class="ad-list-wrap">
        <table class="ad-list">
            <thead>
                <tr>
                    <th class="ad-list__check"><input type="checkbox" class="form-check-input" id="selectAllTableCheckbox" onchange="toggleSelectAllTable(this)" aria-label="Select all"></th>
                    <th>Author</th>
                    <th class="text-center">Books</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th class="text-end"><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($adRows as $r)
                    @php $a = $r->a; @endphp
                    <tr id="authorRow-{{ $a->id }}" class="{{ $a->is_active ? '' : 'is-off' }}" data-id="{{ $a->id }}">
                        <td class="ad-list__check"><input type="checkbox" class="form-check-input author-select-checkbox" value="{{ $a->id }}" onchange="updateBulkSelectionState()" aria-label="Select {{ $a->name }}"></td>
                        <td>
                            <div class="ad-who">
                                <button type="button" class="ad-avatar ad-avatar--sm" onclick="openAuthorDetailsModal({{ $a->id }})" style="--ad-bg: {{ $r->bg }}">
                                    @if ($r->avatar)<img src="{{ $r->avatar }}" alt="" loading="lazy" onerror="this.remove()">@endif
                                    <span>{{ $r->initials }}</span>
                                </button>
                                <div class="min-w-0">
                                    <div class="ad-name ad-name--row">
                                        <button type="button" onclick="openAuthorDetailsModal({{ $a->id }})">{{ $a->name }}</button>
                                        @if ($a->is_verified)<span class="ad-tick" title="Verified">✓</span>@endif
                                    </div>
                                    <div class="ad-sub">{{ $r->sub ?: '/' . $a->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center"><a class="ad-tag" href="{{ route('admin.books', ['author_id' => $a->id]) }}">{{ number_format($a->books_count) }}</a></td>
                        <td class="ad-contact ad-contact--row">{{ $r->contact ?: '—' }}</td>
                        <td>
                            <div class="ad-status">
                                <label class="ad-live form-switch" title="Show in the public directory">
                                    <input class="form-check-input ad-live-input" type="checkbox" role="switch" data-id="{{ $a->id }}" @checked($a->is_active) onchange="toggleAuthorStatusAjax({{ $a->id }}, this)">
                                </label>
                                @if ($r->pending)<span class="ad-tag ad-tag--warn">Pending</span>@endif
                                @if ($r->portal)<span class="ad-tag ad-tag--blue">Portal</span>@endif
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="ad-row-actions">
                                <button type="button" class="btn btn-sm btn-primary ad-edit" onclick="openEditAuthorModal({{ $a->id }})">Edit</button>
                                @include('admin.partials.author-menu', ['a' => $a, 'r' => $r])
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="ad-foot">
        <span>Showing {{ number_format($authors->firstItem()) }}–{{ number_format($authors->lastItem()) }} of {{ number_format($authors->total()) }}</span>
        @if ($authors->hasPages())
            <nav class="ad-pages">{{ $authors->onEachSide(1)->links() }}</nav>
        @endif
    </div>
@endif
