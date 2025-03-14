<?php

namespace Src\Application\Auth\Command;

final class RefreshTokenCommand
{
    public function __construct(private string $refreshToken)
    {
    }

    public function refreshToken(): string
    {
        return $this->refreshToken;
    }
}
