<?php
require __DIR__ . '/../vendor/autoload.php';

$divisions = ['ঢাকা', 'চট্টগ্রাম', 'রাজশাহী', 'খুলনা', 'বরিশাল', 'সিলেট', 'রংপুর', 'ময়মনসিংহ', 'অনির্ধারিত বিভাগ'];
foreach ($divisions as $d) {
    echo $d . ' => ' . \Illuminate\Support\Str::slug($d) . ' | md5: ' . substr(md5($d), 0, 8) . PHP_EOL;
}
