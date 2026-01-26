<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth']], function () {
    Route::get('stock-transfer-approve', 'StockTransferApproveController@index')->name('stock.transfer.approve');
    Route::group(['prefix' => 'stock-transfer-approve', 'as'=>'stock.transfer.approve.'], function () {
        Route::post('index', 'StockTransferApproveController@index')->name('index');
        Route::post('datatable-data', 'StockTransferApproveController@getDataTableData')->name('datatable.data');
        Route::get('show/{id}', 'StockTransferApproveController@show')->name('show');
        Route::post('change-status', 'StockTransferApproveController@changeStatus')->name('change.status');
    });
});
