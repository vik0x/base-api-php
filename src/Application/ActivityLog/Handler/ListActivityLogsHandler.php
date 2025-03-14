<?php

namespace Src\Application\ActivityLog\Handler;

use Src\Application\ActivityLog\Query\ListActivityLogsQuery;
use Src\Domain\ActivityLog\Repositories\ActivityLogRepository;

final class ListActivityLogsHandler
{
    public function __construct(private ActivityLogRepository $repository)
    {
    }

    public function __invoke(ListActivityLogsQuery $query)
    {
        return $this->repository->findAll(
            $query->page(),
            $query->perPage(),
            $query->criteria()
        );
    }
}
