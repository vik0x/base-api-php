<?php

namespace Src\Infrastructure\Http;

use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\Infrastructure\Http\Services\FractalService;
use Src\Infrastructure\Bus\CommandBus;

abstract class Controller
{
    public function __construct(protected Container $container, protected FractalService $fractal, protected CommandBus $commandBus)
    {
        $this->fractal    = $fractal;
        $this->commandBus = $commandBus;
    }

    protected function jsonResponse(Response $response, mixed $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
