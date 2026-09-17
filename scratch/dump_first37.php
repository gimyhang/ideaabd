<?php

for ($i = 1; $i <= 37; $i++) {
    $file = "scratch/live_epub/OEBPS/brikkho-$i.xhtml";
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $text = strip_tags($content);
        $text = trim(preg_replace('/\s+/', ' ', $text));
        echo "brikkho-$i.xhtml: $text\n";
    }
}
