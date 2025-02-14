<?php

namespace Src\Infrastructure\Persistence\Repositories;

use Doctrine\DBAL\Connection;
use Src\Domain\User\User;
use Src\Domain\User\UserId;
use Src\Domain\User\UserRepository;
use Src\Domain\Shared\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Password;

final class DoctrineUserRepository implements UserRepository
{
    public function __construct(private Connection $connection)
    {
    }

    public function find(UserId $id): ?User
    {
        $stmt = $this->connection->prepare('SELECT * FROM users WHERE id = ?');
        $result = $stmt->executeQuery([$id->value()]);
        
        if ($row = $result->fetchAssociative()) {
            return $this->hydrateUser($row);
        }

        return null;
    }

    public function findByEmail(Email $email): ?User
    {
        $stmt = $this->connection->prepare('SELECT * FROM users WHERE email = ?');
        $result = $stmt->executeQuery([$email->value()]);
        
        if ($row = $result->fetchAssociative()) {
            return $this->hydrateUser($row);
        }

        return null;
    }

    public function save(User $user): void
    {
        if ($user->id() === null) {
            $this->insert($user);
        } else {
            $this->update($user);
        }
    }

    public function delete(UserId $id): void
    {
        $this->connection->delete('users', ['id' => $id->value()]);
    }

    public function search(array $criteria, int $page = 1, int $perPage = 15): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')
           ->from('users');

        foreach ($criteria as $field => $value) {
            $qb->andWhere("$field LIKE :$field")
               ->setParameter($field, "%$value%");
        }

        $total = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM ({$qb->getSQL()}) as count_table",
            $qb->getParameters()
        );

        $qb->setFirstResult(($page - 1) * $perPage)
           ->setMaxResults($perPage)
           ->orderBy('created_at', 'DESC');

        $results = $qb->executeQuery()->fetchAllAssociative();
        $users = array_map([$this, 'hydrateUser'], $results);

        return [
            'data' => $users,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage
        ];
    }

    private function insert(User $user): void
    {
        $this->connection->insert('users', [
            'name' => $user->name(),
            'email' => $user->email()->value(),
            'password' => $user->password()->value(),
            'created_at' => $user->createdAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->updatedAt()?->format('Y-m-d H:i:s')
        ]);

        $id = (int) $this->connection->lastInsertId();
        $user->assignId(new UserId($id));
    }

    private function update(User $user): void
    {
        $this->connection->update('users', [
            'name' => $user->name(),
            'email' => $user->email()->value(),
            'password' => $user->password()->value(),
            'updated_at' => $user->updatedAt()?->format('Y-m-d H:i:s')
        ], [
            'id' => $user->id()->value()
        ]);
    }

    private function hydrateUser(array $row): User
    {
        return User::reconstitute(
            new UserId($row['id']),
            $row['name'],
            new Email($row['email']),
            Password::fromHash($row['password']),
            new \DateTimeImmutable($row['created_at']),
            $row['updated_at'] ? new \DateTimeImmutable($row['updated_at']) : null
        );
    }
} 
