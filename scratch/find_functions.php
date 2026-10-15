<?php
$lines = file('app/Http/Controllers/Admin/EventCampaignAdminController.php');
foreach ($lines as $num => $line) {
    if (preg_match('/function\s+([a-zA-Z0-9_]+)/', $line, $m)) {
        echo ($num + 1) . ": " . $m[1] . PHP_EOL;
    }
}
