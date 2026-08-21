<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Book\Models\Book;
use Modules\Book\Models\Category;
use Illuminate\Support\Facades\DB;

echo "=== CHECKING CATEGORY 'আইন ও বিচার' ===\n";
$lawCats = Category::where('name', 'LIKE', '%আইন%')->orWhere('slug', 'LIKE', '%ain%')->orWhere('slug', 'LIKE', '%law%')->get();
echo "Found " . $lawCats->count() . " matching categories:\n";
foreach ($lawCats as $lc) {
    $bookCount = Book::where('category_id', $lc->id)->count();
    echo "ID: {$lc->id} | Name: '{$lc->name}' | Slug: '{$lc->slug}' | Active: {$lc->is_active} | Books: {$bookCount}\n";
    $bList = Book::where('category_id', $lc->id)->get();
    foreach ($bList as $bk) {
        echo "  -> Book #{$bk->id}: {$bk->title} (is_active={$bk->is_active}, mod_status={$bk->mod_status})\n";
    }
}

echo "\n=== TESTING /books?page=3 ===\n";
$req = Illuminate\Http\Request::create('/books?page=3', 'GET');
$controller = new \Modules\Book\Http\Controllers\Frontend\BookController();
try {
    $res = $controller->index($req);
    $data = $res->getData();
    echo "Page 3 rendered successfully!\n";
    echo "Books on page 3: " . count($data['books']) . "\n";
    echo "Total books in paginator: " . $data['books']->total() . "\n";
    echo "Current page in paginator: " . $data['books']->currentPage() . "\n";
    echo "isSearchMode: " . ($data['isSearchMode'] ? 'TRUE' : 'FALSE') . "\n";
} catch (\Throwable $e) {
    echo "ERROR on page 3: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
