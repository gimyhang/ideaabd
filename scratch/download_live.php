<?php
$url = 'https://www.ideaabd.com/ebooks/1/stream?sample=1';
$content = @file_get_contents($url);
if ($content !== false) {
    file_put_contents('scratch/live_stream.epub', $content);
    echo "Downloaded live stream, size: " . strlen($content) . " bytes\n";
} else {
    echo "Failed to download live stream\n";
}
