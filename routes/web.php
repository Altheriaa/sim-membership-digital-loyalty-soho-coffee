<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoyaltySettingController;
use App\Http\Controllers\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\Admin\MenuCategoryController;
use App\Http\Controllers\Admin\MenuItemAvailabilityController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\TierController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\HistoryController as CustomerHistoryController;
use App\Http\Controllers\Customer\PinController as CustomerPinController;
use App\Http\Controllers\Customer\RewardController as CustomerRewardController;
use App\Http\Controllers\Customer\VoucherWalletController as CustomerVoucherWalletController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Pos\MemberController;
use App\Http\Controllers\Pos\OrderController;
use App\Http\Controllers\Pos\VoucherController as PosVoucherController;
use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;

Route::prefix('app')->name('customer.')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
    Route::get('/check-phone', [CustomerAuthController::class, 'checkPhone'])->name('check-phone');
    Route::post('/login', [CustomerAuthController::class, 'sendOtp'])->name('send-otp');
    Route::get('/otp', [CustomerAuthController::class, 'showOtp'])->name('otp');
    Route::post('/otp', [CustomerAuthController::class, 'verifyOtp'])->name('verify-otp');

    Route::middleware('auth:customer')->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
        Route::get('/', CustomerDashboardController::class)->name('dashboard');

        Route::get('/rewards', [CustomerRewardController::class, 'index'])->name('rewards.index');
        Route::post('/rewards/{voucher}/claim', [CustomerRewardController::class, 'claim'])->name('rewards.claim');

        Route::get('/vouchers', [CustomerVoucherWalletController::class, 'index'])->name('vouchers.index');
        Route::get('/vouchers/{voucher}', [CustomerVoucherWalletController::class, 'show'])->name('vouchers.show');

        Route::get('/history', [CustomerHistoryController::class, 'index'])->name('history.index');
        Route::get('/receipts/{order}', [CustomerHistoryController::class, 'receipt'])->name('receipt');

        Route::get('/pin', [CustomerPinController::class, 'show'])->name('pin.show');
        Route::post('/pin', [CustomerPinController::class, 'update'])->name('pin.update');
        Route::post('/pin/reset', [CustomerPinController::class, 'requestReset'])->name('pin.reset.request');
        Route::get('/pin/reset', [CustomerPinController::class, 'showResetForm'])->name('pin.reset.form');
        Route::post('/pin/reset/confirm', [CustomerPinController::class, 'processReset'])->name('pin.reset.confirm');
    });
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store']);
});

Route::post('/logout', [SessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', HomeController::class)->name('home');

    Route::middleware('role:cashier,admin,owner')->get('pos/orders/{order}', [OrderController::class, 'show'])->name('pos.orders.show');

    Route::middleware('role:cashier,admin')->prefix('pos')->name('pos.')->group(function () {
        Route::get('/', PosController::class)->name('index');
        Route::get('members/lookup', [MemberController::class, 'lookup'])->name('members.lookup');
        Route::post('members', [MemberController::class, 'store'])->name('members.store');
        Route::get('vouchers/lookup', [PosVoucherController::class, 'lookup'])->name('vouchers.lookup');
        Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', DashboardController::class)->middleware('role:owner,admin')->name('dashboard');

        Route::get('reports', [ReportController::class, 'index'])->middleware('role:cashier,owner,admin')->name('reports.index');
        Route::get('reports/print', [ReportController::class, 'print'])->middleware('role:cashier,owner,admin')->name('reports.print');

        Route::middleware('role:admin')->group(function () {
            Route::resource('members', AdminMemberController::class)->except(['create', 'destroy']);
            Route::post('members/{member}/adjust-points', [AdminMemberController::class, 'adjustPoints'])->name('members.adjust-points');
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
