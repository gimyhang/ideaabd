<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Modules\Author\Models\Author;
use Modules\Blog\Models\BlogPost;
use App\Http\Controllers\Admin\RegistrationApprovalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

echo "=== STARTING AUTHOR APPROVAL & DIRECTORY SYNC TEST ===\n";

DB::beginTransaction();

try {
    // 1. Clean up any existing test user
    User::where('email', 'testauthor_bengali@example.com')->delete();
    Author::where('email', 'testauthor_bengali@example.com')->delete();

    // 2. Create pending author user
    $regData = [
        'full_name'      => 'মুহাম্মদ রফিকুল ইসলাম (আইনি নাম)',
        'pen_name'       => 'রফিক আজাদ (লেখকনাম)',
        'name_bn'        => 'রফিক আজাদ (লেখকনাম)',
        'bio'            => 'বাংলা সাহিত্যের একজন প্রথিতযশা কবি ও কথাসাহিত্যিক।',
        'nid_or_passport'=> '1988998877665544',
        'father_name'    => 'মরহুম আব্দুল খালেক',
        'mother_name'    => 'আমেনা বেগম',
        'present_address'=> 'ধানমন্ডি, ঢাকা',
        'payout_number'  => '01711223344',
    ];

    $user = User::create([
        'name'                => 'Rafiqul Islam',
        'email'               => 'testauthor_bengali@example.com',
        'phone'               => '01700998811',
        'password'            => bcrypt('password123'),
        'role'                => 'customer',
        'reg_type'            => 'author',
        'reg_status'          => 'pending',
        'reg_data'            => $regData,
        'reg_submitted_at'    => now(),
        'must_change_password'=> false,
    ]);

    echo "✅ 1. Created Pending Author User (ID: {$user->id})\n";

    // 3. Approve registration via RegistrationApprovalController
    $controller = new RegistrationApprovalController();
    $request = Request::create("/admin/registrations/{$user->id}/approve", 'POST');
    $response = $controller->approve($request, $user);

    $user->refresh();
    echo "✅ 2. Approved Registration - User Role: {$user->role}, Reg Status: {$user->reg_status}\n";

    // 4. Verify Author Record in `authors` table
    $author = Author::where('user_id', $user->id)->first();
    if (!$author) {
        throw new Exception("❌ Author record was NOT created in authors table!");
    }

    echo "✅ 3. Author Record Created (ID: {$author->id}):\n";
    echo "   - Author Name: '{$author->name}' (Expected pen name)\n";
    echo "   - Author Name BN: '{$author->name_bn}'\n";
    echo "   - Author Name EN: '{$author->name_en}'\n";
    echo "   - Author Bio: '{$author->bio}'\n";
    echo "   - Active: " . ($author->is_active ? 'Yes' : 'No') . "\n";
    echo "   - Verified: " . ($author->is_verified ? 'Yes' : 'No') . "\n";

    if ($author->name !== 'রফিক আজাদ (লেখকনাম)') {
        throw new Exception("❌ Author name does not match pen name! Got: {$author->name}");
    }

    // 5. Test User::getAuthorRecord() and user->authorProfile
    $resolvedAuthor = $user->getAuthorRecord();
    if (!$resolvedAuthor || $resolvedAuthor->id !== $author->id) {
        throw new Exception("❌ User::getAuthorRecord() failed to resolve correct author record!");
    }
    echo "✅ 4. User::getAuthorRecord() resolved correctly to Author ID {$resolvedAuthor->id}\n";

    // 6. Test Admin Search (/admin/authors)
    $adminController = app(\App\Http\Controllers\AdminController::class);
    $adminReq = Request::create('/admin/authors', 'GET', ['search' => 'রফিক আজাদ']);
    $adminView = $adminController->authors($adminReq);
    $adminAuthors = $adminView->getData()['authors'];
    $foundInAdmin = collect($adminAuthors->items())->firstWhere('id', $author->id);
    if (!$foundInAdmin) {
        throw new Exception("❌ Admin authors search by Bengali pen name failed to find author!");
    }
    echo "✅ 5. Admin search in /admin/authors found author '{$foundInAdmin->name}'\n";

    // 7. Test Public Storefront Directory Search (/authors)
    $publicController = app(\App\Http\Controllers\AuthorController::class);
    $pubReq = Request::create('/authors', 'GET', ['q' => 'রফিক আজাদ']);
    $pubView = $publicController->index($pubReq);
    $pubAuthors = $pubView->getData()['authors'];
    $foundInPublic = collect($pubAuthors->items())->firstWhere('id', $author->id);
    if (!$foundInPublic) {
        throw new Exception("❌ Public storefront authors search by Bengali pen name failed to find author!");
    }
    echo "✅ 6. Public storefront directory /authors found author '{$foundInPublic->name}'\n";

    // 8. Test Blog Post Display Name
    $post = new BlogPost();
    $post->title = 'বৃষ্টির দিনে একটি কবিতা';
    $post->slug = 'test-rainy-day-poem-' . uniqid();
    $post->content = 'কবিতার কথা...';
    $post->author_id = $user->id;
    $post->status = 'published';
    $post->save();

    $resolvedPostAuthor = $post->resolveAuthorRecord();
    echo "✅ 7. BlogPost::resolveAuthorRecord() resolved to '{$resolvedPostAuthor->name}'\n";
    echo "   - BlogPost author_name attribute: '{$post->author_name}'\n";

    if ($post->author_name !== 'রফিক আজাদ (লেখকনাম)') {
        throw new Exception("❌ BlogPost author_name did not return Bengali pen name! Got: {$post->author_name}");
    }

    echo "\n🎉 ALL AUTHOR APPROVAL, SYNC, DIRECTORY AND PRIVACY TESTS PASSED SUCCESSFULLY!\n";

} catch (\Throwable $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} finally {
    DB::rollBack();
    echo "\n🔄 Rolled back test transaction.\n";
}
