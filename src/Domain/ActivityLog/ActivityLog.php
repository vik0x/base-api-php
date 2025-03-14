<?php

namespace Src\Domain\ActivityLog;

use DateTimeImmutable;
use Src\Domain\Shared\ValueObjects\Id;
use Src\Domain\User\ValueObjects\UserId;

final class ActivityLog
{
    public function __construct(
        private string $action,
        private string $entity,
        private ?string $entityId,
        private array $data,
        private ?UserId $userId = null,
        private ?Id $id = null,
        private ?DateTimeImmutable $createdAt = new DateTimeImmutable()
    ) {
    }

    public static function create(
        string $action,
        string $entity,
        ?string $entityId,
        array $data,
        ?UserId $userId = null
    ): self {
        return new self($action, $entity, $entityId, $data, $userId);
    }

    public function id(): ?Id
    {
        return $this->id;
    }

    public function action(): string
    {
        return $this->action;
    }

    public function entity(): string
    {
        return $this->entity;
    }

    public function entityId(): ?string
    {
        return $this->entityId;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function userId(): ?UserId
    {
        return $this->userId;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
