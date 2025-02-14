<?php

namespace Src\Application\User\Handler;

use Src\Application\User\Command\UpdateUserCommand;
use Src\Domain\User\UserRepository;
use Src\Domain\User\UserId;
use Src\Domain\Shared\ValueObjects\Email;
use Src\Domain\Shared\Exceptions\EmailAlreadyExistsException;
use Src\Domain\Shared\Exceptions\NotFoundException;

final class UpdateUserHandler
{
    public function __construct(private UserRepository $repository) {}

    public function __invoke(UpdateUserCommand $command): void
    {
        $user = $this->repository->find(new UserId($command->id()));

        if (!$user) {
            throw new NotFoundException('User not found');
        }

        $email = new Email($command->email());

        $existingUser = $this->repository->findByEmail($email);
        if ($existingUser && !$existingUser->id()->equals($user->id())) {
            throw new EmailAlreadyExistsException();
        }

        $user->update($command->name(), $email);

        $this->repository->save($user);
    }
}
