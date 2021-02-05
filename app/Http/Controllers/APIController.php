<?php

namespace App\Http\Controllers;

use App\Exceptions\DeviceExistsException;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use App\Models\Subscription;
use App\Virtual\Models\DeviceRegistrationResponse;
use Illuminate\Http\Request;
use App\Models\Device;
use Throwable;
use Log;
use DB;

use Symfony\Component\HttpFoundation\Response;
use Validator;
use Carbon\Carbon;
class APIController
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */

    /**
     * @OA\Post(
     *      path="/register",
     *      operationId="register",
     *      tags={"register"},
     *      summary="Register new device",
     *      description="When a mobile device starts up first, it must register at the API, and device info such as uID, appID,language and operating system (OS) must be saved to the device table at registration.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/DeviceRegistrationResponse")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="something bad has been occurred!"
     *      )
     * )
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->registerRules());
            if ($validator->passes()) { 
                $uId = $request->get(Device::COLUMN_U_ID);
                $appId = $request->get(Device::COLUMN_APP_ID);
                $lang = $request->get(Device::COLUMN_LANG);
                $os = $request->get(Device::COLUMN_OS);

                DB::beginTransaction();

                $device = Device::addNewDevice($uId, $appId, $lang, $os);
                $message = 'Device successfully registered';
                DB::commit();
                return response()->json(['success', $message, $device->getClientToken()], Response::HTTP_OK) ;
            }else{
                $message = "Invalid input";
                return response()->json(['failed', $message, null], Response::HTTP_NOT_ACCEPTABLE) ;
            }

        } catch (DeviceExistsException $exception) {
            
            $message = $exception->getMessage();
            return response()->json(['failed', $message, null], Response::HTTP_CONFLICT);
        } catch (Throwable $exception) {

            Log::error($exception);
            $message = 'server error';
            return response()->json(['failed', $message, null], Response::HTTP_INTERNAL_SERVER_ERROR);
        } finally {

            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

        }

    }

    /**
     * @return array
     */
    private function registerRules(): array
    {
        return [
            Device::COLUMN_U_ID => [
                'required',
                'integer',
                'min:1',
            ],
            Device::COLUMN_APP_ID => [
                'required',
                'integer',
                'min:1',
            ],
            Device::COLUMN_LANG => [
                'required',
                'string',
                'min:2',
            ],
            Device::COLUMN_OS => [
                'required',
                Rule::in(Device::OSES),
            ]
        ];
    }


    /**
     * @OA\Post(
     *      path="/purchase",
     *      operationId="purchase",
     *      tags={"purchase"},
     *      summary="Purchase new subscription",
     *      description="It is the purchase request made in the mobile app. Mobile client sends client-token and receipt (it can be a random, meaningless hash) parameters to this API.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/PurchaseRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/PurchaseResponse")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="something bad has been occurred!"
     *      )
     * )
     */

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function purchase(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->purchaseRules());
            
            if ($validator->passes()) { 
                $clientToken = $request->get(Subscription::COLUMN_CLIENT_TOKEN);
                $receipt = $request->get(Subscription::COLUMN_RECEIPT);
    
                $device = Device::getByClientToken($clientToken);
                
                 DB::beginTransaction();
                $receiptverification = ReceiptVerificationController::verify($device, $receipt);
                $receiptverification  = json_decode($receiptverification, true)['original'];
                
                $expire_date = $receiptverification["expire_date"];

                if ($receiptverification["status"] == "valid") {
                    
                    $subscription = Subscription::registerSubscription($device->getClientToken(), $receipt, Carbon::parse($expire_date));

                    DB::commit();
                    $message = "Successful purchase";
                }else{
                    return $receiptverification;
                    $message = "Failed purchase";
                }
                
                $status = $receiptverification["status"]=='valid' ? 'Success' : 'Failed';
                return response()->json([$status, $message, $expire_date], Response::HTTP_OK);
            }else{

                $message = "Invalid input";
                return response()->json(['Failed', $message, null], Response::HTTP_NOT_ACCEPTABLE);
            }

        } catch (NotFoundException $exception) {
            $message = $exception->getMessage();
            return response()->json(['Failed', $message, null], Response::HTTP_NOT_FOUND);

        } catch (Throwable $exception) {

            Log::error($exception);
            $message = 'Server error';
            return response()->json(['failed', $message, null], Response::HTTP_INTERNAL_SERVER_ERROR);

        } finally {

            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

        }
    }

    /**
     * @return array
     */
    private function purchaseRules(): array
    {
        return [
            Subscription::COLUMN_CLIENT_TOKEN => [
                'required',
                'uuid',
                'exists:devices,client_token',
            ],
            Subscription::COLUMN_RECEIPT => [
                'required',
                'string',
                'min:25',
                'max:255',
            ]
        ];
    }
    /**
     * @OA\GET(
     *      path="/check",
     *      operationId="check",
     *      tags={"check"},
     *      summary="Check Subscription",
     *      description="The mobile client can call this endpoint whenever it is on or at any step it deems necessary. It should return current subscription status as the response to your request, only with the client-token parameter.",
     *      @OA\Parameter(
     *          name="client_token",
     *          description="Unique Client Token",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/DeviceRegistrationResponse")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="something bad has been occurred!"
     *      )
     * )
     */
    public function check(Request $request)
    {
        try {
            
            $validator = Validator::make($request->all(), $this->checkRules());
            
            if ($validator->passes()) { 
                $clientToken = $request->get(Subscription::COLUMN_CLIENT_TOKEN);
                $subscription = Subscription::getByClientToken($clientToken);
    
                return response()->json(['success', $subscription->getStatus()], Response::HTTP_OK);
            }else{
                return response()->json(['failed', "Invalid input"], Response::HTTP_NOT_ACCEPTABLE);
            }

        } catch (NotFoundException $exception) {

            return response()->json(['failed', $exception->getMessage()], Response::HTTP_NOT_FOUND);

        } catch (Throwable $exception) {

            $message = 'Server error';
            Log::error($exception);
            return response()->json(['failed', $message], Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    /**
     * @return array
     */
    private function checkRules(): array
    {
        return [
            Subscription::COLUMN_CLIENT_TOKEN => [
                'required',
                'uuid',
                'exists:devices,client_token',
            ],
        ];
    }


    /**
     * @OA\GET(
     *      path="/report",
     *      operationId="report",
     *      tags={"report"},
     *      summary="Generate Report",
     *      description="Reports showing new, expired and renewed subscriptions on the basis of app, day and OS.",
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/DeviceRegistrationResponse")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="something bad has been occurred!"
     *      )
     * )
     */

    /**
     * @return JsonResponse
     */
    public function report()
    {
        $data = ReportService::get();
        return response()->json(['success', $data], Response::HTTP_OK);
    }
}