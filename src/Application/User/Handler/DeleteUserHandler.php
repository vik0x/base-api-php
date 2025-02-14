<?php

namespace Src\Application\User\Handler;

use Src\Application\User\Command\DeleteUserCommand;
use Src\Domain\User\UserRepository;
use Src\Domain\User\UserId;
use Src\Domain\Shared\Exceptions\NotFoundException;

final class DeleteUserHandler
{
    public function __construct(private UserRepository $repository) {}

    public function __invoke(DeleteUserCommand $command): void
    {
        $userId = new UserId($command->id());

        if (!$this->repository->find($userId)) {
            throw new NotFoundException('User not found');
        }

        $this->repository->delete($userId);
    }
}
