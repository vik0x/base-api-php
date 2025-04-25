<?php

namespace Src\Application\ActivityLog\Handler;

use Src\Application\ActivityLog\Query\FindActivityLogQuery;
use Src\Domain\ActivityLog\Repositories\ActivityLogRepository;
use Src\Domain\ActivityLog\ActivityLog;

final class FindActivityLogHandler
{
    public function __construct(private ActivityLogRepository $repository)
    {
    }

    public function __invoke(FindActivityLogQuery $query): ?ActivityLog
    {
        return $this->repository->findById($query->id());
    }
}
