<?php

namespace Src\Domain\Shared\ValueObjects;

use Src\Domain\Shared\Exceptions\InvalidEmailException;

final class Email
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $this->sanitize($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    private function validate(string $value): void
    {
        if ($value === '') {
            throw new InvalidEmailException('Email cannot be empty');
        }

        if (strlen($value) > 255) {
            throw new InvalidEmailException('Email is too long (maximum is 255 characters)');
        }

        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException('Invalid email format');
        }

        $blockedDomains = [
                           'tempmail.com',
                           'throwaway.com',
                          ];

        $atPos  = strrpos($value, '@');
        $domain = $atPos !== false ? substr($value, $atPos + 1) : '';

        if (in_array($domain, $blockedDomains, true)) {
            throw new InvalidEmailException('Email domain not allowed');
        }
    }

    private function sanitize(string $value): string
    {
        return strtolower(trim($value));
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
