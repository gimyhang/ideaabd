<?php
$content = file_get_contents('scratch/live_epub/OEBPS/brikkho-38.xhtml');
preg_match_all('/<p[^>]*>(.*?)<\/p>/su', $content, $matches);
echo "Total paragraphs in brikkho-38: " . count($matches[0]) . "\n";
echo "First 5 paragraphs:\n";
for ($i = 0; $i < min(5, count($matches[0])); $i++) {
    echo "  [" . ($i+1) . "] " . trim(strip_tags($matches[0][$i])) . "\n";
}
echo "Last 5 paragraphs:\n";
for ($i = max(0, count($matches[0]) - 5); $i < count($matches[0]); $i++) {
    echo "  [" . ($i+1) . "] " . trim(strip_tags($matches[0][$i])) . "\n";
}
