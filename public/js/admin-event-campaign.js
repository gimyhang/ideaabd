/**
 * Admin Event Campaign Management & Participant Interactive Script
 * Provides instant live table search, bulk actions, dynamic ajax toggles & toast feedback.
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Toast Notification Helper
    const toastEl = document.getElementById('liveToast');
    const toastMsg = document.getElementById('toastMessage');
    const toast = toastEl ? new bootstrap.Toast(toastEl, { delay: 3500 }) : null;

    window.showToast = function (msg, isError = false) {
        if (!toastEl || !toastMsg) {
            alert(msg);
            return;
        }
        toastEl.className = isError 
            ? 'toast align-items-center text-bg-danger border-0 rounded-4 shadow'
            : 'toast align-items-center text-bg-dark border-0 rounded-4 shadow';
        toastMsg.innerHTML = msg;
        toast.show();
    };

    // 1.1 Robust CSRF Token Management & Auto-Refresh
    async function getCsrfToken() {
        let token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!token) {
            const inputToken = document.querySelector('input[name="_token"]')?.value;
            if (inputToken) token = inputToken;
        }
        return token || '';
    }

    async function refreshCsrfToken() {
        try {
            const res = await fetch('/auth/csrf-token', {
                headers: { 'Accept': 'application/json' }
            });
            if (res.ok) {
                const data = await res.json();
                if (data && data.csrf_token) {
                    const meta = document.querySelector('meta[name="csrf-token"]');
                    if (meta) meta.setAttribute('content', data.csrf_token);
                    document.querySelectorAll('input[name="_token"]').forEach(input => {
                        input.value = data.csrf_token;
                    });
                    return data.csrf_token;
                }
            }
        } catch (e) {
            console.warn('Could not refresh CSRF token:', e);
        }
        return getCsrfToken();
    }

    async function fetchWithCsrf(url, options = {}) {
        options.headers = options.headers || {};
        let token = await getCsrfToken();
        options.headers['X-CSRF-TOKEN'] = token;
        options.headers['Accept'] = 'application/json';
        options.headers['X-Requested-With'] = 'XMLHttpRequest';

        if (options.body instanceof FormData && token) {
            options.body.set('_token', token);
        }

        let response = await fetch(url, options);

        // Auto-retry once on 419 (CSRF token expired)
        if (response.status === 419) {
            console.warn('CSRF token expired (419). Auto-refreshing token and retrying...');
            token = await refreshCsrfToken();
            options.headers['X-CSRF-TOKEN'] = token;
            if (options.body instanceof FormData && token) {
                options.body.set('_token', token);
            }
            response = await fetch(url, options);
        }

        return response;
    }

    // Keep session / CSRF token alive every 10 minutes
    setInterval(refreshCsrfToken, 10 * 60 * 1000);

    // 2. Instant Copy-to-Clipboard
    document.querySelectorAll('.copy-btn, .copy-slug-btn').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const textToCopy = this.getAttribute('data-url') || this.getAttribute('data-copy') || this.textContent.trim();
            if (!textToCopy) return;

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textToCopy).then(() => {
                    window.showToast('<i class="fa-solid fa-circle-check text-success me-1"></i> Copied: ' + textToCopy);
                }).catch(() => fallbackCopy(textToCopy));
            } else {
                fallbackCopy(textToCopy);
            }
        });
    });

    function fallbackCopy(text) {
        const temp = document.createElement('textarea');
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        window.showToast('<i class="fa-solid fa-circle-check text-success me-1"></i> Copied: ' + text);
    }

    // 3. Client-Side Instant Live Search across Table Rows
    const liveSearchInput = document.getElementById('tableLiveSearch');
    const tableRows = document.querySelectorAll('#participantsTable tbody tr.participant-row');
    const noResultsRow = document.getElementById('noLiveResultsRow');
    const matchCountBadge = document.getElementById('liveMatchCount');

    if (liveSearchInput && tableRows.length > 0) {
        liveSearchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            let visibleCount = 0;

            tableRows.forEach(row => {
                const rowText = row.getAttribute('data-search-text') || row.innerText.toLowerCase();
                if (!query || rowText.includes(query)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (noResultsRow) {
                noResultsRow.style.display = (visibleCount === 0 && query !== '') ? '' : 'none';
            }

            if (matchCountBadge) {
                matchCountBadge.textContent = visibleCount + ' matching';
                matchCountBadge.style.display = query ? 'inline-block' : 'none';
            }
        });
    }

    // 4. Bulk Selection Checkboxes & Floating Action Bar
    const selectAllCheckbox = document.getElementById('selectAllParticipants');
    const rowCheckboxes = document.querySelectorAll('.participant-select-check');
    const bulkBar = document.getElementById('bulkActionBar');
    const selectedCountSpan = document.getElementById('bulkSelectedCount');

    function updateBulkBar() {
        const selected = document.querySelectorAll('.participant-select-check:checked');
        const count = selected.length;

        if (selectedCountSpan) {
            selectedCountSpan.textContent = count;
        }

        if (bulkBar) {
            if (count > 0) {
                bulkBar.classList.add('visible');
            } else {
                bulkBar.classList.remove('visible');
            }
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const isChecked = this.checked;
            rowCheckboxes.forEach(cb => {
                // only check currently visible rows
                const row = cb.closest('tr');
                if (row && row.style.display !== 'none') {
                    cb.checked = isChecked;
                }
            });
            updateBulkBar();
        });
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            if (!this.checked && selectAllCheckbox) {
                selectAllCheckbox.checked = false;
            }
            updateBulkBar();
        });
    });

    // 5. AJAX Toggle Active Status for Campaign
    const toggleStatusBtn = document.getElementById('btnToggleCampaignStatus');
    if (toggleStatusBtn) {
        toggleStatusBtn.addEventListener('click', async function () {
            const url = this.getAttribute('data-url');
            if (!url) return;

            this.disabled = true;
            try {
                const res = await fetchWithCsrf(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                toggleStatusBtn.disabled = false;
                if (data.success) {
                    window.showToast('<i class="fa-solid fa-circle-check text-success me-1"></i> ' + data.message);
                    setTimeout(() => location.reload(), 600);
                } else {
                    window.showToast(data.message || 'Status update failed', true);
                }
            } catch (err) {
                toggleStatusBtn.disabled = false;
                console.error(err);
                window.showToast('Network error updating status', true);
            }
        });
    }

    // 6. AJAX Quick Title and Slug Form
    const editTitleForm = document.getElementById('editTitleForm');
    if (editTitleForm) {
        editTitleForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const submitBtn = document.getElementById('saveTitleBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
            }

            const formData = new FormData(editTitleForm);
            try {
                const res = await fetchWithCsrf(editTitleForm.action, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Save Title';
                }
                if (data.success) {
                    const titleModalEl = document.getElementById('editTitleModal');
                    const modalInstance = bootstrap.Modal.getInstance(titleModalEl);
                    if (modalInstance) modalInstance.hide();

                    const headerTitle = document.getElementById('headerTitle');
                    if (headerTitle) headerTitle.textContent = data.title;

                    const breadcrumbTitle = document.getElementById('breadcrumbTitle');
                    if (breadcrumbTitle) breadcrumbTitle.textContent = data.title;

                    const urlText = document.getElementById('publicUrlText');
                    if (urlText && data.public_url) urlText.textContent = data.public_url;

                    window.showToast('<i class="fa-solid fa-circle-check text-success me-1"></i> ' + (data.message || 'Title updated successfully'));
                } else {
                    window.showToast(data.message || 'Validation error', true);
                }
            } catch (err) {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Save Title';
                }
                console.error(err);
                window.showToast('Error saving title. Please try again.', true);
            }
        });
    }

    // Helper: Canvas Image Auto-Optimizer (High-speed Client-side Lossless/High-fidelity Compression)
    function optimizeImageFile(file, maxWidth, maxHeight, quality, mimeType = 'image/jpeg') {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = new Image();
                img.onload = function () {
                    let width = img.width;
                    let height = img.height;

                    if (width > maxWidth || height > maxHeight) {
                        const ratio = Math.min(maxWidth / width, maxHeight / height);
                        width = Math.round(width * ratio);
                        height = Math.round(height * ratio);
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    
                    // Transparent canvas support for PNG/WebP logos and objects
                    if (mimeType === 'image/jpeg') {
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, width, height);
                    } else {
                        ctx.clearRect(0, 0, width, height);
                    }
                    
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob((blob) => {
                        if (!blob) {
                            resolve({ file: file, dataUrl: e.target.result, originalSize: file.size, optimizedSize: file.size });
                            return;
                        }
                        const ext = mimeType === 'image/png' ? '.png' : (mimeType === 'image/webp' ? '.webp' : '.jpg');
                        const baseName = file.name.substring(0, file.name.lastIndexOf('.')) || file.name;
                        const newFileName = baseName + ext;
                        const optimizedFile = new File([blob], newFileName, {
                            type: mimeType,
                            lastModified: Date.now()
                        });
                        const dataUrl = canvas.toDataURL(mimeType, quality);
                        resolve({
                            file: optimizedFile,
                            dataUrl: dataUrl,
                            originalSize: file.size,
                            optimizedSize: blob.size,
                            width: width,
                            height: height
                        });
                    }, mimeType, quality);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    function formatFileSize(bytes) {
        if (!bytes || bytes <= 0) return '0 B';
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(2) + ' MB';
    }

    // 7. Card Design Live Studio Controller (World-Class Dynamic Customizer)
    const adminLiveCard = document.getElementById('adminLiveCard');
    if (adminLiveCard) {
        // A. Background Image Upload & Auto-Optimization
        const bgInput = document.getElementById('liveInputBgImage');
        const removeBgCheck = document.getElementById('removeBgCheck');
        const bgColorInput = document.getElementById('liveInputBgColor');
        const bgColorText = document.getElementById('liveInputBgColorText');
        const plateColorInput = document.getElementById('liveInputPlateColor');
        const plateColorText = document.getElementById('liveInputPlateColorText');
        const liveNamePlate = document.getElementById('liveNamePlate');
        const bgOptimizeBadge = document.getElementById('bgOptimizeBadge');
        const dropzoneText = document.getElementById('dropzoneText');

        let uploadedBgUrl = null;
        let originalBgStyle = adminLiveCard.style.background;

        if (bgInput) {
            bgInput.addEventListener('change', async function () {
                if (this.files && this.files[0]) {
                    const rawFile = this.files[0];
                    if (dropzoneText) dropzoneText.textContent = 'অপটিমাইজেশন চলছে...';

                    try {
                        const opt = await optimizeImageFile(rawFile, 1600, 2200, 0.88, 'image/jpeg');
                        
                        // Replace form input file with optimized Blob
                        const dt = new DataTransfer();
                        dt.items.add(opt.file);
                        bgInput.files = dt.files;

                        uploadedBgUrl = opt.dataUrl;
                        adminLiveCard.style.background = `url('${uploadedBgUrl}') no-repeat center center / cover`;
                        
                        if (removeBgCheck) removeBgCheck.checked = false;
                        document.querySelectorAll('.theme-preset-btn').forEach(btn => btn.classList.remove('active'));

                        if (dropzoneText) dropzoneText.textContent = rawFile.name;
                        if (bgOptimizeBadge) {
                            bgOptimizeBadge.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;"><i class="fa-solid fa-bolt me-1"></i> অপটিমাইজড: ${formatFileSize(opt.originalSize)} ➔ ${formatFileSize(opt.optimizedSize)}</span>`;
                            bgOptimizeBadge.style.display = 'block';
                        }
                    } catch (err) {
                        console.error('Image optimization fallback:', err);
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            uploadedBgUrl = e.target.result;
                            adminLiveCard.style.background = `url('${uploadedBgUrl}') no-repeat center center / cover`;
                        };
                        reader.readAsDataURL(rawFile);
                    }
                }
            });
        }

        if (removeBgCheck) {
            removeBgCheck.addEventListener('change', function () {
                if (this.checked) {
                    const color = bgColorInput ? bgColorInput.value : '#c98c21';
                    adminLiveCard.style.background = `${color} linear-gradient(145deg, #c4871e 0%, #db9e2a 45%, #b57a15 100%)`;
                } else {
                    if (uploadedBgUrl) {
                        adminLiveCard.style.background = `url('${uploadedBgUrl}') no-repeat center center / cover`;
                    } else {
                        adminLiveCard.style.background = originalBgStyle;
                    }
                }
            });
        }

        // A.1 Logo Upload, Auto-Optimization & Remove
        const logoInput = document.getElementById('liveInputLogoImage');
        const removeLogoCheck = document.getElementById('removeLogoCheck');
        const logoOptimizeBadge = document.getElementById('logoOptimizeBadge');
        const logoDropzoneText = document.getElementById('logoDropzoneText');
        const liveCustomLogoImg = document.getElementById('liveCustomLogoImg');
        const liveDefaultLogoRing = document.getElementById('liveDefaultLogoRing');
        const liveLogoWrap = document.getElementById('liveLogoWrap');
        let uploadedLogoUrl = null;

        if (logoInput) {
            logoInput.addEventListener('change', async function () {
                if (this.files && this.files[0]) {
                    const rawFile = this.files[0];
                    if (logoDropzoneText) logoDropzoneText.textContent = 'লোগো অপটিমাইজেশন চলছে...';

                    try {
                        const opt = await optimizeImageFile(rawFile, 400, 400, 0.92, 'image/png');
                        
                        const dt = new DataTransfer();
                        dt.items.add(opt.file);
                        logoInput.files = dt.files;

                        uploadedLogoUrl = opt.dataUrl;
                        if (liveCustomLogoImg) {
                            liveCustomLogoImg.src = uploadedLogoUrl;
                            liveCustomLogoImg.style.display = 'block';
                        }
                        if (liveDefaultLogoRing) {
                            liveDefaultLogoRing.style.display = 'none';
                        }

                        if (removeLogoCheck) removeLogoCheck.checked = false;
                        if (logoDropzoneText) logoDropzoneText.textContent = rawFile.name;
                        if (logoOptimizeBadge) {
                            logoOptimizeBadge.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;"><i class="fa-solid fa-bolt me-1"></i> লোগো অপটিমাইজড: ${formatFileSize(opt.originalSize)} ➔ ${formatFileSize(opt.optimizedSize)}</span>`;
                            logoOptimizeBadge.style.display = 'block';
                        }
                    } catch (err) {
                        console.error('Logo optimization fallback:', err);
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            uploadedLogoUrl = e.target.result;
                            if (liveCustomLogoImg) {
                                liveCustomLogoImg.src = uploadedLogoUrl;
                                liveCustomLogoImg.style.display = 'block';
                            }
                            if (liveDefaultLogoRing) liveDefaultLogoRing.style.display = 'none';
                        };
                        reader.readAsDataURL(rawFile);
                    }
                }
            });
        }

        if (removeLogoCheck) {
            removeLogoCheck.addEventListener('change', function () {
                if (this.checked) {
                    if (liveCustomLogoImg) liveCustomLogoImg.style.display = 'none';
                    if (liveDefaultLogoRing) liveDefaultLogoRing.style.display = '';
                } else {
                    if (uploadedLogoUrl || (liveCustomLogoImg && liveCustomLogoImg.getAttribute('src'))) {
                        if (liveCustomLogoImg) liveCustomLogoImg.style.display = 'block';
                        if (liveDefaultLogoRing) liveDefaultLogoRing.style.display = 'none';
                    }
                }
            });
        }

        // A.2 Logo Size Slider
        const logoSlider = document.getElementById('logoSizeSlider');
        const logoBadge = document.getElementById('logoSizeBadge');
        if (logoSlider && liveLogoWrap) {
            logoSlider.addEventListener('input', function () {
                const val = this.value;
                if (logoBadge) logoBadge.textContent = val + ' px';
                liveLogoWrap.style.width = val + 'px';
                liveLogoWrap.style.height = val + 'px';
            });
        }

        // A.2.5 EVENT LOGO (রংপুর সাহিত্য উৎসব - হেডার লোগো)
        const eventLogoInput = document.getElementById('liveInputEventLogoImage');
        const removeEventLogoCheck = document.getElementById('removeEventLogoCheck');
        const eventLogoOptimizeBadge = document.getElementById('eventLogoOptimizeBadge');
        const eventLogoDropzoneText = document.getElementById('eventLogoDropzoneText');
        const liveCustomEventLogoImg = document.getElementById('liveCustomEventLogoImg');
        const liveEventLogoWrap = document.getElementById('liveEventLogoWrap');
        const eventLogoSlider = document.getElementById('eventLogoSizeSlider');
        const eventLogoBadge = document.getElementById('eventLogoSizeBadge');
        const toggleShowEventLogo = document.getElementById('toggleShowEventLogo');
        let uploadedEventLogoUrl = null;

        if (eventLogoInput) {
            eventLogoInput.addEventListener('change', async function () {
                if (this.files && this.files[0]) {
                    const rawFile = this.files[0];
                    if (eventLogoDropzoneText) eventLogoDropzoneText.textContent = 'ইভেন্ট লোগো অপটিমাইজেশন চলছে...';

                    try {
                        const opt = await optimizeImageFile(rawFile, 600, 300, 0.95, 'image/png');
                        const dt = new DataTransfer();
                        dt.items.add(opt.file);
                        eventLogoInput.files = dt.files;

                        uploadedEventLogoUrl = opt.dataUrl;
                        if (liveCustomEventLogoImg) {
                            liveCustomEventLogoImg.src = uploadedEventLogoUrl;
                            liveCustomEventLogoImg.style.display = 'block';
                        }
                        if (liveEventLogoWrap) {
                            liveEventLogoWrap.style.display = 'flex';
                        }
                        if (toggleShowEventLogo) toggleShowEventLogo.checked = true;
                        if (removeEventLogoCheck) removeEventLogoCheck.checked = false;
                        if (eventLogoDropzoneText) eventLogoDropzoneText.textContent = rawFile.name;
                        if (eventLogoOptimizeBadge) {
                            eventLogoOptimizeBadge.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;"><i class="fa-solid fa-bolt me-1"></i> ইভেন্ট লোগো অপটিমাইজড: ${formatFileSize(opt.originalSize)} ➔ ${formatFileSize(opt.optimizedSize)}</span>`;
                            eventLogoOptimizeBadge.style.display = 'block';
                        }
                    } catch (err) {
                        console.error('Event logo optimization fallback:', err);
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            uploadedEventLogoUrl = e.target.result;
                            if (liveCustomEventLogoImg) {
                                liveCustomEventLogoImg.src = uploadedEventLogoUrl;
                                liveCustomEventLogoImg.style.display = 'block';
                            }
                            if (liveEventLogoWrap) liveEventLogoWrap.style.display = 'flex';
                        };
                        reader.readAsDataURL(rawFile);
                    }
                }
            });
        }

        if (removeEventLogoCheck) {
            removeEventLogoCheck.addEventListener('change', function () {
                if (this.checked) {
                    if (liveEventLogoWrap) liveEventLogoWrap.style.display = 'none';
                    if (liveCustomEventLogoImg) liveCustomEventLogoImg.style.display = 'none';
                } else {
                    if (uploadedEventLogoUrl || (liveCustomEventLogoImg && liveCustomEventLogoImg.getAttribute('src'))) {
                        if (liveCustomEventLogoImg) liveCustomEventLogoImg.style.display = 'block';
                        if (liveEventLogoWrap) liveEventLogoWrap.style.display = 'flex';
                    }
                }
            });
        }

        if (eventLogoSlider && liveCustomEventLogoImg) {
            eventLogoSlider.addEventListener('input', function () {
                const val = this.value;
                if (eventLogoBadge) eventLogoBadge.textContent = val + ' px';
                liveCustomEventLogoImg.style.maxHeight = val + 'px';
            });
        }

        if (toggleShowEventLogo && liveEventLogoWrap) {
            toggleShowEventLogo.addEventListener('change', function () {
                liveEventLogoWrap.style.display = this.checked ? 'flex' : 'none';
            });
        }

        // =========================================================================
        // A.3 CUSTOM OBJECTS / STICKERS / WATERMARK STUDIO ENGINE
        // =========================================================================
        const customObjectsInput = document.getElementById('customObjectsJsonInput');
        const cardObjectsLayer = document.getElementById('adminCardObjectsLayer');
        const objectFileInput = document.getElementById('liveInputObjectFile');
        const objectDropzoneText = document.getElementById('objectDropzoneText');
        const activeObjectControlsBox = document.getElementById('activeObjectControlsBox');
        const selectedObjNameDisplay = document.getElementById('selectedObjNameDisplay');
        const btnDeleteSelectedObj = document.getElementById('btnDeleteSelectedObj');
        const objSizeSlider = document.getElementById('objSizeSlider');
        const objSizeBadge = document.getElementById('objSizeBadge');
        const objOpacitySlider = document.getElementById('objOpacitySlider');
        const objOpacityBadge = document.getElementById('objOpacityBadge');
        const objRotationSlider = document.getElementById('objRotationSlider');
        const objRotationBadge = document.getElementById('objRotationBadge');
        const btnObjLayerUp = document.getElementById('btnObjLayerUp');
        const btnObjLayerDown = document.getElementById('btnObjLayerDown');
        const objectsListItemsContainer = document.getElementById('objectsListItemsContainer');
        const objectsCountBadge = document.getElementById('objectsCountBadge');
        const noObjectsText = document.getElementById('noObjectsText');

        let customObjects = [];
        let selectedObjectId = null;

        // Parse initial objects
        if (customObjectsInput && customObjectsInput.value) {
            try {
                const parsed = JSON.parse(customObjectsInput.value);
                if (Array.isArray(parsed)) customObjects = parsed;
            } catch (e) {
                console.warn('Could not parse initial custom objects:', e);
            }
        }

        // Preset SVG Generators
        const presetSvgLibrary = {
            seal: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="45" fill="#f59e0b" stroke="#ca8a04" stroke-width="4"/><circle cx="50" cy="50" r="38" fill="none" stroke="#ffffff" stroke-width="2" stroke-dasharray="4,4"/><path d="M50 20 L58 36 L76 38 L62 50 L66 68 L50 58 L34 68 L38 50 L24 38 L42 36 Z" fill="#ffffff"/></svg>`,
            vip: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M50 10 L85 25 L85 65 L50 90 L15 65 L15 25 Z" fill="#ef4444" stroke="#991b1b" stroke-width="3"/><text x="50" y="58" font-size="24" font-weight="bold" fill="#ffffff" text-anchor="middle" font-family="sans-serif">VIP</text></svg>`,
            ribbon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M20 10 L80 10 L80 80 L50 60 L20 80 Z" fill="#0284c7" stroke="#0369a1" stroke-width="3"/><circle cx="50" cy="35" r="18" fill="#ffffff"/><path d="M50 24 L54 32 L63 33 L56 39 L58 48 L50 43 L42 48 L44 39 L37 33 L46 32 Z" fill="#0284c7"/></svg>`,
            book: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M50 25 C35 15 15 20 10 25 L10 75 C15 70 35 65 50 75 C65 65 85 70 90 75 L90 25 C85 20 65 15 50 25 Z" fill="#ffffff" stroke="#10b981" stroke-width="3"/><path d="M50 25 L50 75" stroke="#10b981" stroke-width="3"/></svg>`,
            quill: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M80 15 C45 25 35 55 25 85 C28 75 35 65 45 60 C55 55 75 35 80 15 Z" fill="#06b6d4" stroke="#0891b2" stroke-width="2"/><path d="M25 85 L20 90 L27 86 Z" fill="#0891b2"/></svg>`,
            verified: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="42" fill="#0284c7"/><path d="M30 50 L44 64 L70 36" stroke="#ffffff" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>`,
            star: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M50 12 L61 35 L86 38 L67 56 L72 81 L50 69 L28 81 L33 56 L14 38 L39 35 Z" fill="#f59e0b" stroke="#d97706" stroke-width="3"/></svg>`,
            stamp: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="44" fill="none" stroke="#334155" stroke-width="4"/><circle cx="50" cy="50" r="36" fill="none" stroke="#334155" stroke-width="2" stroke-dasharray="5,3"/><text x="50" y="55" font-size="14" font-weight="bold" fill="#334155" text-anchor="middle" font-family="sans-serif">APPROVED</text></svg>`
        };

        function svgToDataUrl(svgString) {
            return 'data:image/svg+xml;utf8,' + encodeURIComponent(svgString);
        }

        function syncCustomObjectsState() {
            if (customObjectsInput) {
                customObjectsInput.value = JSON.stringify(customObjects);
            }
            if (objectsCountBadge) {
                objectsCountBadge.textContent = customObjects.length;
            }
            renderObjectsListSidebar();
        }

        function renderObjectsListSidebar() {
            if (!objectsListItemsContainer) return;
            objectsListItemsContainer.innerHTML = '';

            if (customObjects.length === 0) {
                if (noObjectsText) noObjectsText.style.display = 'block';
                if (activeObjectControlsBox) activeObjectControlsBox.style.display = 'none';
                return;
            }

            if (noObjectsText) noObjectsText.style.display = 'none';

            customObjects.forEach(obj => {
                const item = document.createElement('div');
                item.className = 'object-item-card d-flex align-items-center justify-content-between p-2' + (obj.id === selectedObjectId ? ' active' : '');
                item.style.cursor = 'pointer';
                item.innerHTML = `
                    <div class="d-flex align-items-center gap-2">
                        <img src="${obj.url}" style="width: 28px; height: 28px; object-fit: contain; border-radius: 6px; border: 1px solid #cbd5e1; background: #f8fafc;">
                        <div>
                            <div class="fw-bold small text-dark" style="font-size: 11px;">${obj.name || 'অবজেক্ট'}</div>
                            <small class="text-muted" style="font-size: 9.5px;">সাইজ: ${obj.width}px | অস্বচ্ছতা: ${Math.round(obj.opacity * 100)}%</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1.5 rounded-pill btn-del-obj-item" data-id="${obj.id}" title="মুছুন">
                        <i class="fa-solid fa-trash" style="font-size: 10px;"></i>
                    </button>
                `;

                item.addEventListener('click', function (e) {
                    if (e.target.closest('.btn-del-obj-item')) return;
                    selectCustomObject(obj.id);
                });

                const delBtn = item.querySelector('.btn-del-obj-item');
                if (delBtn) {
                    delBtn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        deleteCustomObject(obj.id);
                    });
                }

                objectsListItemsContainer.appendChild(item);
            });
        }

        function renderCardObjects() {
            if (!cardObjectsLayer) return;
            cardObjectsLayer.innerHTML = '';

            customObjects.forEach(obj => {
                const el = document.createElement('div');
                el.className = 'card-custom-object' + (obj.id === selectedObjectId ? ' selected' : '');
                el.id = 'canvas_obj_' + obj.id;
                el.style.left = (obj.x || 50) + '%';
                el.style.top = (obj.y || 50) + '%';
                el.style.width = (obj.width || 60) + 'px';
                el.style.height = (obj.width || 60) + 'px';
                el.style.transform = `translate(-50%, -50%) rotate(${obj.rotation || 0}deg)`;
                el.style.opacity = obj.opacity !== undefined ? obj.opacity : 1;
                el.style.zIndex = obj.zIndex || 10;

                el.innerHTML = `
                    <img src="${obj.url}" alt="${obj.name || 'Object'}">
                    <div class="object-delete-bubble" title="অবজেক্ট মুছুন"><i class="fa-solid fa-xmark"></i></div>
                `;

                // Interactive selection & drag
                initDraggableObject(el, obj);

                const delBubble = el.querySelector('.object-delete-bubble');
                if (delBubble) {
                    delBubble.addEventListener('pointerdown', function (e) {
                        e.stopPropagation();
                    });
                    delBubble.addEventListener('click', function (e) {
                        e.stopPropagation();
                        deleteCustomObject(obj.id);
                    });
                }

                cardObjectsLayer.appendChild(el);
            });

            syncCustomObjectsState();
        }

        function initDraggableObject(el, obj) {
            let isDragging = false;
            let startX, startY;
            let initialObjX, initialObjY;

            el.addEventListener('pointerdown', function (e) {
                if (e.target.closest('.object-delete-bubble')) return;
                isDragging = true;
                el.setPointerCapture(e.pointerId);
                selectCustomObject(obj.id);

                startX = e.clientX;
                startY = e.clientY;
                initialObjX = obj.x;
                initialObjY = obj.y;
                e.stopPropagation();
            });

            el.addEventListener('pointermove', function (e) {
                if (!isDragging) return;
                const cardRect = adminLiveCard.getBoundingClientRect();
                if (cardRect.width === 0 || cardRect.height === 0) return;

                const deltaX = e.clientX - startX;
                const deltaY = e.clientY - startY;

                const deltaXPercent = (deltaX / cardRect.width) * 100;
                const deltaYPercent = (deltaY / cardRect.height) * 100;

                let newX = Math.max(2, Math.min(98, initialObjX + deltaXPercent));
                let newY = Math.max(2, Math.min(98, initialObjY + deltaYPercent));

                obj.x = parseFloat(newX.toFixed(2));
                obj.y = parseFloat(newY.toFixed(2));

                el.style.left = obj.x + '%';
                el.style.top = obj.y + '%';
            });

            function endDrag(e) {
                if (isDragging) {
                    isDragging = false;
                    try { el.releasePointerCapture(e.pointerId); } catch (err) {}
                    syncCustomObjectsState();
                }
            }

            el.addEventListener('pointerup', endDrag);
            el.addEventListener('pointercancel', endDrag);
        }

        function selectCustomObject(id) {
            selectedObjectId = id;
            const obj = customObjects.find(o => o.id === id);

            document.querySelectorAll('.card-custom-object').forEach(node => {
                node.classList.toggle('selected', node.id === 'canvas_obj_' + id);
            });

            renderObjectsListSidebar();

            if (!obj || !activeObjectControlsBox) {
                if (activeObjectControlsBox) activeObjectControlsBox.style.display = 'none';
                return;
            }

            activeObjectControlsBox.style.display = 'block';
            if (selectedObjNameDisplay) selectedObjNameDisplay.textContent = obj.name || 'অবজেক্ট';

            if (objSizeSlider) {
                objSizeSlider.value = obj.width || 60;
                if (objSizeBadge) objSizeBadge.textContent = obj.width + 'px';
            }
            if (objOpacitySlider) {
                objOpacitySlider.value = Math.round((obj.opacity !== undefined ? obj.opacity : 1) * 100);
                if (objOpacityBadge) objOpacityBadge.textContent = Math.round((obj.opacity !== undefined ? obj.opacity : 1) * 100) + '%';
            }
            if (objRotationSlider) {
                objRotationSlider.value = obj.rotation || 0;
                if (objRotationBadge) objRotationBadge.textContent = (obj.rotation || 0) + '°';
            }
        }

        function deleteCustomObject(id) {
            customObjects = customObjects.filter(o => o.id !== id);
            if (selectedObjectId === id) {
                selectedObjectId = null;
                if (activeObjectControlsBox) activeObjectControlsBox.style.display = 'none';
            }
            renderCardObjects();
            window.showToast('<i class="fa-solid fa-trash text-danger me-1"></i> অবজেক্টটি মুছে ফেলা হয়েছে।');
        }

        // Object Sliders Event Listeners
        if (objSizeSlider) {
            objSizeSlider.addEventListener('input', function () {
                const obj = customObjects.find(o => o.id === selectedObjectId);
                if (obj) {
                    obj.width = parseInt(this.value);
                    if (objSizeBadge) objSizeBadge.textContent = obj.width + 'px';
                    const el = document.getElementById('canvas_obj_' + obj.id);
                    if (el) {
                        el.style.width = obj.width + 'px';
                        el.style.height = obj.width + 'px';
                    }
                    syncCustomObjectsState();
                }
            });
        }

        if (objOpacitySlider) {
            objOpacitySlider.addEventListener('input', function () {
                const obj = customObjects.find(o => o.id === selectedObjectId);
                if (obj) {
                    obj.opacity = parseFloat((this.value / 100).toFixed(2));
                    if (objOpacityBadge) objOpacityBadge.textContent = this.value + '%';
                    const el = document.getElementById('canvas_obj_' + obj.id);
                    if (el) el.style.opacity = obj.opacity;
                    syncCustomObjectsState();
                }
            });
        }

        if (objRotationSlider) {
            objRotationSlider.addEventListener('input', function () {
                const obj = customObjects.find(o => o.id === selectedObjectId);
                if (obj) {
                    obj.rotation = parseInt(this.value);
                    if (objRotationBadge) objRotationBadge.textContent = obj.rotation + '°';
                    const el = document.getElementById('canvas_obj_' + obj.id);
                    if (el) el.style.transform = `translate(-50%, -50%) rotate(${obj.rotation}deg)`;
                    syncCustomObjectsState();
                }
            });
        }

        if (btnDeleteSelectedObj) {
            btnDeleteSelectedObj.addEventListener('click', function () {
                if (selectedObjectId) deleteCustomObject(selectedObjectId);
            });
        }

        if (btnObjLayerUp) {
            btnObjLayerUp.addEventListener('click', function () {
                const obj = customObjects.find(o => o.id === selectedObjectId);
                if (obj) {
                    obj.zIndex = (obj.zIndex || 10) + 1;
                    const el = document.getElementById('canvas_obj_' + obj.id);
                    if (el) el.style.zIndex = obj.zIndex;
                    syncCustomObjectsState();
                }
            });
        }

        if (btnObjLayerDown) {
            btnObjLayerDown.addEventListener('click', function () {
                const obj = customObjects.find(o => o.id === selectedObjectId);
                if (obj) {
                    obj.zIndex = Math.max(1, (obj.zIndex || 10) - 1);
                    const el = document.getElementById('canvas_obj_' + obj.id);
                    if (el) el.style.zIndex = obj.zIndex;
                    syncCustomObjectsState();
                }
            });
        }

        // Preset Graphics Add Handler
        document.querySelectorAll('.btn-add-preset-obj').forEach(btn => {
            btn.addEventListener('click', function () {
                const type = this.getAttribute('data-type');
                const name = this.getAttribute('data-name');
                const svgContent = presetSvgLibrary[type] || presetSvgLibrary.seal;
                const dataUrl = svgToDataUrl(svgContent);

                const newObj = {
                    id: 'obj_' + Date.now() + '_' + Math.floor(Math.random() * 1000),
                    name: name,
                    type: type,
                    url: dataUrl,
                    x: 50 + (Math.random() * 10 - 5),
                    y: 45 + (Math.random() * 10 - 5),
                    width: 65,
                    opacity: 1,
                    rotation: 0,
                    zIndex: customObjects.length + 10
                };

                customObjects.push(newObj);
                renderCardObjects();
                selectCustomObject(newObj.id);
                window.showToast(`<i class="fa-solid fa-wand-magic-sparkles text-warning me-1"></i> "${name}" কার্ডে যুক্ত হয়েছে।`);
            });
        });

        // Object File Upload Dropzone
        if (objectFileInput) {
            objectFileInput.addEventListener('change', async function () {
                if (this.files && this.files[0]) {
                    const rawFile = this.files[0];
                    if (objectDropzoneText) objectDropzoneText.textContent = 'অবজেক্ট আপলোড হচ্ছে...';

                    const uploadUrl = document.getElementById('cardDesignCustomizerForm')?.getAttribute('data-upload-object-url');

                    try {
                        const opt = await optimizeImageFile(rawFile, 600, 600, 0.95, 'image/png');
                        let objectUrl = opt.dataUrl;

                        // Try server upload if route available
                        if (uploadUrl) {
                            const fd = new FormData();
                            fd.append('object_file', opt.file);
                            const res = await fetchWithCsrf(uploadUrl, {
                                method: 'POST',
                                body: fd
                            });
                            if (res.ok) {
                                const data = await res.json();
                                if (data.success && data.url) {
                                    objectUrl = data.url;
                                }
                            }
                        }

                        const newObj = {
                            id: 'obj_' + Date.now() + '_' + Math.floor(Math.random() * 1000),
                            name: rawFile.name.replace(/\.[^/.]+$/, ""),
                            type: 'custom_image',
                            url: objectUrl,
                            x: 50,
                            y: 50,
                            width: 75,
                            opacity: 1,
                            rotation: 0,
                            zIndex: customObjects.length + 10
                        };

                        customObjects.push(newObj);
                        renderCardObjects();
                        selectCustomObject(newObj.id);

                        if (objectDropzoneText) objectDropzoneText.textContent = rawFile.name;
                        window.showToast('<i class="fa-solid fa-circle-check text-success me-1"></i> নতুন অবজেক্ট আপলোড সম্পন্ন ও কার্ডে যুক্ত হয়েছে!');
                    } catch (err) {
                        console.error('Object upload failed:', err);
                        window.showToast('অবজেক্ট আপলোড ব্যর্থ হয়েছে।', true);
                        if (objectDropzoneText) objectDropzoneText.textContent = 'নতুন অবজেক্ট / ব্যাজ / স্টিকার আপলোড করুন';
                    }
                }
            });
        }

        // Initialize Card Objects Layer
        renderCardObjects();

        // B. Preset Theme Buttons
        const themePresetBtns = document.querySelectorAll('.theme-preset-btn');
        themePresetBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                themePresetBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const bgVal = this.getAttribute('data-bg');
                const plateVal = this.getAttribute('data-plate');

                if (bgColorInput) bgColorInput.value = bgVal;
                if (bgColorText) bgColorText.value = bgVal;
                if (plateColorInput) plateColorInput.value = plateVal;
                if (plateColorText) plateColorText.value = plateVal;

                if (liveNamePlate) liveNamePlate.style.background = plateVal;

                uploadedBgUrl = null;
                if (removeBgCheck) removeBgCheck.checked = true;
                adminLiveCard.style.background = `${bgVal} linear-gradient(145deg, ${bgVal} 0%, #1e1b4b 100%)`;
            });
        });

        // C. Color Pickers & Hex Inputs
        if (bgColorInput) {
            bgColorInput.addEventListener('input', function () {
                if (bgColorText) bgColorText.value = this.value;
                if (!uploadedBgUrl && (!removeBgCheck || removeBgCheck.checked || !originalBgStyle.includes('url('))) {
                    adminLiveCard.style.background = `${this.value} linear-gradient(145deg, ${this.value} 0%, #1e1b4b 100%)`;
                }
            });
        }
        if (bgColorText) {
            bgColorText.addEventListener('input', function () {
                if (bgColorInput) bgColorInput.value = this.value;
                if (!uploadedBgUrl && (!removeBgCheck || removeBgCheck.checked || !originalBgStyle.includes('url('))) {
                    adminLiveCard.style.background = `${this.value} linear-gradient(145deg, ${this.value} 0%, #1e1b4b 100%)`;
                }
            });
        }

        if (plateColorInput && liveNamePlate) {
            plateColorInput.addEventListener('input', function () {
                if (plateColorText) plateColorText.value = this.value;
                liveNamePlate.style.background = this.value;
            });
        }
        if (plateColorText && liveNamePlate) {
            plateColorText.addEventListener('input', function () {
                if (plateColorInput) plateColorInput.value = this.value;
                liveNamePlate.style.background = this.value;
            });
        }

        // D. Show / Hide Element Toggles
        const toggleInputs = document.querySelectorAll('.live-toggle-input');
        toggleInputs.forEach(toggle => {
            toggle.addEventListener('change', function () {
                const targetId = this.getAttribute('data-target');
                const targetEl = document.getElementById(targetId);
                if (targetEl) {
                    targetEl.style.display = this.checked ? '' : 'none';
                }
                
                // Manage parent section containers
                const headerGroup = document.getElementById('liveTopHeaderGroup');
                const badgeGroup = document.getElementById('liveBadgeWrap');
                const topSec = document.getElementById('liveTopSection');
                if (topSec && headerGroup && badgeGroup) {
                    const hasHeader = headerGroup.style.display !== 'none';
                    const hasBadge = badgeGroup.style.display !== 'none';
                    topSec.style.display = (hasHeader || hasBadge) ? '' : 'none';
                }

                const photoFrame = document.getElementById('livePhotoFrame');
                const namePlate = document.getElementById('liveNamePlate');
                const midSec = document.getElementById('liveMiddleSection');
                if (midSec && photoFrame && namePlate) {
                    const hasPhoto = photoFrame.style.display !== 'none';
                    const hasPlate = namePlate.style.display !== 'none';
                    midSec.style.display = (hasPhoto || hasPlate) ? '' : 'none';
                }

                const quoteEl = document.getElementById('liveQuoteText');
                const artEl = document.getElementById('liveArtworkWrap');
                const msgSec = document.getElementById('liveMessageSection');
                if (msgSec && quoteEl && artEl) {
                    const hasQuote = quoteEl.style.display !== 'none';
                    const hasArt = artEl.style.display !== 'none';
                    msgSec.style.display = (hasQuote || hasArt) ? '' : 'none';
                }
            });
        });

        // E. Sizing Controls (Range Sliders & Preset Buttons)
        const photoSlider = document.getElementById('photoSizeSlider');
        const photoBadge = document.getElementById('photoSizeBadge');
        const photoFrame = document.getElementById('livePhotoFrame');

        if (photoSlider && photoFrame) {
            photoSlider.addEventListener('input', function () {
                const val = this.value;
                if (photoBadge) photoBadge.textContent = val + ' px';
                photoFrame.style.width = val + 'px';
                photoFrame.style.height = val + 'px';
            });
        }

        document.querySelectorAll('.btn-preset-photo').forEach(btn => {
            btn.addEventListener('click', function () {
                const size = this.getAttribute('data-size');
                if (photoSlider) {
                    photoSlider.value = size;
                    photoSlider.dispatchEvent(new Event('input'));
                }
            });
        });

        const nameSlider = document.getElementById('nameSizeSlider');
        const nameBadge = document.getElementById('nameSizeBadge');
        const authorName = document.getElementById('liveAuthorName');

        if (nameSlider && authorName) {
            nameSlider.addEventListener('input', function () {
                const val = this.value;
                if (nameBadge) nameBadge.textContent = val + ' px';
                authorName.style.fontSize = val + 'px';
            });
        }

        document.querySelectorAll('.btn-preset-name').forEach(btn => {
            btn.addEventListener('click', function () {
                const size = this.getAttribute('data-size');
                if (nameSlider) {
                    nameSlider.value = size;
                    nameSlider.dispatchEvent(new Event('input'));
                }
            });
        });

        // E.1 Name Line Height Slider
        const nameLineHeightSlider = document.getElementById('nameLineHeightSlider');
        const nameLineHeightBadge = document.getElementById('nameLineHeightBadge');
        if (nameLineHeightSlider && authorName) {
            nameLineHeightSlider.addEventListener('input', function () {
                const val = this.value;
                if (nameLineHeightBadge) nameLineHeightBadge.textContent = val;
                authorName.style.lineHeight = val;
            });
        }

        // E.2 Name Bottom Spacing Slider
        const nameSpacingSlider = document.getElementById('nameSpacingSlider');
        const nameSpacingBadge = document.getElementById('nameSpacingBadge');
        if (nameSpacingSlider && authorName) {
            nameSpacingSlider.addEventListener('input', function () {
                const val = this.value;
                if (nameSpacingBadge) nameSpacingBadge.textContent = val + ' px';
                authorName.style.marginBottom = val + 'px';
            });
        }

        // E.3 Plate Padding Slider
        const platePaddingSlider = document.getElementById('platePaddingSlider');
        const platePaddingBadge = document.getElementById('platePaddingBadge');
        if (platePaddingSlider && liveNamePlate) {
            platePaddingSlider.addEventListener('input', function () {
                const val = parseInt(this.value, 10);
                if (platePaddingBadge) platePaddingBadge.textContent = val + ' px';
                const hasPhoto = photoFrame && photoFrame.style.display !== 'none';
                const topPad = hasPhoto ? (val + 8) : val;
                liveNamePlate.style.padding = `${topPad}px 8px ${val}px 8px`;
            });
        }

        // E.4 Direct Click-to-Edit & Floating Quick-Action Toolbar on Card Nodes
        let currentSelectedNode = null;
        let activeToolbar = null;

        const nodeControlsMap = {
            'photo': { sliderId: 'photoSizeSlider', toggleId: 'toggleShowPhoto', step: 4, name: 'ছবি' },
            'nameplate': { sliderId: 'nameSizeSlider', toggleId: 'toggleShowNamePlate', step: 2, name: 'নেমপ্লেট' },
            'logo': { sliderId: 'logoSizeSlider', toggleId: 'toggleShowLogo', step: 4, name: 'লোগো' },
            'badge': { toggleId: 'toggleShowBadge', name: 'আমন্ত্রণ ব্যাজ' },
            'header': { toggleId: 'toggleShowHeader', name: 'শিরোনাম' },
            'quote': { toggleId: 'toggleShowQuote', name: 'উক্তি' },
            'artwork': { toggleId: 'toggleShowArtwork', name: 'বই আর্টওয়ার্ক' },
            'organizers': { toggleId: 'toggleShowOrganizers', name: 'আয়োজকবৃন্দ' }
        };

        function removeActiveNodeSelection() {
            if (currentSelectedNode) {
                currentSelectedNode.classList.remove('card-node-selected');
                currentSelectedNode = null;
            }
            if (activeToolbar) {
                activeToolbar.remove();
                activeToolbar = null;
            }
        }

        function mountNodeToolbar(nodeEl) {
            removeActiveNodeSelection();

            const nodeType = nodeEl.getAttribute('data-node');
            const targetTab = nodeEl.getAttribute('data-tab');
            const config = nodeControlsMap[nodeType] || {};

            currentSelectedNode = nodeEl;
            nodeEl.classList.add('card-node-selected');

            // Build toolbar element
            const toolbar = document.createElement('div');
            toolbar.className = 'card-node-toolbar';

            let buttonsHtml = '';

            // Scale buttons if slider exists
            if (config.sliderId) {
                buttonsHtml += `
                    <button type="button" class="btn-node-scale-down" title="আকার ছোট করুন (-)">
                        <i class="fa-solid fa-minus"></i>
                    </button>
                    <button type="button" class="btn-node-scale-up" title="আকার বড় করুন (+)">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                `;
            }

            // Tab switch / settings button
            if (targetTab) {
                buttonsHtml += `
                    <button type="button" class="btn-node-edit" title="কাস্টমাইজ সেটিংসে যান">
                        <i class="fa-solid fa-sliders"></i>
                    </button>
                `;
            }

            // Delete / Hide button
            if (config.toggleId) {
                buttonsHtml += `
                    <button type="button" class="btn-node-del" title="কার্ড থেকে লুকান/বাদ দিন">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                `;
            }

            toolbar.innerHTML = buttonsHtml;
            nodeEl.appendChild(toolbar);
            activeToolbar = toolbar;

            // Attach toolbar button events
            if (config.sliderId) {
                const sliderEl = document.getElementById(config.sliderId);
                const btnUp = toolbar.querySelector('.btn-node-scale-up');
                const btnDown = toolbar.querySelector('.btn-node-scale-down');

                if (btnUp && sliderEl) {
                    btnUp.addEventListener('click', function (e) {
                        e.stopPropagation();
                        const current = parseFloat(sliderEl.value);
                        const max = parseFloat(sliderEl.max) || 120;
                        const step = config.step || 2;
                        sliderEl.value = Math.min(max, current + step);
                        sliderEl.dispatchEvent(new Event('input'));
                    });
                }

                if (btnDown && sliderEl) {
                    btnDown.addEventListener('click', function (e) {
                        e.stopPropagation();
                        const current = parseFloat(sliderEl.value);
                        const min = parseFloat(sliderEl.min) || 10;
                        const step = config.step || 2;
                        sliderEl.value = Math.max(min, current - step);
                        sliderEl.dispatchEvent(new Event('input'));
                    });
                }
            }

            // Go to settings tab
            const btnEdit = toolbar.querySelector('.btn-node-edit');
            if (btnEdit && targetTab) {
                btnEdit.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const tabTrigger = document.querySelector(`.nav-link[data-bs-target="#${targetTab}"]`);
                    if (tabTrigger) {
                        const tabInstance = bootstrap.Tab.getOrCreateInstance(tabTrigger);
                        tabInstance.show();
                    }
                });
            }

            // Hide / Delete element
            const btnDel = toolbar.querySelector('.btn-node-del');
            if (btnDel && config.toggleId) {
                btnDel.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const toggleInput = document.getElementById(config.toggleId);
                    if (toggleInput) {
                        toggleInput.checked = false;
                        toggleInput.dispatchEvent(new Event('change'));
                        removeActiveNodeSelection();
                        window.showToast(`<i class="fa-solid fa-eye-slash me-1"></i> ${config.name || 'আইটেম'} লুকানো হয়েছে। কাস্টমাইজার থেকে পুনরায় অন করতে পারবেন।`);
                    }
                });
            }
        }

        // Attach click listeners to all interactive nodes in the card
        document.querySelectorAll('.card-interactive-node').forEach(node => {
            node.addEventListener('click', function (e) {
                e.stopPropagation();
                mountNodeToolbar(this);
            });
        });

        // Clicking outside card clears selection
        document.addEventListener('click', function (e) {
            if (!e.target.closest('.card-interactive-node') && !e.target.closest('.card-node-toolbar')) {
                removeActiveNodeSelection();
            }
        });

        // F. Background Overlay & Blur Controls
        const bgOverlayOpacitySlider = document.getElementById('bgOverlayOpacitySlider');
        const bgOverlayOpacityBadge  = document.getElementById('bgOverlayOpacityBadge');
        const bgBlurSlider           = document.getElementById('bgBlurSlider');
        const bgBlurBadge            = document.getElementById('bgBlurBadge');
        const liveBgOverlay          = document.getElementById('liveBgOverlay');

        if (bgOverlayOpacitySlider && liveBgOverlay) {
            bgOverlayOpacitySlider.addEventListener('input', function () {
                const val = this.value;
                if (bgOverlayOpacityBadge) bgOverlayOpacityBadge.textContent = val + '%';
                liveBgOverlay.style.opacity = (parseFloat(val) / 100).toString();
            });
        }

        if (bgBlurSlider && liveBgOverlay) {
            bgBlurSlider.addEventListener('input', function () {
                const val = this.value;
                if (bgBlurBadge) bgBlurBadge.textContent = val + ' px';
                liveBgOverlay.style.backdropFilter = val > 0 ? `blur(${val}px)` : 'none';
            });
        }

        // G. Photo Studio & Filters Suite
        const photoBorderRadiusSelect = document.getElementById('photoBorderRadiusSelect');
        const photoBorderWidthSlider  = document.getElementById('photoBorderWidthSlider');
        const photoBorderWidthBadge   = document.getElementById('photoBorderWidthBadge');
        const photoBorderColorInput   = document.getElementById('liveInputPhotoBorderColor');
        const photoBorderColorText    = document.getElementById('liveInputPhotoBorderColorText');
        const photoShadowSelect       = document.getElementById('photoShadowSelect');

        const photoBrightnessSlider   = document.getElementById('photoBrightnessSlider');
        const photoBrightnessBadge    = document.getElementById('photoBrightnessBadge');
        const photoContrastSlider     = document.getElementById('photoContrastSlider');
        const photoContrastBadge      = document.getElementById('photoContrastBadge');
        const photoGrayscaleSlider    = document.getElementById('photoGrayscaleSlider');
        const photoGrayscaleBadge     = document.getElementById('photoGrayscaleBadge');
        const photoSepiaSlider        = document.getElementById('photoSepiaSlider');
        const photoSepiaBadge         = document.getElementById('photoSepiaBadge');

        if (photoBorderRadiusSelect && photoFrame) {
            photoBorderRadiusSelect.addEventListener('change', function () {
                photoFrame.style.borderRadius = this.value;
            });
        }

        if (photoBorderWidthSlider && photoFrame) {
            photoBorderWidthSlider.addEventListener('input', function () {
                const val = this.value;
                if (photoBorderWidthBadge) photoBorderWidthBadge.textContent = val + ' px';
                photoFrame.style.borderWidth = val + 'px';
            });
        }

        if (photoBorderColorInput && photoFrame) {
            photoBorderColorInput.addEventListener('input', function () {
                if (photoBorderColorText) photoBorderColorText.value = this.value;
                photoFrame.style.borderColor = this.value;
            });
        }

        if (photoBorderColorText && photoFrame) {
            photoBorderColorText.addEventListener('input', function () {
                if (photoBorderColorInput) photoBorderColorInput.value = this.value;
                photoFrame.style.borderColor = this.value;
            });
        }

        if (photoShadowSelect && photoFrame) {
            photoShadowSelect.addEventListener('change', function () {
                const map = {
                    'soft': '0 4px 12px rgba(0, 0, 0, 0.25)',
                    'deep': '0 10px 25px rgba(0, 0, 0, 0.45)',
                    'glow': '0 0 16px rgba(250, 204, 21, 0.65)',
                    'none': 'none'
                };
                photoFrame.style.boxShadow = map[this.value] || '0 4px 12px rgba(0, 0, 0, 0.25)';
            });
        }

        function updatePhotoFilters() {
            if (!photoFrame) return;
            const b = photoBrightnessSlider ? photoBrightnessSlider.value : 100;
            const c = photoContrastSlider ? photoContrastSlider.value : 100;
            const g = photoGrayscaleSlider ? photoGrayscaleSlider.value : 0;
            const s = photoSepiaSlider ? photoSepiaSlider.value : 0;

            if (photoBrightnessBadge) photoBrightnessBadge.textContent = b + '%';
            if (photoContrastBadge) photoContrastBadge.textContent = c + '%';
            if (photoGrayscaleBadge) photoGrayscaleBadge.textContent = g + '%';
            if (photoSepiaBadge) photoSepiaBadge.textContent = s + '%';

            photoFrame.style.filter = `brightness(${b}%) contrast(${c}%) grayscale(${g}%) sepia(${s}%)`;
        }

        [photoBrightnessSlider, photoContrastSlider, photoGrayscaleSlider, photoSepiaSlider].forEach(slider => {
            if (slider) slider.addEventListener('input', updatePhotoFilters);
        });

        // H. Typography & Color Pickers Suite
        const fontFamilySelect = document.getElementById('fontFamilySelect');
        if (fontFamilySelect && adminLiveCard) {
            fontFamilySelect.addEventListener('change', function () {
                const font = this.value;
                adminLiveCard.style.fontFamily = `'${font}', sans-serif`;
                if (authorName) authorName.style.fontFamily = `'${font}', 'Noto Serif Bengali', serif`;
                const titleText = document.getElementById('liveTitleText');
                const subText = document.getElementById('liveSubtitleText');
                const ribbonText = document.getElementById('liveRibbonText');
                if (titleText) titleText.style.fontFamily = `'${font}', 'Noto Serif Bengali', serif`;
                if (subText) subText.style.fontFamily = `'${font}', 'Noto Serif Bengali', serif`;
                if (ribbonText) ribbonText.style.fontFamily = `'${font}', 'Noto Serif Bengali', serif`;
            });
        }

        function bindColorPicker(pickerId, textId, targetSelector, cssProperty = 'color') {
            const picker = document.getElementById(pickerId);
            const textInput = document.getElementById(textId);
            if (!picker) return;

            const applyColor = (color) => {
                const els = typeof targetSelector === 'string' ? document.querySelectorAll(targetSelector) : [targetSelector];
                els.forEach(el => {
                    if (el) el.style[cssProperty] = color;
                });
            };

            picker.addEventListener('input', function () {
                if (textInput) textInput.value = this.value;
                applyColor(this.value);
            });

            if (textInput) {
                textInput.addEventListener('input', function () {
                    picker.value = this.value;
                    applyColor(this.value);
                });
            }
        }

        bindColorPicker('liveInputTitleColor', 'liveInputTitleColorText', '#liveTitleText');
        bindColorPicker('liveInputSubtitleColor', 'liveInputSubtitleColorText', '#liveSubtitleText');
        bindColorPicker('liveInputNameColor', 'liveInputNameColorText', '#liveAuthorName');
        bindColorPicker('liveInputMetaColor', 'liveInputMetaColorText', '#liveAuthorDesignation, #liveAuthorLocation');
        bindColorPicker('liveInputQuoteColor', 'liveInputQuoteColorText', '#liveQuoteText');
        bindColorPicker('liveInputOrgColor', 'liveInputOrgColorText', '#liveOrganizersSection, #liveOrganizersSection .org-name, #liveOrganizersSection .org-phone');

        // I. Two-Way Direct On-Card Inline Text Editing
        document.querySelectorAll('.live-editable-text').forEach(el => {
            const bindInputId = el.getAttribute('data-bind');
            if (bindInputId) {
                el.addEventListener('input', function () {
                    const formInput = document.getElementById(bindInputId);
                    if (formInput) {
                        formInput.value = this.innerText;
                    }
                });
            }
        });

        // Form Inputs to Card Live Text Binding
        function bindFormText(inputId, targetId, isMultiline = false, isPhone = false) {
            const input = document.getElementById(inputId);
            const target = document.getElementById(targetId);
            if (input && target) {
                input.addEventListener('input', function () {
                    if (isPhone) {
                        target.innerHTML = `<i class="fa-solid fa-phone"></i> ${this.value || ''}`;
                    } else if (isMultiline) {
                        target.innerHTML = (this.value || '').replace(/\n/g, '<br>');
                    } else {
                        target.textContent = this.value || '';
                    }
                });
            }
        }

        bindFormText('liveInputAnniversary', 'liveAnniversaryText');
        bindFormText('liveInputTitle', 'liveTitleText');
        bindFormText('liveInputSubtitle', 'liveSubtitleText');
        bindFormText('liveInputBadge', 'liveRibbonText');
        bindFormText('liveInputQuote', 'liveQuoteText', true);

        bindFormText('liveInputOrg1Name', 'liveOrg1Name');
        bindFormText('liveInputOrg1Role', 'liveOrg1Role', true);
        bindFormText('liveInputOrg1Phone', 'liveOrg1Phone', false, true);

        bindFormText('liveInputOrg2Name', 'liveOrg2Name');
        bindFormText('liveInputOrg2Role', 'liveOrg2Role', true);
        bindFormText('liveInputOrg2Phone', 'liveOrg2Phone', false, true);

        bindFormText('liveInputOrg3Name', 'liveOrg3Name');
        bindFormText('liveInputOrg3Role', 'liveOrg3Role', true);
        bindFormText('liveInputOrg3Phone', 'liveOrg3Phone', false, true);

        // J. Instant High-Resolution Card Downloader (PNG Export)
        const downloadBtn = document.getElementById('btnDownloadCardPng');
        if (downloadBtn && adminLiveCard) {
            downloadBtn.addEventListener('click', function () {
                removeActiveNodeSelection();
                const origText = downloadBtn.innerHTML;
                downloadBtn.disabled = true;
                downloadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> ডাউনলোড হচ্ছে...';

                if (typeof html2canvas !== 'undefined') {
                    html2canvas(adminLiveCard, {
                        scale: 3,
                        useCORS: true,
                        allowTaint: true,
                        backgroundColor: null
                    }).then(canvas => {
                        const link = document.createElement('a');
                        link.download = 'invitation-card-design.png';
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                        downloadBtn.disabled = false;
                        downloadBtn.innerHTML = origText;
                        window.showToast('<i class="fa-solid fa-circle-check text-success me-1"></i> কার্ডের উচ্চ রেজোলিউশন ইমেজ সফলভাবে ডাউনলোড হয়েছে।');
                    }).catch(err => {
                        console.error('Download error:', err);
                        downloadBtn.disabled = false;
                        downloadBtn.innerHTML = origText;
                        window.showToast('ডাউনলোড সম্পন্ন করা যায়নি', true);
                    });
                } else {
                    window.showToast('ডাউনলোড লাইব্রেরি লোড হচ্ছে, অনুগ্রহ করে আবার চেষ্টা করুন।', true);
                    downloadBtn.disabled = false;
                    downloadBtn.innerHTML = origText;
                }
            });
        }

        // K. Reset Defaults Button
        const resetBtn = document.getElementById('btnResetCardDefaults');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                if (!confirm('আপনি কি কার্ডের সকল সেটিংস ডিফল্ট ডিজাইনে ফিরিয়ে নিতে চান?')) return;

                // Pick default palette
                const defaultThemeBtn = document.querySelector('.theme-preset-btn[data-bg="#c98c21"]');
                if (defaultThemeBtn) defaultThemeBtn.click();

                // Reset toggles to checked
                document.querySelectorAll('.live-toggle-input').forEach(toggle => {
                    toggle.checked = true;
                    toggle.dispatchEvent(new Event('change'));
                });

                // Reset sizes
                if (photoSlider) {
                    photoSlider.value = 82;
                    photoSlider.dispatchEvent(new Event('input'));
                }
                if (nameSlider) {
                    nameSlider.value = 16;
                    nameSlider.dispatchEvent(new Event('input'));
                }
                if (nameLineHeightSlider) {
                    nameLineHeightSlider.value = 1.3;
                    nameLineHeightSlider.dispatchEvent(new Event('input'));
                }
                if (nameSpacingSlider) {
                    nameSpacingSlider.value = 4;
                    nameSpacingSlider.dispatchEvent(new Event('input'));
                }
                if (platePaddingSlider) {
                    platePaddingSlider.value = 12;
                    platePaddingSlider.dispatchEvent(new Event('input'));
                }
                if (logoSlider) {
                    logoSlider.value = 58;
                    logoSlider.dispatchEvent(new Event('input'));
                }

                // Reset photo shape & filters
                if (photoBorderRadiusSelect) {
                    photoBorderRadiusSelect.value = '50%';
                    photoBorderRadiusSelect.dispatchEvent(new Event('change'));
                }
                if (photoBorderWidthSlider) {
                    photoBorderWidthSlider.value = 3;
                    photoBorderWidthSlider.dispatchEvent(new Event('input'));
                }
                if (photoBorderColorInput) {
                    photoBorderColorInput.value = '#ffffff';
                    photoBorderColorInput.dispatchEvent(new Event('input'));
                }
                if (photoShadowSelect) {
                    photoShadowSelect.value = 'soft';
                    photoShadowSelect.dispatchEvent(new Event('change'));
                }
                if (photoBrightnessSlider) photoBrightnessSlider.value = 100;
                if (photoContrastSlider) photoContrastSlider.value = 100;
                if (photoGrayscaleSlider) photoGrayscaleSlider.value = 0;
                if (photoSepiaSlider) photoSepiaSlider.value = 0;
                updatePhotoFilters();

                // Reset Overlay
                if (bgOverlayOpacitySlider) {
                    bgOverlayOpacitySlider.value = 0;
                    bgOverlayOpacitySlider.dispatchEvent(new Event('input'));
                }
                if (bgBlurSlider) {
                    bgBlurSlider.value = 0;
                    bgBlurSlider.dispatchEvent(new Event('input'));
                }

                // Reset font family
                if (fontFamilySelect) {
                    fontFamilySelect.value = 'Hind Siliguri';
                    fontFamilySelect.dispatchEvent(new Event('change'));
                }

                // Reset logo to default ring
                if (liveCustomLogoImg) liveCustomLogoImg.style.display = 'none';
                if (liveDefaultLogoRing) liveDefaultLogoRing.style.display = '';
                if (removeLogoCheck) removeLogoCheck.checked = false;

                // Reset custom objects
                customObjects = [];
                selectedObjectId = null;
                renderCardObjects();

                window.showToast('<i class="fa-solid fa-rotate-left text-info me-1"></i> ডিফল্ট সেটিংস সফলভাবে লোড হয়েছে।');
            });
        }

        // H. AJAX Async Form Submission for Studio
        const cardCustomizerForm = document.getElementById('cardDesignCustomizerForm');
        const saveBtn = document.getElementById('btnSaveCardCustomizer');

        if (cardCustomizerForm && saveBtn) {
            cardCustomizerForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> সংরক্ষণ হচ্ছে...';

                try {
                    const formData = new FormData(cardCustomizerForm);
                    formData.set('custom_objects_json', JSON.stringify(customObjects));
                    
                    const res = await fetchWithCsrf(cardCustomizerForm.action, {
                        method: 'POST',
                        body: formData
                    });

                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> সংরক্ষণ';

                    if (res.ok) {
                        const data = await res.json();
                        if (data.success) {
                            window.showToast('<i class="fa-solid fa-circle-check text-success me-1"></i> ' + (data.message || 'কার্ড ডিজাইন সেটিংস সংরক্ষিত হয়েছে!'));
                            if (data.bg_image_url) {
                                uploadedBgUrl = data.bg_image_url;
                                adminLiveCard.style.background = `url('${uploadedBgUrl}') no-repeat center center / cover`;
                            }
                            if (data.logo_image_url) {
                                uploadedLogoUrl = data.logo_image_url;
                                if (liveCustomLogoImg) {
                                    liveCustomLogoImg.src = uploadedLogoUrl;
                                    liveCustomLogoImg.style.display = 'block';
                                }
                                if (liveDefaultLogoRing) liveDefaultLogoRing.style.display = 'none';
                            }
                            if (data.event_logo_image_url) {
                                uploadedEventLogoUrl = data.event_logo_image_url;
                                if (liveCustomEventLogoImg) {
                                    liveCustomEventLogoImg.src = uploadedEventLogoUrl;
                                    liveCustomEventLogoImg.style.display = 'block';
                                }
                                if (liveEventLogoWrap) liveEventLogoWrap.style.display = 'flex';
                            }
                        } else {
                            window.showToast(data.message || 'সংরক্ষণ ব্যর্থ হয়েছে', true);
                        }
                    } else if (res.status === 419) {
                        window.showToast('সেশন মেয়াদোত্তীর্ণ হয়েছে। পেজ রিফ্রেশ করে আবার চেষ্টা করুন।', true);
                    } else {
                        window.showToast('সংরক্ষণ ব্যর্থ হয়েছে (স্ট্যাটাস: ' + res.status + ')', true);
                    }
                } catch (err) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> সংরক্ষণ';
                    console.error('Card customizer save error:', err);
                    window.showToast('সংরক্ষণ ব্যর্থ হয়েছে। অনুগ্রহ করে ইন্টারনেট সংযোগ চেক করে আবার চেষ্টা করুন।', true);
                }
            });
        }
    }
});

// Helper for Viva Mark auto summation
function calcVivaTotal(id) {
    const inputs = document.querySelectorAll('.viva-input-' + id);
    let total = 0;
    inputs.forEach(input => {
        const val = parseFloat(input.value);
        if (!isNaN(val)) {
            total += val;
        }
    });
    const display = document.getElementById('vivaTotalDisplay' + id);
    if (display) {
        display.textContent = (total % 1 === 0 ? total : total.toFixed(1)) + ' / ৫০';
    }
}

// Helper for image preview in edit / add participant modals
window.previewEditImage = function (input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const previewEl = document.getElementById(previewId);
            if (previewEl) {
                if (previewEl.tagName.toLowerCase() === 'img') {
                    previewEl.src = e.target.result;
                } else {
                    previewEl.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">`;
                }
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
};

// Single Participant Delete Handler
document.addEventListener('click', async function (e) {
    const delBtn = e.target.closest('.btn-delete-reg');
    if (!delBtn) return;
    e.preventDefault();

    const regId = delBtn.getAttribute('data-id');
    const regName = delBtn.getAttribute('data-name') || 'অংশগ্রহণকারী';
    const deleteUrl = delBtn.getAttribute('data-url');

    if (!deleteUrl) return;

    if (!confirm(`আপনি কি নিশ্চিতভাবে "${regName}"-এর নিবন্ধন কার্ড ও তথ্য সম্পূর্ণ মুছে ফেলতে চান?`)) {
        return;
    }

    try {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = deleteUrl;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                          document.querySelector('input[name="_token"]')?.value || '';

        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = csrfToken;
        form.appendChild(tokenInput);

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        document.body.appendChild(form);
        form.submit();
    } catch (err) {
        console.error('Delete request error:', err);
        alert('মুছে ফেলা সম্ভব হয়নি। আবার চেষ্টা করুন।');
    }
});

// Select all columns in table customizer
function selectAllColumns(check) {
    document.querySelectorAll('.col-toggle-check').forEach(c => c.checked = check);
}

// Execute Bulk Action
async function executeBulkAction(actionType) {
    const selected = Array.from(document.querySelectorAll('.participant-select-check:checked')).map(cb => cb.value);
    if (selected.length === 0) {
        alert('Please select at least one participant.');
        return;
    }

    if (actionType === 'delete' && !confirm(`Are you sure you want to remove ${selected.length} selected registration(s)?`)) {
        return;
    }

    const bulkForm = document.getElementById('bulkActionHiddenForm');
    const bulkActionInput = document.getElementById('bulkActionTypeInput');
    const bulkIdsInput = document.getElementById('bulkSelectedIdsInput');

    if (bulkForm && bulkActionInput && bulkIdsInput) {
        const tokenInput = bulkForm.querySelector('input[name="_token"]');
        const currentToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (tokenInput && currentToken) {
            tokenInput.value = currentToken;
        }
        bulkActionInput.value = actionType;
        bulkIdsInput.value = JSON.stringify(selected);
        bulkForm.submit();
    }
}
