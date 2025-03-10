<?php

namespace Src\Infrastructure\Http\Transformers;

use Src\Domain\User\User;

class UserTransformer extends AbstractTransformer
{
  public function transform(User $user): array
  {
    return [
      'id' => $user->id()->value(),
      'name' => $user->name(),
      'email' => $user->email()->value(),
      'created_at' => $this->formatDateTime($user->createdAt()),
      'updated_at' => $this->formatDateTime($user->updatedAt()),
    ];
  }
}
