<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\AdjustmentController;
use Modules\Product\Http\Controllers\AttributeController;
use Modules\Product\Http\Controllers\BarcodeController;
use Modules\Product\Http\Controllers\GenericController;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Product\Http\Controllers\StockReportController;

Route::group(['middleware' => ['auth']], function () {
    // Product Routes
    Route::get('product', [ProductController::class, 'index'])->name('product');
    Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
        Route::get('create', [ProductController::class, 'create'])->name('add');
        Route::post('datatable-data', [ProductController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store', [ProductController::class, 'store'])->name('store');
        Route::post('update', [ProductController::class, 'update'])->name('update');
        Route::post('update-unit-details', [ProductController::class, 'update_unit_data'])->name('unit.details.update');
        Route::post('update-bulk-category', [ProductController::class, 'update_bulk_category'])->name('update.bulk.category');
        Route::get('edit/{id}', [ProductController::class, 'edit'])->name('edit');
        Route::get('view/{id}', [ProductController::class, 'show']);
        Route::post('delete', [ProductController::class, 'delete'])->name('delete');
        Route::post('image/show', [ProductController::class, 'showImage'])->name('image.show');
        Route::post('bulk-delete', [ProductController::class, 'bulk_delete'])->name('bulk.delete');
        Route::post('bulk-status-change', [ProductController::class, 'bulkStatusChange'])->name('bulk.status.change');
        Route::post('change-statuss', [ProductController::class, 'change_statuss'])->name('change.statuss');
        Route::get('generate-code', [ProductController::class, 'generateProductCode'])->name('generate.code');
        Route::post('log', [ProductController::class, 'productLog'])->name('log');
    });

    Route::post('barcode/product-autocomplete-search', [BarcodeController::class, 'autocomplete_search_product']);
    Route::get('print-barcode', [BarcodeController::class, 'index'])->name('print.barcode');
    Route::post('generate-barcode', [BarcodeController::class, 'generateBarcode'])->name('generate.barcode');

    Route::post('generate-product-variant', [ProductController::class, 'generate_product_variant']);
    Route::get('product-variant-generate-code', [ProductController::class, 'product_variant_generate_code'])->name('product.variant.generate.code');

    // Adjustment Routes
    Route::get('adjustment', [AdjustmentController::class, 'index'])->name('adjustment');
    Route::group(['prefix' => 'adjustment', 'as' => 'adjustment.'], function () {
        Route::get('create', [AdjustmentController::class, 'create'])->name('add');
        Route::post('datatable-data', [AdjustmentController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store', [AdjustmentController::class, 'store'])->name('store');
        Route::post('update', [AdjustmentController::class, 'update'])->name('update');
        Route::get('edit/{id}', [AdjustmentController::class, 'edit'])->name('edit');
        Route::get('view/{id}', [AdjustmentController::class, 'show'])->name('view');
        Route::post('delete', [AdjustmentController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [AdjustmentController::class, 'bulk_delete'])->name('bulk.delete');

        Route::post('log', [AdjustmentController::class, 'adjustmentLog'])->name('log');
    });

    // Generic Routes
    Route::get('generic', [GenericController::class, 'index'])->name('generic');
    Route::group(['prefix' => 'generic', 'as' => 'generic.'], function () {
        Route::get('generic-create', [GenericController::class, 'genericCreate'])->name('genericCreate');
        Route::post('datatable-data', [GenericController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [GenericController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('update', [GenericController::class, 'update_data'])->name('update');
        Route::post('details-update', [GenericController::class, 'details_update_data'])->name('details.update');
        Route::get('edit/{id}', [GenericController::class, 'edit'])->name('edit');
        Route::post('delete', [GenericController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [GenericController::class, 'bulk_delete'])->name('bulk.delete');
        Route::post('change-status', [GenericController::class, 'change_status'])->name('change.status');
    });

    // Attribute Routes
    Route::get('attribute', [AttributeController::class, 'index'])->name('attribute');
    Route::group(['prefix' => 'attribute', 'as' => 'attribute.'], function () {
        Route::post('datatable-data', [AttributeController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [AttributeController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [AttributeController::class, 'edit'])->name('edit');
        Route::post('delete', [AttributeController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [AttributeController::class, 'bulk_delete'])->name('bulk.delete');
        Route::post('change-status', [AttributeController::class, 'change_status'])->name('change.status');
    });

    // Depo Wise Sales Report Routes
    Route::get('stock-report', [StockReportController::class, 'index'])->name('stock.report');
    Route::post('stock-report-data', [StockReportController::class, 'report_data'])->name('stock.report.data');
});
