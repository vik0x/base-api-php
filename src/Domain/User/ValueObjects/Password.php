<?php

namespace Src\Domain\User\ValueObjects;

use Src\Domain\User\Exceptions\InvalidPasswordException;

final class Password
{
    private string $value;

    public function __construct(string $value, bool $isHashed = false)
    {
        if (! $isHashed) {
            $this->validate($value);
            $this->value = $this->hash($value);
        } else {
            $this->value = $value;
        }
    }

    public static function fromHash(string $hash): self
    {
        return new self($hash, true);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function verify(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->value);
    }

    private function validate(string $value): void
    {
        if ($value === '') {
            throw new InvalidPasswordException('Password cannot be empty');
        }

        if (strlen($value) < 8) {
            throw new InvalidPasswordException('Password must be at least 8 characters');
        }

        if (strlen($value) > 255) {
            throw new InvalidPasswordException('Password is too long (maximum is 255 characters)');
        }

        if (! preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $value)) {
            throw new InvalidPasswordException(
                'Password must contain at least one uppercase letter, one lowercase letter and one number'
            );
        }
    }

    private function hash(string $value): string
    {
        return password_hash(
            $value,
            PASSWORD_ARGON2ID,
            [
             'memory_cost' => 65536,
             'time_cost'   => 4,
             'threads'     => 3,
            ]
        );
    }
}
