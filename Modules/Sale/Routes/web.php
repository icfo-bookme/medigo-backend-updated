<?php

use Illuminate\Support\Facades\Route;
use Modules\Sale\Http\Controllers\DeliveryManAssignController;
use Modules\Sale\Http\Controllers\OrderController;
use Modules\Sale\Http\Controllers\POSController;
use Modules\Sale\Http\Controllers\PrescriptionOrderController;
use Modules\Sale\Http\Controllers\SaleController;
use Modules\Sale\Http\Controllers\SaleNotificationController;
use Modules\Sale\Http\Controllers\OrderWithCallController;
use Modules\Sale\Http\Controllers\ProductController;

Route::group(['middleware' => ['auth']], function () {
    //Sale Routes
    Route::get('sale', [SaleController::class, 'index'])->name('sale');
    Route::get('pos', [POSController::class, 'index'])->name('pos');
    Route::get('manage-delivery-man-order', [DeliveryManAssignController::class, 'assignDeliveryManProduct']);
    Route::post('pos-product-list', [POSController::class, 'products']);

    Route::group(['prefix' => 'pos', 'as' => 'pos.'], function () {
        Route::post('datatable-data', [POSController::class, 'get_datatable_data'])->name('datatable.data');
        Route::get('add', [POSController::class, 'create'])->name('add');
        Route::post('store', [POSController::class, 'store'])->name('store');
        Route::post('customer-store', [POSController::class, 'customer_store'])->name('customer.store');
        Route::get('pos-invoice/{id}', [POSController::class, 'pos_invoice']);
        Route::get('edit/{id}', [POSController::class, 'edit'])->name('edit');
        Route::post('update', [POSController::class, 'update'])->name('update');
        Route::post('delete', [POSController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [POSController::class, 'bulk_delete'])->name('bulk.delete');
        // Sale Product Search Routes (commented out as per request)
         Route::post('product-autocomplete-search', [ProductController::class, 'product_autocomplete_search']);
         Route::post('product-search', [ProductController::class, 'product_search'])->name('product.search');
    });

    Route::group(['prefix' => 'sale', 'as' => 'sale.'], function () {
        Route::post('datatable-data', [SaleController::class, 'get_datatable_data'])->name('datatable.data');
        Route::get('add', [POSController::class, 'create'])->name('add');
        Route::post('store', [POSController::class, 'store'])->name('store');
        Route::post('hold', [POSController::class, 'hold'])->name('hold');
        Route::post('assign-delivery-man', [DeliveryManAssignController::class, 'assignDeliveryMan'])->name('assignDeliveryMan');
        Route::post('sale-assign', [SaleController::class, 'Saleassign'])->name('assign');
        Route::post('sale-order-status', [SaleController::class, 'saleOrderStatus'])->name('order.status');
        Route::post('delivery-datatable-data', [DeliveryManAssignController::class, 'get_delivery_datatable_data'])->name('delivery.datatable.data');
        Route::get('pos-invoice/{id}', [POSController::class, 'pos_invoice']);
        Route::post('show-invoice', [SaleController::class, 'show_invoice'])->name('show.invoice');
        Route::get('edit/{id}', [SaleController::class, 'edit'])->name('edit');
        Route::post('update', [SaleController::class, 'update'])->name('update');
        Route::post('delete', [SaleController::class, 'delete'])->name('delete');
        Route::post('delete-sale', [SaleController::class, 'delete_sale'])->name('delete.sale');
        Route::post('bulk-delete', [SaleController::class, 'bulk_delete'])->name('bulk.delete');
        Route::post('log', [SaleController::class, 'saleLog'])->name('log');

        Route::get('sale_notification_list', [SaleNotificationController::class, 'sale_notification_list'])->name('sale_notification_list');
        Route::post('notification/datatable-data', [SaleNotificationController::class, 'get_datatable_data'])->name('notification.datatable.data');
        Route::post('sale_notification/update-list', [SaleNotificationController::class, 'sale_notification_update'])->name('sale_notification_update');

        Route::post('product-autocomplete-search', [ProductController::class, 'product_autocomplete_search']);
        Route::post('product-search', [ProductController::class, 'product_search'])->name('product.search');
    });

    Route::get('customer-point/{id}', [POSController::class, 'customerPoint'])->name('customer.point');
    Route::post('pos-product-varient-list', [POSController::class, 'products_varient']);
    Route::get('order', [OrderController::class, 'orderIndex'])->name('order');

    Route::group(['prefix' => 'order', 'as' => 'order.'], function () {
        Route::post('datatable-data', [OrderController::class, 'get_order_datatable_data'])->name('order.datatable.data');
        Route::get('pos-invoice/{id}', [OrderController::class, 'order_pos_invoice']);
        Route::post('delete', [OrderController::class, 'order_delete'])->name('order.delete');
        Route::post('order-status', [OrderController::class, 'order_status'])->name('order.status');
        Route::get('return/{id}', [OrderController::class, 'order_return'])->name('order.return');
        Route::post('return-store', [OrderController::class, 'order_returnStore'])->name('order.return.store');
        Route::post('assign', [OrderController::class, 'assign'])->name('assign');
        Route::get('order-pos-invoice/{id}', [OrderController::class, 'order_pos_invoice']);
        Route::post('log', [OrderController::class, 'orderLog'])->name('log');
    });

    Route::group(['prefix' => 'prescription-order', 'as' => 'prescription-order.'], function () {
        Route::get('', [PrescriptionOrderController::class, 'index']);
        Route::get('create', [PrescriptionOrderController::class, 'create'])->name('add');
        Route::post('datatable-data', [PrescriptionOrderController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [PrescriptionOrderController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('update', [PrescriptionOrderController::class, 'update'])->name('update');
        Route::post('edit', [PrescriptionOrderController::class, 'edit'])->name('edit');
        Route::get('view/{id}', [PrescriptionOrderController::class, 'show']);
        Route::post('delete', [PrescriptionOrderController::class, 'delete'])->name('delete');
        Route::post('change-status', [PrescriptionOrderController::class, 'change_status'])->name('change.status');
        Route::get('pos', [PrescriptionOrderController::class, 'pos'])->name('pos');
        Route::post('product-list', [PrescriptionOrderController::class, 'products']);
    });

    Route::group(['prefix' => 'order-with-call', 'as' => 'order-with-call.'], function () {
        Route::get('', [OrderWithCallController::class, 'index']);
        Route::get('create', [OrderWithCallController::class, 'create'])->name('add');
        Route::post('datatable-data', [OrderWithCallController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [OrderWithCallController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [OrderWithCallController::class, 'edit'])->name('edit');
        Route::get('view/{id}', [OrderWithCallController::class, 'show']);
        Route::post('delete', [OrderWithCallController::class, 'delete'])->name('delete');
        Route::post('change-status', [OrderWithCallController::class, 'change_status'])->name('change.status');
    });
});
