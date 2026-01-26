<?php

use Illuminate\Support\Facades\Route;
use Modules\Setting\Http\Controllers\WarehouseController;
use Modules\Setting\Http\Controllers\DeliveryChargeController;
use Modules\Setting\Http\Controllers\SearchTextController;

Route::group(['middleware' => ['auth']], function () {
    Route::get('showroom', [WarehouseController::class, 'index'])->name('showroom');

    // Showroom Routes
    Route::group(['prefix' => 'showroom', 'as' => 'showroom.'], function () {
        Route::post('datatable-data', [WarehouseController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [WarehouseController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [WarehouseController::class, 'edit'])->name('edit');
        Route::post('delete', [WarehouseController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [WarehouseController::class, 'bulk_delete'])->name('bulk.delete');
        Route::post('change-status', [WarehouseController::class, 'change_status'])->name('change.status');
    });

    // Delivery Charge Routes
    Route::get('delivery-charge', [DeliveryChargeController::class, 'index'])->name('delivery-charge');
    Route::group(['prefix' => 'delivery-charge', 'as' => 'delivery-charge.'], function () {
        Route::post('datatable-data', [DeliveryChargeController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [DeliveryChargeController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [DeliveryChargeController::class, 'edit'])->name('edit');
        Route::post('delete', [DeliveryChargeController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [DeliveryChargeController::class, 'bulk_delete'])->name('bulk.delete');
        Route::post('change-status', [DeliveryChargeController::class, 'change_status'])->name('change.status');
    });

    //Search Text Routes
    Route::get('search-text', [SearchTextController::class, 'index'])->name('search-text');
    Route::group(['prefix' => 'search-text', 'as' => 'search-text.'], function () {
        Route::post('datatable-data', [SearchTextController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [SearchTextController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [SearchTextController::class, 'edit'])->name('edit');
    });
});
