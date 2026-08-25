@extends('author.layout')

@section('title', 'নতুন আইডিয়াপত্র লিখুন — লেখক পোর্টাল')

@section('content')
<div class="container-fluid p-0">

    {{-- Top Header Section --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <i class="fas fa-pen-nib text-warning"></i>
                <span>নতুন আইডিয়াপত্র রচনা ও প্রকাশ</span>
            </h4>
            <p class="text-muted small mb-0">সাহিত্য, কবিতা, গল্প, প্রবন্ধ বা কলাম লিখে সরাসরি প্রকাশনার জন্য জমা দিন</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('author.posts.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> আমার লেখাগুলো
            </a>
            <a href="{{ route('blog.index') }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="fas fa-eye me-1"></i> আইডিয়াপত্র ফিড
            </a>
        </div>
    </div>

    {{-- Form Submission Card --}}
    <form action="{{ route('author.posts.store') }}" method="POST" enctype="multipart/form-data" id="authorPostForm">
        @csrf

        <div class="row g-4">
            {{-- Left Column: Main Editor Fields --}}
            <div class="col-12 col-lg-8">
                <div class="author-card p-3 p-md-4 mb-4">
                    {{-- Title --}}
                    <div class="mb-3">
                        <label for="postTitle" class="form-label fw-bold text-dark mb-1">
                            লেখার মূল শিরোনাম <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="title" id="postTitle" 
                               value="{{ old('title') }}" 
                               class="form-control form-control-lg fw-bold @error('title') is-invalid @enderror" 
                               placeholder="আকর্ষণীয় শিরোনাম লিখুন..." required 
                               oninput="onAuthorTitleChange(this.value)">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Subtitle & Category Row --}}
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-7">
                            <label for="postSubtitle" class="form-label small fw-semibold text-dark mb-1">উপশিরোনাম / ট্যাগলাইন (ঐচ্ছিক)</label>
                            <input type="text" name="subtitle" id="postSubtitle" 
                                   value="{{ old('subtitle') }}" 
                                   class="form-control form-control-sm @error('subtitle') is-invalid @enderror" 
                                   placeholder="এক লাইনে লেখার মূল সুর বা সারমর্ম...">
                            @error('subtitle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-5">
                            <label for="postCategory" class="form-label small fw-semibold text-dark mb-1">ক্যাটাগরি / সাহিত্য ধারা <span class="text-danger">*</span></label>
                            <select name="category_id" id="postCategory" class="form-select form-select-sm @error('category_id') is-invalid @enderror" required>
                                <option value="">— ক্যাটাগরি নির্বাচন করুন —</option>
                                @foreach($blogCategories as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Excerpt / Short Summary --}}
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label for="postExcerpt" class="form-label small fw-semibold text-dark mb-0">সংক্ষিপ্ত ভূমিকা / ফেস্টিভাল টিজার (ঐচ্ছিক)</label>
                            <span class="text-muted small" style="font-size: 11px;" id="excerptCounter">০ / ২০০ অক্ষর</span>
                        </div>
                        <textarea name="excerpt" id="postExcerpt" rows="2" 
                                  class="form-control form-control-sm @error('excerpt') is-invalid @enderror" 
                                  placeholder="পাঠকদের আকৃষ্ট করার জন্য ১-২ বাক্যের সংক্ষিপ্ত বিবরণ..." 
                                  maxlength="500"
                                  oninput="updateExcerptCount(this)">{{ old('excerpt') }}</textarea>
                        @error('excerpt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Rich Text Content Editor --}}
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label fw-bold text-dark mb-0">
                                মূল রচনা / পান্ডুলিপি <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark border small fw-normal" id="contentWordStats">শব্দ: ০ | স্তবক: ০</span>
                            </div>
                        </div>

                        <div class="border rounded-3 overflow-hidden shadow-xs">
                            {{-- Formatting Toolbar --}}
                            <div class="bg-light p-2 border-bottom d-flex flex-wrap gap-1 align-items-center">
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 fw-bold" onclick="execCmd('bold')" title="বোল্ড (Ctrl+B)">
                                    <i class="fas fa-bold"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 fst-italic" onclick="execCmd('italic')" title="ইটালিক (Ctrl+I)">
                                    <i class="fas fa-italic"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 text-decoration-underline" onclick="execCmd('underline')" title="আন্ডারলাইন (Ctrl+U)">
                                    <i class="fas fa-underline"></i>
                                </button>

                                <div class="vr mx-1"></div>

                                <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="execFormatBlock('p')" title="প্যারাগ্রাফ">
                                    <i class="fas fa-paragraph"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2 fw-bold" onclick="execFormatBlock('h3')" title="উপশিরোনাম (H3)">
                                    H3
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="execFormatBlock('blockquote')" title="উদ্ধৃতি / কোট">
                                    <i class="fas fa-quote-left"></i>
                                </button>

                                <div class="vr mx-1"></div>

                                <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="execCmd('insertUnorderedList')" title="বুলেট লিস্ট">
                                    <i class="fas fa-list-ul"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="execCmd('insertOrderedList')" title="নাম্বার লিস্ট">
                                    <i class="fas fa-list-ol"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="execCmd('insertHorizontalRule')" title="বিভাজক রেখা">
                                    <i class="fas fa-minus"></i>
                                </button>

                                <div class="vr mx-1"></div>

                                {{-- Literary Helpers --}}
                                <button type="button" class="btn btn-sm btn-outline-primary border py-1 px-2.5 fw-semibold" onclick="formatPoetryMode()" title="কবিতার চরণ ও স্তবক ঠিক করুন">
                                    <i class="fas fa-feather-alt me-1"></i> কবিতা মোড
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary border py-1 px-2.5 fw-semibold" onclick="formatProseMode()" title="গদ্য প্যারাগ্রাফ সাজান">
                                    <i class="fas fa-align-left me-1"></i> গদ্য মোড
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info border py-1 px-2.5 fw-semibold" onclick="formatCleanSpacing()" title="অতিরিক্ত ফাঁকা লাইন মুছুন">
                                    <i class="fas fa-wand-magic-sparkles me-1"></i> স্পেসিং মেরামত
                                </button>

                                <div class="vr mx-1"></div>

                                <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-danger" onclick="execCmd('removeFormat')" title="ফরম্যাটিং মুছুন">
                                    <i class="fas fa-eraser"></i>
                                </button>
                            </div>

                            {{-- Editable Area --}}
                            <div id="authorContentEditable" contenteditable="true" 
                                 class="p-3 bg-white text-dark rich-editor-content" 
                                 style="min-height: 420px; max-height: 700px; overflow-y: auto; outline: none; font-size: 16.5px; line-height: 1.85; font-family: 'Hind Siliguri', 'SolaimanLipi', sans-serif;"
                                 oninput="syncEditorContent()">{!! old('content') !!}</div>

                            {{-- Hidden Form Textarea --}}
                            <textarea id="hiddenContent" name="content" class="d-none @error('content') is-invalid @enderror" required>{!! old('content') !!}</textarea>
                        </div>
                        @error('content')
                            <div class="text-danger small mt-1 fw-semibold">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Right Column: Featured Image, AI Photocard, Guidelines & Actions --}}
            <div class="col-12 col-lg-4">
                {{-- Cover Image & AI Photocard Card --}}
                <div class="author-card p-3 mb-4">
                    <h6 class="fw-bold mb-2 text-dark d-flex align-items-center gap-1.5">
                        <i class="fas fa-image text-primary"></i>
                        <span>পোস্ট কাভার / ফটোকার্ড</span>
                    </h6>
                    <p class="text-muted small mb-3" style="font-size: 12px;">
                        আপনার পোস্টের জন্য একটি আকর্ষণীয় কাভার ছবি আপলোড করুন অথবা শিরোনাম দিয়ে অটো-ফটোকার্ড তৈরি করুন।
                    </p>

                    {{-- Live Cover Preview Box --}}
                    <div class="position-relative mx-auto mb-3 rounded-3 overflow-hidden border shadow-xs text-center" 
                         style="max-height: 160px; aspect-ratio: 16/9; background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);">
                        <img id="coverPreviewImg" src="{{ asset('images/og-banner.jpg') }}" alt="Cover Preview" 
                             class="w-100 h-100 object-fit-cover d-none">
                        <div id="coverPlaceholder" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 text-white">
                            <i class="fas fa-feather-pointed text-warning fs-3 mb-1"></i>
                            <div id="previewCardTitle" class="fw-bold small text-truncate w-100 px-2" style="font-size: 13px;">
                                লেখার শিরোনাম
                            </div>
                            <small class="text-white-50" style="font-size: 10.5px;">{{ $author?->name ?? $user->name }}</small>
                        </div>
                    </div>

                    {{-- File Upload Input --}}
                    <div class="mb-2.5">
                        <label for="featuredImageInput" class="form-label small fw-semibold text-dark mb-1">কাভার ছবি আপলোড (ঐচ্ছিক)</label>
                        <input type="file" name="featured_image" id="featuredImageInput" 
                               accept="image/jpeg,image/png,image/webp" 
                               class="form-control form-control-sm @error('featured_image') is-invalid @enderror"
                               onchange="previewCoverFile(this)">
                        <div class="form-text" style="font-size: 11px;">JPG, PNG বা WebP (সর্বোচ্চ ৮ মেগাবাইট)। অনুপাত ১৬:৯ বা ৪:৩ মানানসই।</div>
                        @error('featured_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Auto Photocard Canvas Holder & Trigger --}}
                    <input type="hidden" name="ai_photocard_data" id="aiPhotocardData" value="">
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill w-100 py-1.5 fw-semibold" onclick="generateAutoTitleCard()">
                        <i class="fas fa-wand-magic-sparkles me-1.5"></i> শিরোনাম দিয়ে ফটোকার্ড তৈরি করুন
                    </button>
                    <canvas id="autoCardCanvas" width="1200" height="630" style="display: none;"></canvas>
                </div>

                {{-- Editorial Guidelines Card --}}
                <div class="author-card p-3 mb-4 bg-light bg-opacity-50">
                    <h6 class="fw-bold mb-2 text-dark d-flex align-items-center gap-1.5" style="font-size: 13.5px;">
                        <i class="fas fa-scroll text-warning"></i>
                        <span>সম্পাদকীয় নির্দেশিকা ও স্বত্বাধিকার</span>
                    </h6>
                    <ul class="text-muted small ps-3 mb-3" style="font-size: 11.5px; line-height: 1.6;">
                        <li>লেখাটি আপনার নিজস্ব ও মৌলিক সৃষ্টি হতে হবে।</li>
                        <li>অন্য কোনো লেখকের লেখার অংশবিশেষ অনুমতি ব্যতিরেকে ব্যবহার করা যাবে না।</li>
                        <li>প্রকাশিত হওয়ার পূর্বে লেখাটি আইডিয়া প্রকাশন সম্পাদনা পরিষদ পর্যালোচনা করবে।</li>
                        <li class="text-danger fw-semibold">লেখাটি প্রকাশ করা অথবা প্রকাশ করলেও যে কোনো সময় কোনো প্রকার অবগতি ছাড়াই অপসারণের অধিকার প্রকাশক কর্তৃক সংরক্ষিত।</li>
                    </ul>

                    <div class="form-check mb-0">
                        <input class="form-check-input @error('agree_policy') is-invalid @enderror" type="checkbox" name="agree_policy" value="1" id="agreePolicy" checked required>
                        <label class="form-check-label small fw-semibold text-dark" for="agreePolicy" style="font-size: 11.5px;">
                            আমি আইডিয়া প্রকাশনের সম্পাদকীয় নীতিমালা ও শর্তাবলি মেনে নিচ্ছি (লেখাটি প্রকাশ করা অথবা প্রকাশ করলেও যে কোনো সময় কোনো প্রকার অবগতি ছাড়াই অপসারণের অধিকার প্রকাশক কর্তৃক সংরক্ষিত)। <span class="text-danger">*</span>
                        </label>
                        @error('agree_policy')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Action Buttons Card --}}
                <div class="author-card p-3">
                    <input type="hidden" name="action_type" id="postActionType" value="submit">
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning text-dark fw-bold py-2.5 rounded-pill shadow-sm" onclick="setActionType('submit')">
                            <i class="fas fa-paper-plane me-1.5"></i> প্রকাশের জন্য জমা দিন
                        </button>
                        
                        <button type="submit" class="btn btn-outline-secondary py-2 rounded-pill fw-semibold" onclick="setActionType('draft')">
                            <i class="fas fa-floppy-disk me-1.5"></i> খসড়া সংরক্ষণ করুন (Save Draft)
                        </button>
                    </div>

                    <div class="text-center mt-2.5">
                        <small class="text-muted" style="font-size: 11px;">
                            <i class="fas fa-lock me-1"></i> জমা দেওয়ার পর সম্পাদকের অনুমোদন সাপেক্ষে লেখাটি লাইভ হবে
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div>

<script>
function execCmd(command, value = null) {
    document.execCommand(command, false, value);
    syncEditorContent();
}

function execFormatBlock(tag) {
    document.execCommand('formatBlock', false, tag);
    syncEditorContent();
}

function syncEditorContent() {
    const editor = document.getElementById('authorContentEditable');
    const hidden = document.getElementById('hiddenContent');
    if (editor && hidden) {
        hidden.value = editor.innerHTML;
        updateWordStats(editor.innerText);
    }
}

function updateWordStats(text) {
    if (!text) {
        document.getElementById('contentWordStats').innerText = 'শব্দ: ০ | স্তবক: ০';
        return;
    }
    const words = text.trim().split(/\s+/).filter(w => w.length > 0).length;
    const stanzas = text.split(/\n\s*\n/).filter(s => s.trim().length > 0).length;
    document.getElementById('contentWordStats').innerText = `শব্দ: ${words} | স্তবক: ${stanzas}`;
}

function updateExcerptCount(el) {
    const counter = document.getElementById('excerptCounter');
    if (counter) {
        counter.innerText = `${el.value.length} / ২০০ অক্ষর`;
    }
}

function formatPoetryMode() {
    const editor = document.getElementById('authorContentEditable');
    if (!editor) return;
    const rawText = editor.innerText || editor.textContent;
    if (!rawText.trim()) return;

    const stanzas = rawText.split(/\r\n\r\n|\n\n+|\r\r+/);
    let formatted = '';
    stanzas.forEach(st => {
        const clean = st.trim();
        if (clean) {
            const lines = clean.split(/\r\n|\n|\r/).map(l => l.trim()).join('<br>');
            formatted += `<p style="margin-bottom: 1.5rem; line-height: 2.1; font-size: 17px;">${lines}</p>`;
        }
    });
    editor.innerHTML = formatted;
    syncEditorContent();
}

function formatProseMode() {
    const editor = document.getElementById('authorContentEditable');
    if (!editor) return;
    const rawText = editor.innerText || editor.textContent;
    if (!rawText.trim()) return;

    const paras = rawText.split(/\r\n\r\n|\n\n+|\r\r+/);
    let formatted = '';
    paras.forEach(p => {
        const clean = p.trim();
        if (clean) {
            formatted += `<p style="margin-bottom: 1.15rem; line-height: 1.85; font-size: 16.5px; text-align: justify;">${clean}</p>`;
        }
    });
    editor.innerHTML = formatted;
    syncEditorContent();
}

function formatCleanSpacing() {
    const editor = document.getElementById('authorContentEditable');
    if (!editor) return;
    let html = editor.innerHTML;
    html = html.replace(/<p><br><\/p>/gi, '');
    html = html.replace(/(<br\s*\/?>){3,}/gi, '<br><br>');
    editor.innerHTML = html;
    syncEditorContent();
}

function setActionType(type) {
    document.getElementById('postActionType').value = type;
    const agree = document.getElementById('agreePolicy');
    if (type === 'draft' && agree) {
        agree.removeAttribute('required');
    } else if (agree) {
        agree.setAttribute('required', 'required');
    }
    syncEditorContent();
}

function onAuthorTitleChange(val) {
    const preview = document.getElementById('previewCardTitle');
    if (preview) {
        preview.innerText = val.trim() || 'লেখার শিরোনাম';
    }
}

function previewCoverFile(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById('coverPreviewImg');
            const placeholder = document.getElementById('coverPlaceholder');
            if (img && placeholder) {
                img.src = e.target.result;
                img.classList.remove('d-none');
                placeholder.classList.add('d-none');
            }
            document.getElementById('aiPhotocardData').value = '';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function generateAutoTitleCard() {
    const title = (document.getElementById('postTitle').value || '').trim() || 'সাহিত্য ও সংস্কৃতি';
    const authorName = "{{ $author?->name ?? $user->name }}";

    const canvas = document.getElementById('autoCardCanvas');
    const ctx = canvas.getContext('2d');

    // Background Gradient
    const gradient = ctx.createLinearGradient(0, 0, 1200, 630);
    gradient.addColorStop(0, '#0f172a');
    gradient.addColorStop(0.5, '#1e1b4b');
    gradient.addColorStop(1, '#312e81');
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, 1200, 630);

    // Decorative Borders & Accents
    ctx.strokeStyle = 'rgba(251, 191, 36, 0.4)';
    ctx.lineWidth = 12;
    ctx.strokeRect(30, 30, 1140, 570);

    ctx.strokeStyle = 'rgba(255, 255, 255, 0.15)';
    ctx.lineWidth = 2;
    ctx.strokeRect(45, 45, 1110, 540);

    // Publication Badge
    ctx.fillStyle = '#fbbf24';
    ctx.font = 'bold 28px "Hind Siliguri", "Kalpurush", sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('আইডিয়াপত্র • সাহিত্য ও চিন্তার উন্মুক্ত মঞ্চ', 600, 120);

    // Title (multiline handling)
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 54px "Hind Siliguri", "Kalpurush", sans-serif';
    
    // Wrap title into up to 3 lines
    const words = title.split(' ');
    let line = '';
    let y = 260;
    const maxWidth = 1000;
    const lineHeight = 68;

    for (let n = 0; n < words.length; n++) {
        const testLine = line + words[n] + ' ';
        const metrics = ctx.measureText(testLine);
        if (metrics.width > maxWidth && n > 0) {
            ctx.fillText(line, 600, y);
            line = words[n] + ' ';
            y += lineHeight;
            if (y > 380) break;
        } else {
            line = testLine;
        }
    }
    ctx.fillText(line, 600, y);

    // Author Name
    ctx.fillStyle = '#93c5fd';
    ctx.font = 'bold 36px "Hind Siliguri", "Kalpurush", sans-serif';
    ctx.fillText(`— ${authorName}`, 600, y + 90);

    // Brand Watermark
    ctx.fillStyle = 'rgba(255, 255, 255, 0.6)';
    ctx.font = '22px sans-serif';
    ctx.fillText('ideaprakashan.com', 600, 545);

    // Convert Canvas to Data URL
    const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
    document.getElementById('aiPhotocardData').value = dataUrl;

    const img = document.getElementById('coverPreviewImg');
    const placeholder = document.getElementById('coverPlaceholder');
    if (img && placeholder) {
        img.src = dataUrl;
        img.classList.remove('d-none');
        placeholder.classList.add('d-none');
    }

    // Reset file input so base64 takes effect
    document.getElementById('featuredImageInput').value = '';
}

// Initial sync on page load
document.addEventListener('DOMContentLoaded', function() {
    syncEditorContent();
});
</script>
@endsection
