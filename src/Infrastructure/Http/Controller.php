<?php

namespace Src\Infrastructure\Http;

use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class Controller
{
    public function __construct(protected Container $container) {}

    protected function dispatch($command)
    {
        $commandClass = get_class($command);
        $handlerClass = str_replace('Command', 'Handler', $commandClass);
        if ($commandClass === $handlerClass) {
            $handlerClass = str_replace('Query', 'Handler', $commandClass);
        }

        return $this->container->get($handlerClass)($command);
    }

    protected function jsonResponse(Response $response, mixed $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
