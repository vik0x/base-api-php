<?php

declare(strict_types=1);

namespace Src\Infrastructure\Console;

use Symfony\Component\Console\Application;
use Src\Infrastructure\Console\Command\GenerateMigrationCommand;
use Src\Infrastructure\Console\Command\DatabaseSeedCommand;
use Src\Infrastructure\Persistence\Doctrine\DoctrineCommandsFactory;

final class CommandLoader
{
    public static function load(Application $application): void
    {
        $migrationsConfig = require __DIR__ . '/../Config/migrations.php';
        $migrationsPath   = $migrationsConfig['migrations_paths']['Src\Infrastructure\Persistence\Migrations'];

        foreach (DoctrineCommandsFactory::create() as $command) {
            $application->add($command);
        }

        $application->add(new GenerateMigrationCommand($migrationsPath));
        $application->add(new DatabaseSeedCommand());
    }
}
