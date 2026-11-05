<?php
$c = file_get_contents('app/Http/Controllers/Admin/IdeaAccountingController.php');
$lines = explode("\n", $c);
for ($i = 2670; $i <= 2820; $i++) {
    if (isset($lines[$i])) {
        echo ($i + 1) . ": " . $lines[$i] . "\n";
    }
}

