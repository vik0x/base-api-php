<?php

namespace Src\Application\Auth\Handler;

use Src\Application\Auth\Command\LogoutUserCommand;
use Src\Domain\Auth\Repositories\RefreshTokenRepository;
use Src\Domain\Auth\ValueObjects\RefreshTokenValue;
use Src\Domain\User\ValueObjects\UserId;

final class LogoutUserHandler
{
    public function __construct(private RefreshTokenRepository $refreshTokenRepository)
    {
    }

    public function __invoke(LogoutUserCommand $command): void
    {
        $tokenValue = RefreshTokenValue::fromString($command->refreshToken());
        $this->refreshTokenRepository->deleteByToken($tokenValue);

        if ($command->userId()) {
            $userId = new UserId((int) $command->userId());
            $this->refreshTokenRepository->deleteAllForUser($userId);
        }
    }
}
