<?php

namespace Src\Application\User\Command;

final class DeleteUserCommand
{
    public function __construct(private int $id)
    {
    }

    public function id(): int
    {
        return $this->id;
    }
}
