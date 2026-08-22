@extends('author.layout')

@section('title', 'নতুন ই-বুক আপলোড ও প্রকাশ — লেখক পোর্টাল')
@section('heading', 'নতুন ই-বুক স্বত্ব ও পাণ্ডুলিপি আপলোড (Self-Publishing)')

@section('content')
<form action="{{ route('author.ebooks.store') }}" method="POST" enctype="multipart/form-data" class="row g-4" id="authorEbookForm">
    @csrf

    {{-- LEFT COLUMN: MAIN CONTENT & DIGITAL FILES (Width: ~67%) --}}
    <div class="col-12 col-lg-8">
        
        {{-- CARD 1: BASIC INFORMATION & DESCRIPTION --}}
        <div class="author-card p-3 p-md-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                        <i class="fas fa-tablet-screen-button"></i>
                    </span>
                    <span>ই-বুক সাধারণ তথ্য ও বিবরণ (Basic Details)</span>
                </h6>
                <span class="badge bg-light text-muted border small">* চিহ্নিত ঘরগুলো আবশ্যক</span>
            </div>

            <div class="row g-3">
                {{-- Title --}}
                <div class="col-12 col-md-8">
                    <label for="f-title" class="form-label small fw-bold text-dark mb-1">
                        <i class="fas fa-book text-primary me-1"></i> ই-বুকের নাম (Title) <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="f-title" name="title" value="{{ old('title') }}" required
                           class="form-control form-control-sm rounded-3 fw-semibold @error('title') is-invalid @enderror" 
                           placeholder="বইয়ের পূর্ণাঙ্গ নাম লিখুন..." oninput="updateLiveCard()">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- ISBN --}}
                <div class="col-12 col-md-4">
                    <label for="f-isbn" class="form-label small fw-bold text-dark mb-1">
                        <i class="fas fa-barcode text-secondary me-1"></i> ISBN / কোড (যদি থাকে)
                    </label>
                    <input type="text" id="f-isbn" name="isbn" value="{{ old('isbn') }}"
                           class="form-control form-control-sm rounded-3 font-monospace @error('isbn') is-invalid @enderror" 
                           placeholder="e.g. 978-984-XXXXX">
                    @error('isbn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Subtitle --}}
                <div class="col-12">
                    <label for="f-subtitle" class="form-label small fw-bold text-dark mb-1">
                        <i class="fas fa-quote-left text-muted me-1"></i> সাবটাইটেল বা ট্যাগলাইন (Subtitle)
                    </label>
                    <input type="text" id="f-subtitle" name="subtitle" value="{{ old('subtitle') }}"
                           class="form-control form-control-sm rounded-3 @error('subtitle') is-invalid @enderror" 
                           placeholder="বই সম্পর্কিত ছোট এক লাইনের বর্ণনা বা উপ-শিরোনাম...">
                    @error('subtitle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Category & Publisher --}}
                <div class="col-12 col-md-6">
                    <label for="f-category_id" class="form-label small fw-bold text-dark mb-1">
                        <i class="fas fa-folder-tree text-primary me-1"></i> বিষয়শ্রেণী (Category) <span class="text-danger">*</span>
                    </label>
                    <select id="f-category_id" name="category_id" required
                            class="form-select form-select-sm rounded-3 @error('category_id') is-invalid @enderror">
                        <option value="">-- বিষয়শ্রেণী নির্বাচন করুন --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="f-publisher_id" class="form-label small fw-bold text-dark mb-1">
                        <i class="fas fa-building text-secondary me-1"></i> প্রকাশনা সংস্থা (ঐচ্ছিক)
                    </label>
                    <select id="f-publisher_id" name="publisher_id"
                            class="form-select form-select-sm rounded-3 @error('publisher_id') is-invalid @enderror">
                        <option value="">আইডিয়া প্রকাশন (ডিফল্ট)</option>
                        @foreach($publishers as $pub)
                            <option value="{{ $pub->id }}" @selected(old('publisher_id') == $pub->id)>{{ $pub->name }}</option>
                        @endforeach
                    </select>
                    @error('publisher_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Description --}}
                <div class="col-12 mt-2">
                    <label for="f-description" class="form-label small fw-bold text-dark mb-1">
                        <i class="fas fa-align-left text-primary me-1"></i> বইয়ের বিস্তারিত বিবরণ ও সূচিপত্র <span class="text-danger">*</span>
                    </label>
                    <textarea id="f-description" name="description" rows="7" required
                              class="form-control rounded-3 @error('description') is-invalid @enderror" 
                              placeholder="বইয়ের বিষয়বস্তু, সারসংক্ষেপ, সূচিপত্র ও পাঠকদের জন্য বিস্তারিত বিবরণ লিখুন...">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- CARD 2: E-BOOK FILES & DIGITAL FORMATS --}}
        <div class="author-card p-3 p-md-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-circle p-2">
                        <i class="fas fa-file-pdf"></i>
                    </span>
                    <span>ই-বুক ডিজিটাল ফাইল ও পাণ্ডুলিপি আপলোড</span>
                </h6>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 small">
                    <i class="fas fa-shield-halved me-1"></i> DRM সুরক্ষিত
                </span>
            </div>

            <div class="row g-3">
                {{-- 1. Main File --}}
                <div class="col-12">
                    <div class="p-3 rounded-3 border bg-light bg-opacity-50">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label for="f-file_path" class="form-label small fw-bold text-dark mb-0">
                                <i class="fas fa-file-arrow-up text-primary me-1"></i> মূল ই-বুক ফাইল (PDF / EPUB) <span class="text-danger">*</span>
                            </label>
                            <span class="text-muted small" style="font-size: 11px;">সর্বোচ্চ ১০০ MB</span>
                        </div>
                        <p class="text-muted small mb-2" style="font-size: 11.5px;">
                            পাঠক এটি সরাসরি ডাউনলোড করতে পারবে না; শুধুমাত্র আমাদের কাস্টম DRM রিডারে ডাইনামিক ওয়াটারমার্কসহ পড়তে পারবে।
                        </p>
                        <input type="file" id="f-file_path" name="file_path" accept=".pdf,.epub" required
                               class="form-control form-control-sm rounded-3 @error('file_path') is-invalid @enderror">
                        @error('file_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- 2. Optional EPUB --}}
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border bg-light bg-opacity-50 h-100">
                        <label for="f-epub_file_path" class="form-label small fw-bold text-dark mb-1">
                            <i class="fas fa-book-open text-info me-1"></i> ডেডিকেটেড EPUB ফাইল (ঐচ্ছিক)
                        </label>
                        <small class="text-muted d-block mb-2" style="font-size: 11px;">ই-পাব রিডারের জন্য অপ্টিমাইজড ফাইল</small>
                        <input type="file" id="f-epub_file_path" name="epub_file_path" accept=".epub"
                               class="form-control form-control-sm rounded-3 @error('epub_file_path') is-invalid @enderror">
                        @error('epub_file_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- 3. Free Sample File --}}
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border bg-light bg-opacity-50 h-100">
                        <label for="f-sample_file_path" class="form-label small fw-bold text-dark mb-1">
                            <i class="fas fa-eye text-warning me-1"></i> ফ্রি নমুনা অংশ (Sample Preview PDF)
                        </label>
                        <small class="text-muted d-block mb-2" style="font-size: 11px;">পাঠকদের ফ্রিতে পড়ার জন্য ডেমো চ্যাপ্টার (ঐচ্ছিক)</small>
                        <input type="file" id="f-sample_file_path" name="sample_file_path" accept=".pdf,.epub"
                               class="form-control form-control-sm rounded-3 @error('sample_file_path') is-invalid @enderror">
                        @error('sample_file_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 3: TERMS & CONDITIONS (আইডিয়া প্রকাশন — ই-বুক প্রকাশনা চুক্তি ও শর্তাবলী) --}}
        <div class="author-card p-3 p-md-4 mb-4 border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                        <i class="fas fa-file-contract"></i>
                    </span>
                    <span>আইডিয়া প্রকাশন — ই-বুক প্রকাশনা চুক্তি ও শর্তাবলী (Terms & Conditions)</span>
                </h6>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small">ডিজিটাল চুক্তি</span>
            </div>

            <div class="p-3 rounded-3 bg-light bg-opacity-75 border overflow-y-auto small" style="max-height: 280px; line-height: 1.75; font-size: 0.85rem;">
                <h6 class="fw-bold text-primary mb-1.5"><i class="fas fa-certificate me-1"></i> ১. মালিকানা ও স্বত্বাধিকার (Copyright & Ownership)</h6>
                <p class="mb-1 text-dark"><strong>লেখকের অধিকার:</strong> বইয়ের মূল কপিরাইট বা সর্বস্বত্ব লেখকের কাছেই সংরক্ষিত থাকবে।</p>
                <p class="mb-3 text-dark"><strong>প্রকাশনা অধিকার:</strong> লেখক আইডিয়া প্রকাশন-কে বইটি ডিজিটাল (ই-বুক) ফরম্যাটে বিশ্বব্যাপী প্রদর্শন, বিক্রয় ও বিতরণের অ-একক (Non-Exclusive) বা একক (Exclusive) ডিজিটাল অধিকার প্রদান করছেন।</p>

                <h6 class="fw-bold text-primary mb-1.5"><i class="fas fa-shield-halved me-1"></i> ২. প্রকাশকের স্বার্থ সংরক্ষণ ও অধিকার (Publisher Rights & Protection)</h6>
                <p class="mb-1 text-dark"><strong>প্রিন্ট সংস্করণের অগ্রাধিকার (First Right of Refusal for Print):</strong> ই-বুক হিসেবে সাফল্য পেলে পরবর্তীতে বইটি পেপারব্যাক বা হার্ডকাভার (প্রিন্ট ভার্সন) হিসেবেও প্রকাশ করতে পারবেন। প্রিন্ট বইয়ের ক্ষেত্রে লেখক বিক্রির অর্থাৎ গায়ের মূল্যে ৩০% বাদ দিয়ে যেম মূল্য দাঁড়াবে তার ১০% রয়্যালটি পাবেন।</p>
                <p class="mb-1 text-dark"><strong>প্রচার ও মার্কেটিং অধিকার:</strong> বইয়ের প্রমোশনের স্বার্থে সামাজিক যোগাযোগ মাধ্যম, ওয়েবসাইট বা ব্যানারে বইয়ের কভার, সারসংক্ষেপ, লেখকের নাম ও ছবি প্রকাশের পূর্ণ অধিকার প্রকাশকের থাকবে।</p>
                <p class="mb-1 text-dark"><strong>প্রমোশনাল ডিসকাউন্ট ও মূল্য ছাড়:</strong> বিশেষ উৎসব বা ক্যাম্পেইনে ই-বুকের বিক্রয় বাড়াতে সাময়িক সময়ে ছাড় দেওয়ার অধিকার প্রকাশকের থাকবে।</p>
                <p class="mb-3 text-dark"><strong>ই-বুক অপসারণের এখতিয়ার:</strong> প্রকাশনী (আইডিয়া প্রকাশন) চাইলে যেকোনো সময় যেকোনো ই-বুক প্ল্যাটফর্ম থেকে নোটিশ ছাড়াই অপসারণ করার পূর্ণ অধিকার সংরক্ষণ করে।</p>

                <h6 class="fw-bold text-primary mb-1.5"><i class="fas fa-scale-balanced me-1"></i> ৩. পান্ডুলিপি, ফরম্যাটিং ও আইনি সুরক্ষা (Manuscript & Legal Terms)</h6>
                <p class="mb-1 text-dark"><strong>ফরম্যাট গ্রহণ:</strong> পান্ডুলিপি অবশ্যই সুনির্দিষ্ট ডিজিটাল ফরম্যাটে (যেমন: EPUB, Word বা PDF) জমা দিতে হবে।</p>
                <p class="mb-1 text-dark"><strong>মান নিয়ন্ত্রণ:</strong> কভার ডিজাইন, ইনডেক্সিং এবং ফন্ট রেন্ডারিং আইডিয়া প্রকাশনের ডিজিটাল স্ট্যান্ডার্ড অনুযায়ী অপটিমাইজ করা হবে।</p>
                <p class="mb-1 text-dark"><strong>নিষিদ্ধ বিষয়বস্তু:</strong> প্লেজিয়ারিজম (চুরি করা লেখা), মানহানিকর, ধর্মীয় ভাবাবেগে আঘাতকারী বা আইনবিরোধী কোনো বিষয়বস্তু প্রকাশ করা যাবে না।</p>
                <p class="mb-3 text-dark"><strong>ক্ষতিপূরণ ও আইনি দায় থেকে সুরক্ষা (Indemnity Clause):</strong> পান্ডুলিপির কনটেন্টের কারণে কোনো কপিরাইট বা আইনি জটিলতা তৈরি হলে, তার সম্পূর্ণ দায়ভার লেখক বহন করবেন এবং প্রকাশনীর কোনো আর্থিক বা সম্মানহানি হলে তা ক্ষতিপূরণ হিসেবে দিতে লেখক বাধ্য থাকবেন।</p>

                <h6 class="fw-bold text-primary mb-1.5"><i class="fas fa-hand-holding-dollar me-1"></i> ৪. রয়্যালটি ও মূল্য নির্ধারণ (Royalty & Pricing Model)</h6>
                <p class="mb-1 text-dark"><strong>মূল্য নির্ধারণ:</strong> বইয়ের ডিজিটাল বিক্রয়মূল্য (List Price) আইডিয়া প্রকাশন ও লেখক যৌথ আলোচনার মাধ্যমে নির্ধারণ করবেন।</p>
                <p class="mb-1 text-dark"><strong>রয়্যালটি বণ্টন:</strong> নেট বিক্রয়মূল্যের (Net Revenue) উপর চুক্তি অনুযায়ী [৫০% লেখক / ৫০% আইডিয়া প্রকাশন] হারে রয়্যালটি হিসাব করা হবে।</p>
                <p class="mb-1 text-dark"><strong>পেমেন্ট সাইকেল:</strong> প্রতি মাসের বা ত্রৈমাসিকের মোট রয়্যালটি হিসাব করে নির্ধারিত নির্দিষ্ট সময়ের [যেমন: ৩০ দিনের] মধ্যে লেখকের ব্যাংক অ্যাকাউন্ট বা বিকাশ/নগদে ট্রান্সফার করা হবে।</p>
                <p class="mb-3 text-dark"><strong>অভিযোগ ও রয়্যালটি বাজেয়াপ্তকরণ:</strong> কপিরাইট লঙ্ঘন, পাইরেসি বা আইনি অভিযোগে অভিযুক্ত বইয়ের ক্ষেত্রে জমাকৃত বা বকেয়া সমস্ত রয়্যালটি সরাসরি বাতিল বা বাজেয়াপ্ত বলে গণ্য হবে।</p>

                <h6 class="fw-bold text-primary mb-1.5"><i class="fas fa-lock me-1"></i> ৫. ডিজিটাল অধিকার ব্যবস্থাপনা (DRM & Content Security)</h6>
                <p class="mb-1 text-dark"><strong>কপিরাইট সুরক্ষা:</strong> অবৈধ পাইরেসি ও ডাউনলোড রোধে বইগুলোতে ডিজিটাল রাইটস ম্যানেজমেন্ট (DRM) বা প্রয়োজনীয় ওয়াটারমার্ক প্রটেকশন এনক্রিপ্ট করা থাকবে।</p>
                <p class="mb-3 text-dark"><strong>প্রিভিউ:</strong> পাঠকদের আকৃষ্ট করতে বইয়ের সর্বোচ্চ ১০% থেকে ১৫% অংশ পর্যন্ত ফ্রিতে স্যাম্পল বা প্রিভিউ হিসেবে প্রদর্শনের অধিকার প্ল্যাটফর্মের থাকবে।</p>

                <h6 class="fw-bold text-primary mb-1.5"><i class="fas fa-ban me-1"></i> ৬. নীতি লঙ্ঘন ও বই অপসারণ (Policy Violation & Termination)</h6>
                <p class="mb-0 text-dark"><strong>পলিসি লঙ্ঘন:</strong> আইডিয়া প্রকাশনের কোনো শর্ত বা কপিরাইট আইন লঙ্ঘিত হলে প্রকাশনী তাৎক্ষণিকভাবে বইটি প্ল্যাটফর্ম থেকে অপসারণের এবং চুক্তি বাতিলের পূর্ণ অধিকার রাখে।</p>
            </div>

            <div class="mt-3 p-2.5 rounded-3 bg-warning-subtle bg-opacity-30 border border-warning border-opacity-50">
                <div class="form-check m-0">
                    <input class="form-check-input" type="checkbox" id="terms_agree" name="terms_agree" required value="1">
                    <label class="form-check-label small fw-bold text-dark" for="terms_agree">
                        আমি আইডিয়া প্রকাশন-এর ই-বুক প্রকাশনা চুক্তি ও সকল শর্তাবলী মনোযোগ সহকারে পড়েছি এবং এতে পূর্ণ সম্মতি জ্ঞাপন করছি। <span class="text-danger">*</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT SIDEBAR: PRICING, 50% ROYALTY, COVER & SUBMIT (Width: ~33%) --}}
    <div class="col-12 col-lg-4">
        
        {{-- CARD 1: PUBLISH SUBMISSION & REVIEW --}}
        <div class="author-card p-3 p-md-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-1.5">
                    <i class="fas fa-paper-plane text-success"></i> রিভিউ ও প্রকাশনা
                </h6>
            </div>

            <p class="text-muted small mb-3" style="font-size: 12px; line-height: 1.5;">
                জমা দেওয়ার পর বইটি আইডিয়া প্রকাশন অ্যাডমিন প্যানেলে রিভিউতে জমা হবে। মান পর্যালোচনার পর বইটি লাইভ স্টোরে প্রকাশিত হবে।
            </p>

            {{-- Real-time File Upload Progress Bar --}}
            <div id="uploadProgressBox" class="d-none mb-3 p-2.5 rounded-3 bg-light border">
                <div class="d-flex justify-content-between small text-dark fw-semibold mb-1" style="font-size: 11.5px;">
                    <span id="uploadProgressLabel"><i class="fas fa-spinner fa-spin text-primary me-1"></i> ফাইল আপলোড হচ্ছে...</span>
                    <strong id="uploadProgressPercent" class="text-primary font-monospace">0%</strong>
                </div>
                <div class="progress" style="height: 7px;">
                    <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                </div>
                <small class="text-muted d-block mt-1" style="font-size: 10.5px;">বড় ফাইল আপলোডের সময় কিছুক্ষণ অপেক্ষা করুন।</small>
            </div>

            <button type="submit" id="ebookSubmitBtn" class="btn btn-success btn-lg w-100 rounded-pill fw-bold shadow-sm py-2.5 d-flex align-items-center justify-content-center gap-2 mb-2">
                <i class="fas fa-circle-check fs-5"></i>
                <span>পর্যালোচনার জন্য জমা দিন (Submit)</span>
            </button>

            <a href="{{ route('author.ebooks.index') }}" class="btn btn-outline-secondary w-100 rounded-pill fw-semibold btn-sm py-2">
                বাতিল করুন
            </a>
        </div>

        {{-- CARD 2: PRICING & 50% ROYALTY CALCULATOR --}}
        <div class="author-card p-3 p-md-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-1.5">
                    <i class="fas fa-hand-holding-dollar text-warning"></i> মূল্য ও ৫০% রয়্যালটি
                </h6>
                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2 py-0.5 small">50% Share</span>
            </div>

            <div class="d-flex flex-column gap-3">
                {{-- Price --}}
                <div>
                    <label for="f-price" class="form-label small fw-bold text-dark mb-1">
                        <i class="fas fa-tag text-primary me-1"></i> বিক্রয় মূল্য (Price ৳) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light fw-bold">৳</span>
                        <input type="number" step="0.01" min="0" id="f-price" name="price" 
                               value="{{ old('price', 150) }}" required
                               class="form-control rounded-end-3 font-monospace fw-semibold @error('price') is-invalid @enderror" 
                               placeholder="150.00" oninput="calculateRoyalty()">
                    </div>
                    @error('price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- Live 50% Royalty Share Display --}}
                <div class="p-3 rounded-3 border bg-success-subtle bg-opacity-30">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-semibold text-dark">প্রতি কপিতে আপনার আয় (৫০%):</span>
                        <strong class="text-success fs-5 font-monospace" id="authorEarningDisplay">৳75.00</strong>
                    </div>
                    <div class="d-flex align-items-center justify-content-between text-muted small" style="font-size: 11px;">
                        <span>প্ল্যাটফর্ম ফি (৫০%):</span>
                        <span class="font-monospace" id="platformFeeDisplay">৳75.00</span>
                    </div>
                </div>

                {{-- Pages --}}
                <div>
                    <label for="f-pages" class="form-label small fw-bold text-dark mb-1">
                        <i class="fas fa-file-lines text-secondary me-1"></i> মোট পৃষ্ঠা সংখ্যা (Pages)
                    </label>
                    <input type="number" min="1" id="f-pages" name="pages" value="{{ old('pages') }}"
                           class="form-control form-control-sm rounded-3 font-monospace @error('pages') is-invalid @enderror" 
                           placeholder="e.g. 180">
                    @error('pages')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- CARD 3: COVER IMAGE DROPZONE --}}
        <div class="author-card p-3 p-md-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-1.5">
                    <i class="fas fa-image text-primary"></i> প্রচ্ছদ ছবি (Cover) <span class="text-danger">*</span>
                </h6>
                <span class="badge bg-light text-muted border small">7:10 রেশিও</span>
            </div>

            {{-- Live Mockup Box --}}
            <div class="position-relative mx-auto rounded-3 overflow-hidden border shadow-xs mb-3 text-center d-flex align-items-center justify-content-center" 
                 style="width: 155px; aspect-ratio: 7 / 10; background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);">
                <img id="coverPreviewImg" src="" alt="Cover Preview" class="w-100 h-100 object-fit-cover d-none">
                <div id="coverPlaceholder" class="w-100 h-100 d-flex flex-column justify-content-between p-3 text-start" style="border-left: 4px solid #6366f1;">
                    <span class="badge bg-primary text-white" style="font-size: 0.6rem; width: fit-content;">ই-বুক</span>
                    <div class="my-auto">
                        <h6 id="mockupTitle" class="fw-bold text-white mb-0" style="font-size: 0.8rem; line-height: 1.35;">
                            ই-বুকের নাম
                        </h6>
                        <small class="text-white-50 d-block text-truncate" style="font-size: 0.68rem;">
                            {{ auth()->user()->name }}
                        </small>
                    </div>
                    <span class="text-white-50" style="font-size: 0.6rem;">আইডিয়া প্রকাশন</span>
                </div>
            </div>

            <div>
                <input type="file" id="f-cover_image" name="cover_image" accept="image/*" required
                       class="form-control form-control-sm rounded-3 @error('cover_image') is-invalid @enderror"
                       onchange="previewCover(this)">
                <small class="text-muted d-block mt-1" style="font-size: 11px;">JPG, PNG, WEBP ফরম্যাট (সর্বোচ্চ ৮ MB)</small>
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
            const placeholder = document.getElementById('coverPlaceholder');
            if (previewImg) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('d-none');
            }
            if (placeholder) {
                placeholder.classList.add('d-none');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function updateLiveCard() {
    const titleInput = document.getElementById('f-title');
    const mockupTitle = document.getElementById('mockupTitle');
    if (titleInput && mockupTitle) {
        mockupTitle.textContent = titleInput.value.trim() || 'ই-বুকের নাম';
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

    const form = document.getElementById('authorEbookForm');
    const submitBtn = document.getElementById('ebookSubmitBtn');
    const progressBox = document.getElementById('uploadProgressBox');
    const progressBar = document.getElementById('uploadProgressBar');
    const progressPercent = document.getElementById('uploadProgressPercent');
    const progressLabel = document.getElementById('uploadProgressLabel');

    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            const hasFiles = document.getElementById('f-epub_file_path')?.files.length > 0 || 
                             document.getElementById('f-file_path')?.files.length > 0 || 
                             document.getElementById('f-cover_image')?.files.length > 0;

            if (hasFiles && typeof XMLHttpRequest !== 'undefined') {
                e.preventDefault();
                submitBtn.disabled = true;
                if (progressBox) progressBox.classList.remove('d-none');

                const xhr = new XMLHttpRequest();
                const formData = new FormData(form);

                xhr.upload.addEventListener('progress', function(event) {
                    if (event.lengthComputable) {
                        const percent = Math.round((event.loaded / event.total) * 100);
                        if (progressBar) progressBar.style.width = percent + '%';
                        if (progressPercent) progressPercent.textContent = percent + '%';
                        if (percent >= 100 && progressLabel) {
                            progressLabel.innerHTML = '<i class="fas fa-cog fa-spin text-success me-1"></i> সার্ভারে ফাইল প্রস্তুত ও সেভ হচ্ছে...';
                        }
                    }
                });

                xhr.addEventListener('load', function() {
                    if (xhr.status >= 200 && xhr.status < 400) {
                        // Success or redirect
                        if (xhr.responseURL) {
                            window.location.href = xhr.responseURL;
                        } else {
                            window.location.href = "{{ route('author.ebooks.index') }}";
                        }
                    } else {
                        // Error handling: reload or replace document
                        document.open();
                        document.write(xhr.responseText);
                        document.close();
                    }
                });

                xhr.addEventListener('error', function() {
                    alert('আপলোডে সমস্যা হয়েছে। অনুগ্রহ করে ইন্টারনেট সংযোগ চেক করে পুনরায় চেষ্টা করুন।');
                    submitBtn.disabled = false;
                    if (progressBox) progressBox.classList.add('d-none');
                });

                xhr.open(form.method, form.action);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.send(formData);
            } else {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5"></span> প্রসেসিং হচ্ছে...';
            }
        });
    }
});
</script>
@endpush
@endsection
