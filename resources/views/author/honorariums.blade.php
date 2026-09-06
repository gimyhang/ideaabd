@extends('author.layout')

@section('title', 'Honorariums — Author Portal')
@section('heading', 'Honorariums')

@section('content')
<div class="d-flex flex-column gap-3.5">

    {{-- Top Summary Cards --}}
    <div class="row g-3">
        <div class="col-6 col-lg-3">
            <div class="author-card p-3 h-100 border-start border-4 border-danger">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Total Honorariums</span>
                    <span class="p-2 bg-danger bg-opacity-10 text-danger rounded-3"><i class="fas fa-heart"></i></span>
                </div>
                <h3 class="fw-bold mb-1 text-danger font-monospace">৳{{ number_format($totalEarned, 2) }}</h3>
                <div class="small text-muted">From readers</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="author-card p-3 h-100 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">This Month</span>
                    <span class="p-2 bg-warning bg-opacity-10 text-warning-emphasis rounded-3"><i class="fas fa-calendar-check"></i></span>
                </div>
                <h3 class="fw-bold mb-1 text-warning-emphasis font-monospace">৳{{ number_format($thisMonthSum, 2) }}</h3>
                <div class="small text-muted">{{ now()->format('M Y') }}</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="author-card p-3 h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Total Donors</span>
                    <span class="p-2 bg-success bg-opacity-10 text-success rounded-3"><i class="fas fa-users"></i></span>
                </div>
                <h3 class="fw-bold mb-1 text-success">{{ $totalCount }}</h3>
                <div class="small text-muted">Supporters</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="author-card p-3 h-100 border-start border-4 border-primary bg-primary bg-opacity-10">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-dark small fw-bold">Wallet Balance</span>
                    <span class="p-2 bg-primary text-white rounded-3"><i class="fas fa-wallet"></i></span>
                </div>
                <h3 class="fw-bold mb-1 text-dark font-monospace">৳{{ number_format($author?->wallet_balance ?? 0, 2) }}</h3>
                <div class="d-flex align-items-center justify-content-between mt-1">
                    <span class="small text-muted" style="font-size: 11px;">Royalties + Honorariums</span>
                    <a href="{{ route('author.payouts.index') }}" class="btn btn-xs btn-primary rounded-pill px-2.5 py-0.5 text-nowrap" style="font-size: 11px;">
                        Payout →
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Policy Notice --}}
    <div class="alert alert-light border border-warning-subtle shadow-xs rounded-4 p-3 d-flex align-items-center gap-3 mb-0 bg-warning-subtle bg-opacity-25">
        <i class="fas fa-info-circle fs-5 text-warning-emphasis flex-shrink-0"></i>
        <div class="small text-dark">
            <strong>Policy:</strong> 70% of reader honorarium is directly credited to your wallet; 30% is retained for platform maintenance.
        </div>
    </div>

    {{-- Filters & Search Bar --}}
    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
        <form method="GET" action="{{ route('author.honorariums') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search reader, phone, TrxID..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <select name="post_id" class="form-select form-select-sm">
                    <option value="">All Posts</option>
                    @foreach($authorPosts as $p)
                        <option value="{{ $p->id }}" {{ request('post_id') == $p->id ? 'selected' : '' }}>
                            {{ \Illuminate\Support\Str::limit($p->title, 35) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill w-100 fw-semibold">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'post_id', 'status']))
                    <a href="{{ route('author.honorariums') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3" title="Reset">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Honorariums Ledger Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light bg-opacity-50">
            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fas fa-heart text-danger"></i>
                <span>Honorarium Records</span>
                <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill">{{ $honorariums->total() }}</span>
            </h6>

            <a href="{{ route('author.posts.create') }}" class="btn btn-warning btn-sm rounded-pill px-3 py-1 fw-bold text-dark text-decoration-none shadow-xs">
                <i class="fas fa-feather-pointed me-1"></i> Create Post
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.90rem;">
                <thead class="table-light text-secondary text-uppercase small" style="font-size: 11px; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-3 py-3">Date & Time</th>
                        <th class="py-3">Post</th>
                        <th class="py-3">Reader & Note</th>
                        <th class="py-3">Method & TrxID</th>
                        <th class="py-3 text-end">Total</th>
                        <th class="py-3 text-end">Author Share (70%)</th>
                        <th class="pe-3 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($honorariums as $h)
                        <tr>
                            {{-- Date --}}
                            <td class="ps-3 text-nowrap">
                                <div class="fw-semibold text-dark">{{ $h->created_at->format('d M, Y') }}</div>
                                <small class="text-muted">{{ $h->created_at->format('h:i A') }}</small>
                            </td>

                            {{-- Post Title --}}
                            <td style="max-width: 240px;">
                                @if($h->post)
                                    <a href="{{ route('blog.show', $h->post->slug ?: $h->post->id) }}" target="_blank" class="text-decoration-none text-dark fw-bold hover-primary line-clamp-2" title="{{ $h->post->title }}">
                                        {{ $h->post->title }}
                                    </a>
                                    <small class="text-muted d-block" style="font-size: 11px;">
                                        <i class="fas fa-eye me-1"></i>{{ $h->post->view_count ?? 0 }} views
                                    </small>
                                @else
                                    <span class="text-muted fst-italic">Post</span>
                                @endif
                            </td>

                            {{-- Donor & Message --}}
                            <td style="max-width: 280px;">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 28px; height: 28px; font-size: 11px;">
                                        <i class="fas fa-user-heart"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $h->display_name }}</div>
                                        @if($h->message)
                                            <div class="small text-secondary bg-light p-2 rounded-2 mt-1 border-start border-2 border-danger fst-italic" style="font-size: 12px; line-height: 1.5;">
                                                "{{ $h->message }}"
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Method & TrxID --}}
                            <td class="text-nowrap">
                                <span class="badge {{ $h->method_badge_class }} rounded-pill px-2.5 py-1 text-uppercase" style="font-size: 10px;">
                                    {{ $h->payment_method }}
                                </span>
                                @if($h->trx_id)
                                    <div class="font-monospace text-muted mt-1" style="font-size: 11px;">
                                        Trx: {{ $h->trx_id }}
                                    </div>
                                @endif
                            </td>

                            {{-- Total Amount --}}
                            <td class="text-end text-nowrap">
                                <span class="text-muted font-monospace" style="font-size: 12px;">৳{{ number_format($h->amount, 2) }}</span>
                            </td>

                            {{-- Author Amount 70% --}}
                            <td class="text-end text-nowrap">
                                <span class="fw-bold text-success fs-6 font-monospace">৳{{ number_format($h->author_amount, 2) }}</span>
                                <small class="text-muted d-block" style="font-size: 10px;">Credited</small>
                            </td>

                            {{-- Status --}}
                            <td class="pe-3 text-center text-nowrap">
                                @if($h->payment_status === 'completed')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                        <i class="fas fa-check-circle me-1"></i> Completed
                                    </span>
                                @elseif($h->payment_status === 'pending')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">
                                        <i class="fas fa-clock me-1"></i> Pending
                                    </span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-2.5 py-1">
                                        {{ ucfirst($h->payment_status) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-inline-flex align-items-center justify-content-center p-3" style="width: 60px; height: 60px;">
                                        <i class="fas fa-hand-holding-heart fs-3 text-danger"></i>
                                    </div>
                                </div>
                                <h6 class="fw-bold text-dark">No honorarium records found</h6>
                                <a href="{{ route('author.posts.create') }}" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold mt-2">
                                    <i class="fas fa-feather-pointed me-1"></i> Create Post
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($honorariums->hasPages())
            <div class="p-3 border-top bg-light">
                {{ $honorariums->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
