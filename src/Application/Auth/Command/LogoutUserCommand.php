<?php

namespace Src\Application\Auth\Command;

final class LogoutUserCommand
{
    public function __construct(private string $refreshToken, private ?string $userId)
    {
    }

    public function refreshToken(): string
    {
        return $this->refreshToken;
    }

    public function userId(): ?string
    {
        return $this->userId;
    }
}
