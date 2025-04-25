<?php

namespace Src\Application\ActivityLog\Query;

final class ListActivityLogsQuery
{
    private int $page;
    private int $perPage;
    private string $search;

    /**
     * @param array{page?: int, perPage?: int, search?: string} $params
     */
    public function __construct(array $params)
    {
        $this->page    = isset($params['page']) ? (int) $params['page'] : 1;
        $this->perPage = isset($params['perPage']) ? (int) $params['perPage'] : 15;
        $this->search  = isset($params['search']) ? (string) $params['search'] : '';
    }

    public function page(): int
    {
        return $this->page;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * @return array<int, string>
     */
    public function criteria(): array
    {
        if ($this->search) {
            return explode(' ', $this->search);
        }
        return [];
    }
}
