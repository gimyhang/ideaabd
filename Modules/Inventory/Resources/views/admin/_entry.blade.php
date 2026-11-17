{{--
    One entry form for the whole lifecycle. Production stages post to the production ledger;
    store wastage posts to the inventory wastage log. JS (inventory-admin.js) switches mode.
    Needs: $locations. Optional: $presetBook (Book) to lock the picker to one title.
--}}
@php
    $wasteReasons = \Modules\Inventory\Http\Controllers\Admin\InventoryAdminController::WASTAGE_REASONS;
    $pastVendors = \App\Models\BookProductionEvent::whereNotNull('vendor')->where('vendor', '!=', '')
        ->distinct()->orderBy('vendor')->limit(60)->pluck('vendor');
@endphp
<datalist id="lifeVendors">@foreach ($pastVendors as $v)<option value="{{ $v }}">@endforeach</datalist>
<div class="modal fade" id="lifeModal" tabindex="-1" aria-labelledby="lifeTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" action="{{ route('admin.production.store') }}" class="modal-content lc-entry" id="lifeForm"
              data-production="{{ route('admin.production.store') }}" data-wastage="{{ route('admin.inventory.wastages.store') }}">
            @csrf
            <input type="hidden" name="stage" id="lifeStage" value="printed">
            <input type="hidden" name="action_taken" value="disposed" data-field="reason">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="lifeTitle">নতুন এন্ট্রি</h5>
                    <div class="lc-sub" id="lifeHelp">প্রেস থেকে ছাপা কপি এলো</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="lc-steps" role="tablist" aria-label="ধাপ">
                    <button type="button" class="lc-step is-active" data-life-mode="printed"><i class="fa-solid fa-print"></i><span>ছাপা</span></button>
                    <button type="button" class="lc-step" data-life-mode="bound"><i class="fa-solid fa-book"></i><span>বাঁধাই</span></button>
                    <button type="button" class="lc-step" data-life-mode="received"><i class="fa-solid fa-warehouse"></i><span>স্টোরে গ্রহণ</span></button>
                    <button type="button" class="lc-step is-waste" data-life-mode="press_waste" data-life-waste><i class="fa-solid fa-triangle-exclamation"></i><span>নষ্ট</span></button>
                </div>

                <div class="lc-wasteseg" id="lifeWasteSeg" hidden>
                    <span>কোথায় নষ্ট হলো?</span>
                    <button type="button" class="lc-chip is-active" data-life-mode="press_waste">প্রেসে (ছাপা অবস্থায়)</button>
                    <button type="button" class="lc-chip" data-life-mode="binder_waste">বাঁধাইখানায়</button>
                    <button type="button" class="lc-chip" data-life-mode="store_waste">স্টোরে / শোরুমে</button>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        @if (!empty($presetBook))
                            <label class="form-label">বই</label>
                            <div class="lc-locked"><i class="fa-solid fa-book-open"></i> {{ $presetBook->title }}</div>
                            <input type="hidden" name="book_id" value="{{ $presetBook->id }}">
                        @else
                            @include('inventory::admin._picker', ['name' => 'book_id', 'label' => 'বই', 'placeholder' => 'বইয়ের নাম / লেখক / ISBN'])
                        @endif
                        <div class="lc-hint" id="lifeHint" @if (empty($presetLife)) hidden @endif>
                            @if (!empty($presetLife))
                                <span>প্রেসে <b data-hint="press">{{ \App\Services\BookProductionService::bn($presetLife->at_press) }}</b></span>
                                <span>বাঁধাইখানায় <b data-hint="binder">{{ \App\Services\BookProductionService::bn($presetLife->at_binder) }}</b></span>
                                <span>স্টোরে <b data-hint="stock">{{ \App\Services\BookProductionService::bn($presetLife->in_store) }}</b></span>
                            @endif
                        </div>
                        @if (!empty($presetLife))
                            <span hidden id="lifePreset" data-press="{{ $presetLife->at_press }}" data-binder="{{ $presetLife->at_binder }}" data-stock="{{ $presetLife->in_store }}"></span>
                        @endif
                    </div>

                    <div class="col-6 col-md-4">
                        <label class="form-label" for="lifeQty">সংখ্যা (কপি)</label>
                        <input type="number" name="quantity" id="lifeQty" min="1" class="form-control lc-qty" required inputmode="numeric">
                        <div class="lc-max" id="lifeMax" hidden></div>
                    </div>
                    <div class="col-6 col-md-4" data-field="date">
                        <label class="form-label" for="lifeDate">তারিখ</label>
                        <input type="date" name="event_date" id="lifeDate" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-4" data-field="batch">
                        <label class="form-label" for="lifeBatch">চালান / মুদ্রণ নং</label>
                        <input name="batch_no" id="lifeBatch" maxlength="100" class="form-control" placeholder="যেমন: ২য় মুদ্রণ">
                    </div>
                    <div class="col-12 col-md-6" data-field="vendor">
                        <label class="form-label" for="lifeVendor" id="lifeVendorLabel">প্রেসের নাম</label>
                        <input name="vendor" id="lifeVendor" maxlength="255" class="form-control" list="lifeVendors">
                    </div>
                    <div class="col-12 col-md-6" data-field="wh">
                        <label class="form-label" for="lifeWh">কোন গুদামে ঢুকলো</label>
                        <select name="location_id" id="lifeWh" class="form-select">
                            @foreach ($locations->where('type', 'warehouse') as $loc)
                                <option value="{{ $loc->id }}" @selected($loc->is_central)>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6" data-field="loc">
                        <label class="form-label" for="lifeLoc">লোকেশন</label>
                        <select name="location_id" id="lifeLoc" class="form-select">
                            @foreach ($locations as $loc)
                                <option value="{{ $loc->id }}" @selected($loc->is_central)>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6" data-field="reason">
                        <label class="form-label" for="lifeReason">কারণ</label>
                        <select name="reason" id="lifeReason" class="form-select">
                            @foreach ($wasteReasons as $key => $label)
                                <option value="{{ $key }}" @selected($key === 'showroom_damage')>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="lifeNote">নোট</label>
                        <input name="notes" id="lifeNote" maxlength="1000" class="form-control" placeholder="ঐচ্ছিক">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">বাতিল</button>
                <button class="btn btn-primary" id="lifeSave">সংরক্ষণ</button>
            </div>
        </form>
    </div>
</div>
