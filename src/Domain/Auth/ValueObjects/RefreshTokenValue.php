<?php

namespace Src\Domain\Auth\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

final class RefreshTokenValue
{
    private function __construct(private string $value)
    {
        $this->validate($value);
    }

    public static function generate(): self
    {
        return new self(bin2hex(random_bytes(32)));
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    private function validate(string $value): void
    {
        if ($value === '') {
            throw new InvalidArgumentException('Refresh token value cannot be empty');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(RefreshTokenValue $anotherToken): bool
    {
        return $this->value === $anotherToken->value();
    }
}
