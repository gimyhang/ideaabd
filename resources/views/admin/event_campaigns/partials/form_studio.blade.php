@php
    $studioFormLogo = $campaign->form_settings['logo_image'] ?? null;
    if ($studioFormLogo && !str_starts_with($studioFormLogo, 'http') && !str_starts_with($studioFormLogo, '/')) {
        $studioFormLogo = asset('storage/' . $studioFormLogo);
    }
    $studioLogoSize = intval($campaign->form_settings['logo_size'] ?? 70);
    $studioLogoShape = $campaign->form_settings['logo_shape'] ?? 'default';
    $studioLogoBorderWidth = intval($campaign->form_settings['logo_border_width'] ?? 0);
    $studioLogoBorderColor = $campaign->form_settings['logo_border_color'] ?? '#0f172a';
    $studioEmblemIcon = $campaign->form_settings['emblem_icon'] ?? 'fa-feather-pointed';

    $studioTitle = $campaign->title;
    $studioSubhead = $campaign->form_settings['form_subhead'] ?? '২০ অক্টোবর ১৩তম প্রতিষ্ঠাবার্ষিকী উপলক্ষে';
    $studioVenue = $campaign->form_settings['form_venue'] ?? 'স্থান: সরকারি টিচার্স ট্রেনিং কলেজ, রংপুর, বাংলাদেশ';
    $studioDate = $campaign->form_settings['form_date'] ?? 'তারিখ: ১৩ নভেম্বর ২০২৬';
    $studioOrg = $campaign->form_settings['form_org'] ?? 'নিবন্ধন সহযোগিতায়: আইডিয়া প্রকাশন | www.ideaabd.com';
    $studioCopyTag = $campaign->form_settings['form_copy_tag'] ?? 'DELEGATE COPY';

    $studioThemeColor = $campaign->form_settings['theme_color'] ?? ($campaign->theme_color ?: '#991b1b');
    $studioBorderColor = $campaign->form_settings['form_border_color'] ?? '#0f172a';
    $studioBgLabel = $campaign->form_settings['form_bg_label'] ?? '#f8fafc';
    $studioFontFamily = $campaign->form_settings['font_family'] ?? 'Hind Siliguri';

    $studioNoticeActive = !empty($campaign->form_settings['form_notice_active']);
    $studioNoticeType = $campaign->form_settings['form_notice_type'] ?? 'info';
    $studioNoticeText = $campaign->form_settings['form_notice_text'] ?? 'রংপুর সাহিত্য উৎসব ও লিটিলম্যাগ মেলার লেখক ও ডেলিগেট নিবন্ধন চলছে। আসন সংখ্যা সীমিত।';

    $defaultCatsList = [
        'কবি',
        'অনুবাদক',
        'ছড়াকার',
        'গল্পকার',
        'কথাসাহিত্যিক',
        'প্রাবন্ধিক',
        'গবেষক',
        'লিটিলম্যাগাজিন সম্পাদক',
        'প্রকাশক',
        'নাট্যকার',
        'শিল্পী / সংস্কৃতিকর্মী',
        'বই প্রতিনিধি ও সংগঠক',
        'অন্যান্য / প্রতিনিধি'
    ];
    $studioCategories = !empty($campaign->form_settings['categories']) && is_array($campaign->form_settings['categories']) && count($campaign->form_settings['categories']) > 0
        ? $campaign->form_settings['categories']
        : $defaultCatsList;

    $studioPhotoReq = $campaign->form_settings['photo_required'] ?? 'required';
    $studioEnableIntl = $campaign->form_settings['enable_intl_address'] ?? true;
    $studioEnableNotableBooks = $campaign->form_settings['enable_notable_books'] ?? true;
    $studioEnableMagazine = $campaign->form_settings['enable_magazine'] ?? true;
    $studioEnableLiterary = $campaign->form_settings['enable_literary_info'] ?? true;
    $studioEnableSignature = $campaign->form_settings['enable_signature'] ?? true;
    $studioRequiresApproval = $campaign->form_settings['requires_approval'] ?? true;
@endphp

<div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-4">
    {{-- Top Studio Header --}}
    <div class="card-header bg-dark text-white py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-pen text-warning"></i> রেজিস্ট্রেশন ফরম ও লোগো কাস্টমাইজার স্টুডিও
            </h5>
            <small class="text-light opacity-75">লাইভ ফর্ম কাস্টমাইজেশন — লোগো, হেডার টেক্সট, ক্যাটাগরি, কালার, নোটিশ ও সকল ফিচার নিয়ন্ত্রণ</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ $campaign->public_url }}" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3 fw-semibold">
                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> লাইভ ফরম দেখুন
            </a>
            <button type="button" class="btn btn-sm btn-light rounded-pill px-3 fw-bold" onclick="resetStudioDefaults()">
                <i class="fa-solid fa-rotate-left me-1"></i> রিসেট
            </button>
            <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill px-4 fw-bold shadow-sm" id="btnStudioTopSave" onclick="saveFormStudioAjax()">
                <i class="fa-solid fa-floppy-disk me-1"></i> সংরক্ষণ করুন
            </button>
        </div>
    </div>

    <div class="row g-0">
        {{-- বাম পাশের কন্ট্রোল প্যানেল (Accordion) --}}
        <div class="col-lg-5 p-3 p-xl-4 bg-light border-end" style="max-height: 850px; overflow-y: auto;">
            <form id="studioFormCustomizer" action="{{ route('admin.event-campaigns.form-customizer', $campaign->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="remove_logo" id="studioRemoveLogoInput" value="0">
                <input type="hidden" name="categories" id="studioCategoriesJsonInput" value="{{ json_encode($studioCategories) }}">

                <div class="accordion accordion-flush" id="studioAccordion">
                    
                    {{-- ১. লোগো ও এমব্লেম সেটিংস --}}
                    <div class="accordion-item rounded-3 mb-2.5 border shadow-2xs overflow-hidden">
                        <h2 class="accordion-header" id="headingLogo">
                            <button class="accordion-button fw-bold py-2.5 px-3 bg-white text-dark d-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLogo" aria-expanded="true" aria-controls="collapseLogo">
                                <i class="fa-solid fa-image text-primary"></i>
                                <span>১. লোগো ও এমব্লেম সেটিংস</span>
                                <span class="badge bg-primary-subtle text-primary border ms-auto font-monospace" id="studioBadgeLogoSize">{{ $studioLogoSize }}px</span>
                            </button>
                        </h2>
                        <div id="collapseLogo" class="accordion-collapse collapse show" aria-labelledby="headingLogo" data-bs-parent="#studioAccordion">
                            <div class="accordion-body bg-white p-3 pt-2">
                                
                                {{-- লোগো আপলোড ও থাম্বনেল --}}
                                <div class="d-flex align-items-center gap-3 mb-3 p-2 bg-light rounded-3 border">
                                    <div class="border rounded-2 p-1 bg-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 72px; height: 72px;">
                                        <img id="studioLogoThumb" src="{{ $studioFormLogo ?: '' }}" alt="Form Logo" style="{{ $studioFormLogo ? 'display: block;' : 'display: none;' }} max-width: 100%; max-height: 100%; object-fit: contain;">
                                        <div id="studioLogoPlaceholder" class="text-center text-muted" style="{{ $studioFormLogo ? 'display: none;' : 'display: block;' }}">
                                            <i class="fa-solid {{ $studioEmblemIcon }} fs-3 text-danger" id="studioPlaceholderIcon"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="form-label small fw-semibold text-dark mb-1">লোগো ফাইল নির্বাচন করুন</label>
                                        <input type="file" name="logo_image" id="studioLogoFileInput" class="form-control form-control-sm mb-1.5" accept="image/*" onchange="handleStudioLogoFile(this)">
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-outline-danger btn-sm py-0.5 px-2.5 rounded-pill small" onclick="removeStudioLogo()">
                                                <i class="fa-solid fa-trash-can me-1"></i> লোগো মুছুন
                                            </button>
                                            <span class="text-muted" style="font-size: 10.5px;">PNG, SVG, JPG, WebP</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- সাইজ স্লাইডার --}}
                                <div class="mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label small fw-semibold text-dark mb-0">লোগোর সাইজ (px)</label>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary py-0 px-2" onclick="adjustStudioLogoSize(-5)">-5</button>
                                            <button type="button" class="btn btn-outline-secondary py-0 px-2" onclick="setStudioLogoSize(70)">রিসেট (70)</button>
                                            <button type="button" class="btn btn-outline-secondary py-0 px-2" onclick="adjustStudioLogoSize(5)">+5</button>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="small text-muted font-monospace">30px</span>
                                        <input type="range" name="logo_size" id="studioLogoSizeSlider" class="form-range flex-grow-1" min="30" max="220" value="{{ $studioLogoSize }}" oninput="updateStudioLogoSize(this.value)">
                                        <span class="small text-muted font-monospace">220px</span>
                                    </div>
                                </div>

                                {{-- লোগো শেপ ও বর্ডার --}}
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">লোগোর আকৃতি</label>
                                        <select name="logo_shape" id="studioLogoShapeSelect" class="form-select form-select-sm" onchange="syncStudioLivePreview()">
                                            <option value="default" {{ $studioLogoShape == 'default' ? 'selected' : '' }}>ডিফল্ট (Aspect Fit)</option>
                                            <option value="circle" {{ $studioLogoShape == 'circle' ? 'selected' : '' }}>গোলাকার (Circle)</option>
                                            <option value="rounded" {{ $studioLogoShape == 'rounded' ? 'selected' : '' }}>রাউন্ডেড (Rounded 12px)</option>
                                            <option value="square" {{ $studioLogoShape == 'square' ? 'selected' : '' }}>চারকোনা (Square)</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">বর্ডার উইডথ (px)</label>
                                        <input type="number" name="logo_border_width" id="studioLogoBorderWidthInput" class="form-control form-control-sm" min="0" max="10" value="{{ $studioLogoBorderWidth }}" oninput="syncStudioLivePreview()">
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">বর্ডার কালার</label>
                                        <div class="studio-color-input-group">
                                            <input type="color" id="studioLogoBorderColorPicker" value="{{ $studioLogoBorderColor }}" oninput="syncStudioColor('studioLogoBorderColorPicker', 'studioLogoBorderColorInput')">
                                            <input type="text" name="logo_border_color" id="studioLogoBorderColorInput" class="form-control form-control-sm font-monospace" value="{{ $studioLogoBorderColor }}" oninput="syncStudioColor('studioLogoBorderColorInput', 'studioLogoBorderColorPicker')">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">ডিফল্ট এমব্লেম আইকন</label>
                                        <select name="emblem_icon" id="studioEmblemIconSelect" class="form-select form-select-sm" onchange="syncStudioLivePreview()">
                                            <option value="fa-feather-pointed" {{ $studioEmblemIcon == 'fa-feather-pointed' ? 'selected' : '' }}>কলম-পালক (Feather)</option>
                                            <option value="fa-book-open" {{ $studioEmblemIcon == 'fa-book-open' ? 'selected' : '' }}>খোলা বই (Book)</option>
                                            <option value="fa-award" {{ $studioEmblemIcon == 'fa-award' ? 'selected' : '' }}>অ্যাওয়ার্ড / মেডেল (Award)</option>
                                            <option value="fa-star" {{ $studioEmblemIcon == 'fa-star' ? 'selected' : '' }}>স্টার (Star)</option>
                                            <option value="fa-pen-nib" {{ $studioEmblemIcon == 'fa-pen-nib' ? 'selected' : '' }}>কলমের নিব (Pen Nib)</option>
                                            <option value="fa-scroll" {{ $studioEmblemIcon == 'fa-scroll' ? 'selected' : '' }}>স্ক্রোল / সনদ (Scroll)</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ২. হেডার প্যাড ও অফিশিয়াল টেক্সট --}}
                    <div class="accordion-item rounded-3 mb-2.5 border shadow-2xs overflow-hidden">
                        <h2 class="accordion-header" id="headingHeader">
                            <button class="accordion-button collapsed fw-bold py-2.5 px-3 bg-white text-dark d-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHeader" aria-expanded="false" aria-controls="collapseHeader">
                                <i class="fa-solid fa-heading text-success"></i>
                                <span>২. হেডার প্যাড ও অফিশিয়াল টেক্সট</span>
                            </button>
                        </h2>
                        <div id="collapseHeader" class="accordion-collapse collapse" aria-labelledby="headingHeader" data-bs-parent="#studioAccordion">
                            <div class="accordion-body bg-white p-3 pt-2">
                                
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold text-dark mb-1">মূল শিরোনাম (Campaign Title) <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="studioInputTitle" class="form-control form-control-sm fw-bold" value="{{ $studioTitle }}" required oninput="syncStudioLivePreview()">
                                </div>

                                <div class="mb-2">
                                    <label class="form-label small fw-semibold text-dark mb-1">সাব-শিরোনাম / বার্ষিকী লাইন (Sub-heading)</label>
                                    <input type="text" name="form_subhead" id="studioInputSubhead" class="form-control form-control-sm" value="{{ $studioSubhead }}" placeholder="২০ অক্টোবর ১৩তম প্রতিষ্ঠাবার্ষিকী উপলক্ষে" oninput="syncStudioLivePreview()">
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">স্থান / ভেন্যু (Form Venue)</label>
                                        <input type="text" name="form_venue" id="studioInputVenue" class="form-control form-control-sm" value="{{ $studioVenue }}" placeholder="স্থান: সরকারি টিচার্স ট্রেনিং কলেজ..." oninput="syncStudioLivePreview()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">তারিখ / সময় (Form Date)</label>
                                        <input type="text" name="form_date" id="studioInputDate" class="form-control form-control-sm" value="{{ $studioDate }}" placeholder="তারিখ: ১৩ নভেম্বর ২০২৬" oninput="syncStudioLivePreview()">
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label small fw-semibold text-dark mb-1">আয়োজক ও সহযোগিতা লাইন (Organization)</label>
                                    <input type="text" name="form_org" id="studioInputOrg" class="form-control form-control-sm" value="{{ $studioOrg }}" placeholder="নিবন্ধন সহযোগিতায়: আইডিয়া প্রকাশন | www.ideaabd.com" oninput="syncStudioLivePreview()">
                                </div>

                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">কপি স্ট্যাম্প ট্যাগ (Copy Tag)</label>
                                        <input type="text" name="form_copy_tag" id="studioInputCopyTag" class="form-control form-control-sm font-monospace text-uppercase" value="{{ $studioCopyTag }}" placeholder="DELEGATE COPY" oninput="syncStudioLivePreview()">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">ব্যাজ টেক্সট (Badge Text)</label>
                                        <input type="text" name="badge_text" id="studioInputBadge" class="form-control form-control-sm" value="{{ $campaign->badge_text }}" placeholder="লেখক ও প্রতিনিধি নিবন্ধন">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ৩. থিম কালার ও ফন্ট --}}
                    <div class="accordion-item rounded-3 mb-2.5 border shadow-2xs overflow-hidden">
                        <h2 class="accordion-header" id="headingColors">
                            <button class="accordion-button collapsed fw-bold py-2.5 px-3 bg-white text-dark d-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseColors" aria-expanded="false" aria-controls="collapseColors">
                                <i class="fa-solid fa-palette text-danger"></i>
                                <span>৩. থিম কালার ও ফন্ট</span>
                            </button>
                        </h2>
                        <div id="collapseColors" class="accordion-collapse collapse" aria-labelledby="headingColors" data-bs-parent="#studioAccordion">
                            <div class="accordion-body bg-white p-3 pt-2">
                                
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">থিম একসেন্ট কালার</label>
                                        <div class="studio-color-input-group">
                                            <input type="color" id="studioThemeColorPicker" value="{{ $studioThemeColor }}" oninput="syncStudioColor('studioThemeColorPicker', 'studioThemeColorInput')">
                                            <input type="text" name="theme_color" id="studioThemeColorInput" class="form-control form-control-sm font-monospace" value="{{ $studioThemeColor }}" oninput="syncStudioColor('studioThemeColorInput', 'studioThemeColorPicker')">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">বর্ডার ও গ্রিড কালার</label>
                                        <div class="studio-color-input-group">
                                            <input type="color" id="studioBorderColorPicker" value="{{ $studioBorderColor }}" oninput="syncStudioColor('studioBorderColorPicker', 'studioBorderColorInput')">
                                            <input type="text" name="form_border_color" id="studioBorderColorInput" class="form-control form-control-sm font-monospace" value="{{ $studioBorderColor }}" oninput="syncStudioColor('studioBorderColorInput', 'studioBorderColorPicker')">
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">লেবেল ব্যাকগ্রাউন্ড</label>
                                        <div class="studio-color-input-group">
                                            <input type="color" id="studioBgLabelPicker" value="{{ $studioBgLabel }}" oninput="syncStudioColor('studioBgLabelPicker', 'studioBgLabelInput')">
                                            <input type="text" name="form_bg_label" id="studioBgLabelInput" class="form-control form-control-sm font-monospace" value="{{ $studioBgLabel }}" oninput="syncStudioColor('studioBgLabelInput', 'studioBgLabelPicker')">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">ফন্ট ফ্যামিলি</label>
                                        <select name="font_family" id="studioFontFamilySelect" class="form-select form-select-sm" onchange="syncStudioLivePreview()">
                                            <option value="Hind Siliguri" {{ $studioFontFamily == 'Hind Siliguri' ? 'selected' : '' }}>হিন্দ শিলিগুড়ি (Hind Siliguri)</option>
                                            <option value="Noto Serif Bengali" {{ $studioFontFamily == 'Noto Serif Bengali' ? 'selected' : '' }}>নোটো সেরিফ বাংলা (Noto Serif)</option>
                                            <option value="Tiro Bangla" {{ $studioFontFamily == 'Tiro Bangla' ? 'selected' : '' }}>তিরো বাংলা (Tiro Bangla)</option>
                                            <option value="Kalpurush" {{ $studioFontFamily == 'Kalpurush' ? 'selected' : '' }}>কালপুরুষ (Kalpurush)</option>
                                            <option value="Inter" {{ $studioFontFamily == 'Inter' ? 'selected' : '' }}>ইন্টার (Inter)</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ৪. জরুরি নোটিশ ব্যানার --}}
                    <div class="accordion-item rounded-3 mb-2.5 border shadow-2xs overflow-hidden">
                        <h2 class="accordion-header" id="headingNotice">
                            <button class="accordion-button collapsed fw-bold py-2.5 px-3 bg-white text-dark d-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNotice" aria-expanded="false" aria-controls="collapseNotice">
                                <i class="fa-solid fa-bullhorn text-warning"></i>
                                <span>৪. জরুরি নোটিশ ও ঘোষণা ব্যানার</span>
                            </button>
                        </h2>
                        <div id="collapseNotice" class="accordion-collapse collapse" aria-labelledby="headingNotice" data-bs-parent="#studioAccordion">
                            <div class="accordion-body bg-white p-3 pt-2">
                                
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="form_notice_active" id="studioNoticeActiveCheck" value="1" {{ $studioNoticeActive ? 'checked' : '' }} onchange="syncStudioLivePreview()">
                                    <label class="form-check-label small fw-bold text-dark" for="studioNoticeActiveCheck">ফরমের শীর্ষে নোটিশ প্রদর্শন করুন</label>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label small fw-semibold text-dark mb-1">ব্যানার স্টাইল / কালার</label>
                                    <select name="form_notice_type" id="studioNoticeTypeSelect" class="form-select form-select-sm" onchange="syncStudioLivePreview()">
                                        <option value="info" {{ $studioNoticeType == 'info' ? 'selected' : '' }}>নীল (Information / Info)</option>
                                        <option value="warning" {{ $studioNoticeType == 'warning' ? 'selected' : '' }}>হলুদ (Warning / Alert)</option>
                                        <option value="danger" {{ $studioNoticeType == 'danger' ? 'selected' : '' }}>লাল (Urgent / Danger)</option>
                                        <option value="success" {{ $studioNoticeType == 'success' ? 'selected' : '' }}>সবুজ (Success / Confirmed)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label small fw-semibold text-dark mb-1">নোটিশ বার্তা (Notice Text)</label>
                                    <textarea name="form_notice_text" id="studioNoticeTextInput" rows="2" class="form-control form-control-sm" placeholder="জরুরি নির্দেশনা..." oninput="syncStudioLivePreview()">{{ $studioNoticeText }}</textarea>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ৫. ক্যাটাগরি ম্যানেজার --}}
                    <div class="accordion-item rounded-3 mb-2.5 border shadow-2xs overflow-hidden">
                        <h2 class="accordion-header" id="headingCategories">
                            <button class="accordion-button collapsed fw-bold py-2.5 px-3 bg-white text-dark d-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCategories" aria-expanded="false" aria-controls="collapseCategories">
                                <i class="fa-solid fa-tags text-info"></i>
                                <span>৫. লেখক ক্যাটাগরি ম্যানেজার</span>
                                <span class="badge bg-secondary ms-auto" id="studioCatCountBadge">{{ count($studioCategories) }}</span>
                            </button>
                        </h2>
                        <div id="collapseCategories" class="accordion-collapse collapse" aria-labelledby="headingCategories" data-bs-parent="#studioAccordion">
                            <div class="accordion-body bg-white p-3 pt-2">
                                <p class="text-muted small mb-2" style="font-size: 11.5px;">
                                    ফরমে লেখকরা কোন কোন ক্যাটাগরি বেছে নিতে পারবে তা যোগ বা মুছে নিয়ন্ত্রণ করুন:
                                </p>

                                <div class="cat-manager-wrap" id="studioCatBadgesList">
                                    {{-- JS dynamic tag chips render here --}}
                                </div>

                                <div class="input-group input-group-sm mb-2">
                                    <input type="text" id="studioNewCatInput" class="form-control" placeholder="নতুন ক্যাটাগরি লিখুন..." onkeypress="if(event.key==='Enter'){event.preventDefault();addStudioCategory();}">
                                    <button type="button" class="btn btn-dark fw-bold px-3" onclick="addStudioCategory()">
                                        <i class="fa-solid fa-plus me-1"></i> যোগ করুন
                                    </button>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-outline-secondary btn-sm py-0.5 px-2" style="font-size: 11px;" onclick="resetStudioCategories()">
                                        <i class="fa-solid fa-rotate-left me-1"></i> ডিফল্ট ক্যাটাগরি রিসেট
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ৬. ফিল্ডস ও ফিচার টগল --}}
                    <div class="accordion-item rounded-3 mb-2.5 border shadow-2xs overflow-hidden">
                        <h2 class="accordion-header" id="headingFields">
                            <button class="accordion-button collapsed fw-bold py-2.5 px-3 bg-white text-dark d-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFields" aria-expanded="false" aria-controls="collapseFields">
                                <i class="fa-solid fa-toggle-on text-primary"></i>
                                <span>৬. ফরম ফিল্ডস ও ফিচার টগল</span>
                            </button>
                        </h2>
                        <div id="collapseFields" class="accordion-collapse collapse" aria-labelledby="headingFields" data-bs-parent="#studioAccordion">
                            <div class="accordion-body bg-white p-3 pt-2">
                                
                                <div class="mb-2.5">
                                    <label class="form-label small fw-semibold text-dark mb-1">ছবি আপলোড শর্ত</label>
                                    <select name="photo_required" id="studioPhotoReqSelect" class="form-select form-select-sm" onchange="syncStudioLivePreview()">
                                        <option value="required" {{ $studioPhotoReq == 'required' ? 'selected' : '' }}>বাধ্যতামূলক (Mandatory)</option>
                                        <option value="optional" {{ $studioPhotoReq == 'optional' ? 'selected' : '' }}>ঐচ্ছিক (Optional)</option>
                                        <option value="hidden" {{ $studioPhotoReq == 'hidden' ? 'selected' : '' }}>লুকান / বন্ধ (Hidden)</option>
                                    </select>
                                </div>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="enable_intl_address" id="studioEnableIntlCheck" value="1" {{ $studioEnableIntl ? 'checked' : '' }} onchange="syncStudioLivePreview()">
                                    <label class="form-check-label small fw-semibold text-dark" for="studioEnableIntlCheck">প্রবাসী / আন্তর্জাতিক ঠিকানা অপশন</label>
                                </div>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="enable_magazine" id="studioEnableMagCheck" value="1" {{ $studioEnableMagazine ? 'checked' : '' }} onchange="syncStudioLivePreview()">
                                    <label class="form-check-label small fw-semibold text-dark" for="studioEnableMagCheck">লিটিলম্যাগাজিন ও পত্রিকার ফিল্ড</label>
                                </div>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="enable_literary_info" id="studioEnableLitCheck" value="1" {{ $studioEnableLiterary ? 'checked' : '' }} onchange="syncStudioLivePreview()">
                                    <label class="form-check-label small fw-semibold text-dark" for="studioEnableLitCheck">সাহিত্যকর্ম শাখা ও বইসংখ্যা ফিল্ড</label>
                                </div>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="enable_notable_books" id="studioEnableBooksCheck" value="1" {{ $studioEnableNotableBooks ? 'checked' : '' }} onchange="syncStudioLivePreview()">
                                    <label class="form-check-label small fw-semibold text-dark" for="studioEnableBooksCheck">উল্লেখযোগ্য প্রকাশিত গ্রন্থসমূহ ফিল্ড</label>
                                </div>

                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="enable_signature" id="studioEnableSigCheck" value="1" {{ $studioEnableSignature ? 'checked' : '' }} onchange="syncStudioLivePreview()">
                                    <label class="form-check-label small fw-semibold text-dark" for="studioEnableSigCheck">লেখকের স্বাক্ষর ব্লক</label>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ৭. অংশগ্রহণ ফি ও পেমেন্ট --}}
                    <div class="accordion-item rounded-3 mb-2.5 border shadow-2xs overflow-hidden">
                        <h2 class="accordion-header" id="headingPayment">
                            <button class="accordion-button collapsed fw-bold py-2.5 px-3 bg-white text-dark d-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePayment" aria-expanded="false" aria-controls="collapsePayment">
                                <i class="fa-solid fa-wallet text-success"></i>
                                <span>৭. অংশগ্রহণ ফি ও পেমেন্ট</span>
                            </button>
                        </h2>
                        <div id="collapsePayment" class="accordion-collapse collapse" aria-labelledby="headingPayment" data-bs-parent="#studioAccordion">
                            <div class="accordion-body bg-white p-3 pt-2">
                                
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="has_fee_or_donation" id="studioFeeActiveCheck" value="1" {{ $campaign->has_fee_or_donation ? 'checked' : '' }} onchange="syncStudioLivePreview()">
                                    <label class="form-check-label small fw-bold text-dark" for="studioFeeActiveCheck">অংশগ্রহণ ফি প্রযোজ্য (Paid Registration)</label>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label small fw-semibold text-dark mb-1">ফি এর পরিমাণ (টাকা / ৳)</label>
                                    <input type="number" name="fee_amount" id="studioFeeAmountInput" class="form-control form-control-sm font-monospace fw-bold" value="{{ $campaign->fee_amount ?: 0 }}" min="0" oninput="syncStudioLivePreview()">
                                </div>

                                <div>
                                    <label class="form-label small fw-semibold text-dark mb-1">পেমেন্ট নির্দেশনা (Instructions)</label>
                                    <textarea name="payment_instructions" id="studioPaymentInstructionsInput" rows="2" class="form-control form-control-sm" placeholder="যেমন: বিকাশ/নগদ মার্চেন্ট নম্বর...">{{ $campaign->payment_instructions }}</textarea>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ৮. নিয়ম ও অন্যান্য সেটিংস --}}
                    <div class="accordion-item rounded-3 border shadow-2xs overflow-hidden">
                        <h2 class="accordion-header" id="headingRules">
                            <button class="accordion-button collapsed fw-bold py-2.5 px-3 bg-white text-dark d-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRules" aria-expanded="false" aria-controls="collapseRules">
                                <i class="fa-solid fa-sliders text-secondary"></i>
                                <span>৮. নিয়ম ও অন্যান্য সেটিংস</span>
                            </button>
                        </h2>
                        <div id="collapseRules" class="accordion-collapse collapse" aria-labelledby="headingRules" data-bs-parent="#studioAccordion">
                            <div class="accordion-body bg-white p-3 pt-2">
                                
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="requires_approval" id="studioRequiresApprovalCheck" value="1" {{ $studioRequiresApproval ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold text-dark" for="studioRequiresApprovalCheck">অ্যাডমিন অনুমোদন প্রয়োজন (Require Approval)</label>
                                </div>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="studioIsActiveCheck" value="1" {{ $campaign->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold text-dark" for="studioIsActiveCheck">ফরম সক্রিয় রাখুন (Form Active)</label>
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">সর্বোচ্চ আবেদন সীমা</label>
                                        <input type="number" name="max_participants" id="studioMaxParticipantsInput" class="form-control form-control-sm" placeholder="Unlimited" value="{{ $campaign->max_participants }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">সমাপ্তির সময় (Deadline)</label>
                                        <input type="datetime-local" name="ends_at" id="studioEndsAtInput" class="form-control form-control-sm" value="{{ $campaign->ends_at ? $campaign->ends_at->format('Y-m-d\TH:i') : '' }}">
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label small fw-semibold text-dark mb-1">সফল সাবমিশন বার্তা (Success Message)</label>
                                    <textarea name="success_message" id="studioSuccessMsgInput" rows="2" class="form-control form-control-sm" placeholder="আপনার লেখক নিবন্ধন সফলভাবে জমা হয়েছে...">{{ $campaign->success_message }}</textarea>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-4 pt-3 border-top d-flex align-items-center justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill btn-sm px-3" onclick="resetStudioDefaults()">
                        <i class="fa-solid fa-rotate-left me-1"></i> রিসেট
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm" id="btnStudioSaveSubmit" onclick="saveFormStudioAjax()">
                        <i class="fa-solid fa-floppy-disk me-1.5"></i> পরিবর্তনগুলো সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>

        {{-- ডান পাশের লাইভ ফর্ম প্রিভিউ --}}
        <div class="col-lg-7 p-3 p-xl-4 form-studio-preview-pane">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fa-solid fa-eye text-primary"></i> লাইভ ফর্ম প্রিভিউ (Live Preview: /{{ $campaign->slug }})
                </div>
                <span class="badge bg-success text-white px-2.5 py-1 small fw-semibold">
                    <i class="fa-solid fa-bolt me-1"></i> রিয়েল-টাইম ইন্টারেক্টিভ
                </span>
            </div>

            {{-- পেপার প্রিভিউ ফ্রেম --}}
            <div class="studio-live-paper" id="studioPaperWrap" style="--preview-border: {{ $studioBorderColor }}; --preview-accent: {{ $studioThemeColor }}; --preview-bg-label: {{ $studioBgLabel }}; --preview-font: '{{ $studioFontFamily }}';">
                
                {{-- প্যাড হেডার --}}
                <div class="d-flex align-items-center justify-content-between gap-3 pb-2 mb-2 border-bottom" style="border-bottom-color: var(--preview-border) !important;">
                    
                    {{-- লোগো প্রিভিউ --}}
                    <div id="prevLogoContainer" class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: {{ $studioLogoSize }}px; height: {{ $studioLogoSize }}px; border-radius: {{ $studioLogoShape === 'circle' ? '50%' : ($studioLogoShape === 'rounded' ? '12px' : '0px') }}; border: {{ $studioLogoBorderWidth }}px solid {{ $studioLogoBorderColor }}; overflow: hidden; transition: all 0.15s ease;">
                        <img id="prevLogoImg" src="{{ $studioFormLogo ?: '' }}" alt="Logo" style="{{ $studioFormLogo ? 'display: block;' : 'display: none;' }} width: 100%; height: 100%; object-fit: contain;">
                        <div id="prevEmblemIcon" class="text-danger d-flex align-items-center justify-content-center" style="{{ $studioFormLogo ? 'display: none;' : 'display: flex;' }} width: 100%; height: 100%; font-size: {{ round($studioLogoSize * 0.55) }}px;">
                            <i class="fa-solid {{ $studioEmblemIcon }}" id="prevEmblemIconTag"></i>
                        </div>
                    </div>

                    {{-- টেক্সট প্রিভিউ --}}
                    <div class="text-center flex-grow-1 px-2">
                        <div class="fw-bolder text-dark" id="prevPadTitle" style="font-size: 18px; line-height: 1.25; font-family: 'Noto Serif Bengali', serif;">
                            {{ $campaign->title }}
                        </div>
                        <div class="fw-bold mt-0.5" id="prevPadSubhead" style="font-size: 12px; color: #475569; {{ empty($studioSubhead) ? 'display: none;' : '' }}">
                            {{ $studioSubhead }}
                        </div>
                        <div class="fw-bold mt-0.5" id="prevPadVenue" style="font-size: 11.5px; color: #0f172a;">
                            {{ $studioVenue }}
                        </div>
                        <div class="fw-bold mt-0.5" id="prevPadDate" style="font-size: 12px; color: var(--preview-accent);">
                            {{ $studioDate }}
                        </div>
                        <div class="mt-0.5 text-muted" id="prevPadOrg" style="font-size: 10px;">
                            {{ $studioOrg }}
                        </div>
                    </div>

                    {{-- কপি স্ট্যাম্প --}}
                    <div class="text-end flex-shrink-0">
                        <span class="badge bg-dark text-white px-2 py-1 font-monospace" id="prevPadCopyTag" style="font-size: 9.5px;">
                            {{ $studioCopyTag }}
                        </span>
                        <div class="mt-1 font-monospace text-dark fw-bold" style="font-size: 9px;">
                            #RSU-PREVIEW
                        </div>
                    </div>
                </div>

                {{-- নোটিশ ব্যানার --}}
                <div id="prevNoticeAlert" class="alert alert-{{ $studioNoticeType }} py-2 px-3 mb-2 small d-flex align-items-center gap-2 rounded-2 border" style="{{ $studioNoticeActive ? 'display: flex;' : 'display: none;' }}">
                    <i class="fa-solid fa-circle-exclamation fs-5 flex-shrink-0"></i>
                    <div id="prevNoticeText" class="fw-semibold">{{ $studioNoticeText }}</div>
                </div>

                {{-- টেবিল প্রিভিউ ১: পরিচিতি --}}
                <table>
                    <colgroup>
                        <col style="width: 20%;">
                        <col style="width: 30%;">
                        <col style="width: 20%;">
                        <col style="width: 30%;">
                    </colgroup>
                    <tr>
                        <td class="grid-section-header" colspan="4">১. পরিচিতি</td>
                    </tr>
                    <tr>
                        <td class="c-label">নাম *</td>
                        <td colspan="2" class="c-val">
                            <span class="text-uppercase fw-bold text-dark small">সাকিল আহমেদ</span>
                        </td>
                        <td rowspan="4" class="text-center" id="prevPhotoCell" style="vertical-align: middle; background: #fafafa; {{ $studioPhotoReq === 'hidden' ? 'display: none;' : '' }}">
                            <div class="border rounded p-2 text-center" style="width: 90px; height: 105px; margin: 0 auto; background: #f8fafc; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-camera text-primary fs-4 mb-1"></i>
                                <div style="font-size: 10px; font-weight: 700;">ছবি আপলোড</div>
                                <div id="prevPhotoBadge" style="font-size: 8.5px; color: {{ $studioPhotoReq === 'required' ? '#dc2626' : '#64748b' }}; font-weight: 600;">
                                    ({{ $studioPhotoReq === 'required' ? 'বাধ্যতামূলক' : 'ঐচ্ছিক' }})
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="c-label">ক্যাটাগরি *</td>
                        <td colspan="2" class="c-val">
                            <div class="d-flex flex-wrap gap-1" id="prevCategoriesWrap">
                                @foreach($studioCategories as $idx => $cat)
                                    <span class="cat-pill {{ $idx === 0 ? 'active' : '' }}"><i class="fa-regular {{ $idx === 0 ? 'fa-circle-dot text-danger' : 'fa-circle text-muted' }} me-1 small"></i> {{ $cat }}</span>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="c-label">মোবাইল *</td>
                        <td colspan="2" class="c-val font-monospace fw-bold text-dark small">
                            <span class="badge bg-dark text-white me-1">🇧🇩 +880</span> 01712345678
                        </td>
                    </tr>
                    <tr>
                        <td class="c-label">ইমেইল</td>
                        <td colspan="2" class="c-val text-muted small">author@example.com</td>
                    </tr>

                    {{-- ডায়নামিক ফিল্ড সারি --}}
                    <tr id="prevMagRow" style="{{ $studioEnableMagazine ? '' : 'display: none;' }}">
                        <td class="c-label">ছোটকাগজ</td>
                        <td class="c-val text-muted small">লিটিলম্যাগাজিন নাম</td>
                        <td class="c-label">সংখ্যা</td>
                        <td class="c-val text-muted small">৫টি সংখ্যা</td>
                    </tr>
                    <tr id="prevLitRow" style="{{ $studioEnableLiterary ? '' : 'display: none;' }}">
                        <td class="c-label">শাখা / মাধ্যম</td>
                        <td class="c-val text-muted small">কবিতা ও কথাসাহিত্য</td>
                        <td class="c-label">বইসংখ্যা</td>
                        <td class="c-val text-muted small">২-৫টি গ্রন্থ</td>
                    </tr>
                    <tr id="prevBooksRow" style="{{ $studioEnableNotableBooks ? '' : 'display: none;' }}">
                        <td class="c-label">গ্রন্থসমূহ</td>
                        <td colspan="3" class="c-val text-muted small">উল্লেখযোগ্য প্রকাশিত বইয়ের নাম</td>
                    </tr>
                </table>

                {{-- ঠিকানা হেডার ও সুইচার --}}
                <div class="d-flex align-items-center justify-content-between my-2">
                    <span class="fw-bold small"><i class="fa-solid fa-location-dot me-1 text-danger"></i> ২. ঠিকানা</span>
                    <div id="prevIntlToggle" class="d-flex align-items-center gap-1 border rounded p-1" style="{{ $studioEnableIntl ? '' : 'display: none;' }}">
                        <span class="badge bg-dark text-white px-2 py-0.5" style="font-size: 10px;">বাংলাদেশ</span>
                        <span class="badge bg-light text-dark px-2 py-0.5" style="font-size: 10px;">প্রবাসী / বিদেশী</span>
                    </div>
                </div>

                {{-- টেবিল প্রিভিউ ২: ঠিকানা --}}
                <table>
                    <tr>
                        <td class="c-label">বিভাগ *</td>
                        <td class="c-val text-muted small">রংপুর</td>
                        <td class="c-label">জেলা *</td>
                        <td class="c-val text-muted small">রংপুর</td>
                    </tr>
                    <tr>
                        <td class="c-label">উপজেলা *</td>
                        <td class="c-val text-muted small">রংপুর সদর</td>
                        <td class="c-label">ডাকঘর</td>
                        <td class="c-val text-muted small">রংপুর জিপিও</td>
                    </tr>
                    <tr>
                        <td class="c-label">ঠিকানা *</td>
                        <td colspan="3" class="c-val text-muted small">রোড নং ৪, সেনপাড়া, রংপুর</td>
                    </tr>
                </table>

                {{-- টেবিল প্রিভিউ ৩: পেমেন্ট (যদি ফি চালু থাকে) --}}
                <table id="prevPaymentTable" style="{{ $campaign->has_fee_or_donation ? '' : 'display: none;' }}">
                    <tr>
                        <td class="grid-section-header" colspan="4">৩. পেমেন্ট বিবরণী</td>
                    </tr>
                    <tr>
                        <td class="c-label">ফি</td>
                        <td class="c-val font-monospace fw-bold text-danger">
                            ৳<span id="prevFeeDisplay">{{ number_format($campaign->fee_amount ?: 0) }}</span>
                        </td>
                        <td class="c-label">ট্রানজেকশন *</td>
                        <td class="c-val text-muted small font-monospace">TrxID: 9X8Y7Z6W</td>
                    </tr>
                </table>

                {{-- লেখকের স্বাক্ষর --}}
                <div id="prevSigWrap" style="display: flex; justify-content: flex-end; margin-top: 25px; margin-bottom: 15px; {{ $studioEnableSignature ? '' : 'display: none;' }}">
                    <div style="text-align: center; min-width: 170px;">
                        <div class="text-uppercase fw-bold font-monospace small" style="color: #0f172a; min-height: 16px;">সাকিল আহমেদ</div>
                        <div style="border-top: 1.5px solid var(--preview-border); padding-top: 3px; font-size: 11px; font-weight: 700;">লেখকের স্বাক্ষর</div>
                    </div>
                </div>

                {{-- সাবমিট বাটন সিমুলেশন --}}
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-dark btn-sm rounded px-4 fw-bold" style="background: var(--preview-border); border-color: var(--preview-border);">
                        <i class="fa-solid fa-paper-plane me-1"></i> জমা দিন
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
// Studio Initial State
let studioActiveCats = @json($studioCategories);
const defaultStudioCats = @json($defaultCatsList);
const studioCustomizerSaveUrl = "{{ route('admin.event-campaigns.form-customizer', $campaign->id) }}";

function renderStudioCategoriesManager() {
    const container = document.getElementById('studioCatBadgesList');
    const previewWrap = document.getElementById('prevCategoriesWrap');
    const hiddenInput = document.getElementById('studioCategoriesJsonInput');
    const countBadge = document.getElementById('studioCatCountBadge');

    if (!container) return;

    container.innerHTML = '';
    if (previewWrap) previewWrap.innerHTML = '';

    studioActiveCats.forEach((cat, idx) => {
        // Manager badge
        const badge = document.createElement('span');
        badge.className = 'cat-manager-badge';
        badge.innerHTML = `<span>${cat}</span><button type="button" class="btn-del-cat" onclick="removeStudioCategory(${idx})" title="মুছুন">&times;</button>`;
        container.appendChild(badge);

        // Preview badge
        if (previewWrap) {
            const pill = document.createElement('span');
            pill.className = 'cat-pill' + (idx === 0 ? ' active' : '');
            pill.innerHTML = `<i class="fa-regular ${idx === 0 ? 'fa-circle-dot text-danger' : 'fa-circle text-muted'} me-1 small"></i> ${cat}`;
            previewWrap.appendChild(pill);
        }
    });

    if (hiddenInput) hiddenInput.value = JSON.stringify(studioActiveCats);
    if (countBadge) countBadge.textContent = studioActiveCats.length;
}

function addStudioCategory() {
    const input = document.getElementById('studioNewCatInput');
    if (!input) return;
    const val = input.value.trim();
    if (!val) return;

    if (!studioActiveCats.includes(val)) {
        studioActiveCats.push(val);
        renderStudioCategoriesManager();
    }
    input.value = '';
    input.focus();
}

function removeStudioCategory(idx) {
    if (idx >= 0 && idx < studioActiveCats.length) {
        studioActiveCats.splice(idx, 1);
        renderStudioCategoriesManager();
    }
}

function resetStudioCategories() {
    studioActiveCats = [...defaultStudioCats];
    renderStudioCategoriesManager();
}

function updateStudioLogoSize(val) {
    const num = Math.max(30, Math.min(220, parseInt(val) || 70));
    const badge = document.getElementById('studioBadgeLogoSize');
    if (badge) badge.textContent = num + 'px';
    syncStudioLivePreview();
}

function adjustStudioLogoSize(delta) {
    const slider = document.getElementById('studioLogoSizeSlider');
    if (!slider) return;
    const cur = parseInt(slider.value) || 70;
    slider.value = Math.max(30, Math.min(220, cur + delta));
    updateStudioLogoSize(slider.value);
}

function setStudioLogoSize(val) {
    const slider = document.getElementById('studioLogoSizeSlider');
    if (!slider) return;
    slider.value = val;
    updateStudioLogoSize(val);
}

function handleStudioLogoFile(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    const reader = new FileReader();
    reader.onload = function(e) {
        const thumb = document.getElementById('studioLogoThumb');
        const placeholder = document.getElementById('studioLogoPlaceholder');
        const prevImg = document.getElementById('prevLogoImg');
        const prevIcon = document.getElementById('prevEmblemIcon');

        if (thumb) { thumb.src = e.target.result; thumb.style.display = 'block'; }
        if (placeholder) placeholder.style.display = 'none';
        if (prevImg) { prevImg.src = e.target.result; prevImg.style.display = 'block'; }
        if (prevIcon) prevIcon.style.display = 'none';

        const rmInput = document.getElementById('studioRemoveLogoInput');
        if (rmInput) rmInput.value = '0';
    };
    reader.readAsDataURL(file);
}

function removeStudioLogo() {
    const thumb = document.getElementById('studioLogoThumb');
    const placeholder = document.getElementById('studioLogoPlaceholder');
    const prevImg = document.getElementById('prevLogoImg');
    const prevIcon = document.getElementById('prevEmblemIcon');
    const fileInput = document.getElementById('studioLogoFileInput');
    const rmInput = document.getElementById('studioRemoveLogoInput');

    if (thumb) { thumb.src = ''; thumb.style.display = 'none'; }
    if (placeholder) placeholder.style.display = 'block';
    if (prevImg) { prevImg.src = ''; prevImg.style.display = 'none'; }
    if (prevIcon) prevIcon.style.display = 'flex';
    if (fileInput) fileInput.value = '';
    if (rmInput) rmInput.value = '1';
}

function syncStudioColor(sourceId, targetId) {
    const source = document.getElementById(sourceId);
    const target = document.getElementById(targetId);
    if (source && target) {
        target.value = source.value;
    }
    syncStudioLivePreview();
}

function syncStudioLivePreview() {
    const paper = document.getElementById('studioPaperWrap');
    if (!paper) return;

    // 1. Texts
    const titleVal = document.getElementById('studioInputTitle')?.value || '';
    const subheadVal = document.getElementById('studioInputSubhead')?.value || '';
    const venueVal = document.getElementById('studioInputVenue')?.value || '';
    const dateVal = document.getElementById('studioInputDate')?.value || '';
    const orgVal = document.getElementById('studioInputOrg')?.value || '';
    const copyTagVal = document.getElementById('studioInputCopyTag')?.value || '';

    const pTitle = document.getElementById('prevPadTitle');
    const pSubhead = document.getElementById('prevPadSubhead');
    const pVenue = document.getElementById('prevPadVenue');
    const pDate = document.getElementById('prevPadDate');
    const pOrg = document.getElementById('prevPadOrg');
    const pCopyTag = document.getElementById('prevPadCopyTag');

    if (pTitle) pTitle.textContent = titleVal;
    if (pSubhead) {
        pSubhead.textContent = subheadVal;
        pSubhead.style.display = subheadVal.trim() ? 'block' : 'none';
    }
    if (pVenue) pVenue.textContent = venueVal;
    if (pDate) pDate.textContent = dateVal;
    if (pOrg) pOrg.textContent = orgVal;
    if (pCopyTag) pCopyTag.textContent = copyTagVal;

    // 2. Colors & CSS Variables
    const themeCol = document.getElementById('studioThemeColorInput')?.value || '#991b1b';
    const borderCol = document.getElementById('studioBorderColorInput')?.value || '#0f172a';
    const bgLabelCol = document.getElementById('studioBgLabelInput')?.value || '#f8fafc';
    const fontFam = document.getElementById('studioFontFamilySelect')?.value || 'Hind Siliguri';

    paper.style.setProperty('--preview-accent', themeCol);
    paper.style.setProperty('--preview-border', borderCol);
    paper.style.setProperty('--preview-bg-label', bgLabelCol);
    paper.style.setProperty('--preview-font', fontFam);

    // 3. Logo Container
    const logoSize = parseInt(document.getElementById('studioLogoSizeSlider')?.value) || 70;
    const logoShape = document.getElementById('studioLogoShapeSelect')?.value || 'default';
    const logoBw = parseInt(document.getElementById('studioLogoBorderWidthInput')?.value) || 0;
    const logoBc = document.getElementById('studioLogoBorderColorInput')?.value || '#0f172a';
    const emblemIcon = document.getElementById('studioEmblemIconSelect')?.value || 'fa-feather-pointed';

    const pLogoCont = document.getElementById('prevLogoContainer');
    if (pLogoCont) {
        pLogoCont.style.width = logoSize + 'px';
        pLogoCont.style.height = logoSize + 'px';
        pLogoCont.style.borderRadius = logoShape === 'circle' ? '50%' : (logoShape === 'rounded' ? '12px' : '0px');
        pLogoCont.style.border = logoBw > 0 ? `${logoBw}px solid ${logoBc}` : 'none';
    }

    const pIcon = document.getElementById('prevEmblemIcon');
    const pIconTag = document.getElementById('prevEmblemIconTag');
    if (pIcon) pIcon.style.fontSize = Math.round(logoSize * 0.55) + 'px';
    if (pIconTag) pIconTag.className = 'fa-solid ' + emblemIcon;

    // 4. Notice Banner
    const noticeActive = document.getElementById('studioNoticeActiveCheck')?.checked;
    const noticeType = document.getElementById('studioNoticeTypeSelect')?.value || 'info';
    const noticeText = document.getElementById('studioNoticeTextInput')?.value || '';
    const pNoticeAlert = document.getElementById('prevNoticeAlert');
    const pNoticeText = document.getElementById('prevNoticeText');

    if (pNoticeAlert) {
        pNoticeAlert.style.display = noticeActive && noticeText.trim() ? 'flex' : 'none';
        pNoticeAlert.className = `alert alert-${noticeType} py-2 px-3 mb-2 small d-flex align-items-center gap-2 rounded-2 border`;
    }
    if (pNoticeText) pNoticeText.textContent = noticeText;

    // 5. Fields & Toggles
    const photoReq = document.getElementById('studioPhotoReqSelect')?.value || 'required';
    const pPhotoCell = document.getElementById('prevPhotoCell');
    const pPhotoBadge = document.getElementById('prevPhotoBadge');
    if (pPhotoCell) pPhotoCell.style.display = photoReq === 'hidden' ? 'none' : 'table-cell';
    if (pPhotoBadge) {
        pPhotoBadge.textContent = photoReq === 'required' ? '(বাধ্যতামূলক)' : '(ঐচ্ছিক)';
        pPhotoBadge.style.color = photoReq === 'required' ? '#dc2626' : '#64748b';
    }

    const enIntl = document.getElementById('studioEnableIntlCheck')?.checked;
    const pIntlToggle = document.getElementById('prevIntlToggle');
    if (pIntlToggle) pIntlToggle.style.display = enIntl ? 'flex' : 'none';

    const enMag = document.getElementById('studioEnableMagCheck')?.checked;
    const pMagRow = document.getElementById('prevMagRow');
    if (pMagRow) pMagRow.style.display = enMag ? 'table-row' : 'none';

    const enLit = document.getElementById('studioEnableLitCheck')?.checked;
    const pLitRow = document.getElementById('prevLitRow');
    if (pLitRow) pLitRow.style.display = enLit ? 'table-row' : 'none';

    const enBooks = document.getElementById('studioEnableBooksCheck')?.checked;
    const pBooksRow = document.getElementById('prevBooksRow');
    if (pBooksRow) pBooksRow.style.display = enBooks ? 'table-row' : 'none';

    const enSig = document.getElementById('studioEnableSigCheck')?.checked;
    const pSigWrap = document.getElementById('prevSigWrap');
    if (pSigWrap) pSigWrap.style.display = enSig ? 'flex' : 'none';

    // 6. Payment Table
    const feeActive = document.getElementById('studioFeeActiveCheck')?.checked;
    const feeAmount = document.getElementById('studioFeeAmountInput')?.value || 0;
    const pPayTable = document.getElementById('prevPaymentTable');
    const pFeeDisp = document.getElementById('prevFeeDisplay');

    if (pPayTable) pPayTable.style.display = feeActive ? 'table' : 'none';
    if (pFeeDisp) pFeeDisp.textContent = parseInt(feeAmount).toLocaleString();
}

function saveFormStudioAjax() {
    const form = document.getElementById('studioFormCustomizer');
    const btn = document.getElementById('btnStudioSaveSubmit');
    const topBtn = document.getElementById('btnStudioTopSave');

    if (!form) return;

    const origText = btn ? btn.innerHTML : '';
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5"></span> সংরক্ষণ হচ্ছে...'; }
    if (topBtn) { topBtn.disabled = true; topBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> সেভ হচ্ছে...'; }

    // Make sure JSON categories is current
    document.getElementById('studioCategoriesJsonInput').value = JSON.stringify(studioActiveCats);

    const formData = new FormData(form);

    fetch(studioCustomizerSaveUrl, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (btn) { btn.disabled = false; btn.innerHTML = origText; }
        if (topBtn) { topBtn.disabled = false; topBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> সংরক্ষণ করুন'; }

        if (data.success) {
            // Sync on-page hero title & badge
            if (data.title) {
                const ht = document.getElementById('headerTitle');
                if (ht) ht.textContent = data.title;
                const bt = document.getElementById('breadcrumbTitle');
                if (bt) bt.textContent = data.title;
            }
            if (data.badge_text) {
                const hb = document.getElementById('headerBadge');
                if (hb) hb.innerHTML = '<i class="fa-solid fa-tag"></i> ' + data.badge_text;
            }
            // Hero logo
            const heroImg = document.getElementById('heroFormLogoImg');
            const heroIcon = document.getElementById('heroFormLogoIcon');
            if (heroImg && heroIcon) {
                if (data.logo_url) {
                    heroImg.src = data.logo_url;
                    heroImg.style.display = 'block';
                    heroIcon.style.display = 'none';
                } else if (document.getElementById('studioRemoveLogoInput').value === '1') {
                    heroImg.src = '';
                    heroImg.style.display = 'none';
                    heroIcon.style.display = 'block';
                }
            }
            // Hero Venue & Date
            const hv = document.getElementById('heroVenueDisplay');
            if (hv && data.form_venue) hv.textContent = data.form_venue;
            const hd = document.getElementById('heroDateDisplay');
            if (hd && data.form_date) hd.textContent = data.form_date;

            // Reset remove_logo flag
            document.getElementById('studioRemoveLogoInput').value = '0';

            // Show Toast
            if (window.showToast) {
                window.showToast('<i class="fa-solid fa-circle-check text-success me-1"></i> ' + (data.message || 'কাস্টমাইজেশন সংরক্ষিত হয়েছে!'));
            } else {
                alert(data.message || 'রেজিস্ট্রেশন ফরম ও লোগো কাস্টমাইজেশন সফলভাবে সংরক্ষিত হয়েছে!');
            }
        } else {
            alert(data.message || 'সংরক্ষণ ব্যর্থ হয়েছে।');
        }
    })
    .catch(err => {
        if (btn) { btn.disabled = false; btn.innerHTML = origText; }
        if (topBtn) { topBtn.disabled = false; topBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> সংরক্ষণ করুন'; }
        console.error(err);
        form.submit();
    });
}

function resetStudioDefaults() {
    if (!confirm('আপনি কি ডিফল্ট সেটিংসে ফিরিয়ে আনতে চান?')) return;
    document.getElementById('studioFormCustomizer')?.reset();
    resetStudioCategories();
    syncStudioLivePreview();
}

document.addEventListener('DOMContentLoaded', function() {
    renderStudioCategoriesManager();
    syncStudioLivePreview();
});
</script>
