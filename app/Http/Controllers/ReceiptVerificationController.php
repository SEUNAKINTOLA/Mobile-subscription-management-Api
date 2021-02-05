<?php

namespace App\Http\Controllers;

use App\Events\CanceledSubscriptionEvent;
use App\Events\RenewedSubscriptionEvent;
use App\Events\StartedSubscriptionEvent;
use App\Exceptions\RateLimitException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Exceptions\NotFoundException;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

class ReceiptVerificationController extends Controller
{
    /**
     * @param Device $device
     * @param string $receipt
     * @return JsonResponse
     * @throws NotFoundException|RateLimitException
     */
    public static function verify(Device $device, string $receipt)
    { 
        
        if($device->os =='android') $request = Request::create('api/v1/verifygooglereceipt', 'POST', ['receipt' => $receipt]);
        else $request = Request::create('api/v1/verifyiosreceipt', 'POST', ['receipt' => $receipt]);
        $response = Route::dispatch($request);
        $result = json_encode($response);

        return $result;
    }

}
