<?php

namespace Src\Infrastructure\Persistence\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250311165557 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the activity_logs table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('activity_logs');

        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('action', Types::STRING, ['length' => 50]);
        $table->addColumn('entity', Types::STRING, ['length' => 50]);
        $table->addColumn('entity_id', Types::STRING, ['length' => 50, 'notnull' => false]);
        $table->addColumn('data', Types::TEXT, ['notnull' => false]);
        $table->addColumn('user_id', Types::STRING, ['length' => 50, 'notnull' => false]);
        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['entity']);
        $table->addIndex(['action']);
        $table->addIndex(['user_id']);
        $table->addIndex(['created_at']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('activity_logs');
    }
}
