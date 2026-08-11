@extends('layouts.admin')

@section('title', $user->name . ' — রেজিস্ট্রেশন')
@section('heading', 'রেজিস্ট্রেশন বিস্তারিত')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.registrations.index') }}" class="text-decoration-none">রেজিস্ট্রেশন</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
@endsection

@section('actions')
    <a href="{{ route('admin.registrations.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>ফিরে যান</a>
@endsection

@section('content')
<div style="max-width:900px">
    <div class="row g-4">
        {{-- Profile card --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-4">
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:80px;height:80px">
                    <i class="fas fa-user fa-2x text-muted"></i>
                </div>
                <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                <p class="text-muted small mb-2">{{ $user->email }}</p>
                <p class="text-muted small mb-3">{{ $user->phone }}</p>

                @if($user->reg_status === 'pending')
                    <span class="badge bg-warning text-dark px-3 py-2 mb-3">অপেক্ষমান</span>
                @elseif($user->reg_status === 'approved')
                    <span class="badge bg-success px-3 py-2 mb-3">অনুমোদিত</span>
                @else
                    <span class="badge bg-danger px-3 py-2 mb-3">প্রত্যাখ্যাত</span>
                @endif

                <small class="text-muted d-block">নিবন্ধনের তারিখ:<br>{{ $user->created_at->format('d M Y, h:i A') }}</small>
            </div>
        </div>

        {{-- Details card --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.05em">প্রদত্ত তথ্য</h6>
                    <table class="table table-sm table-borderless">
                        <tbody>
                            <tr><td class="fw-semibold text-muted" style="width:40%">ধরন</td><td>{{ ucfirst($user->reg_type) }}</td></tr>
                            @if($user->reg_data)
                                @foreach($user->reg_data as $key => $value)
                                <tr>
                                    <td class="fw-semibold text-muted">{{ str_replace('_', ' ', ucwords($key, '_')) }}</td>
                                    <td>{{ $value ?: '—' }}</td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>

                    @if($user->rejection_reason)
                    <div class="alert alert-danger mt-3 mb-0 small">
                        <strong>প্রত্যাখ্যানের কারণ:</strong> {{ $user->rejection_reason }}
                    </div>
                    @endif

                    @if($user->reg_status === 'pending')
                    <hr>
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('admin.registrations.approve', $user) }}" class="flex-grow-1">
                            @csrf @method('PATCH')
                            <button class="btn btn-success w-100"><i class="fas fa-check me-1"></i>অনুমোদন করুন</button>
                        </form>
                        <button class="btn btn-danger flex-grow-1" data-bs-toggle="collapse" data-bs-target="#rejectForm">
                            <i class="fas fa-times me-1"></i>প্রত্যাখ্যান করুন
                        </button>
                    </div>
                    <div id="rejectForm" class="collapse mt-3">
                        <form method="POST" action="{{ route('admin.registrations.reject', $user) }}">
                            @csrf @method('PATCH')
                            <label class="form-label fw-semibold small">প্রত্যাখ্যানের কারণ <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control form-control-sm mb-2" rows="3" required placeholder="ব্যবহারকারীকে কারণ জানানো হবে..."></textarea>
                            <button type="submit" class="btn btn-danger btn-sm w-100">নিশ্চিত করুন</button>
                        </form>
                    </div>
                    @endif

                    @if($user->reg_status === 'rejected')
                    <hr>
                    <form method="POST" action="{{ route('admin.registrations.cancel', $user) }}" onsubmit="return confirm('সম্পূর্ণ ডিলিট করবেন?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm"><i class="fas fa-trash me-1"></i>রেকর্ড মুছুন</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
