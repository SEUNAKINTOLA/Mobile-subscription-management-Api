<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="PurchaseResponse",
 *     description="Device model",
 *     @OA\Xml(
 *         name="PurchaseResponse"
 *     )
 * )
 */
class PurchaseResponse
{
    /**
     * @OA\Property(
     *      title="status",
     *      description="Successful Purchase",
     *      example="Success"
     * )
     *
     * @var string
     */
    public $status;

    /**
     * @OA\Property(
     *      title="message",
     *      description="Registration message",
     *      example="Successful purchase"
     * )
     *
     * @var string
     */
    public $message;

    /**
     * @OA\Property(
     *      title="expire_date",
     *      description="Subscription expiry date",
     *      example="2020-01-01"
     * )
     *
     * @var string
     */
    private $expire_date;
}
