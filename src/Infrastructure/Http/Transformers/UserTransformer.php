<?php

namespace Src\Infrastructure\Http\Transformers;

use League\Fractal\TransformerAbstract;
use Src\Domain\User\User;

class UserTransformer extends TransformerAbstract
{
    /**
     * @return array<string, mixed>
     */
    public function transform(User $user): array
    {
        return [
                'id'         => $user->id()->value(),
                'name'       => $user->name(),
                'email'      => $user->email()->value(),
                'created_at' => $user->createdAt()->format('Y-m-d H:i:s'),
                'updated_at' => $user->updatedAt() ? $user->updatedAt()->format('Y-m-d H:i:s') : null,
               ];
    }
}
