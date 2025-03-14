<?php

namespace Src\Domain\Shared\Exceptions;

class InvalidCredentialsException extends DomainException
{
    public function __construct($message = 'Invalid credentials', $code = 401, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
