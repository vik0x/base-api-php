<?php

namespace Tests;

use DI\Container;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Slim\App;
use Slim\Factory\AppFactory;

abstract class TestCase extends PHPUnitTestCase
{
    protected Container $container;
    protected App $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        $containerConfig = require __DIR__ . '/../src/Infrastructure/Config/Container/container.php';
        $containerConfig($this->container);

        $this->app = AppFactory::createFromContainer($this->container);

        $routes = require __DIR__ . '/../src/Infrastructure/Http/routes.php';
        $routes($this->app);
    }
}
