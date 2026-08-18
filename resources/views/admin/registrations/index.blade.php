@extends('layouts.admin')

@section('title', 'রেজিস্ট্রেশন অনুমোদন')
@section('heading', 'রেজিস্ট্রেশন অনুমোদন')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">রেজিস্ট্রেশন</li>
@endsection

@section('content')
<div>
    <p class="text-muted small mb-3">নতুন সেলার, প্রকাশক ও লেখকদের আবেদন অনুমোদন, সংশোধন বা প্রত্যাখ্যান করুন।</p>

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
    <form method="GET" class="card border-0 shadow-sm mb-4 p-3 rounded-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control rounded-3" placeholder="নাম, ইমেইল বা ফোন দিয়ে খুঁজুন..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select rounded-3">
                    <option value="">সব ধরন</option>
                    <option value="seller" @selected(request('type')==='seller')>সেলার</option>
                    <option value="publisher" @selected(request('type')==='publisher')>প্রকাশক</option>
                    <option value="author" @selected(request('type')==='author')>লেখক</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100 rounded-3"><i class="fas fa-search me-1"></i>ফিল্টার</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.registrations.index') }}" class="btn btn-outline-secondary w-100 rounded-3">রিসেট</a>
            </div>
            @if(request()->hasAny(['status','type'])) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#E8F4F8">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>নাম ও যোগাযোগ</th>
                        <th>ধরন</th>
                        <th style="min-width: 230px;">অতিরিক্ত তথ্য ও পরিচিতি</th>
                        <th>স্ট্যাটাস</th>
                        <th>তারিখ</th>
                        <th class="pe-4 text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $user)
                    @php 
                        $regData = is_array($user->reg_data) ? $user->reg_data : []; 
                        $bioText = $regData['bio'] ?? null;
                    @endphp
                    <tr>
                        <td class="ps-4 text-muted small">{{ $user->id }}</td>
                        <td>
                            <p class="fw-bold mb-0 text-dark">{{ $user->name }}</p>
                            <small class="text-muted d-block"><i class="fa-regular fa-envelope me-1"></i>{{ $user->email }}</small>
                            <small class="text-muted"><i class="fa-solid fa-phone me-1"></i>{{ $user->phone }}</small>
                        </td>
                        <td>
                            @php $icons = ['seller'=>'store','publisher'=>'print','author'=>'pen-fancy']; $colors=['seller'=>'#0066cc','publisher'=>'#d63384','author'=>'#198754']; @endphp
                            <span class="badge rounded-pill px-3 py-1.5 fw-semibold" style="background:{{ $colors[$user->reg_type] ?? '#6c757d' }}20;color:{{ $colors[$user->reg_type] ?? '#6c757d' }}">
                                <i class="fas fa-{{ $icons[$user->reg_type] ?? 'user' }} me-1"></i>
                                {{ ucfirst($user->reg_type ?? 'unknown') }}
                            </span>
                        </td>
                        <td class="small text-muted">
                            @if(!empty($regData['pen_name']))
                                <div class="mb-1"><strong>ছদ্মনাম:</strong> {{ $regData['pen_name'] }}</div>
                            @endif
                            @if(!empty($regData['genre']))
                                <div class="mb-1"><strong>ঘরানা:</strong> {{ $regData['genre'] }}</div>
                            @endif
                            @if(!empty($regData['shop_name']))
                                <div class="mb-1"><strong>দোকান:</strong> {{ $regData['shop_name'] }}</div>
                            @endif
                            @if(!empty($regData['publisher_name']))
                                <div class="mb-1"><strong>প্রকাশনী:</strong> {{ $regData['publisher_name'] }}</div>
                            @endif
                            @if(!empty($bioText))
                                <div class="mt-1 p-1.5 bg-light rounded border border-light-subtle" style="font-size: 11.5px;">
                                    <strong>বায়ো:</strong> 
                                    <span>{{ \Illuminate\Support\Str::words($bioText, 5, '...') }}</span>
                                    <button type="button" 
                                            class="btn btn-link btn-sm p-0 ms-1 text-primary text-decoration-none fw-bold" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#bioModal{{ $user->id }}" 
                                            style="font-size: 11.5px;">
                                        পড়তে ক্লিক করুন
                                    </button>
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($user->reg_status === 'pending')
                                <span class="badge bg-warning text-dark px-2.5 py-1">অপেক্ষমান</span>
                            @elseif($user->reg_status === 'approved')
                                <span class="badge bg-success px-2.5 py-1">অনুমোদিত</span>
                            @else
                                <span class="badge bg-danger px-2.5 py-1">প্রত্যাখ্যাত</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="pe-4 text-end">
                            <div class="d-inline-flex gap-1 align-items-center">
                                <a href="{{ route('admin.registrations.show', $user) }}" class="btn btn-sm btn-outline-primary" title="বিস্তারিত দেখুন">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.registrations.edit', $user) }}" class="btn btn-sm btn-outline-secondary" title="তথ্য সংশোধন ও এডিট">
                                    <i class="fas fa-edit"></i>
                                </a>

                                {{-- Approve Action (Always available) --}}
                                <form method="POST" action="{{ route('admin.registrations.approve', $user) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $user->reg_status === 'approved' ? 'btn-outline-success' : 'btn-success' }}" 
                                            title="{{ $user->reg_status === 'approved' ? 'অনুমোদিত (পুনরায় অনুমোদন করুন)' : 'অনুমোদন করুন ও ইমেইল পাঠান' }}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>

                                {{-- Reject Action (Always available) --}}
                                <button type="button" class="btn btn-sm {{ $user->reg_status === 'rejected' ? 'btn-outline-danger' : 'btn-danger' }}" 
                                        title="{{ $user->reg_status === 'rejected' ? 'বাতিলকৃত (নতুন কারণসহ বাতিল)' : 'প্রত্যাখ্যান / বাতিল করুন' }}"
                                        data-bs-toggle="modal" data-bs-target="#rejectModal{{ $user->id }}">
                                    <i class="fas fa-times"></i>
                                </button>

                                {{-- Delete Action (Always available) --}}
                                <form method="POST" action="{{ route('admin.registrations.cancel', $user) }}" 
                                      onsubmit="return confirm('আপনি কি নিশ্চিত যে {{ addslashes($user->name) }} এর রেজিস্ট্রেশন ও অ্যাকাউন্টটি সম্পূর্ণ মুছে ফেলতে চান?');" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="সম্পূর্ণ মুছে ফেলুন">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>

                            {{-- Bio Popup Modal --}}
                            @if(!empty($bioText))
                            <div class="modal fade text-start" id="bioModal{{ $user->id }}" tabindex="-1" aria-labelledby="bioModalLabel{{ $user->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <div class="modal-header border-bottom py-3">
                                            <h6 class="modal-title fw-bold" id="bioModalLabel{{ $user->id }}">
                                                <i class="fas fa-feather-pointed text-success me-2"></i>{{ $user->name }} — পরিচিতি / বায়ো
                                            </h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <p class="mb-0 text-dark" style="white-space: pre-line; line-height: 1.7; font-size: 14px;">{{ $bioText }}</p>
                                        </div>
                                        <div class="modal-footer border-top py-2">
                                            <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">বন্ধ করুন</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Reject modal --}}
                            <div class="modal fade text-start" id="rejectModal{{ $user->id }}" tabindex="-1">
                                <div class="modal-dialog modal-sm modal-dialog-centered">
                                    <form method="POST" action="{{ route('admin.registrations.reject', $user) }}" class="modal-content rounded-4 border-0 shadow">
                                        @csrf @method('PATCH')
                                        <div class="modal-header border-0 pb-0">
                                            <h6 class="modal-title fw-bold">প্রত্যাখ্যানের কারণ</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <textarea name="reason" class="form-control rounded-3" rows="3" required placeholder="কারণ লিখুন..."></textarea>
                                        </div>
                                        <div class="modal-footer border-0 pt-0">
                                            <button type="submit" class="btn btn-danger w-100 rounded-pill">প্রত্যাখ্যান করুন</button>
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
