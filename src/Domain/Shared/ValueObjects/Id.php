<?php

namespace Src\Domain\Shared\ValueObjects;

use Src\Domain\Shared\Exceptions\DomainException;

final class Id
{
    public function __construct(private int $value)
    {
        $this->validate($value);
    }

    public static function fromString(string $value): self
    {
        return new self((int) $value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    private function validate(int $value): void
    {
        if ($value <= 0) {
            throw new DomainException('Id must be greater than zero');
        }
    }
}
