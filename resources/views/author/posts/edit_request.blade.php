@extends('author.layout')

@section('title', 'Edit Request — Author Portal')

@section('content')
<div class="container-fluid p-0">

    {{-- Top Header Section --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <i class="fas fa-file-pen text-warning"></i>
                <span>Post Edit Request</span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('author.posts.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <a href="{{ route('blog.show', $post->slug ?: $post->id) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="fas fa-arrow-up-right-from-square me-1"></i> View Live
            </a>
        </div>
    </div>

    {{-- Live Status Notice --}}
    <div class="alert alert-info d-flex align-items-center gap-3 p-3 rounded-3 shadow-xs mb-4">
        <i class="fas fa-circle-info fs-5 text-primary flex-shrink-0"></i>
        <div class="small">
            This post is currently live. Submitted changes will be reviewed by editors before updating the live version.
            @if($post->hasPendingEditRequest())
                <span class="badge bg-warning text-dark ms-2"><i class="fas fa-clock me-1"></i> Pending Request</span>
            @endif
        </div>
    </div>

    @php
        $pendingData = $post->edit_request_data ?: [];
        $titleVal = old('title', $pendingData['title'] ?? $post->title);
        $subtitleVal = old('subtitle', $pendingData['subtitle'] ?? $post->subtitle);
        $categoryIdVal = old('category_id', $pendingData['category_id'] ?? $post->category_id);
        $excerptVal = old('excerpt', $pendingData['excerpt'] ?? $post->excerpt);
        $contentVal = old('content', $pendingData['content'] ?? $post->content);
        $notesVal = old('notes', $post->edit_request_notes ?? '');

        if (str_contains($contentVal, '&lt;') || str_contains($contentVal, '&gt;') || str_contains($contentVal, '&quot;') || str_contains($contentVal, '&#')) {
            $contentVal = html_entity_decode($contentVal, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    @endphp

    {{-- Form Submission Card --}}
    <form action="{{ route('author.posts.submit-edit-request', $post->id) }}" method="POST" enctype="multipart/form-data" id="authorPostEditRequestForm">
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
                               value="{{ $titleVal }}" 
                               class="form-control form-control-lg fw-bold @error('title') is-invalid @enderror" 
                               placeholder="Enter title..." required 
                               oninput="onAuthorTitleChange(this.value)">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Subtitle & Category Row --}}
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-7">
                            <label for="postSubtitle" class="form-label small fw-semibold text-dark mb-1">Subtitle (Optional)</label>
                            <input type="text" name="subtitle" id="postSubtitle" 
                                   value="{{ $subtitleVal }}" 
                                   class="form-control form-control-sm @error('subtitle') is-invalid @enderror" 
                                   placeholder="Short subtitle...">
                            @error('subtitle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-5">
                            <label for="postCategory" class="form-label small fw-semibold text-dark mb-1">Category <span class="text-danger">*</span></label>
                            <select name="category_id" id="postCategory" class="form-select form-select-sm @error('category_id') is-invalid @enderror" required>
                                <option value="">— Select Category —</option>
                                @foreach(($blogCategories ?? ($categories ?? [])) as $cat)
                                    <option value="{{ $cat->id }}" @selected($categoryIdVal == $cat->id)>
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
                            <label for="postExcerpt" class="form-label small fw-semibold text-dark mb-0">Excerpt (Optional)</label>
                            <span class="text-muted small" style="font-size: 11px;" id="excerptCounter">{{ mb_strlen($excerptVal ?? '') }} / 200</span>
                        </div>
                        <textarea name="excerpt" id="postExcerpt" rows="2" 
                                  class="form-control form-control-sm @error('excerpt') is-invalid @enderror" 
                                  placeholder="Short summary..." 
                                  maxlength="500"
                                  oninput="updateExcerptCount(this)">{{ $excerptVal }}</textarea>
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
                                <span class="badge bg-light text-dark border small fw-normal" id="contentWordStats">Words: 0 | Stanzas: 0</span>
                            </div>
                        </div>

                        <div class="border rounded-3 overflow-hidden shadow-xs">
                            {{-- Formatting Toolbar --}}
                            <div class="bg-light p-2 border-bottom d-flex flex-wrap gap-1 align-items-center">
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 fw-bold" onclick="execCmd('bold')" title="Bold (Ctrl+B)">
                                    <i class="fas fa-bold"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 fst-italic" onclick="execCmd('italic')" title="Italic (Ctrl+I)">
                                    <i class="fas fa-italic"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 text-decoration-underline" onclick="execCmd('underline')" title="Underline (Ctrl+U)">
                                    <i class="fas fa-underline"></i>
                                </button>

                                <div class="vr mx-1"></div>

                                <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="execFormatBlock('p')" title="Paragraph">
                                    <i class="fas fa-paragraph"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2 fw-bold" onclick="execFormatBlock('h3')" title="Heading (H3)">
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
                                <button type="button" class="btn btn-sm btn-light border py-1 px-2" onclick="execCmd('insertHorizontalRule')" title="Horizontal Rule">
                                    <i class="fas fa-minus"></i>
                                </button>

                                <div class="vr mx-1"></div>

                                {{-- Literary Helpers --}}
                                <button type="button" class="btn btn-sm btn-outline-primary border py-1 px-2.5 fw-semibold" onclick="formatPoetryMode()" title="Poetry Mode">
                                    <i class="fas fa-feather-alt me-1"></i> Poetry
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary border py-1 px-2.5 fw-semibold" onclick="formatProseMode()" title="Prose Mode">
                                    <i class="fas fa-align-left me-1"></i> Prose
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info border py-1 px-2.5 fw-semibold" onclick="formatCleanSpacing()" title="Clean Spacing">
                                    <i class="fas fa-wand-magic-sparkles me-1"></i> Spacing
                                </button>

                                <div class="vr mx-1"></div>

                                <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-danger" onclick="execCmd('removeFormat')" title="Clear Formatting">
                                    <i class="fas fa-eraser"></i>
                                </button>
                            </div>

                            {{-- Editable Area --}}
                            <div id="authorContentEditable" contenteditable="true" 
                                 class="p-3 bg-white text-dark rich-editor-content" 
                                 style="min-height: 420px; max-height: 700px; overflow-y: auto; outline: none; font-size: 16.5px; line-height: 1.85; font-family: 'Hind Siliguri', 'SolaimanLipi', sans-serif;"
                                 oninput="syncEditorContent()">{!! $contentVal !!}</div>

                            {{-- Hidden Form Textarea --}}
                            <textarea id="hiddenContent" name="content" class="d-none @error('content') is-invalid @enderror" required>{!! $contentVal !!}</textarea>
                        </div>
                        @error('content')
                            <div class="text-danger small mt-1 fw-semibold">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Right Column: Notes for Editor, Cover Image & Actions --}}
            <div class="col-12 col-lg-4">
                
                {{-- Notes / Reason Card --}}
                <div class="author-card p-3 mb-4 border-start border-3 border-warning">
                    <h6 class="fw-bold mb-2 text-dark d-flex align-items-center gap-1.5">
                        <i class="fas fa-clipboard-list text-warning"></i>
                        <span>Revision Notes <span class="text-danger">*</span></span>
                    </h6>
                    <textarea name="notes" id="editNotes" rows="3" 
                              class="form-control form-control-sm @error('notes') is-invalid @enderror" 
                              placeholder="Describe what changes were made..." required>{{ $notesVal }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Cover Image Card --}}
                <div class="author-card p-3 mb-4">
                    <h6 class="fw-bold mb-2 text-dark d-flex align-items-center gap-1.5">
                        <i class="fas fa-image text-primary"></i>
                        <span>Cover Image</span>
                    </h6>

                    @php
                        $coverUrl = !empty($pendingData['featured_image']) 
                            ? (str_starts_with($pendingData['featured_image'], 'http') ? $pendingData['featured_image'] : asset('storage/' . ltrim($pendingData['featured_image'], '/')))
                            : ($post->cover_url ?: ($post->featured_image ? (str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . ltrim($post->featured_image, '/'))) : null));
                    @endphp

                    {{-- Live Cover Preview Box --}}
                    <div class="position-relative mx-auto mb-3 rounded-3 overflow-hidden border shadow-xs text-center" 
                         style="max-height: 160px; aspect-ratio: 16/9; background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);">
                        <img id="coverPreviewImg" src="{{ $coverUrl ?: asset('images/og-banner.jpg') }}" alt="Cover Preview" 
                             class="w-100 h-100 object-fit-cover {{ $coverUrl ? '' : 'd-none' }}">
                        <div id="coverPlaceholder" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-3 text-white {{ $coverUrl ? 'd-none' : '' }}">
                            <i class="fas fa-feather-pointed text-warning fs-3 mb-1"></i>
                            <div id="previewCardTitle" class="fw-bold small text-truncate w-100 px-2" style="font-size: 13px;">
                                {{ $post->title }}
                            </div>
                            <small class="text-white-50" style="font-size: 10.5px;">{{ $author?->name ?? $user->name }}</small>
                        </div>
                    </div>

                    {{-- File Upload Input --}}
                    <div class="mb-2.5">
                        <label for="featuredImageInput" class="form-label small fw-semibold text-dark mb-1">Upload New Cover (Optional)</label>
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
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill w-100 py-1.5 fw-semibold" onclick="generateAutoTitleCard()">
                        <i class="fas fa-wand-magic-sparkles me-1.5"></i> Generate Title Card
                    </button>
                    <canvas id="autoCardCanvas" width="1200" height="630" style="display: none;"></canvas>
                </div>

                {{-- Action Buttons Card --}}
                <div class="author-card p-3">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-warning text-dark fw-bold py-2.5 rounded-pill shadow-sm" onclick="syncEditorContent()">
                            <i class="fas fa-paper-plane me-1.5"></i> Submit Edit Request
                        </button>
                        <a href="{{ route('author.posts.index') }}" class="btn btn-outline-secondary py-2 rounded-pill fw-semibold">
                            <i class="fas fa-xmark me-1.5"></i> Cancel
                        </a>
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
        document.getElementById('contentWordStats').innerText = 'Words: 0 | Stanzas: 0';
        return;
    }
    const words = text.trim().split(/\s+/).filter(w => w.length > 0).length;
    const stanzas = text.split(/\n\s*\n/).filter(s => s.trim().length > 0).length;
    document.getElementById('contentWordStats').innerText = `Words: ${words} | Stanzas: ${stanzas}`;
}

function updateExcerptCount(el) {
    const counter = document.getElementById('excerptCounter');
    if (counter) {
        counter.innerText = `${el.value.length} / 200`;
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

    // Decorative Borders
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

    // Title (multiline)
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 54px "Hind Siliguri", "Kalpurush", sans-serif';
    
    const words = title.split(' ');
    let line = '';
    let y = 260;
    const maxWidth = 1000;
    const lineHeight = 68;

    for (let n = 0; n < words.length; n++) {
        const testLine = line + words[n] + ' ';
        const metrics = ctx.measureText(testLine);
        if (metrics.width > maxWidth && n > 0) {
            ctx.fillText(line.trim(), 600, y);
            line = words[n] + ' ';
            y += lineHeight;
        } else {
            line = testLine;
        }
    }
    ctx.fillText(line.trim(), 600, y);

    // Author Name Footer
    ctx.fillStyle = '#cbd5e1';
    ctx.font = '32px "Hind Siliguri", "Kalpurush", sans-serif';
    ctx.fillText('লেখক: ' + authorName, 600, 520);

    // Update Live Preview and hidden input
    const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
    const previewImg = document.getElementById('coverPreviewImg');
    const placeholder = document.getElementById('coverPlaceholder');
    if (previewImg && placeholder) {
        previewImg.src = dataUrl;
        previewImg.classList.remove('d-none');
        placeholder.classList.add('d-none');
    }
    document.getElementById('aiPhotocardData').value = dataUrl;
}

document.addEventListener('DOMContentLoaded', function() {
    syncEditorContent();
});
</script>
@endsection
