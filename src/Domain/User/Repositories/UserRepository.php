<?php

namespace Src\Domain\User\Repositories;

use Src\Domain\User\User;
use Src\Domain\User\ValueObjects\UserId;
use Src\Domain\Shared\ValueObjects\Email;
use Src\Domain\Shared\Pagination\PaginationInterface;

interface UserRepository
{
    public function find(UserId $id): ?User;
    public function findByEmail(Email $email): ?User;
    public function save(User $user): void;
    public function delete(UserId $id): void;
    public function emailExists(Email $email): bool;

    /**
     * @param array<int, string> $criteria
     */
    public function search(array $criteria, int $page = 1, int $perPage = 15): PaginationInterface;
}
