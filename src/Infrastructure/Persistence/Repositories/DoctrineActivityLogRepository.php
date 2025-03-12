<?php

namespace Src\Infrastructure\Persistence\Repositories;

use Doctrine\DBAL\Connection;
use Src\Domain\ActivityLog\ActivityLog;
use Src\Domain\ActivityLog\ActivityLogRepository;
use Src\Domain\Shared\ValueObjects\Id;

final class DoctrineActivityLogRepository implements ActivityLogRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function save(ActivityLog $activityLog): void
    {
        $data = [
                 'action'     => $activityLog->action(),
                 'entity'     => $activityLog->entity(),
                 'entity_id'  => $activityLog->entityId(),
                 'data'       => json_encode($activityLog->data()),
                 'user_id'    => $activityLog->userId(),
                 'created_at' => $activityLog->createdAt()->format('Y-m-d H:i:s'),
                ];

        if ($activityLog->id() === null) {
            $this->connection->insert('activity_logs', $data);
        } else {
            $this->connection->update(
                'activity_logs',
                $data,
                ['id' => $activityLog->id()->value()]
            );
        }
    }

    public function findById(int $id): ?ActivityLog
    {
        $stmt = $this->connection->createQueryBuilder()
        ->select('*')
        ->from('activity_logs')
        ->where('id = :id')
        ->setParameter('id', $id)
        ->executeQuery();

        $row = $stmt->fetchAssociative();

        if (! $row) {
            return null;
        }

        return $this->hydrateActivityLog($row);
    }

    public function findAll(int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;

        $queryBuilder = $this->connection->createQueryBuilder()
        ->select('*')
        ->from('activity_logs')
        ->orderBy('created_at', 'DESC')
        ->setMaxResults($perPage)
        ->setFirstResult($offset);

      // Apply filters if any
        if (isset($filters['entity'])) {
            $queryBuilder->andWhere('entity = :entity')
            ->setParameter('entity', $filters['entity']);
        }

        if (isset($filters['action'])) {
            $queryBuilder->andWhere('action = :action')
            ->setParameter('action', $filters['action']);
        }

        if (isset($filters['user_id'])) {
            $queryBuilder->andWhere('user_id = :user_id')
            ->setParameter('user_id', $filters['user_id']);
        }

        $stmt = $queryBuilder->executeQuery();
        $rows = $stmt->fetchAllAssociative();

      // Count total records for pagination
        $countQueryBuilder = $this->connection->createQueryBuilder()
        ->select('COUNT(*) as total')
        ->from('activity_logs');

      // Apply the same filters to the count query
        if (isset($filters['entity'])) {
            $countQueryBuilder->andWhere('entity = :entity')
            ->setParameter('entity', $filters['entity']);
        }

        if (isset($filters['action'])) {
            $countQueryBuilder->andWhere('action = :action')
            ->setParameter('action', $filters['action']);
        }

        if (isset($filters['user_id'])) {
            $countQueryBuilder->andWhere('user_id = :user_id')
            ->setParameter('user_id', $filters['user_id']);
        }

        $countStmt = $countQueryBuilder->executeQuery();
        $total     = (int) $countStmt->fetchOne();

        $logs = array_map([$this, 'hydrateActivityLog'], $rows);

        return [
                'data'  => $logs,
                'total' => $total,
               ];
    }

    private function hydrateActivityLog(array $row): ActivityLog
    {
        return new ActivityLog(
            $row['action'],
            $row['entity'],
            $row['entity_id'],
            json_decode($row['data'], true),
            $row['user_id'],
            new Id($row['id'])
        );
    }
}
