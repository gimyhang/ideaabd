<?php

/*
|--------------------------------------------------------------------------
| Admin cache console — approved Artisan commands
|--------------------------------------------------------------------------
| The console on /admin/cache/manage only runs commands listed here, and only
| those whose risk is at or below "max_risk". The page, the quick actions and the
| server-side check are all generated from this one list.
|
|   none  — read-only, changes nothing (and safe to run inside a web request:
|           route:list is NOT — it reloads the router and corrupts the response)
|   low   — clears a cache that rebuilds itself on the next request
|   medium— builds caches that FREEZE config / routes / events (breaks env() reads, stale routes,
|           and makes the test suite hit the real database)  -> deliberately not listed
|   high  — anything that changes data or code -> never listed
|
| Add a command by adding an entry; raise or lower the ceiling with CACHE_CONSOLE_MAX_RISK.
*/

return [
    'max_risk' => env('CACHE_CONSOLE_MAX_RISK', 'low'),

    'risk_order' => ['none', 'low', 'medium', 'high'],

    // seconds a command may be repeated by the same admin (guards against double clicks)
    'cooldown' => 2,

    'groups' => [
        'inspect' => ['label' => 'Inspect', 'icon' => 'fa-magnifying-glass'],
        'clear'   => ['label' => 'Clear',   'icon' => 'fa-broom'],
    ],

    'commands' => [
        'about' => [
            'label' => 'About', 'desc' => 'Show application information', 'icon' => 'fa-circle-info', 'tone' => 'info',
            'group' => 'inspect', 'risk' => 'none', 'args' => ['--no-ansi' => true],
        ],
        'migrate:status' => [
            'label' => 'Migrations', 'desc' => 'List which migrations have run', 'icon' => 'fa-database', 'tone' => 'info',
            'group' => 'inspect', 'risk' => 'none', 'args' => ['--no-ansi' => true],
        ],
        'schedule:list' => [
            'label' => 'Schedule', 'desc' => 'List scheduled tasks', 'icon' => 'fa-clock', 'tone' => 'info',
            'group' => 'inspect', 'risk' => 'none', 'args' => ['--no-ansi' => true],
        ],

        'view:clear' => [
            'label' => 'Views', 'desc' => 'Clear compiled Blade views', 'icon' => 'fa-desktop', 'tone' => 'info',
            'group' => 'clear', 'risk' => 'low', 'args' => ['--no-ansi' => true],
        ],
        'route:clear' => [
            'label' => 'Routes', 'desc' => 'Clear route cache', 'icon' => 'fa-signs-post', 'tone' => 'warning',
            'group' => 'clear', 'risk' => 'low', 'args' => ['--no-ansi' => true],
        ],
        'config:clear' => [
            'label' => 'Config', 'desc' => 'Clear configuration cache', 'icon' => 'fa-sliders', 'tone' => 'success',
            'group' => 'clear', 'risk' => 'low', 'args' => ['--no-ansi' => true],
        ],
        'event:clear' => [
            'label' => 'Events', 'desc' => 'Clear event listener cache', 'icon' => 'fa-bolt', 'tone' => 'warning',
            'group' => 'clear', 'risk' => 'low', 'args' => ['--no-ansi' => true],
        ],
        'cache:clear' => [
            'label' => 'Data', 'desc' => 'Clear application cache', 'icon' => 'fa-database', 'tone' => 'danger',
            'group' => 'clear', 'risk' => 'low', 'args' => ['--no-ansi' => true],
        ],
        'optimize:clear' => [
            'label' => 'Everything', 'desc' => 'Clear every framework cache', 'icon' => 'fa-broom', 'tone' => 'warning',
            'group' => 'clear', 'risk' => 'low', 'args' => ['--no-ansi' => true],
        ],
    ],
];
