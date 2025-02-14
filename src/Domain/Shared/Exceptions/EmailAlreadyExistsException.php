<?php

namespace Src\Domain\Shared\Exceptions;

class EmailAlreadyExistsException extends DomainException
{
  public function __construct($message = "Email already exists", $code = 409, \Throwable $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }
}
