@extends('layouts.admin')

@section('title', 'ব্লগ পোস্ট পরিচালনা')
@section('heading', 'ব্লগ পোস্ট ও কনটেন্ট মডারেশন')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">ব্লগ পোস্ট</li>
@endsection

@section('actions')
    <a href="{{ route('admin.content.create', 'blog') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> নতুন ব্লগ পোস্ট যোগ করুন
    </a>
    <a href="{{ route('blog.index') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-up-right-from-square me-1"></i> সাইটে দেখুন
    </a>
@endsection

@section('content')

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">মোট ব্লগ পোস্ট</span>
                    <h3 class="fw-bold mb-0 text-primary">@bn($stats['total'] ?? 0)</h3>
                </div>
                <div class="rounded-circle bg-primary-subtle text-primary p-3"><i class="fas fa-blog fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">প্রকাশিত (অনুমোদিত)</span>
                    <h3 class="fw-bold mb-0 text-success">@bn($stats['published'] ?? 0)</h3>
                </div>
                <div class="rounded-circle bg-success-subtle text-success p-3"><i class="fas fa-check-double fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">অপেক্ষমাণ (Pending)</span>
                    <h3 class="fw-bold mb-0 text-warning">@bn($stats['pending'] ?? 0)</h3>
                </div>
                <div class="rounded-circle bg-warning-subtle text-warning p-3"><i class="fas fa-clock fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">বাতিল (প্রত্যাখ্যাত)</span>
                    <h3 class="fw-bold mb-0 text-danger">@bn($stats['rejected'] ?? 0)</h3>
                </div>
                <div class="rounded-circle bg-danger-subtle text-danger p-3"><i class="fas fa-circle-xmark fs-4"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.blog') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" name="search" class="form-control border-start-0" 
                           placeholder="পোস্টের শিরোনাম, বিষয় বা slug দিয়ে খুঁজুন..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="all" @selected(request('status') === 'all' || !request('status'))>সকল পোস্ট</option>
                    <option value="published" @selected(request('status') === 'published')>প্রকাশিত (Published)</option>
                    <option value="pending" @selected(request('status') === 'pending')>অপেক্ষমাণ (Pending Review)</option>
                    <option value="draft" @selected(request('status') === 'draft')>খসড়া (Draft)</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>বাতিল (Rejected)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> ফিল্টার</button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.blog') }}" class="btn btn-light border" title="রিসেট"><i class="fas fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Data Table --}}
<div class="adm-card shadow-sm rounded-4 overflow-hidden">
    @if ($posts->isEmpty())
        <div class="empty-state py-5 text-center">
            <i class="fas fa-blog fs-1 text-muted opacity-50 mb-3"></i>
            <h5 class="fw-bold text-muted">কোনো ব্লগ পোস্ট পাওয়া যায়নি</h5>
            <p class="text-muted small">নতুন পোস্ট তৈরি করুন অথবা অন্য ফিল্টার ব্যবহার করুন।</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>পোস্টের শিরোনাম</th>
                        <th>লেখক / জমাদানকারী</th>
                        <th>ক্যাটাগরি</th>
                        <th>অবস্থা (Status)</th>
                        <th>ভিউ</th>
                        <th>তারিখ</th>
                        <th class="text-end pe-3" style="min-width: 170px;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($posts as $n => $post)
                        @php
                            $isPublished = ($post->status === 'published' || $post->mod_status === 'approved');
                            $isPending = ($post->status === 'pending' || $post->mod_status === 'pending');
                            $isRejected = ($post->status === 'rejected' || $post->mod_status === 'rejected');
                            $isDraft = ($post->status === 'draft');
                        @endphp
                        <tr>
                            <td class="ps-3 text-muted small">@bn($posts->firstItem() + $n)</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $post->title }}</div>
                                <div class="text-muted small">{{ $post->slug }}</div>
                                @if($isRejected && $post->rejection_reason)
                                    <div class="small text-danger mt-1">
                                        <i class="fas fa-info-circle me-1"></i>বাতিলের কারণ: {{ $post->rejection_reason }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $post->author?->name ?? $post->submitter?->name ?? '—' }}</div>
                                @if($post->author?->phone)
                                    <div class="text-muted small"><i class="fas fa-phone me-1"></i>{{ $post->author->phone }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $post->category?->name ?? 'সাধারণ' }}</span>
                            </td>
                            <td>
                                @if($isPublished)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-circle-check me-1"></i> প্রকাশিত
                                    </span>
                                @elseif($isPending)
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-clock me-1"></i> অপেক্ষমাণ
                                    </span>
                                @elseif($isRejected)
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-circle-xmark me-1"></i> বাতিল
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-file-lines me-1"></i> খসড়া
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted small">@bn($post->view_count ?? 0)</td>
                            <td class="text-muted small">@bnDate($post->created_at)</td>
                            <td class="text-end pe-3">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    {{-- Approve action --}}
                                    @if(!$isPublished)
                                        <form action="{{ route('admin.content.approve', ['type' => 'blog', 'id' => $post->id]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-success px-2 py-1" title="অনুমোদন করুন (Approve)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Reject modal trigger --}}
                                    @if(!$isRejected)
                                        <button type="button" class="btn btn-sm btn-outline-warning px-2 py-1" 
                                                data-bs-toggle="modal" data-bs-target="#rejectModal{{ $post->id }}" title="বাতিল করুন (Reject with/without comment)">
                                            <i class="fas fa-ban"></i>
                                        </button>

                                        <!-- Reject Modal -->
                                        <div class="modal fade text-start" id="rejectModal{{ $post->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content rounded-4 border-0 shadow">
                                                    <div class="modal-header border-bottom py-3 bg-light">
                                                        <h5 class="modal-title fw-bold text-danger"><i class="fas fa-ban me-2"></i>পোস্ট বাতিলকরণ</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('admin.content.reject', ['type' => 'blog', 'id' => $post->id]) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="modal-body p-4">
                                                            <p class="mb-2 fw-semibold">আপনি কি "<strong>{{ $post->title }}</strong>" পোস্টটি বাতিল করতে চান?</p>
                                                            <div class="mb-3">
                                                                <label class="form-label small text-muted">বাতিলকরণের কারণ / মন্তব্য (ঐচ্ছিক):</label>
                                                                <textarea name="reason" rows="3" class="form-control rounded-3" placeholder="কেন বাতিল করা হলো লিখুন (কমেন্ট ছাড়াও সাবমিট করা যাবে)..."></textarea>
                                                            </div>
                                                            <div class="small text-muted">মন্তব্য দেওয়া হলে তা লেখক তার ড্যাশবোর্ডে দেখতে পাবেন।</div>
                                                        </div>
                                                        <div class="modal-footer bg-light py-2">
                                                            <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল নয়</button>
                                                            <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold">বাতিল নিশ্চিত করুন</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Edit action --}}
                                    <a href="{{ route('admin.content.edit', ['type' => 'blog', 'id' => $post->id]) }}" 
                                       class="btn btn-sm btn-outline-primary px-2 py-1" title="সম্পাদনা করুন">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>

                                    {{-- Delete action --}}
                                    <form action="{{ route('admin.content.destroy', ['type' => 'blog', 'id' => $post->id]) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ব্লগ পোস্টটি মুছে ফেলতে চান?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1" title="মুছে ফেলুন">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>

                                    @if($isPublished)
                                        <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener" 
                                           class="btn btn-sm btn-light border px-2 py-1" title="সাইটে দেখুন">
                                            <i class="fas fa-eye text-muted"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($posts->hasPages())
            <div class="adm-card__foot d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 bg-white border-top">
                <span class="text-muted small">
                    মোট @bn($posts->total())টির মধ্যে @bn($posts->firstItem())–@bn($posts->lastItem()) দেখানো হচ্ছে
                </span>
                {{ $posts->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
