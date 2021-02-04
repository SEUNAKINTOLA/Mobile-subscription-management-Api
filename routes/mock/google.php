<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MockService\GooglePlayController;

Route::prefix('google')->group(function () {

    Route::post('purchase/verification', [GooglePlayController::class, 'verification'])
        ->name('api.mock.google.purchase.verification');

});