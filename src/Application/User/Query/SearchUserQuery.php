<?php

namespace Src\Application\User\Query;

final class SearchUserQuery
{
    private int $page;
    private int $perPage;
    private string $search;

    public function __construct($params)
    {
        $this->page    = $params['page'] ?? 1;
        $this->perPage = $params['perPage'] ?? 15;
        $this->search  = $params['search'] ?? '';
    }

    public function page(): int
    {
        return $this->page;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function criteria(): array
    {
        if ($this->search) {
            return explode(' ', $this->search);
        }
        return [];
    }
}
