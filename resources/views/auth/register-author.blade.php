@extends('layouts.app')
@section('title', 'লেখক রেজিস্ট্রেশন - ideaabd')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-0 py-4" style="background:linear-gradient(135deg,#E5FFE5,#D4FFD4)">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;background:#198754">
                            <i class="fas fa-pen-fancy text-white"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0" style="color:#198754">লেখক রেজিস্ট্রেশন</h4>
                            <small class="text-muted">আপনার বই এবং লেখা প্রকাশ করুন</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <form method="POST" action="{{ route('register.submit', 'author') }}">
                        @csrf
                        @include('auth.partials.base-fields')

                        <hr class="my-3">
                        <h6 class="fw-bold text-muted mb-3">লেখকের তথ্য</h6>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">ছদ্মনাম / কলমনাম</label>
                            <input type="text" name="pen_name" class="form-control" value="{{ old('pen_name') }}" placeholder="ঐচ্ছিক — ব্যবহার না করলে আসল নাম ব্যবহার হবে">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">লেখার ধরন / ঘরানা <span class="text-danger">*</span></label>
                            <select name="genre" class="form-select @error('genre') is-invalid @enderror" required>
                                <option value="">বেছে নিন</option>
                                @foreach(['উপন্যাস','গল্প','কবিতা','প্রবন্ধ','শিশু সাহিত্য','বিজ্ঞান কল্পকাহিনী','রহস্য','ঐতিহাসিক','ধর্মীয়','অন্যান্য'] as $g)
                                <option value="{{ $g }}" @selected(old('genre') === $g)>{{ $g }}</option>
                                @endforeach
                            </select>
                            @error('genre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">পরিচিতি / বায়ো <span class="text-danger">*</span></label>
                            <textarea name="bio" rows="3" class="form-control @error('bio') is-invalid @enderror" required
                                      placeholder="আপনার সম্পর্কে সংক্ষেপে লিখুন...">{{ old('bio') }}</textarea>
                            @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">জাতীয় পরিচয়পত্র নম্বর</label>
                            <input type="text" name="nid" class="form-control" value="{{ old('nid') }}" placeholder="ঐচ্ছিক">
                        </div>

                        <div class="alert alert-info small py-2">
                            <i class="fas fa-info-circle me-1"></i>
                            রেজিস্ট্রেশনের পরে অ্যাডমিন যাচাই করবেন।
                        </div>

                        <button type="submit" class="btn w-100 py-2 fw-bold text-white" style="background:#198754">রেজিস্ট্রেশন করুন</button>
                        <p class="text-center mt-3 mb-0">
                            <a href="{{ route('register.choose') }}" class="text-muted small">← অন্য ধরনের অ্যাকাউন্ট</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
