{{-- Book picker. $name = hidden field name, $label = visible label, $locationSelector = optional CSS selector of a location select, $value/$text = preset book --}}
<div class="iv-pick" data-url="{{ route('admin.inventory.books') }}" @isset($locationSelector) data-location="{{ $locationSelector }}" @endisset>
    @isset($label)<label class="form-label">{{ $label }}</label>@endisset
    <input type="search" class="form-control iv-pick-input {{ !empty($value) ? 'is-set' : '' }}" autocomplete="off" aria-label="Book" value="{{ $text ?? '' }}" placeholder="{{ $placeholder ?? '' }}">
    <input type="hidden" name="{{ $name }}" class="iv-pick-id" value="{{ $value ?? '' }}">
    <div class="iv-pick-list" hidden></div>
</div>
