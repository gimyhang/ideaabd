@extends('layouts.admin')

@section('title', 'Media')
@section('heading', 'Media')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Media</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-media.css') }}?v={{ @filemtime(public_path('css/admin-media.css')) }}">
@endpush

@section('content')
<div class="md" id="mdApp"
     data-items="{{ route('admin.media.items') }}"
     data-unused="{{ route('admin.media.unused') }}"
     data-upload="{{ route('admin.media.upload') }}"
     data-bulk="{{ route('admin.media.bulk-action') }}"
     data-save="{{ route('admin.media.save-customized') }}"
     data-rename="{{ route('admin.media.rename') }}"
     data-zip="{{ route('admin.media.download-zip') }}"
     data-replace="{{ route('admin.media.replace') }}"
     data-gd="{{ $gd ? 1 : 0 }}" data-zip-ok="{{ $zip ? 1 : 0 }}">

    {{-- Overview --}}
    <section class="md-hero">
        <div class="md-hero-main">
            <div class="md-hero-title">
                <span class="md-hero-icon"><i class="fa-solid fa-photo-film"></i></span>
                <div>
                    <h2>Media library</h2>
                    <p><span data-stat="count">—</span> images · <span data-stat="bytes">—</span></p>
                </div>
            </div>
            <div class="md-actions">
                <div class="dropdown">
                    <button type="button" class="md-btn" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-wand-magic-sparkles"></i><span>Tools</span><i class="fa-solid fa-chevron-down md-caret"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end md-menu">
                        <li><button type="button" class="dropdown-item" data-tool="webp" @unless($gd) disabled @endunless><i class="fa-solid fa-bolt"></i><span><b>WebP</b><small>Make JPG/PNG files lighter</small></span></button></li>
                        <li><button type="button" class="dropdown-item" data-tool="optimize" @unless($gd) disabled @endunless><i class="fa-solid fa-gauge-high"></i><span><b>Optimize</b><small>Shrink heavy images</small></span></button></li>
                        <li><button type="button" class="dropdown-item" data-tool="unused"><i class="fa-solid fa-broom"></i><span><b>Unused</b><small>Find files nothing uses</small></span></button></li>
                    </ul>
                </div>
                <button type="button" class="md-btn is-primary" data-open-upload><i class="fa-solid fa-cloud-arrow-up"></i><span>Upload</span></button>
            </div>
        </div>

        <div class="md-kpis">
            <div class="md-kpi">
                <div class="md-ring" data-webp-ring style="--p: 0">
                    <svg viewBox="0 0 44 44" aria-hidden="true"><circle cx="22" cy="22" r="18" class="md-ring-track"/><circle cx="22" cy="22" r="18" class="md-ring-value" pathLength="100" stroke-dasharray="0 100"/></svg>
                    <b data-stat="webp_pct">0</b>
                </div>
                <div><strong>WebP</strong><span>of JPG/PNG/WebP</span></div>
            </div>
            <button type="button" class="md-kpi is-link" data-quick-usage="heavy">
                <i class="fa-solid fa-weight-hanging is-amber"></i>
                <div><strong data-stat="heavy">0</strong><span>Heavy (200 KB+)</span></div>
            </button>
            <button type="button" class="md-kpi is-link" data-quick-usage="unused">
                <i class="fa-solid fa-link-slash is-red"></i>
                <div><strong data-stat="unused">0</strong><span>Unused</span></div>
            </button>
            <div class="md-kpi">
                <i class="fa-solid fa-shapes is-blue"></i>
                <div><strong data-stat="formats">—</strong><span>Formats</span></div>
            </div>
        </div>

        <div class="md-storage" aria-label="Storage by folder">
            <div class="md-storage-bar" id="mdStorageBar"></div>
        </div>
    </section>

    {{-- Folders --}}
    <nav class="md-folders" id="mdFolders" aria-label="Folders">
        <button type="button" class="md-folder is-on" data-folder="all" style="--c: #0f172a"><i class="fa-solid fa-layer-group"></i><span>All</span><b data-folder-count="all">0</b></button>
        @foreach($folders as $f)
            <button type="button" class="md-folder" data-folder="{{ $f['key'] }}" style="--c: {{ $f['color'] }}"><i class="fa-solid {{ $f['icon'] }}"></i><span>{{ $f['label'] }}</span><b data-folder-count="{{ $f['key'] }}">0</b></button>
        @endforeach
    </nav>

    {{-- Toolbar --}}
    <div class="md-toolbar">
        <label class="md-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" id="mdSearch" placeholder="Search name, book, author…" autocomplete="off">
            <kbd>/</kbd>
        </label>
        <div class="md-selects">
            <select id="mdFormat" class="md-select" aria-label="Format">
                <option value="all">All formats</option>
                @foreach(['webp' => 'WebP', 'jpg' => 'JPG', 'png' => 'PNG', 'svg' => 'SVG', 'gif' => 'GIF', 'avif' => 'AVIF', 'ico' => 'ICO'] as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
            </select>
            <select id="mdShape" class="md-select" aria-label="Shape">
                <option value="all">All shapes</option>
                <option value="portrait">Portrait</option>
                <option value="square">Square</option>
                <option value="wide">Wide</option>
                <option value="small">Small</option>
            </select>
            <select id="mdUsage" class="md-select" aria-label="Use">
                <option value="all">Any use</option>
                <option value="used">In use</option>
                <option value="unused">Unused</option>
                <option value="heavy">Heavy</option>
            </select>
            <select id="mdSort" class="md-select" aria-label="Sort">
                <option value="latest">Newest</option>
                <option value="oldest">Oldest</option>
                <option value="largest">Largest</option>
                <option value="smallest">Smallest</option>
                <option value="name">Name</option>
            </select>
        </div>
        <div class="md-seg" role="group" aria-label="View">
            <button type="button" data-view="grid" class="is-on" title="Grid"><i class="fa-solid fa-grip"></i></button>
            <button type="button" data-view="list" title="List"><i class="fa-solid fa-list"></i></button>
        </div>
    </div>

    <div class="md-resultbar">
        <label class="md-check"><input type="checkbox" id="mdCheckPage"><span></span></label>
        <span id="mdResultText">Loading…</span>
        <button type="button" class="md-linkbtn" id="mdClearFilters" hidden><i class="fa-solid fa-xmark"></i> Clear filters</button>
    </div>

    <div class="md-grid" id="mdGrid" aria-live="polite"></div>
    <div class="md-more" id="mdMore" hidden><button type="button" class="md-btn" id="mdMoreBtn"><span>Load more</span></button></div>

    {{-- Selection bar --}}
    <div class="md-selbar" id="mdSelbar" hidden>
        <div class="md-selbar-info">
            <b id="mdSelCount">0</b> selected
            <button type="button" class="md-linkbtn" id="mdSelAll">Select all <span id="mdSelAllCount"></span></button>
        </div>
        <div class="md-selbar-acts">
            <button type="button" class="md-icon" data-bulk="copy" title="Copy links"><i class="fa-regular fa-copy"></i></button>
            <button type="button" class="md-icon" data-bulk="zip" title="Download ZIP" @unless($zip) hidden @endunless><i class="fa-solid fa-file-zipper"></i></button>
            @if($gd)
                <button type="button" class="md-icon" data-bulk="webp" title="Make WebP"><i class="fa-solid fa-bolt"></i></button>
                <button type="button" class="md-icon" data-bulk="optimize" title="Optimize"><i class="fa-solid fa-gauge-high"></i></button>
            @endif
            <div class="dropup">
                <button type="button" class="md-icon" data-bs-toggle="dropdown" title="Move to folder"><i class="fa-solid fa-folder-tree"></i></button>
                <ul class="dropdown-menu md-menu">
                    @foreach($folders as $f)<li><button type="button" class="dropdown-item" data-move="{{ $f['key'] }}"><i class="fa-solid {{ $f['icon'] }}" style="color: {{ $f['color'] }}"></i><span>{{ $f['label'] }}</span></button></li>@endforeach
                </ul>
            </div>
            <button type="button" class="md-icon is-danger" data-bulk="delete" title="Delete"><i class="fa-regular fa-trash-can"></i></button>
            <span class="md-selbar-sep"></span>
            <button type="button" class="md-icon" data-bulk="clear" title="Clear selection"><i class="fa-solid fa-xmark"></i></button>
        </div>
    </div>
</div>

{{-- Details drawer --}}
<div class="offcanvas offcanvas-end md-drawer" tabindex="-1" id="mdDrawer" aria-labelledby="mdDrawerTitle">
    <div class="md-drawer-head">
        <button type="button" class="md-icon" data-nav="-1" title="Previous (←)"><i class="fa-solid fa-chevron-left"></i></button>
        <button type="button" class="md-icon" data-nav="1" title="Next (→)"><i class="fa-solid fa-chevron-right"></i></button>
        <h5 id="mdDrawerTitle" class="md-drawer-title">Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="md-drawer-body" id="mdDrawerBody"></div>
</div>

{{-- Upload --}}
<div class="modal fade" id="mdUpload" tabindex="-1" aria-labelledby="mdUploadTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content md-modal">
            <div class="md-modal-head">
                <span class="md-modal-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                <div><h5 id="mdUploadTitle">Upload</h5><p>Drop, choose or paste images. They are resized and compressed for the web.</p></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="md-modal-body">
                <label class="md-drop" id="mdDrop">
                    <input type="file" id="mdFiles" multiple accept="image/*" hidden>
                    <i class="fa-solid fa-images"></i>
                    <strong>Drop images here or <u>choose</u></strong>
                    <span>JPG, PNG, WebP, SVG, GIF · up to 10 MB each · Ctrl+V to paste</span>
                </label>
                <div class="md-upload-opts">
                    <label class="md-field"><span>Folder</span>
                        <select id="mdUpFolder" class="md-select">
                            @foreach($folders as $f)<option value="{{ $f['key'] }}" data-max="{{ $f['max'] }}" @selected($f['key'] === 'uploads')>{{ $f['label'] }}</option>@endforeach
                        </select>
                    </label>
                    <label class="md-field"><span>Max size</span>
                        <select id="mdUpMax" class="md-select">
                            <option value="">Folder default</option>
                            @foreach([400, 600, 800, 1000, 1200, 1600, 1920] as $px)<option value="{{ $px }}">{{ $px }} px</option>@endforeach
                            <option value="0">Keep original</option>
                        </select>
                    </label>
                    <label class="md-switch"><input type="checkbox" id="mdUpWebp" @checked($gd) @disabled(!$gd)><span></span><b>Save as WebP</b></label>
                </div>
                <div class="md-queue" id="mdQueue"></div>
            </div>
            <div class="md-modal-foot">
                <span class="md-foot-note" id="mdQueueNote"></span>
                <button type="button" class="md-btn" data-bs-dismiss="modal">Close</button>
                <button type="button" class="md-btn is-primary" id="mdUpStart" disabled><i class="fa-solid fa-upload"></i><span>Upload</span></button>
            </div>
        </div>
    </div>
</div>

{{-- Editor --}}
<div class="modal fade" id="mdEditor" tabindex="-1" aria-labelledby="mdEditorTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content md-modal">
            <div class="md-modal-head">
                <span class="md-modal-icon"><i class="fa-solid fa-sliders"></i></span>
                <div><h5 id="mdEditorTitle">Edit</h5><p id="mdEdMeta">—</p></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="md-editor">
                <div class="md-stage"><canvas id="mdCanvas"></canvas></div>
                <div class="md-panel">
                    <div class="md-group">
                        <h6>Crop</h6>
                        <div class="md-chips" id="mdEdRatio">
                            @foreach(['0' => 'Original', '1' => '1:1', '0.6667' => '2:3', '0.75' => '3:4', '1.3333' => '4:3', '1.7778' => '16:9', '3' => '3:1'] as $v => $l)
                                <button type="button" data-ratio="{{ $v }}" class="{{ $v === '0' ? 'is-on' : '' }}">{{ $l }}</button>
                            @endforeach
                        </div>
                    </div>
                    <div class="md-group">
                        <h6>Turn</h6>
                        <div class="md-chips">
                            <button type="button" data-turn="-90" title="Rotate left"><i class="fa-solid fa-rotate-left"></i></button>
                            <button type="button" data-turn="90" title="Rotate right"><i class="fa-solid fa-rotate-right"></i></button>
                            <button type="button" data-flip="x" title="Flip sideways"><i class="fa-solid fa-left-right"></i></button>
                            <button type="button" data-flip="y" title="Flip upside down"><i class="fa-solid fa-up-down"></i></button>
                        </div>
                    </div>
                    <div class="md-group">
                        <h6>Tone <button type="button" class="md-linkbtn" id="mdEdResetTone">Reset</button></h6>
                        @foreach(['brightness' => 'Brightness', 'contrast' => 'Contrast', 'saturate' => 'Color'] as $k => $l)
                            <label class="md-range"><span>{{ $l }}</span><input type="range" min="40" max="160" value="100" data-tone="{{ $k }}"><b>100%</b></label>
                        @endforeach
                    </div>
                    <div class="md-group">
                        <h6>Size</h6>
                        <div class="md-size">
                            <label class="md-field"><span>Width</span><input type="number" id="mdEdW" min="16" max="4000"></label>
                            <i class="fa-solid fa-xmark"></i>
                            <label class="md-field"><span>Height</span><input type="number" id="mdEdH" readonly></label>
                        </div>
                    </div>
                    <div class="md-group">
                        <h6>Watermark</h6>
                        <input type="text" id="mdEdMark" class="md-input" maxlength="60" placeholder="© ideaabd.com">
                        <div class="md-size">
                            <select id="mdEdMarkPos" class="md-select">
                                <option value="br">Bottom right</option><option value="bl">Bottom left</option><option value="c">Center</option><option value="tr">Top right</option>
                            </select>
                            <label class="md-range is-compact"><input type="range" min="15" max="100" value="60" id="mdEdMarkOp"><b>60%</b></label>
                        </div>
                    </div>
                    <div class="md-group">
                        <h6>Save</h6>
                        <div class="md-size">
                            <select id="mdEdFormat" class="md-select"><option value="webp">WebP</option><option value="jpg">JPG</option><option value="png">PNG</option></select>
                            <label class="md-range is-compact"><input type="range" min="40" max="100" value="85" id="mdEdQuality"><b>85</b></label>
                        </div>
                        <div class="md-size">
                            <input type="text" id="mdEdName" class="md-input" maxlength="80" placeholder="file-name">
                            <select id="mdEdFolder" class="md-select">@foreach($folders as $f)<option value="{{ $f['key'] }}">{{ $f['label'] }}</option>@endforeach</select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="md-modal-foot">
                <span class="md-foot-note" id="mdEdOut"></span>
                <button type="button" class="md-btn is-danger-soft" id="mdEdOverwrite" title="Replace the original — the link stays the same"><i class="fa-solid fa-floppy-disk"></i><span>Overwrite</span></button>
                <button type="button" class="md-btn is-primary" id="mdEdCopy"><i class="fa-solid fa-copy"></i><span>Save copy</span></button>
            </div>
        </div>
    </div>
</div>

{{-- Tools (WebP / Optimize / Unused) --}}
<div class="modal fade" id="mdTool" tabindex="-1" aria-labelledby="mdToolTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content md-modal">
            <div class="md-modal-head">
                <span class="md-modal-icon" id="mdToolIcon"><i class="fa-solid fa-bolt"></i></span>
                <div><h5 id="mdToolTitle">Tool</h5><p id="mdToolSub"></p></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="md-modal-body" id="mdToolBody"></div>
            <div class="md-modal-foot">
                <span class="md-foot-note" id="mdToolNote"></span>
                <button type="button" class="md-btn" data-bs-dismiss="modal">Close</button>
                <button type="button" class="md-btn is-primary" id="mdToolRun"><span>Start</span></button>
            </div>
        </div>
    </div>
</div>

<input type="file" id="mdReplaceInput" accept="image/*" hidden>
<div class="md-dropzone-overlay" id="mdOverlay" hidden><div><i class="fa-solid fa-cloud-arrow-up"></i><strong>Drop to upload</strong></div></div>
<div class="md-toast" id="mdToast" role="status" hidden></div>

<script type="application/json" id="mdFoldersData">@json($folders)</script>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin-media.js') }}?v={{ @filemtime(public_path('js/admin-media.js')) }}" defer></script>
@endpush
