<?php
$content = file_get_contents('scratch/live_epub/OEBPS/brikkho-38.xhtml');

// Search for possible headings / chapter titles
preg_match_all('/<p[^>]*class="[^"]*ParaOverride-[1-7][^"]*"[^>]*>(.*?)<\/p>/su', $content, $m);
echo "ParaOverride matches: " . count($m[0]) . "\n";
foreach (array_slice($m[0], 0, 10) as $match) {
    echo "  " . strip_tags($match) . "\n";
}

// Search for any h1, h2, h3 or other distinctive paragraph tags
preg_match_all('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/su', $content, $h);
echo "Heading tags: " . count($h[0]) . "\n";
foreach ($h[0] as $match) {
    echo "  " . strip_tags($match) . "\n";
}
