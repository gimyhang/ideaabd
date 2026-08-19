<?php

use Illuminate\Support\Facades\Route;
use Modules\Book\Http\Controllers\Frontend\BookController;
use Modules\Blog\Http\Controllers\Frontend\BlogController;
use Modules\Ebook\Http\Controllers\Frontend\EbookController;
use Modules\Webzine\Http\Controllers\Frontend\WebzineController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\PublisherController;
use App\Http\Controllers\ResearchController;
use App\Http\Controllers\AdminController;

use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\RegistrationApprovalController;
use App\Http\Controllers\Admin\SubAdminController;
use App\Http\Controllers\Admin\AdminAccessController;
use App\Support\ContentTypes;
use App\Http\Controllers\SubAdmin\BillingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;

use App\Http\Controllers\CartController;
use App\Http\Controllers\Admin\PaymentAdminController;

/*
|--------------------------------------------------------------------------
| Web Routes (Ideap Platform Core Routes)
|--------------------------------------------------------------------------
*/

// --- Language Switcher ------------------------------------------------------
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['bn', 'en'], true)) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.switch');

// --- XML Sitemap for SEO ---
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// --- Auth routes (login / logout) --------------------------------------------
Route::get('/login', fn() => view('auth.login'))->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// --- Password Reset via 3-Minute One-Time Email Link ---
Route::get('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showRequestForm'])->name('password.request')->middleware('guest');
Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetLink'])->name('password.email')->middleware('guest');
Route::post('/forgot-password/send', [\App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetLink'])->name('password.send-otp')->middleware('guest'); // Backwards-compatible alias
Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showResetForm'])->name('password.reset')->middleware('guest');
Route::post('/reset-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'resetPassword'])->name('password.update')->middleware('guest');


// --- Search ------------------------------------------------------------------
Route::get('/search', [BookController::class, 'index'])->name('search');

// --- Wishlist / Cart / Checkout ----------------------------------------------
Route::get('/wishlist', fn() => redirect('/books'))->name('wishlist')->middleware('auth');
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::get('/checkout', [CartController::class, 'index'])->name('checkout');
Route::match(['get', 'post'], '/cart/checkout', function (\Illuminate\Http\Request $request) {
    if ($request->isMethod('get')) {
        return redirect()->route('cart');
    }
    return app(CartController::class)->checkout($request);
})->name('cart.checkout');
Route::post('/cart/add', fn() => back())->name('cart.add');
Route::post('/newsletter/subscribe', fn() => back()->with('success', 'Subscribed successfully!'))->name('newsletter.subscribe');
Route::get('/webzines/archive', fn() => redirect(route('webzine.index')))->name('webzine.archive');

// --- Storage Fallback Route for Live Shared Hosts & CPanel without Symlink ---
Route::get('/storage/{path}', function (string $path) {
    $filePath = storage_path('app/public/' . $path);
    if (!file_exists($filePath)) {
        abort(404);
    }
    $mime = mime_content_type($filePath) ?: 'application/octet-stream';
    return response()->file($filePath, ['Content-Type' => $mime]);
})->where('path', '.*')->name('storage.file');

// হোমপেজ
Route::get('/', [HomeController::class, 'index'])->name('home');

// Shop Routes
Route::prefix('shop')->name('shop.')->group(function () {
    Route::get('/', [BookController::class, 'index'])->name('index');
    Route::get('/{slug}', [BookController::class, 'show'])->name('show');
    Route::get('/{slug}/preview', [BookController::class, 'preview'])->name('preview');
    Route::get('/{id}/quick-view', [BookController::class, 'quickView'])->name('quick-view');
});

// Books Routes (with dual alias for book.* and books.*)
Route::prefix('books')->group(function () {
    Route::get('/', [BookController::class, 'index'])->name('book.index');
    Route::get('/{slug}', [BookController::class, 'show'])->name('book.show');
    Route::get('/{slug}/preview', [BookController::class, 'preview'])->name('book.preview');
    Route::get('/{id}/quick-view', [BookController::class, 'quickView'])->name('book.quick-view');
});
// Route aliases for backward compatibility (books.*)
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{slug}', [BookController::class, 'show'])->name('books.show');
Route::get('/books/{slug}/preview', [BookController::class, 'preview'])->name('books.preview');

Route::post('/book-requests', [\App\Http\Controllers\BookRequestController::class, 'store'])->name('book-requests.store');
Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store'])->name('orders.store');
Route::get('/api/recent-orders', [\App\Http\Controllers\SocialProofController::class, 'getRecentOrders']);

// Blog routes are defined in the Blog module (Modules/Blog/Routes/web.php), which
// already registers blog.index / blog.show / blog.category / blog.tag.

// Ebook Routes (with dual alias for ebook.* and ebooks.*)
Route::prefix('ebooks')->group(function () {
    Route::get('/', [EbookController::class, 'index'])->name('ebook.index');
    Route::get('/{slug}', [EbookController::class, 'show'])->name('ebook.show');
    Route::get('/{slug}/read', [EbookController::class, 'read'])->name('ebook.read');
    Route::get('/{slug}/download', [EbookController::class, 'download'])->name('ebook.download');
});
// Route aliases for backward compatibility (ebooks.*)
Route::get('/ebooks', [EbookController::class, 'index'])->name('ebooks.index');
Route::get('/ebooks/{slug}', [EbookController::class, 'show'])->name('ebooks.show');
Route::get('/ebooks/{slug}/read', [EbookController::class, 'read'])->name('ebooks.read');
Route::get('/ebooks/{slug}/download', [EbookController::class, 'download'])->name('ebooks.download');

// Webzine routes are defined in the Webzine module (Modules/Webzine/Routes/web.php)
// Removed duplicate route group to avoid duplicate route name "webzine.index".

// Author Routes
//
// NOTE: the Author/Publisher modules register authors/{slug} and publishers/{slug}
// before this file loads, so those module routes are what actually serve the detail
// pages. The `.show` names below stay declared because views link to them and the
// generated URL resolves to the module route.
Route::prefix('authors')->name('authors.')->group(function () {
    Route::get('/', [AuthorController::class, 'index'])->name('index');
    Route::get('/{author}', [AuthorController::class, 'show'])->name('show');
});

// Publisher Routes
Route::prefix('publishers')->name('publishers.')->group(function () {
    Route::get('/', [PublisherController::class, 'index'])->name('index');
    Route::get('/{publisher}', [PublisherController::class, 'show'])->name('show');
});

// Research Routes — these override the Research module's index/show on purpose.
Route::prefix('research')->name('research.')->group(function () {
    Route::get('/', [ResearchController::class, 'index'])->name('index');
    Route::get('/{slug}', [ResearchController::class, 'show'])->name('show');
});

// Static Pages
Route::view('/hub', 'frontend.pages.hub')->name('hub');
Route::view('/about', 'frontend.pages.about')->name('about');
Route::view('/contact', 'frontend.pages.contact')->name('contact');

// --- Registration routes --------------------------------------------------
Route::get('/register', [RegistrationController::class, 'choose'])->name('register.choose');
Route::get('/register/{type}', [RegistrationController::class, 'showForm'])->name('register.form');
Route::post('/register/{type}', [RegistrationController::class, 'register'])->name('register.submit');
Route::get('/pending-approval', [RegistrationController::class, 'pendingApproval'])
    ->middleware('auth')->name('pending.approval');

// --- User Account & Portal --------------------------------------------------
Route::get('/my-account', [\App\Http\Controllers\UserController::class, 'dashboard'])
    ->middleware('auth')->name('my-account');

Route::get('/user', function () {
    if (!auth()->check()) {
        return redirect()->guest(route('login'));
    }
    $user = auth()->user();
    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    if ($user->isSeller() || $user->isSubAdmin()) {
        return redirect()->route('subadmin.bills.index');
    }
    return redirect()->route('my-account');
})->name('user.portal');

// --- Author Portal & Blog Management (Dashboard, Write Post, Draft, Edit, Delete) ---
Route::get('/blog/write', [\App\Http\Controllers\AuthorBlogController::class, 'writeGateway'])->name('blog.write');

Route::prefix('author')->name('author.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\AuthorBlogController::class, 'dashboard'])->name('dashboard');
    Route::get('/posts/create', [\App\Http\Controllers\AuthorBlogController::class, 'createPost'])->name('posts.create');
    Route::get('/posts/{id}/edit', [\App\Http\Controllers\AuthorBlogController::class, 'editPost'])->name('posts.edit');
    Route::prefix('blog')->name('blog.')->group(function () {
        Route::post('/', [\App\Http\Controllers\AuthorBlogController::class, 'store'])->name('store');
        Route::put('/{id}', [\App\Http\Controllers\AuthorBlogController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\AuthorBlogController::class, 'destroy'])->name('destroy');
    });
});

// --- Admin panel ------------------------------------------------------------
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/books', [AdminController::class, 'books'])->name('books');
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::get('/blog', [AdminController::class, 'blog'])->name('blog');
    Route::get('/blog-categories', [AdminController::class, 'blogCategories'])->name('blog-categories');
    Route::get('/ebooks', [AdminController::class, 'ebooks'])->name('ebooks');
    Route::get('/webzines', [AdminController::class, 'webzines'])->name('webzines');
    Route::get('/authors', [AdminController::class, 'authors'])->name('authors');
    Route::get('/publishers', [AdminController::class, 'publishers'])->name('publishers');

    // Publisher Purchases & Payment Installments
    Route::prefix('purchases')->name('purchases.')->controller(\App\Http\Controllers\Admin\PublisherPurchaseController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/payments', 'payments')->name('payments');
        Route::post('/payments', 'storePayment')->name('payments.store');
        Route::get('/{purchase}', 'show')->name('show');
        Route::get('/{purchase}/edit', 'edit')->name('edit');
        Route::put('/{purchase}', 'update')->name('update');
        Route::delete('/{purchase}', 'destroy')->name('destroy');
    });

    // Idea Prokashon Accounting, Invoicing, Income & Expense Management
    Route::prefix('accounting')->name('accounting.')->controller(\App\Http\Controllers\Admin\IdeaAccountingController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/entries', 'storeEntry')->name('entries.store');
        Route::delete('/entries/{entry}', 'destroyEntry')->name('entries.destroy');
        Route::get('/invoices', 'invoices')->name('invoices.index');
        Route::get('/invoices/create', 'createInvoice')->name('invoices.create');
        Route::post('/invoices', 'storeInvoice')->name('invoices.store');
        Route::get('/invoices/{invoice}', 'showInvoice')->name('invoices.show');
        Route::delete('/invoices/{invoice}', 'destroyInvoice')->name('invoices.destroy');
    });

    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::get('/ecommerce-orders', [AdminController::class, 'ecommerceOrders'])->name('ecommerce-orders');
    Route::get('/ecommerce-orders/{order}', [AdminController::class, 'showEcommerceOrder'])->name('ecommerce-orders.show');
    Route::put('/ecommerce-orders/{order}', [AdminController::class, 'updateEcommerceOrder'])->name('ecommerce-orders.update');
    Route::patch('/ecommerce-orders/{order}/status', [AdminController::class, 'updateEcommerceOrderStatus'])->name('ecommerce-orders.status');
    Route::get('/ecommerce-orders/{order}/invoice', [AdminController::class, 'ecommerceOrderInvoice'])->name('ecommerce-orders.invoice');
    Route::get('/ecommerce-orders/{order}/slip', [AdminController::class, 'ecommerceOrderSlip'])->name('ecommerce-orders.slip');
    Route::delete('/ecommerce-orders/{order}', [AdminController::class, 'destroyEcommerceOrder'])->name('ecommerce-orders.destroy');
    Route::get('/book-requests', [\App\Http\Controllers\BookRequestController::class, 'index'])->name('book-requests.index');
    Route::patch('/book-requests/{id}', [\App\Http\Controllers\BookRequestController::class, 'updateStatus'])->name('book-requests.update');
    Route::get('/visitor-reports', [AdminController::class, 'visitorReports'])->name('visitor-reports');
    Route::get('/reports/print', [AdminController::class, 'printReport'])->name('reports.print');
    Route::post('/books/quick-stock', [AdminController::class, 'quickUpdateStock'])->name('books.quick-stock');
    Route::get('/customers', [AdminController::class, 'customers'])->name('customers');
    Route::post('/customers/broadcast-message', [AdminController::class, 'broadcastMessage'])->name('customers.broadcast');

    // Content management — the admin creates, edits, approves, rejects and
    // deletes any book / ebook / author / publisher / blog post / webzine, and
    // can file it on behalf of a contributor who cannot register online.
    Route::get('/moderation', [ContentController::class, 'queue'])->name('moderation');

    Route::prefix('content/{type}')->name('content.')->controller(ContentController::class)
        ->where(['type' => implode('|', ContentTypes::keys()), 'id' => '[0-9]+'])
        ->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}', 'show')->name('show');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::patch('/{id}/approve', 'approve')->name('approve');
            Route::patch('/{id}/reject', 'reject')->name('reject');
            Route::patch('/{id}/restore', 'restore')->name('restore');
        });

    // Sub-admin & seller accounts, managed from under the site admin dashboard
    Route::prefix('sub-admins')->name('sub-admins.')->controller(SubAdminController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{user}', 'show')->name('show');
        Route::patch('/{user}/toggle', 'toggle')->name('toggle');
        Route::delete('/{user}', 'destroy')->name('destroy');
    });

    // Registration approval (admin only)
    Route::prefix('registrations')->name('registrations.')->controller(RegistrationApprovalController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{user}', 'show')->name('show');
        Route::get('/{user}/edit', 'edit')->name('edit');
        Route::put('/{user}', 'update')->name('update');
        Route::patch('/{user}/approve', 'approve')->name('approve');
        Route::patch('/{user}/reject', 'reject')->name('reject');
        Route::delete('/{user}', 'cancel')->name('cancel');
    });

    // Payment management & gateways
    Route::get('/payments', [PaymentAdminController::class, 'index'])->name('payments.index');
    Route::post('/payments', [PaymentAdminController::class, 'updateGateways'])->name('payments.update');
    Route::patch('/payments/{order}/status', [PaymentAdminController::class, 'updateStatus'])->name('payments.status');

    // Quick AJAX resource creators for books/ebooks/blog forms
    Route::post('/quick/category', [\App\Http\Controllers\Admin\QuickResourceController::class, 'quickStoreCategory'])->name('quick.category');
    Route::post('/quick/blog-category', [\App\Http\Controllers\Admin\QuickResourceController::class, 'quickStoreBlogCategory'])->name('quick.blog-category');
    Route::post('/quick/author', [\App\Http\Controllers\Admin\QuickResourceController::class, 'quickStoreAuthor'])->name('quick.author');
    Route::post('/quick/publisher', [\App\Http\Controllers\Admin\QuickResourceController::class, 'quickStorePublisher'])->name('quick.publisher');

    // Admin access, permissions, activity logs & system settings
    Route::get('/roles-permissions', [AdminAccessController::class, 'rolesPermissions'])->name('roles.index');
    Route::post('/roles-permissions', [AdminAccessController::class, 'updatePermissions'])->name('roles.update');
    Route::get('/activity-logs', [AdminAccessController::class, 'activityLogs'])->name('activity-logs');
    Route::get('/system-settings', [AdminAccessController::class, 'systemSettings'])->name('system-settings');
    Route::post('/system-settings', [AdminAccessController::class, 'updateSystemSettings'])->name('system-settings.update');
    Route::post('/system-settings/clear-cache', [AdminAccessController::class, 'clearCache'])->name('system-settings.clear-cache');
});

// --- Sub-admin / Seller panel ---------------------------------------------
Route::prefix('seller')->name('subadmin.')->middleware(['auth', 'role:sub_admin,seller,admin'])->group(function () {
    Route::get('/bills', [BillingController::class, 'index'])->name('bills.index');
    Route::get('/bills/create', [BillingController::class, 'create'])->name('bills.create');
    Route::post('/bills', [BillingController::class, 'store'])->name('bills.store');
    Route::get('/bills/{bill}', [BillingController::class, 'show'])->name('bills.show');
    Route::get('/bills/{bill}/edit', [BillingController::class, 'edit'])->name('bills.edit');
    Route::put('/bills/{bill}', [BillingController::class, 'update'])->name('bills.update');
    Route::delete('/bills/{bill}', [BillingController::class, 'destroy'])->name('bills.destroy');
    Route::get('/accounts', [BillingController::class, 'sellerAccounts'])->name('accounts');
    Route::get('/api/books/search', [BillingController::class, 'searchBooks'])->name('books.search');
});