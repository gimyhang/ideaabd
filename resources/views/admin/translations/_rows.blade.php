@php
    $langNames = ['bn' => ['BN', 'Bangla'], 'en' => ['EN', 'English'], 'ar' => ['AR', 'Arabic']];
@endphp
<div class="tr-list" data-count="{{ $paginator->total() }}">
    @forelse($paginator as $row)
        <article class="tr-row {{ $row['done'] ? '' : 'is-missing' }}" data-id="{{ $row['id'] }}" data-full="{{ $row['group'] }}.{{ $row['key'] }}">
            <header class="tr-row-head">
                <span class="tr-group">{{ $row['group'] }}</span>
                <code class="tr-key" title="{{ $row['group'] }}.{{ $row['key'] }}">{{ $row['key'] }}</code>
                <button type="button" class="tr-icon-btn" data-copy="__('{{ $row['group'] }}.{{ $row['key'] }}')" title="Copy code"><i class="fa-regular fa-copy"></i></button>
                <span class="tr-badges">
                    @if($row['custom'])<span class="tr-badge is-custom">Custom</span>@endif
                    @if(!$row['done'])<span class="tr-badge is-missing">Missing</span>@endif
                    @if($row['warn'])<span class="tr-badge is-warn" title="English text lacks a :placeholder used in Bangla">Check :vars</span>@endif
                    @foreach($row['vars'] as $var)<span class="tr-var">:{{ $var }}</span>@endforeach
                </span>
                @if($canEdit)
                    <span class="tr-row-acts">
                        @if($row['in_file'])
                            @if($row['custom'])
                                <button type="button" class="tr-icon-btn" data-reset title="Reset to the file text"><i class="fa-solid fa-rotate-left"></i></button>
                            @endif
                        @else
                            <button type="button" class="tr-icon-btn is-danger" data-delete title="Delete key"><i class="fa-regular fa-trash-can"></i></button>
                        @endif
                    </span>
                @endif
            </header>
            <div class="tr-cells">
                @foreach($langNames as $loc => [$code, $name])
                    @php $l = $row['langs'][$loc]; @endphp
                    <label class="tr-cell {{ $l['value'] === '' && $loc !== 'ar' ? 'is-empty' : '' }} {{ $l['saved'] !== '' ? 'is-saved' : '' }}" data-lang="{{ $loc }}">
                        <span class="tr-cell-top">
                            <span class="tr-lang">{{ $code }}</span>
                            <span class="tr-src">{{ $l['saved'] !== '' ? 'Custom' : ($l['default'] !== '' ? 'Default' : ($loc === 'ar' ? 'Optional' : 'Empty')) }}</span>
                            @if($loc === 'en' && $canEdit)
                                <button type="button" class="tr-suggest" data-suggest title="Suggest English"><i class="fa-solid fa-wand-magic-sparkles"></i></button>
                            @endif
                            <span class="tr-state" aria-live="polite"></span>
                        </span>
                        <textarea rows="1" data-lang="{{ $loc }}" aria-label="{{ $name }} text for {{ $row['group'] }}.{{ $row['key'] }}"
                                  placeholder="{{ $l['default'] !== '' ? $l['default'] : ($loc === 'ar' ? '—' : 'Type ' . $name . '…') }}"
                                  @if($loc === 'ar') dir="rtl" @endif
                                  @unless($canEdit) readonly @endunless>{{ $l['saved'] }}</textarea>
                    </label>
                @endforeach
            </div>
        </article>
    @empty
        <div class="tr-empty">
            <i class="fa-solid fa-language"></i>
            <strong>No keys found</strong>
            <span>Change the filter{{ $canEdit ? ', or press Sync to list the language files' : '' }}.</span>
        </div>
    @endforelse
</div>
@if($paginator->hasPages())
    <nav class="tr-pager" aria-label="Pages">
        <span class="tr-pager-info">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
        {{ $paginator->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
    </nav>
@endif
