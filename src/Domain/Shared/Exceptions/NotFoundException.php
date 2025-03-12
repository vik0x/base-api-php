<?php

namespace Src\Domain\Shared\Exceptions;

class NotFoundException extends DomainException
{
    public function __construct(string $message = 'Not Found', int $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
