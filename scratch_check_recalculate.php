<?php
$c = file_get_contents('app/Models/IdeaInvoice.php');
$lines = explode("\n", $c);
foreach ($lines as $i => $l) {
    if (strpos($l, 'function recalculatePayments') !== false) {
        for ($j = $i; $j <= $i + 40; $j++) {
            if (isset($lines[$j])) echo ($j + 1) . ": " . $lines[$j] . "\n";
        }
        break;
    }
}

