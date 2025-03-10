<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Src\Infrastructure\Console\CommandLoader;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$cli = new Application('GWM');

CommandLoader::load($cli);

$cli->run();
