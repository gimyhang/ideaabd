{{-- Quick quotation: live calculator, old-customer lookup, item suggestions by category and the printing cost calculator.
     Posts to the normal invoice store route (type = quotation). --}}
<link rel="stylesheet" href="{{ asset('css/quick-quotation.css') }}?v={{ @filemtime(public_path('css/quick-quotation.css')) ?: 1 }}">
<script src="{{ asset('js/quick-quotation.js') }}?v={{ @filemtime(public_path('js/quick-quotation.js')) ?: 1 }}" defer></script>

<div class="modal fade" id="quickQuotationModal" tabindex="-1" aria-labelledby="quickQuotationTitle" aria-hidden="true"
     data-customers="{{ route('admin.accounting.invoices.customer-search') }}"
     data-items="{{ route('admin.accounting.invoices.item-suggest') }}">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form method="POST" action="{{ route('admin.accounting.invoices.store') }}" class="modal-content" id="quickQuotationForm" autocomplete="off">
            @csrf
            <input type="hidden" name="type" value="quotation">
            <input type="hidden" name="payment_method" value="Cash">
            <input type="hidden" name="tax" id="qqTax" value="0">
            <input type="hidden" name="customer_org" id="qqOrg">
            <input type="hidden" name="customer_email" id="qqEmail">
            <input type="hidden" name="customer_address" id="qqAddress">
            <input type="hidden" name="customer_designation" id="qqDesignation">

            <div class="modal-header">
                <h5 class="modal-title" id="quickQuotationTitle">Quotation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row qq-grid">
                    <div class="col-md-4 col-6">
                        <label class="form-label" for="qqNo">Number</label>
                        <input id="qqNo" name="invoice_no" class="form-control" value="{{ \App\Models\IdeaInvoice::nextNumber('quotation', 'books') }}" required maxlength="50">
                    </div>
                    <div class="col-md-4 col-6">
                        <label class="form-label" for="qqDate">Date</label>
                        <input id="qqDate" type="date" name="invoice_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4 col-12">
                        <label class="form-label" for="qqValid">Valid until</label>
                        <input id="qqValid" type="date" name="valid_until" class="form-control" value="{{ date('Y-m-d', strtotime('+14 days')) }}" required>
                    </div>

                    <div class="col-md-6 col-12 qq-host">
                        <label class="form-label" for="qqCustomer">Customer</label>
                        <input id="qqCustomer" name="customer_name" class="form-control" required maxlength="255" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="qqCustomerList">
                        <div class="qq-suggest" id="qqCustomerList" role="listbox" hidden></div>
                        <div class="qq-due" id="qqDue" hidden></div>
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label" for="qqPhone">Phone</label>
                        <input id="qqPhone" type="tel" name="customer_phone" class="form-control" maxlength="50" inputmode="tel">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label" for="qqCategory">Category</label>
                        <select id="qqCategory" name="sales_category" class="form-select">
                            <option value="books">Books</option>
                            <option value="stationery">Stationery</option>
                            <option value="printing_goods">Printing</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="qqSubject">Subject</label>
                        <input id="qqSubject" name="subject" class="form-control" required maxlength="255">
                    </div>
                </div>

                <div class="qq-items-head">
                    <h6 class="qq-h">Items</h6>
                    <div class="qq-actions">
                        <button type="button" class="btn btn-outline-warning btn-sm" id="qqCalc">Cost</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="qqAdd">Add</button>
                    </div>
                </div>
                <div class="qq-items" id="qqItems"></div>

                <div class="row qq-grid qq-foot">
                    <div class="col-md-7">
                        <div class="row">
                            <div class="col-6">
                                <label class="form-label" for="qqDiscount">Discount</label>
                                <input id="qqDiscount" type="number" name="discount" min="0" step="0.01" value="0" class="form-control text-end">
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="qqTaxPct">Tax %</label>
                                <input id="qqTaxPct" type="number" min="0" max="100" step="0.01" value="0" class="form-control text-end">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="qqNotes">Note</label>
                                <input id="qqNotes" name="notes" class="form-control" maxlength="1000">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <dl class="qq-totals" aria-live="polite">
                            <div><dt>Subtotal</dt><dd id="qqSub">৳0.00</dd></div>
                            <div><dt>Discount</dt><dd id="qqDisc">−৳0.00</dd></div>
                            <div><dt>Tax</dt><dd id="qqTaxAmt">৳0.00</dd></div>
                            <div class="is-grand"><dt>Total</dt><dd id="qqGrand">৳0.00</dd></div>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <a href="{{ route('admin.accounting.invoices.create', ['type' => 'quotation']) }}" class="btn btn-outline-secondary me-auto">Full form</a>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<template id="qqRowTpl">
    <div class="qq-row">
        <div class="qq-title qq-host">
            <label class="form-label">Item</label>
            <input class="form-control qq-in-title" maxlength="500" required role="combobox" aria-autocomplete="list" aria-expanded="false">
            <div class="qq-suggest" role="listbox" hidden></div>
            <input type="hidden" class="qq-full" name="items[__i__][title]">
            <input type="hidden" class="qq-unit" name="items[__i__][unit]">
            <input type="hidden" class="qq-type" name="items[__i__][item_type]">
            <input type="hidden" class="qq-reg" name="items[__i__][regular_price]">
            <input type="hidden" class="qq-book" name="items[__i__][book_id]">
        </div>
        <div><label class="form-label">Qty</label><input type="number" name="items[__i__][quantity]" min="0.01" step="any" value="1" class="form-control qq-in-qty text-end" required></div>
        <div><label class="form-label">Price</label><input type="number" name="items[__i__][price]" min="0" step="0.01" class="form-control qq-in-price text-end" required></div>
        <div class="qq-line"><label class="form-label">Line</label><output class="qq-sum">৳0.00</output></div>
        <button type="button" class="btn btn-outline-danger btn-sm qq-del" aria-label="Remove">Remove</button>
        <div class="qq-detail"><label class="form-label">Detail</label><textarea class="form-control qq-in-detail" rows="2" maxlength="1500"></textarea></div>
    </div>
</template>

{{-- Printing cost calculator (shared with the invoice create / edit forms) --}}
@include('admin.accounting.partials.print-cost-calculator')
