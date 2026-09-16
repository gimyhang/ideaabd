<?php

$file = __DIR__ . '/../resources/views/admin/dashboard.blade.php';
$content = file_get_contents($file);

// 1. Replace legacy icon aliases with modern standard Font Awesome 6 Solid equivalents
$replacements = [
    'fas fa-' => 'fa-solid fa-',
    'fa-home-alt' => 'fa-house',
    'fa-search' => 'fa-magnifying-glass',
    'fa-edit' => 'fa-pen-to-square',
    'fa-trash-alt' => 'fa-trash-can',
    'fa-times-circle' => 'fa-circle-xmark',
    'fa-check-circle' => 'fa-circle-check',
    'fa-info-circle' => 'fa-circle-info',
    'fa-exclamation-triangle' => 'fa-triangle-exclamation',
    'fa-exclamation-circle' => 'fa-circle-exclamation',
    'fa-file-alt' => 'fa-file-lines',
    'fa-plus-circle' => 'fa-circle-plus',
    'fa-minus-circle' => 'fa-circle-minus',
    'fa-external-link-alt' => 'fa-arrow-up-right-from-square',
    'fa-external-link' => 'fa-arrow-up-right-from-square',
    'fa-cog' => 'fa-gear',
    'fa-cogs' => 'fa-gears',
    'fa-sign-out-alt' => 'fa-arrow-right-from-bracket',
    'fa-sign-in-alt' => 'fa-arrow-right-to-bracket',
];

$updated = str_replace(array_keys($replacements), array_values($replacements), $content);

file_put_contents($file, $updated);
echo "Successfully modernized all icons in resources/views/admin/dashboard.blade.php!" . PHP_EOL;
