<?php

namespace Src\Application\Auth\Command;

final class LoginUserCommand
{
    public function __construct(private string $email, private string $password)
    {
    }

    public function email(): string
    {
        return $this->email;
    }

    public function password(): string
    {
        return $this->password;
    }
}
