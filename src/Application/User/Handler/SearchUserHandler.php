<?php

namespace Src\Application\User\Handler;

use Src\Application\User\Query\SearchUserQuery;
use Src\Domain\Shared\Pagination\PaginationInterface;
use Src\Domain\User\Repositories\UserRepository;

/**
 * Handler to search users
 */
class SearchUserHandler
{
    /**
     * @param UserRepository $userRepository User repository
     */
    public function __construct(private UserRepository $userRepository)
    {
    }

    /**
     * Execute the search query
     *
     * @param SearchUserQuery $query Search query
     * @return PaginationInterface Paginated results
     */
    public function __invoke(SearchUserQuery $query): PaginationInterface
    {
        return $this->userRepository->search(
            $query->criteria(),
            $query->page(),
            $query->perPage()
        );
    }
}
