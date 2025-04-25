<?php

use DI\Container;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Src\Domain\User\Repositories\UserRepository;
use Src\Domain\ActivityLog\Repositories\ActivityLogRepository;
use Src\Domain\Auth\Repositories\RefreshTokenRepository;
use Src\Infrastructure\Auth\JwtService;
use Src\Infrastructure\Persistence\Repositories\DoctrineUserRepository;
use Src\Infrastructure\Persistence\Repositories\DoctrineActivityLogRepository;
use Src\Infrastructure\Persistence\Repositories\DoctrineRefreshTokenRepository;
use Src\Infrastructure\Http\Controller;
use Src\Infrastructure\Http\Services\FractalService;
use Src\Infrastructure\Bus\CommandBus;
use Src\Infrastructure\Bus\Middleware\ActivityLogMiddleware;

return function (Container $container) {
    $container->set(Connection::class, function () {
        $config = require __DIR__ . '/../database.php';

        /** @var array{driver: 'pdo_mysql'|'pdo_pgsql'|'pdo_sqlite'|'pdo_sqlsrv'|'mysqli'|'oci8'|'sqlsrv', host: string, port: int, dbname: string, user: string, password: string} $connectionConfig */
        $connectionConfig = $config['connections'][$config['default']];

        return DriverManager::getConnection($connectionConfig);
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

    $container->set(RefreshTokenRepository::class, function (Container $container) {
        return new DoctrineRefreshTokenRepository(
            $container->get(Connection::class)
        );
    });

    $container->set(JwtService::class, function (Container $container) {
        $config = require __DIR__ . '/../jwt.php';
        return new JwtService($config['secret'], $config['expiration']);
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
