<?php

namespace App\Exceptions;

use Exception;

class NotFoundException extends Exception
{
    public function __construct(string $clientToken)
    {
        $message = "Client Token $clientToken not found!";
        parent::__construct($message);
    }

}
