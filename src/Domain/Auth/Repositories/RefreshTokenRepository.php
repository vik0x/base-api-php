<?php

namespace Src\Domain\Auth\Repositories;

use Src\Domain\Auth\RefreshToken;
use Src\Domain\Auth\ValueObjects\RefreshTokenValue;
use Src\Domain\User\ValueObjects\UserId;

interface RefreshTokenRepository
{
    public function save(RefreshToken $refreshToken): void;
    public function findByToken(RefreshTokenValue $token): ?RefreshToken;
    public function deleteByToken(RefreshTokenValue $token): void;
    public function deleteAllForUser(UserId $userId): void;
}
