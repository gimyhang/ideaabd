@extends('layouts.admin')

@section('title', 'লেখক পরিচালনা')
@section('heading', 'লেখক পরিচালনা ও তালিকা')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">লেখক</li>
@endsection

@section('actions')
    <a href="{{ route('admin.content.create', 'authors') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> নতুন লেখক যোগ করুন
    </a>
    <a href="{{ route('authors.index') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-up-right-from-square me-1"></i> সাইটে দেখুন
    </a>
@endsection

@section('content')

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">সর্বমোট লেখক</span>
                    <h3 class="fw-bold mb-0 text-primary">@bn($stats['total'] ?? 0)</h3>
                </div>
                <div class="rounded-circle bg-primary-subtle text-primary p-3"><i class="fas fa-pen-fancy fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">সক্রিয় লেখক</span>
                    <h3 class="fw-bold mb-0 text-success">@bn($stats['active'] ?? 0)</h3>
                </div>
                <div class="rounded-circle bg-success-subtle text-success p-3"><i class="fas fa-user-check fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">যাচাইকৃত (Verified) লেখক</span>
                    <h3 class="fw-bold mb-0 text-info">@bn($stats['verified'] ?? 0)</h3>
                </div>
                <div class="rounded-circle bg-info-subtle text-info p-3"><i class="fas fa-certificate fs-4"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.authors') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-7">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" name="search" class="form-control border-start-0" 
                           placeholder="লেখকের নাম, ফোন, ইমেইল বা slug দিয়ে খুঁজুন..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="is_active" class="form-select" onchange="this.form.submit()">
                    <option value="" @selected(request('is_active') === null || request('is_active') === '')>সকল অবস্থা</option>
                    <option value="1" @selected(request('is_active') === '1')>সক্রিয়</option>
                    <option value="0" @selected(request('is_active') === '0')>নিষ্ক্রিয়</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> ফিল্টার</button>
                @if(request()->hasAny(['search', 'is_active']))
                    <a href="{{ route('admin.authors') }}" class="btn btn-light border" title="রিসেট"><i class="fas fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Authors Table --}}
<div class="adm-card shadow-sm rounded-4 overflow-hidden">
    @if ($authors->isEmpty())
        <div class="empty-state py-5 text-center">
            <i class="fas fa-pen-fancy fs-1 text-muted opacity-50 mb-3"></i>
            <h5 class="fw-bold text-muted">কোনো লেখক পাওয়া যায়নি</h5>
            <p class="text-muted small">নতুন লেখক যোগ করুন অথবা অন্য সার্চ ফিল্টার ব্যবহার করুন।</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>ছবি ও নাম</th>
                        <th>যোগাযোগ</th>
                        <th>মোট বই</th>
                        <th>যাচাইকৃত</th>
                        <th>অবস্থা</th>
                        <th>তারিখ</th>
                        <th class="text-end pe-3" style="min-width: 130px;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($authors as $n => $author)
                        <tr>
                            <td class="ps-3 text-muted small">@bn($authors->firstItem() + $n)</td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    @if(!empty($author->avatar))
                                        <img src="{{ asset('storage/' . $author->avatar) }}" alt="{{ $author->name }}" 
                                             class="rounded-circle object-fit-cover shadow-xs" style="width: 42px; height: 42px;">
                                    @else
                                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" 
                                             style="width: 42px; height: 42px; font-size: 1.1rem;">
                                            {{ mb_substr($author->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark">{{ $author->name }}</div>
                                        <div class="text-muted small" style="font-size: 0.78rem;">{{ $author->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($author->phone)
                                    <div><i class="fas fa-phone text-muted me-1 small"></i>{{ $author->phone }}</div>
                                @endif
                                @if($author->email)
                                    <div class="text-muted small"><i class="fas fa-envelope text-muted me-1 small"></i>{{ $author->email }}</div>
                                @endif
                                @if(!$author->phone && !$author->email)
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2.5 py-1">
                                    <i class="fas fa-book me-1 text-primary"></i>@bn($author->books_count ?? 0) টি
                                </span>
                            </td>
                            <td>
                                @if($author->is_verified)
                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill">
                                        <i class="fas fa-check-circle me-1"></i> ভেরিফাইড
                                    </span>
                                @else
                                    <span class="text-muted small">সাধারণ</span>
                                @endif
                            </td>
                            <td>
                                @if($author->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                        সক্রিয়
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill">
                                        নিষ্ক্রিয়
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted small">@bnDate($author->created_at)</td>
                            <td class="text-end pe-3">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    {{-- Edit Author --}}
                                    <a href="{{ route('admin.content.edit', ['type' => 'authors', 'id' => $author->id]) }}" 
                                       class="btn btn-sm btn-outline-primary px-2.5 py-1" title="লেখক সম্পাদনা করুন">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>

                                    {{-- Delete Author --}}
                                    <form action="{{ route('admin.content.destroy', ['type' => 'authors', 'id' => $author->id]) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('আপনি কি নিশ্চিত যে এই লেখককে মুছে ফেলতে চান?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger px-2.5 py-1" title="মুছে ফেলুন">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>

                                    {{-- View Author on Website --}}
                                    <a href="{{ route('authors.show', $author->slug) }}" target="_blank" rel="noopener" 
                                       class="btn btn-sm btn-light border px-2 py-1" title="সাইটে লেখকের প্রোফাইল দেখুন">
                                        <i class="fas fa-arrow-up-right-from-square text-muted"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($authors->hasPages())
            <div class="adm-card__foot d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 bg-white border-top">
                <span class="text-muted small">
                    মোট @bn($authors->total())টির মধ্যে @bn($authors->firstItem())–@bn($authors->lastItem()) দেখানো হচ্ছে
                </span>
                {{ $authors->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
