<?php

$files = [
    'storage/app/public/ebooks/briksh-zkhn-ktha-ble.epub',
    'storage/app/public/ebooks/briksh-sample.epub'
];

foreach ($files as $file) {
    echo "=== File: $file ===\n";
    $zip = new ZipArchive();
    if ($zip->open($file) === TRUE) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            echo "  $name\n";
        }
        $zip->close();
    }
}
