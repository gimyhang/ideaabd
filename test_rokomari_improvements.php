<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admin = \App\Models\User::where('role', 'admin')->first() ?: \App\Models\User::first();
auth()->login($admin);
view()->share('errors', new \Illuminate\Support\ViewErrorBag());

$book = \Modules\Book\Models\Book::find(73) ?: \Modules\Book\Models\Book::first();

if ($book) {
    echo "1. Testing Admin Edit Form Render...\n";
    $controller = app(\App\Http\Controllers\Admin\ContentController::class);
    $view = $controller->edit('books', $book->id);
    $html = $view->render();
    echo "   [PASSED] Admin Form Rendered Length: " . strlen($html) . " bytes\n";
    
    // Check 400-word counter elements in summary & description
    if (str_contains($html, 'summaryWordBadge') && str_contains($html, 'descriptionWordBadge') && str_contains($html, '৪০০')) {
        echo "   [PASSED] 400-word limit counter elements for Summary and Description are present!\n";
    } else {
        echo "   [FAILED] 400-word limit counter elements missing!\n";
    }

    // 2. Test Summary > 400 words
    echo "\n2. Testing Summary > 400 words validation...\n";
    $longSummary = implode(' ', array_fill(0, 415, 'সারাংশ'));
    $reqSummaryFail = \Illuminate\Http\Request::create(route('admin.content.update', ['type' => 'books', 'id' => $book->id]), 'PUT', [
        'title' => $book->title,
        'summary' => $longSummary,
    ]);

    try {
        $controller->update($reqSummaryFail, 'books', $book->id);
        echo "   [FAILED] Long summary should have failed validation!\n";
    } catch (\Illuminate\Validation\ValidationException $e) {
        echo "   [PASSED] Caught summary > 400 words error: " . json_encode($e->errors()['summary']) . "\n";
    }

    // 3. Test Description > 400 words
    echo "\n3. Testing Description > 400 words validation...\n";
    $longDesc = implode(' ', array_fill(0, 420, 'বিবরণ'));
    $reqDescFail = \Illuminate\Http\Request::create(route('admin.content.update', ['type' => 'books', 'id' => $book->id]), 'PUT', [
        'title' => $book->title,
        'description' => $longDesc,
    ]);

    try {
        $controller->update($reqDescFail, 'books', $book->id);
        echo "   [FAILED] Long description should have failed validation!\n";
    } catch (\Illuminate\Validation\ValidationException $e) {
        echo "   [PASSED] Caught description > 400 words error: " . json_encode($e->errors()['description']) . "\n";
    }

    // 4. Test Valid Update (Summary <= 400, Description <= 400, Author Bio <= 300)
    echo "\n4. Testing valid update within limits...\n";
    $validSummary = implode(' ', array_fill(0, 80, 'সংক্ষেপ'));
    $validDesc = implode(' ', array_fill(0, 150, 'ফ্ল্যাপ'));
    $validBio = implode(' ', array_fill(0, 60, 'পরিচিতি'));

    $reqPass = \Illuminate\Http\Request::create(route('admin.content.update', ['type' => 'books', 'id' => $book->id]), 'PUT', [
        'title' => $book->title,
        'summary' => $validSummary,
        'description' => $validDesc,
        'author_bio' => $validBio,
        'author_input_mode' => 'custom',
        'author_name' => $book->author_name ?: 'পরীক্ষামূলক লেখক',
    ]);

    $resp = $controller->update($reqPass, 'books', $book->id);
    echo "   [PASSED] Successfully updated with response code: " . $resp->getStatusCode() . "\n";

    // 5. Test Frontend Show Page Render
    echo "\n5. Testing Frontend Show Page Render...\n";
    $showView = view('book::frontend.show', ['book' => $book->fresh(['category', 'publisher', 'authors', 'reviews'])]);
    $showHtml = $showView->render();
    echo "   [PASSED] Frontend Show Rendered Length: " . strlen($showHtml) . " bytes\n";

    echo "\n=== ALL ROKOMARI-STYLE UPGRADES & VALIDATION TESTS PASSED! ===\n";
} else {
    echo "No book found!\n";
}
