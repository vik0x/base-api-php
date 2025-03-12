<?php

namespace Src\Infrastructure\Persistence\Seeders;

use Doctrine\DBAL\Connection;

final class DatabaseSeeder
{
    public function __construct(private Connection $connection)
    {
    }

    public function run(): void
    {
        $seeders = [
                    UserSeeder::class,
                   ];

        foreach ($seeders as $seeder) {
            echo sprintf("Running %s...\n", $seeder);
            (new $seeder($this->connection))->run();
            echo "Done!\n";
        }
    }
}
