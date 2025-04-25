<?php

namespace Src\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\Console\Command\RunSqlCommand;
use Doctrine\DBAL\Tools\Console\ConnectionProvider;
use Doctrine\DBAL\Tools\Console\ConnectionProvider\SingleConnectionProvider;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Connection\ConnectionLoader;
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Src\Infrastructure\Persistence\Seeders\DatabaseSeeder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Migrations\Tools\Console\Command as MigrationsCommand;
use Doctrine\Migrations\Tools\Console\Command\ListCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\Tools\Console\Command;

final class DoctrineCommandsFactory
{
    /**
     * @param array<string, mixed> $settings
     * @return Connection
     * @throws \Doctrine\DBAL\Exception
     */
    public static function createConnection(array $settings): Connection
    {
        return DriverManager::getConnection($settings);
    }

    /**
     * @param Connection $connection
     * @param array<string, mixed> $settings
     * @return array<int, SymfonyCommand>
     */
    public static function createDoctrineCommands(Connection $connection, array $settings): array
    {
        $connectionProvider = self::createConnectionProvider($connection);
        $migrationCommands  = self::createMigrationCommands($connection, $settings);

        // Las versiones recientes de Doctrine DBAL tienen una implementación diferente
        // para RunSqlCommand
        $runSqlCommand = new RunSqlCommand($connectionProvider);

        return [
                $runSqlCommand,
                ...$migrationCommands,
               ];
    }

    /**
     * @param Connection $connection
     * @return ConnectionProvider
     */
    private static function createConnectionProvider(Connection $connection): ConnectionProvider
    {
        return new SingleConnectionProvider($connection);
    }

    /**
     * @param Connection $connection
     * @param array<string, mixed> $settings
     * @return array<int, SymfonyCommand>
     */
    private static function createMigrationCommands(Connection $connection, array $settings): array
    {
        $config            = self::createConfiguration($settings);
        $dependencyFactory = self::createDependencyFactory($connection, $config);

        return [
                new DiffCommand($dependencyFactory),
                new ExecuteCommand($dependencyFactory),
                new GenerateCommand($dependencyFactory),
                new MigrateCommand($dependencyFactory),
                new StatusCommand($dependencyFactory),
                new VersionCommand($dependencyFactory),
               ];
    }

    /**
     * @param array<string, mixed> $settings
     * @return Configuration
     */
    private static function createConfiguration(array $settings): Configuration
    {
        $configuration = new Configuration();
        $configuration->addMigrationsDirectory(
            $settings['migrations_namespace'],
            $settings['migrations_directory']
        );

        $configuration->setAllOrNothing($settings['all_or_nothing']);
        $configuration->setCheckDatabasePlatform($settings['check_database_platform']);

        $storageConfiguration = new TableMetadataStorageConfiguration();
        $storageConfiguration->setTableName($settings['migrations_table']);

        $configuration->setMetadataStorageConfiguration($storageConfiguration);

        return $configuration;
    }

    /**
     * @param Connection $connection
     * @param Configuration $config
     * @return DependencyFactory
     */
    private static function createDependencyFactory(Connection $connection, Configuration $config): DependencyFactory
    {
        $configLoader     = new ExistingConfiguration($config);
        $connectionLoader = new ExistingConnection($connection);

        return DependencyFactory::fromConnection(
            $configLoader,
            $connectionLoader
        );
    }

    /**
     * @return array<int, SymfonyCommand>
     */
    public static function create(): array
    {
        $connection      = self::createConnection(self::databaseConfig()['connections'][self::databaseConfig()['default']]);
        $migrationConfig = self::createMigrationConfig($connection);

        // Los loaders deben pasarse en el orden correcto: primero config, luego connection
        $configLoader     = new ExistingConfiguration($migrationConfig);
        $connectionLoader = new ExistingConnection($connection);

        $dependencyFactory = DependencyFactory::fromConnection(
            $configLoader,
            $connectionLoader
        );

        return array_merge(
            self::getMigrationCommands($dependencyFactory),
            self::getSeederCommands($connection)
        );
    }

    /**
     * @param DependencyFactory $dependencyFactory
     * @return array<int, SymfonyCommand>
     */
    private static function getMigrationCommands(DependencyFactory $dependencyFactory): array
    {
        return [
                new Command\MigrateCommand($dependencyFactory),
                new Command\LatestCommand($dependencyFactory),
                new Command\StatusCommand($dependencyFactory),
                new Command\UpToDateCommand($dependencyFactory),
                new Command\DiffCommand($dependencyFactory),
                new Command\ExecuteCommand($dependencyFactory),
                new Command\GenerateCommand($dependencyFactory),
                new Command\ListCommand($dependencyFactory),
                new Command\SyncMetadataCommand($dependencyFactory),
               ];
    }

    /**
     * @param Connection $connection
     * @return array<int, SymfonyCommand>
     */
    private static function getSeederCommands(Connection $connection): array
    {
        $seederClass = DatabaseSeeder::class;

        $seedCommand = new class ($connection, $seederClass) extends SymfonyCommand {
            /** @var string|null */
            protected static $defaultName = 'db:seed';

            /**
             * @param Connection $connection
             * @param string $seederClass
             */
            public function __construct(private Connection $connection, private string $seederClass)
            {
                parent::__construct();
            }

            protected function configure(): void
            {
                $this->setDescription('Seed the database with records');
            }

            /**
             * @param InputInterface $input
             * @param OutputInterface $output
             * @return int
             */
            protected function execute(
                InputInterface $input,
                OutputInterface $output
            ): int {
                try {
                    /** @var DatabaseSeeder $seeder */
                    $seeder = new $this->seederClass($this->connection);
                    $seeder->run();

                    $output->writeln('<info>Database seeded successfully!</info>');
                    return self::SUCCESS;
                } catch (\Exception $e) {
                    $output->writeln('<e>' . $e->getMessage() . '</e>');
                    return self::FAILURE;
                }
            }
        };

        return [$seedCommand];
    }

    /**
     * @return string
     */
    private static function configPath(): string
    {
        return __DIR__ . '/../../Config/';
    }

    /**
     * @return array{connections: array<string, array{driver: string, host: string, port: int, dbname: string, user: string, password: string}>, default: string}
     */
    private static function databaseConfig(): array
    {
        /** @var array{connections: array<string, array{driver: string, host: string, port: int, dbname: string, user: string, password: string}>, default: string} $config */
        $config = require self::configPath() . 'database.php';
        return $config;
    }

    /**
     * @param Connection $connection
     * @return Configuration
     */
    private static function createMigrationConfig(Connection $connection): Configuration
    {
        /** @var array{migrations_paths: array<string, string>, table_storage: array{table_name: string}} $migrationConfig */
        $migrationConfig = require self::configPath() . 'migrations.php';

        $configuration = new Configuration();
        $configuration->setAllOrNothing(true);
        $configuration->setCheckDatabasePlatform(false);

        // Configure Migrations Table
        $storageConfiguration = new TableMetadataStorageConfiguration();
        $storageConfiguration->setTableName($migrationConfig['table_storage']['table_name']);
        $configuration->setMetadataStorageConfiguration($storageConfiguration);

        // Add Migration Paths
        foreach ($migrationConfig['migrations_paths'] as $namespace => $path) {
            $configuration->addMigrationsDirectory($namespace, $path);
        }

        return $configuration;
    }
}
