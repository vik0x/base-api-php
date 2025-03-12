<?php

declare(strict_types=1);

namespace Src\Infrastructure\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Src\Infrastructure\Persistence\Doctrine\DoctrineCommandsFactory;
use Src\Infrastructure\Persistence\Seeders\UserSeeder;

final class DatabaseSeedCommand extends Command
{
    protected static $defaultName = 'db:seed';

    protected function configure(): void
    {
        $this->setDescription('Seed the database with records');
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        try {
            $seeder = new UserSeeder(
                DoctrineCommandsFactory::createConnection()
            );
            $seeder->run();

            $output->writeln('<info>Database seeded successfully!</info>');
            return self::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return self::FAILURE;
        }
    }
}
