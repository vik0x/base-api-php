<?php

namespace Src\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

final class DoctrineCommandsFactory
{
    public static function createConnection(): Connection
    {
        return DriverManager::getConnection(
            self::databaseConfig()['connections'][self::databaseConfig()['default']]
        );
    }

    public static function createCommands(): array
    {
        $connection = self::createConnection();
        $migrationConfig = new ConfigurationArray(require self::configPath() . 'migrations.php');

        $dependencyFactory = DependencyFactory::fromConnection(
            $migrationConfig,
            new ExistingConnection($connection)
        );

        return array_merge(
            self::getMigrationCommands($dependencyFactory),
            self::getSeederCommands($connection)
        );
    }

    private static function getMigrationCommands(DependencyFactory $dependencyFactory): array
    {
        return [
            new Command\ExecuteCommand($dependencyFactory),
            new Command\MigrateCommand($dependencyFactory),
            new Command\LatestCommand($dependencyFactory),
            new Command\StatusCommand($dependencyFactory),
            new Command\UpToDateCommand($dependencyFactory),
            new Command\DiffCommand($dependencyFactory),
            new Command\GenerateCommand($dependencyFactory)
        ];
    }

    private static function getSeederCommands(Connection $connection): array
    {
        $test = new \Src\Infrastructure\Persistence\Seeders\DatabaseSeeder($connection);
        return [
            new class($connection) extends SymfonyCommand {
                protected static $defaultName = 'db:seed';

                public function __construct(private Connection $connection)
                {
                    parent::__construct();
                }

                protected function configure(): void
                {
                    $this->setDescription('Seed the database with records');
                }

                protected function execute(
                    \Symfony\Component\Console\Input\InputInterface $input,
                    \Symfony\Component\Console\Output\OutputInterface $output
                ): int {
                    try {
                        $seeder = new \Src\Infrastructure\Persistence\Seeders\DatabaseSeeder($this->connection);
                        $seeder->run();

                        $output->writeln('<info>Database seeded successfully!</info>');
                        return self::SUCCESS;
                    } catch (\Exception $e) {
                        $output->writeln("<error>{$e->getMessage()}</error>");
                        return self::FAILURE;
                    }
                }
            }
        ];
    }

    private static function configPath(): string
    {
        return __DIR__ . '/../../Config/';
    }

    private static function databaseConfig(): array
    {
        return require self::configPath() . 'database.php';
    }
}
