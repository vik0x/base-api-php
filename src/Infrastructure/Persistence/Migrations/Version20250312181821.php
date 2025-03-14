<?php

namespace Src\Infrastructure\Persistence\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20250312181821 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create refresh_tokens tables';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('refresh_tokens');
        
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('user_id', Types::INTEGER, ['unsigned' => true]);
        $table->addColumn('token', Types::STRING, ['length' => 255]);
        $table->addColumn('expires_at', Types::DATETIME_IMMUTABLE);
        $table->addColumn('created_at', Types::DATETIME_IMMUTABLE);
        $table->addColumn('updated_at', Types::DATETIME_IMMUTABLE, ['notnull' => false]);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['token']);
        $table->addForeignKeyConstraint('users', ['user_id'], ['id'], [
            'onDelete' => 'CASCADE',
            'onUpdate' => 'CASCADE'
        ], 'refresh_tokens_user_id_foreign');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('refresh_tokens');
    }
} 
