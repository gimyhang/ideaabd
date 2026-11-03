{{-- Printing cost & production calculator. Shared by accounting hub, quotations, and invoice forms.
     Logic: public/js/print-cost.js. Hosts define window.addPresetItem(title, spec, itemType, unit, price, regularPrice, qty). --}}
<link rel="stylesheet" href="{{ asset('css/print-cost.css') }}?v={{ @filemtime(public_path('css/print-cost.css')) ?: 2 }}">
<script src="{{ asset('js/print-cost.js') }}?v={{ @filemtime(public_path('js/print-cost.js')) ?: 2 }}" defer></script>

<div class="modal fade" id="printCostCalculatorModal" tabindex="-1" aria-labelledby="pcTitle" aria-hidden="true"
     data-rates-url="{{ route('admin.accounting.print-rates.index') }}"
     data-rates-store="{{ route('admin.accounting.print-rates.store') }}"
     data-rates-adjust="{{ route('admin.accounting.print-rates.adjust') }}"
     data-rates-delete="{{ url('admin/accounting/print-rates') }}">
    <div class="modal-dialog modal-fullscreen-xl-down modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content pc shadow-2xl border-0">
            {{-- Modal Header --}}
            <div class="modal-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="pc-header-icon rounded-circle d-flex align-items-center justify-content-center bg-primary-subtle text-primary" style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-calculator fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0 fs-5" id="pcTitle">মুদ্রণ ও উৎপাদন খরচ ক্যালকুলেটর</h5>
                        <p class="text-muted small mb-0 font-monospace" style="font-size: 11px;">Production Cost Estimator &amp; Quotation Generator</p>
                    </div>
                </div>
                <div class="pc-head-actions d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold shadow-2xs" id="pcRatesBtn" title="কাঁচামালের দর ও রেট চার্ট দেখুন">
                        <i class="fa-solid fa-layer-group me-1.5"></i> রেট চার্ট (Rates)
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5" id="pcReset" title="রিসেট করুন">
                        <i class="fa-solid fa-rotate-left me-1"></i> রিসেট
                    </button>
                    <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-3 p-md-4">
                {{-- 1. Main Calculator View --}}
                <div class="pc-layout" id="pcCalcView">
                    <div class="pc-main d-flex flex-column gap-3">
                        {{-- 1.1 Job Specification Card --}}
                        <section class="pc-card pc-job shadow-2xs">
                            <header class="pc-card-head d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                                <h6 class="pc-h m-0 text-dark fw-bold d-flex align-items-center gap-1.5" style="font-size: 12.5px;">
                                    <i class="fa-solid fa-sliders text-primary"></i> কাজের ধরন ও স্পেসিফিকেশন (Job Specs)
                                </h6>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5" id="pcActiveTypeBadge" style="font-size: 11px;">
                                    Book / বই
                                </span>
                            </header>

                            {{-- Job Type Switcher Pills --}}
                            <div class="pc-types mb-3" id="pcTypes" role="tablist" aria-label="কাজের ধরন নির্বাচন"></div>

                            {{-- Specifications Grid --}}
                            <div class="pc-grid">
                                <div class="pc-f pc-f-wide">
                                    <label for="pcTitleIn" class="form-label small fw-semibold text-dark mb-1">কাজের শিরোনাম / আইটেমের নাম (Job Title)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-tag"></i></span>
                                        <input id="pcTitleIn" class="form-control" maxlength="150" placeholder="যেমন: নতুন উপন্যাস মুদ্রণ ও প্রকাশনা, অথবা স্মরণিকা প্রকাশନା...">
                                    </div>
                                </div>

                                <div class="pc-f">
                                    <label for="pcQty" class="form-label small fw-semibold text-dark mb-1">পরিমাণ (Quantity)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-boxes-stacked"></i></span>
                                        <input id="pcQty" type="number" min="1" step="1" class="form-control text-end font-monospace fw-bold" inputmode="numeric" placeholder="1000">
                                    </div>
                                    <div class="pc-quick mt-1">
                                        @foreach ([250, 500, 1000, 2000, 3000, 5000] as $n)
                                            <button type="button" data-qty="{{ $n }}" class="btn btn-xs btn-outline-secondary rounded-pill py-0 px-2" style="font-size: 10.5px;">{{ number_format($n) }}</button>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="pc-f">
                                    <label for="pcUnit" class="form-label small fw-semibold text-dark mb-1">একক (Unit)</label>
                                    <select id="pcUnit" class="form-select form-select-sm">
                                        @foreach (['Copy' => 'কপি (Copy)', 'Pcs' => 'পিস (Pcs)', 'Book' => 'বই (Book)', 'Set' => 'সেট (Set)', 'Sheet' => 'শীট (Sheet)', 'Ream' => 'রিম (Ream)', 'Box' => 'বক্স (Box)', 'Lot' => 'লট (Lot)'] as $u => $label)
                                            <option value="{{ $u }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="pc-f" id="pcPagesWrap">
                                    <label for="pcPages" class="form-label small fw-semibold text-dark mb-1">মোট পৃষ্ঠা (Pages)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-file-lines"></i></span>
                                        <input id="pcPages" type="number" min="0" step="1" class="form-control text-end font-monospace" inputmode="numeric" placeholder="128">
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-1" id="pcFormasWrap" hidden>
                                        <small class="text-muted" style="font-size: 11px;">ফর্মা: <b id="pcFormas" class="text-primary font-monospace">0</b></small>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="text-muted" style="font-size: 10px;">ফর্মা সাইজ:</span>
                                            <select id="pcFormaSize" class="form-select form-select-xs py-0 px-1 font-monospace" style="font-size: 10.5px; width: 65px; height: 20px;">
                                                <option value="16" selected>16 পৃ.</option>
                                                <option value="8">8 পৃ.</option>
                                                <option value="4">4 পৃ.</option>
                                                <option value="32">32 পৃ.</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="pc-f">
                                    <label for="pcColors" class="form-label small fw-semibold text-dark mb-1">মুদ্রণ রঙ (Print Color)</label>
                                    <select id="pcColors" class="form-select form-select-sm">
                                        <option value="1">১ রঙ (1 Color / Black)</option>
                                        <option value="2">২ রঙ (2 Color)</option>
                                        <option value="4" selected>৪ রঙ (4 Color / Multi)</option>
                                        <option value="5">৫ রঙ (4 Color + Spot)</option>
                                    </select>
                                </div>

                                <div class="pc-f">
                                    <label for="pcSides" class="form-label small fw-semibold text-dark mb-1">প্রিন্ট সাইড (Sides)</label>
                                    <select id="pcSides" class="form-select form-select-sm">
                                        <option value="1" selected>১ পাশ (Single side)</option>
                                        <option value="2">২ পাশ (Both sides)</option>
                                    </select>
                                </div>

                                <div class="pc-f" id="pcPlateMethodWrap">
                                    <label for="pcPlateMethod" class="form-label small fw-semibold text-dark mb-1">প্লেট মেথড (Plate Method)</label>
                                    <select id="pcPlateMethod" class="form-select form-select-sm">
                                        <option value="work_and_turn" selected>ফার্মা উল্টানো (Work &amp; Turn - ১ প্লেট/রঙ)</option>
                                        <option value="sheetwise">শিটওয়াইজ (Sheetwise - ২ প্লেট/রঙ [আলাদা দুই পাশ])</option>
                                    </select>
                                </div>

                                <div class="pc-f pc-f-half">
                                    <label for="pcSize" class="form-label small fw-semibold text-dark mb-1">বই/আইটেম সাইজ (Trim Size)</label>
                                    <select id="pcSize" class="form-select form-select-sm"></select>
                                </div>

                                <div class="pc-f pc-f-half">
                                    <label for="pcSheet" class="form-label small fw-semibold text-dark mb-1">কাগজের সাইজ (Press Sheet)</label>
                                    <select id="pcSheet" class="form-select form-select-sm">
                                        @foreach (['23x36' => '২৩×৩৬ (Demy Standard)', '25x36' => '২৫×৩৬ (Royal/Crown)', '20x30' => '২০×৩০ (Double Crown)', '18x23' => '১৮×২৩ (Half Sheet)', '27x38' => '২৭×৩৮ (Large Sheet)', '31x43' => '৩১×৪৩ (Jumbo Sheet)'] as $v => $label)
                                            <option value="{{ $v }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted mt-1 d-block" id="pcUpsWrap" hidden style="font-size: 11px;">
                                        ১ শিটে আসবে: <b id="pcUps" class="text-success font-monospace">0</b> আপস (Ups)
                                    </small>
                                </div>
                            </div>

                            {{-- Live Production KPI Status Strip --}}
                            <div class="pc-kpi-strip d-flex align-items-stretch gap-2.5 mt-3 pt-3 border-top flex-wrap" id="pcKpiStrip">
                                <div class="pc-kpi-item flex-fill p-2.5 rounded-3 bg-light border shadow-2xs">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="pc-kpi-label small fw-bold text-muted text-uppercase" style="font-size: 10px;">
                                            <i class="fa-solid fa-sheet-plastic text-warning me-1"></i> কাগজের পরিমাণ
                                        </span>
                                        <span class="badge bg-warning-subtle text-warning-emphasis font-monospace px-1.5 py-0.5 rounded-pill" style="font-size: 9.5px;" id="pcKpiPaperWaste">৫% অপচয়</span>
                                    </div>
                                    <div class="pc-kpi-val fs-6 fw-bold font-monospace text-dark" id="pcKpiPaperReams">০.০ রিম</div>
                                    <div class="pc-kpi-sub text-muted font-monospace" style="font-size: 10.5px;" id="pcKpiPaperSheets">০ শিট প্রেস কাগজ</div>
                                </div>

                                <div class="pc-kpi-item flex-fill p-2.5 rounded-3 bg-light border shadow-2xs">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="pc-kpi-label small fw-bold text-muted text-uppercase" style="font-size: 10px;">
                                            <i class="fa-solid fa-book-open text-primary me-1"></i> মোট ফর্মা
                                        </span>
                                        <span class="badge bg-primary-subtle text-primary font-monospace px-1.5 py-0.5 rounded-pill" style="font-size: 9.5px;" id="pcKpiUpsBadge">১ শিটে ১৬ পৃ.</span>
                                    </div>
                                    <div class="pc-kpi-val fs-6 fw-bold font-monospace text-dark" id="pcKpiFormas">০ ফর্মা</div>
                                    <div class="pc-kpi-sub text-muted font-monospace" style="font-size: 10.5px;" id="pcKpiFormaSub">০ পৃষ্ঠা (১৬ পৃ./ফর্মা)</div>
                                </div>

                                <div class="pc-kpi-item flex-fill p-2.5 rounded-3 bg-light border shadow-2xs">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="pc-kpi-label small fw-bold text-muted text-uppercase" style="font-size: 10px;">
                                            <i class="fa-solid fa-barcode text-info me-1"></i> CTP প্লেট
                                        </span>
                                        <span class="badge bg-info-subtle text-info font-monospace px-1.5 py-0.5 rounded-pill" style="font-size: 9.5px;" id="pcKpiPlateMethodBadge">ফার্মা উল্টা</span>
                                    </div>
                                    <div class="pc-kpi-val fs-6 fw-bold font-monospace text-dark" id="pcKpiPlates">০ টি প্লেট</div>
                                    <div class="pc-kpi-sub text-muted font-monospace" style="font-size: 10.5px;" id="pcKpiPlateSub">ইনার ০ + কভার ০</div>
                                </div>

                                <div class="pc-kpi-item flex-fill p-2.5 rounded-3 bg-light border shadow-2xs">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="pc-kpi-label small fw-bold text-muted text-uppercase" style="font-size: 10px;">
                                            <i class="fa-solid fa-print text-danger me-1"></i> ছাপা ইমপ্রেশন
                                        </span>
                                        <span class="badge bg-danger-subtle text-danger font-monospace px-1.5 py-0.5 rounded-pill" style="font-size: 9.5px;" id="pcKpiImpressionBadge" title="১-১,০০০ কপি পর্যন্ত মিনিমাম ১ হাজার চার্জ">১-১০০০ মিনিমাম</span>
                                    </div>
                                    <div class="pc-kpi-val fs-6 fw-bold font-monospace text-dark" id="pcKpiImpressions">০ হাজার</div>
                                    <div class="pc-kpi-sub text-muted font-monospace" style="font-size: 10.5px;" id="pcKpiImpressionSub">মিনিমাম ১০০০ ছাপ ধার্য</div>
                                </div>
                            </div>
                        </section>

                        {{-- 1.2 Component Add Bar --}}
                        <div class="pc-addbar d-flex align-items-center justify-content-between p-2.5 rounded-3 bg-light border flex-wrap gap-2">
                            <span class="pc-addlabel small fw-bold text-muted text-uppercase d-flex align-items-center gap-1" style="font-size: 11px;">
                                <i class="fa-solid fa-plus-circle text-primary"></i> উপাদান যোগ করুন:
                            </span>
                            <div id="pcAddBar" class="pc-addbtns d-flex flex-wrap gap-1.5"></div>
                        </div>

                        {{-- 1.3 Dynamic Component Groups Table --}}
                        <div id="pcGroups" class="pc-groups d-flex flex-column gap-3"></div>
                        <div id="pcLists" hidden></div>

                        {{-- 1.4 Live Specification & Quotation Text Preview --}}
                        <section class="pc-card pc-spec-preview shadow-2xs">
                            <header class="pc-card-head d-flex align-items-center justify-content-between mb-2 pb-1.5 border-bottom">
                                <h6 class="pc-h m-0 text-dark fw-bold d-flex align-items-center gap-1.5" style="font-size: 12px;">
                                    <i class="fa-solid fa-file-invoice text-success"></i> কোটেশন স্পেসিফিকেশন প্রিভিউ (Quotation Spec Preview)
                                </h6>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-muted" id="pcRefreshSpec" title="স্পেসিফিকেশন রিফ্রেশ করুন">
                                    <i class="fa-solid fa-rotate me-1"></i>রিফ্রেশ
                                </button>
                            </header>
                            <div class="pc-spec-box">
                                <textarea id="pcSpecTextarea" class="form-control font-monospace" rows="3" style="font-size: 12px; line-height: 1.5; resize: vertical;" 
                                          placeholder="ক্যালকুলেশন সম্পন্ন হলে এখানে স্বয়ংক্রিয় স্পেসিফিকেশন ও বিবরণ প্রদর্শিত হবে..."></textarea>
                                <div class="d-flex align-items-center justify-content-between mt-1 text-muted small" style="font-size: 11px;">
                                    <span><i class="fa-solid fa-circle-info me-1 text-primary"></i>এই টেক্সটটি ইনভয়েস/কোটেশনে পণ্যের বিবরণ ও স্পেসিফিকেশন হিসেবে যুক্ত হবে।</span>
                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2" id="pcCopySpecBtn">
                                        <i class="fa-regular fa-copy me-1"></i>টেক্সট কপি
                                    </button>
                                </div>
                            </div>
                        </section>
                    </div>

                    {{-- 2. Side Panel: Cost Analysis, Margins & Tiers --}}
                    <aside class="pc-side">
                        <section class="pc-card pc-summary shadow-2xs mb-3">
                            <header class="pc-card-head d-flex align-items-center justify-content-between mb-2 pb-1.5 border-bottom">
                                <h6 class="pc-h m-0 text-dark fw-bold d-flex align-items-center gap-1.5" style="font-size: 12px;">
                                    <i class="fa-solid fa-chart-pie text-primary"></i> খরচ ও মার্জিন বিশ্লেষণ
                                </h6>
                            </header>

                            {{-- Component breakdown visual progress bars --}}
                            <div class="pc-bars mb-3" id="pcBars"></div>

                            {{-- Cost Summary List --}}
                            <dl class="pc-sum m-0">
                                <div class="d-flex align-items-center justify-content-between py-1 border-bottom border-light">
                                    <dt class="small text-muted fw-bold mb-0">উৎপাদন খরচ (Direct)</dt>
                                    <dd id="pcDirect" class="mb-0 fw-bold font-monospace text-dark">৳0.00</dd>
                                </div>

                                <div class="pc-sum-input py-1.5 border-bottom border-light">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <dt class="small text-muted fw-bold mb-0"><label for="pcOverhead">অপচয়/ওভারহেড %</label></dt>
                                        <dd class="mb-0 d-flex align-items-center gap-1.5">
                                            <input id="pcOverhead" type="number" min="0" max="100" step="0.5" class="form-control form-control-sm text-end font-monospace" style="width: 70px;" placeholder="0">
                                            <b id="pcOverheadAmt" class="font-monospace text-muted" style="min-width: 65px; text-align: right;">৳0.00</b>
                                        </dd>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between py-1 border-bottom border-light">
                                    <dt class="small text-muted fw-bold mb-0">মোট খরচ (Cost Price)</dt>
                                    <dd id="pcCost" class="mb-0 fw-bold font-monospace text-dark">৳0.00</dd>
                                </div>

                                <div class="pc-sum-input py-1.5 border-bottom border-light">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <dt class="small text-muted fw-bold mb-0"><label for="pcProfit">লাভের মার্জিন %</label></dt>
                                        <dd class="mb-0 d-flex align-items-center gap-1.5">
                                            <input id="pcProfit" type="number" min="0" max="500" step="0.5" class="form-control form-control-sm text-end font-monospace fw-bold text-success" style="width: 70px;" placeholder="20">
                                            <b id="pcProfitAmt" class="font-monospace text-success" style="min-width: 65px; text-align: right;">৳0.00</b>
                                        </dd>
                                    </div>
                                    <div class="pc-quick pc-quick-profit d-flex justify-content-end gap-1 mt-1">
                                        @foreach ([10, 15, 20, 25, 30, 40] as $p)
                                            <button type="button" data-profit="{{ $p }}" class="btn btn-xs btn-outline-success rounded-pill py-0 px-1.5" style="font-size: 10px;">{{ $p }}%</button>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="pc-sum-input py-1.5 border-bottom border-light">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <dt class="small text-muted fw-bold mb-0"><label for="pcRound">দর রাউন্ডিং</label></dt>
                                        <dd class="mb-0">
                                            <select id="pcRound" class="form-select form-select-sm py-0 px-2 font-monospace" style="font-size: 11px; width: 90px; height: 26px;">
                                                <option value="0">হিসাব অনুযায়ী</option>
                                                <option value="0.5">নিকটবর্তী ০.৫ ৳</option>
                                                <option value="1">নিকটবর্তী ১ ৳</option>
                                                <option value="5">নিকটবর্তী ৫ ৳</option>
                                                <option value="10">নিকটবর্তী ১০ ৳</option>
                                            </select>
                                        </dd>
                                    </div>
                                </div>

                                {{-- Grand Total & Unit Rate Cards --}}
                                <div class="pc-grand pt-2">
                                    <div class="p-2.5 rounded-3 bg-light border d-flex align-items-center justify-content-between mb-2">
                                        <span class="small fw-bold text-uppercase text-muted" style="font-size: 11px;">সর্বমোট মূল্য</span>
                                        <span id="pcTotal" class="fs-5 fw-bold font-monospace text-dark">৳0.00</span>
                                    </div>
                                </div>

                                <div class="pc-unitbox p-3 rounded-3 text-white text-center shadow-xs" style="background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);">
                                    <div class="small fw-semibold opacity-75 mb-1" style="font-size: 11.5px;">
                                        প্রতি ইউনিটের কোটেশন দর <small id="pcRoundedNote" hidden title="Rounded Up">↑</small>
                                    </div>
                                    <div class="fs-3 fw-bold font-monospace" id="pcUnit2">৳0.00</div>
                                </div>
                            </dl>
                        </section>

                        {{-- Quantity Tiers Price Comparison --}}
                        <section class="pc-card pc-tiers shadow-2xs" id="pcTiersWrap" hidden>
                            <header class="pc-card-head d-flex align-items-center justify-content-between mb-2 pb-1.5 border-bottom">
                                <h6 class="pc-h m-0 text-dark fw-bold d-flex align-items-center gap-1" style="font-size: 12px;">
                                    <i class="fa-solid fa-layer-group text-primary"></i> পরিমাণভিত্তিক দর (Tier Rates)
                                </h6>
                                <span class="text-muted" style="font-size: 10px;">ক্লিক করে পরিমাণ পরিবর্তন করুন</span>
                            </header>
                            <div class="pc-tier-list d-flex flex-column gap-1.5" id="pcTiers"></div>
                        </section>
                    </aside>
                </div>

                {{-- 2. Raw Material Rates Sheet Panel --}}
                <div class="pc-rates" id="pcRatesPanel" hidden>
                    <div class="pc-rates-top d-flex align-items-center justify-content-between gap-3 mb-3 pb-2 border-bottom flex-wrap">
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="pcRatesBack">
                                <i class="fa-solid fa-arrow-left me-1"></i> ক্যালকুলেটরে ফিরুন
                            </button>
                            <h6 class="pc-h m-0 text-dark fw-bold fs-6">কাঁচামালের দর ও রেট চার্ট (Raw-Material Rate Sheet)</h6>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-end" style="max-width: 480px;">
                            <input id="pcRatesSearch" type="search" class="form-control form-control-sm rounded-pill" placeholder="কাগজ, প্লেট, বাইন্ডিং সার্চ করুন..." aria-label="Search rates">
                            <select class="form-select form-select-sm rounded-pill font-monospace" id="pcRatesGroups" aria-label="গ্রুপ ফিল্টার" style="max-width: 170px;"></select>
                        </div>
                    </div>

                    <div class="pc-rates-tools row g-3 mb-3">
                        <div class="col-md-7">
                            <form class="pc-card pc-rates-add h-100 shadow-2xs" id="pcRatesAdd" autocomplete="off">
                                <header class="pc-card-head mb-2 pb-1 border-bottom">
                                    <h6 class="pc-h m-0 text-dark fw-bold small"><i class="fa-solid fa-plus text-primary me-1"></i> নতুন দর যুক্ত বা আপডেট করুন</h6>
                                </header>
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-3 col-6">
                                        <label for="prGroup" class="form-label small mb-1">গ্রুপ (Group)</label>
                                        <select id="prGroup" class="form-select form-select-sm"></select>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label for="prItem" class="form-label small mb-1">আইটেম/GSM</label>
                                        <input id="prItem" class="form-control form-control-sm" list="prItems" maxlength="60" placeholder="e.g. 80">
                                        <datalist id="prItems"></datalist>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label for="prKind" class="form-label small mb-1">ধরন (Kind)</label>
                                        <input id="prKind" class="form-control form-control-sm" list="prKinds" maxlength="60" placeholder="e.g. White">
                                        <datalist id="prKinds"></datalist>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label for="prRate" class="form-label small mb-1">দর ৳ (Rate)</label>
                                        <div class="input-group input-group-sm">
                                            <input id="prRate" type="number" min="0" step="0.01" class="form-control form-control-sm text-end font-monospace" required placeholder="0.00">
                                            <button type="submit" class="btn btn-primary btn-sm px-2.5" title="সংরক্ষণ করুন"><i class="fa-solid fa-check"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="col-md-5">
                            <form class="pc-card pc-rates-adjust h-100 shadow-2xs" id="pcRatesAdjust">
                                <header class="pc-card-head mb-2 pb-1 border-bottom">
                                    <h6 class="pc-h m-0 text-dark fw-bold small"><i class="fa-solid fa-percent text-warning me-1"></i> একসাথে রেট বাড়ানো/কমানো</h6>
                                </header>
                                <div class="row g-2 align-items-end">
                                    <div class="col-4">
                                        <label for="paGroup" class="form-label small mb-1">গ্রুপ</label>
                                        <select id="paGroup" class="form-select form-select-sm"></select>
                                    </div>
                                    <div class="col-3">
                                        <label for="paMode" class="form-label small mb-1">মোড</label>
                                        <select id="paMode" class="form-select form-select-sm"><option value="percent">% শতাংশ</option><option value="amount">৳ টাকা</option></select>
                                    </div>
                                    <div class="col-5">
                                        <label for="paValue" class="form-label small mb-1">মান (+ বা -)</label>
                                        <div class="input-group input-group-sm">
                                            <input id="paValue" type="number" step="0.01" class="form-control form-control-sm text-end font-monospace" required placeholder="+5">
                                            <button type="submit" class="btn btn-outline-primary btn-sm px-2.5" title="প্রয়োগ করুন"><i class="fa-solid fa-bolt"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <section class="pc-card pc-rates-table shadow-2xs p-0 overflow-hidden">
                        <div class="table-responsive" style="max-height: 480px;">
                            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                                <thead class="table-light sticky-top" style="border-bottom: 2px solid #e2e8f0;">
                                    <tr>
                                        <th style="width: 140px;">উপাদান গ্রুপ</th>
                                        <th>আইটেম / স্পেসিফিকেশন</th>
                                        <th>ধরন / ফিনিশ</th>
                                        <th class="text-end" style="width: 160px;">একক রেট (৳)</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="pcRatesBody"></tbody>
                            </table>
                        </div>
                        <div class="pc-empty p-4 text-center text-muted" id="pcRatesEmpty" hidden>কোনো রেকর্ড পাওয়া যায়নি</div>
                    </section>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer pc-foot bg-white border-top py-2.5 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2" id="pcFoot">
                <div class="pc-foot-sum d-flex align-items-center gap-3">
                    <span class="pc-missing badge bg-warning text-dark border border-warning-subtle rounded-pill px-2.5 py-1" id="pcMissing" hidden></span>
                    <span class="small text-muted font-monospace">ইউনিট দর: <b id="pcFootUnit" class="text-primary fs-6">৳0.00</b></span>
                    <span class="small text-muted font-monospace">সর্বমোট: <b id="pcFootTotal" class="text-dark fs-6">৳0.00</b></span>
                </div>

                <div class="pc-foot-actions d-flex align-items-center gap-2">
                    {{-- Copy breakdown to clipboard --}}
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="pcCopy" title="পূর্ণাঙ্গ খরচ শিট কপি করুন">
                        <i class="fa-regular fa-copy me-1"></i> শিট কপি
                    </button>

                    {{-- Insert as Itemized Breakdown (New Feature!) --}}
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold" id="pcAddDetailed" disabled title="প্রতিটি উপাদানকে আলাদা আলাদা রো হিসেবে কোটেশনে যুক্ত করুন">
                        <i class="fa-solid fa-list-check me-1.5"></i> ভেঙে আলাদা আইটেম যোগ
                    </button>

                    {{-- Insert as Single Consolidated Line with Full Spec --}}
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 fw-bold shadow-xs" id="pcAdd" disabled title="সম্পূর্ণ স্পেসিফিকেশনসহ একক আইটেম হিসেবে কোটেশনে যোগ করুন">
                        <i class="fa-solid fa-file-circle-plus me-1.5"></i> কোটেশনে একক আইটেম যোগ
                    </button>

                    {{-- Plain (Without rates in spec) --}}
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-2.5 text-muted" id="pcPlain" disabled title="দর ছাড়া শুধুমাত্র স্পেসিফিকেশন দিয়ে যোগ করুন">
                        দর ছাড়া
                    </button>
                </div>
            </div>

            {{-- Floating Toast Notification --}}
            <div class="pc-toast shadow-lg" id="pcToast" role="status" hidden></div>
        </div>
    </div>
</div>
