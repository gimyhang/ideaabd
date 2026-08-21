<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Book\Models\Book;
use Modules\Book\Models\Category;

$books = Book::all(['id', 'title', 'category_id', 'author_name']);
echo "Total books in database: " . $books->count() . "\n";
foreach ($books as $b) {
    $catName = $b->category ? $b->category->name : 'NO CATEGORY';
    echo "#{$b->id} | {$b->title} | Cat: {$catName} (ID: {$b->category_id}) | Author: {$b->author_name}\n";
}
