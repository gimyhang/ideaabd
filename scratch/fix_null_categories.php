<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Book\Models\Book;

$b74 = Book::find(74);
if ($b74 && !$b74->category_id) {
    $b74->category_id = 2; // উপন্যাস
    $b74->save();
    echo "Fixed Book #74 category_id = 2 (উপন্যাস)\n";
}

$b76 = Book::find(76);
if ($b76 && !$b76->category_id) {
    $b76->category_id = 4; // শিশু-কিশোর বই
    $b76->save();
    echo "Fixed Book #76 category_id = 4 (শিশু-কিশোর বই)\n";
}

$b1 = Book::find(1);
if ($b1 && !$b1->category_id) {
    $b1->category_id = 11; // বিজ্ঞান ও প্রযুক্তি
    $b1->save();
    echo "Fixed Book #1 category_id = 11 (বিজ্ঞান ও প্রযুক্তি)\n";
}

echo "All null categories fixed!\n";
