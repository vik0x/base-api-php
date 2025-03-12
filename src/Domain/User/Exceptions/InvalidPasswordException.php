<?php

namespace Src\Domain\User\Exceptions;

use Src\Domain\Shared\Exceptions\DomainException;

final class InvalidPasswordException extends DomainException
{
    public function __construct(string $message = 'Invalid password', int $code = 409, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
