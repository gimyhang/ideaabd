{{-- ── Quick Add Category Modal ── --}}
<div class="modal fade" id="pubQuickCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-2.5 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0"><i class="fas fa-folder-plus text-primary me-1.5"></i> Add New Category</h6>
                <button type="button" class="btn-close btn-close-xs" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-2.5">
                    <label class="form-label small fw-bold text-dark mb-1">Category Name *</label>
                    <input type="text" id="quickCatNameInput" class="form-control form-control-sm rounded-3" placeholder="e.g. History & Research">
                </div>
            </div>
            <div class="modal-footer border-top py-2 px-3">
                <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" onclick="submitQuickCategory()">
                    <i class="fas fa-check me-1"></i> Add Category
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Quick Add Author Modal ── --}}
<div class="modal fade" id="pubQuickAuthorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-2.5 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0"><i class="fas fa-user-plus text-success me-1.5"></i> Add New Author</h6>
                <button type="button" class="btn-close btn-close-xs" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-2.5">
                    <label class="form-label small fw-bold text-dark mb-1">Author Name *</label>
                    <input type="text" id="quickAuthorNameInput" class="form-control form-control-sm rounded-3" placeholder="e.g. Humayun Ahmed">
                </div>
            </div>
            <div class="modal-footer border-top py-2 px-3">
                <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold" onclick="submitQuickAuthor()">
                    <i class="fas fa-check me-1"></i> Add Author
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ── Clean Single Tab Switcher (Synchronizes Pills, Mobile Dropdown & Tab Panes) ──
function switchPublisherTab(tabName) {
    if (tabName === 'account-redirect') {
        window.location.href = "{{ route('my-account') }}";
        return;
    }

    const tabMap = {
        'overview': 'tab-overview-btn',
        'today-purchases': 'tab-today-purchases-btn',
        'books': 'tab-books-btn',
        'add-book': 'tab-add-book-btn',
        'orders': 'tab-orders-btn',
        'settings': 'tab-settings-btn'
    };

    const targetBtnId = tabMap[tabName];
    if (targetBtnId) {
        const btn = document.getElementById(targetBtnId);
        if (btn) {
            if (window.bootstrap && bootstrap.Tab) {
                const tabInstance = bootstrap.Tab.getOrCreateInstance(btn);
                tabInstance.show();
            } else {
                btn.click();
            }
        }
    }

    // Synchronize Mobile Select Dropdown
    syncMobileTabSelect(tabName);

    // Update URL query string without reloading page
    if (window.history && window.history.pushState) {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabName);
        window.history.pushState({ tab: tabName }, '', url);
    }
}

function syncMobileTabSelect(tabName) {
    const mobileSelect = document.getElementById('mobilePublisherTabSelect');
    if (mobileSelect) {
        mobileSelect.value = tabName;
    }
}

// ── Product Type Dynamic Label ──
function onProductTypeChange() {
    const pType = document.getElementById('pubProductType')?.value || 'book';
    const labelBn = document.getElementById('pubTitleLabelBn');
    const labelEn = document.getElementById('pubTitleLabelEn');
    
    if (pType === 'stationery') {
        if (labelBn) labelBn.innerHTML = '<i class="fas fa-pen-ruler text-primary me-1"></i> Stationery Name (BN) <span class="text-danger">*</span>';
        if (labelEn) labelEn.innerHTML = '<i class="fas fa-language text-secondary me-1"></i> Stationery Name (EN) <span class="text-danger">*</span>';
    } else if (pType === 'islamic_gift') {
        if (labelBn) labelBn.innerHTML = '<i class="fas fa-gift text-primary me-1"></i> Gift / Art Item Name (BN) <span class="text-danger">*</span>';
        if (labelEn) labelEn.innerHTML = '<i class="fas fa-language text-secondary me-1"></i> Gift / Art Item Name (EN) <span class="text-danger">*</span>';
    } else if (pType === 'other') {
        if (labelBn) labelBn.innerHTML = '<i class="fas fa-box text-primary me-1"></i> Product Name (BN) <span class="text-danger">*</span>';
        if (labelEn) labelEn.innerHTML = '<i class="fas fa-language text-secondary me-1"></i> Product Name (EN) <span class="text-danger">*</span>';
    } else {
        if (labelBn) labelBn.innerHTML = '<i class="fas fa-book text-primary me-1"></i> Title / Product Name (BN) <span class="text-danger">*</span>';
        if (labelEn) labelEn.innerHTML = '<i class="fas fa-language text-secondary me-1"></i> Product Name (EN) <span class="text-danger">*</span>';
    }
}

// ── Order / Stock Status Pre-Order Box Toggle ──
function onOrderTypeChange() {
    const status = document.getElementById('pubStockStatus')?.value || 'in_stock';
    const preOrderBox = document.getElementById('preOrderDetailsBox');
    if (preOrderBox) {
        preOrderBox.style.display = status === 'pre_order' ? 'block' : 'none';
    }
}

// ── Cover Pricing Logic ──
function toggleCoverPricing() {
    // Standard single MRP list price model with 2-way pricing engine
}

// ── 2-Way Pricing and Margin Calculation Engine ──
function onPubPriceChange() {
    const listPrice = parseFloat(document.getElementById('pubPriceInput')?.value) || 0;
    const discPct = parseFloat(document.getElementById('pubPurchaseDiscPct')?.value) || 0;
    const soldPct = parseFloat(document.getElementById('pubSoldPct')?.value) || 0;

    if (listPrice > 0 && discPct > 0) {
        const costPrice = listPrice - (listPrice * (discPct / 100));
        const costInput = document.getElementById('pubCostPriceInput');
        if (costInput) costInput.value = costPrice.toFixed(2);
    }

    if (listPrice > 0 && soldPct > 0) {
        const offerPrice = listPrice - (listPrice * (soldPct / 100));
        const discInput = document.getElementById('pubDiscountPriceInput');
        if (discInput) discInput.value = offerPrice.toFixed(2);
    }
    updatePubCalculatedStats();
    updatePubMockup();
}

function onPubPurchaseDiscountChange() {
    const listPrice = parseFloat(document.getElementById('pubPriceInput')?.value) || 0;
    const discPct = parseFloat(document.getElementById('pubPurchaseDiscPct')?.value) || 0;

    if (listPrice > 0) {
        const costPrice = listPrice - (listPrice * (discPct / 100));
        const costInput = document.getElementById('pubCostPriceInput');
        if (costInput) costInput.value = costPrice.toFixed(2);
    }
    updatePubCalculatedStats();
}

function onPubCostChange() {
    const listPrice = parseFloat(document.getElementById('pubPriceInput')?.value) || 0;
    const costPrice = parseFloat(document.getElementById('pubCostPriceInput')?.value) || 0;

    if (listPrice > 0 && costPrice <= listPrice) {
        const discPct = ((listPrice - costPrice) / listPrice) * 100;
        const discPctInput = document.getElementById('pubPurchaseDiscPct');
        if (discPctInput) discPctInput.value = discPct.toFixed(1);
    }
    updatePubCalculatedStats();
}

function onPubSoldPctChange() {
    const listPrice = parseFloat(document.getElementById('pubPriceInput')?.value) || 0;
    const soldPct = parseFloat(document.getElementById('pubSoldPct')?.value) || 0;

    if (listPrice > 0) {
        const offerPrice = listPrice - (listPrice * (soldPct / 100));
        const discInput = document.getElementById('pubDiscountPriceInput');
        if (discInput) discInput.value = offerPrice.toFixed(2);
    }
    updatePubCalculatedStats();
    updatePubMockup();
}

function updatePubCalculatedStats() {
    const listPrice = parseFloat(document.getElementById('pubPriceInput')?.value) || 0;
    const costPrice = parseFloat(document.getElementById('pubCostPriceInput')?.value) || 0;
    const soldPct = parseFloat(document.getElementById('pubSoldPct')?.value) || 0;

    const offerPrice = listPrice > 0 ? (listPrice - (listPrice * (soldPct / 100))) : 0;
    const profit = offerPrice > costPrice ? (offerPrice - costPrice) : 0;
    const marginPct = offerPrice > 0 ? ((profit / offerPrice) * 100) : 0;

    const offerEl = document.getElementById('pubCalculatedOfferPrice');
    const profitEl = document.getElementById('pubCalculatedProfit');

    if (offerEl) offerEl.innerText = '৳' + offerPrice.toFixed(2);
    if (profitEl) profitEl.innerText = '৳' + profit.toFixed(2) + ' (' + marginPct.toFixed(1) + '%)';
}

// ── Look Inside Format Switcher ──
function togglePubLookInsideFormat(type) {
    const pdfPanel = document.getElementById('pubLookInsidePdfPanel');
    const imgPanel = document.getElementById('pubLookInsideImagesPanel');

    if (type === 'images') {
        if (pdfPanel) pdfPanel.classList.add('d-none');
        if (imgPanel) imgPanel.classList.remove('d-none');
    } else {
        if (pdfPanel) pdfPanel.classList.remove('d-none');
        if (imgPanel) imgPanel.classList.add('d-none');
    }
}

function previewPubMultiImages(input) {
    const container = document.getElementById('pubMultiImagesPreviewContainer');
    if (!container) return;
    container.innerHTML = '';

    if (input.files) {
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const badge = document.createElement('div');
                badge.className = 'position-relative d-inline-block border rounded p-1 bg-white shadow-xs';
                badge.innerHTML = `
                    <img src="${e.target.result}" style="width: 48px; height: 65px; object-fit: cover;" class="rounded">
                    <span class="badge bg-dark position-absolute top-0 start-0 m-0.5" style="font-size: 8px;">#${index + 1}</span>
                `;
                container.appendChild(badge);
            };
            reader.readAsDataURL(file);
        });
    }
}

// ── Generic Word Counter for 1000 limit ──
function updateGenericWordCount(textarea, maxWords, countId, badgeId, barId, warningId) {
    const text = (textarea.value || '').trim();
    const words = text ? text.split(/\s+/).filter(Boolean) : [];
    const count = words.length;

    const countEl = document.getElementById(countId);
    const badgeEl = document.getElementById(badgeId);
    const barEl = document.getElementById(barId);
    const warnEl = document.getElementById(warningId);

    if (countEl) countEl.textContent = count;

    const pct = Math.min(100, (count / maxWords) * 100);
    if (barEl) barEl.style.width = pct + '%';

    if (count > maxWords) {
        if (badgeEl) { badgeEl.className = 'word-counter-badge exceeded'; }
        if (barEl) { barEl.className = 'word-counter-progress__bar exceeded'; }
        if (warnEl) {
            warnEl.textContent = `শব্দসীমা (${maxWords}) অতিক্রম করেছে (${count} শব্দ)! অনুগ্রহ করে সংক্ষিপ্ত করুন।`;
            warnEl.classList.remove('d-none');
        }
    } else if (count >= maxWords * 0.9) {
        if (badgeEl) { badgeEl.className = 'word-counter-badge warning'; }
        if (barEl) { barEl.className = 'word-counter-progress__bar warning'; }
        if (warnEl) { warnEl.classList.add('d-none'); }
    } else {
        if (badgeEl) { badgeEl.className = 'word-counter-badge safe'; }
        if (barEl) { barEl.className = 'word-counter-progress__bar'; }
        if (warnEl) { warnEl.classList.add('d-none'); }
    }
}

// ── Dynamic Multi-Contributor Repeaters (Author, Translator, Editor, Rewriter) ──
function addPubAuthorField() {
    const container = document.getElementById('pubAuthorsRepeaterContainer');
    if (!container) return;
    const authors = @json($authors ?? []);
    let optionsHtml = '<option value="">— Directory —</option>';
    for (const [aId, aName] of Object.entries(authors)) {
        optionsHtml += `<option value="${aId}">${aName}</option>`;
    }
    const div = document.createElement('div');
    div.className = 'input-group input-group-sm pub-author-field-row shadow-xs';
    div.innerHTML = `
        <select name="author_ids[]" class="form-select form-select-sm rounded-start-3" style="max-width: 135px;" onchange="onPubAuthorSelectRowChange(this)">
            ${optionsHtml}
        </select>
        <input type="text" name="author_names[]" class="form-control form-control-sm pub-author-name-input" 
               placeholder="লেখকের নাম লিখুন..." oninput="updatePubMockup()">
        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.pub-author-field-row').remove(); updatePubMockup();" title="Remove">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

function onPubAuthorSelectRowChange(select) {
    const row = select.closest('.pub-author-field-row');
    if (!row) return;
    const input = row.querySelector('.pub-author-name-input');
    if (input && select.selectedIndex > 0) {
        input.value = select.options[select.selectedIndex].text.trim();
        updatePubMockup();
    }
}

function addPubTranslatorField() {
    const container = document.getElementById('pubTranslatorsRepeaterContainer');
    if (!container) return;
    const div = document.createElement('div');
    div.className = 'input-group input-group-sm pub-translator-field-row shadow-xs';
    div.innerHTML = `
        <input type="text" name="translator_names[]" class="form-control form-control-sm rounded-start-3" placeholder="অনুবাদকের নাম...">
        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.pub-translator-field-row').remove()" title="Remove">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

function addPubEditorField() {
    const container = document.getElementById('pubEditorsRepeaterContainer');
    if (!container) return;
    const div = document.createElement('div');
    div.className = 'input-group input-group-sm pub-editor-field-row shadow-xs';
    div.innerHTML = `
        <input type="text" name="editor_names[]" class="form-control form-control-sm rounded-start-3" placeholder="সম্পাদকের নাম...">
        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.pub-editor-field-row').remove()" title="Remove">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

function addPubRewriterField() {
    const container = document.getElementById('pubRewritersRepeaterContainer');
    if (!container) return;
    const div = document.createElement('div');
    div.className = 'input-group input-group-sm pub-rewriter-field-row shadow-xs';
    div.innerHTML = `
        <input type="text" name="rewriter_names[]" class="form-control form-control-sm rounded-start-3" placeholder="পুনর্লিখনকারী / রূপান্তরকারীর নাম...">
        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.pub-rewriter-field-row').remove()" title="Remove">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

// Sync Book Height & Width cm to combined size
function syncPubBookSizeCombined() {
    const h = document.getElementById('pubBookHeightCm')?.value?.trim();
    const w = document.getElementById('pubBookWidthCm')?.value?.trim();
    const hiddenSize = document.getElementById('pubBookSizeHidden');
    if (!hiddenSize) return;
    if (h && w) {
        hiddenSize.value = `${h} cm × ${w} cm`;
    } else if (h) {
        hiddenSize.value = `${h} cm`;
    } else if (w) {
        hiddenSize.value = `${w} cm`;
    } else {
        hiddenSize.value = '';
    }
}

// ── Live Mockup Updates ──
function updatePubMockup() {
    const title = document.getElementById('pubBookTitleInput')?.value || 'Book Title';
    const authorInputs = document.querySelectorAll('#pubAuthorsRepeaterContainer input.pub-author-name-input');
    let authorNames = [];
    authorInputs.forEach(i => {
        if (i.value && i.value.trim()) authorNames.push(i.value.trim());
    });
    const author = authorNames.length ? authorNames.join(', ') : 'Author Name';

    const p = parseFloat(document.getElementById('pubPriceInput')?.value) || 0;
    const dp = parseFloat(document.getElementById('pubDiscountPriceInput')?.value) || p;
    const pct = p > 0 && dp < p ? Math.round(((p - dp) / p) * 100) : 0;

    const mockTitle = document.getElementById('pubMockupTitle');
    const mockAuthor = document.getElementById('pubMockupAuthor');
    const mockPrice = document.getElementById('pubMockupPrice');
    const mockBadge = document.getElementById('pubMockupDiscountBadge');

    if (mockTitle) mockTitle.innerText = title;
    if (mockAuthor) mockAuthor.innerText = author;
    if (mockPrice) mockPrice.innerText = `৳${dp}`;
    if (mockBadge) {
        if (pct > 0) {
            mockBadge.innerText = `-${pct}%`;
            mockBadge.classList.remove('d-none');
        } else {
            mockBadge.classList.add('d-none');
        }
    }
}

function previewPubBookCover(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const mockImg = document.getElementById('pubMockupCoverImg');
            if (mockImg) mockImg.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewPubPdfName(input) {
    if (input.files && input.files[0]) {
        const txt = document.getElementById('pubPdfFilenameText');
        if (txt) txt.innerText = '📄 ' + input.files[0].name;
    }
}

// ── Quick Modal Creation Scripts with Live AJAX ──
function submitQuickCategory() {
    const input = document.getElementById('quickCatNameInput');
    const val = input ? input.value.trim() : '';
    if (!val) {
        alert('অনুগ্রহ করে ক্যাটাগরির নাম লিখুন।');
        return;
    }

    const btn = document.querySelector('#pubQuickCategoryModal .btn-primary');
    const origHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Adding...';
    }

    fetch("{{ route('publisher.quick.category') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name: val })
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
        if (data.success && data.item) {
            const select = document.getElementById('pubCategorySelect');
            if (select) {
                const opt = document.createElement('option');
                opt.value = data.item.id;
                opt.text = data.item.name;
                opt.selected = true;
                select.add(opt);
            }
            input.value = '';
            const modalEl = document.getElementById('pubQuickCategoryModal');
            if (modalEl && window.bootstrap) {
                const modal = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.hide();
            }
            updatePubMockup();
        } else {
            alert(data.message || 'ক্যাটাগরি যুক্ত করা সম্ভব হয়নি।');
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
        console.error(err);
        alert('সার্ভার যোগাযোগে ত্রুটি হয়েছে।');
    });
}

function submitQuickAuthor() {
    const input = document.getElementById('quickAuthorNameInput');
    const val = input ? input.value.trim() : '';
    if (!val) {
        alert('অনুগ্রহ করে লেখকের নাম লিখুন।');
        return;
    }

    const btn = document.querySelector('#pubQuickAuthorModal .btn-success');
    const origHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Adding...';
    }

    fetch("{{ route('publisher.quick.author') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name: val })
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
        if (data.success && data.item) {
            // Update all author dropdowns in repeaters
            const authorSelects = document.querySelectorAll('#pubAuthorsRepeaterContainer select[name="author_ids[]"]');
            authorSelects.forEach(sel => {
                const opt = document.createElement('option');
                opt.value = data.item.id;
                opt.text = data.item.name;
                sel.add(opt);
            });

            const container = document.getElementById('pubAuthorsRepeaterContainer');
            if (container) {
                const firstRow = container.querySelector('.pub-author-field-row');
                const firstInput = firstRow ? firstRow.querySelector('input.pub-author-name-input') : null;
                const firstSelect = firstRow ? firstRow.querySelector('select[name="author_ids[]"]') : null;

                if (firstInput && !firstInput.value.trim()) {
                    firstInput.value = data.item.name;
                    if (firstSelect) firstSelect.value = data.item.id;
                } else {
                    addPubAuthorField();
                    const allRows = container.querySelectorAll('.pub-author-field-row');
                    const lastRow = allRows[allRows.length - 1];
                    if (lastRow) {
                        const lastInput = lastRow.querySelector('input.pub-author-name-input');
                        const lastSelect = lastRow.querySelector('select[name="author_ids[]"]');
                        if (lastInput) lastInput.value = data.item.name;
                        if (lastSelect) lastSelect.value = data.item.id;
                    }
                }
            }

            input.value = '';
            const modalEl = document.getElementById('pubQuickAuthorModal');
            if (modalEl && window.bootstrap) {
                const modal = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.hide();
            }
            updatePubMockup();
        } else {
            alert(data.message || 'লেখক যুক্ত করা সম্ভব হয়নি।');
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
        console.error(err);
        alert('সার্ভার যোগাযোগে ত্রুটি হয়েছে।');
    });
}

function handlePubBoimelaSelect(val) {
    if (val === '__custom__') {
        togglePubCustomBoimela(true);
    } else {
        const hidden = document.getElementById('pubEkusheyCategory');
        if (hidden) hidden.value = val;
        const wrapper = document.getElementById('pubCustomBoimelaWrapper');
        if (wrapper) wrapper.classList.add('d-none');
        const select = document.getElementById('pubEkusheyCategorySelect');
        if (select) select.classList.remove('d-none');
    }
}

function togglePubCustomBoimela(show = null) {
    const wrapper = document.getElementById('pubCustomBoimelaWrapper');
    const select = document.getElementById('pubEkusheyCategorySelect');
    const customInput = document.getElementById('pubEkusheyCategoryCustom');
    const hidden = document.getElementById('pubEkusheyCategory');
    if (!wrapper || !select) return;

    const shouldShow = show !== null ? show : wrapper.classList.contains('d-none');
    if (shouldShow) {
        wrapper.classList.remove('d-none');
        select.classList.add('d-none');
        select.value = '__custom__';
        if (customInput) {
            customInput.focus();
            if (customInput.value.trim() && hidden) {
                hidden.value = customInput.value.trim();
            }
        }
    } else {
        resetPubBoimelaToSelect();
    }
}

function resetPubBoimelaToSelect() {
    const wrapper = document.getElementById('pubCustomBoimelaWrapper');
    const select = document.getElementById('pubEkusheyCategorySelect');
    const hidden = document.getElementById('pubEkusheyCategory');
    const customInput = document.getElementById('pubEkusheyCategoryCustom');
    if (wrapper) wrapper.classList.add('d-none');
    if (select) {
        select.classList.remove('d-none');
        select.value = '';
    }
    if (customInput) customInput.value = '';
    if (hidden) hidden.value = '';
}

document.addEventListener('DOMContentLoaded', function() {
    onOrderTypeChange();
    onPubPriceChange();
    syncPubBookSizeCombined();
    updatePubMockup();
    const summaryInput = document.getElementById('pubSummaryInput');
    if (summaryInput) {
        updateGenericWordCount(summaryInput, 1000, 'pubSummaryWordCount', 'pubSummaryWordBadge', 'pubSummaryProgressBar', 'pubSummaryWarning');
    }
});
</script>
@endpush
