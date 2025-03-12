<?php

use DI\Container;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Src\Domain\User\UserRepository;
use Src\Domain\ActivityLog\ActivityLogRepository;
use Src\Infrastructure\Persistence\Repositories\DoctrineUserRepository;
use Src\Infrastructure\Persistence\Repositories\DoctrineActivityLogRepository;
use Src\Infrastructure\Http\Controller;
use Src\Infrastructure\Http\Services\FractalService;
use Src\Infrastructure\Bus\CommandBus;
use Src\Infrastructure\Bus\Middleware\ActivityLogMiddleware;

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

    $container->set(ActivityLogRepository::class, function (Container $container) {
        return new DoctrineActivityLogRepository(
            $container->get(Connection::class)
        );
    });

    $container->set(Controller::class, function (Container $container) {
        return new Controller($container, new FractalService(), $container->get(CommandBus::class));
    });

    $container->set(CommandBus::class, function (Container $container) {
        $commandBus = new CommandBus($container);

        $commandBus->addMiddleware(
            new ActivityLogMiddleware(
                $container->get(ActivityLogRepository::class),
                // TODO: get the current user ID from the authentication system
                // For now, we'll pass null
                null
            )
        );

        return $commandBus;
    });
};
