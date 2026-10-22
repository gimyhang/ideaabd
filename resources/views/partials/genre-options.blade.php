{{--
    <option> list for a genre <select>, built from the genres table.
    @include('partials.genre-options', ['selected' => $value, 'lang' => 'en'|'bn'|'both'])
    A value that is no longer active (or unknown) is still listed so editing an old book keeps it.
--}}
@php
    $selected = (string) ($selected ?? '');
    $lang = $lang ?? 'en';
    $genres = \Modules\Book\Models\Genre::active();
    $known = $genres->pluck('slug')->all();
@endphp
@foreach ($genres as $g)
    <option value="{{ $g['slug'] }}" @selected($selected === $g['slug'])>{{ \Modules\Book\Models\Genre::label($g, $lang) }}</option>
@endforeach
@if ($selected !== '' && ! in_array($selected, $known, true))
    <option value="{{ $selected }}" selected>{{ \Modules\Book\Models\Genre::nameFor($selected, $lang) }} (inactive)</option>
@endif
