{{-- Book picker. $name = hidden field name, $label = visible label, $locationSelector = optional CSS selector of a location select --}}
<div class="iv-pick" data-url="{{ route('admin.inventory.books') }}" @isset($locationSelector) data-location="{{ $locationSelector }}" @endisset>
    @isset($label)<label class="form-label">{{ $label }}</label>@endisset
    <input type="search" class="form-control iv-pick-input" autocomplete="off" aria-label="Book">
    <input type="hidden" name="{{ $name }}" class="iv-pick-id" value="{{ $value ?? '' }}">
    <div class="iv-pick-list" hidden></div>
</div>
