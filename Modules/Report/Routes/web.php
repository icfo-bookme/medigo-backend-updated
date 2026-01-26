<?php

use Illuminate\Support\Facades\Route;
use Modules\Report\Http\Controllers\ClosingReportController;
use Modules\Report\Http\Controllers\TodaySalesReportController;
use Modules\Report\Http\Controllers\SalesReportController;
use Modules\Report\Http\Controllers\ProductSalesReportController;
use Modules\Report\Http\Controllers\ProductWiseSalesReportController;
use Modules\Report\Http\Controllers\ProductStockAlertController;
use Modules\Report\Http\Controllers\DeliveryManReportController;
use Modules\Report\Http\Controllers\ProductWisePurchaseReportController;
use Modules\Report\Http\Controllers\ProductExpiryReportController;

Route::group(['middleware' => ['auth']], function () {
    // Closing Route
    Route::get('closing', [ClosingReportController::class, 'index'])->name('closing');
    Route::get('closing/view/{id}', [ClosingReportController::class, 'view'])->name('closing.view');
    Route::post('closing-data', [ClosingReportController::class, 'closing_data'])->name('closing.data');
    Route::post('closing/store', [ClosingReportController::class, 'store'])->name('closing.store');
    Route::post('closing/store-head', [ClosingReportController::class, 'storeHead'])->name('closing.store.head');
    Route::post('closing/delete-head', [ClosingReportController::class, 'deleteHead'])->name('closing.head.delete');

    // Closing Report Route
    Route::get('closing-report', [ClosingReportController::class, 'report'])->name('closing.report');
    Route::post('closing-report/datatable-data', [ClosingReportController::class, 'get_datatable_data'])->name('closing.report.datatable.data');

    // Today Sales Report Route
    Route::get('todays-sales-report', [TodaySalesReportController::class, 'index'])->name('todays.sales.report');
    Route::post('todays-sales-report/datatable-data', [TodaySalesReportController::class, 'get_datatable_data'])->name('todays.sales.report.datatable.data');

    // Sales Report Route
    Route::get('sales-report', [SalesReportController::class, 'index'])->name('sales.report');
    Route::get('facebook-sales-report', [SalesReportController::class, 'facebookSalesReport'])->name('facebook.sales.report');
    Route::get('whatsapp-sales-report', [SalesReportController::class, 'whatsappSalesReport'])->name('whatsapp.sales.report');
    Route::get('call-sales-report', [SalesReportController::class, 'callSalesReport'])->name('call.sales.report');
    Route::post('sales-report/datatable-data', [SalesReportController::class, 'get_datatable_data'])->name('sales.report.datatable.data');

    //Delivery Man Report
    Route::get('deliveryman-report', [DeliveryManReportController::class, 'index'])->name('deliveryman.report');
    Route::post('deliveryman-report/datatable-data', [DeliveryManReportController::class, 'get_datatable_data'])->name('deliveryman.report.datatable.data');

    // Product Sales Report Route
    Route::get('product-sales-report', [ProductSalesReportController::class, 'index'])->name('product.sales.report');
    Route::post('product-sales-report-data', [ProductSalesReportController::class, 'report_data'])->name('product.sales.report.data');

    // Product Wise Sales Report Route
    Route::get('product-wise-sales-report', [ProductWiseSalesReportController::class, 'index'])->name('product.wise.sales.report');
    Route::post('product-wise-sales-report-data', [ProductWiseSalesReportController::class, 'report_data'])->name('product.wise.sales.report.data');

    // Product Wise Purchase Report Route
    Route::get('product-wise-purchase-report', [ProductWisePurchaseReportController::class, 'index'])->name('product.wise.purchase.report');
    Route::post('product-wise-purchase-report-data', [ProductWisePurchaseReportController::class, 'report_data'])->name('product.wise.purchase.report.data');

    //Expiry Date Wise Product Report Route
    Route::get('expiry-date-wise-product-report', [ProductExpiryReportController::class, 'index'])->name('expiry.date.wise.product.report');
    Route::get('expiry-date-wise-product-report-data', [ProductExpiryReportController::class, 'report_data'])->name('expiry.date.wise.product.report.data');

    // Sell Collection Report Route
    Route::get('sell-collection-report', [ProductWiseSalesReportController::class, 'collection_index'])->name('product.sales.collection.report');
    Route::post('sales-collection-report-data', [ProductWiseSalesReportController::class, 'report_collection_data'])->name('product.sales.collection.report.data');

    // Product Stock Alert Report Route
    Route::get('product-stock-alert-report', [ProductStockAlertController::class, 'index'])->name('product.stock.alert.report');
    Route::post('product-stock-alert-report/data', [ProductStockAlertController::class, 'report_data'])->name('product.stock.alert.report.data');
});
