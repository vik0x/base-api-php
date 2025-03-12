<?php

namespace Src\Application\ActivityLog\Query;

final class FindActivityLogQuery
{
    public function __construct(private int $id)
    {
    }

    public function id(): int
    {
        return $this->id;
    }
}
