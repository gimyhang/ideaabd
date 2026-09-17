<?php

for ($i = 1; $i <= 38; $i++) {
    $file = "scratch/live_epub/OEBPS/brikkho-$i.xhtml";
    if (file_exists($file)) {
        $content = file_get_contents($file);
        // Strip XML/HTML tags to see plain text
        $text = strip_tags($content);
        $text = trim(preg_replace('/\s+/', ' ', $text));
        echo "brikkho-$i.xhtml => $text\n";
    }
}
