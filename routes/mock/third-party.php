<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MockService\ThirdPartyController;

Route::prefix('third-party')->group(function () {

    Route::post('status/change', [ThirdPartyController::class, 'changeStatus'])
        ->name('api.mock.third-party.status.change');

});
