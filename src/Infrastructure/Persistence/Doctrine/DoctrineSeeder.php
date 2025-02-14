<?php

namespace Src\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\Connection;
use Faker\Factory;
use Faker\Generator;

abstract class DoctrineSeeder
{
    protected Connection $connection;
    protected Generator $faker;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->faker = Factory::create('es_ES');
    }

    abstract public function run(): void;
} 
