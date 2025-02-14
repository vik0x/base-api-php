<?php

namespace Src\Domain\Shared\Exceptions;

abstract class DomainException extends \DomainException
{
  public function __construct(string $message, int $code = 0, \Throwable $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }
}
