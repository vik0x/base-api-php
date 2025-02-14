<?php

declare(strict_types=1);

namespace Src\Infrastructure\Persistence\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240101000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users table';
    }

    public function up(Schema $schema): void
    {
        $users = $schema->createTable('users');
        
        $users->addColumn('id', 'integer', ['autoincrement' => true, 'unsigned' => true, 'notnull' => true]);
        $users->addColumn('name', 'string', ['length' => 255]);
        $users->addColumn('email', 'string', ['length' => 255]);
        $users->addColumn('password', 'string', ['length' => 255]);
        $users->addColumn('created_at', 'datetime_immutable');
        $users->addColumn('updated_at', 'datetime_immutable', ['notnull' => false]);

        $users->setPrimaryKey(['id']);
        $users->addUniqueIndex(['email']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('users');
    }
} 
