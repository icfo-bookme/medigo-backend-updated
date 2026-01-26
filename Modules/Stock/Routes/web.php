<?php

use Illuminate\Support\Facades\Route;
use Modules\Stock\Http\Controllers\ProductAlertController;
use Modules\Stock\Http\Controllers\ProductStockController;
use Modules\Stock\Http\Controllers\StockTransferController;

Route::group(['middleware' => ['auth']], function () {
    Route::get('stock-transfer', [StockTransferController::class, 'index'])->name('stock.transfer');

    Route::group(['prefix' => 'stock-transfer', 'as' => 'stock.transfer.'], function () {
        Route::post('index', [StockTransferController::class, 'index'])->name('index');
        Route::post('datatable-data', [StockTransferController::class, 'getDataTableData'])->name('datatable.data');
        Route::get('add', [StockTransferController::class, 'create'])->name('add');
        Route::post('store', [StockTransferController::class, 'store'])->name('store');
        Route::get('show/{id}', [StockTransferController::class, 'show'])->name('show');
        Route::get('edit/{id}', [StockTransferController::class, 'edit'])->name('edit');
        Route::post('update', [StockTransferController::class, 'update'])->name('update');
        Route::post('delete', [StockTransferController::class, 'delete'])->name('delete');
        Route::get('warehouse-product/{warehouse_id}', [StockTransferController::class, 'warehouseProduct'])->name('warehouse.product');
    });

    Route::get('product-alert', [ProductAlertController::class, 'index'])->name('product.alert');

    Route::group(['prefix' => 'product-alert', 'as' => 'product.alert.'], function () {
        Route::post('datatable-data', [ProductAlertController::class, 'getDataTableData'])->name('datatable.data');
    });

    Route::get('product-stock', [ProductStockController::class, 'index'])->name('product.stock');

    Route::group(['prefix' => 'stock', 'as' => 'stock.'], function () {
        Route::post('datatable-data', [ProductStockController::class, 'get_product_stock_data'])->name('datatable.data');
    });
});
