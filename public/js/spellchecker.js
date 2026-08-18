/**
 * Bengali Academy & English Literary Spell Checker Engine
 * Standardizes common unstandardized Bengali spellings and fixes English typos.
 * (c) Idea Prokashon / ideaabd.com
 */

const BENGALI_SPELL_DICT = {
    // ণ-ত্ব ও ষ-ত্ব বিধান এবং প্রমিত রূপ
    'দূরবস্থা': 'দুরবস্থা',
    'পুরষ্কার': 'পুরস্কার',
    'তিরষ্কার': 'তিরস্কার',
    'আবিষ্কার': 'আবিষ্কার',
    'পুর্নবাসন': 'পুনর্বাসন',
    'পূনর্বাসন': 'পুনর্বাসন',
    'পুনরুত্থান': 'পুনরুত্থান',
    'পূনর্গঠন': 'পুনর্গঠন',
    'পুর্নগঠন': 'পুনর্গঠন',
    'পূনরাবৃত্তি': 'পুনরাবৃত্তি',
    'শুদ্ধিকরন': 'শুদ্ধিকরণ',
    'স্মরন': 'স্মরণ',
    'স্মরনীয়': 'স্মরণীয়',
    'বর্ননা': 'বর্ণনা',
    'কারন': 'কারণ',
    'ধারন': 'ধারণ',
    'গ্রহন': 'গ্রহণ',
    'প্রনয়ন': 'প্রণয়ন',
    'প্রনালী': 'প্রণালী',

    // -তা / -ত্ব প্রত্যয়যুক্ত শব্দে হ্রস্ব 'ই'
    'সহযোগীতা': 'সহযোগিতা',
    'প্রতিযোগীতা': 'প্রতিযোগিতা',
    'উপযোগীতা': 'উপযোগিতা',
    'স্থায়ীত্ব': 'স্থায়িত্ব',
    'দায়ীত্ব': 'দায়িত্ব',
    'কৃতীত্ব': 'কৃতিত্ব',
    'অধীনস্ত': 'অধীনস্থ',
    'উপস্থিতিী': 'উপস্থিতি',

    // 'অঞ্জলি' যুক্ত শব্দে হ্রস্ব 'ই'
    'শ্রদ্ধাঞ্জলী': 'শ্রদ্ধাঞ্জলি',
    'গীতাঞ্জলী': 'গীতাঞ্জলি',
    'পুষ্পাঞ্জলী': 'পুষ্পাঞ্জলি',
    'জলাঞ্জলী': 'জলাঞ্জলি',
    'ধর্মাঞ্জলী': 'ধর্মাঞ্জলি',

    // প্রচলিত অতি-সাধারণ ভুল বানান
    'উপলক্ষ্য': 'উপলক্ষ',
    'ইতিপূর্বে': 'ইতোপূর্বে',
    'ইতিমধ্যে': 'ইতোমধ্যে',
    'অনুর্ধ্ব': 'অনূর্ধ্ব',
    'অনূর্ধ': 'অনূর্ধ্ব',
    'উজ্জল': 'উজ্জ্বল',
    'কৃতি': 'কৃতী',
    'মনকষ্ট': 'মনঃকষ্ট',
    'শিরোচ্ছেদ': 'শিরচ্ছেদ',
    'দারিদ্র্যতা': 'দারিদ্র্য বা দরিদ্রতা',
    'উৎকর্ষতা': 'উৎকর্ষ বা উৎকৃষ্টতা',
    'সাফল্যতা': 'সাফল্য বা সফলতা',
    'ঐক্যতা': 'ঐক্য বা একতা',
    'অধ্যাবসায়': 'অধ্যবসায়',
    'মুহুর্ত': 'মুহূর্ত',
    'মুহুর্ত্ত': 'মুহূর্ত',
    'সান্ধ': 'সান্ধ্য',
    'সমীচিন': 'সমীচীন',
    'সমিচীন': 'সমীচীন',
    'বুদ্ধিজীবি': 'বুদ্ধিজীবী',
    'আইনজীবি': 'আইনজীবী',
    'পেশাজীবি': 'পেশাজীবী',
    'শ্রমজীবি': 'শ্রমজীবী',
    'চাকরী': 'চাকরি',
    'পাখী': 'পাখি',
    'বাড়ী': 'বাড়ি',
    'শাড়ী': 'শাড়ি',
    'বেশী': 'বেশি',
    'সরকারী': 'সরকারি',
    'সরকারীভাবে': 'সরকারিভাবে',
    'বেসরকারী': 'বেসরকারি',
    'বেসরকারীভাবে': 'বেসরকারিভাবে',
    'তরকারী': 'তরকারি',
    'দোকানী': 'দোকানি',
    'জানুয়ারী': 'জানুয়ারি',
    'ফেব্রুয়ারী': 'ফেব্রুয়ারি',
    'মার্চ': 'মার্চ',
    'এপ্রিল': 'এপ্রিল',
    'মে': 'মে',
    'জুন': 'জুন',
    'জুলাই': 'জুলাই',
    'আগষ্ট': 'আগস্ট',
    'সেপ্টেম্বর': 'সেপ্টেম্বর',
    'অক্টোবর': 'অক্টোবর',
    'নভেম্বর': 'নভেম্বর',
    'ডিসেম্বর': 'ডিসেম্বর',
    'ইংরেজী': 'ইংরেজি',
    'বাঙালী': 'বাঙালি',
    'ধন্নবাদ': 'ধন্যবাদ',
    'অভিনন্ধন': 'অভিনন্দন',
    'স্বাগতম্': 'স্বাগত বা স্বাগতম',
    'ইতাদি': 'ইত্যাদি',
    'সাক্ষাতকার': 'সাক্ষাৎকার',
    'শান্তনা': 'সান্ত্বনা',
    'সান্তনা': 'সান্ত্বনা',
    'উত্তক্ত': 'উত্ত্যক্ত',
    'পুনপুন': 'পুনঃপুন',
    'মুমুর্ষু': 'মুমূর্ষু',
    'মুমূর্ষূ': 'মুমূর্ষু',
    'অহঙ্কার': 'অহংকার',
    'শৃংখলা': 'শৃঙ্খলা',
    'স্বত্বাধিকারী': 'স্বত্বাধিকারী',
    'সত্ত্বাধিকারী': 'স্বত্বাধিকারী',
    'স্বার্থকতা': 'সার্থকতা',
    'নিশব্দ': 'নিঃশব্দ',
    'দুরাবস্থা': 'দুরবস্থা',
    'মুল্যায়ন': 'মূল্যায়ন',
    'মুল্যায়ন': 'মূল্যায়ন',
    'মূল্যায়ন': 'মূল্যায়ন',
    'অসাধারন': 'অসাধারণ',
    'সাধারন': 'সাধারণ',
    'উদ্ভোদন': 'উদ্বোধন',
    'উদ্বোধন': 'উদ্বোধন',
    'প্রতিদ্বন্দিতা': 'প্রতিদ্বন্দ্বিতা',
    'প্রতিদ্বন্দী': 'প্রতিদ্বন্দ্বী',
    'মৎস': 'মৎস্য',
    'ব্যাতিক্রম': 'ব্যতিক্রম',
    'ব্যাতীত': 'ব্যতীত',
    'ব্যতিত': 'ব্যতীত',
    'ব্যবস্তা': 'ব্যবস্থা',
    'পরিস্কার': 'পরিষ্কার',
    'পরিষ্কার': 'পরিষ্কার',
    'শুশ্রূষা': 'শুশ্রূষা',
    'সুশ্রুষা': 'শুশ্রূষা',
    'সুশ্রূষা': 'শুশ্রূষা',
    'স্বনির্ভর': 'স্বনির্ভর',
    'সনির্ভর': 'স্বনির্ভর',
    'স্বরাষ্ট্রমন্ত্রী': 'স্বরাষ্ট্রমন্ত্রী',
    'তথ্যমন্ত্রী': 'তথ্যমন্ত্রী',
    'প্রধানমন্ত্রী': 'প্রধানমন্ত্রী',
    'শিক্ষামন্ত্রী': 'শিক্ষামন্ত্রী',
    'মন্ত্রণালয়': 'মন্ত্রণালয়',
    'মন্ত্রনালয়': 'মন্ত্রণালয়'
};

const ENGLISH_SPELL_DICT = {
    'teh': 'the',
    'recieve': 'receive',
    'seperate': 'separate',
    'occured': 'occurred',
    'untill': 'until',
    'definately': 'definitely',
    'truely': 'truly',
    'calender': 'calendar',
    'goverment': 'government',
    'enviroment': 'environment',
    'recomended': 'recommended',
    'writting': 'writing',
    'comming': 'coming',
    'acommodate': 'accommodate',
    'beleive': 'believe',
    'becuase': 'because',
    'alot': 'a lot',
    'wierd': 'weird',
    'privilege': 'privilege',
    'privelege': 'privilege'
};

let spellCheckerActiveMap = {};

function onEditorInputWithSpellCheck(name) {
    if (typeof syncEditorToTextarea === 'function') {
        syncEditorToTextarea(name);
    }
    if (spellCheckerActiveMap[name]) {
        runSpellChecker(name, false);
    }
}

function toggleSpellChecker(name) {
    spellCheckerActiveMap[name] = !spellCheckerActiveMap[name];
    const btn = document.getElementById('spellBtn-' + name);
    const btnText = document.getElementById('spellBtnText-' + name);
    const resultsBox = document.getElementById('spell-results-' + name);

    if (spellCheckerActiveMap[name]) {
        if (btn) {
            btn.classList.remove('btn-outline-warning');
            btn.classList.add('btn-warning');
        }
        if (btnText) btnText.textContent = 'বানান চেকার চালু';
        runSpellChecker(name, true);
    } else {
        if (btn) {
            btn.classList.remove('btn-warning');
            btn.classList.add('btn-outline-warning');
        }
        if (btnText) btnText.textContent = 'বানান পরীক্ষা';
        if (resultsBox) {
            resultsBox.classList.add('d-none');
            resultsBox.innerHTML = '';
        }
    }
}

function runSpellChecker(name, autoScroll = false) {
    const editor = document.getElementById('editable-' + name);
    const resultsBox = document.getElementById('spell-results-' + name);
    if (!editor || !resultsBox) return;

    const text = editor.innerText || editor.textContent || '';
    if (!text.trim()) {
        resultsBox.classList.remove('d-none');
        resultsBox.innerHTML = `
            <div class="alert alert-info py-2 px-3 rounded-3 small mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-info-circle text-primary"></i> বক্সে কোনো লেখা নেই। লেখা টাইপ বা পেস্ট করে বানান পরীক্ষা করুন।
            </div>`;
        return;
    }

    // Scan for all mistakes
    const detected = [];

    // Scan Bengali dictionary
    for (const [wrong, correct] of Object.entries(BENGALI_SPELL_DICT)) {
        const regex = new RegExp('(^|[\\s,।!?;:"\'()«»–—\\[\\]])(' + escapeRegExp(wrong) + ')(?=[\\s,।!?;:"\'()«»–—\\[\\]]|$)', 'g');
        let count = 0;
        let match;
        while ((match = regex.exec(text)) !== null) {
            count++;
        }
        if (count > 0) {
            detected.push({ wrong, correct, count, lang: 'bn' });
        }
    }

    // Scan English dictionary
    for (const [wrong, correct] of Object.entries(ENGLISH_SPELL_DICT)) {
        const regex = new RegExp('\\b' + escapeRegExp(wrong) + '\\b', 'gi');
        let count = 0;
        let match;
        while ((match = regex.exec(text)) !== null) {
            count++;
        }
        if (count > 0) {
            detected.push({ wrong, correct, count, lang: 'en' });
        }
    }

    resultsBox.classList.remove('d-none');

    if (detected.length === 0) {
        resultsBox.innerHTML = `
            <div class="alert alert-success py-2.5 px-3 rounded-3 border-0 shadow-xs mb-0 d-flex align-items-center justify-content-between gap-2" style="background: #f0fdf4; border-left: 4px solid #16a34a !important;">
                <div class="d-flex align-items-center gap-2 text-success fw-semibold small">
                    <i class="fas fa-circle-check fs-5"></i>
                    <span>চমৎকার! লেখায় কোনো অপ্রমিত বা ভুল বানান শনাক্ত হয়নি। আপনি পোস্ট করার জন্য প্রস্তুত।</span>
                </div>
                <span class="badge bg-success small rounded-pill">১০০% প্রমিত</span>
            </div>`;
    } else {
        let chipsHtml = '';
        detected.forEach(item => {
            chipsHtml += `
                <button type="button" class="btn btn-sm btn-light border text-start d-inline-flex align-items-center gap-1.5 py-1 px-2.5 rounded-pill shadow-xs hover-shadow" 
                        onclick="fixSingleMistake('${name}', '${escapeHtmlAttr(item.wrong)}', '${escapeHtmlAttr(item.correct)}')"
                        title="ক্লিক করলেই '${item.wrong}' বদলে '${item.correct}' হয়ে যাবে">
                    <span class="text-danger text-decoration-line-through fw-bold" style="font-size: 0.85rem;">${item.wrong}</span>
                    <i class="fas fa-arrow-right text-success small"></i>
                    <span class="text-success fw-bold" style="font-size: 0.88rem;">${item.correct}</span>
                    ${item.count > 1 ? `<span class="badge bg-danger rounded-pill" style="font-size: 0.65rem;">${item.count} বার</span>` : ''}
                </button>`;
        });

        resultsBox.innerHTML = `
            <div class="alert alert-warning p-3 rounded-4 border-0 shadow-sm mb-0 position-relative overflow-hidden" 
                 style="background: #fffbeb; border-left: 4px solid #f59e0b !important;">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 pb-2 border-bottom border-warning border-opacity-25">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-triangle-exclamation text-warning fs-5"></i>
                        <span class="fw-bold text-dark" style="font-size: 0.92rem;">
                            পোস্ট করার আগে বানানগুলো চেক করে নিন (<span class="text-danger fw-bold">${detected.length}টি</span> সম্ভাব্য অপ্রমিত বানান পাওয়া গেছে)
                        </span>
                    </div>
                    <button type="button" class="btn btn-xs btn-success rounded-pill px-3 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1" 
                            onclick="fixAllMistakes('${name}')">
                        <i class="fas fa-wand-magic-sparkles"></i> সবগুলো একসাথে প্রমিত করুন
                    </button>
                </div>
                <div class="d-flex flex-wrap gap-2 pt-1">
                    ${chipsHtml}
                </div>
                <div class="text-muted small mt-2" style="font-size: 11px;">
                    <i class="fas fa-circle-info text-primary me-1"></i> বাংলা একাডেমি প্রমিত বানানরীতি অনুসারে উপরে প্রস্তাবিত শুদ্ধ রূপে ক্লিক করে তাৎক্ষণিক পরিবর্তন করতে পারেন।
                </div>
            </div>`;

        if (autoScroll) {
            resultsBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
}

function fixSingleMistake(name, wrongWord, rightWord) {
    const editor = document.getElementById('editable-' + name);
    if (!editor) return;

    // Replace all instances of wrongWord in HTML
    const regex = new RegExp(escapeRegExp(wrongWord), 'g');
    editor.innerHTML = editor.innerHTML.replace(regex, rightWord);

    if (typeof syncEditorToTextarea === 'function') {
        syncEditorToTextarea(name);
    }

    // Re-run spellchecker
    runSpellChecker(name, false);
}

function fixAllMistakes(name) {
    const editor = document.getElementById('editable-' + name);
    if (!editor) return;

    let content = editor.innerHTML;

    for (const [wrong, correct] of Object.entries(BENGALI_SPELL_DICT)) {
        const regex = new RegExp(escapeRegExp(wrong), 'g');
        content = content.replace(regex, correct);
    }

    for (const [wrong, correct] of Object.entries(ENGLISH_SPELL_DICT)) {
        const regex = new RegExp('\\b' + escapeRegExp(wrong) + '\\b', 'gi');
        content = content.replace(regex, correct);
    }

    editor.innerHTML = content;

    if (typeof syncEditorToTextarea === 'function') {
        syncEditorToTextarea(name);
    }

    runSpellChecker(name, false);
}

function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function escapeHtmlAttr(string) {
    return string.replace(/'/g, "\\'").replace(/"/g, '&quot;');
}
