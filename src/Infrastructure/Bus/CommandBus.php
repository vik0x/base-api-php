<?php

namespace Src\Infrastructure\Bus;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use RuntimeException;

class CommandBus
{
    /**
     * @var array<int, Middleware>
     */
    private array $middlewares = [];

    public function __construct(private ContainerInterface $container)
    {
    }

    public function addMiddleware(Middleware $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * @return mixed
     */
    public function dispatch(object $command): mixed
    {
        $handler = $this->resolveHandler($command);

        $chain = $this->createMiddlewareChain($handler);
        return $chain($command);
    }

    /**
     * @return callable
     */
    private function resolveHandler(object $command): callable
    {
        $commandClass = get_class($command);
        $handlerClass = preg_replace('/\\\\Command\\\\(.*)(Command|Query)$/', '\\\\Handler\\\\${1}Handler', $commandClass) ?? '';

        if (! class_exists($handlerClass)) {
            throw new RuntimeException('Handler not found for ' . $commandClass);
        }

        if (! $this->container->has($handlerClass)) {
            $handler = $this->createHandler($handlerClass);
        } else {
            $handler = $this->container->get($handlerClass);
        }

        return fn($command) => $handler->__invoke($command);
    }

    /**
     * @return object
     * @param class-string $handlerClass
     */
    private function createHandler(string $handlerClass): object
    {
        $reflection  = new ReflectionClass($handlerClass);
        $constructor = $reflection->getConstructor();

        if (! $constructor) {
            return new $handlerClass();
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            if (! $type || $type->isBuiltin()) {
                throw new RuntimeException('Cannot autowire parameter ' . $param->getName() . ' for ' . $handlerClass);
            }

            $typeName = $type->getName();

            if ($this->container->has($typeName)) {
                $dependencies[] = $this->container->get($typeName);
            } else {
                throw new RuntimeException('Dependency ' . $typeName . ' not found in container');
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * @param callable $handler
     * @return callable
     */
    private function createMiddlewareChain(callable $handler): callable
    {
        $chain = function (object $command) use ($handler) {
            return $handler($command);
        };

        foreach (array_reverse($this->middlewares) as $middleware) {
            $next  = $chain;
            $chain = function (object $command) use ($middleware, $next) {
                return $middleware->execute($command, $next);
            };
        }

        return $chain;
    }
}
