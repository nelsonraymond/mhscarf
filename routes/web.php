<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WishlistController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleAuthController;

Route::get('/', HomeController::class)->name('home');

// Public Catalog Routes
Route::get('/shop', [CatalogController::class, 'index'])->name('shop.index');
// Canonical product detail URL — declared before the category wildcard so
// /shop/produk/{slug} never falls through to /shop/{category:slug}.
Route::get('/shop/produk/{product:slug}', [CatalogController::class, 'show'])->name('shop.product');
Route::get('/shop/{category:slug}', [CatalogController::class, 'index'])->name('shop.category');
// Legacy product URLs redirect to the canonical /shop/produk/ location.
Route::get('/product/{product:slug}', fn (Product $product) => redirect()->route('shop.product', $product, 301))
    ->name('shop.product.legacy');

// Customer Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'create'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'store'])->name('login.submit')->middleware('throttle:5,1');
    Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register'])->name('register.submit')->middleware('throttle:5,1');

    // Forgot Password & Reset Password
    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

// Customer Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [CustomerAuthController::class, 'destroy'])->name('logout');
    // Customer Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Customer Address Management
    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::get('/addresses/create', [AddressController::class, 'create'])->name('addresses.create');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::get('/addresses/{address}/edit', [AddressController::class, 'edit'])->name('addresses.edit');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::post('/addresses/{address}/default', [AddressController::class, 'setDefault'])->name('addresses.default');

    // Checkout Routes
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // Customer Order History & Detail
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    // Customer Payment (ownership enforced in controller/service)
    Route::get('/orders/{order}/payment', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('/orders/{order}/payment/status', [PaymentController::class, 'status'])->name('payments.status');
    // Route::post('/orders/{order}/payment/simulate', [PaymentController::class, 'simulate'])->name('payments.simulate');

    // Wishlist Routes (Authenticated Customers)
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
});

// Payment Gateway Webhook (public; CSRF-excluded in bootstrap/app.php; signature verified in service)
Route::post('/webhooks/payment', [PaymentController::class, 'webhook'])->name('payments.webhook');

// Shopping Cart Routes (Guests and Customers)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.items.destroy');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.destroy');
Route::post('buy/now', [CartController::class, 'buyNow'])->name('buy.now');

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'store'])->name('login.submit');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

        // Order Management (state machine enforced by OrderService)
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('/orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');
        Route::patch('/orders/{order}/shipment', [AdminOrderController::class, 'updateShipment'])->name('orders.shipment');

        // Category Management
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Product Management
        Route::resource('products', ProductController::class)->except(['show']);

        // Product Variants
        Route::post('/products/{product}/variants', [ProductVariantController::class, 'store'])->name('products.variants.store');
        Route::put('/products/{product}/variants/{variant}', [ProductVariantController::class, 'update'])->name('products.variants.update');
        Route::delete('/products/{product}/variants/{variant}', [ProductVariantController::class, 'destroy'])->name('products.variants.destroy');
        Route::post('/products/{product}/variants/{variant}/stock', [ProductVariantController::class, 'updateStock'])->name('products.variants.stock');

        // Product Images
        Route::post('/products/{product}/images', [ProductImageController::class, 'store'])->name('products.images.store');
        Route::post('/products/{product}/images/{image}/primary', [ProductImageController::class, 'setPrimary'])->name('products.images.primary');
        Route::delete('/products/{product}/images/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');
    });
});
