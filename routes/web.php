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

/*
|--------------------------------------------------------------------------
| Web Routes (Ideap Platform Core Routes)
|--------------------------------------------------------------------------
*/

// --- Auth routes (login / logout) --------------------------------------------
Route::get('/login', fn() => view('auth.login'))->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// --- Search ------------------------------------------------------------------
Route::get('/search', [BookController::class, 'index'])->name('search');

// --- Wishlist / Cart / Checkout (placeholder stubs) --------------------------
Route::get('/wishlist', fn() => redirect('/books'))->name('wishlist')->middleware('auth');
Route::get('/cart', fn() => redirect('/books'))->name('cart');
Route::post('/cart/add', fn() => back())->name('cart.add');
Route::get('/checkout', fn() => redirect('/books'))->name('checkout');
Route::post('/newsletter/subscribe', fn() => back()->with('success', 'Subscribed successfully!'))->name('newsletter.subscribe');
Route::get('/webzines/archive', fn() => redirect(route('webzine.index')))->name('webzine.archive');

// হোমপেজ
Route::get('/', [HomeController::class, 'index'])->name('home');

// Shop Routes
Route::prefix('shop')->name('shop.')->group(function () {
    Route::get('/', [BookController::class, 'index'])->name('index');
    Route::get('/{slug}', [BookController::class, 'show'])->name('show');
    Route::get('/{slug}/preview', [BookController::class, 'preview'])->name('preview');
    Route::get('/{id}/quick-view', [BookController::class, 'quickView'])->name('quick-view');
});

// Books Routes
Route::prefix('books')->name('book.')->group(function () {
    Route::get('/', [BookController::class, 'index'])->name('index');
    Route::get('/{slug}', [BookController::class, 'show'])->name('show');
    Route::get('/{slug}/preview', [BookController::class, 'preview'])->name('preview');
    Route::get('/{id}/quick-view', [BookController::class, 'quickView'])->name('quick-view');
});

Route::post('/book-requests', [\App\Http\Controllers\BookRequestController::class, 'store'])->name('book-requests.store');
Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store'])->name('orders.store');

// Blog routes are defined in the Blog module (Modules/Blog/Routes/web.php), which
// already registers blog.index / blog.show / blog.category / blog.tag.

// Ebook Routes
Route::prefix('ebooks')->name('ebook.')->group(function () {
    Route::get('/', [EbookController::class, 'index'])->name('index');
    Route::get('/{slug}', [EbookController::class, 'show'])->name('show');
    Route::get('/{slug}/read', [EbookController::class, 'read'])->name('read');
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

// --- Admin panel ------------------------------------------------------------
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/books', [AdminController::class, 'books'])->name('books');
    Route::get('/blog', [AdminController::class, 'blog'])->name('blog');
    Route::get('/ebooks', [AdminController::class, 'ebooks'])->name('ebooks');
    Route::get('/webzines', [AdminController::class, 'webzines'])->name('webzines');
    Route::get('/authors', [AdminController::class, 'authors'])->name('authors');
    Route::get('/publishers', [AdminController::class, 'publishers'])->name('publishers');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::get('/ecommerce-orders', [AdminController::class, 'ecommerceOrders'])->name('ecommerce-orders');
    Route::get('/book-requests', [\App\Http\Controllers\BookRequestController::class, 'index'])->name('book-requests.index');
    Route::patch('/book-requests/{id}', [\App\Http\Controllers\BookRequestController::class, 'updateStatus'])->name('book-requests.update');

    // Content management — the admin creates, edits, approves, rejects and
    // deletes any book / ebook / author / publisher / blog post / webzine, and
    // can file it on behalf of a contributor who cannot register online.
    Route::get('/moderation', [ContentController::class, 'queue'])->name('moderation');

    Route::prefix('content/{type}')->name('content.')->controller(ContentController::class)
        ->where(['type' => implode('|', ContentTypes::keys()), 'id' => '[0-9]+'])
        ->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
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
        Route::patch('/{user}/approve', 'approve')->name('approve');
        Route::patch('/{user}/reject', 'reject')->name('reject');
        Route::delete('/{user}', 'cancel')->name('cancel');
    });

    // Admin access, permissions, activity logs & system settings
    Route::get('/roles-permissions', [AdminAccessController::class, 'rolesPermissions'])->name('roles.index');
    Route::post('/roles-permissions', [AdminAccessController::class, 'updatePermissions'])->name('roles.update');
    Route::get('/activity-logs', [AdminAccessController::class, 'activityLogs'])->name('activity-logs');
    Route::get('/system-settings', [AdminAccessController::class, 'systemSettings'])->name('system-settings');
    Route::post('/system-settings', [AdminAccessController::class, 'updateSystemSettings'])->name('system-settings.update');
});

// --- Sub-admin / Seller panel ---------------------------------------------
Route::prefix('seller')->name('subadmin.')->middleware(['auth', 'role:sub_admin,seller,admin'])->group(function () {
    Route::get('/bills', [BillingController::class, 'index'])->name('bills.index');
    Route::get('/bills/create', [BillingController::class, 'create'])->name('bills.create');
    Route::post('/bills', [BillingController::class, 'store'])->name('bills.store');
    Route::get('/bills/{bill}', [BillingController::class, 'show'])->name('bills.show');
    Route::get('/accounts', [BillingController::class, 'sellerAccounts'])->name('accounts');
});