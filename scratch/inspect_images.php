<?php
$content = file_get_contents('scratch/live_epub/OEBPS/brikkho-38.xhtml');
preg_match_all('/<img[^>]+>/i', $content, $imgs);
echo "Images inside brikkho-38: " . count($imgs[0]) . "\n";
foreach ($imgs[0] as $img) {
    echo "  $img\n";
}
