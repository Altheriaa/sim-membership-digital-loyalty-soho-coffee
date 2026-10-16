<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoyaltySettingController;
use App\Http\Controllers\Admin\MenuCategoryController;
use App\Http\Controllers\Admin\MenuItemAvailabilityController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\TierController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store']);
});

Route::post('/logout', [SessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', HomeController::class)->name('home');

    Route::get('/pos', PosController::class)->middleware('role:cashier,admin')->name('pos.index');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', DashboardController::class)->middleware('role:owner,admin')->name('dashboard');

        Route::middleware('role:admin')->group(function () {
            Route::resource('staff', StaffController::class)->parameters(['staff' => 'user'])->except(['show', 'destroy']);
            Route::resource('categories', MenuCategoryController::class)->except('show');
            Route::resource('menu-items', MenuItemController::class)->except('show');
            Route::patch('menu-items/{menu_item}/availability', MenuItemAvailabilityController::class)->name('menu-items.availability');
            Route::resource('tiers', TierController::class)->except('show');
            Route::resource('vouchers', VoucherController::class)->except('show');
            Route::get('loyalty', [LoyaltySettingController::class, 'edit'])->name('loyalty.edit');
            Route::put('loyalty', [LoyaltySettingController::class, 'update'])->name('loyalty.update');
        });
    });
});
