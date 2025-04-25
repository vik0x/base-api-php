<?php

namespace Src\Domain\Shared\Exceptions;

final class InvalidEmailException extends DomainException
{
    public function __construct(string $message = '')
    {
        parent::__construct($message !== '' ? $message : 'Invalid email');
    }
}
