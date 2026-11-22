<?php

use App\Http\Controllers\AdminClassController;
use App\Http\Controllers\AdminContentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminEventController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\SellerController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', [MarketplaceController::class, 'home'])->name('home');
Route::get('/impact', [MarketplaceController::class, 'impact'])->name('impact.index');
Route::view('/preview/mobile', 'preview.mobile')->name('preview.mobile');
Route::view('/preview/laptop', 'preview.laptop')->name('preview.laptop');
Route::get('/projects', [MarketplaceController::class, 'projects'])->name('projects.index');
Route::get('/catalog', [MarketplaceController::class, 'catalog'])->name('catalog.index');
Route::get('/help', [MarketplaceController::class, 'help'])->name('help');
Route::get('/terms', [MarketplaceController::class, 'terms'])->name('terms');
Route::get('/products/{product:slug}', [MarketplaceController::class, 'product'])->name('products.show');
Route::get('/projects/{project}', [MarketplaceController::class, 'project'])->name('projects.show');

// Fallback rute file storage untuk shared hosting jika symlink public/storage tidak tersedia
Route::get('/storage/fallback/{path}', function (string $path) {
    $disk = Storage::disk('public');
    abort_unless($disk->exists($path), 404);

    return response()->file($disk->path($path));
})->where('path', '.*')->name('storage.fallback');

Route::get('/qris-image', [MarketplaceController::class, 'qrisImage'])->name('qris.image');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/forgot-password', [AuthController::class, 'forgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'resetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:6,1')->name('password.update');
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/email/verify', [AuthController::class, 'verificationNotice'])->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');
Route::post('/email/verification-notification', [AuthController::class, 'resendEmailVerification'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');
Route::post('/email/verification-code', [AuthController::class, 'sendEmailVerificationCode'])
    ->middleware(['auth', 'throttle:3,1'])
    ->name('verification.code.send');
Route::post('/email/verify-code', [AuthController::class, 'verifyEmailCode'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.code.verify');
Route::get('/account', [MarketplaceController::class, 'account'])->middleware(['auth', 'verified'])->name('account');

Route::middleware(['auth', 'verified', 'role:buyer'])->group(function () {
    Route::get('/cart', [MarketplaceController::class, 'cart'])->name('cart.index');
    Route::post('/cart', [MarketplaceController::class, 'addToCart'])->name('cart.store');
    Route::patch('/cart/{cartItem}', [MarketplaceController::class, 'updateCart'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [MarketplaceController::class, 'removeCart'])->name('cart.destroy');
    Route::get('/checkout', [MarketplaceController::class, 'checkoutShow'])->name('checkout.show');
    Route::post('/checkout', [MarketplaceController::class, 'checkout'])->name('checkout');
    Route::get('/orders', [MarketplaceController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order:order_code}', [MarketplaceController::class, 'order'])->name('orders.show');
    Route::post('/orders/{order:order_code}/payment-proof', [MarketplaceController::class, 'uploadPayment'])->name('payments.store');
    Route::get('/tickets', [MarketplaceController::class, 'pickupHub'])->name('tickets.hub');
    Route::get('/orders/{order:order_code}/ticket', [MarketplaceController::class, 'ticket'])->name('tickets.show');
});

Route::prefix('seller')->name('seller.')->middleware(['auth', 'verified', 'role:seller'])->group(function () {
    Route::get('/', [SellerController::class, 'dashboard'])->name('dashboard');
    Route::get('/products', [SellerController::class, 'products'])->name('products.index');
    Route::get('/products/status', [SellerController::class, 'productStatus'])->name('products.status');
    Route::get('/products/create', [SellerController::class, 'create'])->name('products.create');
    Route::post('/products', [SellerController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [SellerController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [SellerController::class, 'update'])->name('products.update');
    Route::patch('/products/{product}/submit-review', [SellerController::class, 'submitForReview'])->name('products.submit-review');
    Route::delete('/products/{product}', [SellerController::class, 'destroy'])->name('products.destroy');
    Route::get('/orders', [SellerController::class, 'orders'])->name('orders');
    Route::get('/pickup/scan', [SellerController::class, 'qrScan'])->name('pickup.scan');
    Route::post('/pickup/qr/preview', [SellerController::class, 'previewQr'])->name('pickup.qr.preview');
    Route::post('/pickup/qr/confirm', [SellerController::class, 'confirmQr'])->name('pickup.qr.confirm');
    Route::post('/pickup', [SellerController::class, 'pickup'])->name('pickup');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
    Route::get('/payments/qris', fn () => redirect()->route('admin.payments'))->name('payments.qris');
    Route::post('/payments/qris', [AdminController::class, 'updateQris'])->name('payments.qris.update');
    Route::get('/payments/{payment}/proof', [AdminController::class, 'paymentProof'])->name('payments.proof');
    Route::post('/payments/{payment}/verify', [AdminController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{payment}/reject', [AdminController::class, 'reject'])->name('payments.reject');
    Route::post('/orders/{order}/cancel', [AdminController::class, 'cancelOrder'])->name('orders.cancel');
    Route::post('/order-items/{orderItem}/carbon-impact/adjust', [AdminController::class, 'adjustCarbonImpact'])->name('carbon-impact.adjust');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::patch('/users/{user}/toggle-active', [AdminController::class, 'toggleUser'])->name('users.toggle');
    Route::get('/pickup', [AdminController::class, 'pickupForm'])->name('pickup');
    Route::post('/pickup', [AdminController::class, 'pickup'])->name('pickup.validate');
    Route::get('/reports/daily', [AdminController::class, 'report'])->name('reports');
    Route::get('/event-settings', [AdminEventController::class, 'edit'])->name('event.edit');
    Route::put('/event-settings', [AdminEventController::class, 'update'])->name('event.update');
    Route::get('/classes', [AdminClassController::class, 'index'])->name('classes.index');
    Route::post('/classes', [AdminClassController::class, 'store'])->name('classes.store');
    Route::put('/classes/{project}', [AdminClassController::class, 'update'])->name('classes.update');
    Route::delete('/classes/{project}', [AdminClassController::class, 'destroy'])->name('classes.destroy');
    Route::post('/classes/initialize-defaults', [AdminClassController::class, 'initializeDefaults'])->name('classes.initialize');
    Route::put('/impact', [AdminContentController::class, 'updateImpact'])->name('impact.update');
    Route::get('/articles', [AdminContentController::class, 'articles'])->name('articles.index');
    Route::get('/articles/create', [AdminContentController::class, 'createArticle'])->name('articles.create');
    Route::post('/articles', [AdminContentController::class, 'storeArticle'])->name('articles.store');
    Route::get('/articles/{project}/edit', [AdminContentController::class, 'editArticle'])->name('articles.edit');
    Route::put('/articles/{project}', [AdminContentController::class, 'updateArticle'])->name('articles.update');
    Route::patch('/articles/{project}/archive', [AdminContentController::class, 'archiveArticle'])->name('articles.archive');
    Route::patch('/articles/{project}/publish', [AdminContentController::class, 'publishArticle'])->name('articles.publish');
    Route::delete('/articles/{project}', [AdminContentController::class, 'destroyArticle'])->name('articles.destroy');
    Route::post('/articles/upload-image', [AdminContentController::class, 'uploadArticleImage'])->name('articles.upload-image');
    Route::get('/products', [AdminContentController::class, 'products'])->name('products.index');
    Route::get('/products/{product}/review', [AdminContentController::class, 'reviewProduct'])->name('products.review');
    Route::put('/products/{product}/review', [AdminContentController::class, 'decideProduct'])->name('products.decide');
});
