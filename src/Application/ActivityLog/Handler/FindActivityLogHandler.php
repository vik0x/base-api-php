<?php

namespace Src\Application\ActivityLog\Handler;

use Src\Application\ActivityLog\Query\FindActivityLogQuery;
use Src\Domain\ActivityLog\ActivityLogRepository;

final class FindActivityLogHandler
{
    public function __construct(private ActivityLogRepository $repository)
    {
    }

    public function __invoke(FindActivityLogQuery $query)
    {
        return $this->repository->findById($query->id());
    }
}
