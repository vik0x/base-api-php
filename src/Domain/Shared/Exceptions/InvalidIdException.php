<?php

namespace Src\Domain\Shared\Exceptions;

final class InvalidIdException extends DomainException
{
    public function __construct(string $message = '')
    {
        parent::__construct($message !== '' ? $message : 'Invalid ID value');
    }
}
