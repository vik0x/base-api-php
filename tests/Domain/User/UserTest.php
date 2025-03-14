<?php

namespace Tests\Domain\User;

use PHPUnit\Framework\TestCase;
use Src\Domain\Shared\ValueObjects\Email;
use Src\Domain\User\User;
use Src\Domain\User\ValueObjects\UserId;
use Src\Domain\User\ValueObjects\Password;

final class UserTest extends TestCase
{
    public function testCreateUser(): void
    {
        $user = User::create(
            'John Doe',
            new Email('john@example.com'),
            new Password('Password123')
        );

        $this->assertNull($user->id());
        $this->assertEquals('John Doe', $user->name());
        $this->assertEquals('john@example.com', $user->email()->value());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->createdAt());
        $this->assertNull($user->updatedAt());
    }

    public function testAssignId(): void
    {
        $user = User::create(
            'John Doe',
            new Email('john@example.com'),
            new Password('Password123')
        );

        $userId = new UserId(1);
        $user->assignId($userId);

        $this->assertEquals($userId, $user->id());
    }

    public function testCannotAssignIdTwice(): void
    {
        $this->expectException(\DomainException::class);

        $user = User::create(
            'John Doe',
            new Email('john@example.com'),
            new Password('Password123')
        );

        $user->assignId(new UserId(1));
        $user->assignId(new UserId(2));
    }

    public function testUpdateUser(): void
    {
        $user = User::create(
            'John Doe',
            new Email('john@example.com'),
            new Password('Password123')
        );

        $user->assignId(new UserId(1));
        $user->update('Jane Doe', new Email('jane@example.com'));

        $this->assertEquals('Jane Doe', $user->name());
        $this->assertEquals('jane@example.com', $user->email()->value());
        $this->assertNotNull($user->updatedAt());
    }
}
