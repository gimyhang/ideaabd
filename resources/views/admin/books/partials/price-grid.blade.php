{{--
    One price table for the full book form and Quick Add (same columns, same behaviour: public/js/book-price-grid.js).
      @include('admin.books.partials.price-grid', ['grid' => [...]])
    $grid = [
      'binding'  => current binding (paperback | hardcover | both | …),
      'select'   => CSS selector of the binding <select> the table follows,
      'rows'     => ['paperback' => [field => [id, name|null, value]], 'hardcover' => [...]]   fields: mrp pct sale buy cost
      'required' => true when the MRP of every shown row is required,
    ]
    A row the binding does not use is hidden and its inputs disabled, so it is never posted.
--}}
@php
    $bpgRows = $grid['rows'];
    $bpgShow = fn ($row) => match ($grid['binding'] ?? 'paperback') {
        'hardcover' => $row === 'hardcover',
        'both'      => true,
        default     => $row === 'paperback',
    };
    $bpgInput = function ($row, $field, $attrs = '') use ($bpgRows) {
        [$id, $name, $value] = $bpgRows[$row][$field] + [null, null, null];
        return '<input type="number" min="0" step="any" inputmode="decimal" class="form-control form-control-sm bpg-in" data-bpg="' . $field . '"'
            . ($id ? ' id="' . e($id) . '"' : '') . ($name ? ' name="' . e($name) . '"' : '')
            . ' value="' . e($value) . '" ' . $attrs . '>';
    };
@endphp
<div class="bf-table-wrap">
    <table class="bf-table bpg" data-bpg-grid data-binding-select="{{ $grid['select'] }}" @if(!empty($grid['required'])) data-required="1" @endif>
        <thead>
            <tr>
                <th class="bpg-col-bind">Binding</th>
                <th>MRP <span class="text-danger">*</span></th>
                <th>Discount %</th>
                <th>Price</th>
                <th>Buy %</th>
                <th>Cost</th>
                <th class="text-end">Margin</th>
            </tr>
        </thead>
        <tbody>
            @foreach (['paperback' => 'Paperback', 'hardcover' => 'Hardcover'] as $row => $rowLabel)
                @continue(!isset($bpgRows[$row]))
                <tr data-bpg-row="{{ $row }}" @unless($bpgShow($row)) hidden @endunless>
                    <th scope="row" class="bpg-col-bind">{{ $rowLabel }}</th>
                    <td data-label="MRP"><div class="bpg-money">{!! $bpgInput($row, 'mrp', 'placeholder="0"') !!}</div></td>
                    <td data-label="Discount %"><div class="bpg-pct">{!! $bpgInput($row, 'pct', 'max="100" placeholder="0"') !!}</div></td>
                    <td data-label="Price"><div class="bpg-money">{!! $bpgInput($row, 'sale', 'placeholder="= MRP"') !!}</div></td>
                    <td data-label="Buy %"><div class="bpg-pct">{!! $bpgInput($row, 'buy', 'max="100" placeholder="0"') !!}</div></td>
                    <td data-label="Cost"><div class="bpg-money">{!! $bpgInput($row, 'cost', 'placeholder="0"') !!}</div></td>
                    <td data-label="Margin" class="text-end"><span class="bpg-margin" data-bpg-out="margin">—</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="bpg-note" data-bpg-note role="status" aria-live="polite"></div>
