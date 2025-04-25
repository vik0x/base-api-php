<?php

declare(strict_types=1);

namespace Src\Infrastructure\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

final class GenerateMigrationCommand extends Command
{
    protected static $defaultName = 'migrations:make';
    private string $migrationsPath;

    public function __construct(string $migrationsPath)
    {
        parent::__construct();
        $this->migrationsPath = $migrationsPath;
    }

    protected function configure(): void
    {
        $this
        ->setDescription('Generate a new migration with a custom name')
        ->addArgument(
            'name',
            InputArgument::REQUIRED,
            'The name of the migration'
        );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        try {
            $name      = $input->getArgument('name');
            $version   = date('YmdHis');
            $name      = (string) preg_replace('/[^a-zA-Z0-9_]/', '', $name);
            $className = sprintf('%s_%s', $version, mb_strtolower($name));

            $migrationContent = $this->getMigrationTemplate($className);

            if (! is_dir($this->migrationsPath)) {
                mkdir($this->migrationsPath, 0777, true);
            }

            $fileName = $this->migrationsPath . '/' . $className . '.php';
            file_put_contents($fileName, $migrationContent);

            $output->writeln('<info>Migration created successfully: ' . $className . '</info>');
            return self::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return self::FAILURE;
        }
    }

    private function getMigrationTemplate(string $className): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

namespace Src\Infrastructure\Persistence\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class {$className} extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema \$schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

    }

    public function down(Schema \$schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
PHP;
    }
}
