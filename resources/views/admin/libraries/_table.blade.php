{{-- Table + pagination for the library registry; rendered on page load and by the AJAX refresh. --}}
<div class="card border-0 shadow-2xs rounded-4 bg-white overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-lib-custom">
            <thead>
                <tr>
                    <th class="ps-3" style="width: 40px;"><input type="checkbox" class="form-check-input" id="libCheckAll" aria-label="Select all"></th>
                    <th>Library</th>
                    <th>Representative</th>
                    <th>Location</th>
                    <th>Readers / Books</th>
                    <th>Allocation</th>
                    <th>Receipt</th>
                    <th>Approval</th>
                    <th class="pe-3 text-end" style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($libraries as $lib)
                    @php
                        $fd = $lib->form_data ?? [];
                        $isApproved = in_array($lib->status, ['confirmed', 'selected', 'approved'], true);
                        $allocated = intval($fd['books_allocated'] ?? 0);
                        $dispatchedDate = $fd['dispatched_date'] ?? null;
                        $receivedCount = intval($fd['received_books_count'] ?? 0);
                        $receivedDate = $fd['received_date'] ?? null;
                        $ackStatus = $lib->acknowledgment_status;
                        $libName = $lib->institution_or_org ?: ($fd['library_name'] ?? 'Library');
                        $libType = $fd['library_type'] ?? ($lib->designation_or_class ?: 'Public Library');
                        $bookItems = $fd['allocated_books_list'] ?? [];
                    @endphp
                    <tr id="row-lib-{{ $lib->id }}">
                        <td class="ps-3"><input type="checkbox" class="form-check-input lib-row-check" value="{{ $lib->id }}" aria-label="Select {{ $lib->institution_or_org }}"></td>

                        {{-- Library Name & Type --}}
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-3 bg-success-subtle text-success p-2 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                    <i class="fa-solid fa-landmark"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 14px;">{{ $libName }}</div>
                                    <div class="d-flex align-items-center gap-1.5 flex-wrap mt-0.5">
                                        <span class="badge bg-light text-secondary border px-1.5 py-0.5" style="font-size: 10.5px;">{{ $libType }}</span>
                                        @if(!empty($fd['established_year']))
                                            <span class="badge bg-light text-muted border px-1.5 py-0.5" style="font-size: 10px;">Est: {{ $fd['established_year'] }}</span>
                                        @endif
                                        <span class="text-muted small font-monospace" style="font-size: 11px;">#{{ $lib->registration_number }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Representative & Contact --}}
                        <td>
                            <div class="fw-semibold text-dark">{{ $lib->name }}</div>
                            <div class="small">
                                <a href="tel:{{ $lib->phone }}" class="text-decoration-none text-muted hover-success fw-medium">
                                    <i class="fa-solid fa-phone me-1 text-success small"></i>{{ $lib->phone }}
                                </a>
                            </div>
                            @if($lib->email)
                                <div class="text-muted small" style="font-size: 11px;">{{ $lib->email }}</div>
                            @endif
                        </td>

                        {{-- Location & Address --}}
                        <td>
                            <div class="fw-semibold text-dark small">
                                {{ $lib->district ?? '—' }}{{ $lib->thana ? ', ' . $lib->thana : '' }}
                            </div>
                            <div class="text-muted small text-truncate" style="max-width: 180px; font-size: 11.5px;" title="{{ $lib->address }}">
                                {{ $fd['division'] ? $fd['division'] . ' • ' : '' }}{{ $lib->address }}
                            </div>
                        </td>

                        {{-- Readers / Current Books --}}
                        <td>
                            <div class="small">
                                <span class="text-muted">Readers:</span> <span class="fw-semibold text-dark">{{ $fd['reader_count'] ?? '—' }}</span>
                            </div>
                            <div class="small text-muted" style="font-size: 11.5px;">
                                Current books: <span class="fw-medium text-dark">{{ $fd['current_book_count'] ?? '—' }}</span>
                            </div>
                            @php
                                $hasPrev = ($fd['has_previous_books'] ?? '') === 'হ্যাঁ' || ($fd['has_previous_books'] ?? '') === 'yes' || (!empty($lib->has_previous_books) && $lib->has_previous_books !== 'না');
                            @endphp
                            <div class="mt-1">
                                @if($hasPrev)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5" style="font-size: 10px;" title="Received books in a previous year">
                                        Previous books: <strong>{{ $fd['previous_books_count'] ?? ($lib->previous_books_count ?? '—') }}</strong> ({{ $fd['previous_books_year'] ?? ($lib->previous_books_year ?? '') }})
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border px-1.5 py-0.5" style="font-size: 10px;">
                                        No previous books
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Allocation & Dispatch Info --}}
                        <td>
                            @if($allocated > 0)
                                <div class="fw-bold text-success fs-6">
                                    {{ $allocated }} <span class="small fw-normal text-muted">Books</span>
                                    @if(count($bookItems) > 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill ms-1" style="font-size: 10px;">{{ count($bookItems) }} titles</span>
                                    @endif
                                </div>
                                <div class="text-muted small" style="font-size: 11px;">
                                    <i class="fa-regular fa-calendar-check me-1"></i>{{ $dispatchedDate ? date('d M, Y', strtotime($dispatchedDate)) : 'No date' }}
                                </div>
                                @if(!empty($fd['dispatch_tracking_no']))
                                    <span class="badge bg-light text-muted border px-1.5 py-0.5 mt-0.5" style="font-size: 10px;">Trk: {{ $fd['dispatch_tracking_no'] }}</span>
                                @endif
                            @else
                                <span class="badge bg-light text-muted border">Not Allocated</span>
                            @endif
                        </td>

                        {{-- Acknowledgment Info --}}
                        <td>
                            @if($ackStatus === 'acknowledged')
                                <span class="badge-ack-received d-inline-flex align-items-center gap-1 mb-1">
                                    <i class="fa-solid fa-circle-check text-success"></i> Received ({{ $receivedCount ?: $allocated }} Books)
                                </span>
                                @if($receivedDate)
                                    <div class="text-muted small" style="font-size: 11px;">
                                        {{ date('d M, Y', strtotime($receivedDate)) }}
                                    </div>
                                @endif
                            @elseif($allocated > 0)
                                <span class="badge-ack-dispatched d-inline-flex align-items-center gap-1">
                                    <i class="fa-solid fa-truck-fast"></i> Dispatched (Pending)
                                </span>
                            @else
                                <span class="badge-ack-pending">Pending Allocation</span>
                            @endif
                        </td>

                        {{-- Approval Status --}}
                        <td>
                            <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 fw-semibold {{ $isApproved ? 'btn-success-subtle text-success border-success-subtle' : 'btn-warning-subtle text-warning border-warning-subtle' }}"
                                    onclick="toggleApproval('{{ $lib->id }}', this)" title="Click to toggle status">
                                <i class="fa-solid {{ $isApproved ? 'fa-circle-check' : 'fa-hourglass-half' }} me-1"></i>
                                <span>{{ $isApproved ? 'Approved' : 'Pending' }}</span>
                            </button>
                        </td>

                        {{-- Action Buttons --}}
                        <td class="pe-3 text-end">
                            <div class="d-inline-flex align-items-center gap-1">
                                {{-- 1. Dispatch Modal Button (Itemized Books Entry) --}}
                                <button type="button" class="btn btn-sm btn-outline-success btn-action-icon" title="Allocate & Itemize Books"
                                        onclick='openDispatchModal("{{ $lib->id }}", "{{ addslashes($libName) }}", "{{ $allocated }}", "{{ $dispatchedDate }}", "{{ addslashes($fd['dispatch_tracking_no'] ?? '') }}", "{{ addslashes($fd['dispatch_notes'] ?? '') }}", {{ json_encode($bookItems) }})'>
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                </button>

                                {{-- 2. Acknowledgment Button --}}
                                <button type="button" class="btn btn-sm btn-outline-primary btn-action-icon" title="Receipt Acknowledgment & Verify"
                                        onclick='openAckModal("{{ $lib->id }}", "{{ addslashes($libName) }}", "{{ $allocated }}", "{{ $receivedCount ?: $allocated }}", "{{ $receivedDate }}", "{{ addslashes($fd['acknowledgment_notes'] ?? '') }}")'>
                                    <i class="fa-solid fa-signature"></i>
                                </button>

                                {{-- 3. Print / Preview Application Form (PDF View) --}}
                                <a href="{{ route('admin.libraries.print', $lib->id) }}" target="_blank" class="btn btn-sm btn-outline-success btn-action-icon" title="Preview Official Form / Print">
                                    <i class="fa-solid fa-file-invoice"></i>
                                </a>

                                {{-- 4. Direct Download PDF --}}
                                <a href="{{ route('admin.libraries.pdf', $lib->id) }}" class="btn btn-sm btn-outline-danger btn-action-icon" title="Download Official PDF">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>

                                {{-- 5. Details Modal --}}
                                <button type="button" class="btn btn-sm btn-light border btn-action-icon" title="View Full Profile & Book List"
                                        onclick="openDetailsModal({{ json_encode($lib) }})">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-book-open-reader fs-1 text-secondary mb-3 d-block opacity-50"></i>
                            <div class="fw-semibold fs-6">No libraries match your filters.</div>
                            <small class="text-muted">Try clearing a filter or changing the search.</small>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($libraries->hasPages())
        <div class="card-footer bg-white border-top p-3 d-flex align-items-center justify-content-between">
            <div class="small text-muted">
                Showing {{ $libraries->firstItem() }} to {{ $libraries->lastItem() }} of {{ $libraries->total() }} libraries
            </div>
            <div>{{ $libraries->withPath(route('admin.libraries.index'))->links('pagination::bootstrap-5') }}</div>
        </div>
    @endif
</div>
