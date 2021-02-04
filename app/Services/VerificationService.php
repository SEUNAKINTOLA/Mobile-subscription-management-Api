<?php
namespace App\Services;

use App\Exceptions\RateLimitException;
use Carbon\Carbon;

class VerificationService 
{
    /**
     * @param int $appId
     * @param string $receipt
     * @return array
     * @throws RateLimitException
     */
    public function verifyReceipt(int $appId, string $receipt): array
    {
        return [
            'status' => 'success'
        ];
    }
}