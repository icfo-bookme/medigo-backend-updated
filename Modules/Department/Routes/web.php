<?php

Route::group(['middleware' => ['auth']], function () {
    //Department Routes
    Route::get('manage-departments', 'DepartmentController@index')->name('department');
    Route::group(['prefix' => 'department', 'as' => 'department.'], function () {
        Route::post('datatable-data', 'DepartmentController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'DepartmentController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'DepartmentController@edit')->name('edit');
        Route::post('delete', 'DepartmentController@delete')->name('delete');
        Route::post('bulk-delete', 'DepartmentController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'DepartmentController@change_status')->name('change.status');
    });
});
