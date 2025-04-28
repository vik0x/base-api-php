<?php

namespace Src\Application\User\Query;

/**
 * Query to search users with pagination and filters
 */
final class SearchUserQuery
{
    private int $page;
    private int $perPage;
    private string $search;

    /**
     * @param array $params Search and pagination parameters
     */
    public function __construct(array $params)
    {
        $this->page    = isset($params['page']) ? (int) $params['page'] : 1;
        $this->perPage = isset($params['perPage']) ? (int) $params['perPage'] : 15;
        $this->search  = isset($params['search']) ? (string) $params['search'] : '';
    }

    /**
     * Get the current page number
     *
     * @return int Current page number
     */
    public function page(): int
    {
        return $this->page;
    }

    /**
     * Get the number of elements per page
     *
     * @return int Elements per page
     */
    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * Get the processed search criteria
     *
     * @return array<int, string> List of search terms
     */
    public function criteria(): array
    {
        if ($this->search) {
            return explode(' ', $this->search);
        }
        return [];
    }
}
