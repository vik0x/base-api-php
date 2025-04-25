<?php

namespace Src\Infrastructure\Persistence\Repositories;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Src\Domain\Auth\RefreshToken;
use Src\Domain\Auth\Repositories\RefreshTokenRepository;
use Src\Domain\Auth\ValueObjects\RefreshTokenValue;
use Src\Domain\Shared\ValueObjects\Id;
use Src\Domain\User\ValueObjects\UserId;

class DoctrineRefreshTokenRepository implements RefreshTokenRepository
{
    private string $table = 'refresh_tokens';

    public function __construct(private Connection $connection)
    {
    }

    public function save(RefreshToken $refreshToken): void
    {
        $data = [
                 'user_id'    => $refreshToken->userId()->value(),
                 'token'      => $refreshToken->token()->value(),
                 'expires_at' => $refreshToken->expiresAt()->format('Y-m-d H:i:s'),
                 'created_at' => $refreshToken->createdAt()->format('Y-m-d H:i:s'),
                ];

        if ($refreshToken->id()) {
            $this->connection->update(
                $this->table,
                $data,
                ['id' => $refreshToken->id()->value()]
            );
        } else {
            $this->connection->insert($this->table, $data);
        }
    }

    public function findByToken(RefreshTokenValue $token): ?RefreshToken
    {
        $stmt = $this->connection->prepare('SELECT * FROM ' . $this->table . ' WHERE token = :token');
        $stmt->bindValue(':token', $token->value());
        $row = $stmt->executeQuery()->fetchAssociative();

        if (! $row) {
            return null;
        }

        return $this->hydrateRefreshToken($row);
    }

    public function deleteByToken(RefreshTokenValue $token): void
    {
        $this->connection->delete($this->table, ['token' => $token->value()]);
    }

    public function deleteAllForUser(UserId $userId): void
    {
        $this->connection->delete($this->table, ['user_id' => $userId->value()]);
    }

    public function deleteExpired(): int
    {
        $now           = new DateTimeImmutable();
        $formattedDate = $now->format('Y-m-d H:i:s');

        $result = $this->connection->executeStatement(
            'DELETE FROM ' . $this->table . ' WHERE expires_at < :now',
            ['now' => $formattedDate]
        );

        return (int) $result;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrateRefreshToken(array $row): RefreshToken
    {
        $id        = new Id((int) $row['id']);
        $userId    = new UserId((int) $row['user_id']);
        $token     = RefreshTokenValue::fromString((string) $row['token']);
        $expiresAt = new DateTimeImmutable((string) $row['expires_at']);
        $createdAt = new DateTimeImmutable((string) $row['created_at']);

        return RefreshToken::reconstitute(
            $id,
            $userId,
            $token,
            $expiresAt,
            $createdAt
        );
    }
}
