@extends('layouts.admin')

@section('title', 'রেজিস্ট্রেশন এডিট — ' . $user->name)
@section('heading', 'রেজিস্ট্রেশন তথ্য সংশোধন')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.registrations.index') }}" class="text-decoration-none">রেজিস্ট্রেশন</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.registrations.show', $user) }}" class="text-decoration-none">{{ $user->name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">এডিট</li>
@endsection

@section('actions')
    <a href="{{ route('admin.registrations.show', $user) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> ফিরে যান
    </a>
@endsection

@section('content')
<div style="max-width: 850px;">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-light border-0 py-3 px-4">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-user-edit text-primary me-2"></i>আবেদনকারীর তথ্য সম্পাদনা (ID: #{{ $user->id }})
            </h5>
        </div>
        <div class="card-body p-4">
            @if($errors->any())
                <div class="alert alert-danger rounded-3 mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.registrations.update', $user) }}">
                @csrf
                @method('PUT')

                @php $regData = is_array($user->reg_data) ? $user->reg_data : []; @endphp

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">নাম <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ইমেইল এড্রেস <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control rounded-3" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">মোবাইল নম্বর (ইউজারনেম) <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control rounded-3" value="{{ old('phone', $user->phone) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">অ্যাকাউন্ট ধরন <span class="text-danger">*</span></label>
                        <select name="role" class="form-select rounded-3">
                            <option value="author" @selected(old('role', $user->role) === 'author')>লেখক (Author)</option>
                            <option value="seller" @selected(old('role', $user->role) === 'seller')>সেলার (Seller)</option>
                            <option value="publisher" @selected(old('role', $user->role) === 'publisher')>প্রকাশক (Publisher)</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">অনুমোদন স্ট্যাটাস <span class="text-danger">*</span></label>
                        <select name="reg_status" class="form-select rounded-3">
                            <option value="pending" @selected(old('reg_status', $user->reg_status) === 'pending')>অপেক্ষমান (Pending)</option>
                            <option value="approved" @selected(old('reg_status', $user->reg_status) === 'approved')>অনুমোদিত (Approved)</option>
                            <option value="rejected" @selected(old('reg_status', $user->reg_status) === 'rejected')>প্রত্যাখ্যাত (Rejected)</option>
                        </select>
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="fw-bold text-muted mb-3"><i class="fas fa-list-check me-1"></i> অতিরিক্ত আবেদন তথ্য</h6>

                {{-- Author fields --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ছদ্মনাম / কলমনাম</label>
                        <input type="text" name="pen_name" class="form-control rounded-3" value="{{ old('pen_name', $regData['pen_name'] ?? '') }}" placeholder="ঐচ্ছিক">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ঘরানা / বিষয়</label>
                        <input type="text" name="genre" class="form-control rounded-3" value="{{ old('genre', $regData['genre'] ?? '') }}" placeholder="যেমন: গল্প, উপন্যাস...">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">লেখক পরিচিতি / বায়ো</label>
                        <textarea name="bio" rows="4" class="form-control rounded-3" placeholder="লেখকের বায়ো...">{{ old('bio', $regData['bio'] ?? '') }}</textarea>
                    </div>
                </div>

                {{-- Seller & Publisher fields --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">দোকানের নাম (সেলারদের জন্য)</label>
                        <input type="text" name="shop_name" class="form-control rounded-3" value="{{ old('shop_name', $regData['shop_name'] ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">প্রকাশনীর নাম (প্রকাশকদের জন্য)</label>
                        <input type="text" name="publisher_name" class="form-control rounded-3" value="{{ old('publisher_name', $regData['publisher_name'] ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ট্রেড লাইসেন্স নম্বর</label>
                        <input type="text" name="trade_license" class="form-control rounded-3" value="{{ old('trade_license', $regData['trade_license'] ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">জাতীয় পরিচয়পত্র নম্বর (NID)</label>
                        <input type="text" name="nid" class="form-control rounded-3" value="{{ old('nid', $regData['nid'] ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">ঠিকানা</label>
                        <textarea name="address" rows="2" class="form-control rounded-3">{{ old('address', $regData['address'] ?? '') }}</textarea>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('admin.registrations.show', $user) }}" class="btn btn-outline-secondary px-4 rounded-pill">বাতিল</a>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold">
                        <i class="fas fa-save me-1"></i> তথ্য সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
