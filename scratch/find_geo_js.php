<?php
$lines = file('resources/views/frontend/events/writer_register.blade.php');
foreach ($lines as $num => $line) {
    if (stripos($line, 'writerDivision') !== false || stripos($line, 'bd-geo') !== false || stripos($line, 'bangladesh') !== false || stripos($line, 'district') !== false && stripos($line, 'select') !== false) {
        echo ($num + 1) . ": " . trim($line) . PHP_EOL;
    }
}
