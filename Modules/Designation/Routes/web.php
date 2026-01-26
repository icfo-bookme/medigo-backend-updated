<?php

Route::group(['middleware' => ['auth']], function () {
    Route::get('manage-designation', 'DesignationController@index')->name('designation');
    Route::group(['prefix' => 'designation', 'as' => 'designation.'], function () {
        Route::post('datatable-data', 'DesignationController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'DesignationController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'DesignationController@edit')->name('edit');
        Route::post('delete', 'DesignationController@delete')->name('delete');
        Route::post('bulk-delete', 'DesignationController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'DesignationController@change_status')->name('change.status');
    });
});
