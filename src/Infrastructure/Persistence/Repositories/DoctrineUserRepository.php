<?php

namespace Src\Infrastructure\Persistence\Repositories;

use Doctrine\DBAL\Connection;
use Src\Domain\User\User;
use Src\Domain\User\ValueObjects\UserId;
use Src\Domain\User\Repositories\UserRepository;
use Src\Domain\Shared\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Password;
use DateTimeImmutable;

class DoctrineUserRepository implements UserRepository
{
    private string $table = 'users';

    public function __construct(private Connection $connection)
    {
    }

    public function find(UserId $id): ?User
    {
        $stmt = $this->connection->prepare('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        $stmt->bindValue(':id', $id->value());
        $row = $stmt->executeQuery()->fetchAssociative();

        if (! $row) {
            return null;
        }

        return $this->hydrateUser($row);
    }

    public function findByEmail(Email $email): ?User
    {
        $stmt = $this->connection->prepare('SELECT * FROM ' . $this->table . ' WHERE email = :email');
        $stmt->bindValue(':email', $email->value());
        $row = $stmt->executeQuery()->fetchAssociative();

        if (! $row) {
            return null;
        }

        return $this->hydrateUser($row);
    }

    public function save(User $user): void
    {
        $data = [
                 'name'       => $user->name(),
                 'email'      => $user->email()->value(),
                 'password'   => $user->password()->value(),
                 'created_at' => $user->createdAt()->format('Y-m-d H:i:s'),
                 'updated_at' => $user->updatedAt() ? $user->updatedAt()->format('Y-m-d H:i:s') : null,
                ];

        if ($user->id()) {
            $this->connection->update(
                $this->table,
                $data,
                ['id' => $user->id()->value()]
            );
        } else {
            $this->connection->insert($this->table, $data);
            $userId = new UserId((int) $this->connection->lastInsertId());
            $user->assignId($userId);
        }
    }

    public function delete(UserId $id): void
    {
        $this->connection->delete($this->table, ['id' => $id->value()]);
    }

    public function emailExists(Email $email): bool
    {
        $stmt = $this->connection->prepare('SELECT COUNT(*) FROM ' . $this->table . ' WHERE email = :email');
        $stmt->bindValue(':email', $email->value());
        $count = (int) $stmt->executeQuery()->fetchOne();

        return $count > 0;
    }

    /**
     * @param array<int, string> $criteria
     * @return array<string, mixed>
     */
    public function search(array $criteria, int $page = 1, int $perPage = 15): array
    {
        $offset     = ($page - 1) * $perPage;
        $query      = 'SELECT * FROM ' . $this->table . ' ';
        $countQuery = 'SELECT COUNT(*) FROM ' . $this->table . ' ';
        $params     = [];

        $whereClauses = [];
        if (! empty($criteria)) {
            foreach ($criteria as $index => $term) {
                if (is_string($term) && $term !== '') {
                    $whereClauses[]          = '(name LIKE :term' . $index . ' OR email LIKE :term' . $index . ')';
                    $params['term' . $index] = '%' . $term . '%';
                }
            }
        }

        if (! empty($whereClauses)) {
            $whereClause = ' WHERE ' . implode(' OR ', $whereClauses);
            $query      .= $whereClause;
            $countQuery .= $whereClause;
        }

        $query .= ' ORDER BY created_at DESC LIMIT ' . $perPage . ' OFFSET ' . $offset;

        $stmt      = $this->connection->prepare($query);
        $countStmt = $this->connection->prepare($countQuery);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
            $countStmt->bindValue(':' . $key, $value);
        }

        $rows  = $stmt->executeQuery()->fetchAllAssociative();
        $total = (int) $countStmt->executeQuery()->fetchOne();

        $users = [];
        foreach ($rows as $row) {
            $users[] = $this->hydrateUser($row);
        }

        return [
                'data'     => $users,
                'total'    => $total,
                'page'     => $page,
                'per_page' => $perPage,
               ];
    }

    /**
     * @param array<string, mixed> $user
     */
    private function hydrateUser(array $user): User
    {
        $userId    = new UserId((int) $user['id']);
        $name      = (string) $user['name'];
        $email     = new Email((string) $user['email']);
        $password  = Password::fromHash((string) $user['password']);
        $createdAt = new DateTimeImmutable((string) $user['created_at']);
        $updatedAt = $user['updated_at'] ? new DateTimeImmutable((string) $user['updated_at']) : null;

        return User::reconstitute(
            $userId,
            $name,
            $email,
            $password,
            $createdAt,
            $updatedAt
        );
    }
}
