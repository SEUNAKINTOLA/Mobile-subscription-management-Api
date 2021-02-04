<?php

namespace App\Http\Controllers;

use App\Exceptions\DeviceExistsException;
use App\Exceptions\ValidationException;
use App\Exceptions\NotFoundException;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\Device;
use Throwable;
use Log;
use DB;

class APIController extends BaseController
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
            $this->validateRequest($request, $this->registerRules());

            $uId = $request->get(Device::COLUMN_U_ID);
            $appId = $request->get(Device::COLUMN_APP_ID);
            $lang = $request->get(Device::COLUMN_LANG);
            $os = $request->get(Device::COLUMN_OS);

            DB::beginTransaction();

            $device = Device::addNewDevice($uId, $appId, $lang, $os);

            DB::commit();

            return $this->jsonResponse(
                [
                    'message' => 'the device successfully has been added to the DB',
                    Device::COLUMN_CLIENT_TOKEN => $device->getClientToken(),
                ]
            );

        } catch (ValidationException $exception) {

            return $this->jsonResponse(
                [
                    'message' => $exception->getMessage(),
                    'errors' => $exception->getValidateExceptions()
                ],
                self::HTTP_RESPONSE_NOT_ACCEPTABLE
            );

        } catch (DeviceExistsException $exception) {

            return $this->jsonResponse(
                [
                    'message' => $exception->getMessage()
                ],
                self::HTTP_RESPONSE_CONFLICT
            );


        } catch (Throwable $exception) {

            Log::error($exception);
            return $this->jsonResponse(
                [
                    'message' => 'something bad has been occurred!'
                ],
                self::HTTP_RESPONSE_ERROR
            );

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
     *          description="Successful operation"
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
            $this->validateRequest($request, $this->purchaseRules());

            $clientToken = $request->get(Subscription::COLUMN_CLIENT_TOKEN);
            $receipt = $request->get(Subscription::COLUMN_RECEIPT);

            $device = Device::getByClientToken($clientToken);

            DB::beginTransaction();
            $subscription = Subscription::verify($device, $receipt);
            DB::commit();

            if ($subscription !== null) {
                $expiration = $subscription->getExpireAt();
                $message = "subscription for $clientToken successfully registered till $expiration";

            } else {
                $message = "subscription for $clientToken was denied by the provider!";

            }

            return $this->jsonResponse(
                [
                    'result' => $subscription!==null ? 'Success' : 'Failed',
                    'message' => $message
                ]
            );

        } catch (NotFoundException $exception) {

            return $this->jsonResponse(
                [
                    'message' => $exception->getMessage(),
                ],
                self::HTTP_RESPONSE_NOT_FOUND
            );

        } catch (ValidationException $exception) {

            return $this->jsonResponse(
                [
                    'message' => $exception->getMessage(),
                    'errors' => $exception->getValidateExceptions()
                ],
                self::HTTP_RESPONSE_NOT_ACCEPTABLE
            );

        } catch (Throwable $exception) {

            Log::error($exception);
            return $this->jsonResponse(
                [
                    'message' => 'Something bad has been occurred!'
                ],
                self::HTTP_RESPONSE_ERROR
            );

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
     *          in="path",
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
            $this->validateRequest($request, $this->checkRules());

            $clientToken = $request->get(Subscription::COLUMN_CLIENT_TOKEN);
            $subscription = Subscription::getByClientToken($clientToken);

            return $this->jsonResponse(
                [
                    'status' => $subscription->getStatus(),
                ]
            );

        } catch (NotFoundException $exception) {

            return $this->jsonResponse(
                [
                    'message' => $exception->getMessage(),
                ],
                self::HTTP_RESPONSE_NOT_FOUND
            );

        } catch (ValidationException $exception) {

            return $this->jsonResponse(
                [
                    'message' => $exception->getMessage(),
                    'errors' => $exception->getValidateExceptions()
                ],
                self::HTTP_RESPONSE_NOT_ACCEPTABLE
            );

        } catch (Throwable $exception) {

            Log::error($exception);
            return $this->jsonResponse(
                [
                    'message' => 'Something bad has been occurred!'
                ],
                self::HTTP_RESPONSE_ERROR
            );

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
     * @return JsonResponse
     */
    public function report()
    {
        $data = ReportService::get();

        return $this->jsonResponse(
            [
                'data' => $data,
            ]
        );
    }
}