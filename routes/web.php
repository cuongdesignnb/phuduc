<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\MediaLibraryController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\WarrantyController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HomeContentController;
use App\Http\Controllers\Guest\ProductController as GuestProductController;
use App\Http\Controllers\Guest\CartController;
use App\Http\Controllers\Guest\CheckoutController;
use App\Http\Controllers\Guest\OrderLookupController;
use App\Http\Controllers\Guest\ReviewController as GuestReviewController;
use App\Http\Controllers\Guest\WarrantyLookupController;
use App\Http\Controllers\Guest\NewsController;
use App\Http\Controllers\Guest\PageController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ─── Public / Guest Routes ───────────────────────────────────────

Route::get('/', [PageController::class, 'home'])->name('home');

// Products
Route::get('/san-pham', [GuestProductController::class, 'index'])->name('products.index');
Route::get('/san-pham/{slug}', [GuestProductController::class, 'show'])->name('products.show');

// Cart & Checkout
Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
Route::post('/gio-hang/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/gio-hang/update', [CartController::class, 'update'])->name('cart.update');
Route::delete('/gio-hang/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/thanh-toan', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/thanh-toan/thanh-cong/{order}', [CheckoutController::class, 'success'])->name('checkout.success');

// Order Lookup
Route::get('/tra-cuu-don-hang', [OrderLookupController::class, 'index'])->name('order-lookup.index');
Route::post('/tra-cuu-don-hang', [OrderLookupController::class, 'lookup'])->name('order-lookup.lookup');

// Guest Reviews
Route::post('/danh-gia', [GuestReviewController::class, 'store'])->name('reviews.store');

// Warranty Lookup
Route::get('/tra-cuu-bao-hanh', [WarrantyLookupController::class, 'index'])->name('warranty-lookup.index');
Route::post('/tra-cuu-bao-hanh', [WarrantyLookupController::class, 'lookup'])->name('warranty-lookup.lookup');

// News
Route::get('/tin-tuc', [NewsController::class, 'index'])->name('news.index');
Route::get('/tin-tuc/{slug}', [NewsController::class, 'show'])->name('news.show');

// About Us
Route::get('/gioi-thieu', [PageController::class, 'about'])->name('about');

// ─── Auth Routes ─────────────────────────────────────────────────

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ─── Admin Routes ────────────────────────────────────────────
    Route::prefix('admin')->name('admin.')->group(function () {
        // Media
        Route::get('media', [MediaLibraryController::class, 'index'])->name('media.index');
        Route::post('media', [MediaLibraryController::class, 'store'])->name('media.store');
        Route::delete('media/{id}', [MediaLibraryController::class, 'destroy'])->name('media.destroy');

        // Menus
        Route::resource('menus', MenuController::class);
        Route::post('menus/{menu}/items', [MenuController::class, 'saveItems'])->name('menus.items');

        // Products
        Route::resource('products', ProductController::class);
        Route::post('products/{product}/images', [ProductController::class, 'uploadImages'])->name('products.images.upload');
        Route::post('products/{product}/images/from-media', [ProductController::class, 'addImageFromMedia'])->name('products.images.from-media');
        Route::delete('products/{product}/images/{image}', [ProductController::class, 'deleteImage'])->name('products.images.delete');
        Route::post('products/{product}/images/reorder', [ProductController::class, 'reorderImages'])->name('products.images.reorder');

        // Orders
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

        // Reviews
        Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::patch('reviews/{review}/status', [ReviewController::class, 'updateStatus'])->name('reviews.updateStatus');
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

        // Warranties
        Route::resource('warranties', WarrantyController::class)->except(['show']);

        // Post Categories
        Route::resource('post-categories', PostCategoryController::class)->except(['show']);

        // Posts
        Route::resource('posts', PostController::class);

        // Home Content
        Route::get('home-content', [HomeContentController::class, 'index'])->name('home-content.index');
        Route::post('home-content', [HomeContentController::class, 'save'])->name('home-content.save');

        // Settings
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'save'])->name('settings.save');
    });
});

require __DIR__ . '/auth.php';
