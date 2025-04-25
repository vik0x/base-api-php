<?php

namespace Src\Domain\Shared\Pagination;

interface PaginationInterface
{
    /**
     * Returns the paginated results
     *
     * @return array<int, mixed>
     */
    public function getResults(): array;

    /**
     * Returns the current page number
     *
     * @return int Current page number (starting from 1)
     */
    public function getCurrentPage(): int;

    /**
     * Returns the number of elements per page
     *
     * @return int Number of elements per page
     */
    public function getItemsPerPage(): int;

    /**
     * Returns the total number of pages
     *
     * @return int Total number of pages
     */
    public function getNbPages(): int;

    /**
     * Returns the total number of results
     *
     * @return int Total number of results in all pages
     */
    public function getNbResults(): int;

    /**
     * Checks if there is a next page
     *
     * @return bool True if there is a next page, false otherwise
     */
    public function hasNextPage(): bool;

    /**
     * Checks if there is a previous page
     *
     * @return bool True if there is a previous page, false otherwise
     */
    public function hasPreviousPage(): bool;

    /**
     * Returns the pagination metadata
     *
     * @return array<string, mixed> Information about the pagination state
     */
    public function getMetadata(): array;
}
