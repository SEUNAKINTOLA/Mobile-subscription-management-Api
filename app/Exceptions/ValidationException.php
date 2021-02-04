<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Support\MessageBag;

class ValidationException extends Exception
{
    private array $allValidations = [];

    public function __construct(MessageBag $messageBag)
    {
        $this->allValidations = $messageBag->all();
        parent::__construct('Invalid request has been retrieved!');
    }

    public function getValidateExceptions(): array
    {
        return $this->allValidations;
    }
}
