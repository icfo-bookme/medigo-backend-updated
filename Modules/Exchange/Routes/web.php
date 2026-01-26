<?php

use Illuminate\Support\Facades\Route;
use Modules\Exchange\Http\Controllers\ExchangeController;

Route::group(['middleware' => ['auth']], function () {
    Route::get('stock-exchange/{id}', [ExchangeController::class, 'stockExchange'])->name('stock.exchange');
    Route::get('get-sale-product-details', [ExchangeController::class, 'saleProductDetails'])->name('sale.product.details');
    Route::get('get-product-details', [ExchangeController::class, 'getProductDetails'])->name('get.product.details');

    Route::get('stock-exchange-list', [ExchangeController::class, 'index'])->name('stock.exchange.list');
    Route::group(['prefix' => 'stock-exchange', 'as' => 'stock.exchange.'], function () {
        Route::post('datatable-data', [ExchangeController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store', [ExchangeController::class, 'store'])->name('store');
        Route::post('change-status', [ExchangeController::class, 'changeStatus'])->name('change.status');
        Route::get('show/{id}', [ExchangeController::class, 'show'])->name('show');
        Route::post('delete', [ExchangeController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [ExchangeController::class, 'bulk_delete'])->name('bulk.delete');

        //Log Routes
        Route::post('log', [ExchangeController::class, 'exchangeLog'])->name('log');

        //Stock Exchange Receive
        Route::get('receive/{id}', [ExchangeController::class, 'exchangeReceive'])->name('receive');
        Route::post('receive-store', [ExchangeController::class, 'exchangeReceiveStore'])->name('receive.store');
    });
});
