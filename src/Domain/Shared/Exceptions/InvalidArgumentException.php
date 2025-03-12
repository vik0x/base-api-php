<?php

namespace Src\Domain\Shared\Exceptions;

class InvalidArgumentException extends DomainException
{
    public function __construct($message = 'Invalid argument', $code = 409, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
