<?php

namespace App\Http\Controllers\MockService;

use App\Http\Controllers\BaseController;
use App\Exceptions\RateLimitException;
use Carbon\Carbon;

class BaseStoreController extends BaseController
{
    const UTC = 'UTC';
    const UTCm6 = 'Pacific/Easter';

    /**
     * @param string $receipt
     * @return bool
     * @throws RateLimitException
     */
    protected function doDummyVerification(string $receipt): bool
    {
        $lastChar = substr($receipt, -1, 1);

        if (is_numeric($lastChar)) {

            if (((int)$lastChar) % 2 === 1) {
                return true;

            } else {

                if (((int)$lastChar) % 6 === 0 && rand(0, 1) === 1) {
                    throw new RateLimitException('Rate Limit Exception raised!');
                }

            }


        }

        return false;
    }

    protected function returnResult(bool $result, string $utc = self::UTC): array
    {
        $resultArray = [
            'result' => $result
        ];

        if ($result === true) {

            $resultArray = array_merge(
                $resultArray,
                [
                    'expiration' => Carbon::now()->addDays(rand(1, 365))->setTimezone($utc)
                ]
            );

        }

        return $resultArray;
    }
}