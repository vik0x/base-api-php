<?php

namespace Src\Infrastructure\Http\Transformers;

use League\Fractal\TransformerAbstract;
use Src\Domain\ActivityLog\ActivityLog;

class ActivityLogTransformer extends TransformerAbstract
{
    /**
     * @return array<string, mixed>
     */
    public function transform(ActivityLog $activityLog): array
    {
        return [
                'id'         => $activityLog->id() ? $activityLog->id()->value() : null,
                'action'     => $activityLog->action(),
                'entity'     => $activityLog->entity(),
                'entity_id'  => $activityLog->entityId(),
                'data'       => $activityLog->data(),
                'user_id'    => $activityLog->userId() ? $activityLog->userId()->value() : null,
                'created_at' => $activityLog->createdAt()->format('Y-m-d H:i:s'),
               ];
    }
}
