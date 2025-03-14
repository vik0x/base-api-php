<?php

namespace Src\Infrastructure\Persistence\Repositories;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Src\Domain\Auth\RefreshToken;
use Src\Domain\Auth\Repositories\RefreshTokenRepository;
use Src\Domain\Shared\ValueObjects\Id;
use Src\Domain\Auth\ValueObjects\RefreshTokenValue;
use Src\Domain\User\ValueObjects\UserId;

final class DoctrineRefreshTokenRepository implements RefreshTokenRepository
{
    public function __construct(private Connection $connection)
    {
    }

    public function save(RefreshToken $refreshToken): void
    {
        $this->connection->insert(
            'refresh_tokens',
            [
             'user_id'    => $refreshToken->userId()->value(),
             'token'      => $refreshToken->token()->value(),
             'expires_at' => $refreshToken->expiresAt()->format('Y-m-d H:i:s'),
             'created_at' => $refreshToken->createdAt()->format('Y-m-d H:i:s'),
            ]
        );
    }

    public function findByToken(RefreshTokenValue $token): ?RefreshToken
    {
        $tokenData = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('refresh_tokens')
            ->where('token = :token')
            ->setParameter('token', $token->value())
            ->executeQuery()
            ->fetchAssociative();

        if (! $tokenData) {
            return null;
        }

        return RefreshToken::reconstitute(
            new Id($tokenData['id']),
            new UserId($tokenData['user_id']),
            RefreshTokenValue::fromString($tokenData['token']),
            new DateTimeImmutable($tokenData['expires_at']),
            new DateTimeImmutable($tokenData['created_at'])
        );
    }

    public function deleteByToken(RefreshTokenValue $token): void
    {
        $this->connection->delete(
            'refresh_tokens',
            [
             'token' => $token->value(),
            ]
        );
    }

    public function deleteAllForUser(UserId $userId): void
    {
        $this->connection->delete(
            'refresh_tokens',
            [
             'user_id' => $userId->value(),
            ]
        );
    }
}
