<?php

namespace Src\Application\User\Command;

final class UpdateUserCommand
{
    private int $id;
    private string $name;
    private string $email;

    /**
     * @param array{id: int, name: string, email: string} $data
     */
    public function __construct(array $data)
    {
        $this->id    = $data['id'];
        $this->name  = $data['name'];
        $this->email = $data['email'];
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
