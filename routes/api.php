<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;
use App\Http\Controllers\OSMockController;

Route::prefix('v1')->group(function ()  {

    Route::post('register', [APIController::class, 'register'])
        ->name('api.register');
    Route::post('purchase', [APIController::class, 'purchase'])
        ->name('api.purchase');
    Route::get('check', [APIController::class, 'check'])
        ->name('api.check');
    Route::post('verifygooglereceipt', [OSMockController::class, 'verifygooglereceipt'])
    ->name('api.verifygooglereceipt');
    Route::post('verifyiosreceipt', [OSMockController::class, 'verifyiosreceipt'])
    ->name('api.verifyiosreceipt');

    Route::get('report', [APIController::class, 'report'])
        ->name('api.report');

});
