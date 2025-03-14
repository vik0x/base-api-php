<?php

namespace Src\Infrastructure\Persistence\Repositories;

use Doctrine\DBAL\Connection;
use Src\Domain\User\User;
use Src\Domain\User\ValueObjects\UserId;
use Src\Domain\User\Repositories\UserRepository;
use Src\Domain\Shared\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Password;
use DateTimeImmutable;

final class DoctrineUserRepository implements UserRepository
{
    public function __construct(private Connection $connection)
    {
    }

    public function find(UserId $id): ?User
    {
        $user = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('users')
            ->where('id = :id')
            ->setParameter('id', $id->value())
            ->executeQuery()
            ->fetchAssociative();

        if ($user) {
            return $this->hydrateUser($user);
        }

        return null;
    }

    public function findByEmail(Email $email): ?User
    {
        $user = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('users')
            ->where('email = :email')
            ->setParameter('email', $email->value())
            ->executeQuery()
            ->fetchAssociative();

        if ($user) {
            return $this->hydrateUser($user);
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
            $qb->andWhere($field . ' LIKE :' . $field)
               ->setParameter($field, '%' . $value . '%');
        }

        $total = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM (' . $qb->getSQL() . ') as count_table',
            $qb->getParameters()
        );

        $qb->setFirstResult(($page - 1) * $perPage)
           ->setMaxResults($perPage)
           ->orderBy('created_at', 'DESC');

        $results = $qb->executeQuery()->fetchAllAssociative();
        $users   = array_map([$this, 'hydrateUser'], $results);

        return [
                'data'     => $users,
                'total'    => $total,
                'page'     => $page,
                'per_page' => $perPage,
               ];
    }

    private function insert(User $user): void
    {
        $this->connection->insert(
            'users',
            [
             'name'       => $user->name(),
             'email'      => $user->email()->value(),
             'password'   => $user->password()->value(),
             'created_at' => $user->createdAt()->format('Y-m-d H:i:s'),
             'updated_at' => $user->updatedAt()?->format('Y-m-d H:i:s'),
            ]
        );

        $id = (int) $this->connection->lastInsertId();
        $user->assignId(new UserId($id));
    }

    private function update(User $user): void
    {
        $this->connection->update(
            'users',
            [
             'name'       => $user->name(),
             'email'      => $user->email()->value(),
             'password'   => $user->password()->value(),
             'updated_at' => $user->updatedAt()?->format('Y-m-d H:i:s'),
            ],
            ['id' => $user->id()->value()]
        );
    }

    public function emailExists(Email $email): bool
    {
        $count = $this->connection->createQueryBuilder()
        ->select('COUNT(id)')
        ->from('users')
        ->where('email = :email')
        ->setParameter('email', $email->value())
        ->executeQuery()
        ->fetchOne();

        return $count > 0;
    }

    private function hydrateUser(array $user): User
    {
        return User::reconstitute(
            new UserId($user['id']),
            $user['name'],
            new Email($user['email']),
            Password::fromHash($user['password']),
            new DateTimeImmutable($user['created_at']),
            $user['updated_at'] ? new DateTimeImmutable($user['updated_at']) : null
        );
    }
}
