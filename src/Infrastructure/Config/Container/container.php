<?php

use DI\Container;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Src\Domain\User\UserRepository;
use Src\Infrastructure\Persistence\Repositories\DoctrineUserRepository;
use Src\Application\User\Handler\CreateUserHandler;
use Src\Application\User\Handler\UpdateUserHandler;
use Src\Application\User\Handler\DeleteUserHandler;
use Src\Application\User\Handler\FindUserHandler;
use Src\Application\User\Handler\SearchUserHandler;
use Src\Infrastructure\Http\Controllers\Controller;
use Src\Infrastructure\Http\Controllers\UserController;

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

    $container->set(CreateUserHandler::class, function (Container $container) {
        return new CreateUserHandler(
            $container->get(UserRepository::class)
        );
    });

    $container->set(UpdateUserHandler::class, function (Container $container) {
        return new UpdateUserHandler(
            $container->get(UserRepository::class)
        );
    });

    $container->set(DeleteUserHandler::class, function (Container $container) {
        return new DeleteUserHandler(
            $container->get(UserRepository::class)
        );
    });

    $container->set(FindUserHandler::class, function (Container $container) {
        return new FindUserHandler(
            $container->get(UserRepository::class)
        );
    });

    $container->set(Controller::class, function (Container $container) {
        return new Controller($container);
    });

    // $container->set(UserController::class, function (Container $container) {
    //     return new UserController(
    //         $container->get(CreateUserHandler::class),
    //         $container->get(UpdateUserHandler::class),
    //         $container->get(DeleteUserHandler::class),
    //         $container->get(FindUserHandler::class),
    //         $container->get(UserRepository::class)
    //     );
    // });

    $container->set(SearchUserHandler::class, function (Container $container) {
        return new SearchUserHandler(
            $container->get(UserRepository::class)
        );
    });
};
