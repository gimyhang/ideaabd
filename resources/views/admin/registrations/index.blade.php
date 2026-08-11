@extends('layouts.admin')

@section('title', 'রেজিস্ট্রেশন অনুমোদন')
@section('heading', 'রেজিস্ট্রেশন অনুমোদন')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">রেজিস্ট্রেশন</li>
@endsection

@section('content')
<div>
    <p class="text-muted small mb-3">নতুন সেলার, প্রকাশক ও লেখকদের আবেদন অনুমোদন বা প্রত্যাখ্যান করুন।</p>

    {{-- Count tabs --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        @php $tab = request('status',''); @endphp
        <a href="{{ route('admin.registrations.index') }}" class="btn btn-sm {{ $tab==='' ? 'btn-primary' : 'btn-outline-primary' }}">
            সব <span class="badge bg-white text-primary ms-1">{{ $counts['all'] }}</span>
        </a>
        <a href="{{ route('admin.registrations.index', ['status'=>'pending']) }}" class="btn btn-sm {{ $tab==='pending' ? 'btn-warning' : 'btn-outline-warning' }}">
            অপেক্ষমান <span class="badge bg-white text-warning ms-1">{{ $counts['pending'] }}</span>
        </a>
        <a href="{{ route('admin.registrations.index', ['status'=>'approved']) }}" class="btn btn-sm {{ $tab==='approved' ? 'btn-success' : 'btn-outline-success' }}">
            অনুমোদিত <span class="badge bg-white text-success ms-1">{{ $counts['approved'] }}</span>
        </a>
        <a href="{{ route('admin.registrations.index', ['status'=>'rejected']) }}" class="btn btn-sm {{ $tab==='rejected' ? 'btn-danger' : 'btn-outline-danger' }}">
            প্রত্যাখ্যাত <span class="badge bg-white text-danger ms-1">{{ $counts['rejected'] }}</span>
        </a>
    </div>

    {{-- Filter bar --}}
    <form method="GET" class="card border-0 shadow-sm mb-4 p-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="নাম, ইমেইল বা ফোন দিয়ে খুঁজুন..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select">
                    <option value="">সব ধরন</option>
                    <option value="seller" @selected(request('type')==='seller')>সেলার</option>
                    <option value="publisher" @selected(request('type')==='publisher')>প্রকাশক</option>
                    <option value="author" @selected(request('type')==='author')>লেখক</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>ফিল্টার</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.registrations.index') }}" class="btn btn-outline-secondary w-100">রিসেট</a>
            </div>
            @if(request()->hasAny(['status','type'])) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#E8F4F8">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>নাম ও যোগাযোগ</th>
                        <th>ধরন</th>
                        <th>অতিরিক্ত তথ্য</th>
                        <th>স্ট্যাটাস</th>
                        <th>তারিখ</th>
                        <th class="pe-4">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $user)
                    <tr>
                        <td class="ps-4 text-muted small">{{ $user->id }}</td>
                        <td>
                            <p class="fw-bold mb-0">{{ $user->name }}</p>
                            <small class="text-muted d-block">{{ $user->email }}</small>
                            <small class="text-muted">{{ $user->phone }}</small>
                        </td>
                        <td>
                            @php $icons = ['seller'=>'store','publisher'=>'print','author'=>'pen-fancy']; $colors=['seller'=>'#0066cc','publisher'=>'#d63384','author'=>'#198754']; @endphp
                            <span class="badge rounded-pill px-3 py-2" style="background:{{ $colors[$user->reg_type] ?? '#6c757d' }}20;color:{{ $colors[$user->reg_type] ?? '#6c757d' }}">
                                <i class="fas fa-{{ $icons[$user->reg_type] ?? 'user' }} me-1"></i>
                                {{ ucfirst($user->reg_type ?? 'unknown') }}
                            </span>
                        </td>
                        <td class="small text-muted">
                            @if($user->reg_data)
                                @foreach(array_slice($user->reg_data, 0, 2) as $k => $v)
                                    <div><strong>{{ $k }}:</strong> {{ $v }}</div>
                                @endforeach
                            @endif
                        </td>
                        <td>
                            @if($user->reg_status === 'pending')
                                <span class="badge bg-warning text-dark">অপেক্ষমান</span>
                            @elseif($user->reg_status === 'approved')
                                <span class="badge bg-success">অনুমোদিত</span>
                            @else
                                <span class="badge bg-danger">প্রত্যাখ্যাত</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="pe-4">
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.registrations.show', $user) }}" class="btn btn-sm btn-outline-primary" title="বিস্তারিত">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($user->reg_status === 'pending')
                                <form method="POST" action="{{ route('admin.registrations.approve', $user) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-success" title="অনুমোদন"><i class="fas fa-check"></i></button>
                                </form>
                                <button class="btn btn-sm btn-danger" title="প্রত্যাখ্যান"
                                    data-bs-toggle="modal" data-bs-target="#rejectModal{{ $user->id }}">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                                @if($user->reg_status === 'rejected')
                                <form method="POST" action="{{ route('admin.registrations.cancel', $user) }}" onsubmit="return confirm('সম্পূর্ণ মুছে ফেলবেন?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="মুছুন"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </div>

                            {{-- Reject modal --}}
                            <div class="modal fade" id="rejectModal{{ $user->id }}" tabindex="-1">
                                <div class="modal-dialog modal-sm">
                                    <form method="POST" action="{{ route('admin.registrations.reject', $user) }}" class="modal-content">
                                        @csrf @method('PATCH')
                                        <div class="modal-header border-0"><h6 class="modal-title fw-bold">প্রত্যাখ্যানের কারণ</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body">
                                            <textarea name="reason" class="form-control" rows="3" required placeholder="কারণ লিখুন..."></textarea>
                                        </div>
                                        <div class="modal-footer border-0 pt-0">
                                            <button type="submit" class="btn btn-danger w-100">প্রত্যাখ্যান করুন</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>কোনো রেজিস্ট্রেশন নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($registrations->hasPages())
        <div class="px-4 py-3 border-top">{{ $registrations->links() }}</div>
        @endif
    </div>
</div>
@endsection
