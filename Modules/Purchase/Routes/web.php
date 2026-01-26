<?php

use Illuminate\Support\Facades\Route;
use Modules\Purchase\Http\Controllers\PurchaseController;
use Modules\Purchase\Http\Controllers\PaymentController;

Route::group(['middleware' => ['auth']], function () {
    // Purchase Routes
    Route::get('purchase', [PurchaseController::class, 'index'])->name('purchase');
    Route::group(['prefix' => 'purchase', 'as' => 'purchase.'], function () {
        Route::get('add', [PurchaseController::class, 'create'])->name('add');
        Route::post('datatable-data', [PurchaseController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store', [PurchaseController::class, 'store'])->name('store');
        Route::post('hold', [PurchaseController::class, 'hold'])->name('hold');
        Route::post('update', [PurchaseController::class, 'update'])->name('update');
        Route::get('edit/{id}', [PurchaseController::class, 'edit'])->name('edit');
        Route::get('view/{id}', [PurchaseController::class, 'show'])->name('view');
        Route::post('delete', [PurchaseController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [PurchaseController::class, 'bulk_delete'])->name('bulk.delete');
        Route::get('change-status', [PurchaseController::class, 'changeStatus'])->name('change.status');
        Route::post('show-invoice', [PurchaseController::class, 'show_invoice'])->name('show.invoice');
        Route::post('log', [PurchaseController::class, 'purchaseLog'])->name('log');

        Route::post('product-autocomplete-search', [PurchaseController::class, 'autocomplete_search_product']);
        Route::post('product-search', [PurchaseController::class, 'search_product'])->name('product.search');
    });

    // Purchase Payment Routes
    Route::post('purchase-payment-store-or-update', [PaymentController::class, 'store_or_update'])->name('purchase.payment.store.or.update');
    Route::post('purchase-payment/view', [PaymentController::class, 'show'])->name('purchase.payment.show');
    Route::post('purchase-payment/edit', [PaymentController::class, 'edit'])->name('purchase.payment.edit');
    Route::post('purchase-payment/delete', [PaymentController::class, 'delete'])->name('purchase.payment.delete');

    Route::get('draft', [\Modules\Purchase\Http\Controllers\DraftController::class, 'index'])->name('draft');
    Route::group(['prefix' => 'draft', 'as' => 'draft.'], function () {
        Route::post('datatable-data', [\Modules\Purchase\Http\Controllers\DraftController::class, 'getDataTableData'])->name('datatable.data');
        Route::post('store-Or-Update', [\Modules\Purchase\Http\Controllers\DraftController::class, 'storeOrUpdate'])->name('store.or.update');
        Route::post('edit', [\Modules\Purchase\Http\Controllers\DraftController::class, 'edit'])->name('edit');
        Route::post('delete', [\Modules\Purchase\Http\Controllers\DraftController::class, 'delete'])->name('delete');
        Route::post('change-status', [\Modules\Purchase\Http\Controllers\DraftController::class, 'changeStatus'])->name('change.status');
        Route::get('purchase/{id}', [\Modules\Purchase\Http\Controllers\DraftController::class, 'createPurchase'])->name('create.purchase');
    });
});
