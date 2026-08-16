@extends('layouts.admin')

@section('title', 'বইয়ের ক্যাটাগরি')
@section('heading', 'ক্যাটাগরি পরিচালনা')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">ক্যাটাগরি</li>
@endsection

@section('actions')
    <a href="{{ route('admin.content.create', 'categories') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> নতুন ক্যাটাগরি তৈরি
    </a>
@endsection

@section('content')

{{-- Stats Row --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="adm-card p-3 d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; font-size: 1.25rem;">
                <i class="fas fa-folder-tree"></i>
            </div>
            <div>
                <div class="small text-muted fw-semibold">সর্বমোট ক্যাটাগরি</div>
                <div class="fs-4 fw-bold text-dark">@bn($stats['total'])</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="adm-card p-3 d-flex align-items-center gap-3">
            <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; font-size: 1.25rem;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <div class="small text-muted fw-semibold">সক্রিয় ক্যাটাগরি</div>
                <div class="fs-4 fw-bold text-success">@bn($stats['active'])</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="adm-card p-3 d-flex align-items-center gap-3">
            <div class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; font-size: 1.25rem;">
                <i class="fas fa-folder"></i>
            </div>
            <div>
                <div class="small text-muted fw-semibold">মূল (প্যারেন্ট) ক্যাটাগরি</div>
                <div class="fs-4 fw-bold text-info">@bn($stats['parents'])</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="adm-card p-3 d-flex align-items-center gap-3">
            <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; font-size: 1.25rem;">
                <i class="fas fa-turn-down"></i>
            </div>
            <div>
                <div class="small text-muted fw-semibold">সাব-ক্যাটাগরি</div>
                <div class="fs-4 fw-bold text-warning">@bn($stats['children'])</div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="adm-card p-3 mb-4">
    <form method="GET" action="{{ route('admin.categories') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="search" class="form-control" placeholder="ক্যাটাগরির নাম দিয়ে খুঁজুন..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="parent_id" class="form-select">
                <option value="">— সব লেভেল —</option>
                <option value="root" @selected(request('parent_id') === 'root')>শুধুমাত্র মূল (প্যারেন্ট) ক্যাটাগরি</option>
                @foreach ($parentCategories as $pId => $pName)
                    <option value="{{ $pId }}" @selected((string)request('parent_id') === (string)$pId)>
                        সাব: {{ $pName }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="is_active" class="form-select">
                <option value="">— সব স্ট্যাটাস —</option>
                <option value="1" @selected(request('is_active') === '1')>সক্রিয়</option>
                <option value="0" @selected(request('is_active') === '0')>নিষ্ক্রিয়</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>ফিল্টার</button>
            @if(request()->hasAny(['search', 'parent_id', 'is_active']))
                <a href="{{ route('admin.categories') }}" class="btn btn-outline-secondary" title="রিসেট"><i class="fas fa-rotate-left"></i></a>
            @endif
        </div>
    </form>
</div>

{{-- Table --}}
<div class="adm-card p-0 overflow-hidden">
    <div class="table-responsive mb-0">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
            <thead class="table-light">
                <tr>
                    <th class="py-3 px-3" style="width: 60px;">#</th>
                    <th class="py-3 px-3">ক্যাটাগরির নাম ও স্লাগ</th>
                    <th class="py-3 px-3">মূল (প্যারেন্ট) ক্যাটাগরি</th>
                    <th class="py-3 px-3 text-center">সংযুক্ত বই</th>
                    <th class="py-3 px-3 text-center">ক্রম</th>
                    <th class="py-3 px-3 text-center">অবস্থা</th>
                    <th class="py-3 px-3 text-end" style="width: 140px;">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr>
                        <td class="px-3 text-muted">@bn($loop->iteration + ($categories->currentPage() - 1) * $categories->perPage())</td>
                        <td class="px-3">
                            <div class="fw-bold text-dark">
                                @if($cat->parent_id)
                                    <i class="fas fa-turn-down text-muted me-1 small"></i>
                                @else
                                    <i class="fas fa-folder text-primary me-1"></i>
                                @endif
                                {{ $cat->name }}
                            </div>
                            <small class="text-muted font-monospace">{{ $cat->slug }}</small>
                            @if($cat->description)
                                <div class="text-muted small text-truncate" style="max-width: 350px;">{{ $cat->description }}</div>
                            @endif
                        </td>
                        <td class="px-3">
                            @if($cat->parent)
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-folder-open text-primary me-1"></i> {{ $cat->parent->name }}
                                </span>
                            @else
                                <span class="badge bg-primary-subtle text-primary border">মূল ক্যাটাগরি</span>
                            @endif
                        </td>
                        <td class="px-3 text-center">
                            <span class="badge bg-secondary-subtle text-secondary fw-semibold">
                                @bn($cat->books_count ?? 0) টি বই
                            </span>
                        </td>
                        <td class="px-3 text-center text-muted">@bn($cat->sort_order ?? 0)</td>
                        <td class="px-3 text-center">
                            @if($cat->is_active)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">সক্রিয়</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">নিষ্ক্রিয়</span>
                            @endif
                        </td>
                        <td class="px-3 text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.content.edit', ['type' => 'categories', 'id' => $cat->id]) }}"
                                   class="btn btn-sm btn-outline-primary py-1 px-2" title="সম্পাদনা করুন">
                                    <i class="fas fa-pen-to-square"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.content.destroy', ['type' => 'categories', 'id' => $cat->id]) }}"
                                      onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ক্যাটাগরিটি মুছে ফেলতে চান?');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="মুছে ফেলুন">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fs-2 mb-2 d-block text-muted opacity-50"></i>
                            কোনো ক্যাটাগরি পাওয়া যায়নি।
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
        <div class="p-3 border-top">
            {{ $categories->links() }}
        </div>
    @endif
</div>

@endsection
