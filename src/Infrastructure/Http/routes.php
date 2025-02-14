<?php

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Src\Infrastructure\Http\Controllers\UserController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

return function (App $app) {
    $app->group('/api/v1', function (RouteCollectorProxy $group) {
        $group->get('', function (Request $request, Response $response) {
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'timestamp' => time(),
                'environment' => $_ENV['APP_ENV'] ?? 'local'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        });

        $group->group('/users', function (RouteCollectorProxy $group) {
            $group->get('', [UserController::class, 'list']);
            $group->get('/{id:[0-9]+}', [UserController::class, 'find']);
            $group->post('', [UserController::class, 'create']);
            $group->put('/{id:[0-9]+}', [UserController::class, 'update']);
            $group->delete('/{id:[0-9]+}', [UserController::class, 'delete']);
        });
    });
    // ->add(new \Tuupola\Middleware\JwtAuthentication([
    //     "ignore" => ["/api/v1/auth/login", "/api/v1/auth/register"],
    //     "secret" => $_ENV['JWT_SECRET'],
    //     "algorithm" => ["HS256"],
    //     "secure" => false, // true en producción
    //     "error" => function ($response, $arguments) {
    //         $data = [
    //             "error" => [
    //                 "code" => 401,
    //                 "message" => $arguments["message"]
    //             ]
    //         ];
    //         $response->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    //         return $response
    //             ->withHeader("Content-Type", "application/json")
    //             ->withStatus(401);
    //     }
    // ]));

    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function (Request $request, Response $response) {
        $error = json_encode([
            'error' => [
                'code' => 404,
                'message' => 'Route not found'
            ]
        ]);

        $response->getBody()->write($error);

        return $response
            ->withStatus(404)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Length', strlen($error));
    });
};
