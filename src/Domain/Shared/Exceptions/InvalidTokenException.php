<?php

namespace Src\Domain\Shared\Exceptions;

class InvalidTokenException extends DomainException
{
    public function __construct($message = 'Invalid token', $code = 401, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
