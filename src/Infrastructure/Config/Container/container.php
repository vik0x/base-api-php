<?php

use DI\Container;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Src\Domain\User\UserRepository;
use Src\Infrastructure\Persistence\Repositories\DoctrineUserRepository;
use Src\Infrastructure\Http\Controller;
use Src\Infrastructure\Http\Services\FractalService;
use Src\Infrastructure\Bus\CommandBus;

return function (Container $container) {
    $container->set(Connection::class, function () {
        $config = require __DIR__ . '/../database.php';
        return DriverManager::getConnection($config['connections'][$config['default']]);
    });

    $container->set(UserRepository::class, function (Container $container) {
        return new DoctrineUserRepository(
            $container->get(Connection::class)
        );
    });

    $container->set(Controller::class, function (Container $container) {
        return new Controller($container, new FractalService(), $container->get(CommandBus::class));
    });

    $container->set(CommandBus::class, function (Container $container) {
        return new CommandBus($container);
    });
};
