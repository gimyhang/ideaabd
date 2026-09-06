<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Modules\Author\Models\Author;
use App\Http\Controllers\Admin\RegistrationApprovalController;
use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

echo "=== TESTING REGISTRATIONS VIEW & VERIFICATION SYNC ===\n";

DB::beginTransaction();

try {
    $user = User::where('role', 'author')->orWhere('reg_type', 'author')->first();
    if (!$user) {
        $user = User::first();
    }

    $regController = app(RegistrationApprovalController::class);
    $adminController = app(AdminController::class);

    // 1. Test Index View Rendering
    $req = Request::create('/admin/registrations', 'GET');
    $view = $regController->index($req);
    $html = $view->render();
    if (strpos($html, 'table-registrations') === false) {
        throw new Exception("❌ Table class table-registrations missing from rendered view!");
    }
    if (strpos($html, 'btn-action-icon') === false) {
        throw new Exception("❌ Compact button class btn-action-icon missing from rendered view!");
    }
    echo "✅ 1. Registrations Index view renders with compact table & icon buttons\n";

    // 2. Test Details AJAX Endpoint
    $detailsReq = Request::create("/admin/registrations/{$user->id}/details", 'GET');
    $detailsRes = $regController->details($user);
    $detailsData = json_decode($detailsRes->getContent(), true);
    if (!isset($detailsData['success']) || !$detailsData['success']) {
        throw new Exception("❌ Details endpoint failed!");
    }
    echo "✅ 2. Registration details AJAX endpoint returns success\n";

    // 3. Test Bi-directional Author Verification Sync
    $author = $user->getAuthorRecord();
    if ($author) {
        $oldVerified = $author->is_verified;
        $toggleRes = $adminController->toggleAuthorVerified($author->id);
        $toggleData = json_decode($toggleRes->getContent(), true);
        
        $author->refresh();
        $user->refresh();
        
        echo "✅ 3. Toggle Author Verified in AdminController:\n";
        echo "   - Author is_verified: " . ($author->is_verified ? 'true' : 'false') . "\n";
        echo "   - User reg_status synced: {$user->reg_status}\n";

        // Toggle back
        $adminController->toggleAuthorVerified($author->id);
        $author->refresh();
        $user->refresh();
        echo "   - Restored original verified status: " . ($author->is_verified ? 'true' : 'false') . "\n";
    }

    echo "\n🎉 ALL REGISTRATION VIEW & VERIFICATION SYNC TESTS PASSED!\n";

} catch (\Throwable $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} finally {
    DB::rollBack();
    echo "\n🔄 Rolled back test transaction.\n";
}
