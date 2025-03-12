<?php

namespace Src\Domain\ActivityLog;

interface ActivityLogRepository
{
    public function save(ActivityLog $activityLog): void;
    public function findById(int $id): ?ActivityLog;
    public function findAll(int $page = 1, int $perPage = 15, array $filters = []): array;
}
