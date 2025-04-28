<?php

namespace Src\Infrastructure\Persistence\Repositories;

use Doctrine\DBAL\Connection;
use Pagerfanta\Pagerfanta;
use Src\Domain\User\User;
use Src\Domain\User\ValueObjects\UserId;
use Src\Domain\User\Repositories\UserRepository;
use Src\Domain\Shared\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\Shared\Pagination\PaginationInterface;
use Src\Infrastructure\Pagination\DoctrineDbalAdapter;
use Src\Infrastructure\Pagination\PagerfantaPagination;
use DateTimeImmutable;
use Pagerfanta\Adapter\CallbackAdapter;
use ReflectionClass;
use ReflectionProperty;

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
     * Search users with pagination and filters
     *
     * @param array<int, string> $criteria Search criteria
     * @param int $page Current page number
     * @param int $perPage Number of elements per page
     * @return PaginationInterface Paginated results
     */
    public function search(array $criteria, int $page = 1, int $perPage = 15): PaginationInterface
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->table)
            ->orderBy('created_at', 'DESC');

        $countQueryBuilder = $this->connection->createQueryBuilder()
            ->from($this->table);

        if (! empty($criteria)) {
            $whereClauses = [];
            foreach ($criteria as $index => $term) {
                if (is_string($term) && $term !== '') {
                    $paramName      = 'term' . $index;
                    $whereClauses[] = $queryBuilder->expr()->like('name', ':' . $paramName) . ' OR ' .
                                     $queryBuilder->expr()->like('email', ':' . $paramName);

                    $paramValue = '%' . $term . '%';
                    $queryBuilder->setParameter($paramName, $paramValue);
                    $countQueryBuilder->setParameter($paramName, $paramValue);
                }
            }

            if (! empty($whereClauses)) {
                $queryBuilder->where('(' . implode(') OR (', $whereClauses) . ')');
                $countQueryBuilder->where('(' . implode(') OR (', $whereClauses) . ')');
            }
        }

        $adapter = new DoctrineDbalAdapter(
            $queryBuilder,
            $countQueryBuilder,
            'id'
        );

        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($perPage);
        $pagerfanta->setCurrentPage($page);

        $results = $pagerfanta->getCurrentPageResults();
        $users   = [];
        foreach ($results as $row) {
            $users[] = $this->hydrateUser($row);
        }

        $hydratingAdapter = new CallbackAdapter(
            fn() => $adapter->getNbResults(),
            fn (int $offset, int $length) => array_map(
                [
                 $this,
                 'hydrateUser',
                ],
                $adapter->getSlice($offset, $length)
            )
        );

        $pagerfanta = new Pagerfanta($hydratingAdapter);
        $pagerfanta->setMaxPerPage($perPage);
        $pagerfanta->setCurrentPage($page);

        return new PagerfantaPagination($pagerfanta);
    }

    /**
     * Converts an associative array from the database to a User domain entity
     *
     * @param array<string, mixed> $user User data from the database
     * @return User Domain entity User
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
