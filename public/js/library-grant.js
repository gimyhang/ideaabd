/**
 * পাঠাগার নিবন্ধন ও বই অনুদান ফরম — জাভাস্ক্রিপ্ট মডিউল
 * ডাইনামিক লোকেশন ক্যাসকেড, ক্যানভাস অটো-ক্রপ ও কম্প্রেশন, লাইভ প্রোগ্রেস ও ভ্যালিডেশন
 */

document.addEventListener('DOMContentLoaded', function () {
    initLibraryForm();
});

function initLibraryForm() {
    initLocationCascade();
    initPhotoUploader();
    initWordCounter();
    initPhoneFormatter();
    initCategoryChips();
    initProgressTracker();
    initFormValidation();
}

/**
 * ১. ৪-স্তরের ডাইনামিক লোকেশন ক্যাসকেড (বিভাগ -> জেলা -> থানা/উপজেলা -> পোস্ট অফিস)
 */
function initLocationCascade() {
    const divSelect = document.getElementById('libDivision');
    const distSelect = document.getElementById('libDistrict');
    const upaSelect = document.getElementById('libThana') || document.getElementById('libUpazila');
    const poSelect = document.getElementById('libPostOffice');

    if (!divSelect || !distSelect || !upaSelect) return;

    let rawDiv = (divSelect.getAttribute('data-old') || '').trim();
    let rawDist = (distSelect.getAttribute('data-old') || '').trim();
    let rawUpa = (upaSelect.getAttribute('data-old') || '').trim();
    let rawPo = (poSelect ? poSelect.getAttribute('data-old') || '' : '').trim();

    // ইংরেজি থেকে বাংলা কনভার্শন (যদি থাকে)
    const enMap = window.EN_TO_BN_GEO || {};
    const oldDiv = enMap[rawDiv] || rawDiv;
    const oldDist = enMap[rawDist] || rawDist;
    const oldUpa = rawUpa;
    const oldPo = rawPo;

    function getGeo() {
        return window.BD_GEO_BN || window.BD_GEO || {
            divisions: {},
            upazilas: {},
            postOffices: {}
        };
    }

    // বিভাগ লোড
    const geo = getGeo();
    if (geo.divisions && Object.keys(geo.divisions).length > 0) {
        divSelect.innerHTML = '<option value="">-- বিভাগ নির্বাচন করুন --</option>';
        Object.keys(geo.divisions).forEach(div => {
            const opt = document.createElement('option');
            opt.value = div;
            opt.textContent = div;
            if (oldDiv && (oldDiv.toLowerCase() === div.toLowerCase())) {
                opt.selected = true;
            }
            divSelect.appendChild(opt);
        });
    }

    // জেলা লোড
    function populateDistricts(selectedDiv, preselectedDist = '') {
        distSelect.innerHTML = '<option value="">-- জেলা নির্বাচন করুন --</option>';
        upaSelect.innerHTML = '<option value="">-- উপজেলা / থানা নির্বাচন করুন --</option>';
        if (poSelect) poSelect.innerHTML = '<option value="">-- পোস্ট অফিস নির্বাচন করুন --</option>';

        const currentGeo = getGeo();
        const mappedDiv = enMap[selectedDiv] || selectedDiv;
        if (mappedDiv && currentGeo.divisions && currentGeo.divisions[mappedDiv]) {
            currentGeo.divisions[mappedDiv].forEach(dist => {
                const opt = document.createElement('option');
                opt.value = dist;
                opt.textContent = dist;
                const matchDist = enMap[preselectedDist] || preselectedDist;
                if (matchDist && (matchDist.toLowerCase() === dist.toLowerCase())) {
                    opt.selected = true;
                }
                distSelect.appendChild(opt);
            });
        }
        updateFormProgress();
    }

    // উপজেলা ও পোস্ট অফিস লোড
    function populateUpazilasAndPostOffices(selectedDist, preselectedUpa = '', preselectedPo = '') {
        upaSelect.innerHTML = '<option value="">-- উপজেলা / থানা নির্বাচন করুন --</option>';
        if (poSelect) poSelect.innerHTML = '<option value="">-- পোস্ট অফিস নির্বাচন করুন (ঐচ্ছিক) --</option>';

        const currentGeo = getGeo();
        const mappedDist = enMap[selectedDist] || selectedDist;
        if (mappedDist && currentGeo.upazilas && currentGeo.upazilas[mappedDist]) {
            currentGeo.upazilas[mappedDist].forEach(upa => {
                const opt = document.createElement('option');
                opt.value = upa;
                opt.textContent = upa;
                if (preselectedUpa && (preselectedUpa.toLowerCase() === upa.toLowerCase())) {
                    opt.selected = true;
                }
                upaSelect.appendChild(opt);
            });
        }

        if (poSelect && mappedDist) {
            let poList = [];
            if (currentGeo.postOffices && currentGeo.postOffices[mappedDist]) {
                poList = currentGeo.postOffices[mappedDist];
            } else if (currentGeo.upazilas && currentGeo.upazilas[mappedDist]) {
                poList.push(`${mappedDist} প্রধান ডাকঘর`);
                currentGeo.upazilas[mappedDist].forEach(u => poList.push(`${u} ডাকঘর`));
            }

            poList.forEach(po => {
                const opt = document.createElement('option');
                opt.value = po;
                opt.textContent = po;
                if (preselectedPo && (preselectedPo.toLowerCase() === po.toLowerCase())) {
                    opt.selected = true;
                }
                poSelect.appendChild(opt);
            });
        }
        updateFormProgress();
    }

    divSelect.addEventListener('change', function () {
        populateDistricts(this.value);
    });

    distSelect.addEventListener('change', function () {
        populateUpazilasAndPostOffices(this.value);
    });

    upaSelect.addEventListener('change', function () {
        updateFormProgress();
    });

    // পূর্বের ইনপুট অনুযায়ী অটো-সিলেক্ট
    if (oldDiv) {
        populateDistricts(oldDiv, oldDist);
        if (oldDist) {
            populateUpazilasAndPostOffices(oldDist, oldUpa, oldPo);
        }
    }
}

/**
 * ২. ফটো আপলোড, ড্র্যাগ-অ্যান্ড-ড্রপ ও ক্যানভাস অটো-ক্রপ
 */
function initPhotoUploader() {
    const photoBox = document.getElementById('libPhotoBox');
    const fileInput = document.getElementById('libPhotoInput');
    const previewImg = document.getElementById('libPreviewImg');
    const placeholder = document.getElementById('libUploadPrompt');
    const badge = document.getElementById('libOptBadge');
    const removeBtn = document.getElementById('libPhotoRemove');
    const hiddenData = document.getElementById('optimizedPhotoData');

    if (!photoBox || !fileInput) return;

    photoBox.addEventListener('click', function (e) {
        if (e.target !== removeBtn && !removeBtn?.contains(e.target)) {
            fileInput.click();
        }
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        photoBox.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            photoBox.classList.add('drag-over');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        photoBox.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            photoBox.classList.remove('drag-over');
        }, false);
    });

    photoBox.addEventListener('drop', function (e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length) {
            fileInput.files = files;
            processPhoto(files[0]);
        }
    });

    fileInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            processPhoto(this.files[0]);
        }
    });

    if (removeBtn) {
        removeBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            fileInput.value = '';
            if (hiddenData) hiddenData.value = '';
            if (previewImg) {
                previewImg.src = '';
                previewImg.style.display = 'none';
            }
            if (placeholder) placeholder.style.display = 'block';
            if (badge) badge.style.display = 'none';
            removeBtn.style.display = 'none';
            updateFormProgress();
        });
    }

    function processPhoto(file) {
        if (!file.type.match('image.*')) {
            alert('অনুগ্রহ করে একটি সঠিক ছবি (JPG, PNG বা WEBP) নির্বাচন করুন।');
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            const img = new Image();
            img.onload = function () {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const targetW = 360;
                const targetH = 420;

                canvas.width = targetW;
                canvas.height = targetH;

                // সেন্টার ক্রপ
                const srcRatio = img.width / img.height;
                const targetRatio = targetW / targetH;
                let cropW = img.width;
                let cropH = img.height;
                let cropX = 0;
                let cropY = 0;

                if (srcRatio > targetRatio) {
                    cropW = img.height * targetRatio;
                    cropX = (img.width - cropW) / 2;
                } else {
                    cropH = img.width / targetRatio;
                    cropY = (img.height - cropH) / 2;
                }

                ctx.drawImage(img, cropX, cropY, cropW, cropH, 0, 0, targetW, targetH);

                const dataUrl = canvas.toDataURL('image/jpeg', 0.86);

                if (hiddenData) hiddenData.value = dataUrl;
                if (previewImg) {
                    previewImg.src = dataUrl;
                    previewImg.style.display = 'block';
                }
                if (placeholder) placeholder.style.display = 'none';
                if (badge) {
                    badge.textContent = '✓ ছবি প্রস্তুত হয়েছে';
                    badge.style.display = 'inline-block';
                }
                if (removeBtn) removeBtn.style.display = 'flex';

                updateFormProgress();
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

/**
 * ৩. লাইভ শব্দ গণনাকারী ও ৮০ শব্দের মনিটর
 */
function initWordCounter() {
    const textarea = document.getElementById('reasonText');
    const badge = document.getElementById('wordCounter');
    const alertBox = document.getElementById('wordLimitAlert');
    const submitBtn = document.getElementById('submitBtn');

    if (!textarea || !badge) return;

    function countWords() {
        const text = textarea.value.trim();
        const words = text ? text.split(/\s+/).filter(Boolean) : [];
        const count = words.length;
        const maxWords = 80;

        // বাংলা সংখ্যা রূপান্তর
        const bnCount = count.toString().replace(/\d/g, d => '০১২৩৪৫৬৭৮৯'[d]);
        const bnMax = maxWords.toString().replace(/\d/g, d => '০১২৩৪৫৬৭৮৯'[d]);

        badge.textContent = `${bnCount} / ${bnMax} শব্দ`;

        if (count > maxWords) {
            badge.className = 'lib-word-badge badge-danger';
            if (alertBox) alertBox.classList.remove('d-none');
            if (submitBtn) submitBtn.disabled = true;
        } else if (count >= 70) {
            badge.className = 'lib-word-badge badge-warn';
            if (alertBox) alertBox.classList.add('d-none');
            if (submitBtn) submitBtn.disabled = false;
        } else {
            badge.className = 'lib-word-badge badge-safe';
            if (alertBox) alertBox.classList.add('d-none');
            if (submitBtn) submitBtn.disabled = false;
        }
        updateFormProgress();
    }

    textarea.addEventListener('input', countWords);
    if (textarea.value) countWords();
}

/**
 * ৪. ফোন নম্বর ফরম্যাটিং (বাংলা সংখ্যা রূপান্তর সহ)
 */
function initPhoneFormatter() {
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    const bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    const en = ['0','1','2','3','4','5','6','7','8','9'];

    function normalizeDigits(str) {
        if (!str) return '';
        let res = str.toString();
        for (let i = 0; i < bn.length; i++) {
            res = res.replaceAll(bn[i], en[i]);
        }
        return res.replace(/[^0-9]/g, '');
    }

    phoneInputs.forEach(input => {
        input.addEventListener('input', function () {
            this.value = normalizeDigits(this.value).slice(0, 15);
            if (this.value.length >= 6) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
            }
            updateFormProgress();
        });
    });
}

/**
 * ৫. ক্যাটাগরি চিপস
 */
function initCategoryChips() {
    const chips = document.querySelectorAll('.genre-chip-item');
    const countBadge = document.getElementById('selectedGenreCount');

    function updateGenreCount() {
        const selected = document.querySelectorAll('.genre-chip-item input[type="checkbox"]:checked');
        if (countBadge) {
            const bnLen = selected.length.toString().replace(/\d/g, d => '০১২৩৪৫৬৭৮৯'[d]);
            countBadge.textContent = selected.length > 0 ? `(${bnLen} টি বিষয় নির্বাচিত)` : '';
        }
    }

    chips.forEach(chip => {
        const checkbox = chip.querySelector('input[type="checkbox"]');
        if (!checkbox) return;

        chip.addEventListener('click', function (e) {
            if (e.target !== checkbox) {
                checkbox.checked = !checkbox.checked;
            }
            if (checkbox.checked) {
                chip.classList.add('active');
            } else {
                chip.classList.remove('active');
            }
            updateGenreCount();
            updateFormProgress();
        });

        if (checkbox.checked) {
            chip.classList.add('active');
        }
    });

    updateGenreCount();
}

/**
 * ৬. লাইভ ফর্ম পূরণ অগ্রগতি ট্র্যাকার
 */
function initProgressTracker() {
    const form = document.getElementById('dynamicLibraryForm');
    if (!form) return;

    form.addEventListener('input', updateFormProgress);
    form.addEventListener('change', updateFormProgress);
    updateFormProgress();
}

function updateFormProgress() {
    const requiredInputs = document.querySelectorAll('#dynamicLibraryForm [required]');
    const fillBar = document.getElementById('formProgressFill');
    const percentLabel = document.getElementById('formProgressPercent');

    if (!requiredInputs.length || !fillBar || !percentLabel) return;

    let filledCount = 0;
    requiredInputs.forEach(input => {
        if (input.type === 'checkbox') {
            if (input.checked) filledCount++;
        } else if (input.value && input.value.trim() !== '') {
            filledCount++;
        }
    });

    const percent = Math.min(100, Math.round((filledCount / requiredInputs.length) * 100));
    fillBar.style.width = percent + '%';
    const bnPercent = percent.toString().replace(/\d/g, d => '০১২৩৪৫৬৭৮৯'[d]);
    percentLabel.textContent = bnPercent + '%';
}

/**
 * ৭. ফর্ম সাবমিশন ও ডাবল-সাবমিট প্রতিরোধ
 */
function initFormValidation() {
    const form = document.getElementById('dynamicLibraryForm');
    const submitBtn = document.getElementById('submitBtn');

    if (!form || !submitBtn) return;

    form.addEventListener('submit', function (e) {
        const phone = document.getElementById('libPhone');
        if (phone && phone.value.length < 11) {
            e.preventDefault();
            phone.classList.add('is-invalid');
            phone.focus();
            alert('অনুগ্রহ করে সঠিক ১১ ডিজিটের মোবাইল নম্বর দিন (যেমন: 017XXXXXXXX)');
            return;
        }

        const reason = document.getElementById('reasonText');
        if (reason && reason.value) {
            const words = reason.value.trim().split(/\s+/).filter(Boolean);
            if (words.length > 80) {
                e.preventDefault();
                reason.focus();
                alert('অনুদানের প্রয়োজনীয়তার বিবরণ ৮০ শব্দের মধ্যে হতে হবে।');
                return;
            }
        }

        // স্পিনার ও বাটন লক
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> আবেদন জমা দেওয়া হচ্ছে...';
        submitBtn.disabled = true;
    });
}
