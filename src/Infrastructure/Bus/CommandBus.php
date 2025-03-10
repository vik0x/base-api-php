<?php

namespace Src\Infrastructure\Bus;

use Psr\Container\ContainerInterface;

class CommandBus
{
  private $container;

  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
  }

  public function dispatch(object $command)
  {
    $handler = $this->resolveHandler($command);

    return $handler($command);
  }

  private function resolveHandler(object $command): callable
  {
    // Convención: El nombre del handler se deriva del nombre del comando
    $commandClass = get_class($command);

    // Por ejemplo, de SearchUserQuery a SearchUserHandler
    $handlerClass = str_replace(['Query', 'Command'], 'Handler', $commandClass);

    // Verificar si el handler existe
    if (!class_exists($handlerClass)) {
      throw new \RuntimeException("Handler not found for " . $commandClass);
    }

    // Si no está en el container, lo creamos con sus dependencias
    if (!$this->container->has($handlerClass)) {
      $handler = $this->createHandler($handlerClass);
    } else {
      $handler = $this->container->get($handlerClass);
    }

    return $handler;
  }

  private function createHandler(string $handlerClass)
  {
    // Usamos reflexión para analizar el constructor del handler
    $reflection = new \ReflectionClass($handlerClass);
    $constructor = $reflection->getConstructor();

    if (!$constructor) {
      return new $handlerClass();
    }

    // Resolver dependencias del constructor
    $dependencies = [];
    foreach ($constructor->getParameters() as $param) {
      $type = $param->getType();

      if (!$type || $type->isBuiltin()) {
        throw new \RuntimeException("Cannot autowire parameter {$param->getName()} for {$handlerClass}");
      }

      $typeName = $type->getName();

      if ($this->container->has($typeName)) {
        $dependencies[] = $this->container->get($typeName);
      } else {
        throw new \RuntimeException("Dependency {$typeName} not found in container");
      }
    }

    return $reflection->newInstanceArgs($dependencies);
  }
}
