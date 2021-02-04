<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="DeviceRegistrationResponse",
 *     description="Device model",
 *     @OA\Xml(
 *         name="DeviceRegistrationResponse"
 *     )
 * )
 */
class DeviceRegistrationResponse
{
    /**
     * @OA\Property(
     *      title="result",
     *      description="Result textName of the new project",
     *      example="Ok"
     * )
     *
     * @var string
     */
    public $result;

    /**
     * @OA\Property(
     *      title="message",
     *      description="Registration message",
     *      example="Device registered"
     * )
     *
     * @var string
     */
    public $message;

    /**
     * @OA\Property(
     *      title="client_token",
     *      description="Client Token",
     *      example="6bf768c9-d576-4168-9905-a0ess9"
     * )
     *
     * @var string
     */
    private $client_token;
}
