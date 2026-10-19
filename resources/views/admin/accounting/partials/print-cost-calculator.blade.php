{{-- Printing cost calculator. Shared by the accounting hub (quick quotation) and the invoice create / edit forms.
     Logic: public/js/print-cost.js. Hosts define window.addPresetItem(title, spec, itemType, unit, price, regularPrice, qty). --}}
<link rel="stylesheet" href="{{ asset('css/print-cost.css') }}?v={{ @filemtime(public_path('css/print-cost.css')) ?: 1 }}">
<script src="{{ asset('js/print-cost.js') }}?v={{ @filemtime(public_path('js/print-cost.js')) ?: 1 }}" defer></script>

<div class="modal fade" id="printCostCalculatorModal" tabindex="-1" aria-labelledby="pcTitle" aria-hidden="true"
     data-rates-url="{{ route('admin.accounting.print-rates.index') }}"
     data-rates-store="{{ route('admin.accounting.print-rates.store') }}"
     data-rates-adjust="{{ route('admin.accounting.print-rates.adjust') }}"
     data-rates-delete="{{ url('admin/accounting/print-rates') }}">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content pc">
            <div class="modal-header">
                <h5 class="modal-title" id="pcTitle">Cost</h5>
                <div class="pc-head-actions">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="pcRatesBtn">Rates</button>
                    <button type="button" class="btn btn-light btn-sm" id="pcReset">Reset</button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body">
                <div class="pc-layout" id="pcCalcView">
                    <div class="pc-main">
                        <section class="pc-card pc-job">
                            <header class="pc-card-head"><h6 class="pc-h">Job</h6></header>
                            <div class="pc-types" id="pcTypes" role="tablist" aria-label="Job"></div>

                            <div class="pc-grid">
                                <div class="pc-f pc-f-wide"><label for="pcTitleIn">Title</label><input id="pcTitleIn" class="form-control" maxlength="120"></div>
                                <div class="pc-f">
                                    <label for="pcQty">Quantity</label>
                                    <input id="pcQty" type="number" min="0" step="1" class="form-control text-end" inputmode="numeric">
                                    <div class="pc-quick">@foreach ([500, 1000, 2000, 5000] as $n)<button type="button" data-qty="{{ $n }}">{{ number_format($n) }}</button>@endforeach</div>
                                </div>
                                <div class="pc-f">
                                    <label for="pcUnit">Unit</label>
                                    <select id="pcUnit" class="form-select">@foreach (['Copy', 'Pcs', 'Set', 'Book', 'Sheet', 'Ream'] as $u)<option>{{ $u }}</option>@endforeach</select>
                                </div>
                                <div class="pc-f" id="pcPagesWrap">
                                    <label for="pcPages">Pages</label>
                                    <input id="pcPages" type="number" min="0" step="1" class="form-control text-end" inputmode="numeric">
                                    <small class="pc-note" id="pcFormasWrap" hidden>Formas <b id="pcFormas">0</b></small>
                                </div>
                                <div class="pc-f">
                                    <label for="pcColors">Colors</label>
                                    <select id="pcColors" class="form-select"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4" selected>4</option></select>
                                </div>
                                <div class="pc-f">
                                    <label for="pcSides">Sides</label>
                                    <select id="pcSides" class="form-select"><option value="1" selected>1</option><option value="2">2</option></select>
                                </div>
                                <div class="pc-f">
                                    <label for="pcSize">Size</label>
                                    <select id="pcSize" class="form-select"></select>
                                </div>
                                <div class="pc-f">
                                    <label for="pcSheet">Sheet</label>
                                    <select id="pcSheet" class="form-select">
                                        @foreach (['23x36' => '23×36', '25x36' => '25×36', '20x30' => '20×30', '18x23' => '18×23'] as $v => $label)<option value="{{ $v }}">{{ $label }}</option>@endforeach
                                    </select>
                                    <small class="pc-note" id="pcUpsWrap" hidden>Ups <b id="pcUps">0</b></small>
                                </div>
                            </div>
</section>

                        <div class="pc-addbar">
                            <span class="pc-addlabel">Add</span>
                            <div id="pcAddBar" class="pc-addbtns"></div>
                        </div>

                        <div id="pcGroups" class="pc-groups"></div>
                        <div id="pcLists" hidden></div>
                    </div>

                    <aside class="pc-side">
                        <section class="pc-card pc-summary">
                            <header class="pc-card-head"><h6 class="pc-h">Total</h6></header>
                            <div class="pc-bars" id="pcBars"></div>

                            <dl class="pc-sum">
                                <div><dt>Direct</dt><dd id="pcDirect">৳0</dd></div>
                                <div class="pc-sum-input">
                                    <dt><label for="pcOverhead">Overhead %</label></dt>
                                    <dd><input id="pcOverhead" type="number" min="0" max="100" step="0.1" class="form-control form-control-sm text-end"><b id="pcOverheadAmt">৳0</b></dd>
                                </div>
                                <div><dt>Cost</dt><dd id="pcCost">৳0</dd></div>
                                <div class="pc-sum-input">
                                    <dt><label for="pcProfit">Profit %</label></dt>
                                    <dd><input id="pcProfit" type="number" min="0" max="500" step="0.1" class="form-control form-control-sm text-end"><b id="pcProfitAmt">৳0</b></dd>
                                </div>
                                <div class="pc-quick pc-quick-profit">@foreach ([10, 15, 20, 25, 30] as $p)<button type="button" data-profit="{{ $p }}">{{ $p }}%</button>@endforeach</div>
                                <div class="pc-sum-input">
                                    <dt><label for="pcRound">Round</label></dt>
                                    <dd><select id="pcRound" class="form-select form-select-sm"><option value="0">—</option><option value="0.5">0.5</option><option value="1">1</option><option value="5">5</option><option value="10">10</option></select></dd>
                                </div>
                                <div class="pc-grand"><dt>Total</dt><dd id="pcTotal">৳0</dd></div>
                                <div class="pc-unitbox"><dt>Rate <small id="pcRoundedNote" hidden>↑</small></dt><dd id="pcUnit2">৳0</dd></div>
                            </dl>
                        </section>
                        <section class="pc-card pc-tiers" id="pcTiersWrap" hidden>
                            <header class="pc-card-head"><h6 class="pc-h">Tiers</h6></header>
                            <div class="pc-tier-list" id="pcTiers"></div>
                        </section>
                    </aside>
                </div>
                {{-- Rate sheet: raw-material prices used by every line above --}}
                <div class="pc-rates" id="pcRatesPanel" hidden>
                    <div class="pc-rates-top">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="pcRatesBack">Back</button>
                        <h6 class="pc-h m-0">Rates</h6>
                        <input id="pcRatesSearch" type="search" class="form-control form-control-sm pc-rates-search" aria-label="Search">
                    </div>
                    <select class="form-select form-select-sm pc-rates-groups" id="pcRatesGroups" aria-label="Group"></select>

                    <div class="pc-rates-tools">
                        <form class="pc-card pc-rates-add" id="pcRatesAdd" autocomplete="off">
                            <header class="pc-card-head"><h6 class="pc-h">Add</h6></header>
                            <div class="pc-rates-fields">
                                <div class="pc-f"><label for="prGroup">Group</label><select id="prGroup" class="form-select form-select-sm"></select></div>
                                <div class="pc-f"><label for="prItem">Item</label><input id="prItem" class="form-control form-control-sm" list="prItems" maxlength="60"><datalist id="prItems"></datalist></div>
                                <div class="pc-f"><label for="prKind">Kind</label><input id="prKind" class="form-control form-control-sm" list="prKinds" maxlength="60"><datalist id="prKinds"></datalist></div>
                                <div class="pc-f"><label for="prRate">Rate</label><input id="prRate" type="number" min="0" step="0.01" class="form-control form-control-sm text-end" required></div>
                                <div class="pc-f pc-f-btn"><button class="btn btn-primary btn-sm">Add</button></div>
                            </div>
                        </form>
                        <form class="pc-card pc-rates-adjust" id="pcRatesAdjust">
                            <header class="pc-card-head"><h6 class="pc-h">Adjust</h6></header>
                            <div class="pc-rates-fields">
                                <div class="pc-f"><label for="paGroup">Group</label><select id="paGroup" class="form-select form-select-sm"></select></div>
                                <div class="pc-f"><label for="paMode">Mode</label><select id="paMode" class="form-select form-select-sm"><option value="percent">%</option><option value="amount">৳</option></select></div>
                                <div class="pc-f"><label for="paValue">Value</label><input id="paValue" type="number" step="0.01" class="form-control form-control-sm text-end" required></div>
                                <div class="pc-f pc-f-btn"><button class="btn btn-outline-primary btn-sm">Apply</button></div>
                            </div>
                        </form>
                    </div>

                    <section class="pc-card pc-rates-table">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead><tr><th>Group</th><th>Item</th><th>Kind</th><th class="text-end">Rate</th><th></th></tr></thead>
                                <tbody id="pcRatesBody"></tbody>
                            </table>
                        </div>
                        <div class="pc-empty" id="pcRatesEmpty" hidden>—</div>
                    </section>
                </div>
            </div>

            <div class="modal-footer pc-foot" id="pcFoot">
                <div class="pc-foot-sum">
                    <span class="pc-missing" id="pcMissing" hidden></span>
                    <span>Rate <b id="pcFootUnit">৳0</b></span>
                    <span>Total <b id="pcFootTotal">৳0</b></span>
                </div>
                <div class="pc-foot-actions">
                    <button type="button" class="btn btn-outline-secondary" id="pcCopy">Copy</button>
                    <button type="button" class="btn btn-outline-primary" id="pcPlain" disabled title="Detail without rates">Plain</button>
                    <button type="button" class="btn btn-primary" id="pcAdd" disabled title="Detail with rates">Rated</button>
                </div>
            </div>
            <div class="pc-toast" id="pcToast" role="status" hidden></div>
        </div>
    </div>
</div>
