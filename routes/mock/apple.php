<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MockService\AppleStoreController;

Route::prefix('apple')->group(function () {

    Route::post('purchase/verification', [AppleStoreController::class, 'verification'])
        ->name('api.mock.apple.purchase.verification');

});
