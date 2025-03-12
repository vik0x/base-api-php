<?php

namespace Src\Application\User\Handler;

use Src\Domain\User\User;
use Src\Domain\User\UserRepository;
use Src\Domain\User\UserId;
use Src\Domain\Shared\Exceptions\NotFoundException;
use Src\Application\User\Query\FindUserQuery;

final class FindUserHandler
{
    public function __construct(private UserRepository $repository)
    {
    }

    public function __invoke(FindUserQuery $query): User
    {
        $user = $this->repository->find(new UserId($query->id()));

        if (! $user) {
            throw new NotFoundException('User not found');
        }

        return $user;
    }
}
