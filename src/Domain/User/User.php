<?php

namespace Src\Domain\User;

use Src\Domain\Shared\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Password;
use Src\Domain\User\Events\UserCreated;
use Src\Domain\User\Events\UserUpdated;
use Src\Domain\User\ValueObjects\UserId;
use DateTimeImmutable;

final class User
{
    private function __construct(
        private ?UserId $id,
        private string $name,
        private Email $email,
        private Password $password,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $updatedAt
    ) {
    }

    public static function create(
        string $name,
        Email $email,
        Password $password
    ): self {
        $user = new self(
            null,
            $name,
            $email,
            $password,
            new DateTimeImmutable(),
            null,
        );

        return $user;
    }

    public static function reconstitute(
        UserId $id,
        string $name,
        Email $email,
        Password $password,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $updatedAt = null
    ): self {
        return new self(
            $id,
            $name,
            $email,
            $password,
            $createdAt,
            $updatedAt
        );
    }

    public function assignId(UserId $id): void
    {
        if ($this->id !== null) {
            throw new \DomainException('User already has an ID');
        }
        $this->id = $id;

        $this->record(new UserCreated($this->id->value()));
    }

    public function update(
        string $name,
        Email $email
    ): void {
        $this->name      = $name;
        $this->email     = $email;
        $this->updatedAt = new \DateTimeImmutable();

        $this->record(new UserUpdated($this->id->value()));
    }

    public function id(): ?UserId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function password(): Password
    {
        return $this->password;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function verifyPassword(string $plainPassword): bool
    {
        return $this->password->verify($plainPassword);
    }

    /**
     * @return array{id: int|null, email: string, name: string, created_at: string, updated_at: string|null}
     */
    public function toArray(): array
    {
        return [
                'id'         => $this->id?->value(),
                'email'      => $this->email->value(),
                'name'       => $this->name,
                'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
                'updated_at' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
               ];
    }

    private function record(object $event): void
    {
        // Trigger event using EventDispatcher
    }
}
