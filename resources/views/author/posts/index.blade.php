@extends('author.layout')

@section('title', 'আমার আইডিয়াপত্র — লেখক পোর্টাল')

@section('author_content')
<div class="container-fluid p-0">

    {{-- Top Header Section --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <i class="fas fa-feather-pointed text-warning"></i>
                <span>আমার আইডিয়াপত্র (আমার ব্লগ ও কলামসমূহ)</span>
            </h4>
            <p class="text-muted small mb-0">আপনার রচিত সাহিত্যকর্ম, কলাম, প্রবন্ধ ও আইডিয়াপত্র ব্লগ পোস্ট পরিচালনা করুন</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('blog.index') }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-arrow-up-right-from-square me-1"></i> ব্লগে দেখুন
            </a>
            <a href="{{ route('author.posts.create') }}" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3.5 shadow-sm">
                <i class="fas fa-pen-nib me-1.5"></i> নতুন আইডিয়াপত্র লিখুন
            </a>
        </div>
    </div>

    {{-- KPI Metric Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="author-card p-3 h-100 text-center">
                <div class="text-muted small mb-1">মোট লেখা</div>
                <h4 class="fw-bold text-dark mb-0">{{ $stats['total'] }}</h4>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="author-card p-3 h-100 text-center border-start border-3 border-success">
                <div class="text-muted small mb-1">প্রকাশিত</div>
                <h4 class="fw-bold text-success mb-0">{{ $stats['published'] }}</h4>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="author-card p-3 h-100 text-center border-start border-3 border-warning">
                <div class="text-muted small mb-1">পর্যালোচনায় (Pending)</div>
                <h4 class="fw-bold text-warning mb-0">{{ $stats['pending'] }}</h4>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="author-card p-3 h-100 text-center border-start border-3 border-secondary">
                <div class="text-muted small mb-1">খসড়া (Draft)</div>
                <h4 class="fw-bold text-secondary mb-0">{{ $stats['draft'] }}</h4>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="author-card p-3 h-100 text-center border-start border-3 border-danger">
                <div class="text-muted small mb-1">সংশোধন প্রয়োজন</div>
                <h4 class="fw-bold text-danger mb-0">{{ $stats['rejected'] }}</h4>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="author-card p-3 h-100 text-center border-start border-3 border-info">
                <div class="text-muted small mb-1">মোট পাঠ / ভিউ</div>
                <h4 class="fw-bold text-info mb-0 font-monospace">{{ number_format($stats['views']) }}</h4>
            </div>
        </div>
    </div>

    {{-- Filter Nav Pills --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <ul class="nav nav-pills gap-1 small">
            <li class="nav-item">
                <a href="{{ route('author.posts.index') }}" class="nav-link rounded-pill py-1 px-3 {{ $filterStatus === 'all' ? 'active bg-dark text-white' : 'text-dark bg-light' }}">
                    সবগুলো <span class="badge {{ $filterStatus === 'all' ? 'bg-light text-dark' : 'bg-secondary' }} rounded-pill ms-1">{{ $stats['total'] }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('author.posts.index', ['status' => 'published']) }}" class="nav-link rounded-pill py-1 px-3 {{ $filterStatus === 'published' ? 'active bg-success text-white' : 'text-dark bg-light' }}">
                    প্রকাশিত <span class="badge {{ $filterStatus === 'published' ? 'bg-light text-success' : 'bg-secondary' }} rounded-pill ms-1">{{ $stats['published'] }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('author.posts.index', ['status' => 'pending']) }}" class="nav-link rounded-pill py-1 px-3 {{ $filterStatus === 'pending' ? 'active bg-warning text-dark' : 'text-dark bg-light' }}">
                    পর্যালোচনায় আছে <span class="badge {{ $filterStatus === 'pending' ? 'bg-light text-dark' : 'bg-secondary' }} rounded-pill ms-1">{{ $stats['pending'] }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('author.posts.index', ['status' => 'draft']) }}" class="nav-link rounded-pill py-1 px-3 {{ $filterStatus === 'draft' ? 'active bg-secondary text-white' : 'text-dark bg-light' }}">
                    খসড়া (Draft) <span class="badge {{ $filterStatus === 'draft' ? 'bg-light text-dark' : 'bg-secondary' }} rounded-pill ms-1">{{ $stats['draft'] }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('author.posts.index', ['status' => 'rejected']) }}" class="nav-link rounded-pill py-1 px-3 {{ $filterStatus === 'rejected' ? 'active bg-danger text-white' : 'text-dark bg-light' }}">
                    সংশোধন আবশ্যক <span class="badge {{ $filterStatus === 'rejected' ? 'bg-light text-danger' : 'bg-secondary' }} rounded-pill ms-1">{{ $stats['rejected'] }}</span>
                </a>
            </li>
        </ul>
    </div>

    {{-- Main Articles Table Card --}}
    <div class="author-card p-3 p-md-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small fw-bold text-secondary">
                    <tr>
                        <th style="width: 65px;">ছবি</th>
                        <th>শিরোনাম ও সারসংক্ষেপ</th>
                        <th style="width: 140px;">ক্যাটাগরি</th>
                        <th style="width: 120px;">তারিখ</th>
                        <th style="width: 100px;">ভিউ</th>
                        <th style="width: 130px;">স্ট্যাটাস</th>
                        <th style="width: 120px;" class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($posts as $post)
                        <tr>
                            <td>
                                @php
                                    $imgUrl = $post->cover_url ?: ($post->featured_image ? (str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . ltrim($post->featured_image, '/'))) : 'https://placehold.co/100x70?text=Post');
                                @endphp
                                <img src="{{ $imgUrl }}" alt="{{ $post->title }}" class="rounded shadow-xs object-fit-cover" style="width: 52px; height: 38px;">
                            </td>
                            <td>
                                <div class="fw-bold text-dark mb-0.5" style="font-size: 0.92rem;">{{ $post->title }}</div>
                                @if($post->subtitle)
                                    <div class="text-secondary small text-truncate" style="max-width: 380px;">{{ $post->subtitle }}</div>
                                @elseif($post->excerpt)
                                    <div class="text-muted small text-truncate" style="max-width: 380px;">{{ $post->excerpt }}</div>
                                @endif

                                @if($post->status === 'rejected' && $post->rejection_reason)
                                    <div class="alert alert-danger p-1.5 mt-1.5 mb-0 small rounded-2" style="font-size: 11px;">
                                        <i class="fas fa-triangle-exclamation me-1"></i> <strong>সম্পাদকের মন্তব্য:</strong> {{ $post->rejection_reason }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $post->category?->name ?? 'সাধারণ' }}</span>
                            </td>
                            <td class="text-muted">
                                {{ $post->published_at ? $post->published_at->format('d M, Y') : $post->created_at->format('d M, Y') }}
                            </td>
                            <td>
                                <span class="text-muted font-monospace"><i class="fas fa-eye me-1 text-primary"></i>{{ number_format($post->view_count ?? 0) }}</span>
                            </td>
                            <td>
                                @if($post->status === 'published')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                        <i class="fas fa-check-circle me-1"></i> প্রকাশিত
                                    </span>
                                @elseif($post->status === 'pending')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">
                                        <i class="fas fa-hourglass-half me-1"></i> পর্যালোচনায়
                                    </span>
                                @elseif($post->status === 'rejected')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">
                                        <i class="fas fa-circle-exclamation me-1"></i> সংশোধন প্রয়োজন
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1">
                                        <i class="fas fa-file-lines me-1"></i> খসড়া
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm shadow-xs">
                                    @if($post->status === 'published')
                                        <a href="{{ route('blog.show', $post->slug ?: $post->id) }}" target="_blank" class="btn btn-outline-primary" title="ব্লগে পড়ুন">
                                            <i class="fas fa-arrow-up-right-from-square"></i>
                                        </a>
                                    @elseif($post->status === 'pending')
                                        <a href="{{ route('blog.show', $post->slug ?: $post->id) }}" target="_blank" class="btn btn-outline-secondary" title="প্রিভিউ দেখুন">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('author.posts.edit', $post->id) }}" class="btn btn-outline-warning text-dark fw-bold" title="এডিট করুন">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <form action="{{ route('author.posts.destroy', $post->id) }}" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি নিশ্চিতভাবে এই খসড়া লেখাটি মুছে ফেলতে চান?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="মুছে ফেলুন">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-feather-pointed fs-1 opacity-25 d-block mb-2 text-warning"></i>
                                <h6 class="fw-bold text-dark">কোনো আইডিয়াপত্র পোস্ট পাওয়া যায়নি</h6>
                                <p class="small text-muted mb-3">আপনি এখনও এই বিভাগে কোনো লেখা যোগ করেননি।</p>
                                <a href="{{ route('author.posts.create') }}" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-4">
                                    <i class="fas fa-pen-nib me-1.5"></i> প্রথম আইডিয়াপত্র লিখুন
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($posts->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $posts->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
