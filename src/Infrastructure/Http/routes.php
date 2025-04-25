<?php

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Src\Infrastructure\Http\Controllers\UserController;
use Src\Infrastructure\Http\Controllers\ActivityLogController;
use Src\Infrastructure\Http\Controllers\AuthController;
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

        $group->group('/auth', function (RouteCollectorProxy $group) {
            $group->post('/login', [AuthController::class, 'login']);
            $group->post('/logout', [AuthController::class, 'logout']);
            $group->post('/refresh', [AuthController::class, 'refresh']);
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
                    $errorLength = is_string($error) ? strlen($error) : 0;

                    return $response
                    ->withStatus(404)
                    ->withHeader('Content-Type', 'application/json')
                    ->withHeader('Content-Length', (string) $errorLength);
              });

    // Protected route handling
    $app->add(function (Request $request, $handler) use ($app) {
        $route = $request->getUri()->getPath();

        // Rutas públicas que no requieren autenticación
        $publicRoutes = [
                         '/',
                         '/api/v1/auth/login',
                         '/api/v1/auth/refresh',
                        ];

        // Verificar si es una ruta pública
        foreach ($publicRoutes as $publicRoute) {
            if ($route === $publicRoute) {
                return $handler->handle($request);
            }
        }

        // Obtener el token de autorización
        $authHeader = $request->getHeaderLine('Authorization');
        $token      = null;

        if (! empty($authHeader)) {
            $parts = explode(' ', $authHeader);
            if (count($parts) === 2 && $parts[0] === 'Bearer') {
                $token = $parts[1];
            }
        }

        // Si no hay token, verificar si es una petición a la documentación de la API
        if ($token === null) {
            $pathPrefix = '/api-docs';
            $pathInfo   = $request->getUri()->getPath();

            // Calcular directamente la longitud del prefijo
            $prefixLength = strlen($pathPrefix);

            // Si el prefijo coincide con la ruta, permitir acceso a la documentación
            if ($prefixLength > 0 && strncmp($pathInfo, $pathPrefix, $prefixLength) === 0) {
                return $handler->handle($request);
            }

            // De lo contrario, retornar error de autenticación
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Authentication required']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // Verificar el token usando el servicio JWT
        $container = $app->getContainer();
        if (! $container) {
            throw new \RuntimeException('Container is not available');
        }

        $jwtService = $container->get(\Src\Infrastructure\Auth\JwtService::class);
        $decoded    = $jwtService->validateToken($token);

        if ($decoded === null) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Invalid or expired token']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        // Si el token es válido, continuar con la petición
        return $handler->handle($request);
    });
};
