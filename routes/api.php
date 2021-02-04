<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;

Route::prefix('mock')->group(function () {
    include'mock/apple.php';
    include'mock/google.php';
    include'mock/third-party.php';
});

Route::prefix('v1')->group(function ()  {

    Route::post('register', [APIController::class, 'register'])
        ->name('api.register');
    Route::post('purchase', [APIController::class, 'purchase'])
        ->name('api.purchase');
    Route::get('check', [APIController::class, 'check'])
        ->name('api.check');

    Route::get('report', [APIController::class, 'report'])
        ->name('api.report');

});
