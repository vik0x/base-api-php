<?php

namespace Src\Infrastructure\Persistence\Repositories;

use Doctrine\DBAL\Connection;
use Src\Domain\ActivityLog\ActivityLog;
use Src\Domain\ActivityLog\Repositories\ActivityLogRepository;
use Src\Domain\Shared\ValueObjects\Id;
use Src\Domain\User\ValueObjects\UserId;

class DoctrineActivityLogRepository implements ActivityLogRepository
{
    private string $table = 'activity_logs';

    public function __construct(private Connection $connection)
    {
    }

    public function save(ActivityLog $activityLog): void
    {
        $data = [
                 'action'     => $activityLog->action(),
                 'entity'     => $activityLog->entity(),
                 'entity_id'  => $activityLog->entityId(),
                 'data'       => json_encode($activityLog->data()),
                 'user_id'    => $activityLog->userId() ? $activityLog->userId()->value() : null,
                 'created_at' => $activityLog->createdAt()->format('Y-m-d H:i:s'),
                ];

        if ($activityLog->id()) {
            $this->connection->update(
                $this->table,
                $data,
                ['id' => $activityLog->id()->value()]
            );
        } else {
            $this->connection->insert($this->table, $data);
        }
    }

    public function findById(int $id): ?ActivityLog
    {
        $stmt = $this->connection->prepare('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        $stmt->bindValue(':id', $id);
        $row = $stmt->executeQuery()->fetchAssociative();

        if (! $row) {
            return null;
        }

        return $this->hydrateActivityLog($row);
    }

    /**
     * @param array<int, string> $filters
     * @return array<string, mixed>
     */
    public function findAll(int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $offset     = ($page - 1) * $perPage;
        $query      = 'SELECT * FROM ' . $this->table . ' ';
        $countQuery = 'SELECT COUNT(*) FROM ' . $this->table . ' ';
        $params     = [];

        $filterClauses = [];
        if (! empty($filters)) {
            foreach ($filters as $index => $filter) {
                // Solo construir las condiciones si son cadenas con data válida
                if (is_string($filter) && $filter !== '') {
                    $filterClauses[]            = '(entity LIKE :entity' . $index . ' OR action LIKE :action' . $index . ' OR user_id LIKE :user_id' . $index . ')';
                    $params['entity' . $index]  = '%' . $filter . '%';
                    $params['action' . $index]  = '%' . $filter . '%';
                    $params['user_id' . $index] = '%' . $filter . '%';
                }
            }
        }

        if (! empty($filterClauses)) {
            $whereClause = ' WHERE ' . implode(' AND ', $filterClauses);
            $query      .= $whereClause;
            $countQuery .= $whereClause;
        }

        $query .= ' ORDER BY created_at DESC LIMIT ' . $perPage . ' OFFSET ' . $offset;

        $stmt      = $this->connection->prepare($query);
        $countStmt = $this->connection->prepare($countQuery);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
            $countStmt->bindValue(':' . $key, $value);
        }

        $rows  = $stmt->executeQuery()->fetchAllAssociative();
        $total = (int) $countStmt->executeQuery()->fetchOne();

        $logs = [];
        foreach ($rows as $row) {
            $logs[] = $this->hydrateActivityLog($row);
        }

        return [
                'data'     => $logs,
                'total'    => $total,
                'page'     => $page,
                'per_page' => $perPage,
               ];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrateActivityLog(array $row): ActivityLog
    {
        $action   = (string) $row['action'];
        $entity   = (string) $row['entity'];
        $entityId = $row['entity_id'] !== null ? (string) $row['entity_id'] : null;
        $data     = json_decode((string) $row['data'], true);

        /** @var array<string, mixed> $data */
        $data = is_array($data) ? $data : [];

        $userId = null;
        if ($row['user_id'] !== null) {
            $userId = new UserId((int) $row['user_id']);
        }

        $id = new Id((int) $row['id']);

        return new ActivityLog(
            $action,
            $entity,
            $entityId,
            $data,
            $userId,
            $id,
            new \DateTimeImmutable((string) $row['created_at'])
        );
    }
}
