<?php

namespace Src\Infrastructure\Persistence\Seeders;

use Src\Infrastructure\Persistence\Doctrine\DoctrineSeeder;
use Src\Domain\User\User;
use Src\Domain\Shared\ValueObjects\Email;
use Src\Domain\User\ValueObjects\Password;

final class UserSeeder extends DoctrineSeeder
{
    public function run(): void
    {
        $this->createUser([
                           'name'     => 'Admin User',
                           'email'    => 'admin@example.com',
                           'password' => 'Admin123!',
                          ]);

        for ($i = 0; $i < 20; $i++) {
            $firstName = $this->faker->firstName();
            $lastName  = $this->faker->lastName();

            $this->createUser([
                               'name'     => $firstName . ' ' . $lastName,
                               'email'    => $this->faker->unique()->safeEmail(),
                               'password' => 'Password123!',
                              ]);
        }

        $testUsers = [
                      [
                       'name'     => 'Test User',
                       'email'    => 'test@example.com',
                       'password' => 'Test123!',
                      ],
                      [
                       'name'     => 'Developer',
                       'email'    => 'dev@example.com',
                       'password' => 'Dev12354!',
                      ],
                     ];

        foreach ($testUsers as $userData) {
            $this->createUser($userData);
        }
    }

    private function createUser(array $userData): void
    {
        $exists = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM users WHERE email = ?',
            [$userData['email']]
        );

        if ($exists) {
            return;
        }

        $name     = (string) $userData['name'];
        $email    = new Email((string) $userData['email']);
        $password = new Password((string) $userData['password']);

        $user = User::create(
            $name,
            $email,
            $password
        );

        $this->connection->insert('users', [
                                            'name'       => $user->name(),
                                            'email'      => $user->email()->value(),
                                            'password'   => $user->password()->value(),
                                            'created_at' => $user->createdAt()->format('Y-m-d H:i:s'),
                                            'updated_at' => null,
                                           ]);

        echo sprintf(
            "Created user: %s (%s)\n",
            (string) $userData['name'],
            (string) $userData['email']
        );
    }
}
