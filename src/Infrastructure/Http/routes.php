<?php

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Src\Infrastructure\Http\Controllers\UserController;
use Src\Infrastructure\Http\Controllers\ActivityLogController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

return function (App $app) {
    $app->group('/api/v1', function (RouteCollectorProxy $group) {
        $group->get('', function (Request $request, Response $response) {
            $response->getBody()->write(json_encode([
                                                     'status'      => 'success',
                                                     'timestamp'   => time(),
                                                     'environment' => $_ENV['APP_ENV'] ?? 'local',
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

        $group->group('/activity-logs', function (RouteCollectorProxy $group) {
            $group->get('', [ActivityLogController::class, 'list']);
            $group->get('/{id:[0-9]+}', [ActivityLogController::class, 'find']);
        });
    });

    $app->map([
               'GET',
               'POST',
               'PUT',
               'DELETE',
               'PATCH',
              ], '/{routes:.+}', function (Request $request, Response $response) {
                    $error = json_encode([
                                          'error' => [
                                                      'code'    => 404,
                                                      'message' => 'Route not found',
                                                     ],
                                         ]);

                    $response->getBody()->write($error);

                    return $response
                    ->withStatus(404)
                    ->withHeader('Content-Type', 'application/json')
                    ->withHeader('Content-Length', strlen($error));
              });
};
