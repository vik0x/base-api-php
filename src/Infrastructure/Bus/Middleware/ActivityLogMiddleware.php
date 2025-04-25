<?php

namespace Src\Infrastructure\Bus\Middleware;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use Src\Domain\ActivityLog\ActivityLog;
use Src\Domain\ActivityLog\Repositories\ActivityLogRepository;
use Src\Domain\User\ValueObjects\UserId;
use Src\Infrastructure\Bus\Middleware;

final class ActivityLogMiddleware implements Middleware
{
    public function __construct(
        private ActivityLogRepository $activityLogRepository,
        private ?string $currentUserId = null
    ) {
    }

    /**
     * @return mixed
     */
    public function execute(object $command, callable $next): mixed
    {
        $result = $next($command);

        $this->logActivity($command);

        return $result;
    }

    private function logActivity(object $command): void
    {
        $commandClass = get_class($command);
        $parts        = explode('\\', $commandClass);
        $commandName  = end($parts);

        $action = $this->extractAction($commandName);
        $entity = $this->extractEntity($commandName);

        $entityId = null;
        if (method_exists($command, 'id')) {
            $entityId = (string) $command->id();
        }

        $data = $this->extractData($command);

        $userId = null;
        if ($this->currentUserId !== null) {
            $userId = new UserId((int) $this->currentUserId);
        }

        // Create and save activity log
        $activityLog = ActivityLog::create(
            $action,
            $entity,
            $entityId,
            $data,
            $userId
        );

        $this->activityLogRepository->save($activityLog);
    }

    private function extractAction(string $commandName): string
    {
        $name = str_replace('Command', '', $commandName);
        if (preg_match('/^(Create|Update|Delete|Find|Search|Get|List|Enable|Disable|Activate|Deactivate|Login)/', $name, $matches)) {
            return strtolower($matches[1]);
        }
        return 'unknown';
    }

    private function extractEntity(string $commandName): string
    {
        $name = str_replace('Command', '', $commandName);
        if (preg_match('/^(Create|Update|Delete|Find|Search|Get|List|Enable|Disable|Activate|Deactivate|Login)(.+)$/', $name, $matches)) {
            return strtolower($matches[2]);
        }
        return 'unknown';
    }

    /**
     * @param object $command
     * @return array<string, mixed>
     */
    private function extractData(object $command): array
    {
        $data       = [];
        $reflection = new ReflectionClass($command);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $propertyName = $property->getName();

            if (in_array($propertyName, ['password', 'token', 'secret'], true)) {
                continue;
            }

            $data[$propertyName] = $property->getValue($command);
        }

        return $data;
    }
}
