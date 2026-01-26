<?php

use Illuminate\Support\Facades\Route;
use Modules\MobileBank\Http\Controllers\MobileBankController;
use Modules\MobileBank\Http\Controllers\MobileBankTransactionController;

Route::group(['middleware' => ['auth']], function () {
    // Mobile Bank Routes
    Route::get('mobile-bank', [MobileBankController::class, 'index'])->name('mobilebank');
    Route::group(['prefix' => 'mobile-bank', 'as' => 'mobilebank.'], function () {
        Route::get('create', [MobileBankController::class, 'create'])->name('create');
        Route::post('datatable-data', [MobileBankController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [MobileBankController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [MobileBankController::class, 'edit'])->name('edit');
        Route::post('change-status', [MobileBankController::class, 'change_status'])->name('change.status');
        Route::post('delete', [MobileBankController::class, 'delete'])->name('delete');
    });

    // Mobile Bank Transaction Routes
    Route::get('mobile-bank-transaction', [MobileBankTransactionController::class, 'index'])->name('mobilebank.transaction');
    Route::post('store-mobile-bank-transaction', [MobileBankTransactionController::class, 'store'])->name('store.mobilebank.transaction');

    // Mobile Bank Ledger Routes
    Route::get('mobile-bank-ledger', [MobileBankController::class, 'bank_ledger'])->name('mobilebank.ledger');
    Route::post('mobile-bank-ledger-data', [MobileBankController::class, 'bank_ledger_data'])->name('mobilebank.ledger.data');
});
