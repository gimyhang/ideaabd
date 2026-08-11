<?php

/*
|--------------------------------------------------------------------------
| Brand / identity
|--------------------------------------------------------------------------
|
| Everything visual that identifies the publisher. Change it here or via .env
| without touching any Blade file.
|
| To swap the logo, either drop a new file at public/images/logo.svg (same path,
| no config change needed) or point BRAND_LOGO at another file inside /public:
|
|     BRAND_LOGO=images/my-logo.png
|
| Set BRAND_LOGO to an empty value to fall back to the lettermark.
|
*/

return [

    // Publisher name shown next to the logo.
    'name' => env('BRAND_NAME', 'আইডিয়া প্রকাশন'),

    // Short line under the name in the admin sidebar.
    'tagline' => env('BRAND_TAGLINE', 'অ্যাডমিন প্যানেল'),

    // Path inside /public. Leave empty to use the lettermark instead.
    'logo' => env('BRAND_LOGO', 'images/logo.svg'),

    // Optional separate mark for the collapsed sidebar; falls back to `logo`.
    'logo_mark' => env('BRAND_LOGO_MARK', 'images/logo-mark.svg'),

    'favicon' => env('BRAND_FAVICON', 'images/logo-mark.svg'),

    // Single letter used when no logo file is present.
    'lettermark' => env('BRAND_LETTERMARK', 'আ'),

    // Theme colours — also mirrored in public/css/admin.css custom properties.
    'colors' => [
        'primary' => env('BRAND_COLOR_PRIMARY', '#0066cc'),
        'accent'  => env('BRAND_COLOR_ACCENT', '#0099ff'),
    ],

];
