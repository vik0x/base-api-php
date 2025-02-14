<?php

use DI\Container;
use Slim\Factory\AppFactory;
use Src\Infrastructure\Http\Middleware\ErrorHandler;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$container = new Container();

$containerConfig = require __DIR__ . '/../src/Infrastructure/Config/Container/container.php';
$containerConfig($container);

$app = AppFactory::createFromContainer($container);

$app->add(new ErrorHandler());

$routes = require __DIR__ . '/../src/Infrastructure/Http/routes.php';
$routes($app);

$app->run();
