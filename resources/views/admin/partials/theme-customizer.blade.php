{{-- Dynamic Admin Theme Customizer Drawer --}}
@php
    $currentTheme = \App\Support\SiteSetting::themeSettings();
    $userPrefs = auth()->user()->reg_data['preferences'] ?? [];
    $activeMode = $userPrefs['theme'] ?? $currentTheme['default_mode'] ?? 'light';
    $activePrimary = $userPrefs['primary_color'] ?? $currentTheme['primary_color'] ?? '#0066cc';
    $activeSecondary = $userPrefs['secondary_color'] ?? $currentTheme['secondary_color'] ?? '#0099ff';
    $activeSidebar = $userPrefs['sidebar_theme'] ?? $currentTheme['sidebar_theme'] ?? 'theme-deep-navy';
    $activeFont = $userPrefs['font_family'] ?? $currentTheme['font_family'] ?? 'Hind Siliguri';
@endphp

<div class="offcanvas offcanvas-end theme-customizer-drawer" tabindex="-1" id="admThemeCustomizerDrawer" aria-labelledby="admThemeCustomizerLabel">
    <div class="offcanvas-header p-3.5 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary p-2 rounded-3 fs-6">
                <i class="fa-solid fa-palette"></i>
            </span>
            <div>
                <h5 class="offcanvas-title fw-bold mb-0 fs-6" id="admThemeCustomizerLabel">ড্যাশবোর্ড থিম কাস্টমাইজার</h5>
                <small class="text-muted" style="font-size: 0.72rem;">Live Dynamic Theme & Layout Studio</small>
            </div>
        </div>
        <button type="button" class="btn-close text-reset shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-3.5 custom-scrollbar">
        
        {{-- Section 1: Display Mode (Light / Dark / System) --}}
        <div class="mb-4">
            <label class="form-label fw-bold small text-dark d-flex align-items-center justify-content-between mb-2">
                <span><i class="fa-solid fa-circle-half-stroke me-1.5 text-primary"></i>ডিসপ্লে মোড (Display Mode)</span>
                <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.65rem;">Auto-Sync</span>
            </label>
            <div class="row g-2" id="themeModeOptions">
                <div class="col-4">
                    <button type="button" class="btn btn-outline-secondary w-100 p-2.5 rounded-3 text-center border d-flex flex-column align-items-center gap-1.5 {{ $activeMode === 'light' ? 'border-primary bg-primary-subtle text-primary fw-bold' : '' }}" 
                            onclick="AdminTheme.setMode('light', true); updateCustomizerActiveState();" data-mode-btn="light">
                        <i class="fa-solid fa-sun text-warning fs-5"></i>
                        <span style="font-size: 0.75rem;">লাইট (Light)</span>
                    </button>
                </div>
                <div class="col-4">
                    <button type="button" class="btn btn-outline-secondary w-100 p-2.5 rounded-3 text-center border d-flex flex-column align-items-center gap-1.5 {{ $activeMode === 'dark' ? 'border-primary bg-primary-subtle text-primary fw-bold' : '' }}" 
                            onclick="AdminTheme.setMode('dark', true); updateCustomizerActiveState();" data-mode-btn="dark">
                        <i class="fa-solid fa-moon text-indigo fs-5"></i>
                        <span style="font-size: 0.75rem;">ডার্ক (Dark)</span>
                    </button>
                </div>
                <div class="col-4">
                    <button type="button" class="btn btn-outline-secondary w-100 p-2.5 rounded-3 text-center border d-flex flex-column align-items-center gap-1.5 {{ $activeMode === 'auto' ? 'border-primary bg-primary-subtle text-primary fw-bold' : '' }}" 
                            onclick="AdminTheme.setMode('auto', true); updateCustomizerActiveState();" data-mode-btn="auto">
                        <i class="fa-solid fa-desktop text-info fs-5"></i>
                        <span style="font-size: 0.75rem;">অটো (System)</span>
                    </button>
                </div>
            </div>
        </div>

        <hr class="my-3.5 opacity-50">

        {{-- Section 2: Preset Color Palettes --}}
        <div class="mb-4">
            <label class="form-label fw-bold small text-dark d-flex align-items-center justify-content-between mb-2">
                <span><i class="fa-solid fa-swatchbook me-1.5 text-primary"></i>প্রিসেট কালার প্যালেট</span>
                <span class="small text-muted" style="font-size: 0.7rem;">১-ক্লিকে পরিবর্তন</span>
            </label>
            <div class="row g-2">
                {{-- Preset 1: Classic Blue --}}
                <div class="col-6">
                    <div class="theme-preset-btn p-2 border rounded-3 d-flex align-items-center gap-2 cursor-pointer" 
                         onclick="applyPresetPalette('#0066cc', '#0099ff', '#ff6b35', 'theme-deep-navy')">
                        <div class="d-flex align-items-center">
                            <span class="theme-color-dot" style="background: #0066cc;"></span>
                            <span class="theme-color-dot ms-n1" style="background: #0099ff; margin-left: -8px;"></span>
                        </div>
                        <div class="text-truncate">
                            <div class="fw-bold small lh-1">Classic Blue</div>
                            <small class="text-muted" style="font-size: 0.65rem;">রয়্যাল ব্লু</small>
                        </div>
                    </div>
                </div>

                {{-- Preset 2: Emerald Green --}}
                <div class="col-6">
                    <div class="theme-preset-btn p-2 border rounded-3 d-flex align-items-center gap-2 cursor-pointer" 
                         onclick="applyPresetPalette('#059669', '#10b981', '#f59e0b', 'theme-emerald-forest')">
                        <div class="d-flex align-items-center">
                            <span class="theme-color-dot" style="background: #059669;"></span>
                            <span class="theme-color-dot ms-n1" style="background: #10b981; margin-left: -8px;"></span>
                        </div>
                        <div class="text-truncate">
                            <div class="fw-bold small lh-1">Emerald Oasis</div>
                            <small class="text-muted" style="font-size: 0.65rem;">সবুজ অরণ্য</small>
                        </div>
                    </div>
                </div>

                {{-- Preset 3: Royal Indigo --}}
                <div class="col-6">
                    <div class="theme-preset-btn p-2 border rounded-3 d-flex align-items-center gap-2 cursor-pointer" 
                         onclick="applyPresetPalette('#6366f1', '#8b5cf6', '#ec4899', 'theme-royal-purple')">
                        <div class="d-flex align-items-center">
                            <span class="theme-color-dot" style="background: #6366f1;"></span>
                            <span class="theme-color-dot ms-n1" style="background: #8b5cf6; margin-left: -8px;"></span>
                        </div>
                        <div class="text-truncate">
                            <div class="fw-bold small lh-1">Royal Purple</div>
                            <small class="text-muted" style="font-size: 0.65rem;">রাজকীয় বেগুনী</small>
                        </div>
                    </div>
                </div>

                {{-- Preset 4: Sunset Orange --}}
                <div class="col-6">
                    <div class="theme-preset-btn p-2 border rounded-3 d-flex align-items-center gap-2 cursor-pointer" 
                         onclick="applyPresetPalette('#ea580c', '#f59e0b', '#0284c7', 'theme-midnight-slate')">
                        <div class="d-flex align-items-center">
                            <span class="theme-color-dot" style="background: #ea580c;"></span>
                            <span class="theme-color-dot ms-n1" style="background: #f59e0b; margin-left: -8px;"></span>
                        </div>
                        <div class="text-truncate">
                            <div class="fw-bold small lh-1">Sunset Flame</div>
                            <small class="text-muted" style="font-size: 0.65rem;">উজ্জ্বল কমলা</small>
                        </div>
                    </div>
                </div>

                {{-- Preset 5: Velvet Crimson --}}
                <div class="col-6">
                    <div class="theme-preset-btn p-2 border rounded-3 d-flex align-items-center gap-2 cursor-pointer" 
                         onclick="applyPresetPalette('#e11d48', '#f43f5e', '#f59e0b', 'theme-crimson-night')">
                        <div class="d-flex align-items-center">
                            <span class="theme-color-dot" style="background: #e11d48;"></span>
                            <span class="theme-color-dot ms-n1" style="background: #f43f5e; margin-left: -8px;"></span>
                        </div>
                        <div class="text-truncate">
                            <div class="fw-bold small lh-1">Velvet Crimson</div>
                            <small class="text-muted" style="font-size: 0.65rem;">রক্তিম লাল</small>
                        </div>
                    </div>
                </div>

                {{-- Preset 6: Cyber Slate --}}
                <div class="col-6">
                    <div class="theme-preset-btn p-2 border rounded-3 d-flex align-items-center gap-2 cursor-pointer" 
                         onclick="applyPresetPalette('#0284c7', '#38bdf8', '#10b981', 'theme-midnight-slate')">
                        <div class="d-flex align-items-center">
                            <span class="theme-color-dot" style="background: #0284c7;"></span>
                            <span class="theme-color-dot ms-n1" style="background: #38bdf8; margin-left: -8px;"></span>
                        </div>
                        <div class="text-truncate">
                            <div class="fw-bold small lh-1">Cyber Cyan</div>
                            <small class="text-muted" style="font-size: 0.65rem;">সাইবার ব্লু</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-3.5 opacity-50">

        {{-- Section 3: Custom Brand Colors --}}
        <div class="mb-4">
            <label class="form-label fw-bold small text-dark d-flex align-items-center justify-content-between mb-2">
                <span><i class="fa-solid fa-eye-dropper me-1.5 text-primary"></i>কাস্টম কালার কোড (HEX)</span>
                <span class="small text-muted" style="font-size: 0.7rem;">লাইভ প্রিভিউ</span>
            </label>

            <div class="p-3 bg-light rounded-3 border">
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label text-muted" style="font-size: 0.72rem;">Primary Color</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" id="drawerPrimaryColorPicker" value="{{ $activePrimary }}" 
                                   class="form-control form-control-color border-0 p-0 rounded-circle cursor-pointer" 
                                   style="width: 32px; height: 32px;" 
                                   oninput="onCustomColorChange('primary', this.value)">
                            <input type="text" id="drawerPrimaryColorHex" value="{{ $activePrimary }}" 
                                   class="form-control form-control-sm font-monospace rounded-2" 
                                   oninput="onCustomColorChange('primary', this.value)">
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted" style="font-size: 0.72rem;">Secondary Gradient</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" id="drawerSecondaryColorPicker" value="{{ $activeSecondary }}" 
                                   class="form-control form-control-color border-0 p-0 rounded-circle cursor-pointer" 
                                   style="width: 32px; height: 32px;" 
                                   oninput="onCustomColorChange('secondary', this.value)">
                            <input type="text" id="drawerSecondaryColorHex" value="{{ $activeSecondary }}" 
                                   class="form-control form-control-sm font-monospace rounded-2" 
                                   oninput="onCustomColorChange('secondary', this.value)">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-3.5 opacity-50">

        {{-- Section 4: Sidebar Themes --}}
        <div class="mb-4">
            <label class="form-label fw-bold small text-dark d-flex align-items-center justify-content-between mb-2">
                <span><i class="fa-solid fa-table-columns me-1.5 text-primary"></i>সাইডবার থিম (Sidebar Style)</span>
            </label>
            <div class="row g-2" id="sidebarThemeOptions">
                <div class="col-6">
                    <button type="button" class="btn btn-outline-secondary w-100 p-2 rounded-3 text-start border d-flex align-items-center gap-2" 
                            onclick="AdminTheme.setSidebarTheme('theme-deep-navy', true); updateCustomizerActiveState();" data-sidebar-btn="theme-deep-navy">
                        <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #0b2545;"></span>
                        <span class="small">Deep Navy</span>
                    </button>
                </div>
                <div class="col-6">
                    <button type="button" class="btn btn-outline-secondary w-100 p-2 rounded-3 text-start border d-flex align-items-center gap-2" 
                            onclick="AdminTheme.setSidebarTheme('theme-midnight-slate', true); updateCustomizerActiveState();" data-sidebar-btn="theme-midnight-slate">
                        <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #0f172a;"></span>
                        <span class="small">Carbon Slate</span>
                    </button>
                </div>
                <div class="col-6">
                    <button type="button" class="btn btn-outline-secondary w-100 p-2 rounded-3 text-start border d-flex align-items-center gap-2" 
                            onclick="AdminTheme.setSidebarTheme('theme-emerald-forest', true); updateCustomizerActiveState();" data-sidebar-btn="theme-emerald-forest">
                        <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #064e3b;"></span>
                        <span class="small">Forest Green</span>
                    </button>
                </div>
                <div class="col-6">
                    <button type="button" class="btn btn-outline-secondary w-100 p-2 rounded-3 text-start border d-flex align-items-center gap-2" 
                            onclick="AdminTheme.setSidebarTheme('theme-royal-purple', true); updateCustomizerActiveState();" data-sidebar-btn="theme-royal-purple">
                        <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #311042;"></span>
                        <span class="small">Royal Purple</span>
                    </button>
                </div>
                <div class="col-6">
                    <button type="button" class="btn btn-outline-secondary w-100 p-2 rounded-3 text-start border d-flex align-items-center gap-2" 
                            onclick="AdminTheme.setSidebarTheme('theme-crimson-night', true); updateCustomizerActiveState();" data-sidebar-btn="theme-crimson-night">
                        <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background: #4c0519;"></span>
                        <span class="small">Velvet Crimson</span>
                    </button>
                </div>
                <div class="col-6">
                    <button type="button" class="btn btn-outline-secondary w-100 p-2 rounded-3 text-start border d-flex align-items-center gap-2" 
                            onclick="AdminTheme.setSidebarTheme('theme-minimal-light', true); updateCustomizerActiveState();" data-sidebar-btn="theme-minimal-light">
                        <span class="rounded-circle d-inline-block border bg-white" style="width: 16px; height: 16px;"></span>
                        <span class="small">Minimal Light</span>
                    </button>
                </div>
            </div>
        </div>

        <hr class="my-3.5 opacity-50">

        {{-- Section 5: Bengali Font Typography --}}
        <div class="mb-4">
            <label class="form-label fw-bold small text-dark d-flex align-items-center justify-content-between mb-2">
                <span><i class="fa-solid fa-font me-1.5 text-primary"></i>বাংলা ফন্ট টাইপোগ্রাফি (Font)</span>
            </label>
            <select class="form-select form-select-sm rounded-3" id="drawerFontSelect" onchange="AdminTheme.setFont(this.value, true);">
                <option value="Hind Siliguri" {{ $activeFont === 'Hind Siliguri' ? 'selected' : '' }}>হিন্দ শিলিগুড়ি (Hind Siliguri - Default)</option>
                <option value="Kalpurush" {{ $activeFont === 'Kalpurush' ? 'selected' : '' }}>কালপুরুষ (Kalpurush)</option>
                <option value="Nikosh" {{ $activeFont === 'Nikosh' ? 'selected' : '' }}>নিকোশ (Nikosh)</option>
                <option value="Inter" {{ $activeFont === 'Inter' ? 'selected' : '' }}>Inter (English / Clean)</option>
            </select>
        </div>

    </div>

    {{-- Offcanvas Footer Actions --}}
    <div class="offcanvas-footer p-3 border-top bg-light d-flex flex-column gap-2">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-primary btn-sm flex-grow-1 rounded-pill fw-bold py-2 shadow-sm" onclick="saveThemePreferences(false)">
                <i class="fa-solid fa-circle-check me-1"></i> প্রোফাইলে সংরক্ষণ
            </button>
            @if(auth()->user() && auth()->user()->isAdmin())
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill fw-bold py-2" onclick="saveThemePreferences(true)" title="Save as global default for all admins">
                    <i class="fa-solid fa-globe me-1"></i> গ্লোবাল ডিফল্ট
                </button>
            @endif
        </div>
        <button type="button" class="btn btn-link btn-sm text-danger text-decoration-none py-1 fw-semibold" onclick="resetThemeDefaults()">
            <i class="fa-solid fa-rotate-left me-1"></i> ফ্যাক্টরি ডিফল্টে রিস্টোর করুন
        </button>
    </div>
</div>

<script>
    function applyPresetPalette(primary, secondary, accent, sidebar) {
        AdminTheme.setColors(primary, secondary, accent, true);
        if (sidebar) {
            AdminTheme.setSidebarTheme(sidebar, true);
        }
        document.getElementById('drawerPrimaryColorPicker').value = primary;
        document.getElementById('drawerPrimaryColorHex').value = primary;
        document.getElementById('drawerSecondaryColorPicker').value = secondary;
        document.getElementById('drawerSecondaryColorHex').value = secondary;
        updateCustomizerActiveState();
    }

    function onCustomColorChange(type, value) {
        if (!value) return;
        if (type === 'primary') {
            document.getElementById('drawerPrimaryColorPicker').value = value;
            document.getElementById('drawerPrimaryColorHex').value = value;
            AdminTheme.setColors(value, null, null, true);
        } else if (type === 'secondary') {
            document.getElementById('drawerSecondaryColorPicker').value = value;
            document.getElementById('drawerSecondaryColorHex').value = value;
            AdminTheme.setColors(null, value, null, true);
        }
    }

    function updateCustomizerActiveState() {
        var currentMode = AdminTheme.state.mode;
        var currentSidebar = AdminTheme.state.sidebar;

        document.querySelectorAll('[data-mode-btn]').forEach(function(btn) {
            if (btn.getAttribute('data-mode-btn') === currentMode) {
                btn.className = 'btn btn-outline-secondary w-100 p-2.5 rounded-3 text-center border d-flex flex-column align-items-center gap-1.5 border-primary bg-primary-subtle text-primary fw-bold';
            } else {
                btn.className = 'btn btn-outline-secondary w-100 p-2.5 rounded-3 text-center border d-flex flex-column align-items-center gap-1.5';
            }
        });

        document.querySelectorAll('[data-sidebar-btn]').forEach(function(btn) {
            if (btn.getAttribute('data-sidebar-btn') === currentSidebar) {
                btn.classList.add('border-primary', 'bg-primary-subtle', 'fw-bold');
            } else {
                btn.classList.remove('border-primary', 'bg-primary-subtle', 'fw-bold');
            }
        });
    }

    function saveThemePreferences(saveGlobal) {
        AdminTheme.saveToServer(saveGlobal, function(data) {
            if (window.SwalToast) {
                window.SwalToast('success', data.message || 'থিম সেটিংস সফলভাবে সংরক্ষিত হয়েছে!');
            }
            var offcanvasEl = document.getElementById('admThemeCustomizerDrawer');
            var bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
            if (bsOffcanvas) bsOffcanvas.hide();
        });
    }

    function resetThemeDefaults() {
        if (confirm('আপনি কি নিশ্চিত যে থিম সেটিংস ফ্যাক্টরি ডিফল্টে ফিরিয়ে নিতে চান?')) {
            AdminTheme.resetDefaults(true, function(data) {
                document.getElementById('drawerPrimaryColorPicker').value = '#0066cc';
                document.getElementById('drawerPrimaryColorHex').value = '#0066cc';
                document.getElementById('drawerSecondaryColorPicker').value = '#0099ff';
                document.getElementById('drawerSecondaryColorHex').value = '#0099ff';
                document.getElementById('drawerFontSelect').value = 'Hind Siliguri';
                updateCustomizerActiveState();
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateCustomizerActiveState();
    });
</script>
