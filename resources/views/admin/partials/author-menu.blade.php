{{-- The "⋯" menu of one author (card and list row). Needs $a (Author) and $r (row helper from authors_grid_partial). --}}
<div class="dropdown">
    <button type="button" class="btn btn-sm btn-light border ad-more" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" aria-label="More actions for {{ $a->name }}">⋯</button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 py-1">
        <li><button type="button" class="dropdown-item" onclick="openAuthorDetailsModal({{ $a->id }})">Profile &amp; stats</button></li>
        <li><a class="dropdown-item" href="{{ route('admin.books', ['author_id' => $a->id]) }}">Books ({{ number_format($a->books_count) }})</a></li>
        <li><a class="dropdown-item" href="{{ $r->public }}" target="_blank" rel="noopener">Public page ↗</a></li>
        <li><button type="button" class="dropdown-item" onclick="copyToClipboard('{{ $r->public }}', 'Link copied')">Copy link</button></li>
        <li><hr class="dropdown-divider my-1"></li>
        <li><button type="button" class="dropdown-item ad-verify-item" data-id="{{ $a->id }}" onclick="toggleAuthorVerifiedAjax({{ $a->id }}, this)">{{ $a->is_verified ? 'Remove verified' : 'Mark verified' }}</button></li>
        @if ($a->user_id)
            <li><button type="button" class="dropdown-item" onclick="openAuthorPasswordResetModal({{ $a->id }}, @js($a->name), @js($a->user->email ?? $a->user->phone ?? ''))">Reset portal password</button></li>
        @endif
        <li><hr class="dropdown-divider my-1"></li>
        <li><button type="button" class="dropdown-item text-danger" onclick="handleDeleteAuthor({{ $a->id }}, @js($a->name), {{ (int) $a->books_count }})">Delete</button></li>
    </ul>
</div>
