<?php

namespace Src\Infrastructure\Http\Transformers;

use Src\Domain\ActivityLog\ActivityLog;

class ActivityLogTransformer extends AbstractTransformer
{
    public function transform(ActivityLog $activityLog): array
    {
        return [
                'id'         => $activityLog->id()->value(),
                'action'     => $activityLog->action(),
                'entity'     => $activityLog->entity(),
                'entity_id'  => $activityLog->entityId(),
                'data'       => $activityLog->data(),
                'user_id'    => $activityLog->userId(),
                'created_at' => $this->formatDateTime($activityLog->createdAt()),
               ];
    }
}
