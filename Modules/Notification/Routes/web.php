<?php

use Illuminate\Support\Facades\Route;
use Modules\Notification\Http\Controllers\NotificationController;

Route::group(['middleware' => ['auth']], function () {
    Route::post('send-notification', [NotificationController::class, 'sendNotification']);
});
