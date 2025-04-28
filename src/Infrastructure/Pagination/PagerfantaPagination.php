<?php

namespace Src\Infrastructure\Pagination;

use Pagerfanta\Pagerfanta;
use Src\Domain\Shared\Pagination\PaginationInterface;

class PagerfantaPagination implements PaginationInterface
{
    /**
     * @var Pagerfanta<mixed>
     */
    private Pagerfanta $pagerfanta;

    /**
     * @param Pagerfanta<mixed> $pagerfanta
     */
    public function __construct(Pagerfanta $pagerfanta)
    {
        $this->pagerfanta = $pagerfanta;
    }

    /**
     * @return array<int, mixed>
     */
    public function getResults(): array
    {
        /** @var array<int, mixed> $results */
        $results = $this->pagerfanta->getCurrentPageResults();
        return $results;
    }

    public function getCurrentPage(): int
    {
        return $this->pagerfanta->getCurrentPage();
    }

    public function getItemsPerPage(): int
    {
        return $this->pagerfanta->getMaxPerPage();
    }

    public function getNbPages(): int
    {
        return $this->pagerfanta->getNbPages();
    }

    public function getNbResults(): int
    {
        return $this->pagerfanta->getNbResults();
    }

    public function hasNextPage(): bool
    {
        return $this->pagerfanta->hasNextPage();
    }

    public function hasPreviousPage(): bool
    {
        return $this->pagerfanta->hasPreviousPage();
    }

    /**
     * @return array{current_page: int, per_page: int, total_items: int, total_pages: int, has_previous_page: bool, has_next_page: bool}
     */
    public function getMetadata(): array
    {
        return [
                'current_page'      => $this->getCurrentPage(),
                'per_page'          => $this->getItemsPerPage(),
                'total_items'       => $this->getNbResults(),
                'total_pages'       => $this->getNbPages(),
                'has_previous_page' => $this->hasPreviousPage(),
                'has_next_page'     => $this->hasNextPage(),
               ];
    }
}
