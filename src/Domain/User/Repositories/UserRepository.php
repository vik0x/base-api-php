<?php

namespace Src\Domain\User\Repositories;

use Src\Domain\User\User;
use Src\Domain\Shared\ValueObjects\Email;
use Src\Domain\User\ValueObjects\UserId;

interface UserRepository
{
    public function find(UserId $id): ?User;
    public function findByEmail(Email $email): ?User;
    public function save(User $user): void;
    public function delete(UserId $id): void;
    public function emailExists(Email $email): bool;

    /**
     * @param array<int, string> $criteria
     * @return array<string, mixed>
     */
    public function search(array $criteria, int $page = 1, int $perPage = 15): array;
}
