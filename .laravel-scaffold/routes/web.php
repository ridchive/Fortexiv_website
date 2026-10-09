<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\SellerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MarketplaceController::class, 'home'])->name('home');
Route::get('/products/{product:slug}', [MarketplaceController::class, 'product'])->name('products.show');
Route::get('/projects/{project}', [MarketplaceController::class, 'project'])->name('projects.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/account', [MarketplaceController::class, 'account'])->middleware('auth')->name('account');

Route::middleware(['auth', 'role:buyer'])->group(function () {
    Route::get('/cart', [MarketplaceController::class, 'cart'])->name('cart.index');
    Route::post('/cart', [MarketplaceController::class, 'addToCart'])->name('cart.store');
    Route::patch('/cart/{cartItem}', [MarketplaceController::class, 'updateCart'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [MarketplaceController::class, 'removeCart'])->name('cart.destroy');
    Route::post('/checkout', [MarketplaceController::class, 'checkout'])->name('checkout');
    Route::get('/orders', [MarketplaceController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order:order_code}', [MarketplaceController::class, 'order'])->name('orders.show');
    Route::post('/orders/{order:order_code}/payment-proof', [MarketplaceController::class, 'uploadPayment'])->name('payments.store');
    Route::get('/orders/{order:order_code}/ticket', [MarketplaceController::class, 'ticket'])->name('tickets.show');
});

Route::prefix('seller')->name('seller.')->middleware(['auth', 'role:seller'])->group(function () {
    Route::get('/', [SellerController::class, 'dashboard'])->name('dashboard');
    Route::get('/products', [SellerController::class, 'products'])->name('products.index');
    Route::get('/products/create', [SellerController::class, 'create'])->name('products.create');
    Route::post('/products', [SellerController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [SellerController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [SellerController::class, 'update'])->name('products.update');
    Route::patch('/products/{product}/publish', [SellerController::class, 'publish'])->name('products.publish');
    Route::delete('/products/{product}', [SellerController::class, 'destroy'])->name('products.destroy');
    Route::get('/orders', [SellerController::class, 'orders'])->name('orders');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
    Route::get('/payments/{payment}/proof', [AdminController::class, 'paymentProof'])->name('payments.proof');
    Route::post('/payments/{payment}/verify', [AdminController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{payment}/reject', [AdminController::class, 'reject'])->name('payments.reject');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::patch('/users/{user}/toggle-active', [AdminController::class, 'toggleUser'])->name('users.toggle');
    Route::get('/pickup', [AdminController::class, 'pickupForm'])->name('pickup');
    Route::post('/pickup', [AdminController::class, 'pickup'])->name('pickup.validate');
    Route::get('/reports/daily', [AdminController::class, 'report'])->name('reports');
});
