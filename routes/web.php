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
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::get('/login/refresh-bot-challenge', [LoginController::class, 'refreshBotChallenge'])->name('login.refresh-bot');
Route::match(['get', 'post'], '/logout', [LoginController::class, 'logout'])->name('logout');

// --- Password Reset via Email / WhatsApp (+8801558712810) ---
Route::get('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showRequestForm'])->name('password.request')->middleware('guest');
Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetLink'])->name('password.email')->middleware('guest');
Route::post('/forgot-password/send', [\App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetLink'])->name('password.send-otp')->middleware('guest');
Route::get('/reset-password-otp', [\App\Http\Controllers\Auth\PasswordResetController::class, 'showOtpResetForm'])->name('password.reset-otp')->middleware('guest');
Route::post('/reset-password-otp', [\App\Http\Controllers\Auth\PasswordResetController::class, 'resetPasswordWithOtp'])->name('password.update-otp')->middleware('guest');
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
Route::post('/cart/validate-coupon', [CartController::class, 'validateCoupon'])->name('cart.validate-coupon');
Route::post('/cart/add', fn() => back())->name('cart.add');
Route::post('/newsletter/subscribe', fn() => back()->with('success', 'Subscribed successfully!'))->name('newsletter.subscribe');

// --- Reviews & Comments (Dynamic, Registered & Guest Safe) -------------------
Route::post('/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
Route::get('/reviews', [\App\Http\Controllers\ReviewController::class, 'list'])->name('reviews.list');
// --- Automated & Online Payment Gateway Routes -----------------------------
Route::controller(\App\Http\Controllers\PaymentController::class)->group(function () {
    // bKash PGW
    Route::post('/payment/bkash/create', 'createBkashPayment')->name('bkash.create');
    Route::match(['get', 'post'], '/payment/bkash/callback', 'bkashCallback')->name('bkash.callback');

    // Nagad PGW
    Route::post('/payment/nagad/create', 'createNagadPayment')->name('nagad.create');
    Route::match(['get', 'post'], '/payment/nagad/callback', 'nagadCallback')->name('nagad.callback');

    // SSLCommerz PGW
    Route::post('/payment/sslcommerz/create', 'createSslcommerzPayment')->name('sslcommerz.create');
    Route::post('/payment/sslcommerz/success', 'sslcommerzSuccess')->name('sslcommerz.success');
    Route::post('/payment/sslcommerz/fail', 'sslcommerzFail')->name('sslcommerz.fail');
    Route::post('/payment/sslcommerz/cancel', 'sslcommerzCancel')->name('sslcommerz.cancel');
    Route::post('/payment/sslcommerz/ipn', 'sslcommerzIpn')->name('sslcommerz.ipn');

    // Global Payment Result Pages
    Route::get('/payment/success', 'success')->name('payment.success');
    Route::get('/payment/fail', 'fail')->name('payment.fail');
});

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

// Public / Client Invoice & Delivery Challan Viewer (Link & QR access)
Route::get('/invoices/view/{token}', [\App\Http\Controllers\Admin\IdeaAccountingController::class, 'publicShow'])->name('invoices.public.show');

// হোমপেজ
Route::get('/', [HomeController::class, 'index'])->name('home');

// Shop Routes (Redirect 301 permanently to /books to prevent duplicate URL canonical issues in Search Console)
Route::prefix('shop')->group(function () {
    Route::get('/', fn() => redirect('/books', 301))->name('shop.index');
    Route::get('/{slug}', fn($slug) => redirect('/books/' . $slug, 301))->name('shop.show');
    Route::get('/{slug}/preview', fn($slug) => redirect('/books/' . $slug . '/preview', 301))->name('shop.preview');
    Route::get('/{id}/quick-view', [BookController::class, 'quickView'])->name('shop.quick-view');
});

// Books Routes (Canonical Primary Shop)
Route::prefix('books')->name('book.')->group(function () {
    Route::get('/', [BookController::class, 'index'])->name('index');
    Route::get('/{slug}', [BookController::class, 'show'])->name('show');
    Route::get('/{slug}/preview', [BookController::class, 'preview'])->name('preview');
    Route::get('/{id}/quick-view', [BookController::class, 'quickView'])->name('quick-view');
});

Route::post('/book-requests', [\App\Http\Controllers\BookRequestController::class, 'store'])->name('book-requests.store');
Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store'])->name('orders.store');
Route::get('/api/recent-orders', [\App\Http\Controllers\SocialProofController::class, 'getRecentOrders']);

// Blog routes are defined in the Blog module (Modules/Blog/Routes/web.php), which
// already registers blog.index / blog.show / blog.category / blog.tag.

// Ebook Routes
Route::prefix('ebooks')->name('ebook.')->group(function () {
    Route::get('/', [\Modules\Ebook\Http\Controllers\Frontend\EbookController::class, 'index'])->name('index');
    Route::get('/{slug}', [\Modules\Ebook\Http\Controllers\Frontend\EbookController::class, 'show'])->name('show');
    Route::get('/{slug}/read', [\Modules\Ebook\Http\Controllers\Frontend\EbookController::class, 'read'])->name('read');
    Route::get('/{slug}/preview', [\Modules\Ebook\Http\Controllers\Frontend\EbookController::class, 'preview'])->name('preview');
    Route::get('/{slug}/download', [\Modules\Ebook\Http\Controllers\Frontend\EbookController::class, 'download'])->name('download');
    Route::post('/{slug}/claim', [\Modules\Ebook\Http\Controllers\Frontend\EbookController::class, 'claim'])->name('claim');
    Route::get('/{id}/stream', [\Modules\Ebook\Http\Controllers\Frontend\EbookController::class, 'stream'])->name('stream');
    Route::post('/{id}/progress', [\Modules\Ebook\Http\Controllers\Frontend\EbookController::class, 'saveProgress'])->name('progress');
});

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

// Ideapatra (আইডিয়াপত্র) Route Aliases & Honorarium Sending
Route::prefix('ideapatra')->name('ideapatra.')->group(function () {
    Route::get('/', [\Modules\Blog\Http\Controllers\Frontend\BlogController::class, 'index'])->name('index');
    Route::get('/category/{slug}', [\Modules\Blog\Http\Controllers\Frontend\BlogController::class, 'category'])->name('category');
    Route::get('/tag/{slug}', [\Modules\Blog\Http\Controllers\Frontend\BlogController::class, 'tag'])->name('tag');
    Route::get('/write', [\App\Http\Controllers\AuthorBlogController::class, 'writeGateway'])->name('write');
    Route::post('/honorarium/send', [\App\Http\Controllers\AuthorHonorariumController::class, 'store'])->name('honorarium.send');
    Route::get('/{slug}', [\Modules\Blog\Http\Controllers\Frontend\BlogController::class, 'show'])->name('show');
});
Route::post('/blog/honorarium/send', [\App\Http\Controllers\AuthorHonorariumController::class, 'store'])->name('blog.honorarium.send');
Route::post('/author-honorarium/send', [\App\Http\Controllers\AuthorHonorariumController::class, 'store'])->name('author.honorarium.send');

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

// --- User Account & Portal (Buyer / Customer) --------------------------------
Route::prefix('my-account')->middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\UserController::class, 'dashboard'])->name('my-account');
    Route::post('/profile', [\App\Http\Controllers\UserController::class, 'updateProfile'])->name('my-account.profile.update');
    Route::post('/address', [\App\Http\Controllers\UserController::class, 'updateAddress'])->name('my-account.address.update');
    Route::post('/password', [\App\Http\Controllers\UserController::class, 'updatePassword'])->name('my-account.password.update');
    Route::get('/orders/{id}', [\App\Http\Controllers\UserController::class, 'orderDetails'])->name('my-account.orders.details');
    Route::post('/wishlist/remove/{id}', [\App\Http\Controllers\UserController::class, 'removeFromWishlist'])->name('my-account.wishlist.remove');
});

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
    if ($user->isPublisher()) {
        return redirect()->route('publisher.dashboard');
    }
    if ($user->isAuthor()) {
        return redirect()->route('author.dashboard');
    }
    return redirect()->route('my-account');
})->name('user.portal');

// --- Publisher Portal & Catalog Management ---
Route::prefix('publisher')->name('publisher.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Publisher\PublisherPortalController::class, 'dashboard'])->name('dashboard');
    Route::post('/books', [\App\Http\Controllers\Publisher\PublisherPortalController::class, 'storeBook'])->name('books.store');
    Route::get('/books/{id}/edit', [\App\Http\Controllers\Publisher\PublisherPortalController::class, 'editBook'])->name('books.edit');
    Route::put('/books/{id}', [\App\Http\Controllers\Publisher\PublisherPortalController::class, 'updateBook'])->name('books.update');
    Route::post('/books/{id}/quick-update', [\App\Http\Controllers\Publisher\PublisherPortalController::class, 'quickUpdateBook'])->name('books.quick-update');
    Route::delete('/books/{id}', [\App\Http\Controllers\Publisher\PublisherPortalController::class, 'destroyBook'])->name('books.destroy');
    Route::post('/profile', [\App\Http\Controllers\Publisher\PublisherPortalController::class, 'updateProfile'])->name('profile.update');
    Route::get('/purchases/{id}/challan', [\App\Http\Controllers\Publisher\PublisherPortalController::class, 'printChallan'])->name('purchases.challan');
});

// Company Panel route alias (Rokomari style seller/company-panel redirect)
Route::middleware(['auth'])->group(function () {
    Route::get('/company-panel', fn() => redirect()->route('publisher.dashboard', ['tab' => 'overview']))->name('company-panel');
    Route::get('/company-panel/today-purchase-list', fn() => redirect()->route('publisher.dashboard', ['tab' => 'today-purchases', 'date_filter' => 'today']))->name('company-panel.today-purchases');
    Route::get('/company-panel/book-list', fn() => redirect()->route('publisher.dashboard', ['tab' => 'books']))->name('company-panel.books');
    Route::get('/company-panel/add-book', fn() => redirect()->route('publisher.dashboard', ['tab' => 'add-book']))->name('company-panel.add-book');
    Route::get('/company-panel/product-entry', fn() => redirect()->route('publisher.dashboard', ['tab' => 'add-book']))->name('company-panel.product-entry');
});


// --- Author Portal (KDP Self-Publishing, Royalties, Payouts & Blogs) --------
Route::get('/blog/write', [\App\Http\Controllers\AuthorBlogController::class, 'writeGateway'])->name('blog.write');

Route::prefix('author')->name('author.')->middleware(['auth'])->group(function () {
    // KDP Dashboard & Sales Analytics
    Route::get('/dashboard', [\App\Http\Controllers\Author\AuthorDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/royalties', [\App\Http\Controllers\Author\AuthorDashboardController::class, 'royalties'])->name('royalties');

    // Self-Publishing E-Books CRUD
    Route::post('/categories/quick-store', [\App\Http\Controllers\Author\AuthorEbookController::class, 'quickStoreCategory'])->name('categories.quick-store');
    Route::resource('ebooks', \App\Http\Controllers\Author\AuthorEbookController::class);

    // Royalty Payout / Withdrawal Requests
    Route::get('/payouts', [\App\Http\Controllers\Author\AuthorPayoutController::class, 'index'])->name('payouts.index');
    Route::post('/payouts', [\App\Http\Controllers\Author\AuthorPayoutController::class, 'storeRequest'])->name('payouts.store');

    // Ideapatra (Blog Articles) Management & Honorariums
    Route::get('/honorariums', [\App\Http\Controllers\Author\AuthorDashboardController::class, 'honorariums'])->name('honorariums');
    Route::get('/posts', [\App\Http\Controllers\AuthorBlogController::class, 'index'])->name('posts.index');
    Route::get('/posts/create', [\App\Http\Controllers\AuthorBlogController::class, 'createPost'])->name('posts.create');
    Route::post('/posts', [\App\Http\Controllers\AuthorBlogController::class, 'store'])->name('posts.store');
    Route::get('/posts/{id}/edit', [\App\Http\Controllers\AuthorBlogController::class, 'editPost'])->name('posts.edit');
    Route::put('/posts/{id}', [\App\Http\Controllers\AuthorBlogController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{id}', [\App\Http\Controllers\AuthorBlogController::class, 'destroy'])->name('posts.destroy');
    Route::prefix('blog')->name('blog.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AuthorBlogController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\AuthorBlogController::class, 'createPost'])->name('create');
        Route::post('/', [\App\Http\Controllers\AuthorBlogController::class, 'store'])->name('store');
        Route::put('/{id}', [\App\Http\Controllers\AuthorBlogController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\AuthorBlogController::class, 'destroy'])->name('destroy');
    });
});

// --- Admin panel ------------------------------------------------------------
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // E-Book Sales Report, Royalty Management & Payouts (KDP Engine)
    Route::get('/ebook-sales-report', [\App\Http\Controllers\Admin\AuthorRoyaltyAdminController::class, 'salesReport'])->name('ebook-sales-report');
    Route::get('/author-royalties', [\App\Http\Controllers\Admin\AuthorRoyaltyAdminController::class, 'index'])->name('author-royalties.index');
    Route::post('/author-royalties/adjustment', [\App\Http\Controllers\Admin\AuthorRoyaltyAdminController::class, 'storeAdjustment'])->name('author-royalties.adjustment');
    Route::get('/author-payouts', [\App\Http\Controllers\Admin\AuthorPayoutAdminController::class, 'index'])->name('author-payouts.index');
    Route::post('/author-payouts/{payout}/process', [\App\Http\Controllers\Admin\AuthorPayoutAdminController::class, 'process'])->name('author-payouts.process');
    Route::get('/author-payouts/{id}/receipt', [\App\Http\Controllers\Admin\AuthorRoyaltyAdminController::class, 'payoutReceipt'])->name('author-payouts.receipt');
    Route::get('/royalty-payout-logs', [\App\Http\Controllers\Admin\GatewayReportController::class, 'royaltyPayoutLogs'])->name('royalty-payout-logs');
    
    // IdeaPatra Author Honorariums (পড়ে ভালো লাগা সম্মানি)
    Route::get('/author-honorariums', [\App\Http\Controllers\Admin\AuthorHonorariumAdminController::class, 'index'])->name('author-honorariums.index');
    Route::patch('/author-honorariums/{id}/status', [\App\Http\Controllers\Admin\AuthorHonorariumAdminController::class, 'updateStatus'])->name('author-honorariums.status');
    Route::delete('/author-honorariums/{id}', [\App\Http\Controllers\Admin\AuthorHonorariumAdminController::class, 'destroy'])->name('author-honorariums.destroy');

    // Customer Payment Gateway Reports & Transaction Logs
    Route::get('/gateway-reports', [\App\Http\Controllers\Admin\GatewayReportController::class, 'index'])->name('gateway-reports');

    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/books', [AdminController::class, 'books'])->name('books');
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::get('/blog', [AdminController::class, 'blog'])->name('blog');
    Route::get('/blog-categories', [AdminController::class, 'blogCategories'])->name('blog-categories');
    Route::post('/blog/settings', [AdminController::class, 'updateBlogSettings'])->name('blog.settings.update');
    Route::post('/blog/bulk-normalize-typography', [AdminController::class, 'bulkNormalizeBlogTypography'])->name('blog.bulk-normalize-typography');
    Route::post('/blog/bulk-action', [AdminController::class, 'bulkBlogAction'])->name('blog.bulk-action');
    Route::post('/blog/{id}/toggle-status', [AdminController::class, 'togglePostStatus'])->name('blog.toggle-status');
    Route::post('/blog/{id}/toggle-featured', [AdminController::class, 'togglePostFeatured'])->name('blog.toggle-featured');
    Route::delete('/blog/{id}', [AdminController::class, 'destroyPost'])->name('blog.destroy');
    Route::get('/ebooks', [AdminController::class, 'ebooks'])->name('ebooks');
    Route::post('/ebooks/settings', [AdminController::class, 'updateEbookSettings'])->name('ebooks.settings');
    Route::post('/ebooks/{id}/toggle-status', [AdminController::class, 'toggleEbookStatus'])->name('ebooks.toggle-status');
    Route::post('/ebooks/{id}/approve', [AdminController::class, 'approveEbook'])->name('ebooks.approve');
    Route::post('/ebooks/{id}/reject', [AdminController::class, 'rejectEbook'])->name('ebooks.reject');
    Route::get('/webzines', [AdminController::class, 'webzines'])->name('webzines');
    Route::get('/authors', [AdminController::class, 'authors'])->name('authors');
    Route::post('/authors/quick-store', [AdminController::class, 'quickStoreAuthor'])->name('authors.quick-store');
    Route::get('/authors/{id}/details', [AdminController::class, 'authorDetails'])->name('authors.details');
    Route::post('/authors/{id}/quick-update', [AdminController::class, 'quickUpdateAuthor'])->name('authors.quick-update');
    Route::post('/authors/{id}/toggle-status', [AdminController::class, 'toggleAuthorStatus'])->name('authors.toggle-status');
    Route::post('/authors/{id}/toggle-verified', [AdminController::class, 'toggleAuthorVerified'])->name('authors.toggle-verified');
    Route::post('/authors/{id}/reset-password', [AdminController::class, 'resetAuthorPassword'])->name('authors.reset-password');
    Route::get('/publishers', [AdminController::class, 'publishers'])->name('publishers');
    Route::post('/publishers/quick-store', [AdminController::class, 'quickStorePublisher'])->name('publishers.quick-store');
    Route::get('/publishers/{id}', [AdminController::class, 'publisherShow'])->name('publishers.show');
    Route::post('/publishers/{id}/quick-update', [AdminController::class, 'quickUpdatePublisher'])->name('publishers.quick-update');
    Route::post('/publishers/{id}/toggle-status', [AdminController::class, 'togglePublisherStatus'])->name('publishers.toggle-status');
    Route::post('/publishers/{id}/quick-payment', [AdminController::class, 'quickPublisherPayment'])->name('publishers.quick-payment');
    Route::post('/publishers/{id}/send-purchase-order', [AdminController::class, 'sendPublisherPurchaseOrderEmail'])->name('publishers.send-po');

    // Publisher Purchases & Payment Installments
    Route::prefix('purchases')->name('purchases.')->controller(\App\Http\Controllers\Admin\PublisherPurchaseController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/payments', 'payments')->name('payments');
        Route::post('/payments', 'storePayment')->name('payments.store');
        Route::get('/monthly-report', 'monthlyReport')->name('monthly-report');
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
        Route::get('/invoices/{invoice}/edit', 'editInvoice')->name('invoices.edit');
        Route::put('/invoices/{invoice}', 'updateInvoice')->name('invoices.update');
        Route::post('/invoices/{invoice}/send-email', 'sendInvoiceEmail')->name('invoices.send-email');
        Route::post('/invoices/{invoice}/convert', 'convertInvoiceType')->name('invoices.convert');
        Route::delete('/invoices/{invoice}', 'destroyInvoice')->name('invoices.destroy');
        Route::post('/settings', 'updateSettings')->name('settings.update');

        // Financial & P&L Reports (Daily, Weekly, Monthly, Yearly)
        Route::get('/reports', 'reports')->name('reports.index');

        // Employees & Staff Payroll Management
        Route::get('/employees', 'employees')->name('employees.index');
        Route::post('/employees', 'storeEmployee')->name('employees.store');
        Route::put('/employees/{employee}', 'updateEmployee')->name('employees.update');
        Route::delete('/employees/{employee}', 'destroyEmployee')->name('employees.destroy');

        // Salary Disbursement & Pay Slips
        Route::get('/salary', 'salaryDisbursements')->name('salary.index');
        Route::post('/salary', 'storeSalaryPayment')->name('salary.store');
        Route::get('/salary/{salary}/slip', 'salarySlip')->name('salary.slip');
    });

    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::get('/ecommerce-orders', [AdminController::class, 'ecommerceOrders'])->name('ecommerce-orders');
    Route::get('/ecommerce-orders/{order}', [AdminController::class, 'showEcommerceOrder'])->name('ecommerce-orders.show');
    Route::put('/ecommerce-orders/{order}', [AdminController::class, 'updateEcommerceOrder'])->name('ecommerce-orders.update');
    Route::patch('/ecommerce-orders/{order}/status', [AdminController::class, 'updateEcommerceOrderStatus'])->name('ecommerce-orders.status');
    Route::delete('/ecommerce-orders/{order}', [AdminController::class, 'destroyEcommerceOrder'])->name('ecommerce-orders.destroy');
    Route::get('/ecommerce-orders/{order}/invoice', [AdminController::class, 'ecommerceOrderInvoice'])->name('ecommerce-orders.invoice');
    Route::get('/ecommerce-orders/{order}/slip', [AdminController::class, 'ecommerceOrderSlip'])->name('ecommerce-orders.slip');
    Route::get('/book-requests', [\App\Http\Controllers\BookRequestController::class, 'index'])->name('book-requests.index');
    Route::post('/book-requests/admin-store', [\App\Http\Controllers\BookRequestController::class, 'storeAdmin'])->name('book-requests.admin-store');
    Route::patch('/book-requests/{id}', [\App\Http\Controllers\BookRequestController::class, 'updateStatus'])->name('book-requests.update');
    Route::post('/book-requests/{id}/notes', [\App\Http\Controllers\BookRequestController::class, 'updateNotes'])->name('book-requests.notes');
    Route::delete('/book-requests/{id}', [\App\Http\Controllers\BookRequestController::class, 'destroy'])->name('book-requests.destroy');
    Route::post('/book-requests/bulk-action', [\App\Http\Controllers\BookRequestController::class, 'bulkAction'])->name('book-requests.bulk-action');
    Route::get('/visitor-reports', [AdminController::class, 'visitorReports'])->name('visitor-reports');
    Route::get('/reports/print', [AdminController::class, 'printReport'])->name('reports.print');
    Route::post('/books/quick-stock', [AdminController::class, 'quickUpdateStock'])->name('books.quick-stock');
    Route::post('/books/quick-update', [AdminController::class, 'quickUpdateBook'])->name('books.quick-update');
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
        Route::get('/{user}/details', 'details')->name('details');
        Route::get('/{user}', 'show')->name('show');
        Route::get('/{user}/edit', 'edit')->name('edit');
        Route::put('/{user}', 'update')->name('update');
        Route::post('/{user}/quick-update', 'quickUpdate')->name('quick-update');
        Route::patch('/{user}/approve', 'approve')->name('approve');
        Route::patch('/{user}/reject', 'reject')->name('reject');
        Route::patch('/{user}/toggle-status', 'toggleStatus')->name('toggle-status');
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