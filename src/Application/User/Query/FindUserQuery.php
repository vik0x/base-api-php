<?php

namespace Src\Application\User\Query;

final class FindUserQuery
{
    public function __construct(private int $id)
    {
    }

    public function id(): int
    {
        return $this->id;
    }
}
