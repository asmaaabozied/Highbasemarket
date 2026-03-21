<?php

use App\Http\Controllers\Account\NewsFeedController;
use App\Http\Controllers\Account\Orders\OrderReviewController;
use App\Http\Controllers\Brands\BrandProductsController;
use App\Http\Controllers\DefaultTermController;
use App\Http\Controllers\Storefront\BranchNewsController;
use App\Http\Controllers\Storefront\BrandController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CouponValidationController;
use App\Http\Controllers\Storefront\GlobalController;
use App\Http\Controllers\Storefront\InstantOrderRedirectController;
use App\Http\Controllers\Storefront\LocalStoreController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\ProfileController;
use App\Http\Controllers\Storefront\SavedListController;
use App\Http\Middleware\RefMiddleware;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'storefront', 'as' => 'storefront.', 'middleware' => ['invite', RefMiddleware::class]],
    function () {
        Route::get('cart', CartController::class)->name('cart');
        Route::get('/global', GlobalController::class)->name('global');
        Route::get('/brands', BrandController::class)->name('brands');
        Route::get('products/brand/{brand:slug}', BrandProductsController::class)->name('products.brand');
        Route::get('/profile/{branch:slug}/news', BranchNewsController::class)->name('profile.news');
        Route::get('/profile/{branch:slug}/terms', [DefaultTermController::class, 'index'])->name('profile.terms');
        Route::get('/profile/{branch:slug}', ProfileController::class)
            ->middleware('signature')
            ->name('profile');
        Route::get('/stores/{branch:slug}', LocalStoreController::class)->name('stores');
        Route::get('timeline', [NewsFeedController::class, 'index'])->name('timeline')->middleware('auth');

        // add couponed products route
        Route::get('products/coupons', \App\Http\Controllers\Storefront\CouponsProductsController::class)->name('products.couponed');

        Route::put('review', [OrderReviewController::class, 'put'])->name('review');
        Route::get('review', [OrderReviewController::class, 'index'])->name('review.show');

        Route::resource('/products', ProductController::class)
            ->only(['index', 'show'])
            ->parameter('product', 'product:slug');
        Route::resource('saved-lists', SavedListController::class)->only(['index', 'destroy']);

        Route::get('/instant-order-detail/{branch:slug}', InstantOrderRedirectController::class)->name('instant-order');
        Route::post('validate-coupon', CouponValidationController::class)->name('validate-coupon');
    }
);
