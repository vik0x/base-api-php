<?php

namespace Src\Application\User\Command;

final class CreateUserCommand
{
    private string $name;
    private string $email;
    private string $password;

    public function __construct(private array $data)
    {
        $this->name     = $data['name'];
        $this->email    = $data['email'];
        $this->password = $data['password'];
    }

    public function name(): string
    {
        return $this->name;
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
