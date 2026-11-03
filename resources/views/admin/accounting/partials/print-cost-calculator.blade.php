{{-- Printing cost & production calculator. Shared by accounting hub, quotations, and invoice forms.
     Logic: public/js/print-cost.js. Hosts define window.addPresetItem(title, spec, itemType, unit, price, regularPrice, qty). --}}
<link rel="stylesheet" href="{{ asset('css/print-cost.css') }}?v={{ @filemtime(public_path('css/print-cost.css')) ?: 3 }}">
<script src="{{ asset('js/print-cost.js') }}?v={{ @filemtime(public_path('js/print-cost.js')) ?: 3 }}" defer></script>

<div class="modal fade" id="printCostCalculatorModal" tabindex="-1" aria-labelledby="pcTitle" aria-hidden="true"
     data-rates-url="{{ route('admin.accounting.print-rates.index') }}"
     data-rates-store="{{ route('admin.accounting.print-rates.store') }}"
     data-rates-adjust="{{ route('admin.accounting.print-rates.adjust') }}"
     data-rates-delete="{{ url('admin/accounting/print-rates') }}">
    <div class="modal-dialog modal-fullscreen-md-down modal-dialog-centered modal-dialog-scrollable pc-modal-dialog">
        <div class="modal-content pc shadow-sm">
            {{-- Modal Header --}}
            <div class="modal-header bg-white border-bottom py-2.5 px-4">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="pc-header-icon rounded-2 d-flex align-items-center justify-content-center bg-primary-subtle text-primary">
                        <i class="fa-solid fa-calculator fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0 fs-5" id="pcTitle">Calculator</h5>
                        <p class="text-muted small mb-0 font-monospace" style="font-size: 11px;">Cost Estimator &amp; Quotation Generator</p>
                    </div>
                </div>
                <div class="pc-head-actions d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-2 px-3 fw-semibold" id="pcRatesBtn" title="Rates">
                        <i class="fa-solid fa-layer-group me-1.5"></i> Rates
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-2.5" id="pcReset" title="Reset">
                        <i class="fa-solid fa-rotate-left me-1"></i> Reset
                    </button>
                    <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-3 p-md-4">
                {{-- 1. Main Calculator View --}}
                <div class="pc-layout" id="pcCalcView">
                    <div class="pc-main d-flex flex-column gap-3">
                        {{-- 1.1 Job Specification Card --}}
                        <section class="pc-card pc-job shadow-xs">
                            <header class="pc-card-head d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <h6 class="pc-h m-0 text-dark fw-bold d-flex align-items-center gap-1.5">
                                    <i class="fa-solid fa-sliders text-primary"></i> Specs
                                </h6>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-2 px-2 py-1 font-monospace" id="pcActiveTypeBadge" style="font-size: 11px;">
                                    Book
                                </span>
                            </header>

                            {{-- Product Selection Segmented Tab Bar --}}
                            <div class="pc-types mb-3" id="pcTypes" role="tablist" aria-label="Job type"></div>

                            {{-- Specifications Grid Layout --}}
                            <div class="pc-grid">
                                <div class="pc-f pc-f-wide">
                                    <label for="pcTitleIn" class="pc-label">Title</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-tag"></i></span>
                                        <input id="pcTitleIn" class="form-control" maxlength="150" placeholder="Job / Item title...">
                                    </div>
                                </div>

                                <div class="pc-f">
                                    <label for="pcQty" class="pc-label">Quantity</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-boxes-stacked"></i></span>
                                        <input id="pcQty" type="number" min="1" step="1" class="form-control text-end font-monospace fw-bold" inputmode="numeric" placeholder="1000">
                                    </div>
                                    <div class="pc-quick mt-1">
                                        @foreach ([250, 500, 1000, 2000, 3000, 5000] as $n)
                                            <button type="button" data-qty="{{ $n }}" class="pc-quick-chip">{{ number_format($n) }}</button>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="pc-f">
                                    <label for="pcUnit" class="pc-label">Unit</label>
                                    <select id="pcUnit" class="form-select form-select-sm">
                                        @foreach (['Copy' => 'Copy', 'Pcs' => 'Pcs', 'Book' => 'Book', 'Set' => 'Set', 'Sheet' => 'Sheet', 'Ream' => 'Ream', 'Box' => 'Box', 'Lot' => 'Lot'] as $u => $label)
                                            <option value="{{ $u }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="pc-f" id="pcPagesWrap">
                                    <label for="pcPages" class="pc-label">Pages</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-file-lines"></i></span>
                                        <input id="pcPages" type="number" min="0" step="1" class="form-control text-end font-monospace" inputmode="numeric" placeholder="128">
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-1" id="pcFormasWrap" hidden>
                                        <small class="text-muted" style="font-size: 11px;">Formas: <b id="pcFormas" class="text-primary font-monospace">0</b></small>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="text-muted" style="font-size: 10px;">Size:</span>
                                            <select id="pcFormaSize" class="form-select form-select-xs py-0 px-1 font-monospace" style="font-size: 10.5px; width: 65px; height: 22px;">
                                                <option value="16" selected>16p</option>
                                                <option value="8">8p</option>
                                                <option value="4">4p</option>
                                                <option value="32">32p</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="pc-f">
                                    <label for="pcColors" class="pc-label">Colors</label>
                                    <select id="pcColors" class="form-select form-select-sm">
                                        <option value="1">1 Color</option>
                                        <option value="2">2 Colors</option>
                                        <option value="4" selected>4 Colors</option>
                                        <option value="5">5 Colors</option>
                                    </select>
                                </div>

                                <div class="pc-f">
                                    <label for="pcSides" class="pc-label">Sides</label>
                                    <select id="pcSides" class="form-select form-select-sm">
                                        <option value="1" selected>1 Side</option>
                                        <option value="2">2 Sides</option>
                                    </select>
                                </div>

                                <div class="pc-f" id="pcPlateMethodWrap">
                                    <label for="pcPlateMethod" class="pc-label">Plates</label>
                                    <select id="pcPlateMethod" class="form-select form-select-sm">
                                        <option value="work_and_turn" selected>Work &amp; Turn</option>
                                        <option value="sheetwise">Sheetwise</option>
                                    </select>
                                </div>

                                <div class="pc-f pc-f-half">
                                    <label for="pcSize" class="pc-label">Trim</label>
                                    <select id="pcSize" class="form-select form-select-sm"></select>
                                </div>

                                <div class="pc-f pc-f-half">
                                    <label for="pcSheet" class="pc-label">Sheet</label>
                                    <select id="pcSheet" class="form-select form-select-sm">
                                        @foreach (['23x36' => '23×36 (Demy)', '25x36' => '25×36 (Royal)', '20x30' => '20×30 (Crown)', '18x23' => '18×23 (Half)', '27x38' => '27×38 (Large)', '31x43' => '31×43 (Jumbo)'] as $v => $label)
                                            <option value="{{ $v }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted mt-1 d-block" id="pcUpsWrap" hidden style="font-size: 11px;">
                                        Ups: <b id="pcUps" class="text-success font-monospace">0</b>
                                    </small>
                                </div>
                            </div>

                            {{-- Live Production KPI Status Strip --}}
                            <div class="pc-kpi-strip d-flex align-items-stretch gap-2.5 mt-3 pt-3 border-top flex-wrap" id="pcKpiStrip">
                                <div class="pc-kpi-item flex-fill p-2.5 rounded-2 bg-light border">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="pc-kpi-label small fw-bold text-muted text-uppercase" style="font-size: 10.5px;">
                                            <i class="fa-solid fa-sheet-plastic text-warning me-1"></i> Paper
                                        </span>
                                        <span class="badge bg-warning-subtle text-warning-emphasis font-monospace px-1.5 py-0.5 rounded-1" style="font-size: 9.5px;" id="pcKpiPaperWaste">5% Waste</span>
                                    </div>
                                    <div class="pc-kpi-val fs-6 fw-bold font-monospace text-dark" id="pcKpiPaperReams">0.0 Reams</div>
                                    <div class="pc-kpi-sub text-muted font-monospace" style="font-size: 10.5px;" id="pcKpiPaperSheets">0 Sheets</div>
                                </div>

                                <div class="pc-kpi-item flex-fill p-2.5 rounded-2 bg-light border">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="pc-kpi-label small fw-bold text-muted text-uppercase" style="font-size: 10.5px;">
                                            <i class="fa-solid fa-book-open text-primary me-1"></i> Formas
                                        </span>
                                        <span class="badge bg-primary-subtle text-primary font-monospace px-1.5 py-0.5 rounded-1" style="font-size: 9.5px;" id="pcKpiUpsBadge">16p / Sheet</span>
                                    </div>
                                    <div class="pc-kpi-val fs-6 fw-bold font-monospace text-dark" id="pcKpiFormas">0 Formas</div>
                                    <div class="pc-kpi-sub text-muted font-monospace" style="font-size: 10.5px;" id="pcKpiFormaSub">0 Pages</div>
                                </div>

                                <div class="pc-kpi-item flex-fill p-2.5 rounded-2 bg-light border">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="pc-kpi-label small fw-bold text-muted text-uppercase" style="font-size: 10.5px;">
                                            <i class="fa-solid fa-barcode text-info me-1"></i> Plates
                                        </span>
                                        <span class="badge bg-info-subtle text-info font-monospace px-1.5 py-0.5 rounded-1" style="font-size: 9.5px;" id="pcKpiPlateMethodBadge">Work &amp; Turn</span>
                                    </div>
                                    <div class="pc-kpi-val fs-6 fw-bold font-monospace text-dark" id="pcKpiPlates">0 Plates</div>
                                    <div class="pc-kpi-sub text-muted font-monospace" style="font-size: 10.5px;" id="pcKpiPlateSub">Inner 0 + Cover 0</div>
                                </div>

                                <div class="pc-kpi-item flex-fill p-2.5 rounded-2 bg-light border">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="pc-kpi-label small fw-bold text-muted text-uppercase" style="font-size: 10.5px;">
                                            <i class="fa-solid fa-print text-danger me-1"></i> Impressions
                                        </span>
                                        <span class="badge bg-danger-subtle text-danger font-monospace px-1.5 py-0.5 rounded-1" style="font-size: 9.5px;" id="pcKpiImpressionBadge" title="Minimum 1,000 charge">Min 1,000</span>
                                    </div>
                                    <div class="pc-kpi-val fs-6 fw-bold font-monospace text-dark" id="pcKpiImpressions">0 K</div>
                                    <div class="pc-kpi-sub text-muted font-monospace" style="font-size: 10.5px;" id="pcKpiImpressionSub">Min 1,000 charge</div>
                                </div>
                            </div>
                        </section>

                        {{-- 1.2 Component Add Bar --}}
                        <div class="pc-addbar d-flex align-items-center justify-content-between p-2.5 rounded-2 bg-white border flex-wrap gap-2 shadow-xs">
                            <span class="pc-addlabel small fw-bold text-muted text-uppercase d-flex align-items-center gap-1.5" style="font-size: 11px;">
                                <i class="fa-solid fa-plus-circle text-primary"></i> Add:
                            </span>
                            <div id="pcAddBar" class="pc-addbtns d-flex flex-wrap gap-1.5"></div>
                        </div>

                        {{-- 1.3 Dynamic Component Groups Table (Accordion) --}}
                        <div id="pcGroups" class="pc-groups d-flex flex-column gap-2.5"></div>
                        <div id="pcLists" hidden></div>

                        {{-- 1.4 Live Specification & Quotation Text Preview --}}
                        <section class="pc-card pc-spec-preview shadow-xs">
                            <header class="pc-card-head d-flex align-items-center justify-content-between mb-2 pb-1.5 border-bottom">
                                <h6 class="pc-h m-0 text-dark fw-bold d-flex align-items-center gap-1.5" style="font-size: 12px;">
                                    <i class="fa-solid fa-file-invoice text-success"></i> Preview
                                </h6>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-muted" id="pcRefreshSpec" title="Refresh">
                                    <i class="fa-solid fa-rotate me-1"></i>Refresh
                                </button>
                            </header>
                            <div class="pc-spec-box">
                                <textarea id="pcSpecTextarea" class="form-control font-monospace" rows="3" style="font-size: 12px; line-height: 1.5; resize: vertical;" 
                                          placeholder="Specifications will be generated automatically..."></textarea>
                                <div class="d-flex align-items-center justify-content-between mt-1 text-muted small" style="font-size: 11px;">
                                    <span><i class="fa-solid fa-circle-info me-1 text-primary"></i>Included in quotation details.</span>
                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-2 px-2 py-0.5" id="pcCopySpecBtn">
                                        <i class="fa-regular fa-copy me-1"></i>Copy
                                    </button>
                                </div>
                            </div>
                        </section>
                    </div>

                    {{-- 2. Side Panel: Cost Analysis, Margins & Tiers (Sticky on Desktop) --}}
                    <aside class="pc-side">
                        <section class="pc-card pc-summary shadow-xs mb-3">
                            <header class="pc-card-head d-flex align-items-center justify-content-between mb-2.5 pb-1.5 border-bottom">
                                <h6 class="pc-h m-0 text-dark fw-bold d-flex align-items-center gap-1.5" style="font-size: 12px;">
                                    <i class="fa-solid fa-chart-pie text-primary"></i> Summary
                                </h6>
                            </header>

                            {{-- Component breakdown visual progress bars --}}
                            <div class="pc-bars mb-3" id="pcBars"></div>

                            {{-- Cost Summary List --}}
                            <dl class="pc-sum m-0">
                                <div class="d-flex align-items-center justify-content-between py-1.5 border-bottom border-light">
                                    <dt class="small text-muted fw-bold mb-0">Direct</dt>
                                    <dd id="pcDirect" class="mb-0 fw-bold font-monospace text-dark">৳0.00</dd>
                                </div>

                                <div class="pc-sum-input py-1.5 border-bottom border-light">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <dt class="small text-muted fw-bold mb-0"><label for="pcOverhead">Overhead %</label></dt>
                                        <dd class="mb-0 d-flex align-items-center gap-1.5">
                                            <input id="pcOverhead" type="number" min="0" max="100" step="0.5" class="form-control form-control-sm text-end font-monospace rounded-2" style="width: 70px;" placeholder="0">
                                            <b id="pcOverheadAmt" class="font-monospace text-muted" style="min-width: 65px; text-align: right;">৳0.00</b>
                                        </dd>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between py-1.5 border-bottom border-light">
                                    <dt class="small text-muted fw-bold mb-0">Cost</dt>
                                    <dd id="pcCost" class="mb-0 fw-bold font-monospace text-dark">৳0.00</dd>
                                </div>

                                <div class="pc-sum-input py-1.5 border-bottom border-light">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <dt class="small text-muted fw-bold mb-0"><label for="pcProfit">Profit %</label></dt>
                                        <dd class="mb-0 d-flex align-items-center gap-1.5">
                                            <input id="pcProfit" type="number" min="0" max="500" step="0.5" class="form-control form-control-sm text-end font-monospace fw-bold text-success rounded-2" style="width: 70px;" placeholder="20">
                                            <b id="pcProfitAmt" class="font-monospace text-success" style="min-width: 65px; text-align: right;">৳0.00</b>
                                        </dd>
                                    </div>
                                    <div class="pc-quick pc-quick-profit d-flex justify-content-end gap-1 mt-1.5">
                                        @foreach ([10, 15, 20, 25, 30, 40] as $p)
                                            <button type="button" data-profit="{{ $p }}" class="pc-profit-chip">{{ $p }}%</button>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="pc-sum-input py-1.5 border-bottom border-light">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <dt class="small text-muted fw-bold mb-0"><label for="pcRound">Rounding</label></dt>
                                        <dd class="mb-0">
                                            <select id="pcRound" class="form-select form-select-sm py-0 px-2 font-monospace rounded-2" style="font-size: 11px; width: 95px; height: 28px;">
                                                <option value="0">Exact</option>
                                                <option value="0.5">0.5</option>
                                                <option value="1">1.0</option>
                                                <option value="5">5.0</option>
                                                <option value="10">10.0</option>
                                            </select>
                                        </dd>
                                    </div>
                                </div>

                                {{-- Grand Total & Unit Rate Hero Card --}}
                                <div class="pc-grand pt-2">
                                    <div class="p-2.5 rounded-2 bg-light border d-flex align-items-center justify-content-between mb-2">
                                        <span class="small fw-bold text-uppercase text-muted" style="font-size: 11px;">Total</span>
                                        <span id="pcTotal" class="fs-5 fw-bold font-monospace text-dark">৳0.00</span>
                                    </div>
                                </div>

                                <div class="pc-unitbox p-3 rounded-2 text-white text-center shadow-xs">
                                    <div class="small fw-semibold opacity-75 mb-1 text-uppercase" style="font-size: 11px; letter-spacing: 0.04em;">
                                        Unit Price <small id="pcRoundedNote" hidden title="Rounded Up">↑</small>
                                    </div>
                                    <div class="fs-3 fw-bold font-monospace" id="pcUnit2">৳0.00</div>
                                </div>
                            </dl>
                        </section>

                        {{-- Quantity Tiers Price Comparison --}}
                        <section class="pc-card pc-tiers shadow-xs" id="pcTiersWrap" hidden>
                            <header class="pc-card-head d-flex align-items-center justify-content-between mb-2 pb-1.5 border-bottom">
                                <h6 class="pc-h m-0 text-dark fw-bold d-flex align-items-center gap-1" style="font-size: 12px;">
                                    <i class="fa-solid fa-layer-group text-primary"></i> Tiers
                                </h6>
                                <span class="text-muted" style="font-size: 10px;">Select</span>
                            </header>
                            <div class="pc-tier-list d-flex flex-column gap-1.5" id="pcTiers"></div>
                        </section>
                    </aside>
                </div>

                {{-- 2. Raw Material Rates Sheet Panel --}}
                <div class="pc-rates" id="pcRatesPanel" hidden>
                    <div class="pc-rates-top d-flex align-items-center justify-content-between gap-3 mb-3 pb-2 border-bottom flex-wrap">
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3" id="pcRatesBack">
                                <i class="fa-solid fa-arrow-left me-1"></i> Back
                            </button>
                            <h6 class="pc-h m-0 text-dark fw-bold fs-6">Rates</h6>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-end" style="max-width: 480px;">
                            <input id="pcRatesSearch" type="search" class="form-control form-control-sm rounded-2" placeholder="Search..." aria-label="Search rates">
                            <select class="form-select form-select-sm rounded-2 font-monospace" id="pcRatesGroups" aria-label="Group" style="max-width: 170px;"></select>
                        </div>
                    </div>

                    <div class="pc-rates-tools row g-3 mb-3">
                        <div class="col-md-7">
                            <form class="pc-card pc-rates-add h-100 shadow-xs" id="pcRatesAdd" autocomplete="off">
                                <header class="pc-card-head mb-2 pb-1 border-bottom">
                                    <h6 class="pc-h m-0 text-dark fw-bold small"><i class="fa-solid fa-plus text-primary me-1"></i> New</h6>
                                </header>
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-3 col-6">
                                        <label for="prGroup" class="form-label small mb-1">Group</label>
                                        <select id="prGroup" class="form-select form-select-sm rounded-2"></select>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label for="prItem" class="form-label small mb-1">Item</label>
                                        <input id="prItem" class="form-control form-control-sm rounded-2" list="prItems" maxlength="60" placeholder="e.g. 80">
                                        <datalist id="prItems"></datalist>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label for="prKind" class="form-label small mb-1">Kind</label>
                                        <input id="prKind" class="form-control form-control-sm rounded-2" list="prKinds" maxlength="60" placeholder="e.g. White">
                                        <datalist id="prKinds"></datalist>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label for="prRate" class="form-label small mb-1">Rate</label>
                                        <div class="input-group input-group-sm">
                                            <input id="prRate" type="number" min="0" step="0.01" class="form-control form-control-sm text-end font-monospace rounded-start-2" required placeholder="0.00">
                                            <button type="submit" class="btn btn-primary btn-sm px-2.5 rounded-end-2" title="Save"><i class="fa-solid fa-check"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="col-md-5">
                            <form class="pc-card pc-rates-adjust h-100 shadow-xs" id="pcRatesAdjust">
                                <header class="pc-card-head mb-2 pb-1 border-bottom">
                                    <h6 class="pc-h m-0 text-dark fw-bold small"><i class="fa-solid fa-percent text-warning me-1"></i> Adjust</h6>
                                </header>
                                <div class="row g-2 align-items-end">
                                    <div class="col-4">
                                        <label for="paGroup" class="form-label small mb-1">Group</label>
                                        <select id="paGroup" class="form-select form-select-sm rounded-2"></select>
                                    </div>
                                    <div class="col-3">
                                        <label for="paMode" class="form-label small mb-1">Mode</label>
                                        <select id="paMode" class="form-select form-select-sm rounded-2"><option value="percent">%</option><option value="amount">৳</option></select>
                                    </div>
                                    <div class="col-5">
                                        <label for="paValue" class="form-label small mb-1">Value</label>
                                        <div class="input-group input-group-sm">
                                            <input id="paValue" type="number" step="0.01" class="form-control form-control-sm text-end font-monospace rounded-start-2" required placeholder="+5">
                                            <button type="submit" class="btn btn-outline-primary btn-sm px-2.5 rounded-end-2" title="Apply"><i class="fa-solid fa-bolt"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <section class="pc-card pc-rates-table shadow-xs p-0 overflow-hidden">
                        <div class="table-responsive" style="max-height: 480px;">
                            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                                <thead class="table-light sticky-top" style="border-bottom: 2px solid #e2e8f0;">
                                    <tr>
                                        <th style="width: 140px;">GROUP</th>
                                        <th>ITEM</th>
                                        <th>KIND</th>
                                        <th class="text-end" style="width: 160px;">RATE</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="pcRatesBody"></tbody>
                            </table>
                        </div>
                        <div class="pc-empty p-4 text-center text-muted" id="pcRatesEmpty" hidden>Empty</div>
                    </section>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer pc-foot bg-white border-top py-2.5 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2" id="pcFoot">
                <div class="pc-foot-sum d-flex align-items-center gap-3">
                    <span class="pc-missing badge bg-warning text-dark border border-warning-subtle rounded-2 px-2.5 py-1" id="pcMissing" hidden></span>
                    <span class="small text-muted font-monospace">Rate: <b id="pcFootUnit" class="text-primary fs-6">৳0.00</b></span>
                    <span class="small text-muted font-monospace">Total: <b id="pcFootTotal" class="text-dark fs-6">৳0.00</b></span>
                </div>

                <div class="pc-foot-actions d-flex align-items-center gap-2">
                    {{-- Copy breakdown to clipboard --}}
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-2 px-3" id="pcCopy" title="Copy">
                        <i class="fa-regular fa-copy me-1"></i> Copy
                    </button>

                    {{-- Insert as Itemized Breakdown --}}
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-2 px-3 fw-semibold" id="pcAddDetailed" disabled title="Breakdown">
                        <i class="fa-solid fa-list-check me-1.5"></i> Breakdown
                    </button>

                    {{-- Insert as Single Consolidated Line with Full Spec --}}
                    <button type="button" class="btn btn-primary btn-sm rounded-2 px-3.5 fw-bold shadow-xs" id="pcAdd" disabled title="Insert">
                        <i class="fa-solid fa-file-circle-plus me-1.5"></i> Insert
                    </button>

                    {{-- Plain --}}
                    <button type="button" class="btn btn-light btn-sm rounded-2 px-2.5 text-muted border" id="pcPlain" disabled title="Plain">
                        Plain
                    </button>
                </div>
            </div>

            {{-- Floating Toast Notification --}}
            <div class="pc-toast shadow-lg" id="pcToast" role="status" hidden></div>
        </div>
    </div>
</div>
