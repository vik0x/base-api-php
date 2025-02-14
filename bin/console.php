<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Src\Infrastructure\Persistence\Doctrine\DoctrineCommandsFactory;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$cli = new Application('Database Migrations');

foreach (DoctrineCommandsFactory::createCommands() as $command) {
    $cli->add($command);
}

$cli->add(new class extends \Symfony\Component\Console\Command\Command {
    protected static $defaultName = 'db:seed';

    protected function configure(): void
    {
        $this->setDescription('Seed the database with records');
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ): int {
        try {
            $seeder = new \Src\Infrastructure\Persistence\Seeders\UserSeeder(
                DoctrineCommandsFactory::createConnection()
            );
            $seeder->run();

            $output->writeln('<info>Database seeded successfully!</info>');
            return self::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return self::FAILURE;
        }
    }
});

$cli->run();
