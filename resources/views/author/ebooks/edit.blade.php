@extends('author.layout')

@section('title', 'ই-বুক সম্পাদনা — ' . $ebook->title)
@section('heading', 'ই-বুক তথ্য সম্পাদনা (Edit E-Book)')

@section('content')
<form action="{{ route('author.ebooks.update', $ebook->id) }}" method="POST" enctype="multipart/form-data" class="row g-4" id="authorEbookEditForm">
    @csrf
    @method('PUT')

    {{-- LEFT COLUMN: MAIN CONTENT & DIGITAL FILES --}}
    <div class="col-12 col-lg-8">
        
        {{-- CARD 1: BASIC INFORMATION --}}
        <div class="author-card p-3 p-md-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                        <i class="fas fa-pen-to-square"></i>
                    </span>
                    <span>ই-বুক সাধারণ তথ্য ও বিবরণ</span>
                </h6>
                @if($ebook->mod_status === 'rejected' && $ebook->rejection_reason)
                    <span class="badge bg-danger text-white">সংশোধনের অনুরোধ: {{ $ebook->rejection_reason }}</span>
                @endif
            </div>

            <div class="row g-3">
                {{-- Title --}}
                <div class="col-12 col-md-8">
                    <label for="f-title" class="form-label small fw-bold text-dark mb-1">
                        ই-বুকের নাম (Title) <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="f-title" name="title" value="{{ old('title', $ebook->title) }}" required
                           class="form-control form-control-sm rounded-3 fw-semibold @error('title') is-invalid @enderror" 
                           placeholder="বইয়ের পূর্ণাঙ্গ নাম লিখুন..." oninput="updateLiveCard()">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- ISBN --}}
                <div class="col-12 col-md-4">
                    <label for="f-isbn" class="form-label small fw-bold text-dark mb-1">
                        ISBN / কোড
                    </label>
                    <input type="text" id="f-isbn" name="isbn" value="{{ old('isbn', $ebook->isbn) }}"
                           class="form-control form-control-sm rounded-3 font-monospace @error('isbn') is-invalid @enderror" 
                           placeholder="e.g. 978-984-XXXXX">
                    @error('isbn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Subtitle --}}
                <div class="col-12">
                    <label for="f-subtitle" class="form-label small fw-bold text-dark mb-1">
                        সাবটাইটেল বা ট্যাগলাইন (Subtitle)
                    </label>
                    <input type="text" id="f-subtitle" name="subtitle" value="{{ old('subtitle', $ebook->subtitle) }}"
                           class="form-control form-control-sm rounded-3 @error('subtitle') is-invalid @enderror" 
                           placeholder="বই সম্পর্কিত ছোট এক লাইনের বর্ণনা বা উপ-শিরোনাম...">
                    @error('subtitle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Category & Publisher --}}
                <div class="col-12 col-md-6">
                    <label for="f-category_id" class="form-label small fw-bold text-dark mb-1">
                        বিষয়শ্রেণী (Category) <span class="text-danger">*</span>
                    </label>
                    <select id="f-category_id" name="category_id" required
                            class="form-select form-select-sm rounded-3 @error('category_id') is-invalid @enderror">
                        <option value="">-- বিষয়শ্রেণী নির্বাচন করুন --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $ebook->category_id) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="f-publisher_id" class="form-label small fw-bold text-dark mb-1">
                        প্রকাশনা সংস্থা
                    </label>
                    <select id="f-publisher_id" name="publisher_id"
                            class="form-select form-select-sm rounded-3 @error('publisher_id') is-invalid @enderror">
                        <option value="">আইডিয়া প্রকাশন (ডিফল্ট)</option>
                        @foreach($publishers as $pub)
                            <option value="{{ $pub->id }}" @selected(old('publisher_id', $ebook->publisher_id) == $pub->id)>{{ $pub->name }}</option>
                        @endforeach
                    </select>
                    @error('publisher_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Description --}}
                <div class="col-12 mt-2">
                    <label for="f-description" class="form-label small fw-bold text-dark mb-1">
                        বইয়ের বিস্তারিত বিবরণ ও সূচিপত্র <span class="text-danger">*</span>
                    </label>
                    <textarea id="f-description" name="description" rows="7" required
                              class="form-control rounded-3 @error('description') is-invalid @enderror">{{ old('description', $ebook->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- CARD 2: DIGITAL FILES --}}
        <div class="author-card p-3 p-md-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-circle p-2">
                        <i class="fas fa-file-pdf"></i>
                    </span>
                    <span>ডিজিটাল ফাইল পরিবর্তন (ঐচ্ছিক)</span>
                </h6>
            </div>

            <div class="row g-3">
                {{-- 1. Main File --}}
                <div class="col-12">
                    <div class="p-3 rounded-3 border bg-light bg-opacity-50">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label for="f-file_path" class="form-label small fw-bold text-dark mb-0">
                                মূল ই-বুক ফাইল (PDF / EPUB)
                            </label>
                            @if($ebook->file_path)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="fas fa-check-circle me-1"></i> বর্তমান ফাইল সংরক্ষিত
                                </span>
                            @endif
                        </div>
                        <input type="file" id="f-file_path" name="file_path" accept=".pdf,.epub"
                               class="form-control form-control-sm rounded-3 @error('file_path') is-invalid @enderror">
                        <small class="text-muted d-block mt-1" style="font-size: 11px;">ফাইল পরিবর্তন করতে চাইলে নতুন ফাইল সিলেক্ট করুন।</small>
                        @error('file_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- 2. Optional EPUB --}}
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border bg-light bg-opacity-50 h-100">
                        <label for="f-epub_file_path" class="form-label small fw-bold text-dark mb-1">
                            ডেডিকেটেড EPUB ফাইল
                        </label>
                        <input type="file" id="f-epub_file_path" name="epub_file_path" accept=".epub"
                               class="form-control form-control-sm rounded-3 @error('epub_file_path') is-invalid @enderror">
                        @error('epub_file_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- 3. Free Sample File --}}
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border bg-light bg-opacity-50 h-100">
                        <label for="f-sample_file_path" class="form-label small fw-bold text-dark mb-1">
                            ফ্রি নমুনা অংশ (Sample Preview)
                        </label>
                        <input type="file" id="f-sample_file_path" name="sample_file_path" accept=".pdf,.epub"
                               class="form-control form-control-sm rounded-3 @error('sample_file_path') is-invalid @enderror">
                        @error('sample_file_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT SIDEBAR --}}
    <div class="col-12 col-lg-4">
        
        {{-- CARD 1: UPDATE & SAVE --}}
        <div class="author-card p-3 p-md-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-1.5">
                    <i class="fas fa-floppy-disk text-success"></i> পরিবর্তন সংরক্ষণ
                </h6>
            </div>

            <button type="submit" class="btn btn-success btn-lg w-100 rounded-pill fw-bold shadow-sm py-2.5 d-flex align-items-center justify-content-center gap-2 mb-2">
                <i class="fas fa-circle-check fs-5"></i>
                <span>আপডেট ও সেভ করুন (Save Changes)</span>
            </button>

            <a href="{{ route('author.ebooks.index') }}" class="btn btn-outline-secondary w-100 rounded-pill fw-semibold btn-sm py-2">
                বাতিল করুন
            </a>
        </div>

        {{-- CARD 2: PRICING & 50% ROYALTY --}}
        <div class="author-card p-3 p-md-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-1.5">
                    <i class="fas fa-hand-holding-dollar text-warning"></i> মূল্য ও ৫০% রয়্যালটি
                </h6>
            </div>

            <div class="d-flex flex-column gap-3">
                <div>
                    <label for="f-price" class="form-label small fw-bold text-dark mb-1">
                        বিক্রয় মূল্য (Price ৳) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light fw-bold">৳</span>
                        <input type="number" step="0.01" min="0" id="f-price" name="price" 
                               value="{{ old('price', $ebook->price) }}" required
                               class="form-control rounded-end-3 font-monospace fw-semibold @error('price') is-invalid @enderror" 
                               placeholder="150.00" oninput="calculateRoyalty()">
                    </div>
                    @error('price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- Live Royalty Calculator --}}
                <div class="p-3 rounded-3 border bg-success-subtle bg-opacity-30">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-semibold text-dark">প্রতি কপিতে আপনার আয় (৫০%):</span>
                        <strong class="text-success fs-5 font-monospace" id="authorEarningDisplay">৳0.00</strong>
                    </div>
                    <div class="d-flex align-items-center justify-content-between text-muted small" style="font-size: 11px;">
                        <span>প্ল্যাটফর্ম ফি (৫০%):</span>
                        <span class="font-monospace" id="platformFeeDisplay">৳0.00</span>
                    </div>
                </div>

                <div>
                    <label for="f-pages" class="form-label small fw-bold text-dark mb-1">
                        মোট পৃষ্ঠা সংখ্যা
                    </label>
                    <input type="number" min="1" id="f-pages" name="pages" value="{{ old('pages', $ebook->pages) }}"
                           class="form-control form-control-sm rounded-3 font-monospace @error('pages') is-invalid @enderror">
                    @error('pages')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- CARD 3: COVER IMAGE --}}
        <div class="author-card p-3 p-md-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-1.5">
                    <i class="fas fa-image text-primary"></i> প্রচ্ছদ ছবি (Cover)
                </h6>
            </div>

            <div class="position-relative mx-auto rounded-3 overflow-hidden border shadow-xs mb-3 text-center d-flex align-items-center justify-content-center" 
                 style="width: 155px; aspect-ratio: 7 / 10; background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);">
                <img id="coverPreviewImg" src="{{ $ebook->cover_url ?? '' }}" alt="Cover" class="w-100 h-100 object-fit-cover">
            </div>

            <div>
                <input type="file" id="f-cover_image" name="cover_image" accept="image/*"
                       class="form-control form-control-sm rounded-3 @error('cover_image') is-invalid @enderror"
                       onchange="previewCover(this)">
                <small class="text-muted d-block mt-1" style="font-size: 11px;">নতুন ছবি দিলে পূর্বের ছবি পরিবর্তিত হবে।</small>
                @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

    </div>
</form>

@push('scripts')
<script>
function previewCover(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = document.getElementById('coverPreviewImg');
            if (previewImg) {
                previewImg.src = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function calculateRoyalty() {
    const price = parseFloat(document.getElementById('f-price')?.value) || 0;
    const authorShare = (price * 0.50).toFixed(2);
    const platformShare = (price - authorShare).toFixed(2);

    document.getElementById('authorEarningDisplay').textContent = '৳' + authorShare;
    document.getElementById('platformFeeDisplay').textContent = '৳' + platformShare;
}

document.addEventListener('DOMContentLoaded', function() {
    calculateRoyalty();
});
</script>
@endpush
@endsection
