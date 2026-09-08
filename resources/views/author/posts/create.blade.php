@extends('author.layout')

@section('title', 'নতুন আইডিয়াপত্র লিখুন — লেখক পোর্টাল')

@section('content')
<div class="container-fluid p-0">

    {{-- Top Header Section --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <i class="fas fa-pen-nib text-warning"></i>
                <span>Write Post</span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('author.posts.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> My Posts
            </a>
            <a href="{{ route('blog.index') }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="fas fa-eye me-1"></i> Feed
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
                            Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="title" id="postTitle" 
                               value="{{ old('title') }}" 
                               class="form-control form-control-lg fw-bold @error('title') is-invalid @enderror" 
                               placeholder="Enter post title..." required 
                               oninput="onAuthorTitleChange(this.value)">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Subtitle & Category Row --}}
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-7">
                            <label for="postSubtitle" class="form-label small fw-semibold text-dark mb-1">Subtitle</label>
                            <input type="text" name="subtitle" id="postSubtitle" 
                                   value="{{ old('subtitle') }}" 
                                   class="form-control form-control-sm @error('subtitle') is-invalid @enderror" 
                                   placeholder="Subtitle or tagline...">
                            @error('subtitle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-5">
                            <label for="postCategory" class="form-label small fw-semibold text-dark mb-1">Category <span class="text-danger">*</span></label>
                            <select name="category_id" id="postCategory" class="form-select form-select-sm @error('category_id') is-invalid @enderror" required>
                                <option value="">Select Category</option>
                                @foreach(($blogCategories ?? ($categories ?? [])) as $cat)
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
                            <label for="postExcerpt" class="form-label small fw-semibold text-dark mb-0">Excerpt</label>
                            <span class="text-muted small" style="font-size: 11px;" id="excerptCounter">0 / 200</span>
                        </div>
                        <textarea name="excerpt" id="postExcerpt" rows="2" 
                                  class="form-control form-control-sm @error('excerpt') is-invalid @enderror" 
                                  placeholder="Short summary or teaser..." 
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
                                Content <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark border small fw-normal" id="contentWordStats">Words: 0 | Lines: 0</span>
                            </div>
                        </div>

                        <div class="border rounded-3 overflow-hidden shadow-xs">
                            {{-- Formatting Toolbar --}}
                            <div class="bg-light p-2 border-bottom d-flex flex-wrap gap-1 align-items-center">
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 fw-bold" onclick="execCmd('bold')" title="Bold">
                                    <i class="fas fa-bold"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 fst-italic" onclick="execCmd('italic')" title="Italic">
                                    <i class="fas fa-italic"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 text-decoration-underline" onclick="execCmd('underline')" title="Underline">
                                    <i class="fas fa-underline"></i>
                                </button>

                                <div class="vr mx-1"></div>

                                <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="execFormatBlock('p')" title="Paragraph">
                                    <i class="fas fa-paragraph"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2 fw-bold" onclick="execFormatBlock('h3')" title="Heading 3">
                                    H3
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="execFormatBlock('blockquote')" title="Quote">
                                    <i class="fas fa-quote-left"></i>
                                </button>

                                <div class="vr mx-1"></div>

                                <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="execCmd('insertUnorderedList')" title="Bullet List">
                                    <i class="fas fa-list-ul"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="execCmd('insertOrderedList')" title="Numbered List">
                                    <i class="fas fa-list-ol"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="execCmd('insertHorizontalRule')" title="Divider">
                                    <i class="fas fa-minus"></i>
                                </button>

                                <div class="vr mx-1"></div>

                                {{-- Literary Helpers --}}
                                <button type="button" class="btn btn-sm btn-outline-primary border py-1 px-2.5 fw-semibold" onclick="formatPoetryMode()" title="Poetry Format">
                                    <i class="fas fa-feather-alt me-1"></i> Poetry
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary border py-1 px-2.5 fw-semibold" onclick="formatProseMode()" title="Prose Format">
                                    <i class="fas fa-align-left me-1"></i> Prose
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info border py-1 px-2.5 fw-semibold" onclick="formatCleanSpacing()" title="Clean Spacing">
                                    <i class="fas fa-wand-magic-sparkles me-1"></i> Format
                                </button>

                                <div class="vr mx-1"></div>

                                <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-danger" onclick="execCmd('removeFormat')" title="Clear Format">
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
                        <span>Cover Image</span>
                    </h6>

                    {{-- Live Cover Preview Box --}}
                    <div class="position-relative mx-auto mb-3 rounded-3 overflow-hidden border shadow-xs text-center" 
                         style="max-height: 160px; aspect-ratio: 16/9; background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);">
                        <img id="coverPreviewImg" src="{{ asset('images/og-banner.jpg') }}" alt="Cover Preview" 
                             class="w-100 h-100 object-fit-cover d-none">
                        <div id="coverPlaceholder" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 text-white">
                            <i class="fas fa-feather-pointed text-warning fs-3 mb-1"></i>
                            <div id="previewCardTitle" class="fw-bold small text-truncate w-100 px-2" style="font-size: 13px;">
                                Title
                            </div>
                            <small class="text-white-50" style="font-size: 10.5px;">{{ $author?->name ?? $user->name }}</small>
                        </div>
                    </div>

                    {{-- File Upload Input --}}
                    <div class="mb-2.5">
                        <label for="featuredImageInput" class="form-label small fw-semibold text-dark mb-1">Upload Cover</label>
                        <input type="file" name="featured_image" id="featuredImageInput" 
                               accept="image/jpeg,image/png,image/webp" 
                               class="form-control form-control-sm @error('featured_image') is-invalid @enderror"
                               onchange="previewCoverFile(this)">
                        @error('featured_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Auto Photocard Canvas Holder & Trigger --}}
                    <input type="hidden" name="ai_photocard_data" id="aiPhotocardData" value="">
                    <button type="button" id="btnGenCard" class="btn btn-sm btn-outline-primary rounded-pill w-100 py-1.5 fw-semibold" onclick="generateAutoTitleCard()">
                        <i class="fas fa-wand-magic-sparkles me-1.5"></i> কভার তৈরি করুন (Title Card)
                    </button>
                    <canvas id="autoCardCanvas" width="1200" height="675" style="display: none;"></canvas>
                </div>

                {{-- Policy Terms --}}
                <div class="author-card p-3 mb-4 bg-light bg-opacity-50">
                    <div class="form-check mb-0">
                        <input class="form-check-input @error('agree_policy') is-invalid @enderror" type="checkbox" name="agree_policy" value="1" id="agreePolicy" checked required>
                        <label class="form-check-label small fw-semibold text-dark" for="agreePolicy" style="font-size: 12px;">
                            I accept the editorial policy & terms. <span class="text-danger">*</span>
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
                        <button type="submit" class="btn btn-warning text-dark fw-bold py-2 rounded-pill shadow-sm" onclick="setActionType('submit')">
                            <i class="fas fa-paper-plane me-1.5"></i> Submit Post
                        </button>
                        
                        <button type="submit" class="btn btn-outline-secondary py-2 rounded-pill fw-semibold" onclick="setActionType('draft')">
                            <i class="fas fa-floppy-disk me-1.5"></i> Save Draft
                        </button>
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

async function generateAutoTitleCard() {
    const btn = document.getElementById('btnGenCard');
    const origBtnHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1.5"></i> কভার তৈরি হচ্ছে...';
    }

    try {
        const title = (document.getElementById('postTitle').value || '').trim() || 'সাহিত্য ও সংস্কৃতি';
        const authorName = "{{ $author?->name ?? $user->name }}";

        // Preload and ensure Bengali fonts are ready in canvas
        try {
            await document.fonts.load('bold 52px "Hind Siliguri"');
            await document.fonts.load('bold 24px "Hind Siliguri"');
            await document.fonts.load('bold 32px "Hind Siliguri"');
            await document.fonts.ready;
        } catch (e) {
            console.warn('Font loading check:', e);
        }

        const canvas = document.getElementById('autoCardCanvas');
        canvas.width = 1200;
        canvas.height = 675;
        const ctx = canvas.getContext('2d');

        // Background Gradient (Deep Luxury Indigo / Slate)
        const gradient = ctx.createLinearGradient(0, 0, 1200, 675);
        gradient.addColorStop(0, '#0f172a');
        gradient.addColorStop(0.5, '#1e1b4b');
        gradient.addColorStop(1, '#312e81');
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, 1200, 675);

        // Soft Center Radial Glow
        const radial = ctx.createRadialGradient(600, 320, 30, 600, 320, 560);
        radial.addColorStop(0, 'rgba(251, 191, 36, 0.2)');
        radial.addColorStop(1, 'rgba(0, 0, 0, 0)');
        ctx.fillStyle = radial;
        ctx.fillRect(0, 0, 1200, 675);

        // Decorative Double Gold Borders
        ctx.strokeStyle = 'rgba(251, 191, 36, 0.4)';
        ctx.lineWidth = 2;
        ctx.strokeRect(30, 30, 1140, 615);

        ctx.strokeStyle = '#fbbf24';
        ctx.lineWidth = 4;
        ctx.strokeRect(42, 42, 1116, 591);

        // Ornate Corner Lines
        ctx.strokeStyle = '#fbbf24';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(42, 85); ctx.lineTo(85, 42);
        ctx.moveTo(42, 105); ctx.lineTo(105, 42);
        ctx.moveTo(1158, 85); ctx.lineTo(1115, 42);
        ctx.moveTo(1158, 105); ctx.lineTo(1095, 42);
        ctx.moveTo(42, 590); ctx.lineTo(85, 633);
        ctx.moveTo(42, 570); ctx.lineTo(105, 633);
        ctx.moveTo(1158, 590); ctx.lineTo(1115, 633);
        ctx.moveTo(1158, 570); ctx.lineTo(1095, 633);
        ctx.stroke();

        // Top Category Pill Badge
        ctx.fillStyle = 'rgba(255, 255, 255, 0.12)';
        ctx.strokeStyle = '#fbbf24';
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        if (typeof ctx.roundRect === 'function') {
            ctx.roundRect(430, 65, 340, 44, 22);
        } else {
            ctx.rect(430, 65, 340, 44);
        }
        ctx.fill();
        ctx.stroke();

        // Badge Text
        ctx.fillStyle = '#fbbf24';
        ctx.font = 'bold 20px "Hind Siliguri", "Kalpurush", sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('✦ আইডিয়াপত্র • সাহিত্য ও চিন্তার উন্মুক্ত মঞ্চ ✦', 600, 95);

        // Title Calculation & Smart Wrapping
        let fontSize = 48;
        if (title.length > 60) fontSize = 38;
        else if (title.length > 35) fontSize = 44;

        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold ' + fontSize + 'px "Hind Siliguri", "Kalpurush", sans-serif';
        ctx.textAlign = 'center';

        const words = title.split(' ');
        let line = '';
        const lines = [];
        const maxW = 980;

        for (let n = 0; n < words.length; n++) {
            const testLine = line + (line ? ' ' : '') + words[n];
            const metrics = ctx.measureText(testLine);
            if (metrics.width > maxW && n > 0) {
                lines.push(line);
                line = words[n];
                if (lines.length >= 3) break;
            } else {
                line = testLine;
            }
        }
        if (line && lines.length < 3) lines.push(line);

        const lineHeight = fontSize + 18;
        const totalTextHeight = lines.length * lineHeight;
        let startY = 320 - (totalTextHeight / 2) + (lineHeight / 2);

        for (let i = 0; i < lines.length; i++) {
            ctx.fillText(lines[i], 600, startY + (i * lineHeight));
        }

        // Center Gold Divider Ornament
        const dividerY = Math.max(430, startY + totalTextHeight + 20);
        ctx.strokeStyle = 'rgba(234, 179, 8, 0.7)';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(300, dividerY);
        ctx.lineTo(520, dividerY);
        ctx.moveTo(680, dividerY);
        ctx.lineTo(900, dividerY);
        ctx.stroke();

        ctx.fillStyle = '#fef08a';
        ctx.font = '22px "Hind Siliguri", sans-serif';
        ctx.fillText('❖ ─── ✦ ─── ❖', 600, dividerY + 8);

        // Footer Divider Line
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.moveTo(75, 565);
        ctx.lineTo(1125, 565);
        ctx.stroke();

        // Footer Author Name (Left)
        ctx.textAlign = 'left';
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 24px "Hind Siliguri", "Kalpurush", sans-serif';
        ctx.fillText('✍️ রচনা: ' + authorName, 80, 608);

        // Footer Brand (Right)
        ctx.textAlign = 'right';
        ctx.fillStyle = '#fbbf24';
        ctx.font = 'bold 22px "Hind Siliguri", "Kalpurush", sans-serif';
        ctx.fillText('আইডিয়া প্রকাশন | ideaprakashan.com', 1120, 608);

        // Convert Canvas to Data URL (High Quality JPEG)
        const dataUrl = canvas.toDataURL('image/jpeg', 0.95);
        document.getElementById('aiPhotocardData').value = dataUrl;

        const img = document.getElementById('coverPreviewImg');
        const placeholder = document.getElementById('coverPlaceholder');
        if (img && placeholder) {
            img.src = dataUrl;
            img.classList.remove('d-none');
            placeholder.classList.add('d-none');
        }

        // Clear file input so base64 takes effect
        const fileInput = document.getElementById('featuredImageInput');
        if (fileInput) fileInput.value = '';

        if (btn) {
            btn.innerHTML = '<i class="fas fa-check text-success me-1.5"></i> কভার তৈরি সম্পন্ন';
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = origBtnHtml;
            }, 2000);
        }
    } catch (err) {
        console.error('Error generating title card:', err);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origBtnHtml;
        }
    }
}

// Initial sync on page load & form submit safety
document.addEventListener('DOMContentLoaded', function() {
    syncEditorContent();
    const form = document.getElementById('authorPostForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            syncEditorContent();
            const contentVal = (document.getElementById('hiddenContent').value || '').trim();
            if (!contentVal || contentVal === '<p><br></p>' || contentVal === '<br>') {
                e.preventDefault();
                alert('অনুগ্রহ করে ব্লগের মূল বিষয়বস্তু বা রচনা লিখুন।');
                document.getElementById('authorContentEditable').focus();
                return false;
            }
        });
    }
});
</script>
@endsection
