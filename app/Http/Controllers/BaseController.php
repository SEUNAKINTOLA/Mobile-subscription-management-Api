<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

class BaseController
{
    const HTTP_RESPONSE_SUCCESS = 200;
    const HTTP_RESPONSE_ERROR = 500;
    const HTTP_RESPONSE_NOT_FOUND = 404;
    const HTTP_RESPONSE_NOT_ACCEPTABLE = 406;
    const HTTP_RESPONSE_CONFLICT = 409;
    const HTTP_RESPONSE_TOO_MANY_REQUEST = 429;

    /**
     * @param Request $request
     * @param array $rules
     * @return void
     * @throws ValidationException
     */
    public function validateRequest(Request $request, array $rules)
    {
        $validator = Validator::make(
            $request->all(),
            $rules
        );

        if ($validator->fails()) {
            throw new ValidationException($validator->getMessageBag());

        }
    }

    /**
     * @param array $data
     * @param int $responseCode
     * @return JsonResponse
     */
    public function jsonResponse(array $data, int $responseCode = self::HTTP_RESPONSE_SUCCESS)
    {
        return response()->json(
            array_merge(
                [
                    'result' => $responseCode === self::HTTP_RESPONSE_SUCCESS ? 'Success' : 'Failed',
                ],
                $data
            ),
            $responseCode
        );
    }
}