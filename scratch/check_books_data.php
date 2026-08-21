<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$total = \Modules\Book\Models\Book::count();
$active = \Modules\Book\Models\Book::where('is_active', true)->count();
echo "Total books: $total, Active books: $active\n";

$cats = \Modules\Book\Models\Category::withCount('books')->get();
foreach ($cats as $c) {
    if ($c->books_count > 0) {
        echo "Category ID {$c->id}: {$c->name} (slug: '{$c->slug}') -> {$c->books_count} books\n";
    }
}

$sampleBooks = \Modules\Book\Models\Book::latest()->take(10)->get();
foreach ($sampleBooks as $b) {
    echo "Book ID {$b->id}: {$b->title} | Cat ID: {$b->category_id} | Slug: '{$b->slug}' | Mod: '{$b->mod_status}' | Active: {$b->is_active} | Price: {$b->price}\n";
}
