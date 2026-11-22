{{-- Money receipt form: honorarium paid / bill money received. Needs css/admin-receipts.css + js/admin-receipts.js --}}
@php
    $mrMethods = \App\Models\MoneyReceipt::METHODS;
    $mrIncome = \App\Models\IdeaAccountingEntry::categories()['income'] ?? [];
@endphp
<div class="modal fade" id="mrModal" tabindex="-1" aria-labelledby="mrTitle" aria-hidden="true"
     data-store="{{ route('admin.receipts.store') }}" data-lookup="{{ route('admin.receipts.lookup') }}" data-list="{{ route('admin.receipts.index') }}">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <form class="modal-content mr-modal" id="mrForm" novalidate autocomplete="off">
            <div class="mr-head">
                <span class="mr-head-icon" id="mrIcon"><i class="fa-solid fa-hand-holding-dollar"></i></span>
                <div class="min-w-0">
                    <h5 id="mrTitle">Honorarium</h5>
                    <p id="mrSub">লেখককে সম্মানী প্রদানের রসিদ</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="mr-tabs" role="tablist">
                <button type="button" class="is-on" data-mr-kind="honorarium" role="tab"><i class="fa-solid fa-feather-pointed"></i><span><b>Honorarium</b><small>সম্মানী প্রদান</small></span></button>
                <button type="button" data-mr-kind="bill" role="tab"><i class="fa-solid fa-receipt"></i><span><b>Receipt</b><small>বিলের অর্থ গ্রহণ</small></span></button>
            </div>

            <div class="modal-body mr-body" id="mrBody">
                <input type="hidden" name="kind" value="honorarium">
                <input type="hidden" name="author_id">
                <input type="hidden" name="invoice_id">

                {{-- who --}}
                <div class="mr-field mr-lookup" data-for="bill">
                    <label for="mrBillSearch">Bill <em>বকেয়া বিল খুঁজুন (নম্বর, নাম, ফোন) — না থাকলে খালি রাখুন</em></label>
                    <div class="mr-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="mrBillSearch" data-lookup-type="invoice" placeholder="IDEA-INV-… / গ্রাহকের নাম">
                        <button type="button" class="mr-x" data-clear-link hidden title="Remove bill"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <div class="mr-suggest" hidden></div>
                    <div class="mr-linked" id="mrLinkedBill" hidden></div>
                </div>

                <div class="mr-grid">
                    <div class="mr-field mr-lookup mr-span2">
                        <label for="mrName"><span data-for="honorarium">Writer <em>লেখকের নাম (তালিকা থেকে বাছুন বা লিখুন)</em></span><span data-for="bill" hidden>From <em>যাঁর কাছ থেকে টাকা পেলেন</em></span></label>
                        <div class="mr-search">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" id="mrName" name="party_name" maxlength="191" required data-lookup-type="author" placeholder="নাম">
                        </div>
                        <div class="mr-suggest" hidden></div>
                    </div>
                    <div class="mr-field">
                        <label for="mrPhone">Phone</label>
                        <input type="tel" id="mrPhone" name="party_phone" maxlength="40" placeholder="01XXXXXXXXX" inputmode="tel">
                    </div>
                    <div class="mr-field">
                        <label for="mrDate">Date</label>
                        <input type="date" id="mrDate" name="receipt_date" value="{{ now()->toDateString() }}" max="{{ now()->addDay()->toDateString() }}" required>
                    </div>
                    <div class="mr-field mr-span2" data-for="bill" hidden>
                        <label for="mrAddress">Address</label>
                        <input type="text" id="mrAddress" name="party_address" maxlength="255" placeholder="ঠিকানা (ঐচ্ছিক)">
                    </div>
                    <div class="mr-field mr-span2">
                        <label for="mrPurpose">Purpose <em data-for="honorarium">কীসের সম্মানী — যেমন: “নদী ও মানুষ” গ্রন্থের সম্মানী</em><em data-for="bill" hidden>কী বাবদ — যেমন: বই সরবরাহের বিল বাবদ</em></label>
                        <input type="text" id="mrPurpose" name="purpose" maxlength="255" required>
                    </div>
                </div>

                {{-- book sales (honorarium) --}}
                <div class="mr-books" data-for="honorarium">
                    <div class="mr-books-head">
                        <label>Books <em>বই বিক্রির হিসাব — দিলে লেখকের প্রাপ্য নিজে থেকে হিসাব হবে</em></label>
                        <button type="button" class="mr-chip is-pay" id="mrAddBook"><i class="fa-solid fa-plus"></i> Book</button>
                    </div>
                    <div class="mr-bk" id="mrBooks" hidden>
                        <div class="mr-bk-head"><span>বই</span><span>কপি</span><span>মূল্য ৳</span><span>কমিশন %</span><span class="r">প্রাপ্য</span><span></span></div>
                        <div id="mrBookRows"></div>
                        <div class="mr-bk-total">
                            <span><b id="mrBkQty">০</b> কপি</span>
                            <span>বিক্রি <b id="mrBkSale">৳০</b></span>
                            <span>কমিশন <b id="mrBkComm">৳০</b></span>
                            <span>লেখকের প্রাপ্য <b id="mrBkNet" class="is-net">৳০</b></span>
                        </div>
                    </div>
                </div>

                {{-- money --}}
                <div class="mr-amount">
                    <label for="mrAmount">Amount</label>
                    <div class="mr-amount-row">
                        <span>৳</span>
                        <input type="number" id="mrAmount" name="amount" min="1" step="0.01" inputmode="decimal" required placeholder="0">
                        <button type="button" class="mr-chip" id="mrFullDue" data-for="bill" hidden>Full due</button>
                    </div>
                    <small id="mrWords" class="mr-words"></small>
                </div>

                <div class="mr-field">
                    <label>Method</label>
                    <div class="mr-methods">
                        @foreach($mrMethods as $key => $label)
                            <label class="mr-method"><input type="radio" name="payment_method" value="{{ $key }}" @checked($key === 'cash')><span>{{ $label }}</span></label>
                        @endforeach
                    </div>
                </div>

                <div class="mr-grid">
                    <div class="mr-field">
                        <label for="mrRef">Reference <em>TrxID / চেক নম্বর</em></label>
                        <input type="text" id="mrRef" name="reference" maxlength="120">
                    </div>
                    <div class="mr-field" data-for="bill-free" hidden>
                        <label for="mrCat">Category <em>হিসাবের খাত</em></label>
                        <select id="mrCat" name="category">
                            @foreach($mrIncome as $c)<option value="{{ $c }}" @selected($c === 'বিবিধ আয়')>{{ $c }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mr-field mr-span2">
                        <label for="mrNotes">Notes</label>
                        <textarea id="mrNotes" name="notes" rows="2" maxlength="1000" placeholder="ঐচ্ছিক"></textarea>
                    </div>
                </div>

                <div class="mr-error" id="mrError" hidden></div>
            </div>

            {{-- done --}}
            <div class="modal-body mr-done" id="mrDone" hidden>
                <div class="mr-done-icon"><i class="fa-solid fa-circle-check"></i></div>
                <h5 id="mrDoneTitle">রসিদ তৈরি হয়েছে</h5>
                <p id="mrDoneText"></p>
                <div class="mr-done-acts">
                    <a class="mr-btn is-primary" id="mrPrint" href="#" target="_blank" rel="noopener"><i class="fa-solid fa-print"></i><span>Print</span></a>
                    <a class="mr-btn" id="mrPrint2" href="#" target="_blank" rel="noopener" title="অফিস কপি + গ্রাহক কপি"><i class="fa-solid fa-copy"></i><span>Print ×2</span></a>
                    <button type="button" class="mr-btn" id="mrAgain"><i class="fa-solid fa-plus"></i><span>New</span></button>
                </div>
                <a class="mr-link" href="{{ route('admin.receipts.index') }}">সব রসিদ দেখুন →</a>
            </div>

            <div class="mr-foot" id="mrFoot">
                <span class="mr-foot-note"><i class="fa-solid fa-book"></i> হিসাবখাতায় নিজে থেকে উঠবে</span>
                <button type="button" class="mr-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="mr-btn is-primary" id="mrSave"><i class="fa-solid fa-check"></i><span>Save</span></button>
            </div>
        </form>
    </div>
</div>
