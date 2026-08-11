{{--
    Kept as an alias so existing @include('components.header') calls keep working.

    The previous version here was a second, Tailwind-classed header. Pages that
    included it standalone loaded no Tailwind build, so its `hidden` utility never
    applied and the dropdown panels rendered permanently open. There is now one
    header for the whole site.
--}}
@include('layouts.header')
