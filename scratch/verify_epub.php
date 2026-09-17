<?php

// Verify new EPUB structure
$epubPath = 'storage/app/public/ebooks/briksh-zkhn-ktha-ble.epub';
echo "EPUB size: " . filesize($epubPath) . " bytes\n";

$zip = new ZipArchive();
if ($zip->open($epubPath) === TRUE) {
    echo "Total entries: " . $zip->numFiles . "\n";
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        echo "  " . $stat['name'] . " (" . $stat['size'] . " bytes)\n";
    }
    $zip->close();
} else {
    // If ZipArchive not loaded in PHP CLI, test with PowerShell
    echo "ZipArchive extension not loaded in CLI.\n";
}
