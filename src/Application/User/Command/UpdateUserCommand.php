<?php

namespace Src\Application\User\Command;

final class UpdateUserCommand
{
    public function __construct(
        private int $id,
        private string $name,
        private string $email
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }
} 
