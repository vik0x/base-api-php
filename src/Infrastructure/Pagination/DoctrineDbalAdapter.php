<?php

namespace Src\Infrastructure\Pagination;

use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * @implements AdapterInterface<array<string, mixed>>
 */
class DoctrineDbalAdapter implements AdapterInterface
{
    private QueryBuilder $queryBuilder;
    private QueryBuilder $countQueryBuilder;
    private string $countField;

    /**
     * @param QueryBuilder $queryBuilder
     * @param QueryBuilder $countQueryBuilder
     * @param string $countField
     */
    public function __construct(QueryBuilder $queryBuilder, QueryBuilder $countQueryBuilder, string $countField = '*')
    {
        $this->queryBuilder      = $queryBuilder;
        $this->countQueryBuilder = $countQueryBuilder;
        $this->countField        = $countField;
    }

    /**
     * {@inheritdoc}
     * @return int<0, max>
     */
    public function getNbResults(): int
    {
        $this->countQueryBuilder->select('COUNT(' . $this->countField . ') AS total_results');
        $count = (int) $this->countQueryBuilder->executeQuery()->fetchOne();

        /** @var int<0, max> */
        return max(0, $count);
    }

    /**
     * {@inheritdoc}
     * @param int $offset
     * @param int $length
     * @return array<int, array<string, mixed>>
     */
    public function getSlice($offset, $length): array
    {
        $this->queryBuilder->setFirstResult($offset)
            ->setMaxResults($length);

        return $this->queryBuilder->executeQuery()->fetchAllAssociative();
    }
}
