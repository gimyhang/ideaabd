<?php
$c = file_get_contents('app/Http/Controllers/Admin/IdeaAccountingController.php');
$lines = explode("\n", $c);
foreach ($lines as $i => $l) {
    if (strpos($l, 'previous_due') !== false) {
        echo ($i + 1) . ": " . $l . "\n";
    }
}

