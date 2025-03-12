<?php

namespace Src\Domain\User\Events;

final class UserCreated
{
    public function __construct(private int $userId)
    {
    }

    public function userId(): int
    {
        return $this->userId;
    }
}
