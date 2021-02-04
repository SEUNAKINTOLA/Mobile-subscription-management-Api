<?php

namespace App\Exceptions;

use Exception;

class DeviceExistsException extends Exception
{
    public function __construct(int $uId, int $appId)
    {
        $message = "Device with UID = $uId and AppId = $appId is already exists in the DB";
        parent::__construct($message);
    }

}
