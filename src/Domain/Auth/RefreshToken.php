<?php

namespace Src\Domain\Auth;

use DateTimeImmutable;
use Src\Domain\Auth\ValueObjects\RefreshTokenValue;
use Src\Domain\Shared\ValueObjects\Id;
use Src\Domain\User\ValueObjects\UserId;

final class RefreshToken
{
    private function __construct(
        private ?Id $id,
        private UserId $userId,
        private RefreshTokenValue $token,
        private DateTimeImmutable $expiresAt,
        private DateTimeImmutable $createdAt
    ) {
    }

    public static function create(
        int $userId,
        int $expiresInSeconds = 604800 // 7 days by default
    ): self {
        $userId    = new UserId($userId);
        $token     = RefreshTokenValue::generate();
        $now       = new DateTimeImmutable();
        $expiresAt = (clone $now)->modify('+' . $expiresInSeconds . ' seconds');

        return new self(
            null,
            $userId,
            $token,
            $expiresAt,
            $now
        );
    }

    public static function reconstitute(
        Id $id,
        UserId $userId,
        RefreshTokenValue $token,
        DateTimeImmutable $expiresAt,
        DateTimeImmutable $createdAt
    ): self {
        return new self(
            $id,
            $userId,
            $token,
            $expiresAt,
            $createdAt
        );
    }

    public function id(): ?Id
    {
        return $this->id;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function token(): RefreshTokenValue
    {
        return $this->token;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new DateTimeImmutable();
    }

    public function toArray(): array
    {
        return [
                'id'         => $this->id->value(),
                'user_id'    => $this->userId->value(),
                'token'      => $this->token->value(),
                'expires_at' => $this->expiresAt->format('Y-m-d H:i:s'),
                'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
               ];
    }
}
