<?php

namespace Tests\Infrastructure;

use Doctrine\DBAL\Connection;
use Tests\TestCase;

abstract class IntegrationTestCase extends TestCase
{
    protected Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->connection = $this->container->get(Connection::class);
        $this->connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->connection->rollBack();
        parent::tearDown();
    }
} 
