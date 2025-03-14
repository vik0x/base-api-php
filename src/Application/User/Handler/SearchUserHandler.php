<?php

namespace Src\Application\User\Handler;

use Src\Domain\User\Repositories\UserRepository;
use Src\Application\User\Query\SearchUserQuery;

final class SearchUserHandler
{
    public function __construct(private UserRepository $repository)
    {
    }

    public function __invoke(SearchUserQuery $query): array
    {
        return $this->repository->search($query->criteria(), $query->page(), $query->perPage());
    }
}
