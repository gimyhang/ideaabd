<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Modules\Book\Models\Book;
use Modules\Book\Models\Category;

echo "=== CATEGORIES IN DB ===\n";
$cats = DB::table('categories')->get();
echo "Total categories: " . $cats->count() . "\n";
foreach ($cats as $c) {
    $bookCount = Book::where('category_id', $c->id)->count();
    echo "ID {$c->id} | Name: '{$c->name}' | Slug: '{$c->slug}' | Active: {$c->is_active} | Books: {$bookCount}\n";
}

echo "\n=== CHECKING FOR DUPLICATE CATEGORIES ===\n";
$duplicates = DB::table('categories')
    ->select('name', DB::raw('count(*) as count'), DB::raw('GROUP_CONCAT(id) as ids'))
    ->groupBy('name')
    ->having('count', '>', 1)
    ->get();
echo "Duplicate category names: " . $duplicates->count() . "\n";
foreach ($duplicates as $d) {
    echo "Name: '{$d->name}' -> IDs: {$d->ids} (count: {$d->count})\n";
}

echo "\n=== ALL 77 BOOKS IN DB ===\n";
$books = Book::all();
echo "Total books: " . $books->count() . "\n";
$noCategory = [];
$inactive = [];
$pending = [];
foreach ($books as $b) {
    if (!$b->category_id) {
        $noCategory[] = "#{$b->id}: {$b->title}";
    }
    if (!$b->is_active) {
        $inactive[] = "#{$b->id}: {$b->title} (is_active={$b->is_active})";
    }
    if ($b->mod_status !== 'approved') {
        $pending[] = "#{$b->id}: {$b->title} (mod_status={$b->mod_status})";
    }
}
echo "Books without category_id (" . count($noCategory) . "):\n" . implode("\n", $noCategory) . "\n";
echo "Inactive books (" . count($inactive) . "):\n" . implode("\n", $inactive) . "\n";
echo "Non-approved books (" . count($pending) . "):\n" . implode("\n", $pending) . "\n";

echo "\n=== CHECKING CATEGORY LOOKUPS IN ContentController ===\n";
$lookups = DB::table('categories')->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id')->all();
echo "Lookups in ContentController:\n";
print_r($lookups);
