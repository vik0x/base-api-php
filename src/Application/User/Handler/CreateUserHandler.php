<?php

namespace Src\Application\User\Handler;

use Src\Application\User\Command\CreateUserCommand;
use Src\Domain\Shared\Exceptions\EmailAlreadyExistsException;
use Src\Domain\Shared\Exceptions\InvalidArgumentException;
use Src\Domain\User\UserRepository;
use Src\Domain\User\User;
use Src\Domain\Shared\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Password;

final class CreateUserHandler
{
    public function __construct(
        private UserRepository $repository
    ) {}

    public function __invoke(CreateUserCommand $command): void
    {
        if (empty($command->name())) {
            throw new InvalidArgumentException('Name is required');
        }

        $email = new Email($command->email());
        if ($this->repository->findByEmail($email)) {
            throw new EmailAlreadyExistsException();
        }

        $user = User::create(
            $command->name(),
            $email,
            new Password($command->password())
        );

        $this->repository->save($user);
    }
}
