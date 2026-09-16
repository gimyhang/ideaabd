<?php

$files = [
    'resources/views/admin/dashboard.blade.php',
    'resources/views/admin/partials/sidebar.blade.php',
    'resources/views/admin/partials/topbar.blade.php',
    'resources/views/layouts/admin.blade.php'
];

$allIcons = [];

foreach ($files as $f) {
    $path = __DIR__ . '/../' . $f;
    if (!file_exists($path)) continue;
    $content = file_get_contents($path);
    preg_match_all('/class=[\'"]([^\'"]*fa[srbld]?[^\'"]*)[\'"]/', $content, $matches);
    foreach ($matches[1] as $cls) {
        $allIcons[$f][] = $cls;
    }
}

foreach ($allIcons as $f => $classes) {
    echo "=== File: {$f} (" . count($classes) . " icon tags) ===" . PHP_EOL;
    $unique = array_unique($classes);
    foreach (array_slice($unique, 0, 20) as $u) {
        echo "  - " . $u . PHP_EOL;
    }
    if (count($unique) > 20) {
        echo "  ... and " . (count($unique) - 20) . " more." . PHP_EOL;
    }
}
