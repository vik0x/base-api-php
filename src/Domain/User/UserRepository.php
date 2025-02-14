<?php

namespace Src\Domain\User;

use Src\Domain\Shared\ValueObjects\Email;

interface UserRepository
{
    public function find(UserId $id): ?User;
    public function findByEmail(Email $email): ?User;
    public function save(User $user): void;
    public function delete(UserId $id): void;
    public function search(array $criteria, int $page = 1, int $perPage = 15): array;
} 
