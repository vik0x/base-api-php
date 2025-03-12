<?php

namespace Src\Infrastructure\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Src\Domain\Shared\Exceptions\DomainException;
use Slim\Psr7\Response;

final class ErrorHandler implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {
            return $handler->handle($request);
        } catch (DomainException $e) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                                                     'error' => $e->getMessage(),
                                                    ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($e->getCode() ?? 400);
        } catch (\Throwable $e) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                                                     'error'    => 'An unexpected error occurred',
                                                     'message'  => $e->getMessage(),
                                                     'trace'    => $e->getTraceAsString(),
                                                     'file'     => $e->getFile(),
                                                     'line'     => $e->getLine(),
                                                     'code'     => $e->getCode(),
                                                     'previous' => $e->getPrevious(),
                                                    ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}
